<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\CopyTransContacts4.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class CopyTransContacts4 Класс для распознавания SMS полученных путём экспорта из iPhone
 * при помощи программы CopyTrans Contacts 4.
 */
class FileSMS_CopyTransContacts4 extends FileSMS {
  /**
   * Получение SMS из файла.
   * Структкра файла известна заранее:
   * - 0 - номер с которого пришла SMS
   * - 1 - время получения SMS
   * - 3 - Тело SMS
   * @return array
   */
  function SMSFromFile () {
    $arr = array();
    $convert = self::convertFileEncode($this->file['tmp_name'], 'UTF-16');
    $csv = self::StringToArrayCsv($convert, ';');
    foreach ($csv as $data) {
      // Телефонный номер с которого приходят SMS от Сбербанка
      if ($data[0] == 900) {
        $dateTime = new DateTime();
        $item[SMS_TIME_SMS] = $dateTime->setTimestamp(strtotime($data[1]))->format('Y-m-d H:i:s');
        $item[SMS_UNKNOWN_TEXT] = $data[3];
        $arr[] = $item;
      }
    }
    return $arr;
  }
}