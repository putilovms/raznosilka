<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\TemplateTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Template.php';

  class TemplateTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Template
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Template')->setMethods(array('__construct'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_set_true () {
      $result = $this->mock->set('name', 'value');
      $this->assertTrue($result);
    }

    public function test_set_overwrite_true () {
      $this->mock->set('name', 'value');
      $result = $this->mock->set('name', 'new value', true);
      $this->assertTrue($result);
    }

    public function test_set_overwrite_exception () {
      $this->setExpectedException('Exception');
      $this->mock->set('name', 'value');
      $this->mock->set('name', 'new value');
    }

    public function test_remove_true () {
      $this->mock->set('name', 'value');
      $result = $this->mock->remove('name');
      $this->assertTrue($result);
    }

    public function test_remove_false () {
      $result = $this->mock->remove('name');
      $this->assertFalse($result);
    }

    public function test_setTitle () {
      $this->mock->setTitle('title');
    }
  }
 