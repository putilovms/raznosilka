<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\YandexKassa.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Payment_YandexKassa Реализует протокол для работы с Яндекс.Кассой
 */
class PaymentSystem_YandexKassa extends PaymentSystem {

  /**
   * Настройки
   */

  /**
   * Тестовый режим
   */
  const TEST_MODE = 0;
  /**
   * Идентификатор магазина
   */
  const SHOP_ID = 115982;
  /**
   * Номер витрины
   */
  const SCID = 45935;
  /**
   * Пароль магазина
   */
  const SHOP_PASSWORD = 'urQnx3KcZ4TPe0n8VkkO';
  /**
   * ID сисетмы оплаты в сервисе
   */
  const PAYMENT_SYSTEM_ID = 3;

  /**
   * Получить данные для вывода платёжной формы
   * @return array Массив с информацией для вывода платёжной формы, формата:
   *  ['action_url'] - адрес обработчика запросов
   *  ['shop_id'] - Идентификатор магазина
   *  ['scid'] - Номер витрины
   *  ['uid']
   * @throws Exception
   */
  function getPaymentFormInfo () {
    $result = array();
    $result['action_url'] = 'https://money.yandex.ru/eshop.xml';
    // Если тестовый режим
    if (self::TEST_MODE) {
      $result['action_url'] = 'https://demomoney.yandex.ru/eshop.xml';
    }
    $uid = $this->user->getUserId();
    $result['uid'] = $uid;
    $result['shop_id'] = self::SHOP_ID;
    $result['scid'] = self::SCID;
    $result['sum'] = MONTH_COST;
    $result['email'] = $this->user->getUserEmail();
    return $result;
  }

  /**
   * Получить путь к шаблону с формой для платежа
   * @return string Путь к шаблону с формой для платежа
   */
  function getPathToForm () {
    return $this->tplPath . '/yandex_kassa_form.tpl.php';
  }

  /**
   * Сохранить данные о платеже
   * @param $request array Данные запроса для сохранения
   * @return int|false ID данных о платеже
   */
  function savePayingData ($request) {
    $data = array();
    $data[INVOICE_ID] = $request['invoiceId'];
    $data[CUSTOMER_NUMBER] = $request['customerNumber'];
    $data[SHOP_ARTICLE_ID] = isset($request['shopArticleId']) ? $request['shopArticleId'] : 0;
    $data[ORDER_SUM_AMOUNT] = $request['orderSumAmount'];
    $data[SHOP_SUM_AMOUNT] = isset($request['shopSumAmount']) ? $request['shopSumAmount'] : 0;
    $data[PAYMENT_TYPE] = isset($request['paymentType']) ? $request['paymentType'] : '';
    $data[PAYMENT_DATETIME] = isset($request['paymentDatetime']) ? strftime('%Y-%m-%d %H:%M:%S', strtotime($request['paymentDatetime'])) : null;
    $data[ORDER_CREATED_DATETIME] = isset($request['orderCreatedDatetime']) ? strftime('%Y-%m-%d %H:%M:%S', strtotime($request['orderCreatedDatetime'])) : null;
    $result = $this->db->addPayingDataYandexKassa($data);
    if ($result) {
      $result = $data[INVOICE_ID];
    }
    return $result;
  }

  /**
   * Обработать запрос от Яндекс.Кассы
   * @param $action string "checkOrder" или "paymentAviso"
   * @param $request array Данные запроса
   * @param bool $test Если запрос в режиме тестирования
   */
  function processRequest ($action, $request, $test = false) {
    // Логирование
    $this->log->paymentLog($action);
    if ($test) {
      $this->log->paymentLog("Уведомление получено на URL для тестирования");
    }
    if (self::TEST_MODE) {
      $this->log->paymentLog("Платёжная система находится в режиме тестирования");
    }
    $this->log->paymentLog("Запрос: " . Kit::arrayToString_r($request)); // http_build_query($request)
    // Проверка на целостность данных запроса
    $this->checkRequest($action, $request);
    // Проверка режима работы платёжной системы
    if (!$test) {
      $this->checkModePaymentSystem($action, $request);
    }
    // Проверка режима работы сайта
    $this->checkMode($action, $request);
    // Проверка MD5-хэша параметров платежной формы
    $this->checkMD5($action, $request);
    switch ($action) {
      case 'checkOrder':
        $this->checkOrder($request);
        break;
      case 'paymentAviso':
        $this->paymentAviso($request, $test);
        break;
    }
  }

