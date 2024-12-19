<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\LotTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Lot.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class LotTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Lot
     */
    public $lot;

    public function setUp () {
      $lotArray = TestHelper::getLot();
      $this->lot = new Lot($lotArray, true);
    }

    public function tearDown () {

    }

    public function test_getStatusLot_NORMAL () {
      // Проверка
      $result = $this->lot->getStatusLotForAnalysis();
      $this->assertEquals($result, NORMAL);
    }

    public function test_getStatusLot_WARNING () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $smsArray[SMS_COMMENT] = '';
      $sms = new SMS($smsArray);
      /** @var Pay[] $pays */
      $pays = $this->lot->getPays();
      $pays[0]->addFoundSms($sms);
      // Проверка
      $result = $this->lot->getStatusLotForAnalysis();
      $this->assertEquals($result, WARNING);
    }

    public function test_getStatusLot_ERROR () {
      // Подготовка
      $smsArray = TestHelper::getFioSMS();
      $sms = new SMS($smsArray);
      /** @var Pay[] $pays */
      $pays = $this->lot->getPays();
      $pays[0]->addFoundSms($sms);
      // Проверка
      $result = $this->lot->getStatusLotForAnalysis();
      $this->assertEquals($result, ERROR);
    }

    public function test_getCommentOrg_string () {
      // Проверка
      $result = $this->lot->getCommentOrg();
      $this->assertInternalType('string', $result);
      $this->assertEquals($result, 'comment');
    }

    public function test_getTotalPut_float () {
      // Проверка
      $result = $this->lot->getTotalPut();
      $this->assertInternalType('float', $result);
      $this->assertEquals($result, 0);
    }

    public function test_getTotal_float () {
      // Проверка
      $result = $this->lot->getTotal();
      $this->assertInternalType('float', $result);
      $this->assertEquals($result, 116); // 100 + 16%
    }

    public function test_getPays_array () {
      // Проверка
      $result = $this->lot->getPays();
      $this->assertInternalType('array', $result);
    }

    public function test_getUserPurchase_null () {
      // Проверка
      $result = $this->lot->getUserPurchase();
      $this->assertInstanceOf('UserPurchase', $result);
    }

    public function test_isActiveLot_true () {
      // Проверка
      $result = $this->lot->isActiveLot();
      $this->assertTrue($result);
    }

    public function test_isActiveLot_false () {
      // Подготовка
      $lotArray = TestHelper::getLot();
      $lotArray['orders'] = array();
      $lotArray['pays'] = array();
      $lot = new Lot($lotArray, true);
      // Проверка
      $result = $lot->isActiveLot();
      $this->assertFalse($result);
    }

    public function test_isSpecifiedPays_true () {
      // Подготовка
      $lotArray = TestHelper::getLot();
      $lotArray['orders'] = array();
      $lot = new Lot($lotArray, true);
      // Проверка
      $result = $lot->isSpecifiedPays();
      $this->assertTrue($result);
    }

    public function test_isSpecifiedPays_false () {
      // Подготовка
      $lotArray = TestHelper::getLot();
      $lotArray['pays'] = array();
      $lot = new Lot($lotArray, true);
      // Проверка
      $result = $lot->isSpecifiedPays();
      $this->assertFalse($result);
    }

    public function test_getTotalFound_float () {
      // Проверка
      $result = $this->lot->getTotalFound();
      $this->assertInternalType('float', $result);
      $this->assertEquals($result, 50);
    }

    public function test_getTotalPreFound_float () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      $sms = new SMS($smsArray);
      /** @var Pay[] $pays */
      $pays = $this->lot->getPays();
      $pays[0]->addFoundSms($sms);
      // Проверка
      $result = $this->lot->getTotalPreFound();
      $this->assertInternalType('float', $result);
      $this->assertEquals($result, 70);
    }

    public function test_getOrders_array () {
      // Проверка
      $result = $this->lot->getOrders();
      $this->assertInternalType('array', $result);
      $this->assertCount(1, $result);
    }

    public function test_isForFilling_false () {
      // Проверка
      $result = $this->lot->isForFilling();
      $this->assertFalse($result);
    }

    public function test_isForFilling_true () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      $sms = new SMS($smsArray);
      /** @var Pay[] $pays */
      $pays = $this->lot->getPays();
      $pays[0]->addFoundSms($sms);
      $pays[0]->setSelectSms(0);
      // Проверка
      $result = $this->lot->isForFilling();
      $this->assertTrue($result);
    }

    public function test_getTotalForFilling_float () {
      // Подготовка
      $smsArray = TestHelper::getCardSMS();
      $sms = new SMS($smsArray);
      /** @var Pay[] $pays */
      $pays = $this->lot->getPays();
      $pays[0]->addFoundSms($sms);
      $pays[0]->setSelectSms(0);
      // Проверка
      $result = $this->lot->getTotalForFilling();
      $this->assertInternalType('float', $result);
      $this->assertEquals($result, 70);
    }

  }