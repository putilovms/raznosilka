<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\PurchaseHelper.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class PurchaseHelper Содержит методы для работы с закупками
 */
class PurchaseHelper {

  /**
   * Содержит объект Site для доступа к данным сайта СП
   * @var Site
   */
  private $site;
  /**
   * Содержит реестр сессий
   * @var Registry_Session
   */
  private $regSess;
  /**
   * Текущий пользователь
   * @var User
   */
  private $user;
  /**
   * Содержит объект досутпа к БД
   * @var DataBase
   */
  private $db;

  /**
   * Конструктор класса
   */
  function __construct () {
    $this->site = Site::getSite();
    $this->regSess = Registry_Session::instance();
    $this->user = Registry_Request::instance()->get('user');
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
  }

  /**
   * Записывается выбранная пользователем закупка в реестр сессий
   * @param $purchaseId int ID закупка
   */
  public function setPurchase ($purchaseId) {
    if (is_int($purchaseId) and !empty($purchaseId)) {
      // Существует ли выбранная закупка
      if ($this->checkSelectPurchase($purchaseId)) {
        // Если существует, записываем её ID в реестр сессий
        $this->regSess->set('purchase', $purchaseId, true);
      } else {
        // Если не существует, очищаем реестр сессий
        $this->regSess->del('purchase');
      }
    }
  }

  /**
   * Проверяет на существование выбранной закупки по её ID
   * @param $purchaseId int ID закупки
   * @return bool Результат проверки
   */
  public function checkSelectPurchase ($purchaseId) {
    $result = false;
    $spId = $this->user->getSpId();
    $userId = $this->user->getUserId();
    $purchase = $this->db->getPurchase($purchaseId, $userId, $spId);
    // Если закупка найдена в БД
    if ($purchase !== false) {
      $result = true;
    }
    return $result;
  }

  /**
   * Добавляет или обновляет закупку в БД если это необходимо
   * @param $purchaseName string Название закупки
   * @param $purchaseId int ID закупки
   * @param $payTo string Дата до которой УЗ должны оплатить
   */
  function addPurchase ($purchaseName, $purchaseId, $payTo) {
    $spId = $this->user->getSpId();
    $userId = $this->user->getUserId();
    $purchase = $this->db->getPurchase($purchaseId, $userId, $spId);
    if ($purchase === false) {
      // Если закупка не найдена, то добавляем
      $this->db->addPurchase($purchaseName, $purchaseId, $userId, $spId, $payTo);
    } else {
      // Если закупка существует
      if (($purchase[PURCHASE_NAME] != $purchaseName) or ($purchase[PURCHASE_PAY_TO] != $payTo)) {
        // Если имя закупки изменено, то обновляем его
        $this->db->updatePurchase($purchaseName, $purchaseId, $userId, $spId, $payTo);
      }
    }
  }

  /**
   * Получает данные о выбранной закупке
   * @return array|false Данные о выбранной закупке
   * - array - если закупка выбрана:
   *  - ['purchase_id'] - ID Закупки
   *  - ['user_id'] - ID пользователя
   *  - ['purchase_name'] - Имя закупки
   *  - ['sp_id'] - ID сайта СП
   *  - ['url'] - URL закупки
   * - false - если закупка не выбрана
   */
  static function getSelectPurchaseInfo () {
    if (PurchaseHelper::isSelect()) {
      // Инициализация
      $regSess = Registry_Session::instance();
      $site = Site::getSite();
      $user = Registry_Request::instance()->get('user');
      $db = new DataBase(Registry_Request::instance()->get('db'));
      // Получения данных
      $purchaseId = $regSess->get('purchase');
      $spId = $user->getSpId();
      $userId = $user->getUserId();
      $purchase = $db->getPurchase($purchaseId, $userId, $spId);
      $purchase['url'] = $site->getPurchaseURL($purchaseId);
      return $purchase;
    }
    return false;
  }

