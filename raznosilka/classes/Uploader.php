<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Uploader.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Uploader Класс содержащий модуль загрузки SMS
   */
  class Uploader {
    /**
     * @var array Массив с сообщениями о результате обработке файлов с SMS
     */
    protected $message = array();
    /**
     * @var array Список файлов с неизвестными форматами экспорта SMS
     */
    protected $fileUnknownFormat = array();
    /**
     * @var LoadFiles Содержит объект для работы с файлами SMS
     */
    private $load;
    /**
     * @var array|false Массив с загруженными файлами
     */
    private $files = false;
    /**
     * @var array Массив с объектами содержащими распознанные SMS
     */
    private $arrayObjSMS = array();

    /**
     * Конструктор класса
     */
    function __construct () {
      // Помещён в свойства класса, чтобы корректно сработал деструктор
      $this->load = new LoadFiles();
    }

    /**
     * Запуск загрузки SMS
     * @param array $files Список загруженных файлов из массива $_FILES
     * @return bool Результат загрузки и сохранения СМС:
     * - true - Файлы были успешно распознанны, а уникальные СМС добавлены
     * - false - не удалось загрузить или распознать ни одного файла.
     */
    function execute ($files) {
      // Инициализация
      $return = false;
      $logs = new Logs();
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      // Загружаем файлы с SMS
      $result = $this->loadFiles($files);
      $this->files = $result;
      if ($result) {
        // Распознаём файлы с SMS
        foreach ($this->files as $file) {
          $result = $this->processedSMS($file);
          if ($result instanceof FileSMS) {
            // Добавляем в массив распознанных файлов с СМС
            $this->arrayObjSMS[] = $result;
            // Лог
            $logs->actionLog($user->getUserInfo(), "Файл \"{$file['name']}\" успешно загружен");
          } else {
            // Добавляем в массив с файлами неизвестных фаорматов
            $this->fileUnknownFormat[] = $file;
            $this->setMessage("Не удалось определить формат файла <b>{$file['name']}</b>.", ERROR_NOTIFY);
            // Лог
            $logs->actionLog($user->getUserInfo(), "Ошибка загрузки файла. Не определён формат файла \"{$file['name']}\"");
          }
        }
        // Если имеются распознанные файлы с СМС
        if (!empty($this->arrayObjSMS)) {
          // Сохраняем полученные SMS в БД
          $this->saveSMS();
          // Отсылаем e-mail администратору, если есть неопознанные СМС добавленные в БД
          $this->sendMailUnknownSMS();
          $return = true;
        }
      }
      $this->printAllNotify();
      return $return;
    }

    /**
     * Вспомогательный метод для загрузки файлов
     * @param array $files Список загруженных файлов из массива $_FILES
     * @return array|false Результат выполнения загрузки файлов на сервис
     * - false - в случае если файлы не загружены
     * - array - массив с путями к загруженным файлам во временном каталоге
     */
    function loadFiles ($files) {
      $tmpFiles = $this->load->loadSms($files);
      return $tmpFiles;
    }

    /**
     * Распознавание загруженных SMS
     * @param array $file Информация о файле для обработки
     * @return false|FileSMS Результат выполнения операции:
     * - FileSMS - распознанный файл с SMS
     * - false - в случае неудачи
     */
    function processedSMS ($file) {
      // Получаем реализацию объекта FileSMS для распознавания файла с SMS
      $fileSms = FileSMS::detect($file);
      if ($fileSms == false) return false;
      // Запуск распознавания файла с SMS
      $fileSms->decrypt();
      // возврат объекта с обработанным файлом SMS
      return $fileSms;
    }

    /**
     * Метод для добавления сообщений о результатах обработки SMS
     * @param string $message Сообщение
     * @param string $type Тип сообщения
     */
    function setMessage ($message, $type) {
      $this->message[] = array('type' => $type, 'text' => $message);
    }

    /**
     * Сохранение полученных SMS в БД
     */
    function saveSMS () {
      foreach ($this->arrayObjSMS as $fileSms) {
        // Сохраняем распознанные СМС
        /** @var FileSMS $fileSms **/
        $fileSms->saveSMS();
      }
    }

    /**
     * Отсылает E-mail администратору если после сохранения, была добавлена
     * хотя бы одна новая неопознанная СМС в таблицу sms_unknown
     */
    function sendMailUnknownSMS () {
      // инициализация
      $unknown = false;
      $arr = array();
      // Обнаружение добавленных неопознанных СМС
      foreach ($this->arrayObjSMS as $fileSms) {
        /** @var FileSMS $fileSms **/
        $sms = $fileSms->getArraySaveUnknownSMS();
        if (count($sms) > 0) {
          $arr = array_merge($arr, $sms);
          $unknown = true;
        }
      }
      // Отослать E-mail если неопознанная СМС добавлена
      if ($unknown) {
        $mail = new Mail();
        $mail->sendAdminNotifySmsUnknownMail($arr);
      }
    }

    /**
     * Вывод результатов работы загрузчика через Notify.
     * Для корректного вывода требуется сброс post запроса, соотвественно
     * вместе с ним сбрасывается вся информация о результате работы модуля.
     * Для вывода информации о работе модуля без сброса post запроса, и
     * соотвественно без использования Notify, необходимо воспользоваться
     * {@see Uploader::getInfo методом} для получения информации в виде массива.
     */
    function printAllNotify () {
      // Выводим сообщения о загруженных файлах
      $notify = new Notify();
      $this->load->printMessagesAsNotify($notify);
      $this->printMessagesAsNotify($notify);
      if (!empty($this->arrayObjSMS)) {
        foreach ($this->arrayObjSMS as $fileSms) {
          // Выводим сообщения об обработанных файлах
          /** @var FileSMS $fileSms **/
          $fileSms->printMessagesAsNotify($notify);
        }
      }
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
     * Подготавливает полную информацию о работе модуля; информацию о файлах,
     * информацию о SMS, итоговую информацию.
     * @return array Вся информация. Формат:
     *  ['files'] - информация о файлах
     *    ['load'] - загружаемые пользователем файлы
     *    ['error'] - список не сохранённых на сервер файлов
     *    ['save'] - список сохранённых на сервер файлов
     *    ['unknown_format'] - файлы с нераспознанным форматом
     *    ['processed'] - обработанные файлы
     *  ['sms'] - информация СМС в каждом отдельном обработанном файле
     *    [x]
     *      ['file'] - информация о данном файле
     *      ['sms_file'] - необработанные данные из файла
     *      ['glued'] - только склеенные СМС
     *      ['unglued'] - только расклеенные СМС
     *      ['separated'] - СМС после расклейки
     *      ['processed'] - СМС после обработки
     *      ['detected_unknown'] - распознанные СМС без информации
     *      ['unknown'] - нераспознанные СМС без информации
     *      ['save'] - сохранённые в БД СМС
     *      ['not_save'] - не сохранённые в БД СМС
     *      ['save_unknown'] - сохранённые в БД нераспознанные СМС
     *      ['comment'] - СМС с сообщениями
     *  ['total'] - объединённая по всем файлам, аналогичная информация
     *    ['sms_file']
     *    ['glued']
     *    ['unglued']
     *    ['separated']
     *    ['processed']
     *    ['detected_unknown']
     *    ['unknown']
     *    ['save']
     *    ['not_save']
     *    ['save_unknown']
     *    ['comment']
     *    ['all_unknown'] - все неопределённые СМС (detected_unknown + unknown)
     */
    function getInfo () {
      // загружаемые пользователем файлы
      $result['files']['load'] = $this->load->getLoadFiles();
      // список незагруженных файлов
      $result['files']['error'] = $this->load->getErrorFiles();
      // список сохранённых файлов
      $result['files']['save'] = $this->files;
      // список файлов с нераспознанным форматом
      $result['files']['unknown_format'] = $this->fileUnknownFormat;
      // инициализация
      $result['files']['processed'] = array();
      $result['sms'] = array();
      $result['total']['sms_file'] = array();
      $result['total']['glued'] = array();
      $result['total']['unglued'] = array();
      $result['total']['separated'] = array();
      $result['total']['processed'] = array();
      $result['total']['detected_unknown'] = array();
      $result['total']['unknown'] = array();
      $result['total']['save'] = array();
      $result['total']['not_save'] = array();
      $result['total']['save_unknown'] = array();
      $result['total']['comment'] = array();
      $result['total']['all_unknown'] = array();
      // информация по загруженным и обработанным файлам с SMS
      if (!empty($this->arrayObjSMS)) {
        foreach ($this->arrayObjSMS as $file) {
          /** @var FileSMS $file * */
          $sms = array();
          // Информация о файле
          $sms['file'] = $file->getFileInfo();
          $result['files']['processed'][] = $sms['file'];
          // СМС из файла
          $sms['sms_file'] = $file->getArrayFromFile();
          $result['total']['sms_file'] = array_merge($result['total']['sms_file'], $sms['sms_file']);
          // Только склеенные СМС
          $sms['glued'] = $file->getArrayGluedSMS();
          $result['total']['glued'] = array_merge($result['total']['glued'], $sms['glued']);
          // Только расклеенные СМС
          $sms['unglued'] = $file->getArrayUngluedSMS();
          $result['total']['unglued'] = array_merge($result['total']['unglued'], $sms['unglued']);
          // СМС из файла расклеенные
          $sms['separated'] = $file->getArraySeparatedSMS();
          $result['total']['separated'] = array_merge($result['total']['separated'], $sms['separated']);
          // Обработанные СМС
          $sms['processed'] = $file->getArrayProcessedSMS();
          $result['total']['processed'] = array_merge($result['total']['processed'], $sms['processed']);
          // Опознанные СМС не содерждащие полезных данных
          $sms['detected_unknown'] = $file->getArrayDetectedUnknownSMS();
          $result['total']['detected_unknown'] = array_merge($result['total']['detected_unknown'], $sms['detected_unknown']);
          // Неопознанные СМС
          $sms['unknown'] = $file->getArrayNotDetectedUnknownSMS();
          $result['total']['unknown'] = array_merge($result['total']['unknown'], $sms['unknown']);
          // Сохранённые в БД обработанные СМС
          $sms['save'] = $file->getArraySaveProcessedSMS();
          $result['total']['save'] = array_merge($result['total']['save'], $sms['save']);
          // Не сохранённые в БД обработанные СМС
          $sms['not_save'] = $file->getArrayNotSaveProcessedSMS();
          $result['total']['not_save'] = array_merge($result['total']['not_save'], $sms['not_save']);
          // Сохранённые неопознанные СМС
          $sms['save_unknown'] = $file->getArraySaveUnknownSMS();
          $result['total']['save_unknown'] = array_merge($result['total']['save_unknown'], $sms['save_unknown']);
          // Сохранённые СМС с комментариями
          $sms['comment'] = $file->getArraySaveProcessedCommentSMS();
          $result['total']['comment'] = array_merge($result['total']['comment'], $sms['comment']);
          $result['sms'][] = $sms;
        }
        // Получить общее количество неопределённых СМС
        $result['total']['all_unknown'] = array_merge($result['total']['detected_unknown'], $result['total']['unknown']);
      }
      return $result;
    }
  }