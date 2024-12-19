<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Reports.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Reports Контроллер для работы с логами
 */
class Controller_Reports extends Controller {

  /**
   * Просмотр журналов (логов)
   */
  function index() {
    // Если пользователь является администратором - запуск админки
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getListLogs();
    $this->template->set('logs', $view);
    // загрузка шаблона
    $this->template->setTitle('Просмотр журналов');
    $this->template->show('reports');
  }

  /**
   * Просмотр журнала действий пользователя
   */
  function action() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getActionLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал действий пользователей');
    $this->template->show('action');
  }

  /**
   * Просмотр журнала ошибок PHP
   */
  function error() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getPhpErrorLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал ошибок PHP');
    $this->template->show('error');
  }

  /**
   * Просмотр журнала ошибок скрипта update.php
   */
  function update() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getUpdateErrorLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал ошибок скрипта update.php');
    $this->template->show('update');
  }

  /**
   * Просмотр журнала запросов к сайтам СП
   */
  function request() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getRequestLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал запросов к сайтам СП');
    $this->template->show('request');
  }

  /**
   * Просмотр журнала рассылки почты
   */
  function mail() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getMailLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал отправки почты');
    $this->template->show('mail');
  }

  /**
   * Просмотр журнала работы хрона
   */
  function cron() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getCronLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал работы хрона');
    $this->template->show('cron');
  }

  /**
   * Просмотр журнала работы платёжной системы
   */
  function payment() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getPaymentLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал работы платёжной системы');
    $this->template->show('payment');
  }

  /**
   * Просмотр журнала URL
   */
  function path() {
    $this->access(ADMIN);
    $logs = new Logs();
    $view = $logs->getPathLogForView();
    $this->template->set('log', $view);
    // загрузка шаблона
    $this->template->setTitle('Журнал URL');
    $this->template->show('path');
  }

  /**
   * Удалить журнал
   */
  function delete(){
    $this->access(ADMIN);
    // Получаем URL для редиректа
    $url = Kit::getRefererURL();
    // Если URL получен
    if (!empty($url)) {
      $logs = new Logs();
      $arg = isset($_REQUEST['log']) ? $_REQUEST['log'] : '';
      $result = $logs->delLog($arg);
      if ($result) {
        $this->notify->sendNotify('Журнал успешно удалён', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось удалить журнал', ERROR_NOTIFY);
      }
      // Редирект на целевую страницу
      $this->headerLocation($url);
    }
    // Вывод ошибки, если URL для редиректа не получен
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Удалить все журналы
   */
  function delete_all(){
    $this->access(ADMIN);
    // Получаем URL для редиректа
    $url = Kit::getRefererURL();
    // Если URL получен
    if (!empty($url)) {
      $logs = new Logs();
      $result = $logs->delAllLogs();
      if ($result) {
        $this->notify->sendNotify('Журналы успешно удалены', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось удалить журналы', ERROR_NOTIFY);
      }
      // Редирект на целевую страницу
      $this->headerLocation($url);
    }
    // Вывод ошибки, если URL для редиректа не получен
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

}