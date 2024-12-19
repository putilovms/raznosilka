<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\SMSBackupAndRestore7.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class FileSMS_SMSBackupAndRestore7 Класс для распознавания SMS полученных путём экспорта из телефона
   * на Android при помощи программы SMS Backup & Restore 7.
   */
  class FileSMS_SMSBackupAndRestore7 extends FileSMS {

    /**
     * Получение SMS из файла XML.
     * Структкра файла известна заранее:
     * Тег SMS в котором содержатся аттрибуты с информацией:
     * - address - отправитель (может содержать буквы)
     * - date - время получения SMS
     * - body - Тело SMS
     * @return array
     */
    function SMSFromFile () {
      $xml = simplexml_load_file($this->file['tmp_name']);
      // var_dump($xml);
      $sms_arr = array();
      foreach ($xml->sms as $val) {
        // var_dump($val);
        /** @var SimpleXMLElement $val */
        $number = (string)$val->attributes()->address;
        if ($number == '900') {
          // Подготовка даты
          $date = (float)$val->attributes()->date;
          $date = $date / 1000;
          $date = strftime('%Y-%m-%d %H:%M:%S', $date);
          $text = (string)$val->attributes()->body;
          // удаляем одинарные кавычки
          $text = str_replace("'", "", $text);
          $sms_arr[] = array(SMS_TIME_SMS => $date, SMS_UNKNOWN_TEXT => $text);
        }
      }
      // var_dump($sms_arr);
      return $sms_arr;
    }

  }