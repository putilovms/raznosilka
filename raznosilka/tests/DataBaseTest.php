<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\DataBaseTest.php>
 * @copyright © М. С. Путилов, 2015
 */

require_once '../classes/DataBase.php';
require_once '../resources/const.php';
require_once 'TestHelper.php';

class DataBaseTest extends PHPUnit_Framework_TestCase {
  /**
   * @var DataBase
   */
  public static $db;
  /**
   * @var PDO
   */
  public static $pdo;

  public static function setUpBeforeClass () {
    // Соединение с БД
    $dbOptions = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
    $dsn = "mysql:host=192.168.1.35:3306;dbname=test";
    $user = 'user';
    $pass = 'pass';
    self::$pdo = new PDO($dsn, $user, $pass, $dbOptions);
    // Установка кодировки для БД
    self::$pdo->query("SET NAMES UTF8");
    self::$db = new DataBase(self::$pdo);
    // Настройка кодировки
    mb_internal_encoding('UTF-8');
  }

  public static function tearDownAfterClass () {
    self::$pdo->query("DROP TABLE IF EXISTS correction, messages, pay, purchase, sms, sms_unknown, sp, users, users_purchase");
  }

  public function test_createTableUsers_true () {
    $result = self::$db->createTableUsers();
    $this->assertTrue($result);
  }

  public function test_createTableSp_true () {
    $result = self::$db->createTableSp();
    $this->assertTrue($result);
  }

  public function test_createTableSms_true () {
    $result = self::$db->createTableSms();
    $this->assertTrue($result);
  }

  public function test_createTablePay_true () {
    $result = self::$db->createTablePay();
    $this->assertTrue($result);
  }

  public function test_createTablePurchase_true () {
    $result = self::$db->createTablePurchase();
    $this->assertTrue($result);
  }

  public function test_createTableUsersPurchase_true () {
    $result = self::$db->createTableUsersPurchase();
    $this->assertTrue($result);
  }

  public function test_createTableCorrection_true () {
    $result = self::$db->createTableCorrection();
    $this->assertTrue($result);
  }

  public function test_createTableSmsUnknown_true () {
    $result = self::$db->createTableSmsUnknown();
    $this->assertTrue($result);
  }

  public function test_createTableMessages_true () {
    $result = self::$db->createTableMessages();
    $this->assertTrue($result);
  }

  public function test_encodePassword_and_decodePassword () {
    $pass = 'password';
    $encode = self::$db->encodePassword($pass);
    $this->assertNotEquals($pass, $encode);
    $decode = self::$db->decodePassword($encode);
    $this->assertEquals($pass, $decode);
  }

