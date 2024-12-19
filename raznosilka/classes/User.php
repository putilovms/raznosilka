<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\User.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class User содержит информацию о текущем пользователе.
 */
class User {
  /**
   * @var array Содержит информацию о пользователе полученную из базы данных
   */
  private $user = array();
  /**
   * @var bool Авторизован ли пользователь
   */
  private $auth = false;
  /**
   * @var int|null ID текущего пользователя
   */
  private $userIp;
  /**
   * @var bool Активирован ли аккаунт
   */
  private $activate = false;
  /**
   * Количество новых сообщений для пользователя
   */
  private $countNewMessages = 0;
  /**
   * @var Registry_Session Доступ к реестру сессий
   */
  private $regSession;
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  private $db;
  /**
   * @var Notify Уведомления для пользователя
   */
  private $notify;
  /**
   * @var string Браузер пользователя
   */
  private $userAgent;
  /**
   * @var array|null Данные об СП сайте пользователя
   */
  private $sp;
  /**
   * @var OrderUser Объект для работы с заказами пользователя
   */
  private $order;
  /**
   * @var bool Оплачен аккаунт или нет
   */
  private $status;
  /**
   * @var int Дата до которой оплачен аккаунт, в формате Unix
   */
  private $dateDone;
  /**
   * @var string Настройки временной зоны польователя (по умолчанию московское время)
   */
  private $timeZone = 'Europe/Moscow';

  /**
   * Конструктор нового пользователя.
   */
  function __construct () {
    $this->regSession = Registry_Session::instance();
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    $this->notify = new Notify();
    $this->auth = false;
    $param = $this->getAuthParam();
    if (!is_null($param)) {
      $this->auth = true;
      // Получаем данные о пользователе из БД
      $uid = $this->regSession->get(USER_ID);
      $this->user = $this->db->getUserById($uid);
      if (is_null($this->user) or ($this->user[USER_PASSWORD] != $param[USER_PASSWORD])) {
        // Если такого пользователя нет в базе данных или пароли не совпадают (после смены), то выходим из аккаунта
        $this->logout();
      }
      // Выставляем часовой пояс выбранный пользователем
      $this->timeZone = $this->user[USER_TIME_ZONE];
      date_default_timezone_set($this->timeZone);
      // Установить зону при помощи идентификатора
      $result = $this->db->setTimeZone($this->timeZone);
      // Если таблица временных зон не установлена, задать зону смещением времени
      if (!$result) {
        $this->db->setTimeZone(date('P'));
      }
      // Обновляем данные о пользователе после обновления временной зоны
      $this->user = $this->db->getUserById($uid);
      // Получаем данные о статусе услуги
      $this->order = new OrderUser($uid);
      $this->status = $this->order->getStatus();
      $this->dateDone = $this->order->getDateDone();
      // Получаем дополнительные данные о пользователе
      $this->userIp = $this->regSession->get('user_ip');
      $this->userAgent = $this->regSession->get('user_agent');
      // Обновляем время последнего входа пользователя в сервис
      $this->db->setLastTime($uid);
      // Проверяем активирован ли пользователь
      $this->activate = $this->user[USER_ACTIVATE];
      // Получение количества непрочитанных уведомлений
      $this->countNewMessages = Messages::getCountNewMessages($uid);
      // Получение данных о сайте СП на котором зарегистрирован пользователь
      $this->sp = $this->getSpName();
      // Проверка актуальности текущей сессии
      if (!Cache::isActualSID($uid)) {
        Cache::updateCache($uid);
      }
    } else {
      // Выстовляем часовой пояс по умолчанию
      date_default_timezone_set($this->timeZone);
      $this->db->setTimeZone($this->timeZone);
    }
  }

  /**
   * Заблокирован ли пользователь?
   * @return bool Истина, если заблокирован
   */
  function isBlocked () {
    return (isset($this->user[USER_BLOCKED])) ? $this->user[USER_BLOCKED] : false;
  }

