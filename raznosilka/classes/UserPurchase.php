<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\UserPurchase.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class UserPurchase Информация об участнике закупки
   */
  class UserPurchase {
    /**
     * @var int ID участника закупки
     */
    private $userPurchaseId;
    /**
     * @var string ФИО участника закупки
     */
    private $fio;
    /**
     * @var string Ник участника закупки
     */
    private $nick;
    /**
     * @var string Путь к личному кабинету участника закупки
     */
    private $url;

    /**
     * Создание объекта участника закупки
     * @param array $user Данные о пользователе полученные из сайта СП, формата:
     * - [USER_PURCHASE_ID] - ID участника на сайте
     * - [USER_PURCHASE_NAME] - ФИО участника
     * - [USER_PURCHASE_NICK] - Ник участника
     * - ['url'] - URL к профилю участника закупки
     * @param bool $test Для тестирования
     * @throws Exception
     */
    function __construct (array $user, $test = false) {
      if (!isset($user[USER_PURCHASE_ID]) or !isset($user[USER_PURCHASE_NAME]) or !isset($user[USER_PURCHASE_NICK]) or !isset($user['url'])) throw new Exception();
      $this->userPurchaseId = $user[USER_PURCHASE_ID];
      $this->fio = $user[USER_PURCHASE_NAME];
      $this->nick = $user[USER_PURCHASE_NICK];
      $this->url = $user['url'];
      // Добавляем участника закупки в БД
      if (!$test) {
        $this->addUserPurchaseToDb();
      }
    }

    /**
     * Получить ID участника закупки
     * @return int ID участника закупки
     */
    function getUserPurchaseId(){
      return $this->userPurchaseId;
    }

    /**
     * Получить ФИО участника закупки
     * @return string ФИО участника закупки
     */
    function getFio(){
      return $this->fio;
    }

    /**
     * Получить ник участника закупки
     * @return string Ник участника закупки
     */
    function getNick(){
      return $this->nick;
    }

    /**
     * Получить URL к личному кабинету участника закупки
     * @return string URL к личному кабинету участника закупки
     */
    function getUrl(){
      return $this->url;
    }

    /**
     * Добавить данного участника закупки в БД или обновить данные о нём
     */
    function addUserPurchaseToDb () {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      $spId = $user->getSpId();
      $result = $db->getUserPurchase($this->userPurchaseId, $spId);
      // Если такого участника ещё нет в БД, то добавляем его
      if ($result === false) {
        $db->addUserPurchase($this->userPurchaseId, $this->fio, $this->nick, $spId);
      } else {
        // Если ФИО или ник участника изменились, то обновляем их
        if (($result[USER_PURCHASE_NAME] != $this->fio) or ($result[USER_PURCHASE_NICK] != $this->nick)){
          $db->updateUserPurchase($this->userPurchaseId, $this->fio, $this->nick, $spId);
        }
      }
    }



  }