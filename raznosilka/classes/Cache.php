<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Cache.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Cache служит для управления внутренним кешем Разносилки
 */
class Cache {

  /**
   * Проверка актуальности текущей сессии.
   * @param $uid int ID пользователя Разносилки
   * @return bool Если true, то сессия актуальна
   */
  static function isActualSID ($uid) {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $userSID = $db->getUserSID($uid);
    if ($userSID == session_id()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Удалить данные в кеше которые могли устареть
   * @param $uid int ID пользователя Разносилки
   */
  public static function updateCache ($uid) {
    // Обновить ID текущей сессии
    self::setActualSID($uid);
    // Список устаревающих данных в кеше
    $staleCache = array(
      'purchase_obj'
    );
    // Удалить устаревшие данные
    $regSess = Registry_Session::instance();
    foreach ($staleCache as $item) {
      $regSess->del($item);
    }
  }

  /**
   * Обновить ID текущей сессии
   * @param $uid int ID пользователя Разносилки
   */
  static function setActualSID ($uid) {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $sid = session_id();
    $db->setUserSID($sid, $uid);
  }

  /**
   * Сохраняет и сериализует закупку в сессии
   * @param $purchase Purchase Объект закупки который необходимо сохранить в сессии
   * @param bool $serialize
   */
  static function savePurchase (Purchase $purchase, $serialize = false) {
    $regSess = Registry_Session::instance();
    if ($serialize) {
      $purchase = serialize($purchase);
    }
    $regSess->set('purchase_obj', $purchase, true);
  }

  /**
   * Получить актуальный объект закупки из кэша, если он там есть и при этом соответствует выбранной закупке
   * @return false|Purchase Объект с закупкой
   */
  public static function getPurchaseFromCache () {
    // Получаем объект закупки
    $purchase = self::loadPurchase();
    // Если объект закупки получен
    if ($purchase instanceof Purchase) {
      $purchaseHelper = new PurchaseHelper();
      // Проверка актуальности объекта с закупкой
      if ($purchaseHelper->verifyPurchaseId($purchase)) {
        return $purchase;
      }
    }
    return false;
  }

  /**
   * Загружает и автоматически десериализует закупку из сессии
   * @return false|Purchase Результат операции
   */
  static function loadPurchase () {
    $regSess = Registry_Session::instance();
    $purchase = $regSess->get('purchase_obj');
    if (!is_null($purchase)) {
      if ($purchase instanceof Purchase) {
        return $purchase;
      }
      $result = unserialize($purchase);
      if ($result instanceof Purchase) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Удалить объект с закупкой из кэша
   */
  public static function deletePurchaseCache () {
    $regSess = Registry_Session::instance();
    $regSess->del('purchase_obj');
  }

}