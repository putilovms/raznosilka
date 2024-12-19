<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Registry_RequestTest.php>
 * @copyright © М. С. Путилов, 2015
 */

  require_once '../classes/registry/Request.php';
  require_once '../classes/Registry.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

class Registry_RequestTest extends PHPUnit_Framework_TestCase {
  /**
   * @var Registry_Request
   */
  public $mock;

  public function setUp () {
    $this->mock = Registry_Request::instance();
  }

  public function tearDown () {

  }

  public function test_set() {
    $value = true;
    $this->mock->set('test_set', $value);
  }

  public function test_set_exception () {
    // Подготовка
    $value = true;
    $this->mock->set('test_set_exception', $value);
    // Проверка
    $this->setExpectedException('Exception');
    $this->mock->set('test_set_exception', $value);
  }

  public function test_get_true () {
    $value = true;
    $this->mock->set('test_get', $value);
    // Проверка
    $result = $this->mock->get('test_get');
    $this->assertEquals($result, $value);
  }

  public function test_get_exception () {
    // Проверка
    $this->setExpectedException('Exception');
    $this->mock->get('test_get_exception');
  }

  public function test_getAll_array () {
    $result = $this->mock->getAll();
    $this->assertInternalType('array', $result);
    $this->assertNotEmpty($result);
  }

}
 