  /**
   * Обработка запроса checkOrder от платёжной системы
   * @param $request array Данные запроса
   */
  function checkOrder ($request) {
    $action = 'checkOrder';
    // Проверяем сумму платежа
    if ($request['orderSumAmount'] == MONTH_COST) {
      $response = $this->buildResponse($action, $request['invoiceId'], 0);
      $this->log->paymentLog("Сервис готов принять перевод");
    } else {
      $response = $this->buildResponse($action, $request['invoiceId'], 100, "Ошибка: Указана некорректная сумма");
      $this->log->paymentLog("Ошибка: Указана некорректная сумма");
    }
    $this->sendResponse($response);
  }

  /**
   * Обработка запроса paymentAviso от платёжной системы
   * @param $request array Данные запроса
   * @param bool $test Если запрос в режиме тестирования
   */
  function paymentAviso ($request, $test = false) { // todo нет проверки на сумму или сверкой с запросом checkOrder
    $action = 'paymentAviso';
    // Добавление заказа
    if ($test) {
      $result = true;
    } else {
      $result = false;
      $paymentId = $this->savePayingData($request);
      if ($paymentId !== false) {
        $userId = $request['customerNumber'];
        $day = 30;
        $result = $this->createOrder($userId, $paymentId, $day);
        if ($result !== false) {
          // Лог действий пользователя
          $user = $this->db->getUserById($request['customerNumber']);
          $this->log->actionLog($user, 'Куплена услуга');
        }
      }
    }
    // Проверка добавления заказа
    if ($result !== false) {
      // Отсылаем ответ об успехе
      $response = $this->buildResponse($action, $request['invoiceId'], 0);
      $this->log->paymentLog("Платёж успешно обработан");
      $this->sendResponse($response);
    } else {
      // Отсылаем ответ об ошибке
      $response = $this->buildResponse($action, $request['invoiceId'], 200, "Ошибка: Не удалось предоставить услугу пользователю");
      $this->log->paymentLog("Ошибка: Не удалось предоставить услугу пользователю");
      $this->sendResponse($response);
    }
  }

  /**
   * Отсылка ответа Яндекс.Деньгам
   * @param $responseBody string Тело ответа
   */
  private function sendResponse ($responseBody) {
    $this->log->paymentLog("Ответ: " . $responseBody);
    header("HTTP/1.0 200");
    header("Content-Type: application/xml");
    print $responseBody;
    exit;
  }

  /**
   * Создать XML ответ
   * @param  string $functionName "checkOrder" или "paymentAviso"
   * @param  string $invoiceId Уникальный номер транзакции в Яндекс.Деньгах
   * @param  string $result_code Код результата обработки запроса
   *  0 - Успешно
   *  1 - Ошибка авторизации
   *  100 - Отказ в приеме перевода
   *  200 - Ошибка разбора запроса
   * @param  string $message Сообщение об ошибке, по умолчанию null
   * @return string Подготовленный ответ в формате XML
   */
  private function buildResponse ($functionName, $invoiceId, $result_code, $message = null) {
    try {
      $performedDatetime = $this->formatDate();
      $response = array();
      $response[] = '<?xml version="1.0" encoding="UTF-8"?>';
      $response[] = '<' . $functionName . 'Response ';
      $response[] = 'performedDatetime="' . $performedDatetime . '" ';
      $response[] = 'code="' . $result_code . '" ';
      $response[] = (is_null($message)) ? "" : 'message="' . $message . '" ';
      $response[] = 'invoiceId="' . $invoiceId . '" ';
      $response[] = 'shopId="' . self::SHOP_ID . '" ';
      $response[] = '/>';
      $response = implode("", $response);
      return $response;
    } catch (\Exception $e) {
      $this->log->paymentLog($e);
    }
    return null;
  }

