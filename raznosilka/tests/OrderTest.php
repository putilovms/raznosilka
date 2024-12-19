<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\OrderTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Order.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class OrderTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Order
     */
    public $order;

    public function setUp () {
      $orderArray = TestHelper::getOrder();
      $this->order = new Order($orderArray);
    }

    public function tearDown () {

    }

    public function test_getOrgFee_int () {
      // Проверка
      $result = $this->order->getOrgFee();
      $this->assertInternalType('integer', $result);
      $this->assertEquals(16, $result);
    }

    public function test_getState_int () {
      // Проверка
      $result = $this->order->getState();
      $this->assertInternalType('integer', $result);
      $this->assertEquals(1, $result);
    }

    public function test_getDelivery_float () {
      // Проверка
      $result = $this->order->getDelivery();
      $this->assertInternalType('float', $result);
      $this->assertEquals(0, $result);
    }

    public function test_getComment_string () {
      // Проверка
      $result = $this->order->getComment();
      $this->assertInternalType('string', $result);
      $this->assertEquals('My order', $result);
    }

    public function test_getName_string () {
      // Проверка
      $result = $this->order->getName();
      $this->assertInternalType('string', $result);
      $this->assertEquals('Lot name', $result);
    }

    public function test_getPrice_float () {
      // Проверка
      $result = $this->order->getPrice();
      $this->assertInternalType('float', $result);
      $this->assertEquals(100, $result);
    }

    public function test_getOrderId_int () {
      // Проверка
      $result = $this->order->getOrderId();
      $this->assertInternalType('integer', $result);
      $this->assertEquals(1, $result);
    }

    public function test_isActiveOrder_true () {
      // Проверка
      $result = $this->order->isActiveOrder ();
      $this->assertTrue($result);
    }

    public function test_isActiveOrder_false () {
      // Подготовка
      $orderArray = TestHelper::getOrder();
      $orderArray['state'] = 3;
      $order = new Order($orderArray);
      // Проверка
      $result = $order->isActiveOrder ();
      $this->assertFalse($result);
    }

  }
 