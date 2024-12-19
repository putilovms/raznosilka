<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\LoadFiles.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class LoadFiles Загрузчик файлов с SMS.
   * Отвечает за загрузку, проверку и удаление файлов с SMS.
   */
  class LoadFiles {
    /**
     * @var string Путь к каталогу временных файлов
     */
    private $tmpPath;
    /**
     * @var array Массив с сообщениями о результате загрузки файлов с SMS
     */
    private $message = array();
    /**
     * @var array Массив с сохранёнными файлами во временную папку
     */
    private $saveFiles = array();
    /**
     * @var array Массив с загружаемыми пользователем файлами
     */
    private $loadFiles = array();
    /**
     * @var array Массив с незагруженными файлами - имена файлов и причины по которым они не были загружены
     */
    private $errorFiles = array();

    /**
     * Конструктор класса
     * @param string $tmpPath Путь к временной папке (введён для тестирования)
     */
    function __construct ($tmpPath = null) {
      if (empty($tmpPath)) {
        $this->tmpPath = $this->getTmpPath();
      } else {
        $this->tmpPath = $tmpPath;
      }
    }

    /**
     * Получает путь к временной папке.
     * Вынесено для тестирования.
     * @return string
     */
    function getTmpPath () {
      return $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_path');
    }

    /**
     * Возвращает массив с незагруженными файлами
     * @return array Массив с незагруженными файлами:
     * - имена файлов
     * - причины по которым они не были загружены
     */
    function getErrorFiles () {
      return $this->errorFiles;
    }

    /**
     * Возвращает массив с загружаемыми пользователем файлами
     * @return array Массив с загружаемыми пользователем файлами
     */
    function getLoadFiles () {
      return $this->loadFiles;
    }

    /**
     * Метод для загрузки файлов с SMS.
     * @param array $files Список загруженных файлов из массива $_FILES
     * @param bool $test добавлено для тестирования
     * @return false|array Результат загрузки файлов
     * - false - если не загружен ни один файл
     * - array - список файлов содержащий пользовательское имя файла, и временный путь к файлу.
     */
    public function loadSms ($files, $test = false) {
      if (!empty($files)) {
        // Преобразование массива с файлами
        $files = $this->conversionFilesArr($files);
        $this->loadFiles = $files;
        // Предварительная проверка файлов
        $files = $this->preCheckFiles($files);
        // Сохраняем все файлы в каталоге временных файлов
        if (!empty($files)) {
          $result = $this->saveFiles($files, $test);
          $this->saveFiles = $result;
          // Проверка файлов после загрузки
          $result = $this->postCheckFiles($this->saveFiles);
          $this->saveFiles = $result;
          if (!empty($this->saveFiles)) {
            // $count = count($this->saveFiles);
            // $this->setMessage("Всего успешно загружено файлов с SMS: <b>{$count} шт</b>.", SUCCESS_NOTIFY);
            return $this->saveFiles;
          }
        }
      }
      return false;
    }

    /**
     * Преобразование массива $_FILES в удобный для работы вид.
     * @param array $files Исходный массив $_FILES
     * @return array Преобразованный массив $_FILES
     */
    function conversionFilesArr ($files) {
      $arr = array();
      foreach ($files as $key => $file) {
        foreach ($file as $key_file => $value) {
          $arr[$key_file][$key] = $value;
        }
      }
      return $arr;
    }

    /**
     * Предварительная проверка загруженных пользователем файлов.
     * Проверка на соотвествие MIME типа, расширения и отсутствие ошибок загрузки.
     * @param array $files Список загруженных файлов
     * @return array
     */
    function preCheckFiles ($files) { // todo описать выходной массив
      // Список разрешённых MIME типов файла
      $allowedTypes = array('text/plain','application/xml');
      // Список разрешённых расширений файла
      $allowedExtension = array('csv', 'xml');
      // Инициализация
      $arr = array();
      $logs = new Logs();
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      // Перебор и проверка загруженных файлов
      foreach ($files as $file) {
        // Если при загрузке файла произошла ошибка
        if (!empty($file['error'])) {
          $this->errorFiles[] = array('name' => $file['name'], 'error' => 'Ошибка загрузки');
          $this->setMessage("При загрузке файла <b>{$file['name']}</b> произошла ошибка.", ERROR_NOTIFY);
          // Лог
          $logs->actionLog($user->getUserInfo(), "Ошибка загрузки файла #{$file['error']}. Не удалось загрузить файл \"{$file['name']}\"");
          continue;
        }
        // Пороверка допустимых расширений файла
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array($extension, $allowedExtension)) {
          $this->errorFiles[] = array('name' => $file['name'], 'error' => 'Файл имеет недопустимое расширение');
          $this->setMessage("Файл <b>{$file['name']}</b> имеет недопустимое расширение.", ERROR_NOTIFY);
          // Лог
          $logs->actionLog($user->getUserInfo(), "Ошибка загрузки файла. Файл \"{$file['name']}\" имеет недопустимое расширение");
          continue;
        }
        // Проверка допустимых MIME типов файла
        if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
          $this->errorFiles[] = array('name' => $file['name'], 'error' => 'Файл имеет недопустимый тип');
          $this->setMessage("Файл <b>{$file['name']}</b> имеет недопустимый тип.", ERROR_NOTIFY);
          // Лог
          $logs->actionLog($user->getUserInfo(), "Ошибка загрузки файла. Файл \"{$file['name']}\" имеет недопустимый тип");
          continue;
        }
        $arr[] = $file;
      }
      return $arr;
    }

    /**
     * Сохраняет предварительно проверенные файлы во временном каталоге.
     * @param array $files Список загруженных файлов
     * @param bool $test Режим тестирования
     * @return array Массив сохранённых файлов во временную папку:
     * - tmp_name - Путь к сохранённому файлу во временной папке
     * - name - пользовательское имя файла
     */
    function saveFiles ($files, $test = false) {
      $saveFiles = array();
      foreach ($files as $file) {
        $tmpName = $file['tmp_name'];
        $saveName = $this->tmpPath . '/' . md5($tmpName) . ".tmp";
        // Если это не тестирование
        if (!$test) {
          $result = move_uploaded_file($tmpName, $saveName);
        } else {
          $result = copy($tmpName, $saveName);
        }
        if (!$result) {
          $controller = new Controller_Error();
          $controller->index(__LINE__, __FILE__);
        }
        $saveFiles[] = array('tmp_name' => $saveName, 'name' => $file['name']);
      }
      return $saveFiles;
    }

    /**
     * Проверка после загрузки файлов, на то что это действительно файл CSV
     * @param array $files Массив с загруженными файлами
     * @param bool $test для тестирования
     * @return array Массив с файлами прошедшими проверку
     */
    function postCheckFiles ($files, $test = false) {
      $saveFiles = array();
      if (!empty($files)) {
        $arr = $files;
        foreach ($files as $key => $file) {
          $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
          $check = true;
          switch ($extension) {
            case 'csv' :
              // Проверка на то, что файл действительно CSV
              $handle = fopen($file['tmp_name'], "r"); // todo нет проверки на ошибку
              $result = fgetcsv($handle, 0, ";");
              fclose($handle);
              if (count($result) <= 1) $check = false;
              break;
            case 'xml' :
              // Проверка на то, что файл действительно XML
              if ($test) libxml_use_internal_errors(true); // Для тестирования
              $xml = simplexml_load_file($file['tmp_name']);
              if (!($xml instanceof \SimpleXMLElement)) $check = false;
              break;
          }
          if (!$check) {
            $this->errorFiles[] = array('name' => $file['name'], 'error' => 'Файл имеет недопустимый тип');
            $this->setMessage("Файл <b>{$file['name']}</b> имеет недопустимый тип.", ERROR_NOTIFY);
            unset($arr[$key]);
            @unlink($file['tmp_name']); // todo нет проверки на ошибку
          }
        }
        // обновление данных массива со списком файлов
        $saveFiles = $arr;
      }
      return $saveFiles;
    }

    /**
     * Получить все сообщения
     * @return array Массив сообщений
     */
    function getMessage () {
      return $this->message;
    }

    /**
     * Метод для добавления сообщений о результате загрузки и обработки SMS
     * @param string $message Сообщение
     * @param string $type Тип сообщения
     */
    function setMessage ($message, $type) {
      $this->message[] = array('type' => $type, 'text' => $message);
    }

    /**
     * Вывод результатов работы скрипта при помощи класса Notify
     * @param Notify $notify
     */
    function printMessagesAsNotify (Notify $notify) {
      if (!empty($this->message)) {
        foreach ($this->message as $message) {
          $notify->sendNotify($message['text'], $message['type']);
        }
      }
    }

    /**
     * Деструктор класса. Удаляет загруженные файлы из временного каталога.
     */
    function __destruct () {
      if (!empty($this->saveFiles)) {
        foreach ($this->saveFiles as $file) {
          @unlink($file['tmp_name']); // todo проверка на ошибку
        }
      }
    }

  }