  /**
   * Вспомогательный метод отделяющий получение данных о пользователе из реестра
   * сессий от их проверки.
   * @return null|array Результат получения данных об авторизованном пользователе
   * - null - если данных об авторизации нет
   * - array - Массив с данными об авторизации пользователя из реестра сессий, а так же
   * текущий IP пользователя
   */
  private function getAuthParam () {
    // Емайл пользователя
    $param[USER_EMAIL] = $this->regSession->get(USER_EMAIL);
    if (is_null($param[USER_EMAIL])) {
      return null;
    }
    // Логин пользователя
    $param[USER_LOGIN] = $this->regSession->get(USER_LOGIN);
    if (is_null($param[USER_LOGIN])) {
      return null;
    }
    // ИД пользователя
    $param[USER_ID] = $this->regSession->get(USER_ID);
    if (is_null($param[USER_ID])) {
      return null;
    }
    // Пароль пользователя
    $param[USER_PASSWORD] = $this->regSession->get(USER_PASSWORD);
    if (is_null($param[USER_PASSWORD])) {
      return null;
    }
    // ИП пользователя из реестра
    $param['user_ip'] = $this->regSession->get('user_ip');
    if (is_null($param['user_ip'])) {
      return null;
    }
    // ИП пользователя от сервера
    $param['remote_ip'] = $_SERVER['REMOTE_ADDR'];
    if (is_null($param['remote_ip'])) {
      return null;
    }
    return $param;
  }

  /**
   * Выход пользователя с сайта
   */
  function logout () {
    session_destroy();
    $url = URL::base();
    header("Location: " . $url);
    exit;
  }

  /**
   * Получить код для активации
   * @param $date string Строка с датой регистрации
   * @return string Код для активации
   */
  static function getActivateKey ($date) {
    $timestamp = strtotime($date);
    $gmdate = gmdate("Y-m-d H:i:s", $timestamp);
    $key = md5($gmdate . SALT);
    return $key;
  }

  /**
   * Проверка наличия OrgID
   * @return bool Результат наличия OrgID
   */
  function isBinding () {
    if (isset($this->user[USER_ORG_ID])) {
      if ((int)$this->user[USER_ORG_ID] !== -1) {
        return true;
      }
    }
    return false;
  }

  /**
   * Проверка введён ли пароль и логин для сайта СП
   * @return bool Результат проверки
   */
  function isHaveLogin () {
    if (isset($this->user[USER_SP_LOGIN]) and isset($this->user[USER_SP_PASSWORD])) {
      if (!empty($this->user[USER_SP_LOGIN]) and !empty($this->user[USER_SP_PASSWORD])) {
        return true;
      }
    }
    return false;
  }

  /**
   * Активация нового пользователя
   * @param int $id ID пользователя (полученное от пользователя)
   * @param string $activateKey Ключ активации от пользователя
   * @return bool true в случае успешной активации
   */
  public function activate ($id, $activateKey) {
    $id = (int)$id;
    $user = $this->db->getUserById($id);
    $key = User::getActivateKey($user[USER_REG_DATE]);
    $logs = new Logs();
    // Если пользователь получен
    if (!is_null($user)) {
      // Если пользователь ещё не активирован
      if (!$user[USER_ACTIVATE]) {
        // Если ключи совпадают
        if ($key == $activateKey) {
          // Если активация успешна, высылаем письмо об успешной активации
          $user = $this->db->activate($id);
          if ($user) {
            $mail = new Mail();
            $mail->sendUserWelcomeMail($user);
            // Опопвещение
            $this->notify->sendNotify("Вы успешно активировали аккаунт.", SUCCESS_NOTIFY);
            $this->sendWelcomeMessage($id);
            // Запись в лог
            $logs->actionLog($user, 'Аккаунт активирован');
            // Входим под активированной учётной записью
            $this->login($user);
          }
        }
      }
    }
    // Запись в лог
    $logs->actionLog(array(USER_ID => $id), 'Не удалось активировать аккаунт');
    return false;
  }