  /**
   * Проверяет, выбрана ли закупка
   * @return bool Выбрана ли закупка
   */
  static function isSelect () {
    $regSess = Registry_Session::instance();
    $purchase = $regSess->get('purchase');
    if (is_null($purchase)) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Получить ID выбранной закупки
   * @return int|null ID выбранной закупки
   */
  function getSelectPurchaseId () {
    return $this->regSess->get('purchase');
  }

  /**
   * Получает список закупок текущего пользователя сохранённые в БД
   * @param $filter string Строка для поиска закупки
   * @return array|false Массив с закупками пользователя
   */
  public function getListPurchaseFromService ($filter) {
    $spId = $this->user->getSpId();
    $userId = $this->user->getUserId();
    $list = $this->db->getAllPurchaseOfUser($userId, $spId, $filter);
    return $list;
  }

  /**
   * Проверка соответствия ID выбранной закупки с той закупкой которая передана
   * @param Purchase $purchase Объект закупки, для которой происходит проверка
   * @return bool
   */
  function verifyPurchaseId (Purchase $purchase) {
    if ($this->isSelect()) {
      if ($purchase->getPurchaseId() == $this->getSelectPurchaseId()) {
        return true;
      }
    }
    return false;
  }

  /**
   * Получение обработанного массива закупки, которая выбрана пользователем
   * Коды ошибок:
   *  ERROR_ACCESS - Нет доступа к выбранной странице
   *  ERROR_PAGE - Не удалось получить страницу
   *  ERROR_DATA - Не удалось получить данные
   *  PURCHASE_NOT_SELECT - Закупка не выбрана
   * @return int|Purchase Объект содержащий закупку или код ошибки
   */
  function getSelectPurchase () { // todo устарела
    if (!$this->isSelect()) {
      return PURCHASE_NOT_SELECT;
    }
    $purchaseId = $this->regSess->get('purchase');
    $purchaseData = $this->site->getPurchaseFromSite($purchaseId);
    // Если возвращена ошибка
    if (!is_array($purchaseData)) {
      return $purchaseData;
    }
    $purchase = new Purchase($purchaseData);
    return $purchase;
  }

  /**
   * Получить информацию для запроса о выбранной закупке
   * @param $cmdService string Команда к сервису
   * @param $typeRequest string Тип запроса к расширению
   * @return array Массив с данными для запроса, формата:
   * @see Site::getRequestInfoPurchase()
   *  ['error'] - код ошибки:
   *    PURCHASE_NOT_SELECT - закупка не выбрана
   */
  public function getRequestInfoSelectPurchase ($cmdService, $typeRequest) {
    // Инициализация
    $purchaseId = $this->regSess->get('purchase');
    $result = $this->site->getRequestInfoPurchase($cmdService, $typeRequest, $purchaseId);
    if (!$this->isSelect()) {
      $result['error'] = PURCHASE_NOT_SELECT;
    }
    return $result;
  }

  /**
   * Получить массив с закупкой с сайта СП
   * @param $info array Информация о выбранной закупке для запроса
   * @return array Массив содержащий данные о закупке, формата:
   *  ['info'] - данные запроса
   *  ['purchase'] - массив с данными закупки
   */
  function getPurchaseFromSite (array $info) {
    // Инициализация
    $result = array();
    $result['info'] = $info;
    $result['purchase'] = array();
    // Получить массив с данными закупки
    $purchaseData = $this->site->getPurchaseFromSite($info);
    // Если ошибка
    if (!is_array($purchaseData)) {
      $result['info']['error'] = $purchaseData;
      return $result;
    }
    $result['purchase'] = $purchaseData;
    return $result;
  }

  /**
   * Массив с данными для запроса страницы "Анализатора"
   * @return array Массив с данными для запроса, формата:
   * @see Site::getRequestInfoPurchase(), либо:
   *  ['error'] - код ошибки:
   *    PURCHASE_NOT_SELECT - закупка не выбрана
   */
  public function getRequestInfoAnalysis () {
    $cmdService = Command::CMD_AUTO_ANALYSIS_ORG;
    $typeRequest = 'autoAnalysis';
    return $this->getRequestInfoSelectPurchase($cmdService, $typeRequest);
  }

  /**
   * Массив с данными для запроса страницы "Редактора закупки"
   * @param $arg string Агрумент определяющий что будет отображаться
   * @return array Массив с данными для запроса, формата:
   * @see Site::getRequestInfoPurchase(), либо:
   *  ['error'] - код ошибки:
   *    PURCHASE_NOT_SELECT - закупка не выбрана
   */
  public function getRequestInfoEditorPurchase ($arg) {
    $cmdService = Command::CMD_EDITOR_PURCHASE;
    $typeRequest = 'editorPurchase';
    $info = $this->getRequestInfoSelectPurchase($cmdService, $typeRequest);
    $info['view'] = $arg;
    $info['cache'] = false;
    return $info;
  }

  /**
   * Массив с данными для запроса страницы "Сверить общую сумму внесённую на сайт"
   * @return array Массив с данными для запроса, формата:
   * @see Site::getRequestInfoPurchase(), либо:
   *  ['error'] - код ошибки:
   *    PURCHASE_NOT_SELECT - закупка не выбрана
   */
  public function getRequestInfoCheckTotal () {
    $cmdService = Command::CMD_CHECK_TOTAL;
    $typeRequest = 'checkTotal';
    return $this->getRequestInfoSelectPurchase($cmdService, $typeRequest);
  }

}