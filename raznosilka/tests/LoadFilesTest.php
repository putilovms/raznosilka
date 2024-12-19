<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\LoadFilesTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/LoadFiles.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class LoadFilesTest extends PHPUnit_Framework_TestCase {
    /**
     * @var LoadFiles
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('LoadFiles')->setMethods(array('getTmpPath'))->setConstructorArgs(array('Z:/domains/raznosilka/tests/tmp'))->getMock();
    }

    public function tearDown () {

    }

    /**
     * conversionFilesArr
     */

    public function test_conversionFilesArr_array () {
      $files = TestHelper::getFilesArr();
      $result = $this->mock->conversionFilesArr($files);
      $this->assertInternalType('array', $result);
    }

    /**
     * setMessage и getMessage
     */

    public function test_setMessage_and_getMessage () {
      $this->mock->setMessage('message', ERROR_NOTIFY);
      $result = $this->mock->getMessage();
      // Проверка
      $this->assertInternalType('array', $result);
      $this->assertCount(1, $result);
      $this->assertContains('message', $result[0]);
      $this->assertContains(ERROR_NOTIFY, $result[0]);
    }

    /**
     * preCheckFiles
     */

    public function test_preCheckFiles_array () {
      // Подготовка
      $files = TestHelper::getFilesArr();
      $count = count($files['name']);
      $files = $this->mock->conversionFilesArr($files);
      $this->assertCount($count, $files);
      $result = $this->mock->preCheckFiles($files);
      // Проверка
      $this->assertInternalType('array', $result);
      $this->assertCount(4, $result);
    }

    /**
     * printMessagesAsNotify
     */

    public function test_printMessagesAsNotify () {
      $this->mock->setMessage('message', ERROR_NOTIFY);
      $stub = $this->getMockBuilder('Notify')->setMethods(array('getNotifyFromRegistry', 'delAllNotify','setNotifyInRegistry'))->disableOriginalConstructor()->getMock();
      $stub->expects($this->once())->method('setNotifyInRegistry')->with($this->anything());
      $this->mock->printMessagesAsNotify($stub);
    }

    /**
     * saveFiles
     */

    public function test_saveFiles_array () {
      $files = TestHelper::getFilesArr();
      $files = $this->mock->conversionFilesArr($files);
      $files = $this->mock->preCheckFiles($files);
      $count = count($files);
      // Проверка
      $result = $this->mock->saveFiles($files, true);
      $this->assertInternalType('array', $result);
      $this->assertCount($count, $result);
    }

    /**
     * postCheckFiles
     */

    public function test_postCheckFiles_array () {
      $files = TestHelper::getFilesArr();
      $files = $this->mock->conversionFilesArr($files);
      $files = $this->mock->preCheckFiles($files);
      $files = $this->mock->saveFiles($files, true);
      // Проверка
      $result = $this->mock->postCheckFiles($files, true);
      $this->assertInternalType('array', $result);
      $this->assertCount(2, $result);
    }

    /**
     * loadSms и destruct
     */

    public function test_loadSms_noFiles_false () {
      $files = array();
      $result = $this->mock->loadSms($files);
      $this->assertFalse($result);
    }

    public function test_loadSms_noFiles_array () {
      $files = TestHelper::getFilesArr();
      $result = $this->mock->loadSms($files, true);
      $this->assertInternalType('array', $result);
      $this->assertCount(2, $result);
      $this->mock->__destruct();
    }

    /**
     * getErrorFiles
     */

    public function test_getErrorFiles_array () {
      $files = TestHelper::getFilesArr();
      $this->mock->loadSms($files, true);
      $result = $this->mock->getErrorFiles();
      $this->assertInternalType('array', $result);
      $this->assertCount(5, $result);
    }

    /**
     * getLoadFiles
     */

    public function test_getLoadFiles_array () {
      $files = TestHelper::getFilesArr();
      $this->mock->loadSms($files, true);
      $result = $this->mock->getLoadFiles();
      $this->assertInternalType('array', $result);
      $this->assertCount(7, $result);
    }

  }