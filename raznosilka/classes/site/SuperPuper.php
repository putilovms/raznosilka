<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\SuperPuper.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Site_SuperPuper Содержит методы для работы с сайтом Супер Пупер - sp63.ru
 */
class Site_SuperPuper extends Site {
  /**
   * Путь к организаторской
   */
  const ORG = 'org.php?i=1';
  /**
   * Путь к форме входа на сайт
   */
  const LOGIN_FORM = 'ucp.php?mode=login';
  /**
   * Путь к закупке
   */
  const PURCHASE = 'org.php?i=1&mode=otchet&p=';
  /**
   * Путь к участнику закупке
   */
  const USER_PURCHASE = 'memberlist.php?mode=viewprofile&u=';
  /**
   * Путь к файлу комманд
   */
  const COMMAND_URL = 'function.php';
  /**
   * Фраза если нет доступа к странице
   */
  const NOT_ACCESS = 'У вас нет доступа в эту часть форума.';
  /**
   * Строка в cookie содержащая номер пользователя
   */
  const COOKIE_USER = 'phpbb3_sp63_u';
  /**
   * Округлять ли копейки
   */
  const ROUNDING = true;

  /**
   * Проверяет, есть ли доступ к сайту СП
   * @param $login string Логин от сайта СП
   * @param $pass string Пароль от сайта СП
   * @return bool Результат проверки доступа
   */
  function checkAccessByLogin ($login, $pass) {
    // Удаляем куки если они были
    $this->delCookieFromRegistry();
    // Первый вход. Получение данных
    $url = $this->urlSite . self::LOGIN_FORM;
    $result = $this->getPageInfo($url);
    // Второй вход. Авторизация
    $cookie = $result['cookie'];
    $post = $this->getPost($result['body'], $login, $pass);
    $result = $this->login($url, $post, $cookie);
    // Проверка успешности авторизации
    if (!$result['success']) {
      return false;
    }
    // Третий вход. Получение страницы
    $cookie = $result['cookie'];
    // URL к организаторской
    $url = $this->urlSite . self::ORG;
    $result = $this->getPageByCookie($url, '', $cookie);
    // Проверка доступа
    if (!$result['access']) {
      return false;
    }
    // todo проверить более надёжным способом, например на наличие массивов внутри страницы, т.к. лист закупок может быть пуст
    // Проверка путём получения списка закупок
    $listPurchaseArr = $this->getListPurchaseArr($result['body']);
    if (is_array($listPurchaseArr)) {
      // Обновляем куки
      $this->setCookieFromRegistry($cookie);
      return true;
    }
    return false;
  }

  /**
   * Извлеч массив списка закупок из JSON массива
   * @param $body string Тело страницы из который извлекается массив
   * @return false|array Резульатат выполнения:
   * - false - в случае если не удалось извлеч массив списка закупок
   * - array - извлечённый массив списка закупок в формате
   *  [x] - номер закупки
   *    ['id'] - id закупки
   *    ['name'] - имя закупки
   *    ['status'] - статус закупки
   *    ['pay_to'] - оплата участниками до
   *    ['url'] - URL для
   */
  function getListPurchaseArr ($body) {
    $result = false;
    if (!empty($body)) {
      // Подготовка страницы
      $search = array("\t", "\n", "\r");
      $body = str_replace($search, "", $body);
      // Удаляем всё до начала массива
      $str = "var purchases = {";
      $pos = strpos($body, $str);
      $body = substr($body, $pos + strlen($str), strlen($body));
      // Удаляем всё после окончания массива
      $str = "};";
      $pos = strpos($body, $str);
      $body = substr($body, 0, $pos);
      $body = '{' . $body . '}';
      // Декодируем полученный JSON массив
      $list = json_decode($body, true);
      if (!is_array($list)) {
        return false;
      }
      // форматирование массива в стандартный вид
      $result = array();
      foreach ($list as $purchase) {
        $item = array();
        $item['id'] = $purchase['id'];
        $item['name'] = $purchase['name'];
        $item['status'] = $purchase['status'];
        // форматирование времени в формате UNIX
        if (!empty($purchase['status_payto'])) {
          $dateTime = DateTime::createFromFormat("d-m-Y", $purchase['status_payto']);
          $item['pay_to'] = strtotime($dateTime->format('Y-m-d 00:00:00'));
        } else {
          $item['pay_to'] = 0;
        }
        $item['url'] = $this->getPurchaseURL($purchase['id']);
        $result[] = $item;
      }
    }
    return $result;
  }

