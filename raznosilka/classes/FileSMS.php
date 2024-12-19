<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\FileSMS.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class FileSMS Абстрактный класс служит для обработки SMS содержащихся в файле и последующей работы с ними
 */
abstract class FileSMS {
  /**
   * @var array Информация о раскодируемом файле с SMS
   */
  protected $file;
  /**
   * @var array Массив полученный из исходного файла
   */
  protected $arrayFromFile = array();
  /**
   * @var array Массив только со склеенными SMS
   */
  protected $arrayGluedSMS = array();
  /**
   * @var array Массив только с расклеенными СМС
   */
  protected $arrayUngluedSMS = array();
  /**
   * @var array Массив разделённых SMS
   */
  protected $arraySeparatedSMS = array();
  /**
   * @var array Массив распознанных SMS и извлечённых из них данных
   */
  protected $arrayProcessedSMS = array();
  /**
   * @var array Массив распознанных но не содержащих полезных данных SMS
   */
  protected $arrayDetectedUnknownSMS = array();
  /**
   * @var array Массив неизвестных SMS
   */
  protected $arrayNotDetectedUnknownSMS = array();
  /**
   * @var array Массив с сообщениями о результате обработке файлов с SMS
   */
  protected $message = array();
  /**
   * @var array Массив с сохранёнными распознанными СМС
   */
  protected $arraySaveProcessedSMS = array();
  /**
   * @var array Массив с не сохранёнными распознанными СМС (уже имеющимися в БД)
   */
  protected $arrayNotSaveProcessedSMS = array();
  /**
   * @var array Массив с сохранёнными не распознанными и неопределёнными СМС
   */
  protected $arraySaveUnknownSMS = array();
  /**
   * @var array Массив с сохранёными распознанными СМС содержащими сообщение
   */
  protected $arraySaveProcessedCommentSMS = array();
  /**
   * @var ToolsSMS Вспомогательный класс содержащий инструменты для работы с СМС
   */
  protected $tools;

  /**
   * Конструктор класса
   * @param array $file Массив с пользовательским именем файла, и путём к файлу во временной папке на сервере
   */
  function __construct ($file) {
    $this->file = $file;
    $this->tools = new ToolsSMS();
  }

