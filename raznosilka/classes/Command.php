<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Command.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Command Обработка команд от Ajax запросов
 */
class Command {
  /**
   * Команда для получения списка закупок из организаторской при помощи расширения
   */
  const CMD_LIST_PURCHASE_ORG = 'cmd=list_org&body=';
  /**
   * Команда для запуска автоматического поиска оплат для закупки
   */
  const CMD_AUTO_ANALYSIS_ORG = 'cmd=auto_analysis&body=';
  /**
   * Команда ручного проставления оплаты
   */
  const CMD_MANUAL_FILLING = 'cmd=manual_filling&lot=';
  /**
   * Команда для автоматического проставления платы
   */
  const CMD_AUTO_FILLING = 'cmd=auto_filling&lot=';
  /**
   * Команда для проверки общей суммы
   */
  const CMD_CHECK_TOTAL = 'cmd=check_total&sum=';
  /**
   * Команда для вывода редактора закупок
   */
  const CMD_EDITOR_PURCHASE = 'cmd=editor_purchase&body=';
  /**
   * Команда для отметки платежа как ошибочного
   */
  const CMD_ERROR_SET = 'cmd=error_set&lot=%s&pay=%s&view=';
  /**
   * Команда для удаления отметки ошибочности платежа
   */
  const CMD_ERROR_DEL = 'cmd=error_del&lot=%s&pay=%s&view=';
  /**
   * Команда для обновления внесённой суммы заказа на сайте СП
   */
  const CMD_UPDATE_SUM = 'cmd=update_sum&lot=%s&view=';
  /**
   * Команда для удаления корректировки
   */
  const CMD_CORRECTION_DEL = 'cmd=correction_del&lot=%s&correction=%s&view=';
  /**
   * Команда для удаления проставленной оплаты
   */
  const CMD_PAY_DEL = 'cmd=pay_del&lot=%s&pay=%s&view=';
  /**
   * Команда для удаления проставленной оплаты
   */
  const CMD_LOST_PAY_DEL = 'cmd=lost_pay_del&lot=%s&pay=%s';
  /**
   * Команда для проставления оплиты при помощи найденной СМС
   */
  const CMD_SEARCH_FILLING = 'cmd=search_filling&lot=%s&pay=%s&sms=';

  /**
   * @var false|Purchase Объект с закупкой
   */
  private $purchase;
  /**
   * @var Site Содержит объект Site для доступа к данным сайта СП
   */
  private $site;
  /**
   * @var int Тип запроса к сайту СП, выбранный пользователем
   */
  private $userRequest;
  /**
   * @var User Текущий пользователь
   */
  private $user;
  /**
   * @var Logs Журналирование событий
   */
  private $logs;

  /**
   * Конструктор класса
   */
  function __construct () {
    $this->purchase = Cache::getPurchaseFromCache();
    $this->site = Site::getSite();
    $this->user = Registry_Request::instance()->get('user');
    $this->userRequest = $this->user->getUserRequest();
    $this->logs = new Logs();
  }

  /**
   * Проставление платежа для выбранной закупки
   * @param $type string Текстовый параметр:
   * - 'manual' - ручное проставление платежа
   * - 'auto' - автоматическое проставление платежа
   * @param $lot int Номер заказа, для которого будет проставлена сумма
   * @param string $body Тело ответа сайта СП
   * @param $request string json массив с данными запроса от сервиса
   * @return false|float Общая сумма найденная Разносилкой для всей закупки
   */
  public function filling ($type, $lot, $body = '', $request = '') {
    // Если закупка сохранена в реестре сессий
    if ($this->purchase instanceof Purchase) {
      if (is_int($lot)) {
        // Проверка OrgID
        if ($this->checkOrgID($request)) {
          // Сохранение кэша тела страницы
          $this->saveBodyCache($body, $request);
          /** @var Lot[] $lots */
          $lots = $this->purchase->getLots();
          if (isset($lots[$lot])) {
            if ($lots[$lot]->isForFilling()) {
              switch ($type) {
                case 'auto' :
                  // Автоматическое проставление оплаты
                  $result = $this->autoFilling($lots[$lot], $body);
                  return $result;
                  break;
                case 'manual' :
                  // Ручное проставление оплаты
                  return $this->manualFilling($lots[$lot]);
                  break;
              }
            }
          }
        }
      }
    }
    $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проставления плтёжа. Тип проставления - {$type}.");
    return 'false';
  }

