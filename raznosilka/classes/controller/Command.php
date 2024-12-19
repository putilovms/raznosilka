<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Command.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Command Контроллер отвечающий за работу с Ajax запросами
 */
class Controller_Command extends Controller {

  /**
   * Приём команд /command
   */
  function index () {
    $this->access(USER_AUTH);
    if (isset($_REQUEST['cmd'])) {
      // Проверка прав доступа к API, за исключением некоторых команд
      if ($_REQUEST['cmd'] !== 'get_cookie_info') {
        $this->access(NOT_BLOCKED);
        $this->access(ACTIVATE);
        $this->access(USER_BIND);
        $this->access(USER_PAY);
      }
      $command = new Command();
      switch ($_REQUEST['cmd']) {
        // Автоматическое проставление платежа на сайте СП
        case 'auto_filling' : // todo нет обработки 500 ошибки для JS
          if (isset($_REQUEST['lot'])) {
            $lot = (int)$_REQUEST['lot'];
            $body = isset($_REQUEST['body']) ? $_REQUEST['body'] : '';
            $request = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';
            $result = $command->filling('auto', $lot, $body, $request);
            return print $result;
          }
          break;
        // Ручное проставление платежа (только сохранение с БД)
        case 'manual_filling' : // todo нет обработки 500 ошибки для JS
          if (isset($_REQUEST['lot'])) {
            $lot = (int)$_REQUEST['lot'];
            $result = $command->filling('manual', $lot);
            return print $result;
          }
          break;
        // Проверка внесённой суммы и найденной Разносилкой
        case 'check_total' : // todo нет обработки 500 ошибки для JS
          if (isset($_REQUEST['sum'])) {
            $sum = (float)$_REQUEST['sum'];
            $body = isset($_REQUEST['body']) ? $_REQUEST['body'] : '';
            $request = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';
            $result = $command->checkTotal($sum, $body, $request);
            return print $result;
          }
          break;
        // Получение списка закупок через запрос от расширения
        case 'list_org' :
          if (isset($_REQUEST['body']) and isset($_REQUEST['request'])) {
            $body = $_REQUEST['body'];
            $request = $_REQUEST['request'];
            $result = $command->getListPurchase($request, $body);
            return print $result;
          }
          break;
        // Получение закупки для автоматического поиска СМС через запрос от расширения
        case 'auto_analysis' :
          if (isset($_REQUEST['body']) and isset($_REQUEST['request'])) {
            $body = $_REQUEST['body'];
            $request = $_REQUEST['request'];
            $result = $command->getPageAnalysis($request, $body);
            return print $result;
          }
          break;
        // Получить данные для расшифровки cookie для выбранного пользователем сайта СП
        case 'get_cookie_info' :
          $result = $command->getCookieInfo();
          return print $result;
          break;
        // Получить данные для редактора закупок
        case 'editor_purchase' :
          if (isset($_REQUEST['body']) and isset($_REQUEST['request'])) {
            $body = $_REQUEST['body'];
            $request = $_REQUEST['request'];
            $result = $command->getEditorPurchase($request, $body);
            return print $result;
          }
          break;
        // Отметить оплату как ошибочную
        case 'error_set' :
          if (isset($_REQUEST['view'])) {
            $arg = $_REQUEST['view'];
            $lotNumber = isset($_REQUEST['lot']) ? $_REQUEST['lot'] : '';
            $payNumber = isset($_REQUEST['pay']) ? $_REQUEST['pay'] : '';
            $result = $command->payErrorSet($lotNumber, $payNumber, $arg);
            return print $result;
          }
          break;
        // Удалить отметку ошибочности платежа
        case 'error_del' :
          if (isset($_REQUEST['view'])) {
            $arg = $_REQUEST['view'];
            $lotNumber = isset($_REQUEST['lot']) ? $_REQUEST['lot'] : '';
            $payNumber = isset($_REQUEST['pay']) ? $_REQUEST['pay'] : '';
            $result = $command->payErrorDel($lotNumber, $payNumber, $arg);
            return print $result;
          }
          break;
        // Обновить сумму в заказе
        case 'update_sum' :
          $notify = new Notify();
          $isNotify = isset($_REQUEST['notify']) ? $_REQUEST['notify'] : false;
          if (isset($_REQUEST['lot']) and isset($_REQUEST['view'])) {
            $arg = $_REQUEST['view'];
            $lot = $_REQUEST['lot'];
            $body = isset($_REQUEST['body']) ? $_REQUEST['body'] : '';
            $request = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';
            $result = $command->updateSum($lot, $arg, $body, $request);
            if ($result !== 'false') {
              // Уведомление о проставлении оплаты
              if ($isNotify) {
                $notify->sendNotify('Сумма успешно проставлена на сайте СП', SUCCESS_NOTIFY);
              }
              return print $result;
            }
          }
          if ($isNotify) {
            $notify->sendNotify('Не удалось проставить сумму на сайте СП', ERROR_NOTIFY);
          }
          break;
        // Добавить корректировку
        case 'correction_add' :
          if (isset($_REQUEST['lot']) and isset($_REQUEST['view']) and isset($_REQUEST['correction_comment']) and isset($_REQUEST['correction_sum'])) {
            $arg = $_REQUEST['view'];
            $lot = $_REQUEST['lot'];
            $comment = $_REQUEST['correction_comment'];
            $sum = $_REQUEST['correction_sum'];
            $result = $command->correctionAdd($lot, $comment, $sum, $arg);
            return print $result;
          }
          break;
        // Удалить корректировку
        case 'correction_del' :
          if (isset($_REQUEST['lot']) and isset($_REQUEST['view']) and isset($_REQUEST['correction'])) {
            $arg = $_REQUEST['view'];
            $lot = $_REQUEST['lot'];
            $correction = $_REQUEST['correction'];
            $result = $command->correctionDel($lot, $correction, $arg);
            return print $result;
          }
          break;
        // Удалить проставленный платёж
        case 'pay_del' :
          if (isset($_REQUEST['lot']) and isset($_REQUEST['view']) and isset($_REQUEST['pay'])) {
            $arg = $_REQUEST['view'];
            $lot = $_REQUEST['lot'];
            $pay = $_REQUEST['pay'];
            $result = $command->payDel($lot, $pay, $arg);
            return print $result;
          }
          break;
        // Удалить потерянный проставленный платёж
        case 'lost_pay_del' : // todo нет обработки 500 ошибки для JS
          if (isset($_REQUEST['lot']) and isset($_REQUEST['pay'])) {
            $lot = $_REQUEST['lot'];
            $pay = $_REQUEST['pay'];
            $result = $command->lostPayDel($lot, $pay);
            return print $result;
          }
          break;
        // Проставить платёж при помощи найденной вручную СМС
        case 'search_filling' :
          $notify = new Notify();
          if (isset($_REQUEST['lot']) and isset($_REQUEST['pay']) and isset($_REQUEST['sms'])) {
            $lot = $_REQUEST['lot'];
            $pay = $_REQUEST['pay'];
            $sms = $_REQUEST['sms'];
            $result = $command->searchFilling($lot, $pay, $sms);
            if ($result !== 'false') {
              // Уведомление о проставлении оплаты
              $notify->sendNotify('Оплата успешно проставлена', SUCCESS_NOTIFY);
              return print $result;
            }
          }
          $notify->sendNotify('Не удалось проставить оплату', ERROR_NOTIFY);
          break;
      }
    }
    return print 'false';
  }
}