  /**
   * Возвращает URL закупки по её ID
   * @param $id int ID закупки
   * @return string URL закупки
   */
  function getPurchaseURL ($id) {
    $url = $this->urlSite . self::PURCHASE . $id;
    return $url;
  }

  /**
   * Получение ID организатора из кук
   * @return false|int ID организатора
   */
  function getOrganizerId () {
    $result = false;
    $cookie = $this->getCookieFromRegistry();
    foreach ($cookie as $val) {
      $val = explode('=', $val);
      if ($val[0] == self::COOKIE_USER) {
        $result = $val[1];
        $result = trim($result, ';');
        break;
      }
    }
    return $result;
  }

  /**
   * Обновление или получение кук
   * @return bool Результат получения кук
   */
  function getCookie () {
    // Первый вход. Получение данных
    $url = $this->urlSite . self::LOGIN_FORM;
    $result = $this->getPageInfo($url);
    // Второй вход. Авторизация
    $cookie = $result['cookie'];
    $post = $this->getPost($result['body'], $this->login, $this->password);
    $result = $this->login($url, $post, $cookie);
    // Проверка успешности входа
    if ($result['success']) {
      $this->setCookieFromRegistry($result['cookie']);
      return true;
    } else {
      return false;
    }
  }

  /**
   * Получить массив списка закупок с сайта СП.
   * Коды ошибок:
   *  ERROR_NONE - Нет ошибок
   *  ERROR_ACCESS - Нет доступа к выбранной странице
   *  ERROR_PAGE - Не удалось получить страницу
   *  ERROR_DATA - Не удалось получить данные
   * @param array $info Содержит информацию о запросе @see Site::getRequestInfoListPurchase()
   * @return array Код ошибки или массив формата:
   *  ['info'] - информация о запросе @see Site::getRequestInfoListPurchase()
   *  ['list'] - список закупок
   *    [x] - номер закупки
   *      ['id'] - ID закупки
   *      ['name'] - Название закупки
   *      ['status'] - статус закупки (параметр получанный с сайта СП)
   *      ['pay_to'] - до какого числа должны оплатить УЗ (параметр получанный с сайта СП)
   *      ['url'] - url закупки
   */
  function getListPurchaseFromSite (array $info) {
    // Инициализация
    $result['info'] = $info;
    $result['list'] = array();
    $page = $this->getPage($result['info']['urlRequest']);
    // Если не удалось получить страницу
    if ($page === false) {
      $result['info']['error'] = ERROR_PAGE;
      return $result;
    }
    // Если доступа к странице нет
    if (!$page['access']) {
      $result['info']['error'] = ERROR_ACCESS;
      return $result;
    }
    $list = $this->getListPurchaseArr($page['body']);
    // Не удалось получить данные о списке закупок
    if ($list === false) {
      $result['info']['error'] = ERROR_DATA;
      return $result;
    }
    $result['list'] = $list;
    return $result;
  }

  /**
   * Получить информацию для запроса к сайту СП для получения списка закупок
   * @param $filter string Строка для поиска закупки
   * @return array Информация о запросе, формата:
   *  ['error'] - код ошибки @see Site::getRequestInfoListPurchase()
   *  ['filter'] - строка для поиска закупки
   *  ['page'] - номер текущей страницы (для пейджера)
   *  ['cmdService'] - команда к сервису
   *  ['typeRequest'] - тип запроса к расширению
   *  ['url'] - URL от которого пришёл запрос
   *  ['urlRequest'] - адрес запроса
   *  ['cmdSite'] - команда к сайту СП
   */
  function getRequestInfoListPurchase ($filter) {
    // Инициализация
    $result = array();
    $result['error'] = ERROR_NONE;
    $result['filter'] = $filter;
    $result['page'] = Pager::getPageParam();
    $result['cmdService'] = Command::CMD_LIST_PURCHASE_ORG;
    $result['typeRequest'] = 'getListPurchase';
    $result['url'] = $_SERVER['REQUEST_URI'];
    $url = $this->urlSite . self::ORG;
    $result['urlRequest'] = $url;
    $result['cmdSite'] = null;
    return $result;
  }