  /**
   * Проверка ID организатора, добавляет в БД в случае его отсутствия
   * @param $request string json массив с данными запроса от сервиса
   * @return bool Результат проверки ID организатора (true если ID совпадают)
   * @throws Exception
   */
  private function checkOrgID ($request) {
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        // Получить данные запроса
        $info = json_decode($request, true);
        // Получить OrgId из запроса
        $orgId = (isset($info['orgId'])) ? (int)$info['orgId'] : 0;
        // Если OrgId в запросе не задан
        if ($orgId <= 1) { // чтобы не сохранить ID Гостя
          $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проверки ID организатора. ID организатора не задан.");
          return false;
        }
        // Если ID организатора ещё не сохранён
        if (!$this->user->isBinding()) {
          // todo нет проверки на то что, пользователь залогинен как организатор (возможно сохранение id обычного пользователя)
          // Проверить есть ли такой ID в базе данных
          $db = new DataBase(Registry_Request::instance()->get('db'));
          $spId = $this->user->getSpId();
          $orgUser = $db->getUserFromSpAndOrgId($spId, $orgId);
          if ($orgUser !== false) {
            $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проверки ID организатора. Не удалось сохранить ID организатора (#{$orgId}), так как в сервисе уже сохранён организатор с таким ID.");
            return false;
          }
          // Сохраняем ID организатора в БД
          $setting = new SettingsUser($this->user->getUserId());
          $setting->setSetting(USER_ORG_ID, $orgId);
          $result = $setting->setSettings();
          if (!$result) {
            $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проверки ID организатора. Не удалось сохранить ID организатора (#{$orgId}), неизвестная ошибка.");
          }
          return $result;
        }
        // Если ID организатора не совпадает с ID сохранённом в сервисе
        $orgIdService = $this->user->getOrgId();
        if ($orgIdService !== $orgId) {
          $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проверки ID организатора. Указанный ID организатора (#{$orgId}) не совпадает с сохранённом в сервисе (#{$orgIdService}).");
          return false;
        }
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        // Если прямой запрос к сайту СП

        break;
      }
    }
    return true;
  }

  /**
   * Ручное проставление оплаты
   * @param Lot $lot Объект лота в котором будет проставлена оплата
   * @return float Общая сумма найденная Разносилкой для всей закупки
   */
  private function manualFilling (Lot $lot) {
    $purchaseId = $this->purchase->getPurchaseId();
    // Проставить оплату в Разносилке
    $lot->fillingAllPay($purchaseId);
    // Обновить проставленную сумму в объекте
    $sum = $lot->getTotalForFilling();
    $lot->setTotalPut($sum);
    // Вернуть итоговую сумму после проставления оплаты
    $totalFound = number_format($this->purchase->getCountTotalFoundMoney(), 2, '.', '');
    return $totalFound;
  }

  /**
   * Автоматическое проставление оплаты
   * @param Lot $lot Объект лота в котором будет проставлена оплата
   * @param string $body Тело ответа сайта СП
   * @return false|float Общая сумма найденная Разносилкой для всей закупки
   */
  private function autoFilling (Lot $lot, $body) {
    $purchaseId = $this->purchase->getPurchaseId();
    $sum = $lot->getTotalForFilling();
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {

        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        // Если прямой запрос к сайту СП
        if (empty($body)) {
          // Получение команды
          $userPurchaseId = $lot->getUserPurchase()->getUserPurchaseId();
          $cmd = $this->site->getCommandAddPay($purchaseId, $userPurchaseId, $sum);
          // Отправление команды на сайт СП
          $url = $this->site->getCommandUrl();
          $body = $this->site->getPage($url, $cmd);
          $body = $body['body'];
        }
        break;
      }
    }
    // Если ответ получен
    if ($this->site->checkResponse($body)) {
      // Проставить оплату в Разносилке
      $lot->fillingAllPay($purchaseId);
      // Обновить проставленную сумму в объекте
      $lot->setTotalPut($sum);
      // Вернуть итоговую сумму после проставления оплаты
      $totalFound = number_format($this->purchase->getCountTotalFoundMoney(), 2, '.', '');
      return $totalFound;
    }
    $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка автоматического проставления оплаты. Сайт СП ответил ошибкой.");
    return 'false';
  }

