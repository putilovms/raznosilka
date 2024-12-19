<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\EditPay.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_EditPay Контроллер для редактора закупки
 */
class Controller_Purchase extends Controller {

  /**
   * Редактор закупки (ручной поиск платежей, удаление, изменение суммы платежа)
   */
  function index () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $this->postReset();
    $editorPurchase = new EditorPurchase();
    $arg = isset($_REQUEST['view']) ? $_REQUEST['view'] : '';
    // Получаем данные для вывода
    $view = $editorPurchase->getPageEditorPurchase($arg);
    $data = $editorPurchase->getJsonPageEditorPurchase($view);
    // Добавить данные для вывода
    $this->template->addCodeJs($data);
    $this->template->addFileJs(URL::to('files/extension.js'));
    $this->template->addFileJs(URL::to('files/editor.js'));
    $this->template->set('purchase', $view);
    $this->template->setTitle('Обзор и редактирование закупки');
    $this->template->show('purchase');
  }

  /**
   * Принудительное обновление объекта с закупкой
   */
  function update () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    // Получаем URL для редиректа
    $url = Kit::getRefererURL();
    // Если URL получен
    if (!empty($url)) {
      // Обновляем объект с закупкой
      Cache::deletePurchaseCache();
      $this->notify->sendNotify('Закупка успешно обновлена', SUCCESS_NOTIFY);
      // Редирект на целевую страницу
      $this->headerLocation($url);
    }
    // Вывод ошибки, если URL для редиректа не получен
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Поиск SMS для редактора закупок
   */
  function search () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    if (isset($_POST['lot']) and isset($_POST['pay'])) {
      // Генерировать запрос для поиска СМС
      $editorPurchase = new EditorPurchase();
      $result = $editorPurchase->initSearchSMS($_POST['lot'], $_POST['pay']);
      if ($result) {
        // Редирект на целевую страницу
        $url = $editorPurchase->getRedirectURL();
        $this->headerLocation($url);
      }
    } else { // todo перенести в отдельный контроллер и запускать его отсюда
      // Вывести форму поиска СМС
      $search = new Search_Pay();
      if ($search->init($_GET)) {
        $view = $search->getView();
        $data = $search->getJsonSearch();
        // Добавить данные для вывода
        $this->template->addCodeJs($data);
        $this->template->set('search', $view);
        $this->template->addFileJs(URL::to('files/extension.js'));
        $this->template->addFileJs(URL::to('files/editor.js'));
        $this->template->setTitle('Поиск SMS');
        $this->template->show('search');
      }
    }
    // Вывод ошибки если не плучены данные для поиска
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Проставление выбранного платежа
   */
  function filling () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $idSms = isset($_POST['sms_id']) ? $_POST['sms_id'] : '';
    $lotNumber = isset($_POST['lot']) ? $_POST['lot'] : '';
    $payNumber = isset($_POST['pay']) ? $_POST['pay'] : '';
    $editorPurchase = new EditorPurchase();
    // Проставление выбранного платежа
    $result = $editorPurchase->payFilling($lotNumber, $payNumber, $idSms);
    // Вывод уведомления о результате операции
    if ($result) {
      $this->notify->sendNotify('Оплата успешно проставлена', SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось проставить оплату', ERROR_NOTIFY);
    }
    // Редирект на целевую страницу
    $url = $editorPurchase->getRedirectURL();
    $this->headerLocation($url);
  }

}