  /**
   * Получить информацию для запроса к сайту СП для получения страницы с закупкой.
   * @param $purchaseId int ID выбранной закупки
   * @param $cmdService string Типа запроса к сервису
   * @param $typeRequest string Типа запроса к сервису
   * @return array Информация о запросе, формата:
   *  ['error'] - код ошибки
   *  ['cmdService'] - команда к сервису
   *  ['typeRequest'] - тип запроса к расширению
   *  ['urlRequest'] - адрес запроса
   *  ['cmdSite'] - команда к сайту СП
   */
  function getRequestInfoPurchase ($cmdService, $typeRequest, $purchaseId = null) {
    $result = array();
    $result['error'] = ERROR_NONE;
    $result['cmdService'] = $cmdService;
    $result['typeRequest'] = $typeRequest;
    if (is_null($purchaseId)) {
      $url = null;
    } else {
      $url = $this->urlSite . self::PURCHASE . $purchaseId;
    }
    $result['urlRequest'] = $url;
    $result['cmdSite'] = null;
    return $result;
  }

  /**
   * Получить информацию для запроса к сайту СП для проставления оплаты
   * @param $keyLot int Номер лота
   * @param $purchaseId int ID выбранной закупки
   * @param $userPurchaseId int ID участника закупки
   * @param $fillingSum float Сумма к проставлению
   * @return array Информация для запроса к сайту СП для проставления оплаты, формата:
   *  ['error'] - код ошибки
   *  ['cmdService'] - команда к сервису
   *  ['typeRequest'] - тип запроса к расширению
   *  ['urlRequest'] - адрес запроса
   *  ['cmdSite'] - команда к сайту СП
   */
  function getRequestInfoFilling ($keyLot, $purchaseId, $userPurchaseId, $fillingSum) {
    // Инициализация
    $result = array();
    $result['error'] = ERROR_NONE;
    $result['cmdService'] = Command::CMD_AUTO_FILLING . $keyLot . '&body=';
    $result['typeRequest'] = 'autoFilling';
    $url = $this->getCommandUrl();
    $result['urlRequest'] = $url;
    $result['cmdSite'] = $this->getCommandAddPay($purchaseId, $userPurchaseId, $fillingSum);
    return $result;
  }

  /**
   * Получить информацию для запроса к сайту СП для обновления суммы оплаты
   * @param $keyLot int Номер лота
   * @param $purchaseId int ID выбранной закупки
   * @param $userPurchaseId int ID участника закупки
   * @param $sum float Сумма к проставлению
   * @return array Информация для запроса к сайту СП для обновления суммы оплаты, формата:
   *  ['error'] - код ошибки
   *  ['cmdService'] - команда к сервису
   *  ['typeRequest'] - тип запроса к расширению
   *  ['urlRequest'] - адрес запроса
   *  ['cmdSite'] - команда к сайту СП
   */
  function getRequestInfoUpdateSum ($keyLot, $purchaseId, $userPurchaseId, $sum) {
    // Инициализация
    $result = array();
    $result['error'] = ERROR_NONE;
    $cmdService = Command::CMD_UPDATE_SUM;
    $cmdService = sprintf($cmdService, $keyLot);
    $result['cmdService'] = $cmdService;
    $result['typeRequest'] = 'updateSum';
    $url = $this->getCommandUrl();
    $result['urlRequest'] = $url;
    $result['cmdSite'] = $this->getCommandAddPay($purchaseId, $userPurchaseId, $sum);
    return $result;
  }

  /**
   * Получить данные закупки с сайта СП по её ID
   * Коды ошибок:
   *  ERROR_ACCESS - Нет доступа к выбранной странице
   *  ERROR_PAGE - Не удалось получить страницу
   *  ERROR_DATA - Не удалось получить данные
   * @param $info array Информация о выбранной закупке
   * @return array|int Код ошибки или массив формата:
   *  ['purchase_name'] - имя закупки
   *  ['purchase_id'] - ID закупки
   *  ['url'] - url закупки
   *  ['purchase'] - данные с сайта СП
   *    [x] - номер заказа
   *      ['user'] - данные о участнике закупки
   *        ['user_purchase_name'] - ФИО УЗ
   *        ['user_purchase_nick'] - ник УЗ
   *        ['user_purchase_id'] - ID УЗ
   *        ['url'] - url к профилю УЗ
   *      ['comment_org'] - комментарий организатора
   *      ['total_put'] - всего внесено
   *      ['orders'] - товары
   *        [x] - номер товара
   *          ['id'] - ID товара
   *          ['org_fee'] - орг сбор
   *          ['state'] -
   *          ['delivery'] -
   *          ['comment_lot'] - комментарий УЗ
   *          ['name_lot'] - название товара
   *          ['price'] - цена
   */
  function getPurchaseFromSite (array $info) {
    // Получаем страницу с закупкой
    $page = $this->getPage($info['urlRequest']);
    // Если не удалось получить страницу
    if ($page === false) {
      return ERROR_PAGE;
    }
    // Если доступа к странице нет
    if (!$page['access']) {
      return ERROR_ACCESS;
    }
    $purchase = $this->getPurchaseArr($page['body']);
    // Не удалось получить данные
    if ($purchase === false) {
      return ERROR_DATA;
    }
    $purchase['url'] = $this->getPurchaseURL($purchase[PURCHASE_ID]);
    return $purchase;
  }

