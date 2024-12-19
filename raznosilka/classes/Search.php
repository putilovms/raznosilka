<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Search.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Search Модуль ручного поиска по SMS
 */
abstract class Search {
  /**
   * @var array Массив с найденными объектами СМС
   */
  private $foundSmsForView = array();
  /**
   * @var int Общее число найденных SMS
   */
  private $countFoundSms;

  // Значения полей поиска SMS
  /**
   * @var array Массив содержащий данные из полей фильтра:
   *  - ['dtc'] - bool Поиск по полю даты и времени
   *  - ['dt'] - string Дата и время для поска SMS
   *  - ['fk'] - int Вилка даты для поиска SMS
   *  - ['cc'] - bool Поиск по полю номера карты
   *  - ['c'] - int Номер карты
   *  - ['sc'] - bool Поиск по полю суммы платежа
   *  - ['s'] - float Сумма платежа
   *  - ['fc'] - bool Поиск по полю ФИО
   *  - ['f'] - string ФИО плательщика
   *  - ['mc'] - bool Поиск только SMS с сообщением
   *  - ['rc'] - bool Поиск только возвращённых SMS
   *  - ['t'] - int Поиск по типу СМС (номер карты или ФИО)
   *  - ['st'] - int Поиск по статусу СМС (использованная или нет)
   */
  private $form = array();

  // Пейджер
  /**
   * @var Pager Объект для вывода пейджера
   */
  private $pager;
  /**
   * Количество SMS показанных за один раз
   */
  const ITEM_ON_PAGE = 50;

  /**
   * Инициализация поиска
   * @param $cmd array Значения полей фильтра
   * @return bool Результат выполнения инициализации
   */
  abstract function init (array $cmd);