  /**
   * Получить текущую дату в формате Яндекс.Денег
   * @return string Строка с датой формата: 2011-05-04T20:38:00.000+04:00
   */
  public function formatDate () {
    $date = new DateTime();
    $performedDatetime = $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
    return $performedDatetime;
  }

  /**
   * Проверка MD5-хэша параметров платежной формы
   * @link https://tech.yandex.ru/money/doc/payment-solution/payment-notifications/payment-notifications-http-docpage/ Документация по составлению MD5-хеша
   * @param $request array Данные запроса
   * @param $action string "checkOrder" или "paymentAviso"
   * @return bool Результат проверки
   */
  function checkMD5 ($action, $request) {
    // Стоимость заказа в валюте, определенной параметром запроса orderSumCurrencyPaycash
    $orderSumAmount = $request['orderSumAmount'];
    // Код валюты для суммы заказа
    $orderSumCurrencyPaycash = $request['orderSumCurrencyPaycash'];
    // Код процессингового центра в Яндекс.Деньгах для суммы заказа
    $orderSumBankPaycash = $request['orderSumBankPaycash'];
    // Уникальный номер транзакции в Яндекс.Деньгах
    $invoiceId = $request['invoiceId'];
    // Идентификатор плательщика на стороне магазина. Присылается в платежной форме
    $customerNumber = $request['customerNumber'];
    // Получение MD5-хэша
    $str = "{$action};{$orderSumAmount};{$orderSumCurrencyPaycash};{$orderSumBankPaycash};" . self::SHOP_ID . ";{$invoiceId};{$customerNumber};" . self::SHOP_PASSWORD;
    $MD5 = strtoupper(md5($str));
    $receivedMD5 = strtoupper($request['md5']);
    // Если хэши не совпали
    if ($MD5 != $receivedMD5) {
      $response = $this->buildResponse($action, $invoiceId, 1);
      $this->log->paymentLog("Ошибка: Ожидаемый MD5-хэш: " . $MD5 . ", не совпал с полученным MD5-хэшем: " . $receivedMD5);
      $this->sendResponse($response);
    }
  }

  /**
   * Проверить налицие необходимых данных в запросе
   * @param $action string "checkOrder" или "paymentAviso"
   * @param $request array Данные запроса
   */
  function checkRequest ($action, $request) {
    $result = true;
    if (!isset($request['action'])) {
      $result = false;
    }
    if (!isset($request['orderSumAmount'])) {
      $result = false;
    }
    if (!isset($request['orderSumCurrencyPaycash'])) {
      $result = false;
    }
    if (!isset($request['orderSumBankPaycash'])) {
      $result = false;
    }
    if (!isset($request['shopId'])) {
      $result = false;
    }
    if (!isset($request['invoiceId'])) {
      $result = false;
    }
    if (!isset($request['customerNumber'])) {
      $result = false;
    }
    if (!isset($request['md5'])) {
      $result = false;
    }
    // Если необходимые данные отсутствуют
    if (!$result) {
      $invoiceId = isset($request['invoiceId']) ? $request['invoiceId'] : '';
      $response = $this->buildResponse($action, $invoiceId, 200, "Ошибка: Данные запроса повреждены");
      $this->log->paymentLog("Ошибка: Данные запроса повреждены");
      $this->sendResponse($response);
    }
  }

