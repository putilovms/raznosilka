<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\TestHelper.php>
 * @copyright © М. С. Путилов, 2015
 */
class TestHelper {

  /**
   * Данные пользователя
   * @return array
   */
  static function getUser () {
    $user[USER_ID] = 2;
    $user[USER_LOGIN] = 'login';
    $user[USER_EMAIL] = 'mail@mail.ru';
    $user[USER_PASSWORD] = 'password';
    $user[USER_REG_DATE] = '2015-03-07 14:07:30';
    $user[USER_ORG_ID] = 1;
    return $user;
  }

  /**
   * Данные админа
   * @return array
   */
  static function getAdmin () {
    $user[USER_ID] = 1;
    $user[USER_LOGIN] = 'admin';
    $user[USER_EMAIL] = 'admin@mail.ru';
    $user[USER_PASSWORD] = 'password';
    $user[USER_REG_DATE] = '2015-03-01 07:01:29';
    $user[USER_ORG_ID] = 1;
    return $user;
  }

  /**
   * Имитация данных полученных из метода getUserInfo класса User
   * @return array
   */
  static function getUserInfo () {
    $user[USER_ID] = '2';
    $user[USER_LOGIN] = 'login';
    $user[USER_EMAIL] = 'mail@mail.ru';
    $user[USER_PASSWORD] = 'password';
    $user[USER_REG_DATE] = '2015-03-07 14:07:30';
    $user['user_ip'] = '192.168.1.35';
    $user['user_agent'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36';
    return $user;
  }

  /**
   * Иммитация данных о СМС с номером карты полученных из БД
   * @return array Массив с СМС
   */
  static function getCardSMS () {
    $sms[SMS_ID] = 1;
    $sms[USER_ID] = 1;
    $sms[SMS_TIME_SMS] = '2015-01-01 00:00:00';
    $sms[SMS_TIME_PAY] = '2015-01-02 00:00:00';
    $sms[SMS_SUM_PAY] = 20.00;
    $sms[SMS_CARD_PAYER] = 1111;
    $sms[SMS_FIO] = '';
    $sms[SMS_COMMENT] = '';
    $sms[SMS_RETURN] = 0;
    $sms[PAY_ID] = 0;
    return $sms;
  }

  /**
   * Иммитация данных о СМС с ФИО полученных из БД
   * @return array Массив с СМС
   */
  static function getFioSMS () {
    $sms[SMS_ID] = 2;
    $sms[USER_ID] = 1;
    $sms[SMS_TIME_SMS] = '2015-01-01 00:00:00';
    $sms[SMS_TIME_PAY] = '0000-00-00 00:00:00';
    $sms[SMS_SUM_PAY] = 20.00;
    $sms[SMS_CARD_PAYER] = -1;
    $sms[SMS_FIO] = '';
    $sms[SMS_COMMENT] = 'comment';
    $sms[SMS_RETURN] = 0;
    $sms[PAY_ID] = 0;
    return $sms;
  }

  /**
   * Данные для добавления Unknown SMS
   * @return array
   */
  static function getUnknownSMS () {
    $sms[USER_ID] = '1';
    $sms[SMS_TIME_SMS] = '0000-00-00 00:00:00';
    $sms[SMS_UNKNOWN_TEXT] = 'Текст неопределённой СМС';
    return $sms;
  }

  /**
   * Имитация данных из $_FILES
   * @return array
   */
  static function getFilesArr () {
    $files = array(
      'name' => array(
        'error.file',
        '1.txt',
        '1.csv',
        'png.csv',
        'empty.csv',
        '1.xml',
        'empty.xml'
      ),
      'type' => array(
        '',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.ms-excel',
        'application/vnd.ms-excel',
        'text/xml',
        'text/xml'
      ),
      'tmp_name' => array(
        '',
        'Z:\domains\raznosilka\tests\files\1.tmp',
        'Z:\domains\raznosilka\tests\files\2.tmp',
        'Z:\domains\raznosilka\tests\files\3.tmp',
        'Z:\domains\raznosilka\tests\files\4.tmp',
        'Z:\domains\raznosilka\tests\files\5.tmp',
        'Z:\domains\raznosilka\tests\files\6.tmp'
      ),
      'error' => array(
        4,
        0,
        0,
        0,
        0,
        0,
        0
      ),
      'size' => array(
        0,
        0,
        0,
        0,
        0,
        0,
        0
      )
    );
    return $files;
  }

  /**
   * Получить склеенные СМС
   * @return array
   */
  static function getGluedSmsArr () {
    $sms[] = array(SMS_UNKNOWN_TEXT => 'MAES6277: 24.09.14 12:30 операция зачисления на сумму 321.00р. SBOLMAES6277: 24.09.14 09:47 операция зачисления на сумму 255.00р. SBOL s karty 6761****7312. Баланс: 156414.98р. s karty 6761****1955. Баланс: 154007.98р.');
    $sms[] = array(SMS_UNKNOWN_TEXT => 'MAES8874: 25.09.14 18:54 операция списания на сумму 684.00р. SBOL. Баланс: 4914.55р.');
    $sms[] = array(SMS_UNKNOWN_TEXT => 'Сбербанк ОнЛ@йн. Внимательно проверьте реквизиты операции: карта спMAES6277: 24.09.14 23:11 операция зачисления на сумму 603.00р. SBOLисания **** 6277, карта зачисления **** 5232, сумма 2761,00 RUB. Па s karty 6761****5786. Баланс: 168445.98р.роль для подтверждения данной операции - 99167.');
    $sms[] = array(SMS_UNKNOWN_TEXT => 'Сбербанк ОнЛ@йн. Внимательно проверьте реквизиты операции: карта спСбербанк ОнЛ@йн. 09:48 25.09.14 вход в систему. Не вводите пароль дисания **** 6277, карта зачисления **** 3571, сумма 679,00 RUB. Парля отмены операций/шаблонов, не подтверждайте операции, которые Вы оль для подтверждения данной операции - 13670.не совершали.');
    return $sms;
  }

  /**
   * Получить массив с иммитацией корректировки
   * @return array Корректировка
   */
  static function getCorrection () {
    $correction = array();
    $correction[CORRECTION_ID] = 1;
    $correction[CORRECTION_SUM] = 50.00;
    $correction[CORRECTION_COMMENT] = 'Pay by order';
    return $correction;
  }

  /**
   * Получить массив с платежом, иммитирующий массив полученный из базы данных
   * @return array Массив с платежом
   */
  static function getPayToDB () {
    $pay = array();
    $pay[USER_ID] = 1;
    $pay[PURCHASE_ID] = 1;
    $pay[USER_PURCHASE_ID] = 1;
    $pay[PAY_TIME] = '2015-04-02 00:00:00';
    $pay[PAY_SUM] = 10.00;
    $pay[PAY_CARD_PAYER] = 1111;
    $pay[PAY_CREATED] = '2015-04-01 00:00:00';
    $pay[SMS_ID] = 0;
    return $pay;
  }

  /**
   * Получить массив с заказом, иммитирующий массив полученный с сайта СП
   * @return array Массив с заказом
   */
  static function getLot () {
    $lot = array();
    $lot['total_put'] = 0.00;
    $lot['comment_org'] = 'comment';
    // Получение массивов с данными
    $lot['user'] = self::getUserPurchase();
    $lot['pays'][] = self::getPay();
    $lot['orders'][] = self::getOrder();
    $lot['corrections'][] = self::getCorrection();
    return $lot;
  }

  /**
   * Получить массив с участником закупки, иммитирующий массив полученный с сайта СП
   * @return array Массив с участником закупки
   */
  static function getUserPurchase () {
    $userPurchase = array();
    $userPurchase[USER_PURCHASE_ID] = 1;
    $userPurchase[USER_PURCHASE_NAME] = 'FIO';
    $userPurchase[USER_PURCHASE_NICK] = 'Nick';
    $userPurchase['url'] = 'http://www.url.ru/';
    return $userPurchase;
  }

  /**
   * Получить массив с товаром, иммитирующий массив полученный с сайта СП
   * @return array Массив с товаром
   */
  static function getOrder () {
    $order = array();
    $order['id'] = 1;
    $order['org_fee'] = 16;
    $order['state'] = 1;
    $order['delivery'] = 0.00;
    $order['comment_lot'] = 'My order';
    $order['name_lot'] = 'Lot name';
    $order['price'] = 100.00;
    return $order;
  }

  /**
   * Получить массив с платежом, иммитирующий массив полученный с сайта СП
   * @return array Массив с платежом
   */
  static function getPay () {
    $pay = array();
    $pay[PAY_TIME] = '2015-04-02 00:00:00';
    $pay[PAY_CREATED] = '2015-04-01 00:00:00';
    $pay[PAY_SUM] = 10.00;
    $pay[PAY_CARD_PAYER] = 1111;
    return $pay;
  }

  /**
   * Получить массив с корректировкой для добавления в БД
   * @return array Массив с корректировкой
   */
  static function getCorrectionAdd () {
    $correction = array();
    $correction[USER_ID] = 1;
    $correction[PURCHASE_ID] = 1;
    $correction[USER_PURCHASE_ID] = 1;
    $correction[CORRECTION_SUM] = 50.00;
    $correction[CORRECTION_COMMENT] = 'Pay by order';
    return $correction;
  }

  /**
   * @return mixed
   */
  static function getSp () {
    $sp[SP_ID] = 1;
    $sp[SP_SITE_NAME] = 'sp';
    $sp[SP_SITE_URL] = 'http://www.test.ru/';
    $sp[SP_FILLING_DAY] = 2;
    $sp[SP_DESCRIPTION] = 'Тестовый сайт';
    return $sp;
    }
}