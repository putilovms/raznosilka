<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Filling.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Filling Модуль разнесения платежей
 */
class Filling {
  /**
   * @var Purchase Объект проанализированной закупки
   */
  private $purchase;
  /**
   * @var Site Содержит объект Site для доступа к данным сайта СП
   */
  private $site;
  /**
   * @var int Тип запроса к сайту СП, выбранный пользователем
   */
  private $userRequest;
  /**
   * @var PurchaseHelper Получение объекта выбранной закупки
   */
  private $purchaseHelper;
  /**
   * @var array Данные для запросов для проставления оплат.
   */
  private $requestData = array();


  /**
   * Конструктор класса
   */
  function __construct () {
    $this->purchaseHelper = new PurchaseHelper();
    // Получение объекта закупки
    $this->purchase = Cache::getPurchaseFromCache();
    $this->site = Site::getSite();
    // Получаем тип запроса к сайту СП
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    $this->userRequest = (int)$userInfo[USER_REQUEST];
  }

  /**
   * Назначить платежу SMS на основе выбора пользователя
   * @param $post array POST массив с выбранными SMS
   */
  public function setSelectedSms (array $post) {
    $result = array();
    // Преобразование POST массива
    foreach ($post as $key => $value) {
      if (preg_match('|^(\d+)-(\d+)|', $key, $match)) {
        $result[$match[1]][$match[2]] = $value;
      }
    }
    // Добавляем данные о выборанных пользователем СМС в закупку
    if (!empty($result)) {
      /** @var Lot[] $lots */
      $lots = $this->purchase->getLots();
      foreach ($result as $keyLot => $lot) {
        /** @var Pay[] $pays */
        $pays = $lots[$keyLot]->getPays();
        foreach ($lot as $keyPay => $keySms) {
          if (is_numeric($keySms)) {
            $pays[$keyPay]->setSelectSms($keySms);
          }
        }
      }
    }
  }

  /**
   * Получить массив закупки с выбранными для проставления платежами подготовленный для вывода
   * @return array Массив закупки с выбранными для проставления платежами подготовленный для вывода формата:
   *  ['url'] - URL к закупке
   *  [PURCHASE_NAME] - имя закупки
   *  ['count_total_found_money'] - Общее количество найденных денег Разносилкой
   *  ['lots'] - Заказы
   *    [x] - номер заказа
   *      ['user_purchase_name'] - ФИО участника закупки
   *      ['user_purchase_nick'] - Ник участника закупки
   *      ['url'] - URL к профилю УЗ
   *      ['total_filling'] - Сумма к проставлению
   *      ['total'] - Всего УЗ должен внести
   *      ['total_found'] - Найдено до этого
   */
  public function getView () {
    // Инициализация
    $result = array();
    if ($this->purchase instanceof Purchase) {
      // Информация о закупке
      $result['url'] = $this->purchase->getPurchaseUrl();
      $purchaseId = $this->purchase->getPurchaseId();
      $result[PURCHASE_NAME] = $this->purchase->getPurchaseName();
      // Сводная информация
      // $result['count_active_lots'] = $this->purchase->getCountActiveLots();
      // $result['count_active_orders'] = $this->purchase->getCountActiveOrders();
      // $countTotalMoney = $this->purchase->getCountTotalMoney();
      // $result['count_total_money'] = number_format($countTotalMoney, 2, ',', '');
      $countTotalFoundMoney = $this->purchase->getCountTotalFoundMoney();
      $result['count_total_found_money'] = number_format($countTotalFoundMoney, 2, ',', '');
      // Получение неразнесённых платежей имеющих выбранную СМС для проставления
      $lots = $this->purchase->getLots();
      if (!empty($lots)) {
        /** @var Lot $lot */
        foreach ($lots as $keyLot => $lot) {
          // Если в заказе есть платежи для разнесения
          if ($lot->isForFilling()) {
            // Информация о участнике закупки
            $userPurchase = $lot->getUserPurchase();
            $result['lots'][$keyLot][USER_PURCHASE_NAME] = $userPurchase->getFio();
            $result['lots'][$keyLot][USER_PURCHASE_NICK] = $userPurchase->getNick();
            $result['lots'][$keyLot]['url'] = $userPurchase->getUrl();
            // Получение суммы к проставлению
            $totalFilling = $lot->getTotalForFilling();
            $result['lots'][$keyLot]['total_filling'] = number_format($totalFilling, 2, ',', '');
            // Информация о заказе
            $total = $lot->getTotal();
            $result['lots'][$keyLot]['total'] = number_format($total, 2, ',', '');
            $totalFound = $lot->getTotalFound();
            $result['lots'][$keyLot]['total_found'] = number_format($totalFound, 2, ',', '');
            // Добавляем команду для проставления платежа
            $userPurchaseId = $userPurchase->getUserPurchaseId();
            $this->requestData['filling'][$keyLot] = $this->getRequestInfoFilling($keyLot, $purchaseId, $userPurchaseId, $totalFilling);
          }
        }
      }
      $this->requestData['checkTotal'] = $this->getRequestInfoCheckTotal();
    }
    return $result;
  }

  /**
   * Получить информацию для запроса к сайту СП для проставления оплаты
   * @param $keyLot int Номер лота
   * @param $purchaseId int ID выбранной закупки
   * @param $userPurchaseId int ID участника закупки
   * @param $fillingSum float Сумма к проставлению
   * @return array Информация для запроса к сайту СП для проставления оплаты, формата:
   *  ['auto'] - в зависимости от типа запроса к сайту СП:
   *    - строка с командой к сервису для автоматического проставления оплаты
   *    - @see Site::getRequestInfoFilling()
   *  ['manual'] - строка с командой к сервису для ручного проставления оплаты
   */
  private function getRequestInfoFilling ($keyLot, $purchaseId, $userPurchaseId, $fillingSum) {
    $result = array();
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        $result['auto'] = $this->site->getRequestInfoFilling($keyLot, $purchaseId, $userPurchaseId, $fillingSum);
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        $result['auto'] =  Command::CMD_AUTO_FILLING . $keyLot;
        break;
      }
    }
    $result['manual'] = Command::CMD_MANUAL_FILLING . $keyLot;
    return $result;
  }

  /**
   * Получить массив команд для проставления оплат
   * @return string Строка с массивом команд для проставления оплат
   */
  public function getJsonRequestData () {
    $result = json_encode($this->requestData);
    $result = 'var ' . REQUEST_DATA_JS . ' = ' . $result . ';';
    return $result;
  }

  /**
   * Получить информацию для запроса к сайту СП для проверки общей суммы
   * @return array|string Информацию для запроса к сайту СП для проставления оплаты
   */
  private function getRequestInfoCheckTotal () {
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        $result = $this->purchaseHelper->getRequestInfoCheckTotal();
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        $result =  Command::CMD_CHECK_TOTAL;
        break;
      }
    }
    return $result;
  }

}