  /**
   * Подтверждение смены e-mail в Разносилке
   * @param int $id ID пользователя (полученное от пользователя)
   * @param string $activateKey Ключ активации от пользователя
   * @return bool true в случае успешной активации
   */
  public function changeEmail ($id, $activateKey) {
    $id = (int)$id;
    $user = $this->db->getUserById($id);
    $oldEmail = $user[USER_TMP_EMAIL];
    $key = md5($user[USER_TMP_EMAIL] . SALT);
    $logs = new Logs();
    // Если пользователь получен
    if (!is_null($user)) {
      // Если пользователь начал смену
      if (!empty($user[USER_TMP_EMAIL])) {
        // Если ключи совпадают
        if ($key == $activateKey) {
          // Если смена email успешна, высылаем письмо
          $user = $this->db->changeEmail($id);
          if ($user) {
            // Опопвещение
            $this->notify->sendNotify("Вы успешно подтвердили смену e-mail.", SUCCESS_NOTIFY);
            // Запись в лог
            $logs->actionLog($user, "E-mail сменён с {$oldEmail} на {$user[USER_EMAIL]}");
            // Входим под данной учётной записью
            $this->login($user);
          }
        }
      }
    }
    // Запись в лог
    $logs->actionLog(array(USER_ID => $id), 'Не удалось сменить e-mail');
    return false;
  }

  /**
   * Вспомогательный метод для входа пользователя на сайт. Устанавливает сессионные
   * переменные и переходит на указанную страницу (по умолчанию на главную).
   * @param array $user Данные о пользователе, поля массива имеют имена аналогичные
   * именам соотвествующих полей в таблице user
   * @param string $url Путь на который необходимо направить вновь зашедшего пользователя,
   * например 'user/login'. По умолчанию пользователь перенаправляется в корневой URL.
   */
  function login (array $user, $url = '') {
    // Уничтожаем предыдущую сессию и стартуем новую
    if (isset($_SESSION)) {
      $this->notify->backUp();
      session_destroy();
      session_start();
      $this->notify->restore();
    }
    $this->regSession->set(USER_ID, $user[USER_ID]);
    $this->regSession->set(USER_LOGIN, $user[USER_LOGIN]);
    $this->regSession->set(USER_EMAIL, $user[USER_EMAIL]);
    $this->regSession->set(USER_PASSWORD, $user[USER_PASSWORD]);
    $this->regSession->set('user_ip', $_SERVER['REMOTE_ADDR']);
    $this->regSession->set('user_agent', $_SERVER['HTTP_USER_AGENT']);
    $logs = new Logs();
    $logs->actionLog($user, 'Вход пользователя в систему');
    // $this->notify->sendNotify("Добро пожаловать на сайт.", SUCCESS_NOTIFY);
    header("Location: " . URL::to($url));
    exit;
  }

  /**
   * Восстановление пароля и логина
   * @param string $email Адрес который указал пользователь
   * @return bool Результат выполнения операции
   */
  public function forgot ($email) {
    $result = false;
    $user = $this->db->getUserByEmail($email);
    // Отсылать только для активированного пользователя
    if ($user[USER_ACTIVATE]) {
      $mail = new Mail();
      $result = $mail->sendUserForgotMail($user);
      // Логирование
      if ($result) {
        $logs = new Logs();
        $logs->actionLog($user, 'Письмо для восстановления логина и пароля отправлено');
      } else {
        trigger_error('Ошибка, не удалось отправить письмо для восстановления логина и пароля');
      }
    }
    return $result;
  }

