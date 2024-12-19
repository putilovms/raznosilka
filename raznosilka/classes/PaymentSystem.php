<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Payment.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Payment Абстрактный класс отвечающий за платёжную систему
 */
abstract class PaymentSystem {

  /**
   * @var User Объект пользователя
   */
  protected $user;
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  protected $db;
  /**
   * @var string Путь к шаблону с формой для оплаты
   */
  protected $tplPath;
  /**
   * @var Logs Объект для ведения логов
   */
  protected $log;
  /**
   * @var Registry_Request Реестр
   */
  protected $regReq;
  /**
   * @var string Режим работы сайта СП
   */
  protected $mode;

  /**
   * Конструктор класса
   */
  function __construct () {
    $this->regReq = Registry_Request::instance();
    $this->user = $this->regReq->get('user');
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    $this->tplPath = $_SERVER['DOCUMENT_ROOT'] . $this->regReq->get('tpl_path') . "/payment_system";
    $this->log = new Logs();
    $this->mode = $this->regReq->get('mode');
  }

  /**
   * Получить список доступных на данный момент платёжных систем
   * @return array Список доступных на данный момент платёжных систем
   */
  static function getListPaymentSystems () {
    $list = array(
      ORDER_YANDEX_KASSA => 'Яндекс.Касса'
    );
    return $list;
  }

  /**
   * Получить объект для работы с платёжной системой
   * @param null|int $payment Явно заданная платёжная система
   * @return bool|PaymentSystem_YandexKassa Возвращает объект для работы с текущей платёжной системой
   * @throws Exception
   */
  static function getPaymentSystem ($payment = null) {
    $result = false;
    if (is_null($payment)) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $payment = $db->getSetting('payment_system');
      $payment = (int)$payment[SETTINGS_VALUE];
    }
    if ($payment !== false) {
      switch ($payment) {
        case ORDER_YANDEX_KASSA :
          $result = new PaymentSystem_YandexKassa();
          break;
      }
    }
    return $result;
  }

  /**
   * Получить HTML код формы для платежа из шаблона
   * @return string HTML код формы для платежа из шаблона
   */
  function getPaymentFormHTML () {
    $var = $this->getPaymentFormInfo();
    $path = $this->getPathToForm();
    ob_start();
    require($path);
    $html = ob_get_clean();
    return $html;
  }

  /**
   * Добавить пользователю заказ на услугу
   * @param $userId int ID пользователя
   * @param $paymentId int ID данных о платеже
   * @param $day int Количество дней оказания услуги
   * @return false|int ID добавленного заказа на услугу
   */
  abstract function createOrder ($userId, $paymentId, $day);

  /**
   * Получить данные для вывода платёжной формы
   * @return array Массив с информацией для вывода платёжной формы
   */
  abstract function getPaymentFormInfo ();

  /**
   * Получить путь к шаблону с формой для платежа
   * @return string Путь к шаблону с формой для платежа
   */
  abstract function getPathToForm ();

  /**
   * Сохранить данные о платеже
   * @param $data array Данные для сохранения
   * @return int ID данных о платеже
   */
  abstract function savePayingData ($data);

  /**
   * Обработать запрос от Яндекс.Кассы
   * @param $action string "checkOrder" или "paymentAviso"
   * @param $request array Данные запроса
   * @param bool $test Если запрос в режиме тестирования
   */
  abstract function processRequest ($action, $request, $test = false);

  /**
   * Получить HTML код с данными о платеже
   * @param $paymentId int ID платежа
   * @return string HTML код с данными о платеже
   */
  function getPaymentInfoHTML ($paymentId) {
    $var = $this->getPaymentInfo($paymentId);
    $path = $this->getPathToInfo();
    ob_start();
    require($path);
    $html = ob_get_clean();
    return $html;
  }

  /**
   * Получить данные о платеже
   * @param $paymentId int ID платежа
   * @return array Данные о платеже
   */
  abstract function getPaymentInfo ($paymentId);

  /**
   * Получить путь к шаблону с таблицей данных о платеже
   * @return string Путь к шаблону с таблицей данных о платеже
   */
  abstract function getPathToInfo ();

  /**
   * Получить сумму полученную сервисом от платёжной системы
   * @return float Сумма полученная сервисом от платёжной системы
   */
  abstract function getSumReceived();

  /**
   * Получить сумму заплаченную пользователями платёжной системе
   * @return float Сумма заплаченная пользователями платёжной системе
   */
  abstract function getSumPaid();

  /**
   * Получить сумму полученную сервисом от платёжной системы от определённого пользователя
   * @param $uid int ID пользователя
   * @return float Сумма полученная сервисом от платёжной системы от определённого пользователя
   */
  abstract function getUserSumReceived($uid);

  /**
   * Получить сумму заплаченную определённым пользователем платёжной системе
   * @param $uid int ID пользователя
   * @return float Сумма заплаченная определённым пользователем платёжной системе
   */
  abstract function getUserSumPaid($uid);

  /**
   * Получить количество записей в таблице для текущей  платёжной системы
   * @return array Массив с именем таблице и количеством записей, формата:
   *  ['name'] - имя таблицы
   *  ['count'] - число записей
   */
  abstract function getCountRecordsPaymentSystem();

  /**
   * Получить информацию о платёжной системе
   * @return array массив с инфомрацией о платёжной системе, формата:
   *  [x] - Параметр
   *    ['name'] - имя параметра для вывода
   *    ['value'] - значение параметра для вывода
   */
  abstract function getAdminInfoForView();

}