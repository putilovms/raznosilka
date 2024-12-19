<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\UserPurchaseTest.php>
 * @copyright © М. С. Путилов, 2015
 */

  require_once '../classes/UserPurchase.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

class UserPurchaseTest extends PHPUnit_Framework_TestCase {
  /**
   * @var UserPurchase
   */
  public $userPurchase;

  public function setUp () {
    $userPurchaseArr = TestHelper::getUserPurchase();
    $this->userPurchase = new UserPurchase($userPurchaseArr, true);
  }

  public function tearDown () {

  }

  public function test_getUserPurchaseId_int () {
    // Проверка
    $result = $this->userPurchase->getUserPurchaseId();
    $this->assertInternalType('int', $result);
    $this->assertEquals(1, $result);
  }

  public function test_getFio_string () {
    // Проверка
    $result = $this->userPurchase->getFio();
    $this->assertInternalType('string', $result);
  }

  public function test_getNick_string () {
    // Проверка
    $result = $this->userPurchase->getNick();
    $this->assertInternalType('string', $result);
  }

  public function test_getUrl_string () {
    // Проверка
    $result = $this->userPurchase->getUrl();
    $this->assertInternalType('string', $result);
  }

}
 