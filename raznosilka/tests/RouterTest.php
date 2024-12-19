<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\RouterTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Router.php';

  class RouterTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Router
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Router')->setMethods(array('runController','getClass','checkMethod'))->getMock();
    }

    public function tearDown () {

    }
//    /**
//     * @dataProvider runController_controller_method
//     */
    public function test_runController_empty () {
      $stub = $this->getMockBuilder('Controller_Index')->getMock();

      $this->mock->expects($this->once())->method('getClass')->will($this->returnValue($stub))->with($this->identicalTo('controller_index'));
      $this->mock->expects($this->once())->method('checkMethod')->will($this->returnValue(true))->with($this->identicalTo($stub),$this->identicalTo('index'));
      $this->mock->expects($this->once())->method('runController')->with($this->identicalTo($stub),$this->identicalTo('index'));

      $provider = '/';
      $this->mock->findController($provider);
    }

    public function test_runController_controller_found () {
      $stub = $this->getMockBuilder('Controller_User')->getMock();

      $this->mock->expects($this->once())->method('getClass')->will($this->returnValue($stub))->with($this->identicalTo('controller_user'));
      $this->mock->expects($this->once())->method('checkMethod')->will($this->returnValue(true))->with($this->identicalTo($stub),$this->identicalTo('index'));
      $this->mock->expects($this->once())->method('runController')->with($this->identicalTo($stub),$this->identicalTo('index'));

      $provider = '/user';
      $this->mock->findController($provider);
    }

    public function test_runController_controller_notFound () {
      $stub = $this->getMockBuilder('Controller_Error')->getMock();

      $this->mock->expects($this->at(0))->method('getClass')->will($this->returnValue(false))->with($this->identicalTo('controller_user'));
      $this->mock->expects($this->never())->method('checkMethod');
      $this->mock->expects($this->at(1))->method('getClass')->will($this->returnValue($stub))->with($this->identicalTo('controller_error'));
      $this->mock->expects($this->at(2))->method('runController')->with($this->identicalTo($stub),$this->identicalTo('notFound'));

      $provider = '/user';
      $this->mock->findController($provider);
    }

    public function test_runController_controller_method () {
      $stub = $this->getMockBuilder('Controller_User')->getMock();

      $this->mock->expects($this->once())->method('getClass')->will($this->returnValue($stub))->with($this->identicalTo('controller_user'));
      $this->mock->expects($this->once())->method('checkMethod')->will($this->returnValue(true))->with($this->identicalTo($stub),$this->identicalTo('register'));
      $this->mock->expects($this->once())->method('runController')->with($this->identicalTo($stub),$this->identicalTo('register'));

      $provider = '/user/register';
      $this->mock->findController($provider);
    }

    public function test_runController_controller_method_notFound () {
      $stub = $this->getMockBuilder('Controller_User')->getMock();
      $stubError = $this->getMockBuilder('Controller_Error')->getMock();

      $this->mock->expects($this->at(0))->method('getClass')->will($this->returnValue($stub))->with($this->identicalTo('controller_user'));
      $this->mock->expects($this->at(1))->method('checkMethod')->will($this->returnValue(false))->with($this->identicalTo($stub),$this->identicalTo('register'));
      $this->mock->expects($this->at(2))->method('getClass')->will($this->returnValue($stubError))->with($this->identicalTo('controller_error'));
      $this->mock->expects($this->at(3))->method('runController')->with($this->identicalTo($stubError),$this->identicalTo('notFound'));

      $provider = '/user/register';
      $this->mock->findController($provider);
    }

    public function test_runController_invalid () {
      $stub = $this->getMockBuilder('Controller_Error')->getMock();

      $this->mock->expects($this->never())->method('checkMethod');
      $this->mock->expects($this->at(0))->method('getClass')->will($this->returnValue($stub))->with($this->identicalTo('controller_error'));
      $this->mock->expects($this->at(1))->method('runController')->with($this->identicalTo($stub),$this->identicalTo('notFound'));

      $provider = '/user/register/mode';
      $this->mock->findController($provider);
    }

  }