  /**
   * Регистрация пользователя.
   * Если регистрация прошла успешно, то новый пользователь автоматически входит на сайт.
   * @param string $login Логин пользователя прошедший валидацию
   * @param string $email Email пользователя прошедший валидацию
   * @param string $pass Пароль пользователя прошедший валидацию
   * @param $spId int ID сайта СП прошедший валицацию
   * @return bool Результат добавления нового пользователя в базу данных
   * @throws Exception
   */
  public function register ($login, $email, $pass, $spId) {
    // Инициализация
    $logs = new Logs();
    $spId = (int)$spId;
    $site = Site::getSite($spId);
    $newUser = array();
    // Получение данных о новом пользователе
    $newUser[USER_LOGIN] = trim($login);
    $newUser[USER_EMAIL] = trim($email);
    $newUser[USER_PASSWORD] = trim($pass);
    $newUser[SP_ID] = $spId;
    $newUser[USER_REG_DATE] = strftime('%Y-%m-%d %H:%M:%S', time());
    $newUser[USER_ACTIVATE] = (Registry_Request::instance()->get('activate_account')) ? 0 : 1;
    $newUser[USER_FILLING_DAY] = $site->getFillingDay();
    $newUser[USER_TIME_ZONE] = $site->getSpTimeZone();
    $newUser[USER_REQUEST] = $site->getSpRequest();
    $addUser = $this->db->addUser($newUser);
    // Если регистрация успешна, то входим под зарегистрированным логином
    if ($addUser) {
      $mail = new Mail();
      // Оповещаем администратора о новой регистрации
      $mail->sendAdminNotifyRegistrationMail($addUser);
      // Отсылаем письмо новому пользователю
      if ($addUser[USER_ACTIVATE]) {
        $mail->sendUserWelcomeMail($addUser);
        $this->sendWelcomeMessage($addUser[USER_ID]);
      } else {
        $mail->sendUserActivateMail($addUser);
      }
      // Опопвещение
      $this->notify->sendNotify("Вы успешно зарегистрировались.", SUCCESS_NOTIFY);
      // Запись в журнал
      $logs->actionLog($addUser, 'Регистрация пользователя');
      // Заходим под новой учётной записью
      $this->login($addUser);
    }
    // ошибока в случае неудачи
    trigger_error('Пользователь не зарегистрирован. Неизвестная ошибка.');
    return false;
  }

  /**
   * Обновляет количество непрочитанных сообщений
   * @return bool Результат выполнения операции
   */
  function updateCountNewMessages () {
    if ($this->auth) {
      // Получаем данные о пользователе
      $id = Registry_Session::instance()->get(USER_ID);
      $this->countNewMessages = Messages::getCountNewMessages($id);
      return true;
    }
    return false;
  }

  /**
   * Возвращает информацию о текщем пользователе
   * @return array Массив с данными о пользователе
   */
  function getUserInfo () { // todo описание массива
    // Полученные из БД
    $user[USER_ID] = (isset($this->user[USER_ID])) ? $this->user[USER_ID] : null;
    $user[USER_LOGIN] = (isset($this->user[USER_LOGIN])) ? $this->user[USER_LOGIN] : null;
    $user[USER_EMAIL] = (isset($this->user[USER_EMAIL])) ? $this->user[USER_EMAIL] : null;
    $user[USER_PASSWORD] = (isset($this->user[USER_PASSWORD])) ? $this->user[USER_PASSWORD] : null;
    $user[USER_SP_LOGIN] = (isset($this->user[USER_SP_LOGIN])) ? $this->user[USER_SP_LOGIN] : null;
    $user[USER_SP_PASSWORD] = (isset($this->user[USER_SP_PASSWORD])) ? $this->user[USER_SP_PASSWORD] : null;
    $user[SP_ID] = (isset($this->user[SP_ID])) ? $this->user[SP_ID] : null;
    $user[USER_ORG_ID] = (isset($this->user[USER_ORG_ID])) ? $this->user[USER_ORG_ID] : null;
    $user[USER_REG_DATE] = (isset($this->user[USER_REG_DATE])) ? $this->user[USER_REG_DATE] : null;
    $user[USER_ACTIVATE] = (isset($this->user[USER_ACTIVATE])) ? $this->user[USER_ACTIVATE] : null;
    $user[USER_REMINDING] = (isset($this->user[USER_REMINDING])) ? $this->user[USER_REMINDING] : null;
    $user[USER_FILLING_DAY] = (isset($this->user[USER_FILLING_DAY])) ? $this->user[USER_FILLING_DAY] : null;
    $user[USER_TMP_EMAIL] = (isset($this->user[USER_TMP_EMAIL])) ? $this->user[USER_TMP_EMAIL] : null;
    $user[USER_TIME_ZONE] = (isset($this->user[USER_TIME_ZONE])) ? $this->user[USER_TIME_ZONE] : null;
    $user[USER_REQUEST] = (isset($this->user[USER_REQUEST])) ? $this->user[USER_REQUEST] : null;
    // USER_SESSION_ID
    // Сгенерированные данные
    $user['auth'] = $this->isAuth();
    $user['admin'] = $this->isAdmin();
    $user['access_to_tools'] = $this->hasAccessToTools();
    $user['new_msg'] = $this->countNewMessages;
    $user['user_ip'] = $this->userIp;
    $user['user_agent'] = $this->userAgent;
    $user[SP_SITE_NAME] = (isset($this->sp[SP_SITE_NAME])) ? $this->sp[SP_SITE_NAME] : null;
    $user['status'] = $this->status;
    $user['date_done'] = $this->dateDone;
    return $user;
  }

