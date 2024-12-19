<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Mail.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Mail Класс отвечающий за отправку почты
 */
class Mail {
  /**
   * @var string содержит режим работы сайта
   */
  private $mode;
  /**
   * @var string Емайл с которого будет отсылаться почта
   */
  private $systemEmail;
  /**
   * @var string Разделитель
   */
  private $boundary;

  /**
   * Конструктор
   */
  function __construct () {
    // Инициализация
    $this->systemEmail = Registry_Request::instance()->get('system_email');
    $this->mode = Registry_Request::instance()->get('mode');
    $this->boundary = md5('Разносилка');
  }

  /**
   * Отсылает письмо новому пользователю о статусе активации аккаунта
   * @param array $user Массив данных о пользователе из таблицы users
   * @return bool true если письмо отправлено
   */
  function sendUserActivateMail ($user) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Активация аккаунта.";
    $key = User::getActivateKey($user[USER_REG_DATE]);
    $query = array('id' => $user[USER_ID], 'activate' => $key);
    $var['link'] = URL::to('user/activate', $query);
    $body = $this->getBody('activate', $var);
    return $this->sendMail($user[USER_EMAIL], $subject, $body);
  }

  /**
   * Отсылает приветственное письмо новому пользователю после активации
   * @param array $user Массив данных о пользователе из таблицы users
   * @return bool true если письмо отправлено
   * @throws Exception
   */
  function sendUserWelcomeMail (array $user) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Добро пожаловать.";
    $var['user'] = $user;
    switch ((int)$user[USER_REQUEST]) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        $body = $this->getBody('welcome_extension', $var);
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        $body = $this->getBody('welcome_curl', $var);
        break;
      }
    }
    $result = $this->sendMail($user[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Проверяет наличие соотвествующих полей у массива содержащего данные пользователя
   * @param array $user Проверяемый массив
   * @throws Exception
   */
  private function validateUserArray ($user) {
    if (!isset($user[USER_LOGIN])) {
      throw new Exception('Массив $user повреждён');
    }
    if (!isset($user[USER_PASSWORD])) {
      throw new Exception('Массив $user повреждён');
    }
    if (!isset($user[USER_ID])) {
      throw new Exception('Массив $user повреждён');
    }
    if (!isset($user[USER_REG_DATE])) {
      throw new Exception('Массив $user повреждён');
    }
    if (!isset($user[USER_REQUEST])) {
      throw new Exception('Массив $user повреждён');
    }
  }

  /**
   * Вспомогательный метод для отсылки письма
   * @param string $email Адрес получателя
   * @param string $subject Тема письма
   * @param string $body Тело письма
   * @throws Exception
   * @return bool Результат отсылки письма
   */
  function sendMail ($email, $subject, $body) {
    if (empty($subject)) {
      throw new Exception('Поле тема письма пусто');
    }
    if (empty($body)) {
      throw new Exception('Поле тело письма пусто');
    }
    if (empty($email)) {
      throw new Exception('Не указан email получателя');
    }
    // Замена адреса получателя в режиме отладки
    if ($this->mode == 'debug') {
      $admin = $this->getInfoAdmin();
      if (empty($admin)) {
        throw new Exception ('Невозможно получить данные об администраторе');
      }
      $email = $admin[USER_EMAIL];
    }
    // Кодируем тему сообщения
    $subject = $this->mimeEncode($subject);
    // Заголовки
    $header = $this->getHeaders();
    // Отсылаем письмо
    $parameters = '-f' . $this->systemEmail;
    $result = $this->sendMailWrapper($email, $subject, $body, $header, $parameters);
    // Лог
    $logs = new Logs();
    if ($result) {
      $logs->mailLog($email, $this->systemEmail, $this->mimeDecode($subject));
    } else {
      trigger_error("Неизвестная ошибка. Не удалось отправить почту по адресу {$email}");
    }
    return $result;
  }

  /**
   * Кодирует кириллицу для корректного отображения
   * @param string $str Строка
   * @return string Закодированная строка
   */
  private function mimeEncode ($str) {
    if (!empty($str)) {
      $str = '=?UTF-8?B?' . base64_encode($str) . '?=';
    }
    return $str;
  }

  /**
   * Получить строку из закодированной строки
   * @param $str string Закодированная строка
   * @return false|string Раскодированная строка
   */
  function mimeDecode ($str) {
    $result = false;
    $pattern = '#=\?UTF-8\?B\?(.*)\?=#';
    preg_match($pattern, $str, $matches);
    if (!empty($matches[1])) {
      $base64 = $matches[1];
      $result = base64_decode($base64);
    }
    return $result;
  }

  /**
   * Генерирует заголовки для отсылки почты
   * @return string строка с заголовками для отсылки почты
   */
  private function getHeaders () {
    // Генерируем заголовки
    $email = $this->systemEmail;
    $header[] = "MIME-Version: 1.0";
    // $header[] = "Content-Type: text/html; charset=UTF-8";
    $header[] = "Content-Type: multipart/alternative; boundary={$this->boundary}";
    $header[] = "Content-Transfer-Encoding: 7bit";
    $name = $this->mimeEncode("Разносилка");
    $header[] = "From: {$name} <{$email}>";
    $header = implode("\r\n", $header);
    return $header;
  }

  /**
   * Отсылка письма.
   * Вынесено для юнит-тестирования.
   * @param string $email
   * @param string $subject
   * @param string $body
   * @param string $header
   * @param null $parameters
   * @return bool Результат отсылки письма
   */
  function sendMailWrapper ($email, $subject, $body, $header, $parameters = null) {
    return mail($email, $subject, $body, $header, $parameters);
  }

  /**
   * Отправить уведомление администратору о новом пользователе
   * @param $data array Массив с данными о новом пользователе
   * @return bool Результат отсылки письма
   * @throws Exception
   */
  public function sendAdminNotifyRegistrationMail (array $data) {
    $admin = $this->getInfoAdmin();
    if (empty($admin)) {
      throw new Exception ('Невозможно получить данные об администраторе');
    }
    $this->validateUserArray($data);
    $subject = "Разносилка. Новый аккаунт.";
    $var['user'] = $data;
    $body = $this->getBody('registration', $var);
    $result = $this->sendMail($admin[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Отправить уведомление администратору о нераспознанных СМС
   * @param $data array Массив с нераспознанными СМС
   * @return bool Результат отсылки письма
   * @throws Exception
   */
  public function sendAdminNotifySmsUnknownMail (array $data) {
    $admin = $this->getInfoAdmin();
    if (empty($admin)) {
      throw new Exception ('Невозможно получить данные об администраторе');
    }
    $subject = "Разносилка. Неопределённые SMS.";
    $var['sms'] = $data;
    $body = $this->getBody('sms_unknown', $var);
    $result = $this->sendMail($admin[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Получает сведения об администраторе, вынесен для тестирования.
   * @return array|null Массив с данными об администраторе
   */
  function getInfoAdmin () {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    return $db->getUserById(1);
  }

  /**
   * Емайл с восстановленным паролем
   * @param array $user Массив с данными опользователе
   * @return bool Результат выполнения операции
   */
  function sendUserForgotMail ($user) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Восстановление пароля.";
    $var['user'] = $user;
    $body = $this->getBody('forgot', $var);
    return $this->sendMail($user[USER_EMAIL], $subject, $body);
  }

  /**
   * Подготовить тело письма для рассылки с напоминаниями
   * @param $reminding array Массив с закупками о которых нужно напомнить
   * @return string Тело письма
   */
  public function prepareRemindingPurchaseBody (array $reminding) {
    $var['reminding'] = $reminding;
    $body = $this->getBody('reminding', $var);
    return $body;
  }

  /**
   * Получить тело письма
   * @param $template string Имя шаблона письма
   * @param $var array Массив с переменными для генерации письма
   * @return string Тело письма
   */
  function getBody ($template, array $var) {
    // Инициализация
    $body = array();
    // Получение тела письма
    $htmlBody = $this->getHtmlBody($template, $var);
    $plainBody = $this->getPlainBody($template, $var);
    $body[] = "--{$this->boundary}";
    $body[] = "Content-type: text/plain; charset=utf-8";
    $body[] = $plainBody;
    $body[] = '';
    $body[] = "--{$this->boundary}";
    $body[] = "Content-type: text/html; charset=utf-8";
    $body[] = $htmlBody;
    $body[] = '';
    $body[] = "--{$this->boundary}--";
    $body = implode("\r\n", $body);
    return $body;
  }

  /**
   * Получить письмо в формате HTML
   * @param $template string Имя шаблона письма
   * @param $var array Массив с переменными для генерации письма
   * @return string Письмо в формате HTML
   * @throws Exception
   */
  function getHtmlBody ($template, array $var) {
    ob_start();
    $var['template'] = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tpl_mail_path') . '/html/' . $template . '.tpl.php';
    $var['mode'] = $this->mode;
    $path = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tpl_mail_path') . '/html/body.tpl.php';
    require($path);
    $htmlBody = ob_get_clean();
    return $htmlBody;
  }

  /**
   * Получить письмо в текстовом формате
   * @param $template string Имя шаблона письма
   * @param $var array Массив с переменными для генерации письма
   * @return string Письмо в текстовом формате
   * @throws Exception
   */
  function getPlainBody ($template, array $var) {
    ob_start();
    $var['mode'] = $this->mode;
    $path = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tpl_mail_path') . '/plain/' . $template . '.tpl.php';
    require($path);
    $htmlBody = ob_get_clean();
    return $htmlBody;
  }

  /**
   * Получить примеры всех шаблонов для писем
   * @return array Массив с примерами всех шаблонов для писем
   */
  function getExamplesAllTpl () {
    // Инициализация
    $result = array();
    $user[USER_ID] = 1;
    $user[USER_LOGIN] = 'login';
    $user[USER_PASSWORD] = 'pass';
    $user[USER_REG_DATE] = date('Y-m-d H:i:s');
    $user[USER_TMP_EMAIL] = 'test@test.ru';
    $user[USER_REQUEST] = 1;
    $sms[] = array(SMS_UNKNOWN_TEXT => 'Тестовая СМС №1');
    $sms[] = array(SMS_UNKNOWN_TEXT => 'Тестовая СМС №2');
    $purchase[] = array(PURCHASE_ID => 1, PURCHASE_NAME => 'Тестовая закупка №1');
    $purchase[] = array(PURCHASE_ID => 2, PURCHASE_NAME => 'Тестовая закупка №2');
    $pay = array('date_done' => date('H:i d.m.Y'));
    $payment = array(
      'day' => 30,
      'date_done' => date('H:i d.m.Y'),
      'url_step' => URL::to('help/first_steps'),
      'url_help' => URL::to('help')
    );
    // Получение шаблона welcome_curl
    $var['user'] = $user;
    $result['plain']['welcome_curl'] = $this->getPlainBody('welcome_curl', $var);
    $result['html']['welcome_curl'] = $this->getHtmlBody('welcome_curl', $var);
    // Получение шаблона welcome_extension
    $var['user'] = $user;
    $result['plain']['welcome_extension'] = $this->getPlainBody('welcome_extension', $var);
    $result['html']['welcome_extension'] = $this->getHtmlBody('welcome_extension', $var);
    // Получение шаблона activate
    $key = User::getActivateKey($user[USER_REG_DATE]);
    $query = array('id' => $user[USER_ID], 'activate' => $key);
    $var['link'] = URL::to('user/activate', $query);
    $result['plain']['activate'] = $this->getPlainBody('activate', $var);
    $result['html']['activate'] = $this->getHtmlBody('activate', $var);
    // Получение шаблона registration
    $result['plain']['registration'] = $this->getPlainBody('registration', $var);
    $result['html']['registration'] = $this->getHtmlBody('registration', $var);
    // Получение шаблона sms_unknown
    $var['sms'] = $sms;
    $result['plain']['sms_unknown'] = $this->getPlainBody('sms_unknown', $var);
    $result['html']['sms_unknown'] = $this->getHtmlBody('sms_unknown', $var);
    // Получение шаблона forgot
    $result['plain']['forgot'] = $this->getPlainBody('forgot', $var);
    $result['html']['forgot'] = $this->getHtmlBody('forgot', $var);
    // Получение шаблона reminding
    $var['reminding'] = $purchase;
    $result['plain']['reminding'] = $this->getPlainBody('reminding', $var);
    $result['html']['reminding'] = $this->getHtmlBody('reminding', $var);
    // Получение шаблона reminding_pay
    $var['info'] = $pay;
    $result['plain']['reminding_pay'] = $this->getPlainBody('reminding_pay', $var);
    $result['html']['reminding_pay'] = $this->getHtmlBody('reminding_pay', $var);
    // Получение шаблона payment
    $var['payment'] = $payment;
    $result['plain']['payment'] = $this->getPlainBody('payment', $var);
    $result['html']['payment'] = $this->getHtmlBody('payment', $var);
    // Получение шаблона blocked
    $result['plain']['blocked'] = $this->getPlainBody('blocked', array());
    $result['html']['blocked'] = $this->getHtmlBody('blocked', array());
    // Получение шаблона unblocked
    $result['plain']['unblocked'] = $this->getPlainBody('unblocked', array());
    $result['html']['unblocked'] = $this->getHtmlBody('unblocked', array());
    // Получение шаблона gift
    $var['payment'] = $payment;
    $result['plain']['gift'] = $this->getPlainBody('gift', $var);
    $result['html']['gift'] = $this->getHtmlBody('gift', $var);
    // Получение шаблона change_email
    $query = array('id' => $user[USER_ID], 'activate' => md5($user[USER_TMP_EMAIL] . SALT));
    $var['link'] = URL::to('user/change', $query);
    $result['plain']['change_email'] = $this->getPlainBody('change_email', $var);
    $result['html']['change_email'] = $this->getHtmlBody('change_email', $var);
    // Получение шаблона reminding_forgot
    $var['reminding'] = $purchase;
    $result['plain']['reminding_forgot'] = $this->getPlainBody('reminding_forgot', $var);
    $result['html']['reminding_forgot'] = $this->getHtmlBody('reminding_forgot', $var);
    return $result;
  }

  /**
   * Подготовить тело письма для рассылки с напоминаниями об оплате сервиса
   * @param $payInfo array Массив с информацией для генерации письма
   * @return string Тело письма
   */
  public function prepareRemindingPayBody (array $payInfo) {
    $var['info'] = $payInfo;
    $body = $this->getBody('reminding_pay', $var);
    return $body;
  }

  /**
   * Отправить уведомление пользователю, о том что поступил платёж
   * @param array $user Массив с данными о пользователе
   * @param $data array Массив с информацией о платеже, формата:
   *  - ['day'] - количество оплаченных дней услуги
   *  - ['date_done'] - дата до которой будет оказываться услуга
   * @return bool Результат отсылки письма
   * @throws Exception
   */
  public function sendUserNotifyPaymentMail (array $user, array $data) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Услуга оплачена.";
    $var['payment'] = $data;
    $body = $this->getBody('payment', $var);
    $result = $this->sendMail($user[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Послать письмо пользователю о блокировке аккаунта
   * @param array $user Массив с данными о пользователе
   * @return bool Результат отсылки письма
   */
  public function sendUserBlocked (array $user) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Аккаунт заблокирован.";
    $var = array();
    $body = $this->getBody('blocked', $var);
    $result = $this->sendMail($user[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Послать письмо пользователю о разблокировке аккаунта
   * @param array $user Массив с данными о пользователе
   * @return bool Результат отсылки письма
   */
  public function sendUserUnblocked (array $user) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Аккаунт разблокирован.";
    $var = array();
    $body = $this->getBody('unblocked', $var);
    $result = $this->sendMail($user[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Послать письмо пользователю с уведомлением о получении пробного периода
   * @param array $user Массив с данными о пользователе
   * @param $data array Массив с информацией о пробном периоде, формата:
   *  - ['day'] - количество полученных дней услуги
   *  - ['date_done'] - дата до которой будет оказываться услуга
   *  - ['url_help'] - URL на помощь
   *  - ['url_step'] - URL на первые шаги
   * @return bool Результат отсылки письма
   */
  public function sendUserGiftMail (array $user, array $data) {
    $this->validateUserArray($user);
    $subject = "Разносилка. Пробный период получен.";
    $var['payment'] = $data;
    $body = $this->getBody('gift', $var);
    $result = $this->sendMail($user[USER_EMAIL], $subject, $body);
    return $result;
  }

  /**
   * Отсылает письмо пользователю для подтверждения нового email при смене email
   * @param array $user Массив данных о пользователе из таблицы users
   * @return bool true если письмо отправлено
   * @throws Exception
   */
  public function sendUserChangeEmailMail ($user) {
    $this->validateUserArray($user);
    if (empty($user[USER_TMP_EMAIL])) {
      throw new Exception('Массив $user повреждён');
    }
    $subject = "Разносилка. Смена e-mail.";
    $query = array('id' => $user[USER_ID], 'activate' => md5($user[USER_TMP_EMAIL] . SALT));
    $var['link'] = URL::to('user/change', $query);
    $body = $this->getBody('change_email', $var);
    return $this->sendMail($user[USER_TMP_EMAIL], $subject, $body);
  }

  /**
   * Подготовить тело письма для рассылки с напоминаниями о непроставленных закупках
   * @param $reminding array Массив с закупками о которых нужно напомнить
   * @return string Тело письма
   */
  public function prepareRemindingForgotPurchaseBody (array $reminding) {
    $var['reminding'] = $reminding;
    $body = $this->getBody('reminding_forgot', $var);
    return $body;
  }

}