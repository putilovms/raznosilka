<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Controller.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Абстрактный класс Controller содержит стандартные свойства и методы контроллера
 */
abstract class Controller {
  /**
   * @var Template Объект шаблона вывода
   */
  protected $template;
  /**
   * @var User Объект текущего пользователя
   */
  protected $user;
  /**
   * @var Notify Объект отвечающий за вывод сообщений
   */
  protected $notify;
  /**
   * @var Forwarder Объект отвечающий за пересылку результатов выполнения модуля
   */
  protected $forwarder;

  /**
   * Конструктор в котором создаётся объект шаблона и проверяется авторизация пользователя.
   * При проверке авторизации пользователя в свойство $auth записывается результат проверки авторизованности пользователя,
   * а так же создаётся переменная в шаблоне показывающая состояние авторизации пользователя.
   */
  function __construct () {
    $className = $this->getClassName();
    $this->template = new Template($className);
    $this->notify = new Notify();
    $this->forwarder = new Forwarder();
    $this->user = Registry_Request::instance()->get('user');
    // Режим обслуживания
    $mode = Registry_Request::instance()->get('mode');
    $this->serviceMode($mode);
  }

  /**
   * Получить имя текущего класса
   * @return string
   */
  function getClassName () {
    // Получить полное имя класса без пространства имён
    $className = get_class($this);
    $className = explode('\\', $className);
    $className = end($className);
    // Получить имя класса без его родителя
    $className = explode('_', $className);
    $className = end($className);
    return $className;
  }

  /**
   * Если сайт на обслуживании то автоматически запрещает пользователям вывод запрашиваемого содержания
   * @param string $mode Режим работы сайта
   */
  protected function serviceMode ($mode) {
    $this->runServiceController($mode);
  }

  /**
   * Вынесено для возможности ручного запуска в тех контроллерах, в которых
   * отменена автоматический вывод сообщения о том, что сайт находится на обслуживании.
   * @param string $mode Режим работы сайта
   */
  protected function runServiceController ($mode) {
    if ($mode == 'service' and !$this->user->isAdmin()) {
      $this->runController('Admin', 'maintenance');
    }
  }

  /**
   * Вспомогательная функция запуска пользовательского контроллера
   * @param string $controllerName Имя контроллера
   * @param string $methodName Имя метода
   */
  private function runController ($controllerName, $methodName) {
    $controllerName = "Controller_" . $controllerName;
    $controller = new $controllerName();
    $controller->$methodName();
  }

  /**
   * Вспомогательный метод для сброса POST запроса
   */
  function postReset () {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $url = URL::current();
      header("Location: {$url}");
      exit;
    }
  }

  /**
   * Абстрактная функция отвечающая за вывод по умолчанию.
   */
  abstract function index ();

  /**
   * Метод проверяет права на доступ к запрашиваемой странице
   * @param int $param Роль пользователя необходимая для доступа к запрашиваемой странице,
   * значение по умолчанию GUEST
   * @param string $controllerName Произвольный контроллер загружаемый в случае если у пользователя отсутствует
   * права доступа к запрашиваемой странице. В случае если необходимо совершить действие отличное от действия по умолчанию.
   * @param string $methodName Имя Произвольный метод загружаемый в случае если у пользователя отсутствует
   * права доступа к запрашиваемой странице. В случае если необходимо совершить действие отличное от действия по умолчанию.
   */
  function access ($param = GUEST, $controllerName = '', $methodName = '') {
    switch ($param) {
      case ADMIN : // Проверка права администратора
        // Если пользователь не администратор - доступ запрещён
        if (!$this->user->isAdmin()) {
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(ADMIN);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case GUEST: // Проверка права гостя
        // Если прользователь не является гостем
        if ($this->user->isAuth()) {
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(GUEST);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case USER_AUTH : // Проверка права простого пользователя
        // Если пользователь не авторизован
        if (!$this->user->isAuth()) {
          // Если пусты дополнительные параметры
          if (empty($controllerName) or empty($methodName)) {
            // Предложить войти на сайт
            $controller = new Controller_User();
            $controller->login();
          } else {
            // Если параметры указаны, запустить пользовательский контроллер
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case ACTIVATE : // Активированный пользователь
        // Проверка активированности аккаунта
        if (!$this->user->isActivate()) {
          // Если пусты дополнительные параметры
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(ACTIVATE);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case NO_ACTIVATE : // Неактивированный пользователь
        // Проверка активированности аккаунта
        if ($this->user->isActivate()) {
          // Если пусты дополнительные параметры
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(NO_ACTIVATE);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case USER_PAY: // Проверка права оплаченного пользователя
        // Если пользователь не оплатил
        if (!$this->user->isPaying()) {
          if (empty($controllerName) or empty($methodName)) {
            // Предложить оплатить сервис
            $controller = new Controller_Pay();
            $controller->index();
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case USER_BIND: // Пользователь имееет OrgID
        // Если пользователь не имееет OrgID
        $userRequest = $this->user->getUserRequest();
        switch ($userRequest) {
          // Запросы к сайту СП при помощи расширения браузера
          case REQUEST_EXTENSIONS: {

            break;
          }
          // Запросы к сайту СП при помощи curl по умолчанию
          default : {
            if (!$this->user->isBinding()) {
              if (empty($controllerName) or empty($methodName)) {
                $controller = new Controller_Error();
                $controller->noAccess(USER_BIND);
              } else {
                $this->runController($controllerName, $methodName);
              }
            }
            break;
          }
        }
        break;
      case USER_HAVE_LOGIN_SP: // Пользователь с логином и паролем от сайта СП
        // Если пользователь не имеет логина и пароля от сайта СП
        $userRequest = $this->user->getUserRequest();
        switch ($userRequest) {
          // Запросы к сайту СП при помощи расширения браузера
          case REQUEST_EXTENSIONS: {

            break;
          }
          // Запросы к сайту СП при помощи curl по умолчанию
          default : {
            if (!$this->user->isHaveLogin()) {
              if (empty($controllerName) or empty($methodName)) {
                $controller = new Controller_Error();
                $controller->noAccess(USER_HAVE_LOGIN_SP);
              } else {
                $this->runController($controllerName, $methodName);
              }
            }
            break;
          }
        }
        break;
      case USER_NOT_BIND: // Пользователь с не имееет OrgID
        // Если пользователь имееет OrgID
        if ($this->user->isBinding()) {
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(USER_NOT_BIND);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case NOT_BLOCKED: // Пользователь не заблокирован
        // Если пользователь заблокирован
        if ($this->user->isBlocked()) {
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(NOT_BLOCKED);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
      case USER_CURL: // Прямой запрос к сайту СП
        // Если запрос не прямой
        if ($this->user->getUserRequest() !== REQUEST_CURL) {
          if (empty($controllerName) or empty($methodName)) {
            $controller = new Controller_Error();
            $controller->noAccess(USER_CURL);
          } else {
            $this->runController($controllerName, $methodName);
          }
        }
        break;
    }

  }

  /**
   * Вспомогательный метод для перенаправления пользователя по заданному адресу
   * @param string $url Относительный URL на который необходимо перенаправить пользователя
   */
  protected function headerLocation ($url) {
    header("Location: " . $url);
    exit;
  }

}