  /**
   * Проверка общей суммы внесённой на сайт СП и той что найдена в БД Разносилки
   * для выбранной закупки.
   * @param $sum float Общая сумма которая проставлена скриптом (БД+проставлено)
   * @param string $body Тело ответа сайта СП
   * @param $request string json массив с данными запроса от сервиса
   * @return true|false Результат проверки
   */
  public function checkTotal ($sum, $body = '', $request = '') {
    $result = 'false';
    if ($this->purchase instanceof Purchase) {
      if (is_float($sum)) {
        // Проверка OrgID
        if (!$this->checkOrgID($request)) {
          return $result;
        }
        // Сохранение кэша тела страницы
        $this->saveBodyCache($body, $request);
        $purchaseData = '';
        switch ($this->userRequest) {
          // Запросы к сайту СП при помощи расширения браузера
          case REQUEST_EXTENSIONS: {
            if (!empty($body)) {
              $info = json_decode($request, true);
              $purchaseData = $this->site->getPurchaseArr($body, $info['urlRequest']);
              $purchaseData['url'] = $this->site->getPurchaseURL($purchaseData[PURCHASE_ID]);
            }
            break;
          }
          // Запросы к сайту СП при помощи curl по умолчанию
          default : {
            $purchaseId = $this->purchase->getPurchaseId();
            $info = $this->site->getRequestInfoPurchase(null, null, $purchaseId);
            $purchaseData = $this->site->getPurchaseFromSite($info);
            break;
          }
        }
        // Если данные получены
        if (is_array($purchaseData)) {
          $purchase = new Purchase($purchaseData);
          $sumFromSite = $purchase->getCountTotalPutMoney();
          if ($sum == $sumFromSite) {
            $result = 'true';
          }
        }
      }
    }
    return $result;
  }

