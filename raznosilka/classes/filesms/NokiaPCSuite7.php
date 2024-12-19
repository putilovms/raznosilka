<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\NokiaPCSuite7.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class FileSMS_NokiaPCSuite7 Класс для распознавания SMS полученных путём экспорта из телефона
   * при помощи программы Nokia PC Suite 7.
   */
  class FileSMS_NokiaPCSuite7 extends FileSMS {

    /**
     * Получение SMS из файла.
     * Структкра файла известна заранее:
     * - 2 - номер с которого пришла SMS
     * - 5 - время получения SMS
     * - 7 - Тело SMS
     * @return array
     */
    function SMSFromFile () {
      $arr = array();
      $handle = fopen($this->file['tmp_name'], "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        // Телефонный номер с которого приходят SMS от Сбербанка
        if ($data[2] == 900) {
          $dateTime = DateTime::createFromFormat('Y.m.d H:i', $data[5]);
          $item[SMS_TIME_SMS] = $dateTime->format('Y-m-d H:i:00');
          $item[SMS_UNKNOWN_TEXT] = $data[7];
          $arr[] = $item;
        }
      }
      fclose($handle);
      return $arr;
    }

  }