<?php

  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\MailTest.php>
   * @copyright © М. С. Путилов, 2015
   */

  require_once '../classes/Mail.php';
  require_once '../classes/URL.php';
  require_once '../resources/const.php';
  require_once 'TestHelper.php';

  class MailTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Mail
     */
    public $mock;

    public function setUp () {
      $this->mock = $this->getMockBuilder('Mail')->setMethods(array('sendMailWrapper','getInfoAdmin'))->disableOriginalConstructor()->getMock();
    }

    public function tearDown () {

    }

    /**
     * sendUserActivateMail
     */

    public function test_sendUserActivateMail_activateUser () {
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(true));
      $user = TestHelper::getUser();
      $result = $this->mock->sendUserActivateMail($user, true);
      $this->assertTrue($result);
    }

    public function test_sendUserActivateMail_noActivateUser () {
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(true));
      $user = TestHelper::getUser();
      $result = $this->mock->sendUserActivateMail($user, false);
      $this->assertTrue($result);
    }

    public function test_sendUserActivateMail_false () {
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(false));
      $user = TestHelper::getUser();
      $result = $this->mock->sendUserActivateMail($user, true);
      $this->assertFalse($result);
    }

    public function test_sendUserActivateMail_brokenArray_Exception () {
      $this->setExpectedException('Exception');
      $user = array();
      $this->mock->sendUserActivateMail($user, true);
    }

    /**
     * sendAdminNotifyMail - registration
     */

    public function test_sendAdminNotifyMail_registration_true () {
      $admin = TestHelper::getAdmin();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(true));
      $user = TestHelper::getUser();
      $type = 'registration';
      $result = $this->mock->sendAdminNotifyMail($type, $user);
      $this->assertTrue($result);
    }

    public function test_sendAdminNotifyMail_registration_false () {
      $admin = TestHelper::getAdmin();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(false));
      $user = TestHelper::getUser();
      $type = 'registration';
      $result = $this->mock->sendAdminNotifyMail($type, $user);
      $this->assertFalse($result);
    }

    /**
     * sendAdminNotifyMail - sms_unknown
     */

    public function test_sendAdminNotifyMail_sms_unknown_true () {
      $admin = TestHelper::getAdmin();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(true));
      $data = array();
      for ($i=0; $i<20; $i++) $data[] = '';
      $type = 'sms_unknown';
      $result = $this->mock->sendAdminNotifyMail($type, $data);
      $this->assertTrue($result);
    }

    public function test_sendAdminNotifyMail_sms_unknown_false () {
      $admin = TestHelper::getAdmin();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(false));
      $data = array();
      $type = 'sms_unknown';
      $result = $this->mock->sendAdminNotifyMail($type, $data);
      $this->assertFalse($result);
    }

    /**
     * sendAdminNotifyMail
     */

    public function test_sendAdminNotifyMail_noDate_Exception () {
      $this->setExpectedException('Exception');
      $admin = TestHelper::getAdmin();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $user = array();
      $type = '';
      $this->mock->sendAdminNotifyMail($type, $user);
    }

    public function test_sendAdminNotifyMail_brokenAdmin_Exception () {
      $this->setExpectedException('Exception');
      $admin = array();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $user = TestHelper::getUser();
      $type = 'registration';
      $this->mock->sendAdminNotifyMail($type, $user);
    }

    public function test_sendAdminNotifyMail_noType_Exception () {
      $this->setExpectedException('Exception');
      $admin = TestHelper::getAdmin();
      $this->mock->expects($this->once())->method('getInfoAdmin')->will($this->returnValue($admin));
      $user = TestHelper::getUser();
      $type = '';
      $this->mock->sendAdminNotifyMail($type, $user);
    }

    /**
     * sendUserForgotMail
     */

    public function test_sendUserForgotMail_true () {
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(true));
      $user = TestHelper::getUser();
      $result = $this->mock->sendUserForgotMail($user);
      $this->assertTrue($result);
    }

    public function test_sendUserForgotMail_false () {
      $this->mock->expects($this->once())->method('sendMailWrapper')->will($this->returnValue(false));
      $user = TestHelper::getUser();
      $result = $this->mock->sendUserForgotMail($user);
      $this->assertfalse($result);
    }

    public function test_sendUserForgotMail_brokenArray_Exception () {
      $this->setExpectedException('Exception');
      $user = array();
      $this->mock->sendUserForgotMail($user);
    }
  }
 