  /**
   * Проверяет авторизирован ли пользователь
   * @return bool true если пользователь авторизирован
   */
  function isAuth () {
    return $this->auth;
  }

  /**
   * Проверяет является ли пользователь администратором
   * @return bool true если пользователь администратор
   */
  function isAdmin () {
    if ($this->auth) {
      if ($this->user[USER_ID] == 1) {
        return true;
      }
    }
    return false;
  }

  /**
   * Проверяет активирован ли аккаунт
   * @return bool true если аккаунт активирован
   */
  function isActivate () {
    return $this->activate;
  }

  /**
   * Получить данные сайта СП пользователя
   * @return array|null Данные сайта СП
   */
  function getSpName () {
    $result = null;
    $spId = $this->user[SP_ID];
    if (isset($spId)) {
      $sp = $this->db->getSpById($spId);
      if ($sp !== false) {
        $result = $sp;
      }
    }
    return $result;
  }

  /**
   * Получить пароль текущего пользователя
   * @return null|string Пароль текущего пользователя
   */
  function getUserPassword () {
    $result = null;
    if (isset($this->user[USER_PASSWORD])) {
      $result = $this->user[USER_PASSWORD];
    }
    return $result;
  }

  /**
   * Возвращает ID текущего пользователя
   * @return null|int ID пользователя
   */
  public function getUserId () {
    return (isset($this->user[USER_ID])) ? $this->user[USER_ID] : null;
  }

  /**
   * Возвращает ID выбранного сайта
   * @return null|int ID пользователя
   */
  public function getSpId () {
    return (isset($this->user[SP_ID])) ? $this->user[SP_ID] : null;
  }

  /**
   * Возвращает email пользователя
   * @return null|string email пользователя
   */
  public function getUserEmail () {
    return (isset($this->user[USER_EMAIL])) ? $this->user[USER_EMAIL] : null;
  }