  /**
   * Получить массив для вывода списка найденных SMS
   * @return array Массив содержащий данные о SMS для вывода, формата:
   *  - ['form'] - значение для полей формы поиска
   *    - ['dtc'] - bool Поиск по полю даты и времени
   *    - ['dt'] - string Дата и время для поска SMS
   *    - ['fk'] - int Вилка даты для поиска SMS
   *    - ['cc'] - bool Поиск по полю номера карты
   *    - ['c'] - int Номер карты
   *    - ['sc'] - bool Поиск по полю суммы платежа
   *    - ['s'] - float Сумма платежа
   *    - ['fc'] - bool Поиск по полю ФИО
   *    - ['f'] - string ФИО плательщика
   *    - ['mc'] - bool Поиск только SMS с сообщением
   *    - ['rc'] - bool Поиск только возвращённых SMS
   *    - ['t'] - int Поиск по типу СМС (номер карты или ФИО)
   *    - ['st'] - int Поиск по статусу СМС (использованная или нет)
   *  - ['sms'] - Список найденных SMS для вывода
   *      - [x] - SMS
   *        - [SMS_ID] - ID SMS
   *        - ['time'] - время платежа указанное в SMS или время получения SMS
   *        - [SMS_SUM_PAY] - Сумма платежа полученная из SMS
   *        - ['payer'] - ФИО или номер карты плательщика
   *        - ['status'] - класс статуса SMS
   *        - [SMS_COMMENT] - комментарий содержащийся в СМС
   *        - ['return'] - возвращена ли СМС
   *        - ['used'] - использованна ли данная SMS для проставления платежа
   *        - [PURCHASE_NAME] - название закупки в которой использована СМС
   *        - ['purchase_url'] - URL к закупке на сайте СП в которой использована СМС
   *        - [USER_PURCHASE_NICK] - Ник участника закупки для которого использована СМС
   *        - ['user_purchase_url'] - URL к профилю участника закупки на сайте СП для которого использована СМС
   *  - ['count_sms'] - Общее количество найденных СМС
   *  - ['pager'] - HTML код пейджера
   */
  public function getView () {
    $result = $this->getOptionalView();
    // Значения для формы поиска
    $result['form']['dtc'] = $this->form['dtc'];
    $result['form']['dt'] = $this->form['dt'];
    $result['form']['fk'] = $this->form['fk'];
    $result['form']['cc'] = $this->form['cc'];
    $result['form']['c'] = $this->form['c'];
    $result['form']['sc'] = $this->form['sc'];
    $result['form']['s'] = $this->form['s'];
    $result['form']['fc'] = $this->form['fc'];
    $result['form']['f'] = $this->form['f'];
    $result['form']['mc'] = $this->form['mc'];
    $result['form']['rc'] = $this->form['rc'];
    $result['form']['t'] = $this->form['t'];
    $result['form']['st'] = $this->form['st'];
    // Найденные SMS
    $result['count_sms'] = $this->countFoundSms;
    if (!empty($this->foundSmsForView)) {
      /** @var SMS $sms */
      foreach ($this->foundSmsForView as $keySms => $sms) {
        $result['sms'][$keySms][SMS_ID] = $sms->getIdSms();
        $result['sms'][$keySms]['time'] = strftime('%H:%M %d.%m.%Y', strtotime($sms->getTime()));
        $smsSumPay = $sms->getSum();
        $result['sms'][$keySms][SMS_SUM_PAY] = number_format($smsSumPay, 2, ',', '');
        $result['sms'][$keySms][SMS_CARD_PAYER] = $sms->getCardForView();
        $result['sms'][$keySms][SMS_FIO] = $sms->getFioForView();
        $smsStatus = $sms->getStatusSMSForSearch();
        // $smsStatus = 2;
        $result['sms'][$keySms]['status'] = $this->statusToClass($smsStatus);
        $result['sms'][$keySms][SMS_COMMENT] = $sms->getComment();
        $result['sms'][$keySms]['return'] = $sms->isReturn();
        $result['sms'][$keySms]['used'] = $sms->isUsed();
        // Получение данных о закупке и участнике
        if ($sms->isUsed()) {
          $info = $this->getInfoSmsPay($sms->getIdPay());
          $result['sms'][$keySms][PURCHASE_NAME] = $info[PURCHASE_NAME];
          $result['sms'][$keySms]['purchase_url'] = $info['purchase_url'];
          $result['sms'][$keySms][USER_PURCHASE_NICK] = $info[USER_PURCHASE_NICK];
          $result['sms'][$keySms]['user_purchase_url'] = $info['user_purchase_url'];
        }
      }
    }
    // Вывод пейджера
    $result['pager'] = $this->pager->getHTML();
    return $result;
  }

  /**
   * Получить дополнительную информацию для вывода
   * @return array Дополнительная информация для вывода
   */
  function getOptionalView () {
    return array();
  }

  /**
   * Преобразовать код статуса в класс соотвествующий коду
   * @param $status int Код статуса заказа, платежа или СМС
   * @return string Класс соотвествующий коду
   */
  function statusToClass ($status) {
    switch ($status) {
      case INACTIVE :
        $result = 'inactive';
        break;
      case WARNING :
        $result = 'warning';
        break;
      case ERROR :
        $result = 'error';
        break;
      default:
        $result = 'normal';
        break;
    }
    return $result;
  }

