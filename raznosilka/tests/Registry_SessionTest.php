<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Registry_SessionTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/registry/Session.php';
  require_once '../classes/Registry.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class Registry_SessionTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Registry_Session
     */
    public $mock;

    public function setUp () {
      $this->mock = Registry_Session::instance();
    }

    public function tearDown () {

    }

    public function test_test () {

    }
  }
 