  /**
   * Извлеч массив закупоки из JSON массива
   * @param $body string Тело страницы из который извлекается массив
   * @param $url string URL для получения закупки
   * @return false|array Резульатат выполнения:
   * - false - в случае если не удалось извлеч данные закупоки
   * - array - извлечённый массив данных закупоки, формата:
   *  - [PURCHASE_NAME] - имя закупки
   *  - [PURCHASE_ID] -  ID закупки
   *  - ['purchase'] - массив с закупкой
   */
  function getPurchaseArr ($body, $url = '') {
    // Создаём DOM модель
    $dom = new DOMDocument();
    @$dom->loadHTML($body);
    // Создаём XPath
    $xpath = new DomXPath($dom);
    // Получаем имя закупки
    $namePurchase = $this->getNamePurchaseFromBody($xpath);
    if ($namePurchase === false) {
      return false;
    }
    // Получаем ID закупки
    $idPurchase = $this->getIdPurchaseFromBody($xpath);
    if ($idPurchase === false) {
      return false;
    }
    //Получаем данные закупки из JSON массива
    $arrUsers = $this->getJsonSortedArrayFromBody($body, 'users_sorted');
    $arrOrders = $this->getJsonArrayFromBody($body, 'orders');
    $arrLots = $this->getJsonSortedArrayFromBody($body, 'lots_sorted');
    $purchase = $this->getJsonData($arrUsers, $arrOrders, $arrLots);
    if (is_array($purchase)) {
      $result = array(PURCHASE_NAME => $namePurchase, PURCHASE_ID => $idPurchase, 'purchase' => $purchase);
      return $result;
    } else {
      return false;
    }
  }

  /**
   * Получение имени закупки из XPath тела страницы с закупкой
   * @param DomXPath $xpath XPath тела страницы с закупкой
   * @return false|string Имя закупки
   */
  function getNamePurchaseFromBody (DomXPath $xpath) {
    $result = false;
    $query = $xpath->query(".//*[@id='page-body']/div/div/font/h3/a");
    if ($query->length > 0) {
      $result = $query->item(0)->nodeValue;
      $result = trim($result, '"');
    }
    return $result;
  }

  /**
   * Получение ID закупки из XPath тела страницы с закупкой
   * @param DomXPath $xpath XPath тела страницы с закупкой
   * @return false|int ID закупки
   */
  function getIdPurchaseFromBody (DomXPath $xpath) {
    $result = false;
    $query = $xpath->query(".//input[@name='purchase_id']");
    if ($query->length > 0) {
      /** @var DOMElement $node */
      $node = $query->item(0);
      $result = $node->getAttribute("value");
    }
    return $result;
  }

  /**
   * Подготовка параметров PHP и тела страницы для извлечения JSON массива
   * @param $body string Тело страницы
   * @return string Подготовленное тело страницы
   */
  private function initGetJsonArray ($body) {
    // Снятие ограничения на PCRE выражения
    ini_set('pcre.backtrack_limit', 10000000);
    ini_set('pcre.recursion_limit', 10000000);
    // Получение строки с массивом
    $search = array("\t", "\n", "\r");
    $body = str_replace($search, "", $body);
    return $body;
  }