  /**
   * Обработка значений полей фильтра
   * @param $cmd array Значения выбранных пользователем полей
   */
  function setFormValue ($cmd) {
    // Инициализация
    $dt = time(); // Дата и время
    $fk = 4; // Вилка в днях
    $c = ''; // Номер карты
    $s = ''; // Сумма
    $f = ''; // ФИО
    $t = 0; // Тип
    $st = 0; // Статус
    $dtc = 0; // Поиск по полю даты и времени
    $cc = 0; // Поиск по полю карты
    $sc = 0; // Поиск по полю суммы
    $fc = 0; // Поиск по полю ФИО
    $mc = 0; // Только СМС с сообщением
    $rc = 0; // Только возвращённые СМС
    // Установка значений по умолчанию
    if (empty($cmd['q'])) {
      // Если элемент 'q' пуст, значит надо задать значения по умолчанию
      $dtc = 1; // Поиск по полю даты и времени
      $cc = 0; // Поиск по полю карты
      $sc = 0; // Поиск по полю суммы
      $fc = 0; // Поиск по полю ФИО
      $mc = 0; // Только СМС с сообщением
      $rc = 0; // Только возвращённые СМС
    }
    // Поиск по полю даты и времени
    $this->form['dtc'] = isset($cmd['dtc']) ? $cmd['dtc'] : $dtc;
    // Дата и время
    $datetime = isset($cmd['dt']) ? strtotime($cmd['dt']) : $dt;
    $this->form['dt'] = !empty($datetime) ? strftime('%H:%M %d.%m.%Y', $datetime) : '';
    // Вилка
    $this->form['fk'] = !empty($cmd['fk']) ? (int)$cmd['fk'] : $fk;
    // Поиск по полю карты
    $this->form['cc'] = (isset($cmd['cc'])) ? $cmd['cc'] : $cc;
    // Карта
    if (isset($cmd['c']) and $cmd['c'] === '0') {
      $card = 0;
    } else {
      $card = isset($cmd['c']) ? (int)$cmd['c'] : $c;
      $card = !empty($card) ? $card : '';
    }
    $this->form['c'] = $card;
    // Поиск по полю суммы
    $this->form['sc'] = isset($cmd['sc']) ? $cmd['sc'] : $sc;
    // Сумма
    $sum = isset($cmd['s']) ? (float)$cmd['s'] : $s;
    $this->form['s'] = !empty($sum) ? number_format($sum, 2, '.', '') : '';
    // Поиск по полю ФИО
    $this->form['fc'] = isset($cmd['fc']) ? $cmd['fc'] : $fc;
    // ФИО
    $this->form['f'] = isset($cmd['f']) ? $cmd['f'] : $f; // todo возможна иньекция
    // Только СМС с сообщением
    $this->form['mc'] = isset($cmd['mc']) ? $cmd['mc'] : $mc;
    // Только возвращённые СМС
    $this->form['rc'] = isset($cmd['rc']) ? $cmd['rc'] : $rc;
    // По типу
    $this->form['t'] = isset($cmd['t']) ? (int)$cmd['t'] : $t;
    // По статусу
    $this->form['st'] = isset($cmd['st']) ? (int)$cmd['st'] : $st;
  }

  /**
   * Получение массива для создания запроса для поиска SMS
   * @param $datetime string Дата и время платежа
   * @param $card int Номер карты платежа
   * @param $sum float Сумма платежа
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @return array Массив значений для создания запроса
   */
  static function getQuery ($datetime, $card, $sum, $lotNumber, $payNumber) {
    $query = array();
    $query['dtc'] = 1;
    $query['dt'] = strftime('%H:%M %d.%m.%Y', strtotime($datetime));
    $query['fk'] = 4;
    $query['cc'] = 0;
    $query['c'] = $card;
    $query['sc'] = 1;
    $query['s'] = number_format($sum, 2, '.', '');
    $query['fc'] = 1;
    $query['f'] = '';
    $query['mc'] = 0;
    $query['rc'] = 0;
    $query['t'] = 0;
    $query['st'] = 0;
    $query['l'] = $lotNumber;
    $query['p'] = $payNumber;
    $query['q'] = 1;
    $query[Pager::PAGER_TAG] = 1;
    return $query;
  }

  /**
   * Получение массива для создания пользовательского запроса
   * @param string|null $datetime Дата и время платежа
   * @param int $fork Диапазон вилки
   * @param int|null $card Номер карты платежа
   * @param float|null $sum Сумма платежа
   * @param string|null $fio ФИО
   * @return array Массив значений для создания запроса
   */
  static function getUserQuery ($datetime = null, $fork = 4, $card = null, $sum = null, $fio = null) {
    $query = array();
    $query['dtc'] = (isset($datetime)) ? 1 : 0;
    $query['dt'] = (isset($datetime)) ? strftime('%H:%M %d.%m.%Y', strtotime($datetime)) : '';
    $query['fk'] = $fork;
    $query['cc'] = (isset($card)) ? 1 : 0;
    $query['c'] = (isset($card)) ? $card : '';
    $query['sc'] = (isset($sum)) ? 1 : 0;
    $query['s'] = (isset($sum)) ? number_format($sum, 2, '.', '') : '';
    $query['fc'] = (isset($fio)) ? 1 : 0;
    $query['f'] = (isset($fio)) ? $fio : '';
    $query['mc'] = 0;
    $query['rc'] = 0;
    $query['t'] = 0;
    $query['st'] = 0;
    $query['q'] = 1;
    $query[Pager::PAGER_TAG] = 1;
    return $query;
  }

