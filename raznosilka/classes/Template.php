<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Template.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Template отображение шаблона
 */
class Template {
  /**
   * @var array параметры шаблона
   * - Формат: [параметр] = значение
   */
  private $vars = array();
  /**
   * @var string Заголовок страницы по умолчанию
   */
  private $title = 'Разносилка';
  /**
   * @var Registry_Request Реестр данного запроса
   */
  private $regReq;
  /**
   * @var string Имя папки в которой лежит шаблон
   */
  private $path;
  /**
   * @var array Массив с мета тегами
   */
  private $meta = array();
  /**
   * @var array Массив с javascript кодом
   */
  private $jsCode = array();
  /**
   * @var array Массив с путями к файлам javascript
   */
  private $jsFiles = array();
  /**
   * @var User Объект с информацией о текущем пользователе
   */
  private $user;

  /**
   * Конструктор класса
   * @param $path string Имя папки в которой лежит шаблон (обычно это имя контроллера)
   */
  function __construct ($path) {
    $this->path = mb_strtolower($path);
    $this->regReq = Registry_Request::instance();
    $this->user = $this->regReq->get('user');
    $this->initJS();
  }

  /**
   * Добавление переменной в шаблон
   * @param string $varName Имя переменной
   * @param string $value Значение переменной
   * @param bool $overwrite Разрешение на перезапись существующей переменной
   * @throws Exception
   * @return bool Результат добавления переменной
   */
  function set ($varName, $value, $overwrite = false) {
    if (isset($this->vars[$varName]) == true AND $overwrite == false) {
      throw new Exception("Попытка изменить существующую переменную '{$varName}' в шаблоне");
    }
    $this->vars[$varName] = $value;
    return true;
  }

  /**
   * Удаление переменной из шаблона
   * @param string $varName Имя переменной
   * @return bool Результат удаления переменной
   */
  function remove ($varName) {
    if (!isset($this->vars[$varName])) {
      return false;
    }
    unset($this->vars[$varName]);
    return true;
  }

  /**
   * Задаёт заголовок выводимой страницы
   * @param string $title Имя заголовка
   */
  function setTitle ($title) {
    $this->title = $title;
  }

  /**
   * Вывод шаблона
   * @param string $name Имя шаблона
   * @param string $layer Имя слоя
   */
  function show ($name, $layer = 'page') {
    // Путь к основному слою
    $layer = $_SERVER['DOCUMENT_ROOT'] . $this->regReq->get('layer_path') . "/" . $layer . '.tpl.php';
    // Вывод нформации для отладки
    $var['debug'] = $_SERVER['DOCUMENT_ROOT'] . $this->regReq->get('tpl_path') . "/debug.tpl.php";
    // Создание массива с данными для вывода
    $var['content'] = $_SERVER['DOCUMENT_ROOT'] . $this->regReq->get('tpl_path') . "/" . $this->path . "/" . $name . '.tpl.php';
    $var['var'] = $this->vars;
    $var['title'] = $this->title;
    $var['meta'] = $this->getMetaTags();
    $var['codeJs'] = $this->getCodeJs();
    $var['filesJs'] = $this->getFilesJs();
    $var['user'] = $this->user->getUserInfo();
    $var['system']['notify'] = $this->getNotify();
    $var['system']['mode'] = $this->regReq->get('mode');
    $var['system']['email_admin'] = $this->regReq->get('email_admin');
    $extensionChromeId = $this->regReq->get('chrome_extension_id');
    $var['system']['chrome_extension_id'] = $extensionChromeId;
    // Информация о выбранной закупке
    $var['system']['select'] = PurchaseHelper::getSelectPurchaseInfo();
    // Отсчёт начала отрисовки
    $info = $this->regReq->get('info');
    $info->getTimePiece();
    // Загрузка отображения
    require_once($layer);
    exit;
  }

