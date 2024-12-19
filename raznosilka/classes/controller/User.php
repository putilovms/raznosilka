<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Controller_User.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_User Контроллер авторизации пользователя
 */
class Controller_User extends Controller {
  /**
   * @var Validator Содержит экземпляр класса отвечающий за валидацию данных.
   */
  private $validator;

  /**
   * Конструктор класса
   */
  function __construct () {
    parent::__construct();
    $this->validator = new Validator();
  }

  /**
   * Для отмены автоматической загрузки сообщения о технических работах
   * @param string $mode Для соблюдения Strict Standards
   */
  function serviceMode ($mode) {
  }

  /**
   * Страница /user - содержит форму настроек пользователя
   */
  function index () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    // Показываем страницу настроек
    $this->template->setTitle('Настройки аккаунта');
    $this->template->show('settings');
  }

  /**
   * Вывод информации об аккаунте
   */
  function info () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    // Получаем информацию о пользователе
    $uid = $this->user->getUserId();
    $settings = new SettingsUser($uid);
    $info = $settings->getViewUserInfo();
    $this->template->set('user', $info);
    // Показываем страницу настроек
    $this->template->setTitle('Информация об аккаунте');
    $this->template->show('info');
  }

  /**
   * Уведомления по e-mail
   */
  function notify () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    // Сохраняем настройки
    $settings = new SettingsUser($this->user->getUserId());
    $result = $settings->notifySettings($_POST);
    if ($result['result'] === true) {
      $logs = new Logs();
      $logs->actionLog($this->user->getUserInfo(), 'Пользователь изменил настройки уведомлений по e-mail');
      $this->notify->sendNotify('Настройки успешно изменены.', SUCCESS_NOTIFY);
    }
    if ($result['result'] === false) {
      $this->notify->sendNotify('Не удалось изменить настройки.', ERROR_NOTIFY);
    }
    if (!empty($_POST)) {
      $this->postReset();
    }
    // Показываем страницу настроек
    $this->template->set('notify', $result);
    $this->template->setTitle('Уведомления по e-mail');
    $this->template->show('notify');
  }

  /**
   * Настройки сайта СП
   */
  function sp () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    // Получаем информацию о пользователе
    $user = $this->user->getUserInfo();
    $this->template->set('user', $user);
    // Имеется ли OrgID у пользователя
    $binding = ($this->user->isBinding())? 1 : 0;
    $this->template->set('bind', $binding);
    // Показываем страницу настроек
    $this->template->setTitle('Настройки сайта СП');
    $this->template->show('sp');
  }

  /**
   * Ввод или смена пароля к сайту СП
   */
  function password(){
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_CURL);
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    // Сохраняем значение наличия OrgID
    $binding = $user->isBinding();
    $settings = new SettingsUser(Registry_Session::instance()->get('user_id'));
    $spLogin = isset($_POST['login']) ? $_POST['login'] : '';
    $spPassword = isset($_POST['password']) ? $_POST['password'] : '';
    // Валидация введёных значений
    $result = $this->validator->validatePasswordSP($spLogin, $spPassword);
    if ($result['validate']) {
      if ($settings->setPasswordSP($spLogin, $spPassword)) {
        $this->notify->sendNotify('Логин и пароль к сайту СП успешно сохранены.', SUCCESS_NOTIFY);
        // Редирект на главную страницу
        if ($binding) {
          $this->headerLocation(URL::to('user/sp'));
        } else {
          $this->headerLocation(URL::base());
        }
      } else {
        // Ошибка в случае неудачи
        $controller = new Controller_Error;
        $controller->index(__LINE__, __FILE__);
      }
    }
    $this->template->set('user', $userInfo);
    $this->template->set('password', $result);
    $this->template->setTitle('Логин и пароль к сайту СП');
    $this->template->show('password');
  }

  /**
   * Смена пароля Разносилки
   */
  function security () {
    // todo внедрить аякс
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $settings = new SettingsUser(Registry_Session::instance()->get('user_id'));
    // Смена пароля
    $old_pass = isset($_POST['old_pass']) ? $_POST['old_pass'] : '';
    $new_pass_1 = isset($_POST['new_pass_1']) ? $_POST['new_pass_1'] : '';
    $new_pass_2 = isset($_POST['new_pass_2']) ? $_POST['new_pass_2'] : '';
    $result = $this->validator->validateChangePassword($old_pass, $new_pass_1, $new_pass_2);
    // Если валидация прошла успешно
    if ($result['validate']) {
      if ($settings->changePassword($new_pass_1)) {
        $this->notify->sendNotify('Пароль доступа к сервису успешно изменён.', SUCCESS_NOTIFY);
      } else {
        // Ошибка в случае неудачи
        $controller = new Controller_Error;
        $controller->index(__LINE__, __FILE__);
      }
      // Редирект на страницу настроек
      $this->headerLocation(URL::to('user'));
    }
    $this->template->set('change_pass', $result);
    $this->template->setTitle('Смена пароля к Разносилке');
    $this->template->show('security');
  }

  /**
   * Страница /user/login содержит форму входа на сайт.
   * Выводится в режиме обслуживания.
   */
  function login () {
    $this->access(GUEST);
    // Инициализация
    $login = isset($_POST['auth_login']) ? $_POST['auth_login'] : '';
    $pass = isset($_POST['auth_pass']) ? $_POST['auth_pass'] : '';
    $result = $this->validator->validateUserRegistration($login, $pass);
    if ($result['validate']) {
      $this->user->login($result['user']);
      // Ошибка в случае неудачи
      $controller = new Controller_Error;
      $controller->index(__LINE__, __FILE__);
    }
    // Добавление мета тегов
    $title = 'Вход в Разносилку';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->set('login_result', $result);
    $this->template->setTitle($title);
    $this->template->show('login');
  }

  /**
   * Страница /user/logout выход с сайта.
   * Выводится в режиме обслуживания.
   */
  function logout () {
    $logs = new Logs();
    $logs->actionLog($this->user->getUserInfo(), 'Пользователь вышел');
    $this->user->logout();
  }

  /**
   * Страница /user/register содержит форму регистрации на сайте
   */
  function register () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    // Сброс POST после перехода на данную страницу через кнопку формы
    if (empty($_POST)) {
      $this->postReset();
    }
    $this->access(GUEST);
    $register = Registry_Request::instance()->get('register_account');
    // Разрешена ли регистрация новых пользователей
    if ($register) {
      // todo ajax проверка
      $reg_login = isset($_POST['reg_login']) ? $_POST['reg_login'] : '';
      $reg_email = isset($_POST['reg_email']) ? $_POST['reg_email'] : '';
      $reg_pass_1 = isset($_POST['reg_pass_1']) ? $_POST['reg_pass_1'] : '';
      $reg_pass_2 = isset($_POST['reg_pass_2']) ? $_POST['reg_pass_2'] : '';
      $spId = isset($_POST['sp_id']) ? (int)$_POST['sp_id'] : null;
      $result = $this->validator->validateRegisterForm($reg_login, $reg_email, $reg_pass_1, $reg_pass_2, $spId);
      if ($result['validate']) {
        $this->user->register($reg_login, $reg_email, $reg_pass_1, $spId); // редирект при удачной регистрации
        // Если регистрация окончилась неудачей
        $controller = new Controller_Error;
        $controller->index(__LINE__, __FILE__);
      }
      // Получение списка сайтов СП
      $sp = new Sp;
      $list = $sp->getSpList();
      $this->template->set('sp', $list);
      // Результат проверки
      foreach ($result['data'] as $key => $value) $this->template->set($key, $value);
      $this->template->setTitle('Регистрация в Разносилке');
      $this->template->show('register');
    } else {
      // Если регистрация запрещена
      $controller = new Controller_Error;
      $controller->noAccess('Извините, в данный момент регистрация новых пользователей запрещена.');
    }
  }

  /**
   * Активация нового пользователя
   */
  function activate () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для вывода сообщения о режиме обслуживания
    if (!empty($_GET['id']) and !empty($_GET['activate'])) {
      $this->user->activate($_GET['id'], $_GET['activate']);
    }
    // Если не удалось активировать пользователя
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Восстановление пароля. Шаблон forgot.tpl.php
   */
  function forgot () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(GUEST);
    $email = isset($_POST['forgot_email']) ? $_POST['forgot_email'] : '';
    $result = $this->validator->validateEmail($email, true);
    if ($result['validate']) {
      if ($this->user->forgot($email)) {
        $this->notify->sendNotify('На указанный вами почтовый ящик был выслан логин и пароль для доступа к сервису.', SUCCESS_NOTIFY);
      } else {
        // Ошибка в случае неудачи
        $controller = new Controller_Error;
        $controller->index(__LINE__, __FILE__);
      }
      $this->postReset();
    }
    $this->template->set('forgot_result', $result);
    // Добавление мета тегов
    $title = 'Восстановаление пароля';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('forgot');
  }

  /**
   * Удаление пароля от сайта СП
   */
  function password_del () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_CURL);
    $del = isset($_POST['del']) ? $_POST['del'] : '';
    if ($del) {
      $settings = new SettingsUser(Registry_Session::instance()->get('user_id'));
      if ($settings->delPasswordSp()) {
        $this->notify->sendNotify('Логин и пароль для доступа к сайту СП успешно удалены.', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось удалить логин и пароль для доступа к сайту СП.', ERROR_NOTIFY);
      }
      // Редирект на страницу настроек
      $this->headerLocation(URL::to('user/sp'));
    }
    $this->template->setTitle('Удаление пароля от сайта СП');
    $this->template->show('password_del');
  }

  /**
   * Повторная активация
   */
  function reactivate () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(NO_ACTIVATE);
    // Отсылаем письмо новому пользователю
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    $mail = new Mail();
    $mail->sendUserActivateMail($userInfo);
    $this->notify->sendNotify('Повторное письмо со ссылкой для активации выслано на ваш e-mail.', SUCCESS_NOTIFY);
    // Редирект на главную страницу
    $this->headerLocation(URL::base());
  }

  /**
   * Смена email к Разносилке
   */
  function email () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $settings = new SettingsUser($this->user->getUserId());
    // Смена email
    $email = isset($_POST['new_email']) ? $_POST['new_email'] : '';
    $result = $this->validator->validateEmail($email);
    if (!empty($email)) {
      if ($result['validate']) {
        // todo переместить код в метод класса User
        $settings->setSetting(USER_TMP_EMAIL, $email);
        if ($settings->setSettings()) {
          // Обновляем объект пользователя
          $this->user->setTmpEmail($email);
          // Высылаем емайл с подтверждением
          $userInfo = $this->user->getUserInfo();
          $mail = new Mail();
          $mail->sendUserChangeEmailMail($userInfo);
          // Запись в лог
          $logs = new Logs();
          $logs->actionLog($userInfo, 'Начата смена e-mail');
          $this->postReset();
        } else {
          // Ошибка в случае неудачи
          $controller = new Controller_Error;
          $controller->index(__LINE__, __FILE__);
        }
      }
    }
    // Данные о статусе смены email
    $info = $this->user->getUserInfo();
    $this->template->set('info', $info);
    $this->template->set('email', $result);
    $this->template->setTitle('Смена e-mail в Разносилке');
    $this->template->show('email');
  }

  /**
   * Подтвеждение нового email к Разносилке
   */
  function change () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    if (!empty($_GET['id']) and !empty($_GET['activate'])) {
      $this->user->changeEmail($_GET['id'], $_GET['activate']);
    }
    // Если не удалось сменить email
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Отмена смены email к Разносилке
   */
  function cancel () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $userInfo = $this->user->getUserInfo();
    if (!empty($userInfo[USER_TMP_EMAIL])) {
      $settings = new SettingsUser($this->user->getUserId());
      $settings->setSetting(USER_TMP_EMAIL, '');
      if ($settings->setSettings()) {
        // Оповещение
        $this->notify->sendNotify('Смена e-mail успешно отменена.', SUCCESS_NOTIFY);
        // Запись в лог
        $logs = new Logs();
        $logs->actionLog($userInfo, 'Смена e-mail отменена');
        // Редирект на страницу смены email
        $this->headerLocation(URL::to('user/email'));
      } else {
        $this->notify->sendNotify('Не удалось отменить смену e-mail.', ERROR_NOTIFY);
      }
    }
    // Ошибка в случае неудачи
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Повторное письмо о смене email
   */
  function repeat () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $userInfo = $this->user->getUserInfo();
    if (!empty($userInfo[USER_TMP_EMAIL])) {
      // Высылаем повторный емайл с подтверждением
      $mail = new Mail();
      $mail->sendUserChangeEmailMail($userInfo);
      $this->notify->sendNotify('Повторное письмо со ссылкой для подтверждения смены e-mail, выслано на ваш новый e-mail.', SUCCESS_NOTIFY);
      // Редирект на страницу смены email
      $this->headerLocation(URL::to('user/email'));
    } else {
      // Если процесс смены email не начат
      $controller = new Controller_Error;
      $controller->noAccess('У вас нет доступа к данной странице, так как процесс смены email не начат.');
    }

  }

  /**
   * Пользовательские настройки сервиса
   */
  function service(){
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    // Сохраняем настройки
    $settings = new SettingsUser($this->user->getUserId());
    $result = $settings->serviceSettings($_POST);
    if ($result['result'] === true) {
      $logs = new Logs();
      $logs->actionLog($this->user->getUserInfo(), 'Пользователь изменил настройки аккаунта');
      $this->notify->sendNotify('Настройки успешно изменены.', SUCCESS_NOTIFY);
    }
    if ($result['result'] === false) {
      $this->notify->sendNotify('Не удалось изменить настройки.', ERROR_NOTIFY);
    }
    if (!empty($_POST)) {
      $this->postReset();
    }
    // Показываем страницу настроек
    $this->template->set('info', $result);
    $this->template->setTitle('Настройки «Разносилки»');
    $this->template->show('service');

  }

}