  /**
   * Извлекает JSON массив из тела страницы с закупкой по его имени
   * @param $body string Тело страницы с закупкой
   * @param $arrName string Имя извлекаемого JSON массива
   * @return bool|array Нормализованный и декодированный JSON массив
   */
  function getJsonArrayFromBody ($body, $arrName) {
    $body = $this->initGetJsonArray($body);
    $pattern = '|var ' . $arrName . ' = (\{[\s\S]*?\});|';
    $result = preg_match($pattern, $body, $matches);
    if ($result) {
      // Нормализация массива
      $arr = $this->normalizeJsonArray($matches[1]);
      // Преобразование массива
      $decodeArr = json_decode($arr, true);
      return $decodeArr;
    }
    return false;
  }

  /**
   * Извлекает JSON массив из тела страницы с закупкой по его имени. Для массивов с сортировкой
   * @param $body string Тело страницы с закупкой
   * @param $arrName string Имя извлекаемого JSON массива
   * @return bool|array Нормализованный и декодированный JSON массив
   */
  private function getJsonSortedArrayFromBody ($body, $arrName) {
    $body = $this->initGetJsonArray($body);
    $pattern = '|var ' . $arrName . ' = (\[[\s\S]*?\]);|';
    $result = preg_match($pattern, $body, $matches);
    if ($result) {
      // Нормализация массива
      $arr = $this->normalizeJsonArray($matches[1]);
      // Преобразование массива
      $decodeArr = json_decode($arr, true);
      // Подготовка массива
      $resultArr = array();
      foreach ($decodeArr as $value) {
        $resultArr[$value['id']] = $value;
      }
      return $resultArr;
    }
    return false;
  }

  /**
   * Нормализация полученного с сайта СП JSON массива
   * @param $json array Исходный JSON массив
   * @return array Нормализированный JSON массив
   */
  function normalizeJsonArray ($json) {
    $pattern[0] = '#(\{)([^"\}])#';
    $pattern[1] = '#([^"])(\:\{)#';
    $pattern[2] = '#(\},)([^"\{])#';
    $pattern[3] = '#(\\\\)(?!n|b|f|r|t|\"|/|u[a-fA-F0-9]{4})#';
    $replacement[0] = '$1"$2';
    $replacement[1] = '$1"$2';
    $replacement[2] = '$1"$2';
    $replacement[3] = '';
    $arr = preg_replace($pattern, $replacement, $json);
    return $arr;
  }

  /**
   * Преобразование полученного массива с закупкой в стандартный вид
   * @param $arrUsers array
   * @param $arrOrders array
   * @param $arrLots array
   * @return array Массив с данными о закупке
   *  [x] - Номер отчёта по заказу
   *    ['total_put'] - Уже внесено денег
   *    ['comment_org'] - Комментарий организатора
   *    ['user'] - информация о пользователе
   *      [USER_PURCHASE_ID] - ID участника на сайте
   *      [USER_PURCHASE_NAME] - ФИО участника
   *      [USER_PURCHASE_NICK] - Ник участника
   *      ['url'] - URL к профилю участника закупки
   *    ['pays'] - массив платежей
   *      [x] - номер платежа
   *        [PAY_TIME] - дата и время платежа
   *        [PAY_SUM] - сумма платежа, руб
   *        [PAY_CARD_PAYER] - карта с которой был зачислен платёж
   *        [PAY_CREATED] - платёж создан
   *    ['orders'] - массив заказов
   *      [x] - номер заказа
   *        ['id'] - ID заказа
   *        ['org_fee'] - оргсбор, %
   *        ['state'] - статус закупки
   *        ['delivery'] - сумма доставки, руб
   *        ['comment_lot'] - комментарий участника
   *        ['name_lot'] - название товара
   *        ['price'] - цена товара, руб
   */
  function getJsonData ($arrUsers, $arrOrders, $arrLots) {
    $result = array();
    foreach ($arrUsers as $user) {
      $info = array();
      $info['user'][USER_PURCHASE_NAME] = $user['fio'];
      $info['user'][USER_PURCHASE_NICK] = $user['name'];
      $info['user'][USER_PURCHASE_ID] = $user['id'];
      $info['user']['url'] = $this->getUserPurchaseURL($user['id']);
      $info['comment_pay'] = '';
      $info['comment_org'] = Kit::plainText($user['comment']);
      $info['total_put'] = (float)$user['money'];
      $info['discount'] = 0;
      // Получение суммы за выдачу заказа
      $info['cvz_status'] = '';
      $info['cvz_sum'] = 0;
      // Получение списка товаров
      foreach ($user['orders'] as $orderId => $order) {
        $arr = array();
        $arr['id'] = $orderId;
        $arr['org_fee'] = (int)$arrOrders[$orderId]['org_fee']-(int)$arrOrders[$orderId]['bonus'];
        $arr['state'] = $arrOrders[$orderId]['state'];
        $arr['delivery'] = (float)$arrOrders[$orderId]['delivery'];
        $arr['comment_lot'] = Kit::plainText($arrOrders[$orderId]['comment']);
        $lotId = $arrOrders[$orderId]['lot_id'];
        $arr['name_lot'] = Kit::plainText($arrLots[$lotId]['name']);
        $arr['price'] = (float)$arrLots[$lotId]['price'];
        $info['orders'][] = $arr;
      }
      // Получение списка платежей
      foreach ($user['payments'] as $keyPay => $pay) {
        // Приведение даты оплаты к стандартному виду
        $date = DateTime::createFromFormat('d-m-Y H:i', ($pay['date'] . " " . $pay['time']));
        $info['pays'][$keyPay][PAY_TIME] = $date->format('Y-m-d H:i:00');
        // Приведение даты создания отчёта о платеже к стандартному виду
        $date = DateTime::createFromFormat('H:i:s d-m-Y', ($pay['created']));
        $info['pays'][$keyPay][PAY_CREATED] = $date->format('Y-m-d H:i:s');
        $info['pays'][$keyPay][PAY_SUM] = (float)$pay['sum'];
        $info['pays'][$keyPay][PAY_CARD_PAYER] = ($pay['card'] > 0) ? substr($pay['card'], -4) : '0';
      }
      $result[] = $info;
    }
    return $result;
  }