  /**
   * Проверка режима работы сайта
   * @param $action string "checkOrder" или "paymentAviso"
   * @param $request array Данные запроса
   */
  function checkMode ($action, $request) {
    // Проверка режима работы сайта
    $mode = Registry_Request::instance()->get('mode');
    // Если сайт на обслуживании, то отсылаем ответ с ошибкой
    if ($mode == 'service') {
      $response = $this->buildResponse($action, $request['invoiceId'], 200, "Ошибка: Сайт находится на обслуживании");
      $this->log->paymentLog("Ошибка: Сайт находится на обслуживании");
      $this->sendResponse($response);
    }
  }

  /**
   * Проверка режима работы платёжной сисетмы
   * @param $action string "checkOrder" или "paymentAviso"
   * @param $request array Данные запроса
   */
  function checkModePaymentSystem ($action, $request) {
    // Если платёжная сисетма находится в режиме тестирования, то отсылаем ответ с ошибкой
    if (self::TEST_MODE) {
      $response = $this->buildResponse($action, $request['invoiceId'], 200, "Ошибка: Платёжная сисетма находится в режиме тестирования");
      $this->log->paymentLog("Ошибка: Платёжная сисетма находится в режиме тестирования");
      $this->sendResponse($response);
    }
  }

  /**
   * Добавить пользователю заказ на услугу
   * @param $userId int ID пользователя
   * @param $paymentId int ID данных о платеже
   * @param $day int Количество дней оказания услуги
   * @return false|int ID добавленного заказа на услугу
   */
  function createOrder ($userId, $paymentId, $day) {
    $orderUser = new OrderUser($userId);
    $result = $orderUser->addOrder($day, 'YandexKassa', $paymentId, ORDER_YANDEX_KASSA);
    // Отправить письмо пользователю
    if ($result !== false) {
      $mail = new Mail();
      // Подготовить данные
      $user = $this->db->getUserById($userId);
      $data['day'] = $day;
      $data['date_done'] = strftime('%H:%M %d.%m.%Y', $orderUser->getDateDone());
      $mail->sendUserNotifyPaymentMail($user, $data);
    }

    return $result;
  }

  /**
   * Получить данные о платеже
   * @param $paymentId int ID платежа
   * @return array Данные о платеже
   */
  function getPaymentInfo ($paymentId) {
    $result = array();
    $payment = $this->db->getPaymentByIdYandexKassa($paymentId);
    if ($payment !== false) {
      $payment[PAYMENT_DATETIME] = (!is_null($payment[PAYMENT_DATETIME])) ? strftime('%H:%M %d.%m.%Y', strtotime($payment[PAYMENT_DATETIME])) : '—';
      $payment[ORDER_CREATED_DATETIME] = (!is_null($payment[ORDER_CREATED_DATETIME])) ? strftime('%H:%M %d.%m.%Y', strtotime($payment[ORDER_CREATED_DATETIME])) : '—';
      $payment[ORDER_SUM_AMOUNT] = number_format($payment[ORDER_SUM_AMOUNT], 2, ',', '');
      $payment[SHOP_SUM_AMOUNT] = number_format($payment[SHOP_SUM_AMOUNT], 2, ',', '');
      $payment[PAYMENT_TYPE] = $this->getPaymentMethods($payment[PAYMENT_TYPE]);
      $result['payment'] = $payment;
    }
    return $result;
  }

  /**
   * Получить путь к шаблону с таблицей данных о платеже
   * @return string Путь к шаблону с таблицей данных о платеже
   */
  function getPathToInfo () {
    return $this->tplPath . '/yandex_kassa_info.tpl.php';
  }

