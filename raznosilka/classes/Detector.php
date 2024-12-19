<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Detector.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Detector Модуль работы с нераспознанными SMS
 */
class Detector {
  /**
   * @var ToolsSMS Вспомогательный класс содержащий инструменты для работы с СМС
   */
  protected $tools;
  /**
   * @var DataBase Доступ к методам БД
   */
  private $db;

  // Пейджер
  /**
   * @var Pager Объект для вывода пейджера
   */
  private $pager;
  /**
   * Количество строк показанных за один раз
   */
  const ITEM_ON_PAGE = 50;

  /**
   * Констурктор класса
   */
  function __construct () {
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    $this->tools = new ToolsSMS();
  }

  /**
   * Получение всех записей в из таблицы sms_unknown
   * @return array Массив всех записей в из таблицы sms_unknown
   */
  public function getAllSmsUnknown () {
    return $this->db->getAllSmsUnknown();
  }

  /**
   * Получаение всех нераспознанных SMS для вывода
   * @return array Массив с нераспознанными СМС формата:
   *  ['sms'] - список нераспознанныз СМС
   *    [x] - номер смс
   *      ['sms_unknown_id'] - ID нераспознанной СМС
   *      ['user_id'] - ID пользователя у которого нераспознанна СМС
   *      ['sms_unknown_time'] - Время получения СМС
   *      ['sms_unknown_text'] - Текст сообщения
   *  ['pager'] - пейджер
   *  ['sms_count'] - количество СМС всего
   *  ['count_new'] - количество новых нераспознанных СМС
   */
  public function getViewSmsUnknown () {
    $result = array();
    $smss = $this->db->getAllSmsUnknown();
    if (!empty($smss)) {
      // Пейджер
      $this->pager = new Pager($smss, self::ITEM_ON_PAGE);
      $itemForView = $this->pager->getItemForView();
      $result['pager'] = $this->pager->getHTML();
      $result['sms_count'] = $this->pager->getItemCount();
      $result['count_new'] = $this->db->getCountNewSmsUnknown();
      $arr = array();
      // Вывод СМС
      foreach ($itemForView as $keySms => $sms) {
        $itemForView[$keySms][SMS_UNKNOWN_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($sms[SMS_UNKNOWN_TIME]));
        $itemForView[$keySms]['new'] = ($sms[SMS_UNKNOWN_NEW]) ? 'new' : '';
        $arr[] = $sms[SMS_UNKNOWN_ID];
      }
      // Отметить как просмотренные
      $this->db->readSmsUnknown($arr);
      $result['sms'] = $itemForView;
    }
    return $result;
  }

  /**
   * Удаление выбранных нераспознанных СМС
   * @param array $arr $_POST массив
   * @return false|int
   */
  public function deleteSelectedSmsUnknown ($arr) {
    // Подготовка массива для удаления
    foreach ($arr as $key => $value) {
      if (is_numeric($key)) {
        $result[] = $key;
      }
    }
    // Удаление
    if (!empty($result)) {
      return $this->db->deleteSmsUnknown($result);
    }
    return false;
  }