  /**
   * Метод получает все сообщения для последующего вывода
   * @return array Сообщения
   */
  public function getNotify () {
    $result = array();
    // Получение сообщений
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      $notify = new Notify();
      $result = $notify->getAllNotify();
    }
    // Получить тип запроса к сайту СП
    $userRequest = $this->user->getUserRequest();
    switch ($userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {

        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        // Не введён логин или пароль от сайта СП
        if (!$this->user->isHaveLogin() and $this->user->isAuth() and $this->user->isActivate() and !$this->user->isBlocked()) {
          $result[] = $this->getNotifyNotHaveLogin();
        }
        break;
      }
    }
    // Пользователь не оплатил услугу
    if (!$this->user->isPaying() and $this->user->isAuth() and $this->user->isActivate() and !$this->user->isBlocked()) {
      $result[] = $this->getNotifyNotPaying();
    }
    // Сайт в режиме обслуживания
    $mode = $this->regReq->get('mode');
    if ($mode == 'service' and $this->user->isAdmin()) {
      $result[] = $this->getNotifyMaintenance();
    }
    // Если обнаружен update.php
    $admin = new Admin();
    if ($admin->foundUpdateScript() and $this->user->isAdmin() and $mode != 'debug') {
      $result[] = $this->getNotifyFileUpdate();
    }
    // Аккаунт не активирован
    if (!$this->user->isActivate() and $this->user->isAuth() and !$this->user->isBlocked()) {
      $result[] = $this->getNotifyActivate();
    }
    // Пользователь начал смену email
    if ($this->user->isChangeEmail() and $this->user->isAuth() and $this->user->isActivate() and !$this->user->isBlocked()) {
      $result[] = $this->getNotifyChangeEmail();
    }
    return $result;
  }

  /**
   * Получить сообщение о том что сайт находится в режиме технического обслуживания
   * @return array Массив с сообщением
   */
  function getNotifyMaintenance () {
    $url = URL::to('admin/settings');
    $result = Notify::convertNotify("Сайт находится в режиме  <a href='{$url}'>технического обслуживания</a>.", INFO_NOTIFY);
    return $result;
  }

  /**
   * Получить сообщение о том что пользователь не ввёл логин и пароль от сайта СП
   * @return array Массив с сообщением
   */
  function getNotifyNotHaveLogin () {
    $url = URL::to('user/password');
    $result = Notify::convertNotify("Не ввёден логин и пароль от сайта СП. Вам необходимо <a href='{$url}'>ввести логин и пароль от сайта СП</a> для полноценной работы с сервисом.", INFO_NOTIFY);
    return $result;
  }

  /**
   * Получить сообщение о том что пользователь не оплатил услугу
   * @return array Массив с сообщением
   */
  function getNotifyNotPaying () {
    $url = URL::to('pay');
    if ($this->user->hasGift()) {
      $result = Notify::convertNotify("<a href='{$url}'>Получите бесплатно</a> первые " . DAY_GIFT . " дней работы с «Разносилкой»", SUCCESS_NOTIFY);
    } else {
      $result = Notify::convertNotify("Срок предоставления услуги истек, для продолжения работы с сервисом <a href='{$url}'>оплатите услугу</a>", INFO_NOTIFY);
    }
    return $result;
  }

  /**
   * Получить сообщение о том что обнаружен update.php
   * @return array Массив с сообщением
   */
  function getNotifyFileUpdate () {
    $urlDel = URL::to('admin/update_del');
    $urlRun = URL::to('update.php');
    $result = Notify::convertNotify("Обнаружен файл <b>update.php</b>, что нужно сделать? [<a href='{$urlRun}' target='_blank'>Запустить</a>] или [<a href='{$urlDel}'>Удалить</a>]", INFO_NOTIFY);
    return $result;
  }

  /**
   * Получить сообщение о том что пользователь не активировал аккаунт
   * @return array Массив с сообщением
   */
  function getNotifyActivate () {
    $mail = $this->user->getUserEmail();
    $url = URL::to('user/reactivate');
    $result = Notify::convertNotify("Пожалуйста, активируйте аккаунт при помощи ссылки, которая отправлена по адресу <a href='mailto:{$mail}'>{$mail}</a> указанному при регистрации.</br><a href='{$url}'>Отправить повторное письмо</a>", INFO_NOTIFY);
    // $result = Notify::convertNotify("Письмо не пришло в течение 10 минут? <a href='{$url}'>Отправить повторное письмо</a> для активации.", INFO_NOTIFY);
    return $result;
  }

  /**
   * Получить сообщение о том что пользователь начал смену e-mail
   * @return array Массив с сообщением
   */
  function getNotifyChangeEmail () {
    $mail = $this->user->getUserTmpEmail();
    $result = Notify::convertNotify("Подтвердите смену e-mail при помощи ссылки, которая отправлена по адресу <a href='mailto:{$mail}'>{$mail}</a>", INFO_NOTIFY);
    return $result;
  }

  /**
   * Получить список мета тегов
   * @return string Список мета тегов
   */
  private function getMetaTags () {
    $result = implode("\r\n", $this->meta);
    return $result;
  }

  /**
   * Добавить метатеги для выводимой страницы
   * @param $name string Имя тега
   * @param array $attributes Список аттрибутов
   * @throws Exception
   */
  function addMetaTag ($name, array $attributes) {
    if (empty($name)) {
      throw new Exception('Не указано имя тега');
    }
    if (empty($attributes)) {
      throw new Exception('Массив с аттрибутами пуст');
    }
    $attributesStr = '';
    foreach ($attributes as $nameAttr => $valAttr) {
      $attributesStr .= "{$nameAttr}='{$valAttr}' ";
    }
    $attributesStr = trim($attributesStr);
    $tag = "<{$name} {$attributesStr}>";
    $this->meta[] = $tag;
  }

  /**
   * Получить javascript код
   * @return string javascript код
   */
  private function getCodeJs () {
    $result = implode("\r\n", $this->jsCode);
    return $result;
  }

  /**
   * Добавить javascript код для выводимой страницы
   * @param $js string Строки js кода, который необходимо добавить на страницу
   */
  function addCodeJs ($js) {
    $this->jsCode[] = $js;
  }

  /**
   * Получить тэги для подключения javascript файлов
   * @return string Тэги для подключения javascript файлов
   */
  private function getFilesJs () {
    $result = '';
    foreach ($this->jsFiles as $path) {
      $result .= "<script type='text/javascript' src='{$path}'></script>";
      $result .= "\r\n";
    }
    $result = rtrim($result, "\r\n");
    return $result;
  }

  /**
   * Добавить полный путь к файлу javascript
   * @param $js string Полный путь к файлу javascript
   */
  function addFileJs ($js) {
    $this->jsFiles[] = $js;
  }

  /**
   * Инициализация дополнительного JS для работы расширения
   */
  private function initJS () {
    // Добавить режим запроса к сайту СП
    $userInfo = $this->user->getUserInfo();
    $userRequest = (int)$userInfo[USER_REQUEST];
    $js = "var " . USER_REQUEST_JS . " = {$userRequest};";
    $this->addCodeJs($js);
    // Добавить статус залогиненности пользователя
    $auth = (int)$this->user->isAuth();
    $js = "var USER_AUTH = {$auth};";
    $this->addCodeJs($js);
    // Добавить ID расширения Google Chrome
    $extensionChromeId = $this->regReq->get('chrome_extension_id');
    $js = "var " . EXTENSION_ID_JS . " = \"{$extensionChromeId}\";";
    $this->addCodeJs($js);
  }

}
