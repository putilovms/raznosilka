<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Error.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Error отвечает за обработку различных ошибочных ситуаций
 */
class Controller_Error extends Controller {

  /**
   * Стандартная страница ошибки error.tpl.php
   * @param string $line Номер строки в которой произошла ошибка
   * @param string $file Файл в котором произошла ошибка
   * @throws Exception
   */
  function index ($line='?', $file='?') {
    // Для записи в лог
    $mode = Registry_Request::instance()->get('mode');
    // Не генерировать ошибку, если в режиме отладки
    if ($mode <> 'debug') {
      trigger_error("Ошибка в строке {$line} в файле {$file}", E_USER_WARNING);
    }
    $this->template->setTitle('Ошибка');
    $this->template->show('error');
  }

  /**
   * Выводит шаблон 404.tpl.php
   */
  function notFound () {
    header("HTTP/1.1 404 Not Found"); // todo определять протокол динамически
    $this->template->setTitle('Страница не найдена');
    $this->template->show('404');
  }

  /**
   * Нет прав доступа к странице. Шаблон 403.tpl.php
   * @param $access null|int|string Требуемые права доступа или причина по которой доступ закрыт
   */
  function noAccess ($access = null) {
    header("HTTP/1.1 403 Forbidden"); // todo определять протокол динамически
    $messages = 'У вас нет доступа к данной странице.';
    if (!is_null($access)) {
      switch ($access) {
        case ADMIN :
          break;
        case GUEST :
          $messages = 'У вас нет доступа к данной странице, так как вы уже вошли в сервис.';
          break;
        case USER_AUTH :
          break;
        case ACTIVATE :
          $messages = 'У вас нет доступа к данной странице, так как ваш аккаунт не активирован.';
          break;
        case NO_ACTIVATE :
          $messages = 'У вас нет доступа к данной странице, так как ваш аккаунт уже активирован.';
          break;
        // Оплаченный пользователь
        case USER_PAY :
          break;
        // Пользователь с аккаунтом, привязанным к сайту СП
        case USER_BIND :
          $messages = 'У вас нет доступа к данной странице, так как не ввёден логин и пароль от сайта СП.';
          break;
        // Пользователь с аккаунтом, не имеющий OrgID
        case USER_NOT_BIND :
          break;
        // Пользователь имеет логин и проль от сайта СП
        case USER_HAVE_LOGIN_SP :
          $messages = 'У вас нет доступа к данной странице, так как не ввёден логин и пароль от сайта СП.';
          break;
        // Пользователь не заблокирован
        case NOT_BLOCKED :
          $messages = 'Извините, ваш аккаунт заблокирован.';
          break;
        // Не прямой запрос к сайту СП
        case USER_CURL :
          $messages =  'У вас нет доступа к данной странице, так как вы используете для доступа к сайту СП расширение браузера.';
          break;
        default :
          // Если кода прав доступа не найдено, то вывести сообщение на прямую
          $messages = $access;
          break;
      }
    }
    $this->template->set('messages', $messages);
    $this->template->setTitle('Доступ запрещен');
    $this->template->show('403');
  }

}