<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\InfoTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Info.php';

  class InfoTest extends PHPUnit_Framework_TestCase {

    public function setUp() {

    }

    public function tearDown() {

    }

    public function test_getTimeWork_isFloat() {
      $info = new Info();
      $time = $info->getTimeWork();
      $this->assertInternalType('float', $time);
    }

    public function test_getTimePiece_isFloat() {
      $info = new Info();
      // Первый запуск
      $time = $info->getTimePiece();
      $this->assertInternalType('float', $time);
      // Повторный запуск
      $time = $info->getTimePiece();
      $this->assertInternalType('float', $time);
    }

    public function test_getMemoryUsage_isFloat() {
      $class = new Info();
      $result = $class->getMemoryUsage();
      $this->assertInternalType('float', $result);
    }
  }
 