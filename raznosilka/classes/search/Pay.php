<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Pay.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Search_Pay Дочерний класс Search отвечающий за вывод поиска СМС для платежа
 */
class Search_Pay extends Search {
  /**
   * @var Registry_Session реестр сессий
   */
  private $regSess;
  /**
   * @var Purchase объект с закупкой
   */
  private $purchase;
  /**
   * @var Lot Лот для которого ищется SMS
   */
  private $lot;
  /**
   * @var Pay Платёж для которого ищется SMS
   */
  private $pay;
  /**
   * @var int Номер лота для которого ищется SMS
   */
  private $lotNumber;
  /**
   * @var int Номер платежа для которого ищется SMS
   */
  private $payNumber;

  /**
   * Конструктор класса
   */
  function __construct () {  // переопределить
    // Получение объекта закупки из кэша
    $this->purchase = Cache::getPurchaseFromCache();
    // Получение реестра сессий
    $this->regSess = Registry_Session::instance();
  }

  /**
   * Инициализация поиска
   * @param $cmd array Значения полей фильтра
   * @return bool Результат выполнения инициализации
   */
  public function init (array $cmd) { // переопределить
    $result = false;
    // Если объект закупки получен
    if ($this->purchase instanceof Purchase) {
      // Если поступившие данные прошли проверку
      if ($this->checkLotAndPay($cmd)) {
        // Инициализация значений полей для фильтра поиска
        $this->setFormValue($cmd);
        // Получение найденных СМС для вывода
        $this->getSearchSmsForView();
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Проверить на возможность поиска СМС для введённого пользователем лота и платежа
   * @param $cmd array Значения выбранных пользователем полей
   * @return bool Результат проверки
   */
  function checkLotAndPay ($cmd) { // не нужно
    $result = true;
    // Проверка номера лота
    if (!isset($cmd['l'])) {
      return false;
    }
    if (!Kit::isInt($cmd['l'])) {
      return false;
    }
    // Проверка номера платежа
    if (!isset($cmd['p'])) {
      return false;
    }
    if (!Kit::isInt($cmd['p'])) {
      return false;
    }
    $this->lotNumber = $cmd['l'];
    $this->payNumber = $cmd['p'];
    /** @var Lot[] $lots */
    $lots = $this->purchase->getLots();
    // Если в закупке нет лотов
    if (empty($lots)) {
      return false;
    }
    // Если нет запрашиваемого лота
    if (!isset($lots[$this->lotNumber])) {
      return false;
    }
    $this->lot = $lots[$this->lotNumber];
    /** @var Pay[] $pays */
    $pays = $this->lot->getPays();
    // Если в лоте нет платежа
    if (empty($pays)) {
      return false;
    }
    // Если нет запрашиваемого платежа
    if (!isset($pays[$this->payNumber])) {
      return false;
    }
    $this->pay = $pays[$this->payNumber];
    // Если платёж не доступен для проставления
    if ($this->pay->isError() or $this->pay->isFilling()) {
      return false;
    }
    return $result;
  }

  /**
   * Получить дополнительную информацию для вывода
   * @return array Дополнительная информация для вывода
   *  - ['url'] - URL к закупке
   *  - [PURCHASE_NAME] - Название закупки
   *  - ['lot'] - информация о заказе
   *    - ['number'] - номер лота
   *    - [USER_PURCHASE_NAME] - Имя участника закупки
   *    - [USER_PURCHASE_NICK] - Ник участника закупки
   *    - ['url'] - URL к профилю участника закупки
   *    - ['pay'] - информация о платеже
   *      - ['number'] - номер платежа
   *      - [PAY_TIME] - время платежа
   *      - [PAY_SUM] - сумма платежа, руб
   *      - [PAY_CARD_PAYER] - номер карты плательщика
   *    - ['comment_org'] - Комментарий организатора
   *    - ['comments'] - Массив с комментариями участника закупки
   *      - [x] - комментарий участника
   *  - ['redirect_url'] - URL для редиректа
   */
  function getOptionalView () {
    $result = array();
    // Информация о закупке
    $result['url'] = $this->purchase->getPurchaseUrl();
    $result[PURCHASE_NAME] = $this->purchase->getPurchaseName();
    // Информация о участнике закупки
    $result['lot']['number'] = $this->lotNumber;
    $userPurchase = $this->lot->getUserPurchase();
    $result['lot'][USER_PURCHASE_NAME] = $userPurchase->getFio();
    $result['lot'][USER_PURCHASE_NICK] = $userPurchase->getNick();
    $result['lot']['url'] = $userPurchase->getUrl();
    // Информация о платеже
    $result['lot']['pay']['number'] = $this->payNumber;
    $result['lot']['pay'][PAY_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($this->pay->getTimePay()));
    $result['lot']['pay'][PAY_CREATED] = strftime('%H:%M %d.%m.%Y', strtotime($this->pay->getTimeCreatedPay()));
    $result['lot']['pay'][PAY_SUM] = number_format($this->pay->getSum(), 2, ',', '');
    $result['lot']['pay'][PAY_CARD_PAYER] = sprintf("%04d", $this->pay->getCard());
    // Комментарий к оплате
    $result['lot']['comment_pay'] = $this->lot->getCommentPay();
    // Комментарий организатора
    $result['lot']['comment_org'] = $this->lot->getCommentOrg();
    // Комментарии участника закупки
    $orders = $this->lot->getOrders();
    if (!empty($orders)) {
      /** @var Order $order */
      foreach ($orders as $order) {
        $comment = $order->getComment();
        if (!empty($comment)) {
          $result['lot']['comments'][] = $comment;
        }
      }
    }
    // URL для редиректа
    $result['redirect_url'] = self::getRedirectUrl();
    return $result;
  }

  /**
   * Получить URL для редиректа
   * @return string
   */
  static function getRedirectUrl(){
    $regSess = Registry_Session::instance();
    $url = $regSess->get('editorPurchaseURL');
    $result = (!empty($url)) ? $url : URL::to('purchase', array('view' => 'not_filling')); // todo убрать повторение адресв URL по умолчанию (page.tpl и service.tpl)
    return $result;
  }

  /**
   * Получить данные для клиентской части
   * @return string Строка с JSON объектом в переменной JSON_REQUEST, для использования в JS, формата:
   *  - ['search']
   *    - ['cmd'] - команда для проставления СМС
   *    - ['redirect_url'] - URL для редиректа
   */
  public function getJsonSearch () {
    $result = array();
    $result['search']['cmd'] = $this->getRequestFilling();
    $result['search']['redirect_url'] = self::getRedirectUrl();
    $result = json_encode($result);
    $result = 'var ' . PAGE_DATA_JS . ' = ' . $result . ';';
    return $result;

  }

  /**
   * Получить команду для проставления платежа выбранной СМС
   */
  private function getRequestFilling () {
    $result = Command::CMD_SEARCH_FILLING;
    $result = sprintf($result, $this->lotNumber, $this->payNumber);
    return $result;
  }
  
}