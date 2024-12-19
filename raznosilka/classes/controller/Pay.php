<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Cron.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Pay Контроллер для работы с платёжной системой
 */
class Controller_Pay extends Controller {

  /**
   * Цены и оплата
   */
  function index () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(NOT_BLOCKED);
    $page = OrderUser::getPayPage();
    $this->template->set('page', $page);
    // Добавление мета тегов
    $title = 'Цены и оплата Разносилки';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('pay');
  }

  /**
   * Для отмены автоматической загрузки сообщения о технических работах
   * @param string $mode Для соблюдения Strict Standards
   */
  function serviceMode ($mode) {
  }

  /**
   * На этот адрес будут приходить запрос checkOrder, проверка заказа перед оплатой
   */
  function check () {
    // todo сюда может прийти как checkOrder так и cancelOrder
    $payment = PaymentSystem::getPaymentSystem();
    $payment->processRequest('checkOrder', $_REQUEST);
  }

  /**
   * На этот адрес будут приходить запрос paymentAviso, уведомления о платежах
   */
  function aviso () {
    $payment = PaymentSystem::getPaymentSystem();
    $payment->processRequest('paymentAviso', $_REQUEST);
  }

  /**
   * URL страницы, на которую пользователь может перейти после платежа по ссылке «Вернуться в магазин».
   */
  function success () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(USER_PAY);
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    if ($action == 'PaymentSuccess') {
      // Получить дату до которой предоставляется услуга
      $date = strftime('%H:%M %d.%m.%Y', $this->user->getPayingDate());
      $this->notify->sendNotify("Услуга успешно оплачена до {$date}", SUCCESS_NOTIFY);
      // Редирект на главную страницу
      $this->headerLocation(URL::base());
    }
    $controller = new Controller_Error();
    $controller->noAccess();
  }

  /**
   * Страница для ошибки при платеже
   */
  function fail () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    if ($action == 'PaymentFail') {
      $this->notify->sendNotify("Не удалось оплатить услугу", ERROR_NOTIFY);
      // Редирект на главную страницу
      $this->headerLocation(URL::base());
    }
    $controller = new Controller_Error();
    $controller->noAccess();
  }

  /**
   * На этот адрес будут приходить запрос checkOrder, проверка заказа перед оплатой в режиме тестирования платёжной системы
   */
  function check_test () {
    $payment = PaymentSystem::getPaymentSystem();
    $payment->processRequest('checkOrder', $_REQUEST, true);
  }

  /**
   *  На этот адрес будут приходить запрос paymentAviso, уведомления о платежах в режиме тестирования платёжной системы
   */
  function aviso_test () {
    $payment = PaymentSystem::getPaymentSystem();
    $payment->processRequest('paymentAviso', $_REQUEST, true);
  }

  /**
   * URL страницы, на которую пользователь может перейти после платежа по ссылке «Вернуться в магазин» в режиме тестирования платёжной системы
   */
  function success_test () {
  }

  /**
   * Страница для ошибки при платеже в режиме тестирования платёжной системы
   */
  function fail_test () {
  }

  /**
   * Пробный период
   */
  function gift () {
    parent::runServiceController(Registry_Request::instance()->get('mode')); // Для запрета вывода в режиме обслуживания
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    // Если пользователь имеет пробный период
    if ($this->user->hasGift()) {
      $order = $this->user->getOrder();
      $result = $order->activateGift();
      // Вывод уведомления о результате операции
      if ($result) {
        $this->notify->sendNotify("Пробные " . DAY_GIFT . " дней услуги получены", SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось получить пробный период использования услуги', ERROR_NOTIFY);
      }
      // Редирект на главную страницу
      $this->headerLocation(URL::base());
    }
    // Если не имеет пробный период, то выводим ошибку доступа
    $controller = new Controller_Error();
    $controller->noAccess('Пробный период уже получен, вы не можете получить его повторно.');
  }

}