  /**
   * Получить URL к личному кабинету участника закупки
   * @param $id int ID участника закупки
   * @return string URL к личному кабинету участника закупки
   */
  function getUserPurchaseURL ($id) {
    $url = $this->urlSite . self::USER_PURCHASE . $id;
    return $url;
  }

  /**
   * Получить команду для проставления платежа на сайте СП
   * @param $purchaseId int ID закупки
   * @param $userPurchaseId int ID участника закупки
   * @param $sum float Сумма для проставления
   * @return string Команда для проставления платежа на сайте СП
   */
  function getCommandAddPay ($purchaseId, $userPurchaseId, $sum) {
    $cmd = 'cmd=set_user_purchase_money&user_id=' . $userPurchaseId . '&purchase_id=' . $purchaseId . '&money=' . $sum;
    return $cmd;
  }

  /**
   * Получить статусы закупок которые следует игнорировать
   * @return array Статусы закупок которые следует игнорировать
   */
  function getPurchaseStatusIgnore(){
    $purchaseStatusIgnore = array(3);
    return $purchaseStatusIgnore;
  }

  /**
   * Получить URL для отсылки команд на сайт СП
   * @return string URL для отсылки команд на сайт СП
   */
  function getCommandUrl () {
    $url = $this->urlSite . self::COMMAND_URL;
    return $url;
  }

  /**
   * Проверить ответ полученный после отправки команды на сайт СП
   * @param $response array Тело ответа полученного методом getPage() или другим способом
   * @return bool True - если команда выполнена успешно
   */
  function checkResponse ($response) {
    $result = false;
    if ($response == 'ok') {
      $result = true;
    }
    return $result;
  }

  /**
   * Проверяет, есть ли доступ к запрашиваемой странице (права организатора)
   * @param $body string Тело страницы HTML
   * @param $request string json массив с данными запроса от сервиса
   * @return bool Проверка есть ли доступ к выбранной странице
   */
  function checkAccessPermission ($body, $request) {
    $result = true;
    // Создаём DOM модель
    $dom = new DOMDocument();
    @$dom->loadHTML($body);
    // Создаём XPath
    $xpath = new DomXPath($dom);
    // Проверка наличия надписи о том что доступа нет
    $query = $xpath->query("//*[@id='message']/div/p");
    if ($query->length > 0) {
      $str = $query->item(0)->nodeValue;
      if ($str == self::NOT_ACCESS) {
        $result = false;
      }
    }
    return $result;
  }

  /**
   * Получить имя cookie для получения ID пользователя
   * @return string Имя cookie для получения ID пользователя
   */
  function getNameCookieUser () {
    return self::COOKIE_USER;
  }

  /**
   * Округлять ли до копеек
   * @return bool
   */
  function rounding(){
    return self::ROUNDING;
  }

}