  /**
   * Обновить проставленную сумму у заданного заказа
   * @param $lotNumber int Номер заказа, для которого будет проставлена сумма
   * @param $arg string Параметр фильтра
   * @param $body string Тело ответа сайта СП
   * @param $request string json массив с данными запроса от сервиса
   * @return false|string Данные для обновления лота, для которого обновляется сумма
   */
  public function updateSum ($lotNumber, $arg, $body = '', $request = '') {
    // Если закупка сохранена в реестре сессий
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        if (isset($lots[$lotNumber])) {
          $sum = $lots[$lotNumber]->getTotalFound();
          // Проверка OrgID
          if ($this->checkOrgID($request)) {
            // Сохранение кэша тела страницы
            $this->saveBodyCache($body, $request);
            switch ($this->userRequest) {
              // Запросы к сайту СП при помощи расширения браузера
              case REQUEST_EXTENSIONS: {

                break;
              }
              // Запросы к сайту СП при помощи curl по умолчанию
              default : {
                $purchaseId = $this->purchase->getPurchaseId();
                $userPurchaseId = $lots[$lotNumber]->getUserPurchase()->getUserPurchaseId();
                // Получение команды
                $cmd = $this->site->getCommandAddPay($purchaseId, $userPurchaseId, $sum);
                // Отправление команды на сайт СП
                $url = $this->site->getCommandUrl();
                $body = $this->site->getPage($url, $cmd);
                $body = $body['body'];
                break;
              }
            }
            // Если ответ получен
            if ($this->site->checkResponse($body)) {
              // Обновить проставленную сумму в объекте
              $lots[$lotNumber]->setTotalPut($sum); // todo Для больше точности можно заменить на запрос к сайту СП и полного обновления объекта
              // Обновить данные об объекте
              return $this->updateLotData($lotNumber, $arg);
            }
          }
        }
      }
    }
    $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка обновления суммы на сайте СП.");
    return 'false';
  }

  /**
   * Получить данные для расшифровки cookie для выбранного пользователем сайта СП
   */
  public function getCookieInfo () {
    $result = ''; // Фикс для админа, если не выбран сайт СП
    if ($this->site instanceof Site) {
      $domain = $this->site->getUrlSite();
      $domain = parse_url($domain);
      // убрать www из адреса, так как в куках он не учитывается
      $domain = preg_replace('#^www\.#', '', $domain['host'], 1);
      $result['domain'] = $domain;
      $result['name'] = $this->site->getNameCookieUser();
    }
    return json_encode($result);
  }

  /**
   * Проверка входящих данных
   * @param $result array Массив с данными для ответа на запрос. В случае если проверяемые данные не прошли проверку,
   *  то в данный массив добаляется код ошибки.
   * @param $body string Тело старницы полученной с сайта СП
   * @param $request string json массив с данными запроса от сервиса
   * @return bool Результат проверки
   */
  private function checkData (array &$result, $body, $request) {
    // Проверка ID организатора
    if ((!$this->checkOrgID($request))) {
      $result['info']['error'] = ERROR_ORG_ID;
      return false;
    }
    // Если не удалось получить страницу
    if (empty($body)) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проверки входящих данных. Тело страницы пустое.");
      $result['info']['error'] = ERROR_PAGE;
      return false;
    }
    // Если доступа к странице нет
    if (!$this->site->checkAccessPermission($body, $request)) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка проверки входящих данных. Нет доступа к сайту СП.");
      $result['info']['error'] = ERROR_ACCESS;
      return false;
    }
    // Сохранение кэша тела страницы
    $this->saveBodyCache($body, $request);
    return true;
  }

  /**
   * Получить список закупок из тела сайта, через API
   * @param $request string json массив с данными запроса от сервиса
   * @param $body string Тело старницы полученной с сайта СП
   * @return string JSON объект из массива формата:
   *  ['info'] - информация о запросе @see Site::getRequestInfoListPurchase()
   *  ['list'] - список закупок @see ListPurchase::prepareListPurchaseOrgArray()
   */
  public function getListPurchase ($request, $body) {
    // Инициализация
    $result = array();
    $result['list'] = array();
    // Получить данные запроса
    $info = json_decode($request, true);
    if (!isset($info['url']) or !isset($info['filter']) or !isset($info['page'])) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения списка закупок. Запрос содержит не полные данные.");
      return 'false';
    }
    $result['info'] = $info;
    // Проверка входящих данных
    if (!$this->checkData($result, $body, $request)) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения списка закупок.");
      return json_encode($result);
    }
    // Получить массивы с данными о закупках
    $list = $this->site->getListPurchaseArr($body);
    // Не удалось получить данные о списке закупок
    if ($list === false) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения списка закупок. Не удалось получить данные о списке закупок из тела страницы.");
      $result['info']['error'] = ERROR_DATA;
      return json_encode($result);
    }
    $listPurchase = new ListPurchase();
    // Массив с опциями для пейджера
    $pagerOpt = array();
    $pagerOpt[PAGER_PAGE] = (int)$info['page'];
    $pagerOpt[PAGER_URL] = $info['url'];
    $result['list'] = $listPurchase->prepareListPurchaseOrgArray($list, $info['filter'], $pagerOpt);
    return json_encode($result);
  }

  /**
   * Получить данные для вывода страницы для автоматического поиска СМС, через API
   * @param $request string json массив с данными запроса от сервиса
   * @param $body string Тело старницы полученной с сайта СП
   * @return string JSON объект из массива формата:
   *  ['info'] - информация о запросе @see Site::getRequestInfoListPurchase()
   *  ['analysis'] - данные для вывода анализатора @see Analysis::getRequestInfoAnalysis()
   *  ['sum'] - Массив содержащие все суммы необхоимые для корректной работы скрипта
   */
  public function getPageAnalysis ($request, $body) {
    // Инициализация
    $result = array();
    // Получить данные запроса
    $info = json_decode($request, true);
    $result['info'] = $info;
    $result['analysis'] = array();
    // Проверка входящих данных
    if (!$this->checkData($result, $body, $request)) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Автопоиска СМС'.");
      return json_encode($result);
    }
    // Получить массивы с данными о закупке
    $purchaseData = $this->site->getPurchaseArr($body, $info['urlRequest']);
    // Не удалось получить данные о закупке
    if ($purchaseData === false) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Автопоиска СМС'. Не удалось получить данные о закупке из тела страницы.");
      $result['info']['error'] = ERROR_DATA;
      return json_encode($result);
    }
    $purchaseData['url'] = $this->site->getPurchaseURL($purchaseData[PURCHASE_ID]);
    $purchase = new Purchase($purchaseData);
    if ($purchase instanceof Purchase) {
      $analysis = new Analysis();
      $analysis->setPurchase($purchase);
      $result['analysis'] = $analysis->preparePageAnalyzer();
      $result['sum'] = $analysis->getSum();
    } else {
      // Если не удалось получить объект
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Автопоиска СМС'. Не удалось создать объект Purchase из полученных данных о закупке из тела страницы.");
      $result['info']['error'] = ERROR_OTHER;
    }
    return json_encode($result);
  }

  /**
   * Получить страницу с "Редактором закупок", через API
   * @param $request string json массив с данными запроса от сервиса
   * @param $body string Тело старницы полученной с сайта СП
   * @return string JSON объект из массива формата:
   *  - ['info'] - данные для запроса @see PurchaseHelper::getRequestInfoEditorPurchase()
   *  - ['editor'] - данные для вывода "Редактора закупок" @see EditorPurchase::prepareEditorPurchase()
   */
  public function getEditorPurchase ($request, $body) {
    // Инициализация
    $result = array();
    // Получить данные запроса
    $info = json_decode($request, true);
    if (!isset($info['view'])) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Редактора закупок'. Запрос содержит не полные данные.");
      return 'false';
    }
    $result['info'] = $info;
    $result['editor'] = array();
    // Проверка входящих данных
    if (!$this->checkData($result, $body, $request)) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Редактора закупок'.");
      return json_encode($result);
    }
    // Получить массивы с данными о закупке
    $purchaseData = $this->site->getPurchaseArr($body, $info['urlRequest']);
    // Не удалось получить данные о закупке
    if ($purchaseData === false) {
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Редактора закупок'. Не удалось получить данные о закупке из тела страницы.");
      $result['info']['error'] = ERROR_DATA;
      return json_encode($result);
    }
    $purchaseData['url'] = $this->site->getPurchaseURL($purchaseData[PURCHASE_ID]);
    $purchase = new Purchase($purchaseData);
    if ($purchase instanceof Purchase) {
      $editorPurchase = new EditorPurchase();
      $editorPurchase->setPurchase($purchase);
      $result['editor'] = $editorPurchase->prepareEditorPurchase($info['view']);
    } else {
      // Если не удалось получить объект
      $this->logs->actionLog($this->user->getUserInfo(), "API. Ошибка получения страницы 'Редактора закупок'. Не удалось создать объект Purchase из полученных данных о закупке из тела страницы.");
      $result['info']['error'] = ERROR_OTHER;
    }
    return json_encode($result);
  }

  /**
   * Получить данные для обновления отображения изменённых заказов
   * @param $lotNumber int Номер лота
   * @param $arg string Параметр фильтра
   * @return string Данные для отображения изменённого заказа, а так же итоговых сумм по закупке
   */
  function updateLotData ($lotNumber, $arg) {
    $editorPurchase = new EditorPurchase();
    $lots = $this->purchase->getLots();
    // Проверка фильтра
    if (!array_key_exists($arg, $editorPurchase->getFilters())) {
      $arg = 'all';
    }
    $lotInfo = array();
    $lotInfo['lot'] = $editorPurchase->getPurchaseLot($lotNumber, $lots[$lotNumber], $arg);
    $lotInfo['statistic'] = $editorPurchase->getPurchaseStatistic();
    $lotInfo['filters'] = $editorPurchase->getFiltersForView();
    return json_encode($lotInfo);
  }

  /**
   * Получить данные для обновления отображения изменённых потерянных заказов
   * @param $lotNumber int Номер лота
   * @return string Данные для отображения изменённого потерянного заказа, а так же итоговых сумм по закупке
   */
  function updateLostLotData ($lotNumber) {
    $editorPurchase = new EditorPurchase();
    $lots = $this->purchase->getLostLots();
    $lotInfo = array();
    if (isset($lots[$lotNumber])) {
      $lotInfo['lot'] = $editorPurchase->getPurchaseLostLot($lotNumber, $lots[$lotNumber]);
      $lotInfo['statistic'] = $editorPurchase->getPurchaseStatistic();
      return json_encode($lotInfo);
    } else {
      return 'true';
    }
  }

  /**
   * Обработка команды отметки платежа как ошибочного
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @param $arg string Параметр фильтра
   * @return false|string Данные для отображения изменённого заказа, а так же итоговых сумм по закупке
   */
  public function payErrorSet ($lotNumber, $payNumber, $arg) {
    $editorPurchase = new EditorPurchase();
    // Отметить платёж как ошибочный
    $result = $editorPurchase->payErrorSet($lotNumber, $payNumber);
    // Получение данных для отображения заказа
    if ($result) {
      return $this->updateLotData($lotNumber, $arg);
    }
    return 'false';
  }

  /**
   * Обработка команды удаления отметки у платежа как ошибочного
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @param $arg string Параметр фильтра
   * @return false|string Данные для отображения изменённого заказа, а так же итоговых сумм по закупке
   */
  public function payErrorDel ($lotNumber, $payNumber, $arg) {
    $editorPurchase = new EditorPurchase();
    // Удаление ошибочного платежа
    $result = $editorPurchase->payErrorDelete($lotNumber, $payNumber);
    // Получение данных для отображения заказа
    if ($result) {
      return $this->updateLotData($lotNumber, $arg);
    }
    return 'false';
  }

  /**
   * Обработка команды добавления корректировки
   * @param $lot int Номер лота
   * @param $comment string Комментарий к корректировке
   * @param $sum float Суммы корректировки
   * @param $arg string Параметр фильтра
   * @return false|string Данные для отображения изменённого заказа
   */
  public function correctionAdd ($lot, $comment, $sum, $arg) {
    $editorPurchase = new EditorPurchase();
    // Удалить корректировку
    $result = $editorPurchase->correctionAdd($lot, $sum, $comment);
    // Получение данных для отображения заказа
    if ($result) {
      return $this->updateLotData($lot, $arg);
    }
    return 'false';
  }

  /**
   * Обработка команды удаления корректировки
   * @param $lot int Номер лота
   * @param $correction int Номер корректировки
   * @param $arg string Параметр фильтра
   * @return false|string Данные для отображения изменённого заказа
   */
  public function correctionDel ($lot, $correction, $arg) {
    $editorPurchase = new EditorPurchase();
    // Удалить корректировку
    $result = $editorPurchase->correctionDelete($lot, $correction);
    // Получение данных для отображения заказа
    if ($result) {
      return $this->updateLotData($lot, $arg);
    }
    return 'false';
  }

  /**
   * Обработка команды удаления проставленной оплаты
   * @param $lot int Номер лота
   * @param $pay int Номер платежа
   * @param $arg string Параметр фильтра
   * @return false|string Данные для удаления проставленной оплаты
   */
  public function payDel ($lot, $pay, $arg) {
    $editorPurchase = new EditorPurchase();
    // Удалить проставленную оплату
    $result = $editorPurchase->payDelete($lot, $pay);
    // Получение данных для отображения заказа
    if ($result) {
      return $this->updateLotData($lot, $arg);
    }
    return 'false';
  }

  /**
   * Обработка команды удаления потерянной проставленной оплаты
   * @param $lot int Номер лота
   * @param $pay int Номер платежа
   * @return false|string Данные для удаления проставленной оплаты
   */
  public function lostPayDel ($lot, $pay) {
    $editorPurchase = new EditorPurchase();
    // Удалить проставленную оплату
    $result = $editorPurchase->lostPayDelete($lot, $pay);
    // Получение данных для отображения заказа
    if ($result) {
      return $this->updateLostLotData($lot);
    }
    return 'false';
  }

  /**
   * Обработка команды проставления платежа при помощи найденной вручную СМС
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @param $smsId int ID SMS
   * @return false|string Команда для обновления суммы на сайте СП
   */
  public function searchFilling ($lotNumber, $payNumber, $smsId) {
    $editorPurchase = new EditorPurchase();
    // Удалить проставленную оплату
    $result = $editorPurchase->payFilling($lotNumber, $payNumber, $smsId);
    if ($result) {
      // Получение команды для обновления суммы на сайте СП
      /** @var Lot[] $lots */
      $lots = $this->purchase->getLots();
      $lot = $lots[$lotNumber];
      $totalFound = $lot->getTotalFound();
      $purchaseId = $this->purchase->getPurchaseId();
      $userPurchaseId = $lot->getUserPurchase()->getUserPurchaseId();
      $cmd = $editorPurchase->getRequestUpdateSum($lotNumber, $purchaseId, $userPurchaseId, $totalFound);
      return json_encode($cmd);
    }
    return 'false';
  }

  /**
   * Сохранить тело полученной страницы в кэш
   * @param $body string Тело страницы
   * @param $request string Массив с данными запроса в JSON
   */
  private function saveBodyCache ($body, $request) {
    // Логирование запроса к сайту СП
    $this->logRequestToSiteSp($request);
    if (Registry_Request::instance()->get('load_from_cache')) {
      switch ($this->userRequest) {
        // Запросы к сайту СП при помощи расширения браузера
        case REQUEST_EXTENSIONS: {
          // Получить данные запроса
          $info = json_decode($request, true);
          if (!isset($info['urlRequest'])) {
            return;
          }
          // Сохранить тело страницы
          $fileName = $this->site->getCachePath($info['urlRequest']);
          if (!file_exists($fileName)) {
            $file = fopen($fileName, "w");
            fputs($file, $body);
            fclose($file);
          }
          break;
        }
        // Запросы к сайту СП при помощи curl по умолчанию
        default : {
          // Если прямой запрос к сайту СП

          break;
        }
      }
    }
  }

  /**
   * Логирование запроса к сайту СП
   * @param $request string Данные запроса в JSON
   */
  function logRequestToSiteSp ($request) {
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        $info = json_decode($request, true);
        if (!isset($info['urlRequest'])) {
          return;
        }
        $cmd = (isset($info['cmdSite'])) ? $info['cmdSite'] : '';
        $type = (isset($info['typeRequest'])) ? $info['typeRequest'] : '';
        $logs = new Logs();
        $logs->requestLog($info['urlRequest'], $cmd, $type);
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        // Если прямой запрос к сайту СП

        break;
      }
    }
  }

}