  /**
   * Статический метод фабрика, возвращающий соотвествующий декодер для работы с файлом SMS
   * @param string $file Путь к распозноваемому файлу
   * @return bool|FileSMS
   */
  static function detect ($file) {
    // Определение по расширению файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    switch ($extension) {
      case 'csv' :
        // Если файл в кодировке UTF-8
        $handle = fopen($file['tmp_name'], "r"); // todo нет проверки на ошибку
        // Определение по разделителю - ;
        $data = fgetcsv($handle, 0, ";");
        // Определение по количеству полей
        switch (count($data)) {
          case 8 :
            // Определяем по содержимому
            $marker = !empty($data[0]) && !empty($data[1]) && is_numeric($data[2]) && empty($data[3]) && empty($data[4]) && !empty($data[5]) && empty($data[6]) && !empty($data[7]);
            if ($marker) {
              // Определяем формату даты
              if (DateTime::createFromFormat('Y.m.d H:i', $data[5]) instanceof DateTime) {
                return new FileSMS_NokiaPCSuite7($file);
              }
              if (DateTime::createFromFormat('d.m.Y H:i', $data[5]) instanceof DateTime) {
                return new FileSMS_NokiaSuite3($file);
              }
            }
            break;
        }
        // Если файл в кодировке UTF-16
        $convert = self::convertFileEncode($file['tmp_name'], 'UTF-16');
        // Определение по разделителю - ;
        $dataTemp = self::StringToArrayCsv($convert, ';');
        // Определяем по количеству полей
        switch (count($dataTemp[0])) {
          case 4 :
            // Определяем по содержимому заголовка
            $marker = ($dataTemp[0][0]=='Разговор с') && ($dataTemp[0][1]=='Дата') && ($dataTemp[0][2]=='Отправленные сообщения') && ($dataTemp[0][3]=='Полученные сообщения');
            if ($marker) {
              return new FileSMS_CopyTransContacts4($file);
            }
            break;
        }
        // Возвращение в начало файла
        // rewind($handle);
        // Определение по разделителю - ,
        // $data = fgetcsv($handle, 0, ",");
        fclose($handle);
        break;
      case 'xml' :
        $xml = simplexml_load_file($file['tmp_name']);
        // По наличию тэга
        if (isset($xml->sms)) {
          // По наличию аттрибутов
          $marker = isset($xml->sms->attributes()->address) and isset($xml->sms->attributes()->date) and isset($xml->sms->attributes()->body);
          if ($marker) {
            return new FileSMS_SMSBackupAndRestore7($file);
          }
        }
        break;
    }
    return false;
  }

  /**
   * Изменяет кодировку в указанном файле
   * @param $file string Путь к файлу, у которого нужно изменить кодировку
   * @param $encode string Кодировка исходного файла
   * @return string Файл с изменённой кодировкой (UTF-8)
   */
  static function convertFileEncode ($file, $encode) {
    $dataTemp = file_get_contents($file);
    $data = mb_convert_encoding($dataTemp, 'UTF-8', $encode);
    return $data;
  }

  /**
   * Преобразует строку с содержимым CSV в массив
   * @param $convert string Строка с файлом
   * @param $delivery string Символ разделитель
   * @return array Массив с содержимым CSV
   */
  static function StringToArrayCsv ($convert, $delivery) {
    $csv = array();
    $arr = explode("\r\n", $convert);
    foreach($arr as $value) {
      $csv[] = str_getcsv($value, $delivery);
    }
    return $csv;
  }

  /**
   * Вывод результатов работы скрипта при помощи класса Notify
   * @param Notify $notify экземпляр объекта отвечающего за отсылку уведомлений
   */
  function printMessagesAsNotify (Notify $notify) {
    if (!empty($this->message)) {
      foreach ($this->message as $message) {
        $notify->sendNotify($message['text'], $message['type']);
      }
    }
  }

  /**
   * Распознавание SMS
   */
  function decrypt () {
    // Получение строк с SMS из исходного файла
    $this->arrayFromFile = $this->SMSFromFile();
    // Разделение склеенных SMS
    $result = $this->tools->separationGluedSMS($this->arrayFromFile);
    $this->arraySeparatedSMS = $result['separated'];
    $this->arrayGluedSMS = $result['glued'];
    $this->arrayUngluedSMS = $result['unglued'];
    // Распознование SMS
    $result = $this->tools->processedSMS($this->arraySeparatedSMS);
    $this->arrayProcessedSMS = $result['processed'];
    // Определить нераспознанные SMS
    $result = $this->tools->detectUnknownSMS($result['unknown']);
    $this->arrayDetectedUnknownSMS = $result['detected'];
    $this->arrayNotDetectedUnknownSMS = $result['not_detected'];
  }

  /**
   * Метод для получения массива из файла экспорта.
   * @return array Массив со строками содержимого файла экспорта
   */
  abstract function SMSFromFile ();

  /**
   * Возвращает массив с разделёнными SMS
   * @return array Массив с разделёнными SMS
   */
  function getArraySeparatedSMS () {
    return $this->arraySeparatedSMS;
  }

  /**
   * Возвращает массив со склеенными SMS
   * @return array Массив со склеенными SMS
   */
  function getArrayGluedSMS () {
    return $this->arrayGluedSMS;
  }

  /**
   * Возвращает массив с необработанными строками полученными из файла с SMS
   * @return array Массив с необработанными строками полученными из файла с SMS
   */
  function getArrayFromFile () {
    return $this->arrayFromFile;
  }

  /**
   * Возвращает массив с опознанными SMS и данными полученными из них
   * @return array Массив с опознанными SMS и данными полученными из них
   */
  function getArrayProcessedSMS () {
    return $this->arrayProcessedSMS;
  }

  /**
   * Возвращает массив с опознанными, но не имеющих нужных сведений SMS
   * @return array Массив с опознанными, но не имеющих нужных сведений SMS
   */
  function getArrayDetectedUnknownSMS () {
    return $this->arrayDetectedUnknownSMS;
  }

  /**
   * Возвращает массив с не опознанными SMS
   * @return array Массив с не опознанными SMS
   */
  function getArrayNotDetectedUnknownSMS () {
    return $this->arrayNotDetectedUnknownSMS;
  }

  /**
   * Возвращает массив с сохранёнными распознанными СМС
   * @return array Массив с сохранёнными распознанными СМС
   */
  function getArraySaveProcessedSMS () {
    return $this->arraySaveProcessedSMS;
  }

  /**
   * Возвращает массив с не сохранёнными распознанными СМС (уже имеющимися в БД)
   * @return array Массив с не сохранёнными распознанными СМС (уже имеющимися в БД)
   */
  function getArrayNotSaveProcessedSMS () {
    return $this->arrayNotSaveProcessedSMS;
  }

  /**
   * Возвращает массив с не сохранёнными нераспознанными СМС (уже имеющимися в БД)
   * @return array Массив с не сохранёнными нераспознанными СМС (уже имеющимися в БД)
   */
  function getArraySaveUnknownSMS () {
    return $this->arraySaveUnknownSMS;
  }

  /**
   * Возвращает массив с сохранёнными СМС содержащими сообщения от участников
   * @return array Массив с сохранёнными СМС содержащими сообщения от участников
   */
  function getArraySaveProcessedCommentSMS () {
    return $this->arraySaveProcessedCommentSMS;
  }

  /**
   * Возвращает массив только с расклеенными СМС
   * @return array Массив только с расклеенными СМС
   */
  function getArrayUngluedSMS () {
    return $this->arrayUngluedSMS;
  }

  /**
   * Возвращает информацию о файле
   * @return array Массив с информацией о сайте
   */
  function getFileInfo () {
    return $this->file;
  }

  /**
   * Сохранение СМС
   */
  function saveSMS () {
    // Сохраняем распознанные СМС
    $result = $this->tools->saveProcessedSMS($this->arrayProcessedSMS);
    $this->arraySaveProcessedSMS = $result['save'];
    $this->arrayNotSaveProcessedSMS = $result['not_save'];
    $this->arraySaveProcessedCommentSMS = $result['comment'];
    // Отсылаем уведомления и сообщения
    if (!empty($this->arraySaveProcessedCommentSMS)) {
      $countComment = count($this->arraySaveProcessedCommentSMS);
      $url = URL::to('messages');
      $this->setMessage("Найдены SMS содержащие сообщения от участников: <b>{$countComment} шт</b>. Смотрите в <a href='{$url}'>уведомлениях</a> более подробную информацию.", ERROR_NOTIFY);
      foreach ($this->arraySaveProcessedCommentSMS as $sms) {
        $this->tools->sendMessage($sms);
      }
    }
    // Сохраняем нераспознанные СМС
    $result = $this->tools->saveUnknownSMS($this->arrayNotDetectedUnknownSMS);
    $this->arraySaveUnknownSMS = $result['save'];
    // Сообщение о нераспознанных СМС
    if (!empty($this->arraySaveUnknownSMS)) {
      // $name = $this->file['name'];
      // $count = count($this->arraySaveUnknownSMS);
      // $this->setMessage("В файле <b>{$name}</b> найдены нераспознанные SMS: <b>{$count} шт</b>.", INFO_NOTIFY);
    }
  }

  /**
   * Метод для добавления сообщений о результатах обработки SMS
   * @param string $message Сообщение
   * @param string $type Тип сообщения
   */
  function setMessage ($message, $type) {
    $this->message[] = array('type' => $type, 'text' => $message);
  }

}