  /**
   * Имеет ли ползователь доступ к инструментам
   * @return bool Истина если имеет доступ к инструментам
   */
  function hasAccessToTools () {
    $result = false;
    $userRequest = $this->getUserRequest();
    switch ($userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        if ($this->isAuth() and $this->isActivate() and $this->isPaying() and !$this->isBlocked()) {
          $result = true;
        }
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        if ($this->isHaveLogin() and $this->isBinding() and $this->isAuth() and $this->isActivate() and $this->isPaying() and !$this->isBlocked()) {
          $result = true;
        }
        break;
      }
    }
    return $result;
  }

  /**
   * Возвращает количество дней на проставление оплат
   * @return null|int Количество дней на проставление оплат
   */
  public function getFillingDay () {
    return (isset($this->user[USER_FILLING_DAY])) ? $this->user[USER_FILLING_DAY] : null;
  }

  /**
   * Имеет ли пользователь бесплатный месяц использования сервиса
   * @return bool Истина если пользователь имеет бесплатный месяц
   */
  function hasGift () {
    $result = false;
    if (isset($this->user[USER_GIFT])) {
      if ($this->user[USER_GIFT]) {
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Получить объект для работы с заказами пользователя
   * @return OrderUser Объект с заказами пользователя
   */
  function getOrder () {
    return $this->order;
  }

  /**
   * Получить дату до которой будет оказываться услуга
   * @return int Дата до которой будет оказываться услуга
   */
  function getPayingDate () {
    return $this->dateDone;
  }

  /**
   * Оплатил ли пользователь услугу
   * @return bool Истина если пользователь оплатил услугу
   */
  function isPaying () {
    return $this->status;
  }

  /**
   * Получить платёжный статус пользователя
   * @return int Платёжный статус пользователя:
   *  - 0 - Гость
   *  - 1 - Зарегистрирован, но не активирован
   *  - 2 - Зарегистрирован, активирован, но не ввёл пароль (только для прямого запроса)
   *  - 3 - Новый пользователь (может получить подарок)
   *  - 4 - Пользователь может оплачивать услугу
   */
  public function getPayingStatus () {
    $userRequest = $this->getUserRequest();
    // Гость
    $result = 0;
    // Зарегистрирован, но не активирован
    if ($this->isAuth() and !$this->isActivate()) {
      $result = 1;
    }
    // Новый пользователь
    if ($this->isAuth() and $this->hasGift() and $this->isActivate()) {
      $result = 2;
    }
    // Может оплачивать
    if ($this->isAuth() and !$this->hasGift() and $this->isActivate()) {
      $result = 3;
    }
    return $result;
  }

  /**
   * Задать новый email
   * @param $email string Новый email
   */
  function setTmpEmail ($email) {
    $this->user[USER_TMP_EMAIL] = $email;
  }

  /**
   * Начата ли смена email
   * @return bool Истина если смена email начата
   */
  public function isChangeEmail () {
    return (!empty($this->user[USER_TMP_EMAIL])) ? true : false;
  }

  /**
   * Получить email на который пользователь хочет заменить свой email
   * @return null|string Email на который пользователь хочет заменить свой email
   */
  public function getUserTmpEmail () {
    return (!empty($this->user[USER_TMP_EMAIL])) ? $this->user[USER_TMP_EMAIL] : null;
  }

  /**
   * Получить выбранную пользователем временную зону
   * @return string Временная зона
   */
  function getUserTimeZone () {
    return $this->timeZone;
  }

  /**
   * Уведомление пользователю об успешной активации
   * @param int $id ID пользователя
   */
  private function sendWelcomeMessage ($id) {
    $urlBinding = URL::to('help/binding');
    $urlGift = URL::to('help/gift');
    $urlExtension = URL::to('help/extension');
    $userRequest = $this->getUserRequest();
    switch ($userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        $message = "
          <p>Добро пожаловать в сервис «Разносилка».</p>
          <p>Вы успешно активировали свой аккаунт. Теперь вам нужно <a href='{$urlExtension}' target='_blank'>установить расширение для браузера</a> и <a href='{$urlGift}' target='_blank'>получить пробный период</a>, после чего вы сможете использовать все возможности сервиса.</p>
        ";
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        $message = "
          <p>Добро пожаловать в сервис «Разносилка».</p>
          <p>Вы успешно активировали свой аккаунт. Теперь вам нужно <a href='{$urlBinding}' target='_blank'>ввести логин и пароль от сайта СП</a> и <a href='{$urlGift}' target='_blank'>получить пробный период</a>, после чего вы сможете использовать все возможности сервиса.</p>
        ";
        break;
      }
    }
    $this->db->postMessage(INFO_MESSAGE, $message, $id);
  }

  /**
   * Получить ID организатора
   * @return null|int ID организатора
   */
  public function getOrgId () {
    return (isset($this->user[USER_ORG_ID])) ? (int)$this->user[USER_ORG_ID] : null;
  }

  /**
   * Получить тип запроса к сайту СП
   * @return null|int Тип запроса к сайту СП
   */
  public function getUserRequest(){
    return (isset($this->user[USER_REQUEST])) ? (int)$this->user[USER_REQUEST] : null;
  }

}