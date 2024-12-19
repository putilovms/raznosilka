<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\LogsTest.php>
 * @copyright © М. С. Путилов, 2015
 */

  require_once '../classes/Logs.php';
  require_once '../resources/const.php';

class LogsTest extends PHPUnit_Framework_TestCase {
  /**
   * @var Logs
   */
  public $mock;
  /**
   * @var User
   */
  public $stub;

  public function setUp () {
    $this->mock = $this->getMockBuilder('Logs')->setMethods(array('createLogFile','rotateLogFile','putCsvLogFile'))->disableOriginalConstructor()->getMock();
    $this->stub = $this->getMockBuilder('User')->setMethods(array('getUserInfo'))->disableOriginalConstructor()->getMock();
  }

  public function tearDown () {

  }

  public function test_loginLog_to_putCsvLogFile_string_and_array () {
    $user = TestHelper::getUserInfo();
    $this->stub->expects($this->once())->method('getUserInfo')->will($this->returnValue($user));
    $this->mock->expects($this->once())->method('putCsvLogFile')->with($this->isType('string'),$this->isType('array'));
    $this->mock->actionLog($this->stub);
  }

  public function test_loginLog_to_createLogFile_string () {
    $user = TestHelper::getUserInfo();
    $this->stub->expects($this->once())->method('getUserInfo')->will($this->returnValue($user));
    $this->mock->expects($this->once())->method('createLogFile')->with($this->isType('string'));
    $this->mock->actionLog($this->stub);
  }

  public function test_loginLog_to_rotateLogFile_string () {
    $user = TestHelper::getUserInfo();
    $this->stub->expects($this->once())->method('getUserInfo')->will($this->returnValue($user));
    $this->mock->expects($this->once())->method('rotateLogFile')->with($this->isType('string'));
    $this->mock->actionLog($this->stub);
  }
}
 