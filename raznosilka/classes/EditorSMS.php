<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\EditorSMS.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class EditorSMS Редактор СМС
 */
class EditorSMS {

  /**
   * Сделать возврат SMS
   * @param $idSms int ID SMS
   * @return bool Результат попытки возврата
   */
  public function setReturnSMS ($idSms) {
    // Инициализация
    $idSms = (int)$idSms;
    // Отметить SMS как возвращённую
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $user = Registry_Request::instance()->get('user');
    $idUser = $user->getUserId();
    $result = $db->setReturnSms($idUser, $idSms);
    return $result;
  }

  /**
   * Отменить возврат SMS
   * @param $idSms int ID SMS
   * @return bool Результат отмены возврата
   */
  public function delReturnSMS ($idSms) {
    // Инициализация
    $idSms = (int)$idSms;
    // Отметить SMS как возвращённую
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $user = Registry_Request::instance()->get('user');
    $idUser = $user->getUserId();
    $result = $db->delReturnSms($idUser, $idSms);
    return $result;
  }
}