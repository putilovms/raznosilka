<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\CorrectionTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Correction.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class CorrectionTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Correction
     */
    public $correctionObj;

    public function setUp () {
      $correction = TestHelper::getCorrection();
      $this->correctionObj = new Correction($correction);
    }

    public function tearDown () {

    }

    public function test_getCorrectionId_int () {
      $correctionId = $this->correctionObj->getCorrectionId();
      $this->assertInternalType('int', $correctionId);
      $this->assertEquals(1, $correctionId);
    }

    public function test_getCorrectionSum_float () {
      $correctionId = $this->correctionObj->getCorrectionSum();
      $this->assertInternalType('float', $correctionId);
      $this->assertEquals(50, $correctionId);
    }

    public function test_getCorrectionComment_float () {
      $correctionId = $this->correctionObj->getCorrectionComment();
      $this->assertInternalType('string', $correctionId);
      $this->assertEquals('Pay by order', $correctionId);
    }

  }
 