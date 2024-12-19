<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\SMSTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/SMS.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class SMSTest extends PHPUnit_Framework_TestCase {
    /**
     * @var SMS
     */
    public $sms;

    public function setUp () {
      $smsArray = TestHelper::getCardSMS();
      $this->sms = new SMS($smsArray);
    }

    public function tearDown () {

    }

    public function test_getStatusSMS_NORMAL () {
      // Проверка
      $result = $this->sms->getStatusSMSForAnalysis();
      $this->assertEquals(NORMAL, $result);
    }

    public function test_getStatusSMS_ERROR () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      // Проверка
      $result = $sms->getStatusSMSForAnalysis();
      $this->assertEquals(ERROR, $result);
    }

    public function test_getStatusSMS_WARNING () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $smsArray[SMS_COMMENT] = '';
      $sms = new SMS($smsArray);
      // Проверка
      $result = $sms->getStatusSMSForAnalysis();
      $this->assertEquals(WARNING, $result);
    }

    public function test_isSure_true () {
      // Проверка
      $result = $this->sms->isSure();
      $this->assertTrue($result);
    }

    public function test_isComment_true () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      // Проверка
      $result = $sms->isComment();
      $this->assertTrue($result);
    }

    public function test_isComment_false () {
      // Проверка
      $result = $this->sms->isComment();
      $this->assertFalse($result);
    }

    public function test_getIdPay_int () {
      // Проверка
      $result = $this->sms->getIdPay();
      $this->assertInternalType('integer', $result);
      $this->assertEquals(0, $result);
    }

    public function test_getReturn_false () {
      // Проверка
      $result = $this->sms->isReturn();
      $this->assertInternalType('boolean', $result);
      $this->assertFalse($result);
    }

    public function test_getComment_string () {
      // Проверка
      $result = $this->sms->getComment();
      $this->assertInternalType('string', $result);
    }

    public function test_getFio_string () {
      // Проверка
      $result = $this->sms->getFio();
      $this->assertInternalType('string', $result);
    }

    public function test_getCard_string () {
      // Проверка
      $result = $this->sms->getCard();
      $this->assertInternalType('string', $result);
    }

    public function test_getSum_float () {
      // Проверка
      $result = $this->sms->getSum();
      $this->assertInternalType('float', $result);
      $this->assertEquals(20, $result);
    }

    public function test_getIdSms_integer () {
      // Проверка
      $result = $this->sms->getIdSms();
      $this->assertInternalType('integer', $result);
      $this->assertEquals(1, $result);
    }

    public function test_getTimeSms_string () {
      // Проверка
      $result = $this->sms->getTimeSms();
      $this->assertInternalType('string', $result);
      $this->assertEquals('2015-01-01 00:00:00', $result);
    }

    public function test_getTimePay_string () {
      // Проверка
      $result = $this->sms->getTimePay();
      $this->assertInternalType('string', $result);
      $this->assertEquals('2015-01-02 00:00:00', $result);
    }

    public function test_getDiffTimeOfPay_null () {
      // Проверка
      $result = $this->sms->getDiffTimeOfPay();
      $this->assertNull($result);
    }

    public function test_setDiffTimeOfPay_int () {
      // Подготовка
      $diff = 10;
      $this->sms->setDiffTimeOfPay($diff);
      // Проверка
      $result = $this->sms->getDiffTimeOfPay();
      $this->assertInternalType('integer', $result);
      $this->assertEquals($diff, $result);
    }

    public function test_isDiverOfTime_false () {
      // Подготовка
      $diff = (SMS::FORK_MINUTES * 60) - 1;
      $this->sms->setDiffTimeOfPay($diff);
      // Проверка
      $result = $this->sms->isDiverOfTime();
      $this->assertFalse($result);
    }

    public function test_isDiverOfTime_true () {
      // Подготовка
      $diff = (SMS::FORK_MINUTES * 60) + 1;
      $this->sms->setDiffTimeOfPay($diff);
      // Проверка
      $result = $this->sms->isDiverOfTime();
      $this->assertTrue($result);
    }

    public function test_getPayer_card_string () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      // Проверка
      $result = $this->sms->getPayer();
      $this->assertInternalType('string', $result);
      $this->assertEquals($smsArray[SMS_CARD_PAYER], $result);
    }

    public function test_getPayer_fio_string () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      // Проверка
      $result = $sms->getPayer();
      $this->assertInternalType('string', $result);
      $this->assertEquals($smsArray[SMS_FIO], $result);
    }

    public function test_getTime_timePay_string () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      // Проверка
      $result = $this->sms->getTime();
      $this->assertInternalType('string', $result);
      $this->assertEquals($smsArray[SMS_TIME_PAY], $result);
    }

    public function test_getTime_smsPay_string () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      // Проверка
      $result = $sms->getTime();
      $this->assertInternalType('string', $result);
      $this->assertEquals($smsArray[SMS_TIME_SMS], $result);
    }

  }