  /**
   * Поиск SMS по заданным параметрам.
   * Метод подготавливает данные для запроса и записывает полученный результат в свойство $this->sms
   * @return array Массив с найденными SMS
   */
  private function searchSMS () {
    $result = array();
    // Подготовка параметров для поиска
    $param = array();
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $user = Registry_Request::instance()->get('user');
    $param[USER_ID] = $user->getUserId();
    $param[SMS_TIME_SMS] = $this->form['dtc'] ? $this->form['dt'] : "";
    $param['fork'] = $this->form['fk'];
    $param[SMS_CARD_PAYER] = $this->form['cc'] ? $this->form['c'] : "";
    $param[SMS_SUM_PAY] = $this->form['sc'] ? (float)$this->form['s'] : "";
    // Замена * на %
    $fio = preg_replace('#\*+#', '%', $this->form['f']);
    // Убрать повторяющиеся %
    $fio = preg_replace('#\%+#', '%', $fio);
    $param[SMS_FIO] = $this->form['fc'] ? $fio : "";
    $param['message'] = $this->form['mc'];
    $param['return'] = $this->form['rc'];
    $param['type'] = $this->form['t'];
    $param['status'] = $this->form['st'];
    $foundSms = $db->searchSMS($param);
    if ($foundSms !== false) {
      $result = $foundSms;
    }
    return $result;
  }

  /**
   * Получить информацию о платеже в котором использована SMS
   * @param $payId int ID платежа в котором использована SMS
   * @return array Информацию о платеже в котором использована SMS:
   *  - [PURCHASE_NAME] - название закупки
   *  - ['purchase_url'] - URL к закупке на сайте СП
   *  - [USER_PURCHASE_NICK] - Ник участника закупки
   *  - ['user_purchase_url'] - URL к профилю участника закупки на сайте СП
   */
  function getInfoSmsPay ($payId) {
    $result = array();
    // Инициализация
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $pay = $db->getPayById($payId);
    $purchaseId = $pay[PURCHASE_ID];
    $userPurchaseId = $pay[USER_PURCHASE_ID];
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userId = $user->getUserId();
    $spId = $user->getSpId();
    $site = Site::getSite();
    // Получение названия закупки и ссылки на закупку
    $purchase = $db->getPurchase($purchaseId, $userId, $spId);
    $result[PURCHASE_NAME] = $purchase[PURCHASE_NAME];
    $result['purchase_url'] = $site->getPurchaseURL($purchaseId);
    // Получение ника участника закупки и ссылки на его профиль
    $userPurchase = $db->getUserPurchase($userPurchaseId, $spId);
    $result[USER_PURCHASE_NICK] = $userPurchase[USER_PURCHASE_NICK];
    $result['user_purchase_url'] = $site->getUserPurchaseURL($userPurchaseId);
    return $result;
  }

  /**
   * Получить СМС для вывода
   */
  function getSearchSmsForView () {
    // Поиск СМС
    $foundSms = $this->searchSMS();
    $this->pager = new Pager($foundSms, self::ITEM_ON_PAGE);
    $itemForView = $this->pager->getItemForView();
    $this->countFoundSms = $this->pager->getItemCount();
    if (!empty($itemForView)) {
      foreach ($itemForView as $smsKey => $sms) {
        $this->foundSmsForView[$smsKey] = new SMS($sms);
      }
    }
  }
}