  /**
   * Получить метод оплаты по её типу
   * @param $type string Тип оплаты
   * @return string Метод оплаты
   */
  function getPaymentMethods ($type) {
    $result = $type;
    $methods = array(
      'PC' => 'Оплата из кошелька в Яндекс.Деньгах',
      'AC' => 'Оплата с произвольной банковской карты',
      'MC' => 'Оплата со счета мобильного телефона',
      'GP' => 'Оплата наличными через кассы и терминалы',
      'WM' => 'Оплата из кошелька в системе WebMoney',
      'SB' => 'Оплата через Сбербанк: по смс или Сбербанк Онлайн',
      'MP' => 'Оплата через мобильный терминал (mPOS)',
      'AB' => 'Оплата через Альфа-Клик',
      'MA' => 'Оплата через MasterPass',
      'PB' => 'Оплата через интернет-банк Промсвязьбанка',
      'QW' => 'Оплата через QIWI Wallet',
      'KV' => 'Оплата через КупиВкредит (Тинькофф Банк)',
      'QP' => 'Оплата через Доверительный платеж на Куппи.ру'
    );
    if (isset($methods[$type])) {
      $result = $methods[$type];
    }
    return $result;
  }

  /**
   * Получить сумму полученную сервисом от платёжной системы
   * @return float Сумма полученная сервисом от платёжной системы
   */
  function getSumReceived () { // todo учитывает возвращённые платежи
    $result = 0.00;
    $sum = $this->db->getSumReceivedYandexKassa();
    if ($sum !== false) {
      $result = $sum;
    }
    return $result;
  }

  /**
   * Получить сумму заплаченную пользователями платёжной системе
   * @return float Сумма заплаченная пользователями платёжной системе
   */
  function getSumPaid () { // todo учитывает возвращённые платежи
    $result = 0.00;
    $sum = $this->db->getSumPaidYandexKassa();
    if ($sum !== false) {
      $result = $sum;
    }
    return $result;
  }

  /**
   * Получить сумму полученную сервисом от платёжной системы от определённого пользователя
   * @param $uid int ID пользователя
   * @return float Сумма полученная сервисом от платёжной системы от определённого пользователя
   */
  function getUserSumReceived ($uid) { // todo учитывает возвращённые платежи
    $result = 0.00;
    $sum = $this->db->getUserSumReceivedYandexKassa($uid);
    if ($sum !== false) {
      $result = $sum;
    }
    return $result;
  }

  /**
   * Получить сумму заплаченную определённым пользователем платёжной системе
   * @param $uid int ID пользователя
   * @return float Сумма заплаченная определённым пользователем платёжной системе
   */
  function getUserSumPaid ($uid) { // todo учитывает возвращённые платежи
    $result = 0.00;
    $sum = $this->db->getUserSumPaidYandexKassa($uid);
    if ($sum !== false) {
      $result = $sum;
    }
    return $result;
  }

  /**
   * Получить количество записей в таблице для текущей  платёжной системы
   * @return array Массив с именем таблице и количеством записей, формата:
   *  ['name'] - имя таблицы
   *  ['count'] - число записей
   */
  function getCountRecordsPaymentSystem () {
    $result['name'] = 'yandex_kassa';
    $result['count'] = $this->db->getCountRecordsYandexKassa();;
    return $result;
  }

  /**
   * Получить информацию о платёжной системе
   * @return array массив с инфомрацией о платёжной системе, формата:
   *  [x] - Параметр
   *    ['name'] - имя параметра для вывода
   *    ['value'] - значение параметра для вывода
   */
  function getAdminInfoForView () {
    // Название платёжной системы
    $info['name'] = 'Платёжная система';
    $info['value'] = 'Яндекс.Касса';
    $result['payment_system'] = $info;
    // Название платёжной системы
    $info['name'] = 'ID магазина';
    $info['value'] = self::SHOP_ID;
    $result['shop_id'] = $info;
    // Номер витрины
    $info['name'] = 'Номер витрины';
    $info['value'] = self::SCID;
    $result['scid'] = $info;
    // Состояние платёжной системы
    $info['name'] = 'Режим работы';
    $info['value'] = self::TEST_MODE ? 'В режиме тестирования' : 'В нормальном режиме';
    $result['mode'] = $info;
    return $result;
  }

}