  public function test_addUser_array () {
    $login = 'login';
    $email = 'mail@mail.ru';
    $pass = 'pass';
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $result = self::$db->addUser($login, $email, $pass, $date, '');
    // Значения
    $this->assertEquals($login, $result[USER_LOGIN]);
    $this->assertEquals($email, $result[USER_EMAIL]);
    $this->assertEquals($pass, $result[USER_PASSWORD]);
    $this->assertEquals('', $result[USER_SP_LOGIN]);
    $this->assertEquals('', $result[USER_SP_PASSWORD]);
    $this->assertEquals($date, $result[USER_REG_DATE]);
    $this->assertEquals('0000-00-00 00:00:00', $result[USER_ACTIVATE]);
    $this->assertEquals('0000-00-00 00:00:00', $result[USER_VALIDATE]);
    $this->assertEquals(0, $result[SP_ID]);
    // Структура
    $this->assertInternalType('array', $result);
    $this->assertCount(16, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(USER_LOGIN, $result);
    $this->assertArrayHasKey(USER_EMAIL, $result);
    $this->assertArrayHasKey(USER_PASSWORD, $result);
    $this->assertArrayHasKey(USER_SP_LOGIN, $result);
    $this->assertArrayHasKey(USER_SP_PASSWORD, $result);
    $this->assertArrayHasKey(SP_ID, $result);
    $this->assertArrayHasKey(USER_ORG_ID, $result);
    $this->assertArrayHasKey(USER_REMINDING, $result);
    $this->assertArrayHasKey(USER_FILLING_DAY, $result);
    $this->assertArrayHasKey(USER_REG_DATE, $result);
    $this->assertArrayHasKey(USER_ACTIVATE, $result);
    $this->assertArrayHasKey(USER_VALIDATE, $result);
    $this->assertArrayHasKey(USER_TMP_EMAIL, $result);
    $this->assertArrayHasKey(USER_SESSION_ID, $result);
    $this->assertArrayHasKey(USER_LAST_TIME, $result);
  }

  public function test_checkUserRegistration_array () {
    $result = self::$db->checkUserRegistration('login', 'pass');
    // Значения
    $this->assertEquals('login', $result[USER_LOGIN]);
    $this->assertEquals('pass', $result[USER_PASSWORD]);
    // Структура
    $this->assertInternalType('array', $result);
    $this->assertCount(16, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(USER_LOGIN, $result);
    $this->assertArrayHasKey(USER_EMAIL, $result);
    $this->assertArrayHasKey(USER_PASSWORD, $result);
    $this->assertArrayHasKey(USER_SP_LOGIN, $result);
    $this->assertArrayHasKey(USER_SP_PASSWORD, $result);
    $this->assertArrayHasKey(SP_ID, $result);
    $this->assertArrayHasKey(USER_ORG_ID, $result);
    $this->assertArrayHasKey(USER_REMINDING, $result);
    $this->assertArrayHasKey(USER_FILLING_DAY, $result);
    $this->assertArrayHasKey(USER_REG_DATE, $result);
    $this->assertArrayHasKey(USER_ACTIVATE, $result);
    $this->assertArrayHasKey(USER_VALIDATE, $result);
    $this->assertArrayHasKey(USER_TMP_EMAIL, $result);
    $this->assertArrayHasKey(USER_SESSION_ID, $result);
    $this->assertArrayHasKey(USER_LAST_TIME, $result);
  }

  public function test_checkUserRegistration_null () {
    $result = self::$db->checkUserRegistration('nouser', 'pass');
    $this->assertNull($result);
  }

  public function test_getUserById_array () {
    $result = self::$db->getUserById(1);
    // Значения
    $this->assertEquals(1, $result[USER_ID]);
    // Структура
    $this->assertInternalType('array', $result);
    $this->assertCount(16, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(USER_LOGIN, $result);
    $this->assertArrayHasKey(USER_EMAIL, $result);
    $this->assertArrayHasKey(USER_PASSWORD, $result);
    $this->assertArrayHasKey(USER_SP_LOGIN, $result);
    $this->assertArrayHasKey(USER_SP_PASSWORD, $result);
    $this->assertArrayHasKey(SP_ID, $result);
    $this->assertArrayHasKey(USER_ORG_ID, $result);
    $this->assertArrayHasKey(USER_REMINDING, $result);
    $this->assertArrayHasKey(USER_FILLING_DAY, $result);
    $this->assertArrayHasKey(USER_REG_DATE, $result);
    $this->assertArrayHasKey(USER_ACTIVATE, $result);
    $this->assertArrayHasKey(USER_VALIDATE, $result);
    $this->assertArrayHasKey(USER_TMP_EMAIL, $result);
    $this->assertArrayHasKey(USER_SESSION_ID, $result);
    $this->assertArrayHasKey(USER_LAST_TIME, $result);
  }

  public function test_getUserById_null () {
    $result = self::$db->getUserById(777);
    $this->assertNull($result);
  }

  public function test_getUserByLogin_array () {
    $result = self::$db->getUserByLogin('login');
    // Значения
    $this->assertEquals('login', $result[USER_LOGIN]);
    // Структура
    $this->assertInternalType('array', $result);
    $this->assertCount(16, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(USER_LOGIN, $result);
    $this->assertArrayHasKey(USER_EMAIL, $result);
    $this->assertArrayHasKey(USER_PASSWORD, $result);
    $this->assertArrayHasKey(USER_SP_LOGIN, $result);
    $this->assertArrayHasKey(USER_SP_PASSWORD, $result);
    $this->assertArrayHasKey(SP_ID, $result);
    $this->assertArrayHasKey(USER_ORG_ID, $result);
    $this->assertArrayHasKey(USER_REMINDING, $result);
    $this->assertArrayHasKey(USER_FILLING_DAY, $result);
    $this->assertArrayHasKey(USER_REG_DATE, $result);
    $this->assertArrayHasKey(USER_ACTIVATE, $result);
    $this->assertArrayHasKey(USER_VALIDATE, $result);
    $this->assertArrayHasKey(USER_TMP_EMAIL, $result);
    $this->assertArrayHasKey(USER_SESSION_ID, $result);
    $this->assertArrayHasKey(USER_LAST_TIME, $result);
  }

  public function test_getUserByLogin_null () {
    $result = self::$db->getUserByLogin('super user login');
    $this->assertNull($result);
  }

  public function test_getUserByEmail_array () {
    $result = self::$db->getUserByEmail('mail@mail.ru');
    // Значения
    $this->assertEquals('mail@mail.ru', $result[USER_EMAIL]);
    // Структура
    $this->assertInternalType('array', $result);
    $this->assertCount(16, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(USER_LOGIN, $result);
    $this->assertArrayHasKey(USER_EMAIL, $result);
    $this->assertArrayHasKey(USER_PASSWORD, $result);
    $this->assertArrayHasKey(USER_SP_LOGIN, $result);
    $this->assertArrayHasKey(USER_SP_PASSWORD, $result);
    $this->assertArrayHasKey(SP_ID, $result);
    $this->assertArrayHasKey(USER_ORG_ID, $result);
    $this->assertArrayHasKey(USER_REMINDING, $result);
    $this->assertArrayHasKey(USER_FILLING_DAY, $result);
    $this->assertArrayHasKey(USER_REG_DATE, $result);
    $this->assertArrayHasKey(USER_ACTIVATE, $result);
    $this->assertArrayHasKey(USER_VALIDATE, $result);
    $this->assertArrayHasKey(USER_TMP_EMAIL, $result);
    $this->assertArrayHasKey(USER_SESSION_ID, $result);
    $this->assertArrayHasKey(USER_LAST_TIME, $result);
  }

  public function test_getUserByEmail_null () {
    $result = self::$db->getUserByEmail('none@this.email');
    $this->assertNull($result);
  }

  public function test_setEncodingDb_true () {
    $result = self::$db->setEncodingDb('UTF8');
    $this->assertTrue($result);
  }

  public function test_setEncodingDb_exception () {
    $this->setExpectedException('Exception');
    self::$db->setEncodingDb('');
  }

  public function test_setUserSettings_true () {
    $id = 1;
    $settings[USER_SP_LOGIN] = 'sp login';
    $settings[USER_SP_PASSWORD] = 'sp pass';
    $settings[SP_ID] = '2';
    $result = self::$db->setUserSettings($id, $settings);

    $this->assertTrue($result);

    // Проверка
    $result = self::$db->getUserById($id);

    $this->assertEquals($settings[USER_SP_LOGIN], $result[USER_SP_LOGIN]);
    $this->assertEquals($settings[USER_SP_PASSWORD], $result[USER_SP_PASSWORD]);
    $this->assertEquals($settings[SP_ID], $result[SP_ID]);
  }

  public function test_activate_true () {
    // Подготовка
    $login = 'new user';
    $email = 'mail@mail.ru';
    $pass = 'pass';
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $user = self::$db->addUser($login, $email, $pass, $date, $date);
    $this->assertNotEquals('0000-00-00 00:00:00', $user[USER_ACTIVATE]);

    // Активация
    $result = self::$db->activate($user[USER_ID]);
    $this->assertInternalType('array', $result);
    $this->assertCount(16, $result);
    $this->assertEquals('0000-00-00 00:00:00', $result[USER_ACTIVATE]);
  }

  public function test_postMessage_true () {
    // Подготовка
    $id = 1;
    $type = 0;
    $text = 'text';
    $messages = self::$db->getMessages($id);
    $result = self::$db->postMessage($type, $text, $id);
    $messagesNew = self::$db->getMessages($id);

    // Проверка
    $this->assertTrue($result);
    $this->assertEquals(count($messages), count($messagesNew) - 1);
  }

  public function test_getMessages_array () {
    // Подготовка
    $id = 1;
    $type = 0;
    $text = 'text';
    self::$db->postMessage($type, $text, $id);
    $result = self::$db->getMessages($id);

    // Проверка
    $this->assertGreaterThan(0, count($result));
    $this->assertInternalType('array', $result);
    // извлекаем сообщение
    $msg = array_shift($result);
    // структура
    $this->assertInternalType('array', $msg);
    $this->assertCount(6, $msg);
    $this->assertArrayHasKey(USER_ID, $msg);
    $this->assertArrayHasKey(MESSAGE_ID, $msg);
    $this->assertArrayHasKey(MESSAGE_DATE, $msg);
    $this->assertArrayHasKey(MESSAGE_NEW, $msg);
    $this->assertArrayHasKey(MESSAGE_TEXT, $msg);
    $this->assertArrayHasKey(MESSAGE_TYPE, $msg);
  }

  public function test_getCountNewMessages_true () {
    // Подготовка
    $id = 1;
    $type = 0;
    $text = 'text';
    self::$db->postMessage($type, $text, $id);
    $result = self::$db->getCountNewMessages($id);

    // Проверка
    $this->assertInternalType('integer', $result);
    $this->assertGreaterThan(0, $result);
  }

  public function test_setAllRead_true () {
    // Подготовка
    $id = 1;
    $type = 0;
    $text = 'text';
    self::$db->postMessage($type, $text, $id);
    $count = self::$db->getCountNewMessages($id);
    $this->assertGreaterThan(0, $count);
    $result = self::$db->setMessagesRead($id);
    $count = self::$db->getCountNewMessages($id);

    // Проверка
    $this->assertTrue($result);
    $this->assertEquals(0, $count);
  }

  public function test_deleteMessages_true () {
    // Подготовка
    $id = 1;
    $type = 0;
    $text = 'text';
    self::$db->postMessage($type, $text, $id);
    $messages = self::$db->getMessages($id);
    $arr = array();
    foreach ($messages as $msg) $arr[] = $msg[MESSAGE_ID];
    $result = self::$db->deleteMessages($arr, $id);
    $messages = self::$db->getMessages($id);

    // Проверка
    $this->assertTrue($result);
    $this->assertEquals(0, count($messages));
  }

  public function test_postMessages_active_send () {
    // Подготовка
    $id = 1;
    $type = 0;
    $text = 'text';
    $messages = self::$db->getMessages($id);
    $result = self::$db->postMessages($type, $text);
    $messagesNew = self::$db->getMessages($id);

    // Проверка
    $this->assertInternalType('integer', $result);
    $this->assertGreaterThan(0, $result);
    $this->assertEquals(count($messages), count($messagesNew) - 1);
  }

  public function test_postMessages_notActive_notSend () {
    // Подготовка
    $login = 'not active user';
    $email = 'mail@mail.ru';
    $pass = 'pass';
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $user = self::$db->addUser($login, $email, $pass, $date, $date);
    $id = $user[USER_ID];
    $type = 0;
    $text = 'text';
    $messages = self::$db->getMessages($id);
    self::$db->postMessages($type, $text);
    $messagesNew = self::$db->getMessages($id);

    // Проверка
    $this->assertEquals(count($messages), count($messagesNew));
  }

  public function test_construct () {
    $db = new DataBase(self::$pdo);
    $this->assertInstanceOf('DataBase', $db);
  }

  public function test_addSMS_true () {
    $sms[] = TestHelper::getCardSMS();
    $result = self::$db->addSMS($sms);
    // Проверка
    $this->assertTrue($result);
  }

  public function test_smsExist_true () {
    $sms = TestHelper::getCardSMS();
    $result = self::$db->smsExist($sms);
    // Проверка
    $this->assertTrue($result);
  }

  public function test_smsExist_false_userId () {
    // user_id
    $sms = TestHelper::getCardSMS();
    $sms[USER_ID] = '2';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsExist_false_timeSms () {
    // time_sms
    $sms = TestHelper::getCardSMS();
    $sms[SMS_TIME_SMS] = '2015-03-23 10:33:00';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsExist_false_timePay () {
    // time_pay
    $sms = TestHelper::getCardSMS();
    $sms[SMS_TIME_PAY] = '2015-03-23 10:33:00';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsExist_false_sum () {
    // sum
    $sms = TestHelper::getCardSMS();
    $sms[SMS_SUM_PAY] = '10';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsExist_false_cardPayer () {
    // card_payer
    $sms = TestHelper::getCardSMS();
    $sms[SMS_CARD_PAYER] = '0';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsExist_false_fio () {
    // fio
    $sms = TestHelper::getCardSMS();
    $sms[SMS_FIO] = 'ФИО';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsExist_false_comment () {
    // comment
    $sms = TestHelper::getCardSMS();
    $sms[SMS_COMMENT] = 'коммент';
    $result = self::$db->smsExist($sms);
    $this->assertFalse($result);
  }

  public function test_addUnknownSMS_getAllSmsUnknown_count () {
    $beforeCount = count(self::$db->getAllSmsUnknown());
    $sms[] = TestHelper::getUnknownSMS();
    $result = self::$db->addUnknownSMS($sms);
    $afterCount = count(self::$db->getAllSmsUnknown());
    // Проверка
    $this->assertTrue($result);
    $this->assertEquals($beforeCount, $afterCount - 1);
  }

  public function test_getSmsUnknown_count () {
    // Подготовка
    $sms[] = TestHelper::getUnknownSMS();
    self::$db->addUnknownSMS($sms);
    $all = self::$db->getAllSmsUnknown();
    // Получение ID СМС
    $id = array();
    foreach ($all as $sms) {
      $id[] = $sms[SMS_UNKNOWN_ID];
    }
    $result = self::$db->getSmsUnknownById($id);
    $this->assertEquals(count($all), count($result));
  }

  public function test_getSmsUnknown_false () {
    // Подготовка
    $id = array('строка');
    $result = self::$db->getSmsUnknownById($id);
    $this->assertFalse($result);
  }

  public function test_smsUnknownExist_true () {
    $sms = TestHelper::getUnknownSMS();
    $result = self::$db->smsUnknownExist($sms);
    // Проверка
    $this->assertTrue($result);
  }

  public function test_smsUnknownExist_false_userId () {
    // user_id
    $sms = TestHelper::getUnknownSMS();
    $sms[USER_ID] = '2';
    $result = self::$db->smsUnknownExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsUnknownExist_false_timeSms () {
    // time_sms
    $sms = TestHelper::getUnknownSMS();
    $sms[SMS_TIME_SMS] = '2015-03-23 10:33:00';
    $result = self::$db->smsUnknownExist($sms);
    $this->assertFalse($result);
  }

  public function test_smsUnknownExist_false_text () {
    // text
    $sms = TestHelper::getUnknownSMS();
    $sms[SMS_UNKNOWN_TEXT] = 'Другой текст';
    $result = self::$db->smsUnknownExist($sms);
    $this->assertFalse($result);
  }

  public function test_getCountRecordsSms_int () {
    $result = self::$db->getCountRecordsSms();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsPay_int () {
    $result = self::$db->getCountRecordsPay();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsCorrection_int () {
    $result = self::$db->getCountRecordsCorrection();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsSmsUnknown_int () {
    $result = self::$db->getCountRecordsSmsUnknown();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsPurchase_int () {
    $result = self::$db->getCountRecordsPurchase();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsSp_int () {
    $result = self::$db->getCountRecordsSp();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsUsers_int () {
    $result = self::$db->getCountRecordsUsers();
    $this->assertInternalType('int', $result);
  }

  public function test_getCountRecordsUsersPurchase_int () {
    $result = self::$db->getCountRecordsUsersPurchase();
    $this->assertInternalType('int', $result);
  }

  public function test_deleteSmsUnknown () {
    // Подготовка
    $sms[] = TestHelper::getUnknownSMS();
    self::$db->addUnknownSMS($sms);
    $all = self::$db->getAllSmsUnknown();
    // Получение ID СМС
    $id = array();
    foreach ($all as $sms) {
      $id[] = $sms[SMS_UNKNOWN_ID];
    }
    // Проверка
    self::$db->deleteSmsUnknown($id);
    $all = self::$db->getAllSmsUnknown();
    $this->assertEquals(0, count($all));
  }

  // todo добавить проверку на добавление закупки

  public function test_addSp_int(){
    // Подготовка
    $sp = TestHelper::getSp();
    $result = self::$db->importSpList($sp);
    // Проверка
    $this->assertInternalType('integer', $result);
  }

  public function test_getAllSP_array () {
    // Подготовка
    $result = self::$db->getAllSP();
    // Проверка
    $this->assertInternalType('array', $result);
    $this->assertCount(1, $result);
    // Структура
    $this->assertCount(5, $result[0]);
    $this->assertArrayHasKey(SP_ID, $result[0]);
    $this->assertArrayHasKey(SP_SITE_NAME, $result[0]);
    $this->assertArrayHasKey(SP_SITE_URL, $result[0]);
    $this->assertArrayHasKey(SP_FILLING_DAY, $result[0]);
    $this->assertArrayHasKey(SP_DESCRIPTION, $result[0]);
  }

  public function test_getSpById_array () {
    // Подготовка
    $result = self::$db->getSpById(1);
    // Проверка
    $this->assertInternalType('array', $result);
    $this->assertCount(5, $result);
    $this->assertArrayHasKey(SP_ID, $result);
    $this->assertArrayHasKey(SP_SITE_NAME, $result);
    $this->assertArrayHasKey(SP_SITE_URL, $result);
    $this->assertArrayHasKey(SP_FILLING_DAY, $result);
    $this->assertArrayHasKey(SP_DESCRIPTION, $result);
  }

  public function test_getSpById_false () {
    // Подготовка
    $result = self::$db->getSpById(10);
    // Проверка
    $this->assertFalse($result);
  }

  public function test_addPurchase_true () {
    // Подготовка
    $purchaseName = 'test';
    $purchaseId = 1;
    $userId = 1;
    $spId = 1;
    $payTo = '';
    $result = self::$db->addPurchase($purchaseName, $purchaseId, $userId, $spId, $payTo);
    // Проверка
    $this->assertTrue($result);
  }

  public function test_getPurchase_array () {
    // Подготовка
    $purchaseId = 1;
    $userId = 1;
    $spId = 1;
    $result = self::$db->getPurchase($purchaseId, $userId, $spId);
    // Проверка
    $this->assertInternalType('array', $result);
    $this->assertCount(5, $result);
    $this->assertArrayHasKey(PURCHASE_ID, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(PURCHASE_NAME, $result);
    $this->assertArrayHasKey(PURCHASE_PAY_TO, $result);
    $this->assertArrayHasKey(SP_ID, $result);
  }

  public function test_getPurchase_false () {
    // Подготовка
    $purchaseId = 2;
    $userId = 2;
    $spId = 2;
    $result = self::$db->getPurchase($purchaseId, $userId, $spId);
    // Проверка
    $this->assertFalse($result);
  }

  public function test_updatePurchase_true () {
    // Подготовка
    $purchaseName = 'update';
    $purchaseId = 1;
    $userId = 1;
    $spId = 1;
    $payTo = '';
    $result = self::$db->updatePurchase($purchaseName, $purchaseId, $userId, $spId, $payTo);
    // Проверка
    $this->assertTrue($result);
    // Проверка изменения закупки
    $result = self::$db->getPurchase($purchaseId, $userId, $spId);
    $this->assertEquals($purchaseName, $result[PURCHASE_NAME]);
    $this->assertNotEquals('test', $result[PURCHASE_NAME]);
  }

  public function test_getAllPurchaseOfUser_array () {
    // Подготовка
    $userId = 1;
    $spId = 1;
    $result = self::$db->getAllPurchaseOfUser($userId, $spId);
    // Проверка
    $this->assertInternalType('array', $result);
    $this->assertCount(1, $result);
  }

  public function test_addPay_integer () {
    // Подготовка
    $pay = TestHelper::getPayToDB();
    $result = self::$db->addPay($pay);
    // Проверка
    $this->assertInternalType('integer', $result);
  }

  public function test_getPay_array () {
    // Подготовка
    $pay = TestHelper::getPayToDB();
    $pay[USER_PURCHASE_ID] = 2;
    self::$db->addPay($pay);
    // Проверка
    $result = self::$db->getPay($pay[USER_ID], $pay[PURCHASE_ID], $pay[USER_PURCHASE_ID], $pay[PAY_TIME], $pay[PAY_SUM], $pay[PAY_CARD_PAYER], $pay[PAY_CREATED]);
    $this->assertInternalType('array', $result);
    // Структура
    $this->assertCount(9, $result);
    $this->assertArrayHasKey(PAY_ID, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(PURCHASE_ID, $result);
    $this->assertArrayHasKey(USER_PURCHASE_ID, $result);
    $this->assertArrayHasKey(PAY_TIME, $result);
    $this->assertArrayHasKey(PAY_SUM, $result);
    $this->assertArrayHasKey(PAY_CARD_PAYER, $result);
    $this->assertArrayHasKey(PAY_CREATED, $result);
    $this->assertArrayHasKey(SMS_ID, $result);
  }

  public function test_getPay_false () {
    // Подготовка
    $pay = TestHelper::getPayToDB();
    $pay[USER_PURCHASE_ID] = 1000;
    // Проверка
    $result = self::$db->getPay($pay[USER_ID], $pay[PURCHASE_ID], $pay[USER_PURCHASE_ID], $pay[PAY_TIME], $pay[PAY_SUM], $pay[PAY_CARD_PAYER], $pay[PAY_CREATED]);
    $this->assertFalse($result);
  }

  public function test_findSms_array () {
    // Подготовка
    $sms = TestHelper::getCardSMS();
    $sms[SMS_SUM_PAY] = 10.00;
    $smss[] = $sms;
    self::$db->addSMS($smss);
    $fork = 24;
    // Проверка
    $result = self::$db->findSms($sms[USER_ID], $sms[SMS_TIME_SMS], $fork, $sms[SMS_SUM_PAY], $sms[SMS_CARD_PAYER]);
    $this->assertInternalType('array', $result);
    $this->assertCount(1, $result);
    // Структура
    $this->assertCount(10, $result[0]);
    $this->assertArrayHasKey(SMS_ID, $result[0]);
    $this->assertArrayHasKey(USER_ID, $result[0]);
    $this->assertArrayHasKey(SMS_TIME_SMS, $result[0]);
    $this->assertArrayHasKey(SMS_TIME_PAY, $result[0]);
    $this->assertArrayHasKey(SMS_SUM_PAY, $result[0]);
    $this->assertArrayHasKey(SMS_CARD_PAYER, $result[0]);
    $this->assertArrayHasKey(SMS_FIO, $result[0]);
    $this->assertArrayHasKey(SMS_COMMENT, $result[0]);
    $this->assertArrayHasKey(PAY_ID, $result[0]);
  }

  public function test_findSms_false () {
    // Подготовка
    $sms = TestHelper::getCardSMS();
    $smss[] = $sms;
    self::$db->addSMS($smss);
    $fork = 24;
    // Проверка
    $result = self::$db->findSms(2, $sms[SMS_TIME_SMS], $fork, $sms[SMS_SUM_PAY], $sms[SMS_CARD_PAYER]);
    $this->assertFalse($result);
  }

  public function test_getSmsById_array () {
    // Подготовка
    $sms = TestHelper::getCardSMS();
    $sms[SMS_SUM_PAY] = 100.00;
    $smss[] = $sms;
    self::$db->addSMS($smss);
    $fork = 24;
    $findSms = self::$db->findSms($sms[USER_ID], $sms[SMS_TIME_SMS], $fork, $sms[SMS_SUM_PAY], $sms[SMS_CARD_PAYER]);
    $smsId = $findSms[0][SMS_ID];
    // Проверка
    $result = self::$db->getSmsById($smsId);
    $this->assertContains($smsId, $result);
    $this->assertContains($sms[USER_ID], $result);
    $this->assertContains($sms[SMS_TIME_SMS], $result);
    $this->assertContains($sms[SMS_TIME_PAY], $result);
    $this->assertContains($sms[SMS_SUM_PAY], $result);
    $this->assertContains($sms[SMS_CARD_PAYER], $result);
    $this->assertContains($sms[SMS_FIO], $result);
    $this->assertContains($sms[SMS_COMMENT], $result);
  }

  public function test_getSmsById_false () {
    $smsId = 1000;
    $result = self::$db->getSmsById($smsId);
    $this->assertFalse($result);
  }

  public function test_updateSms_true () {
    // Добавление СМС
    $sms = TestHelper::getCardSMS();
    $sms[SMS_SUM_PAY] = 1000.00;
    $smss[] = $sms;
    self::$db->addSMS($smss);
    // Поиск СМС для получения её ID
    $fork = 24;
    $findSms = self::$db->findSms($sms[USER_ID], $sms[SMS_TIME_SMS], $fork, $sms[SMS_SUM_PAY], $sms[SMS_CARD_PAYER]);
    $smsId = $findSms[0][SMS_ID];
    // Добавление платежа
    $pay = TestHelper::getPayToDB();
    $pay[USER_PURCHASE_ID] = 3;
    $payId = self::$db->addPay($pay);
    // Проверка
    $result = self::$db->updateSms($payId, $smsId);
    $this->assertTrue($result);
    $getSms = self::$db->getSmsById($smsId);
    $this->assertEquals($payId, $getSms[PAY_ID]);
  }

  public function test_fillingPay_true () {
    // Добавление СМС
    $sms = TestHelper::getCardSMS();
    $sms[SMS_SUM_PAY] = 20.00;
    $sms[PAY_ID] = 0;
    $smss[] = $sms;
    self::$db->addSMS($smss);
    // Получение ID СМС
    $fork = 24;
    $findSms = self::$db->findSms($sms[USER_ID], $sms[SMS_TIME_SMS], $fork, $sms[SMS_SUM_PAY], $sms[SMS_CARD_PAYER]);
    $smsId = $findSms[0][SMS_ID];
    // Проверка
    $pay = TestHelper::getPayToDB();
    $pay[USER_PURCHASE_ID] = 3;
    $pay[SMS_ID] = $smsId;
    $pay[PAY_SUM] = 20.00;
    $result = self::$db->fillingPay($pay);
    $this->assertNotFalse($result);
    // Проверка изменения СМС
    $getSms = self::$db->getSmsById($smsId);
    $this->assertNotEquals($findSms[PAY_ID], $getSms[PAY_ID]);
    $this->assertEquals(0, $findSms[PAY_ID]);
    // Проверка добавления платежа
    $getPay = self::$db->getPay($pay[USER_ID], $pay[PURCHASE_ID], $pay[USER_PURCHASE_ID], $pay[PAY_TIME], $pay[PAY_SUM], $pay[PAY_CARD_PAYER], $pay[PAY_CREATED]);
    $this->assertEquals($getSms[PAY_ID], $getPay[PAY_ID]);
    $this->assertEquals($getSms[SMS_ID], $getPay[SMS_ID]);
  }

  public function test_addUserPurchase_true () {
    $userPurchaseId = 1;
    $fio = 'Ivan Ivanov';
    $nick = 'Tsar';
    $spId = 1;
    $result = self::$db->addUserPurchase($userPurchaseId, $fio, $nick, $spId);
    $this->assertTrue($result);
  }

  public function test_getUserPurchase_array () {
    // Подготовка
    $userPurchaseId = 2;
    $fio = 'Ivan Ivanov';
    $nick = 'Tsar';
    $spId = 1;
    self::$db->addUserPurchase($userPurchaseId, $fio, $nick, $spId);
    // Проверка
    $result = self::$db->getUserPurchase($userPurchaseId, $spId);
    $this->assertInternalType('array', $result);
    // Структура
    $this->assertCount(4, $result);
    $this->assertArrayHasKey(USER_PURCHASE_ID, $result);
    $this->assertArrayHasKey(USER_PURCHASE_NAME, $result);
    $this->assertArrayHasKey(USER_PURCHASE_NICK, $result);
    $this->assertArrayHasKey(SP_ID, $result);
  }

  public function test_getUserPurchase_false () {
    $userPurchaseId = 1000;
    $spId = 1000;
    $result = self::$db->getUserPurchase($userPurchaseId, $spId);
    $this->assertFalse($result);
  }

  public function test_updateUserPurchase_true () {
    // Подготовка
    $userPurchaseId = 3;
    $fio = 'Ivan Ivanov';
    $nick = 'Tsar';
    $spId = 1;
    self::$db->addUserPurchase($userPurchaseId, $fio, $nick, $spId);
    // Проверка
    $fioNew = 'Mickle P';
    $nickNew = 'Nick';
    $result = self::$db->updateUserPurchase($userPurchaseId, $fioNew, $nickNew, $spId);
    $this->assertTrue($result);
    // Проверка изменённых значений
    $getUserPurchase = self::$db->getUserPurchase($userPurchaseId, $spId);
    $this->assertInternalType('array', $getUserPurchase);
    $this->assertEquals($fioNew, $getUserPurchase[USER_PURCHASE_NAME]);
    $this->assertEquals($nickNew, $getUserPurchase[USER_PURCHASE_NICK]);
  }

  public function test_addCorrection_true () {
    $correction = TestHelper::getCorrectionAdd();
    $result = self::$db->addCorrection($correction);
    $this->assertEquals($result, 1);
  }

  public function test_getCorrectionToPurchase_array () {
    // Подготовка
    $correction = TestHelper::getCorrectionAdd();
    $correction[USER_ID] = 2;
    $correction[PURCHASE_ID] = 2;
    self::$db->addCorrection($correction);
    // Проверка
    $result = self::$db->getCorrectionToPurchase($correction[USER_ID], $correction[PURCHASE_ID]);
    $this->assertCount(1, $result);
    $this->assertInternalType('array', $result);
    $this->assertInternalType('array', $result[0]);
    // Структура
    $this->assertCount(6, $result[0]);
    $this->assertArrayHasKey(CORRECTION_ID, $result[0]);
    $this->assertArrayHasKey(USER_ID, $result[0]);
    $this->assertArrayHasKey(PURCHASE_ID, $result[0]);
    $this->assertArrayHasKey(USER_PURCHASE_ID, $result[0]);
    $this->assertArrayHasKey(CORRECTION_SUM, $result[0]);
    $this->assertArrayHasKey(CORRECTION_COMMENT, $result[0]);
  }

  public function test_getCorrectionToPurchase_false () {
    // Подготовка
    $correction = TestHelper::getCorrectionAdd();
    $correction[USER_ID] = 3;
    $correction[PURCHASE_ID] = 3;
    // Проверка
    $result = self::$db->getCorrectionToPurchase($correction[USER_ID], $correction[PURCHASE_ID]);
    $this->assertFalse($result);
  }

  public function test_getCorrectionById_true () {
    // Подготовка
    $correction = TestHelper::getCorrectionAdd();
    $correctionId = self::$db->addCorrection($correction);
    $this->assertInternalType('integer', $correctionId);
    // Проверка
    $result = self::$db->getCorrectionById($correctionId);
    $this->assertCount(6, $result);
    $this->assertInternalType('array', $result);
    // Структура
    $this->assertArrayHasKey(CORRECTION_ID, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(PURCHASE_ID, $result);
    $this->assertArrayHasKey(USER_PURCHASE_ID, $result);
    $this->assertArrayHasKey(CORRECTION_SUM, $result);
    $this->assertArrayHasKey(CORRECTION_COMMENT, $result);
  }

  public function test_getCorrectionById_false () {
    // Подготовка
    $correctionId = 1000;
    // Проверка
    $result = self::$db->getCorrectionById($correctionId);
    $this->assertFalse($result);
  }

  public function test_correctionDelete_true () {
    // Подготовка
    $correction = TestHelper::getCorrectionAdd();
    $correctionId = self::$db->addCorrection($correction);
    $this->assertInternalType('integer', $correctionId);
    // Проверка
    $result = self::$db->correctionDelete($correctionId);
    $this->assertTrue($result);
    $result = self::$db->getCorrectionById($correctionId);
    $this->assertFalse($result);
  }

  public function test_payErrorDelete_true () {
    // Подготовка
    $pay = TestHelper::getPayToDB();
    $payId = self::$db->addPay($pay);
    $this->assertInternalType('integer', $payId);
    // Проверка
    $result = self::$db->payErrorDelete($payId);
    $this->assertTrue($result);
  }

  public function test_payErrorDelete_false () {
    // Подготовка
    $payId = 1000;
    $this->assertInternalType('integer', $payId);
    // Проверка
    $result = self::$db->payErrorDelete($payId);
    $this->assertFalse($result);
  }

  public function test_getPayById_array () {
    // Подготовка
    $pay = TestHelper::getPayToDB();
    $payId = self::$db->addPay($pay);
    $this->assertInternalType('integer', $payId);
    // Проверка
    $result = self::$db->getPayById($payId);
    $this->assertInternalType('array', $result);
    // Структура
    $this->assertCount(9, $result);
    $this->assertArrayHasKey(PAY_ID, $result);
    $this->assertArrayHasKey(USER_ID, $result);
    $this->assertArrayHasKey(PURCHASE_ID, $result);
    $this->assertArrayHasKey(USER_PURCHASE_ID, $result);
    $this->assertArrayHasKey(PAY_TIME, $result);
    $this->assertArrayHasKey(PAY_SUM, $result);
    $this->assertArrayHasKey(PAY_CARD_PAYER, $result);
    $this->assertArrayHasKey(PAY_CREATED, $result);
    $this->assertArrayHasKey(SMS_ID, $result);
  }

  public function test_getPayById_false () {
    // Подготовка
    $payId = 1000;
    $this->assertInternalType('integer', $payId);
    // Проверка
    $result = self::$db->getPayById($payId);
    $this->assertFalse($result);
  }


  public function test_setUserSID_true () {
    // Добавление пользователя
    $login = 'login';
    $email = 'mail@mail.ru';
    $pass = 'pass';
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $user = self::$db->addUser($login, $email, $pass, $date, $date);
    $this->assertInternalType('array', $user);
    // Запись USID
    $sid = 'session id';
    $uid = $user[USER_ID];
    $result = self::$db->setUserSID($sid, $uid);
    $this->assertTrue($result);
  }

  public function test_setUserSID_false () {
    $sid = 'session id';
    $uid = 1000;
    $result = self::$db->setUserSID($sid, $uid);
    $this->assertFalse($result);
  }

  public function test_getUserSID_string () {
    // Добавление пользователя
    $login = 'login';
    $email = 'mail@mail.ru';
    $pass = 'pass';
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $user = self::$db->addUser($login, $email, $pass, $date, $date);
    $this->assertInternalType('array', $user);
    // Запись USID
    $sid = 'session id';
    $uid = $user[USER_ID];
    $result = self::$db->setUserSID($sid, $uid);
    $this->assertTrue($result);
    // Получение
    $result = self::$db->getUserSID($uid);
    $this->assertInternalType('string', $result);
    $this->assertEquals($sid, $result);
  }

  public function test_getUserSID_false () {
    $uid = 1000;
    $result = self::$db->getUserSID($uid);
    $this->assertFalse($result);
  }

}
