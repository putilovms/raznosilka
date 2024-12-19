<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Service.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Service Контроллер содержищай основной функционал сервиса
 */
class Controller_Service extends Controller {
  /**
   * Главная страница сервиса
   */
  function index () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $purchase = new PurchaseHelper();
    // Проверка выбранной закупки
    $select = $purchase->getSelectPurchaseInfo();
    $this->template->set('select', $select);
    $this->template->setTitle('Работа с закупками');
    $this->template->show('service');
  }

  /**
   * Загрузка SMS
   */
  function upload () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    // Если имеются файлы для загрузки, обработать их
    if (isset($_FILES['files'])) {
      $load = new Uploader();
      $load->execute($_FILES['files']);
      $this->forwarder->save('upload', $load->getInfo());
      $this->postReset();
    }
    $data = $this->forwarder->load('upload');
    $info = $this->forwarder->getInfo('upload');
    if ($data) {
      $this->template->set('upload', $data);
      $this->template->set('info', $info);
    }
    $this->template->setTitle('Загрузка SMS');
    $this->template->show('upload');
  }

  /**
   * Вывод списка закупок из организаторской
   */
  function purchase_org () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $this->postReset();
    $listPurchase = new ListPurchase();
    $filter = (isset($_GET['filter'])) ? $_GET['filter'] : '';
    $list = $listPurchase->getListPurchaseFromOrganizer($filter);
    $json = $listPurchase->getJsonListPurchaseFromOrganizer($list);
    // Добавить данные для вывода
    $this->template->addCodeJs($json);
    $this->template->addFileJs(URL::to('files/extension.js'));
    $this->template->set('list', $list);
    $this->template->setTitle("Список закупок из организаторкской");
    $this->template->show('purchase_org');
  }

  /**
   * Вывод списка закупок сохранённых в сервисе
   */
  function purchase_all () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $this->postReset();
    $listPurchase = new ListPurchase();
    $filter = (isset($_GET['filter'])) ? $_GET['filter'] : '';
    $list = $listPurchase->getListPurchaseFromService($filter);
    $this->template->set('list', $list);
    $this->template->setTitle('Список закупок сохранённых в «Разносилке»');
    $this->template->show('purchase_all');
  }

  /**
   * Сохранение выбранной закупки
   */
  function set_purchase () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    if (isset($_GET['purchase'])) {
      $purchase = new PurchaseHelper();
      $idPurchase = (int)$_GET['purchase'];
      $purchase->setPurchase($idPurchase);
    }
    $url = URL::to('service');
    $this->headerLocation($url);
  }

  /**
   * Автопоиск оплат
   */
  function analysis () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $this->postReset();
    $analysis = new Analysis();
    // Получаем данные для вывода
    $view = $analysis->getPageAnalyzer();
    $data = $analysis->getJsonPageAnalyzer($view);
    // Добавить данные для вывода
    $this->template->addCodeJs($data);
    $this->template->addFileJs(URL::to('files/extension.js'));
    $this->template->set('purchase', $view);
    $this->template->setTitle('Автопоиск оплат');
    $this->template->show('analysis');
  }

  /**
   * Авторазнесение платежей
   */
  function filling () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $view = array();
    $filling = new Filling();
    if (!empty($_POST)) {
      // Если пользователь выбрал СМС
      $filling->setSelectedSms($_POST);
      $this->postReset();
    } else {
      // Если данных для выбора СМС нет - выводим платежи
      $view = $filling->getView();
      $requestData = $filling->getJsonRequestData();
      // Добавить массив с запросами для проставления оплат
      $this->template->addCodeJs($requestData);
    }
    $this->template->addFileJs(URL::to('files/extension.js'));
    $this->template->addFileJs(URL::to('files/filling.js'));
    $this->template->set('purchase', $view);
    $this->template->setTitle('Автопроставление оплат');
    $this->template->show('filling');
  }

  /**
   * Поиск СМС
   */
  function search () {
    $this->access(USER_AUTH);
    $this->access(NOT_BLOCKED);
    $this->access(ACTIVATE);
    $this->access(USER_BIND);
    $this->access(USER_HAVE_LOGIN_SP);
    $this->access(USER_PAY);
    $search = new Search_SMS();
    $search->init($_GET);
    $view = $search->getView();
    $this->template->set('search', $view);
    $this->template->setTitle('Поиск СМС');
    $this->template->show('search');
  }

  /**
   * Возврат СМС
   */
  function return_set () {
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
      $idSms = isset($_POST['sms_id']) ? $_POST['sms_id'] : '';
      $editorSMS = new EditorSMS();
      // Возврат СМС
      $result = $editorSMS->setReturnSMS($idSms);
      // Вывод уведомления о результате операции
      if ($result) {
        $this->notify->sendNotify('SMS успешно возвращена', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось отметить SMS как возвращённую', ERROR_NOTIFY);
      }
      // Редирект на целевую страницу
      $this->headerLocation($url);
    }
    // Вывод ошибки, если URL для редиректа не получен
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Отменить возврат СМС
   */
  function return_del () {
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
      $idSms = isset($_POST['sms_id']) ? $_POST['sms_id'] : '';
      $editorSMS = new EditorSMS();
      // Отменить возврат СМС
      $result = $editorSMS->delReturnSMS($idSms);
      // Вывод уведомления о результате операции
      if ($result) {
        $this->notify->sendNotify('Отмена возврата SMS прошла успешно', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не отменить возврат SMS', ERROR_NOTIFY);
      }
      // Редирект на целевую страницу
      $this->headerLocation($url);
    }
    // Вывод ошибки, если URL для редиректа не получен
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

}