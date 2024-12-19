<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\PayTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Pay.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class PayTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Pay
     */
    public $pay;

    public function setUp () {
      $payArray = TestHelper::getPay();
      $this->pay = new Pay($payArray);
    }

    public function tearDown () {

    }

    public function test_isSelectSms_false () {
      // Проверка
      $result = $this->pay->isSelectSms();
      $this->assertFalse($result);
    }

    public function test_getStatusPay_NORMAL () {
      // Проверка
      $result = $this->pay->getStatusPayForAnalysis();
      $this->assertEquals($result, NORMAL);
    }

    public function test_getStatusPay_WARNING () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $smsArray[SMS_COMMENT] = '';
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Проверка
      $result = $this->pay->getStatusPayForAnalysis();
      $this->assertEquals($result, WARNING);
    }

    public function test_getStatusPay_ERROR () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Проверка
      $result = $this->pay->getStatusPayForAnalysis();
      $this->assertEquals($result, ERROR);
    }

    public function test_isSure_false () {
      // Проверка
      $result = $this->pay->isSure();
      $this->assertFalse($result);
    }

    public function test_getIdUsedSms_array () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Проверка
      $result = $this->pay->getIdUsedSms();
      $this->assertInternalType('array', $result);
      $this->assertNotEmpty($result);
    }

    public function test_isFilling_false () {
      // Проверка
      $result = $this->pay->isFilling();
      $this->assertFalse($result);
    }

    public function test_isHasSms_false () {
      // Проверка
      $result = $this->pay->isHasFoundSms();
      $this->assertFalse($result);
    }

    public function test_getTimePay_string () {
      // Подготовка
      $payArray = TestHelper::getPay();
      // Проверка
      $result = $this->pay->getTimePay();
      $this->assertInternalType('string', $result);
      $this->assertEquals($payArray[PAY_TIME], $result);
    }

    public function test_getSum_float () {
      // Проверка
      $result = $this->pay->getSum();
      $this->assertInternalType('float', $result);
      $this->assertEquals(10, $result);
    }

    public function test_getCard_string () {
      // Подготовка
      $payArray = TestHelper::getPay();
      // Проверка
      $result = $this->pay->getCard();
      $this->assertInternalType('string', $result);
      $this->assertEquals($payArray[PAY_CARD_PAYER], $result);
    }

    public function test_getTimeCreatedPay_string () {
      // Подготовка
      $payArray = TestHelper::getPay();
      // Проверка
      $result = $this->pay->getTimeCreatedPay();
      $this->assertInternalType('string', $result);
      $this->assertEquals($payArray[PAY_CREATED], $result);
    }

    public function test_getSelectSms_null () {
      // Проверка
      $result = $this->pay->getSelectSms();
      $this->assertNull($result);
    }

    public function test_getIdPay_null () {
      // Проверка
      $result = $this->pay->getIdPay();
      $this->assertNull($result);
    }

    public function test_isError_null () {
      // Проверка
      $result = $this->pay->isError();
      $this->assertNull($result);
    }

    public function test_getFillingSms_null () {
      // Проверка
      $result = $this->pay->getFillingSms();
      $this->assertNull($result);
    }

    public function test_getFoundSms_array () {
      // Проверка
      $result = $this->pay->getFoundSms();
      $this->assertInternalType('array', $result);
    }

    public function test_addFoundSms_sureSms () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Проверка
      $result = $this->pay->isHasFoundSms();
      $this->assertTrue($result);
      $result = $this->pay->isSure();
      $this->assertTrue($result);
    }

    public function test_addFoundSms_notSureSms () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Проверка
      $result = $this->pay->isHasFoundSms();
      $this->assertTrue($result);
      $result = $this->pay->isSure();
      $this->assertFalse($result);
    }

    public function test_setSelectSms () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      $this->pay->setSelectSms(0);
      // Проверка
      $result = $this->pay->isHasFoundSms();
      $this->assertFalse($result);
      $result = $this->pay->isSelectSms();
      $this->assertTrue($result);
    }

    public function test_analyseSms_sureSms () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Предпроверка
      $result = $this->pay->getFoundSms();
      $this->assertInternalType('array', $result);
      $this->assertCount(2, $result);
      // Проверка
      $this->pay->analyseSms();
      $result = $this->pay->isHasFoundSms();
      $this->assertTrue($result);
      $result = $this->pay->isSure();
      $this->assertTrue($result);
      $result = $this->pay->getFoundSms();
      $this->assertInternalType('array', $result);
      $this->assertCount(1, $result);
    }

    public function test_analyseSms_notSureSms () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      $this->pay->addFoundSms($sms);
      // Проверка
      $this->pay->analyseSms();
      $result = $this->pay->isHasFoundSms();
      $this->assertTrue($result);
      $result = $this->pay->isSure();
      $this->assertFalse($result);
    }

  }
 