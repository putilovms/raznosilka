<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\NotifyTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Notify.php';
  require_once '../resources/const.php';

  class NotifyTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Notify
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Notify')->setMethods(array('getNotifyFromRegistry', 'delAllNotify','setNotifyInRegistry'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_getAllNotify_empty () {
      $this->mock->expects($this->once())->method('getNotifyFromRegistry')->will($this->returnValue(null));
      $result = $this->mock->getAllNotify();
      $this->assertInternalType('array', $result);
      $this->assertCount(0, $result);
    }

    public function test_getAllNotify_notEmpty () {
      $notify = array(array('type' => ERROR_NOTIFY, 'text' => 'Error'));
      $this->mock->expects($this->once())->method('getNotifyFromRegistry')->will($this->returnValue($notify));
      $result = $this->mock->getAllNotify();
      $this->assertInternalType('array', $result);
      $this->assertCount(1, $result);
    }

    public function test_sendNotify () {
      $notify = array(array('type' => ERROR_NOTIFY, 'text' => 'Error'));
      $this->mock->expects($this->once())->method('getNotifyFromRegistry')->will($this->returnValue($notify));
      $this->mock->expects($this->once())->method('setNotifyInRegistry')->with($this->anything());
      $this->mock->sendNotify(SUCCESS_NOTIFY, 'Okay');
    }

  }
 