  /**
   * Повторное распознание выбранных СМС
   * @param array $arr $_POST массив
   * @return false|array Отчёт о распознанных СМС, формата:
   *  ['separated'] - После расклейки
   *  ['glued'] - Только склеенные
   *  ['unglued'] - Только расклеенные
   *  ['processed'] - Распознанные
   *  ['detected_unknown'] - Бесполезные
   *  ['unknown'] - Неопределённые
   *  ['save'] - Сохранённые СМС
   *  ['not_save'] - Повторы СМС
   *  ['comment'] - Сохранённые СМС с сообщениями
   *  ['delete_unknown'] - Удалённые неопознанные СМС
   *  ['save_unknown'] - Сохранённые нераспознанные СМС
   */
  public function detectSelectedSmsUnknown ($arr) {
    if (!empty($arr)) {
      // Подготовка массива с СМС
      foreach ($arr as $key => $value) {
        if (is_numeric($key)) {
          $arr_id[] = $key;
        }
      }
      // Определение
      if (!empty($arr_id)) {
        // Получаем массив с СМС
        $result = $this->db->getSmsUnknownById($arr_id);
        if ($result) {
          // Подготавливаем массив для обработки
          $arr_sms = array();
          foreach ($result as $val) {
            $val[SMS_TIME_SMS] = $val[SMS_UNKNOWN_TIME];
            $arr_sms[] = $val;
          }
          // Разделяем СМС
          $separated = $this->tools->separationGluedSMS($arr_sms);
          // Распознаём СМС
          $processed = $this->tools->processedSMS($separated['separated']);
          // Определяем бесполезные СМС
          $unknown = $this->tools->detectUnknownSMS($processed['unknown']);
          // Сохраняем распознанные
          $save = $this->tools->saveProcessedSMS($processed['processed']);
          // Рассылаем пользователям уведомления о найденной СМС с сообщением
          if (!empty($save['comment'])) {
            foreach ($save['comment'] as $sms) {
              $this->tools->sendMessage($sms);
            }
          }
          // Удаляем выделенные пользователем нераспознанные СМС
          $this->db->deleteSmsUnknown($arr_id);
          // Сохраняем оставшиеся нераспознанные СМС
          $saveUnknown = $this->tools->saveUnknownSMS($unknown['not_detected']);
          // Результат работы скрипта
          $result = array();
          $result['separated'] = $separated['separated']; // После расклейки
          $result['glued'] = $separated['glued']; // Только склеенные
          $result['unglued'] = $separated['unglued']; // Только расклеенные
          $result['processed'] = $processed['processed']; // Распознанные
          $result['detected_unknown'] = $unknown['detected']; // Бесполезные
          $result['unknown'] = $unknown['not_detected']; // Неопределённые
          $result['save'] = $save['save']; // Сохранённые СМС
          $result['not_save'] = $save['not_save']; // Повторы СМС
          $result['comment'] = $save['comment']; // Сохранённые СМС с сообщениями
          $result['delete_unknown'] = $arr_sms; // Удалённые неопознанные СМС
          $result['save_unknown'] = $saveUnknown['save']; // Сохранённые нераспознанные СМС
          return $result;
        }
      }
    }
    return false;
  }

  /**
   * Повторное распознание всех СМС
   * @return false|array Отчёт о распознанных СМС, формата:
   *  ['separated'] - После расклейки
   *  ['glued'] - Только склеенные
   *  ['unglued'] - Только расклеенные
   *  ['processed'] - Распознанные
   *  ['detected_unknown'] - Бесполезные
   *  ['unknown'] - Неопределённые
   *  ['save'] - Сохранённые СМС
   *  ['not_save'] - Повторы СМС
   *  ['comment'] - Сохранённые СМС с сообщениями
   *  ['delete_unknown'] - Удалённые неопознанные СМС
   *  ['save_unknown'] - Сохранённые нераспознанные СМС
   */
  public function detectAllSmsUnknown () {
    // Получаем массив с СМС
    $result = $this->db->getAllSmsUnknown();
    if ($result) {
      // Подготавливаем массив для обработки
      $arr_sms = array();
      foreach ($result as $val) {
        $val[SMS_TIME_SMS] = $val[SMS_UNKNOWN_TIME];
        $arr_sms[] = $val;
      }
      // Разделяем СМС
      $separated = $this->tools->separationGluedSMS($arr_sms);
      // Распознаём СМС
      $processed = $this->tools->processedSMS($separated['separated']);
      // Определяем бесполезные СМС
      $unknown = $this->tools->detectUnknownSMS($processed['unknown']);
      // Сохраняем распознанные
      $save = $this->tools->saveProcessedSMS($processed['processed']);
      // Рассылаем пользователям уведомления о найденной СМС с сообщением
      if (!empty($save['comment'])) {
        foreach ($save['comment'] as $sms) {
          $this->tools->sendMessage($sms);
        }
      }
      // Удаляем все нераспознанные СМС
      $this->db->delAllSmsUnknown();
      // Сохраняем оставшиеся нераспознанные СМС
      $saveUnknown = $this->tools->saveUnknownSMS($unknown['not_detected']);
      // Результат работы скрипта
      $result = array();
      $result['separated'] = $separated['separated']; // После расклейки
      $result['glued'] = $separated['glued']; // Только склеенные
      $result['unglued'] = $separated['unglued']; // Только расклеенные
      $result['processed'] = $processed['processed']; // Распознанные
      $result['detected_unknown'] = $unknown['detected']; // Бесполезные
      $result['unknown'] = $unknown['not_detected']; // Неопределённые
      $result['save'] = $save['save']; // Сохранённые СМС
      $result['not_save'] = $save['not_save']; // Повторы СМС
      $result['comment'] = $save['comment']; // Сохранённые СМС с сообщениями
      $result['delete_unknown'] = $arr_sms; // Удалённые неопознанные СМС
      $result['save_unknown'] = $saveUnknown['save']; // Сохранённые нераспознанные СМС
      return $result;
    }
    return false;
  }

}