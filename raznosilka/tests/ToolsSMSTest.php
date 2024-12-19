<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\ToolsSMS.php>
 * @copyright © М. С. Путилов, 2015
 */

  require_once '../classes/ToolsSMS.php';
  require_once '../resources/const.php';

class ToolsSMSTest extends PHPUnit_Framework_TestCase {
  /**
   * @var ToolsSMS
   */
  public $mock;

  public function setUp () {
    $db = $this->getMockBuilder('DataBase')->disableOriginalConstructor()->getMock();
    $user = $this->getMockBuilder('User')->disableOriginalConstructor()->getMock();
    $path = 'Z:\domains\raznosilka';
    $this->mock = $this-> getMockBuilder('ToolsSMS')->setMethods(array('init'))->setConstructorArgs(array($db,$user,$path))->getMock();
  }

  public function tearDown () {

  }

  /**
   * getTemplates
   */

  public function test_getTemplates_useless_array () {
    $result = $this->mock->getTemplates('useless', 'Z:\domains\raznosilka');
    $this->assertInternalType('array', $result);
  }

  public function test_getTemplates_useful_array () {
    $result = $this->mock->getTemplates('useful', 'Z:\domains\raznosilka');
    $this->assertInternalType('array', $result);
  }

  public function test_getTemplates_mark_array () {
    $result = $this->mock->getTemplates('mark', 'Z:\domains\raznosilka');
    $this->assertInternalType('array', $result);
  }

  public function test_getTemplates_Exception () {
    $this->setExpectedException('Exception');
    $this->mock->getTemplates('');
  }

  /**
   * separationGluedSMS
   */

  public function test_separationGluedSMS_array () {
    $sms = TestHelper::getGluedSmsArr();
    $result = $this->mock->separationGluedSMS ($sms);
    // Массивы
    $this->assertInternalType('array', $result);
    $this->assertArrayHasKey('separated', $result);
    $this->assertArrayHasKey('glued', $result);
    $this->assertArrayHasKey('unglued', $result);
    $this->assertInternalType('array', $result['separated']);
    $this->assertInternalType('array', $result['glued']);
    $this->assertInternalType('array', $result['unglued']);
    $this->assertCount(3, $result);
    // Количество СМС
    $this->assertCount(7, $result['separated']);
    $this->assertCount(3, $result['glued']);
    $this->assertCount(6, $result['unglued']);
  }

  /**
   * countInOneSMS
   */

  public function test_countInOneSMS_int_2 () {
    $sms = TestHelper::getGluedSmsArr();
    $result = $this->mock->countInOneSMS ($sms[0][SMS_UNKNOWN_TEXT]);
    $this->assertEquals(2, $result);
  }

  public function test_countInOneSMS_int_1 () {
    $sms = TestHelper::getGluedSmsArr();
    $result = $this->mock->countInOneSMS ($sms[1][SMS_UNKNOWN_TEXT]);
    $this->assertEquals(1, $result);
  }

  /**
   * processedSMS - не понимаю почему, но выдаёт совершенно непонятный результат, скорее всего дело в UTF
   */


}