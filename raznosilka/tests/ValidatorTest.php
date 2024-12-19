<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\ValidatorTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Validator.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  // todo разобраться как работать с кириллицей

  /**
   * validateLogin
   */
  class ValidateLoginTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Validator')->setMethods(array('getUserByLogin'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_validateLogin_empty () {
      $standard = array('validate' => false, 'message' => null, 'class' => null);
      $result = $this->mock->validateLogin('');

      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    public function test_validateLogin_pattern_true () {
      $provider = 'Mickle Putilov';
      $result = $this->mock->validateLogin($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function test_validateLogin_pattern_false () {
      $provider = 'Mickle !!!';
      $result = $this->mock->validateLogin($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    /**
     * @dataProvider validateLogin_length_true
     */
    public function test_validateLogin_length_true ($provider) {
      $result = $this->mock->validateLogin($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function validateLogin_length_true () {
      // Не валидные
      $arr[] = array(str_repeat('a', Validator::minLoginLen));
      $arr[] = array(str_repeat('a', Validator::maxLoginLen));
      return $arr;
    }

    /**
     * @dataProvider validateLogin_length_false
     */
    public function test_validateLogin_length_false ($provider) {
      $result = $this->mock->validateLogin($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function validateLogin_length_false () {
      // Не валидные
      $arr[] = array(str_repeat('a', Validator::minLoginLen - 1));
      $arr[] = array(str_repeat('a', Validator::maxLoginLen + 1));
      return $arr;
    }

    public function test_validateLogin_user_true () {
      $this->mock->expects($this->once())->method('getUserByLogin')->will($this->returnValue(null));
      $provider = 'Mickle Putilov';
      $result = $this->mock->validateLogin($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function test_validateLogin_user_false () {
      // Имитация найденного пользователя
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserByLogin')->will($this->returnValue($user));
      $provider = 'Mickle Putilov';
      $result = $this->mock->validateLogin($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }
  }

  /**
   * validateEmail
   */
  class ValidateEmailTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Validator')->setMethods(array('getUserByEmail'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_validateEmail_empty () {
      $standard = array('validate' => false, 'message' => null, 'class' => null);
      $result = $this->mock->validateEmail('');

      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    /**
     * @dataProvider validateEmail_pattern_true
     */
    public function test_validateEmail_pattern_true ($provider) {
      $result = $this->mock->validateEmail($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function validateEmail_pattern_true () {
      // Валидные
      $arr[] = array('1@1.1');
      $arr[] = array('mickle@putilov.ru');
      return $arr;
    }

    /**
     * @dataProvider validateEmail_pattern_false
     */
    public function test_validateEmail_pattern_false ($provider) {
      $result = $this->mock->validateEmail($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function validateEmail_pattern_false () {
      // Не валидные
      $arr[] = array('1.1@1');
      $arr[] = array('1.1');
      $arr[] = array('1@1');
      $arr[] = array('1');
      return $arr;
    }

    public function test_validateEmail_length_false () {
      $provider = str_repeat('a', Validator::maxEmailLen + 1);
      $result = $this->mock->validateEmail($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function test_validateEmail_userRegister_true () {
      $this->mock->expects($this->once())->method('getUserByEmail')->will($this->returnValue(null));
      $provider = '1@1.1';
      $result = $this->mock->validateEmail($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function test_validateEmail_userRegister_false () {
      // Имитация найденного пользователя
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserByEmail')->will($this->returnValue($user));
      $provider = '1@1.1';
      $result = $this->mock->validateEmail($provider);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function test_validateEmail_userFagot_true () {
      // Имитация найденного пользователя
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserByEmail')->will($this->returnValue($user));
      $provider = '1@1.1';
      $result = $this->mock->validateEmail($provider, true);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function test_validateEmail_userFagot_false () {
      $this->mock->expects($this->once())->method('getUserByEmail')->will($this->returnValue(null));
      $provider = '1@1.1';
      $result = $this->mock->validateEmail($provider, true);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }
  }

  class ValidatePassTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Validator')->setMethods(array('__construct'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    /**
     * @dataProvider validatePass_empty
     */
    public function test_validatePass_empty ($a, $b) {
      $standard = array('validate' => false, 'message' => null, 'class' => null);
      $result = $this->mock->validatePass($a, $b);

      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    public function validatePass_empty () {
      $arr[] = array('', '');
      $arr[] = array('123456', '');
      $arr[] = array('', '123456');
      return $arr;
    }

    public function test_validatePass_match_true () {
      $a = 'password';
      $b = 'password';
      $result = $this->mock->validatePass($a, $b);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function test_validatePass_match_false () {
      $a = 'password';
      $b = 'PASSWORD';
      $result = $this->mock->validatePass($a, $b);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    /**
     * @dataProvider validatePass_length_true
     */
    public function test_validatePass_length_true ($a, $b) {
      $result = $this->mock->validatePass($a, $b);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function validatePass_length_true () {
      // валидные
      $arr[] = array(str_repeat('a', Validator::minPassLen), str_repeat('a', Validator::minPassLen));
      $arr[] = array(str_repeat('a', Validator::maxPassLen), str_repeat('a', Validator::maxPassLen));
      return $arr;
    }

    /**
     * @dataProvider validatePass_length_false
     */
    public function test_validatePass_length_false ($a, $b) {
      $result = $this->mock->validatePass($a, $b);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function validatePass_length_false () {
      // Не валидные
      $arr[] = array(str_repeat('a', Validator::minPassLen - 1), str_repeat('a', Validator::minPassLen - 1));
      $arr[] = array(str_repeat('a', Validator::maxPassLen + 1), str_repeat('a', Validator::maxPassLen + 1));
      return $arr;
    }

  }

  class ValidateUserRegistrationTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Validator')->setMethods(array('checkUserRegistration'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    /**
     * @dataProvider validateUserRegistration_empty
     */
    public function test_validateUserRegistration_empty ($a, $b) {
      $standard = array('validate' => false, 'message' => null, 'user' => null, 'class' => null);
      $result = $this->mock->validateUserRegistration($a, $b);

      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    public function validateUserRegistration_empty () {
      $arr[] = array('', '');
      $arr[] = array('login', '');
      $arr[] = array('', 'password');
      return $arr;
    }

    public function test_validateUserRegistration_user_true () {
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('checkUserRegistration')->will($this->returnValue($user));
      $a = 'login';
      $b = $user[USER_PASSWORD];
      $result = $this->mock->validateUserRegistration($a, $b);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertEquals($user, $element);
      $element = array_shift($result);
      $this->assertNull($element);
    }

    public function test_validateUserRegistration_user_false () {
      $this->mock->expects($this->once())->method('checkUserRegistration')->will($this->returnValue(null));
      $a = 'login';
      $b = 'password';
      $result = $this->mock->validateUserRegistration($a, $b);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertNull($element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

  }

  class ValidateChangePasswordTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Validator')->setMethods(array('getUserById'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    /**
     * @dataProvider validateChangePassword_empty
     */
    public function test_validateChangePassword_empty ($a, $b, $c) {
      $standard = array('validate' => false, 'message' => null, 'class' => null);
      $result = $this->mock->validateChangePassword($a, $b, $c);

      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    public function validateChangePassword_empty () {
      $arr[] = array('', '', '');
      $arr[] = array('', '123456', '');
      $arr[] = array('', '', '123456');
      $arr[] = array('', '123456', '123456');
      return $arr;
    }

    public function test_validateChangePassword_user_true () {
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $a = 'password';
      $b = 'password new';
      $c = 'password new';
      $result = $this->mock->validateChangePassword($a, $b, $c);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('success', $element);
    }

    public function test_validateChangePassword_user_false () {
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $a = 'PASSWORD';
      $b = 'password new';
      $c = 'password new';
      $result = $this->mock->validateChangePassword($a, $b, $c);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    /**
     * @dataProvider validateChangePassword_length_true
     */
    public function test_validateChangePassword_length_true ($a, $b, $c) {
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $result = $this->mock->validateChangePassword($a, $b, $c);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('success', $element);
    }

    public function validateChangePassword_length_true () {
      $user = TestHelper::getUser();
      // валидные
      $arr[] = array($user[USER_PASSWORD], str_repeat('a', Validator::minPassLen), str_repeat('a', Validator::minPassLen));
      $arr[] = array($user[USER_PASSWORD], str_repeat('a', Validator::maxPassLen), str_repeat('a', Validator::maxPassLen));
      return $arr;
    }

    /**
     * @dataProvider validateChangePassword_length_false
     */
    public function test_validateChangePassword_length_false ($a, $b, $c) {
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $result = $this->mock->validateChangePassword($a, $b, $c);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function validateChangePassword_length_false () {
      $user = TestHelper::getUser();
      // валидные
      $arr[] = array($user[USER_PASSWORD], str_repeat('a', Validator::minPassLen - 1), str_repeat('a', Validator::minPassLen - 1));
      $arr[] = array($user[USER_PASSWORD], str_repeat('a', Validator::maxPassLen + 1), str_repeat('a', Validator::maxPassLen + 1));
      return $arr;
    }

    public function test_validateChangePassword_match_false () {
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $a = $user[USER_PASSWORD];
      $b = 'password1';
      $c = 'password2';
      $result = $this->mock->validateChangePassword($a, $b, $c);

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }
  }

  class ValidateRegisterFormTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $methods = array('validateLogin', 'validateEmail', 'validatePass');
      $this->mock = $this->getMockBuilder('Validator')->setMethods($methods)->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_validateRegisterForm_true () {
      $return = array('validate' => true, 'message' => null, 'class' => null);
      $this->mock->expects($this->once())->method('validateLogin')->will($this->returnValue($return));
      $this->mock->expects($this->once())->method('validateEmail')->will($this->returnValue($return));
      $this->mock->expects($this->once())->method('validatePass')->will($this->returnValue($return));
      $result = $this->mock->validateRegisterForm('', '', '', '');

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertTrue($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
    }

    /**
     * @dataProvider validateRegisterForm
     */
    public function test_validateRegisterForm_false ($a, $b, $c) {
      $this->mock->expects($this->once())->method('validateLogin')->will($this->returnValue($a));
      $this->mock->expects($this->once())->method('validateEmail')->will($this->returnValue($b));
      $this->mock->expects($this->once())->method('validatePass')->will($this->returnValue($c));
      $result = $this->mock->validateRegisterForm('', '', '', '');

      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
    }

    public function validateRegisterForm () {
      //не валидные
      $arr[] = array(array('validate' => false, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null));
      $arr[] = array(array('validate' => false, 'message' => null, 'class' => null), array('validate' => true, 'message' => null, 'class' => null), array('validate' => true, 'message' => null, 'class' => null));
      $arr[] = array(array('validate' => true, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null), array('validate' => true, 'message' => null, 'class' => null));
      $arr[] = array(array('validate' => true, 'message' => null, 'class' => null), array('validate' => true, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null));
      $arr[] = array(array('validate' => true, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null));
      $arr[] = array(array('validate' => false, 'message' => null, 'class' => null), array('validate' => true, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null));
      $arr[] = array(array('validate' => false, 'message' => null, 'class' => null), array('validate' => false, 'message' => null, 'class' => null), array('validate' => true, 'message' => null, 'class' => null));
      return $arr;
    }

  }

  class ValidateBindingAccountTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $methods = array('getSiteById');
      $this->mock = $this->getMockBuilder('Validator')->setMethods($methods)->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_validateBindingAccount_empty () {
      $standard = array('validate' => false, 'message' => null, 'class' => null);
      $spId = '';
      $login = '';
      $pass = '';
      $result = $this->mock->validatePasswordSP($spId, $login, $pass);
      // Проверка
      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    /**
     * @dataProvider validateBindingAccount_paramEmpty
     */
    public function test_validateBindingAccount_paramEmpty_false ($spId, $login, $pass) {
      $result = $this->mock->validatePasswordSP($spId, $login, $pass);
      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function validateBindingAccount_paramEmpty () {
      $arr[] = array('1', '', '');
      $arr[] = array('', 'login', '');
      $arr[] = array('', '', 'pass');
      $arr[] = array('1', 'login', '');
      $arr[] = array('1', '', 'pass');
      $arr[] = array('', 'login', 'pass');
      return $arr;
    }

    public function test_validateBindingAccount_site_false () {
      // Получение заглушки для объекта Site
      $site = $this->getMockBuilder('Site_SuperPuper')->setMethods(array('checkAccess'))->disableOriginalConstructor()->getMock();
      $value = false;
      $site->expects($this->once())->method('checkAccess')->will($this->returnValue($value));
      // Подготовка
      $this->mock->expects($this->once())->method('getSiteById')->will($this->returnValue($site));
      $spId = '1';
      $login = 'login';
      $pass = 'pass';
      // Проверка
      $result = $this->mock->validatePasswordSP($spId, $login, $pass);
      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function test_validateBindingAccount_site_true () {
      // Получение заглушки для объекта Site
      $site = $this->getMockBuilder('Site_SuperPuper')->setMethods(array('checkAccess'))->disableOriginalConstructor()->getMock();
      $value = true;
      $site->expects($this->once())->method('checkAccess')->will($this->returnValue($value));
      // Подготовка
      $this->mock->expects($this->once())->method('getSiteById')->will($this->returnValue($site));
      $standard = array('validate' => true, 'message' => null, 'class' => null);
      $spId = '1';
      $login = 'login';
      $pass = 'pass';
      // Проверка
      $result = $this->mock->validatePasswordSP($spId, $login, $pass);
      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

  }

  class ValidateChangePasswordSpTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Validator
     */
    public $mock;

    public function setUp () {
      $methods = array('getSiteById', 'getUserById');
      $this->mock = $this->getMockBuilder('Validator')->setMethods($methods)->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    public function test_validateChangePasswordSp_empty () {
      $standard = array('validate' => false, 'message' => null, 'class' => null);
      $new_login = '';
      $new_pass = '';
      $result = $this->mock->validateChangePasswordSp($new_login, $new_pass);
      // Проверка
      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }

    /**
     * @dataProvider validateChangePasswordSp_paramEmpty
     */
    public function test_validateChangePasswordSp_paramEmpty_false ($new_login, $new_pass) {
      $result = $this->mock->validateChangePasswordSp($new_login, $new_pass);
      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function validateChangePasswordSp_paramEmpty () {
      $arr[] = array('login', '');
      $arr[] = array('', 'pass');
      return $arr;
    }

    public function test_validateChangePasswordSp_siteCheckAccess_false () {
      // Получение заглушки для объекта Site
      $site = $this->getMockBuilder('Site_SuperPuper')->setMethods(array('checkAccess'))->disableOriginalConstructor()->getMock();
      $value = false;
      $site->expects($this->once())->method('checkAccess')->will($this->returnValue($value));
      // Подготовка
      $this->mock->expects($this->once())->method('getSiteById')->will($this->returnValue($site));
      $new_login = 'login';
      $new_pass = 'pass';
      // Проверка
      $result = $this->mock->validateChangePasswordSp($new_login, $new_pass);
      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function test_validateChangePasswordSp_getOrganizerId_false () {
      // Получение заглушки для объекта Site
      $methods = array('checkAccess', 'getOrganizerId', 'delCookieFromRegistry');
      $site = $this->getMockBuilder('Site_SuperPuper')->setMethods($methods)->disableOriginalConstructor()->getMock();
      $value = true;
      $site->expects($this->once())->method('checkAccess')->will($this->returnValue($value));
      $value = 2;
      $site->expects($this->once())->method('getOrganizerId')->will($this->returnValue($value));
      // Подготовка
      $this->mock->expects($this->once())->method('getSiteById')->will($this->returnValue($site));
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $new_login = 'login';
      $new_pass = 'pass';
      // Проверка
      $result = $this->mock->validateChangePasswordSp($new_login, $new_pass);
      $this->assertInternalType('array', $result);
      $element = array_shift($result);
      $this->assertFalse($element);
      $element = array_shift($result);
      $this->assertInternalType('array', $element);
      $element = array_shift($result);
      $this->assertEquals('error', $element);
    }

    public function test_validateChangePasswordSp_true () {
      // Получение заглушки для объекта Site
      $methods = array('checkAccess', 'getOrganizerId', 'delCookieFromRegistry');
      $site = $this->getMockBuilder('Site_SuperPuper')->setMethods($methods)->disableOriginalConstructor()->getMock();
      $value = true;
      $site->expects($this->once())->method('checkAccess')->will($this->returnValue($value));
      $value = 1;
      $site->expects($this->once())->method('getOrganizerId')->will($this->returnValue($value));
      // Подготовка
      $this->mock->expects($this->once())->method('getSiteById')->will($this->returnValue($site));
      $user = TestHelper::getUser();
      $this->mock->expects($this->once())->method('getUserById')->will($this->returnValue($user));
      $standard = array('validate' => true, 'message' => null, 'class' => null);
      $new_login = 'login';
      $new_pass = 'pass';
      // Проверка
      $result = $this->mock->validateChangePasswordSp($new_login, $new_pass);
      $this->assertInternalType('array', $result);
      $this->assertEquals($standard, $result);
    }
  }