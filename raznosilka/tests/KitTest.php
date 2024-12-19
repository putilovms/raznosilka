<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\KitTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Kit.php';
  require_once '../resources/const.php';

  class KitTest extends PHPUnit_Framework_TestCase {
    public function setUp () {

    }

    public function tearDown () {

    }

    public function test_UW () {
      $uString = 'абвгд';
      $wString = iconv('UTF-8', 'WINDOWS-1251', $uString);
      $result = Kit::UW($uString);
      $this->assertEquals($wString, $result);
      $this->assertNotEquals($uString, $result);
    }

    public function test_WU () {
      $uString = 'абвгд';
      $wString = iconv('UTF-8', 'WINDOWS-1251', $uString);
      $result = Kit::WU($wString);
      $this->assertEquals($uString, $result);
      $this->assertNotEquals($wString, $result);
    }

    public function test_arrWU () {
      $uString = 'абвгд';
      $wString = iconv('UTF-8', 'WINDOWS-1251', $uString);
      $arr[] = $wString;
      $result = Kit::arrWU($arr);
      $this->assertNotContains($wString, $result);
      $this->assertContains($uString, $result);
    }

    public function test_arrUW () {
      $uString = 'абвгд';
      $wString = iconv('UTF-8', 'WINDOWS-1251', $uString);
      $arr[] = $uString;
      $result = Kit::arrUW($arr);
      $this->assertNotContains($uString, $result);
      $this->assertContains($wString, $result);
    }

    public function test_textСut () {
      $string = 'abcde';
      $result = Kit::textСut($string, 3);
      $this->assertEquals('abc', $result);
      $this->assertEquals('de', $string);
    }

    public function test_plainText () {
      $string = " <a>a\r b\t c\n d\r\ne</a> ";
      $result = Kit::plainText($string);
      $this->assertEquals('a b c d e', $result);
    }

    /**
     * @dataProvider isInt_true
     */
    public function test_isInt_true ($provider) {
      $result = Kit::isInt($provider);
      $this->assertTrue($result);
    }

    public function isInt_true () {
      $arr[] = array('0');
      $arr[] = array('1');
      return $arr;
    }

    /**
     * @dataProvider isInt_false
     */
    public function test_isInt_false ($provider) {
      $result = Kit::isInt($provider);
      $this->assertFalse($result);
    }

    public function isInt_false () {
      $arr[] = array('O');
      $arr[] = array('qwerty');
      $arr[] = array('1q');
      $arr[] = array('1.1');
      return $arr;
    }

    public function test_getRefererURL_false () { // todo как-то проверить урл с которого пришли
      $result = Kit::getRefererURL ();
      $this->assertFalse($result);
    }

  }