<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Site.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Site Абстрактный класс, отвественный за инструкции по работе с каждым отдельным сайтом
 */
abstract class Site {
  /**
   * Заголовок имитируемого браузера
   */
  const USER_AGENT = "User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)";

  /**
   * @var string URL сайта СП
   */
  protected $urlSite;
  /**
   * @var int ID сайта СП
   */
  protected $idSite;
  /**
   * @var string Имя сайта СП
   */
  protected $nameSite;
  /**
   * @var int Количество дней на проставление оплат по правилам сайта СП
   */
  protected $fillingDay;
  /**
   * @var string Логин для доступа к сайту СП
   */
  protected $login;
  /**
   * @var string Пароль для доступа к сайту СП
   */
  protected $password;
  /**
   * @var Registry_Session Реестр сессий
   */
  protected $regSess;
  /**
   * @var Registry_Request Реестр
   */
  protected $regReq;
  /**
   * @var string Путь к временному каталогу
   */
  protected $tmpPath;
  /**
   * @var string Путь к кэшу данных с сайта СП
   */
  protected $tmpCachePath;
  /**
   * @var string Временная зона сайта СП
   */
  private $spTimeZone;
  /**
   * @var int Тип запроса к сайту СП, по умаолчанию для данного сайта СП
   */
  private $spRequest;
  /**
   * @var string Режим работы сайта СП
   */
  protected $mode;
  /**
   * @var int ID пользователя сервиса
   */
  protected $idUser;

  /**
   * Конструктор класса
   * @param array $sp Данные о СП полученные из БД
   * @throws Exception
   */
  function __construct (array $sp) {
    // Инициализация
    if (!isset($sp[SP_ID]) or !isset($sp[SP_SITE_NAME]) or !isset($sp[SP_SITE_URL]) or !isset($sp[SP_FILLING_DAY]) or !isset($sp[SP_TIME_ZONE])) {
      throw new Exception();
    }
    $this->idSite = $sp[SP_ID];
    $this->nameSite = $sp[SP_SITE_NAME];
    $this->urlSite = $sp[SP_SITE_URL];
    $this->fillingDay = $sp[SP_FILLING_DAY];
    $this->spTimeZone = $sp[SP_TIME_ZONE];
    $this->spRequest = $sp[SP_REQUEST];
    $this->regSess = Registry_Session::instance();
    $this->regReq = Registry_Request::instance();
    $this->tmpPath = $_SERVER['DOCUMENT_ROOT'] . $this->regReq->get('tmp_path');
    $this->tmpCachePath = $_SERVER['DOCUMENT_ROOT'] . $this->regReq->get('tmp_cache_path');
    $this->mode = $this->regReq->get('mode');
    /** @var $user User */
    $user = $this->regReq->get('user');
    $userInfo = $user->getUserInfo();
    $this->idUser = $userInfo[USER_ID];
    $this->login = $userInfo[USER_SP_LOGIN];
    $this->password = $userInfo[USER_SP_PASSWORD];
  }

  /**
   * Получить тип запроса к сайту СП
   * @return int Тип запроса к сайту СП
   */
  function getSpRequest () {
    return $this->spRequest;
  }

  /**
   * Получить название сайта СП
   * @return string Название сайта
   */
  function getNameSite () {
    return $this->nameSite;
  }

  /**
   * Фабрика для создания нужного подтипа объекта Site в зависимости
   * от настроек текущего пользователя.
   * @param int $idSite ID сайта СП
   * @return false|Site Объект для работы с сайтом СП
   */
  static function getSite ($idSite = null) {
    $result = false;
    // Если ID сайта СП не задано явно, то получить его из настроек пользователя
    if (is_null($idSite)) {
      /** @var $user User */
      $user = Registry_Request::instance()->get('user');
      $userInfo = $user->getUserInfo();
      $idSite = $userInfo[SP_ID];
    }
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $sp = $db->getSpById($idSite);
    if ($sp !== false) {
      switch ($idSite) {
        case 1 :
          $result = new Site_SuperPuper($sp);
          break;
        case 2 :
          $result = new Site_SpBermama($sp);
          break;
      }
    }
    return $result;
  }

  /**
   * Проверка доступа к сайту
   * @param $login string Логин от сайта СП
   * @param $pass string Пароль от сайта СП
   * @return bool Результат проверки доступа
   */
  abstract function checkAccessByLogin ($login, $pass);

  /**
   * Получение ID организатора
   * @return false|int ID организатора
   */
  abstract function getOrganizerId ();

  /**
   * Получает страницу с сайта при помощи прямого запроса к сайту.
   * @param $url string URL страницы к которой осуществляется доступ
   * @param null|string $cmd Дополнительный параметр. Отсылаемая команда.
   * @return array|false Результат получения страницы
   *  array - в случае успеха
   *    ['body'] - полученная страница сайта
   *    ['curl_info'] - информация о работе curl
   *    ['header_out'] - отсылаемые заголовки, строка
   *    ['header_in'] - полученные заголовки, строка
   *    ['cookie'] - куки
   *    ['success'] - успешность входа: false - неудача, true - авторизация
   *  false - в случае неудачи
   */
  function getPage ($url, $cmd = '') {
    // Если включен режим отладки, то загружать страницу из файла
    if ($this->mode == 'debug') {
      if (Registry_Request::instance()->get('load_from_cache')) {
        $fileName = $this->getCachePath($url);
        if (file_exists($fileName)) {
          $file = fopen($fileName, "r");
          $result['body'] = fread($file, filesize($fileName));
          $request = json_encode(array('urlRequest' => $url, 'urlResponse' => $url));
          $result['access'] = $this->checkAccessPermission($result['body'], $request);
          fclose($file);
          return $result;
        }
      }
    }
    // Проверка на наличие пароля и логина для сайта СП
    if (!is_null($this->login) and !is_null($this->password)) {
      $cookie = $this->getCookieFromRegistry();
      if (isset($cookie)) {
        // Получаем страницу
        $result = $this->getPageByCookie($url, $cmd, $cookie);
        if ($result['success']) {
          return $result;
        }
      }
      // Если кук нет или они устарели, то получаем их
      if ($this->getCookie()) {
        $cookie = $this->getCookieFromRegistry();
        // Получаем страницу повторно
        $result = $this->getPageByCookie($url, $cmd, $cookie);
        if ($result['success']) {
          return $result;
        }
      }
    }
    return false;
  }

  /**
   * Получает куки из реестра сессий
   * @return array|null Куки
   */
  function getCookieFromRegistry () {
    return $this->regSess->get('cookie');
  }

  /**
   * Получение страницы по URL (все последующие запросы на сайт)
   * @param $url string URL страницы к которой осуществляется доступ
   * @param $cmd string Дополнительный параметр. Отсылаемый параметр.
   * @param $cookie string Строка с куками для авторизации на сайте
   * @return array Возвращаемый массив:
   * - ['body'] - полученная страница сайта
   * - ['curl_info'] - информация о работе curl
   * - ['header_out'] - отсылаемые заголовки, строка
   * - ['header_in'] - полученные заголовки, строка
   * - ['cookie'] - куки
   * - ['success'] - успешность входа: false - неудача, true - авторизация
   * - ['access'] - наличие доступа к странице
   */
  function getPageByCookie ($url, $cmd, $cookie) {
    $headerPath = $this->tmpPath . '/' . md5(time()) . '.header';
    $handle = fopen($headerPath, 'w+');
    $ch = curl_init();
    // Установка параметров
    curl_setopt($ch, CURLOPT_URL, $url); // Указание URL
    curl_setopt($ch, CURLOPT_NOBODY, 0); // Получать тело страницы
    curl_setopt($ch, CURLOPT_HEADER, 0); // Не получать заголовки сайта
    curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT); // Имитация браузера
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Возвращать полученные данные в переменную
    curl_setopt($ch, CURLOPT_WRITEHEADER, $handle); // Записать заголовок сайта во временный файл
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1); // Показать отсылаемый заголовок в curl_getinfo
    curl_setopt($ch, CURLOPT_COOKIE, implode(" ", $cookie)); // Подстановка cookie
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); // Задать версию http протокола (решение 56 ошибки curl)
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); // Следовать редиректу
    // Если указан запрос, то отослать его методом POST
    if (!empty($cmd)) {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $cmd);
    }
    // Получение результата
    $result['body'] = curl_exec($ch); // Тело запрашиваемой страницы
    // Генерация ошибки
    if ($result['body'] === false) {
      trigger_error('Curl error #' . curl_errno($ch) . ': ' . curl_error($ch), E_USER_WARNING);
    }
    $result['curl_info'] = curl_getinfo($ch); // Информация о работе curl
    $result['header_out'] = trim(curl_getinfo($ch, CURLINFO_HEADER_OUT)); // Отсылаемые заголовки
    curl_close($ch); // Закрыть соединение
    fclose($handle);
    // Получение заголовков
    $result['header_in'] = trim(file_get_contents($headerPath)); // Получаемые заголовки
    @unlink($headerPath); // Удалить временный файл с заголовком
    // Получение кук
    $result['cookie'] = $this->getCookieFromHeaders($result['header_in']);
    // Проверка успешности авторизации
    $result['success'] = empty($result['cookie']) ? 1 : 0;
    // Определение URL редиректа
    $redirectUrl = (empty($result['curl_info']['redirect_url'])) ? $result['curl_info']['url'] : $result['curl_info']['redirect_url'];
    $request = json_encode(array('urlRequest' => $url, 'urlResponse' => $redirectUrl));
    $result['access'] = $this->checkAccessPermission($result['body'], $request);
    //Сохранить страницу в файл если выбран режим загрузки из кэща
    if (Registry_Request::instance()->get('load_from_cache')) {
      $fileName = $this->getCachePath($url);
      if (!file_exists($fileName)) {
        $file = fopen($fileName, "w");
        fputs($file, $result['body']);
        fclose($file);
      }
    }
    // Лог
    $logs = new Logs();
    if (empty($cmd)) {
      $type = $result['success'] ? 'Получение страницы: Успех' : 'Получение страницы: Неудача';
    } else {
      $type = 'Команда сайту СП';
    }
    $logs->requestLog($url, $cmd, $type);
    return $result;
  }

  /**
   * Проверяет, есть ли доступ к запрашиваемой странице
   * @param $body string Тело страницы HTML
   * @param $request string json массив с данными запроса от сервиса
   * @return bool Проверка есть ли доступ к выбранной странице
   */
  abstract function checkAccessPermission ($body, $request);

  /**
   * Извелечение возвращённых кук из заголовка сайта
   * @param $headers string Полученные заголовки от сайта
   * @return array Массив с куками формата:
   *  [x] - строка вида: ИМЯ=ЗНАЧЕНИЕ;
   */
  function getCookieFromHeaders ($headers) {
    $cookie = array();
    // Получить массив всех заголовков от сервера
    $headers = explode("\n", $headers);
    for ($i = 0; $i < sizeof($headers); $i++) {
      if (strpos($headers[$i], 'Cookie:') !== false) {
        list(, $cookie[]) = explode(' ', $headers[$i]);
      }
    }
    return $cookie;
  }

  /**
   * Обновление или получение кук
   * @return bool Результат получения кук
   */
  abstract function getCookie ();

  /**
   * Записывает куки в реестр сессий
   * @param $cookie array Куки
   */
  function setCookieFromRegistry ($cookie) {
    $this->regSess->set('cookie', $cookie, true);
  }

  /**
   * Удаляет куки из реестра сессий
   */
  function delCookieFromRegistry () {
    $this->regSess->del('cookie');
  }

  /**
   * Получение информации с сайта для последующей авторизации (первый запрос на сайт)
   * @param $url string URL страницы к которой осуществляется доступ
   * @return array Возвращаемый массив:
   * - ['body'] - полученная страница сайта
   * - ['curl_info'] - информация о работе curl
   * - ['header_out'] - отсылаемые заголовки, строка
   * - ['header_in'] - полученные заголовки, строка
   * - ['cookie'] - куки
   */
  function getPageInfo ($url) {
    $headerPath = $this->tmpPath . '/' . md5(time()) . '.header';
    $handle = fopen($headerPath, 'w+');
    $ch = curl_init();
    // Установка параметров
    curl_setopt($ch, CURLOPT_URL, $url); // Указание URL к форме входа
    curl_setopt($ch, CURLOPT_NOBODY, 0); // Получать тело страницы
    curl_setopt($ch, CURLOPT_HEADER, 0); // Не получать заголовки сайта
    curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT); // Имитация браузера
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Возвращать полученные данные в переменную
    curl_setopt($ch, CURLOPT_WRITEHEADER, $handle); // Записать заголовок сайта во временный файл
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1); // Показать отсылаемый заголовок в curl_getinfo
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); // Задать версию http протокола (решение 56 ошибки curl)
    // Получение результата
    $result['body'] = curl_exec($ch); // Тело запрашиваемой страницы
    // Генерация ошибки
    if ($result['body'] === false) {
      trigger_error('Curl error #' . curl_errno($ch) . ': ' . curl_error($ch), E_USER_WARNING);
    }
    $result['curl_info'] = curl_getinfo($ch); // Информация о работе curl
    $result['header_out'] = trim(curl_getinfo($ch, CURLINFO_HEADER_OUT)); // Отсылаемые заголовки
    curl_close($ch); // Закрыть соединение
    fclose($handle);
    // Получение заголовков
    $result['header_in'] = trim(file_get_contents($headerPath));
    @unlink($headerPath); // Удалить временный файл с заголовком
    // Получение кук
    $result['cookie'] = $this->getCookieFromHeaders($result['header_in']);
    // Лог
    $logs = new Logs();
    $logs->requestLog($url, '', 'Первичный запрос на сайт СП');
    return $result;
  }

  /**
   * Составление POST запроса из полученных полей input и
   * имени пользователя и пароля к сайту СП.
   * @param $body string Тело страницы
   * @param $login string Логин к сайту СП
   * @param $pass string Пароль к сайту СП
   * @return string Запрос для отсылки на сайт СП
   */
  function getPost ($body, $login, $pass) {
    $post = array();
    // Массив возможных названий полей для ввода регистрационных данных
    $fieldName = array('name', 'username');
    $fieldPass = array('pass', 'password');
    // Получение и заполнение POST данных
    $doc = new DOMDocument();
    @$doc->loadHTML($body);
    $searchNodes = $doc->getElementsByTagName("input");
    foreach ($searchNodes as $cur) {
      /** @var $cur DOMElement */
      $post[$cur->getAttribute('name')] = $cur->getAttribute('value');
      if (in_array($cur->getAttribute('name'), $fieldName)) {
        $post[$cur->getAttribute('name')] = $login;
      }
      if (in_array($cur->getAttribute('name'), $fieldPass)) {
        $post[$cur->getAttribute('name')] = $pass;
      }
    }
    $post = http_build_query($post);
    return $post;
  }

  /**
   * Авторизация на сайте, получение куков.
   * @param $url string Адрес с формой входа
   * @param $post string POST запрос с данными для входа
   * @param $cookie string Куки с первого входа
   * @param null|string $ref Ссылка с которой пришли на данный $url
   * @return array Возвращаемый массив:
   * - ['body'] - полученная страница сайта
   * - ['curl_info'] - информация о работе curl
   * - ['header_out'] - отсылаемые заголовки, строка
   * - ['header_in'] - полученные заголовки, строка
   * - ['cookie'] - куки
   * - ['success'] - успешность входа: false - неудача, true - авторизация
   */
  function login ($url, $post, $cookie, $ref = null) {
    $headerPath = $this->tmpPath . '/' . md5(time()) . '.header';
    $handle = fopen($headerPath, 'w+');
    $ch = curl_init();
    // Установка параметров
    curl_setopt($ch, CURLOPT_URL, $url); // Указание URL к форме входа
    curl_setopt($ch, CURLOPT_NOBODY, 0); // Получать тело страницы
    curl_setopt($ch, CURLOPT_HEADER, 0); // Не получать заголовки сайта
    curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT); // Имитация браузера
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Возвращать полученные данные в переменную
    curl_setopt($ch, CURLOPT_WRITEHEADER, $handle); // Записать заголовок сайта во временный файл
    curl_setopt($ch, CURLOPT_POST, 1); // Передача данных методом POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // POST данные
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1); // Показать отсылаемый заголовок в curl_getinfo
    curl_setopt($ch, CURLOPT_COOKIE, implode(" ", $cookie)); // Подстановка cookie
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); // Задать версию http протокола (решение 56 ошибки curl)
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Разрешить редирект
    if (!empty($ref)) {
      // Некоторые сайты требуют данные с какой страницы пришёл пользователь
      curl_setopt($ch, CURLOPT_REFERER, $ref);
    }
    // Получение результата
    $result['body'] = curl_exec($ch); // Тело запрашиваемой страницы
    // Генерация ошибки
    if ($result['body'] === false) {
      trigger_error('Curl error #' . curl_errno($ch) . ': ' . curl_error($ch), E_USER_WARNING);
    }
    $result['curl_info'] = curl_getinfo($ch); // Информация о работе curl
    $result['header_out'] = trim(curl_getinfo($ch, CURLINFO_HEADER_OUT)); // Отсылаемые заголовки
    curl_close($ch); // Закрыть соединение
    fclose($handle);
    // Получение заголовков
    $result['header_in'] = trim(file_get_contents($headerPath));
    @unlink($headerPath); // Удалить временный файл с заголовком
    // Получение кук
    $result['cookie'] = $this->getCookieFromHeaders($result['header_in']);
    // Проверка успешности авторизации
    $result['success'] = empty($result['cookie']) ? 0 : 1;
    // Лог
    $logs = new Logs();
    $logs->requestLog($url, 'Учётные данные', 'Вход на сайт СП');
    return $result;
  }

  /**
   * Получить количество дней на проставление оплат по правилам сайта СП
   * @return int Количество дней на проставление оплат по правилам сайта СП
   */
  function getFillingDay () {
    return $this->fillingDay;
  }

  /**
   * Получить временную зону сайта СП
   * @return string Временная зона
   */
  function getSpTimeZone () {
    return $this->spTimeZone;
  }

  /**
   * Извлеч массив списка закупок из JSON массива
   * @param $body string Тело страницы из который извлекается массив
   * @return false|array Резульатат выполнения:
   * - false - в случае если не удалось извлеч массив списка закупок
   * - array - извлечённый массив списка закупок
   */
  abstract function getListPurchaseArr ($body);

  /**
   * Получить массив списка закупок с сайта СП
   * Коды ошибок:
   *  ERROR_NONE - Нет ошибок
   *  ERROR_ACCESS - Нет доступа к выбранной странице
   *  ERROR_PAGE - Не удалось получить страницу
   *  ERROR_DATA - Не удалось получить данные
   * @param array $info Содержит информацию о запросе @see Site::getRequestInfoListPurchase()
   * @return array Резульатат выполнения, формата:
   *  ['info'] - информация о запросе @see Site::getRequestInfoListPurchase()
   *  ['list'] - список закупок
   *    [x] - номер закупки
   *      ['id'] - ID закупки
   *      ['name'] - Название закупки
   *      ['status'] - статус закупки (параметр получанный с сайта СП)
   *      ['pay_to'] - до какого числа должны оплатить УЗ (параметр получанный с сайта СП)
   *      ['url'] - url закупки
   */
  abstract function getListPurchaseFromSite (array $info);

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
  abstract function getRequestInfoListPurchase ($filter);

  /**
   * Получить информацию для запроса к сайту СП для получения страницы с закупкой.
   * Данный метод НЕОБХОДИМО ПЕРЕОПРЕДЕЛЯТЬ!!!
   * @param $purchaseId int ID выбранной закупки
   * @param $cmdService string Типа запроса к сервису
   * @param $typeRequest string Типа запроса к сервису
   * @return array Информация о запросе, формата:
   *  ['error'] - код ошибки
   *  ['cmdService'] - команда к сервису
   *  ['typeRequest'] - тип запроса к расширению
   *  ['url'] - URL от которого пришёл запрос
   *  ['urlRequest'] - адрес запроса
   *  ['cmdSite'] - команда к сайту СП
   */
  abstract function getRequestInfoPurchase ($cmdService, $typeRequest, $purchaseId = null);

  /**
   * Получить информацию для запроса к сайту СП для проставления оплаты.
   * Данный метод НЕОБХОДИМО ПЕРЕОПРЕДЕЛЯТЬ!!!
   * @param $keyLot int Номер лота
   * @param $purchaseId int ID выбранной закупки
   * @param $userPurchaseId int ID участника закупки
   * @param $fillingSum float Сумма к проставлению
   * @return array Информация для запроса к сайту СП для проставления оплаты, формата:
   *  ['error'] - код ошибки
   *  ['cmdService'] - команда к сервису
   *  ['typeRequest'] - тип запроса к расширению
   */
  abstract function getRequestInfoFilling ($keyLot, $purchaseId, $userPurchaseId, $fillingSum);

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
  abstract function getRequestInfoUpdateSum($keyLot, $purchaseId, $userPurchaseId, $sum);

  /**
   * Получить данные закупки с сайта СП по её ID
   * @param $info array Информация о выбранной закупке
   * @return array|int Код ошибки или массив с закупкой
   */
  abstract function getPurchaseFromSite (array $info);

  /**
   * Извлеч массив закупоки из JSON массива
   * @param $body string Тело страницы из который извлекается массив
   * @param $url string URL для получения закупки
   * @return array|false Резульатат выполнения:
   * - false - в случае если не удалось извлеч данные закупоки
   * - array - извлечённый массив данных закупоки
   */
  abstract function getPurchaseArr ($body, $url = '');

  /**
   * Возвращает URL закупки по её ID
   * @param $id int ID закупки
   * @return string URL закупки
   */
  abstract function getPurchaseURL ($id);

  /**
   * Получить URL к личному кабинету участника закупки
   * @param $id int ID участника закупки
   * @return string URL к личному кабинету участника закупки
   */
  abstract function getUserPurchaseURL ($id);

  /**
   * Получить команду для проставления платежа на сайте СП
   * @param $purchaseId int ID закупки
   * @param $userPurchaseId int ID участника закупки
   * @param $sum float Сумма для проставления
   * @return string Команда для проставления платежа на сайте СП
   */
  abstract function getCommandAddPay ($purchaseId, $userPurchaseId, $sum);

  /**
   * Получить URL для отсылки команд на сайт СП
   * @return string URL для отсылки команд на сайт СП
   */
  abstract function getCommandUrl ();

  /**
   * Проверить ответ от сайта СП
   * @param $response array Ответ полученный методом getPage()
   * @return bool Полученный ответ от сайта СП
   */
  abstract function checkResponse ($response);

  /**
   * Получить путь к кешу запрашиваемой страницы
   * @param $url string URL запрашиваемой страницы
   * @return string Путь к кешу запрашиваемой страницы
   */
  function getCachePath ($url) { // todo этот метод нужно вынести из класса Site
    $parse = parse_url($url);
    // Создать каталоги
    $dir = $this->tmpCachePath . '/' . $parse['host'] . '/' . $this->idUser;
    if (!file_exists($dir)) {
      mkdir($dir, 0755, true);
    }
    // Очистить от лишних символов имя файла
    $query = (isset($parse['query'])) ? "&{$parse['query']}" : '';
    $fileName = $parse['path'] . $query;
    $fileName = preg_replace('#[\/:*?"<>|]#', "", $fileName);
    $fileName = $dir . '/' . $fileName . '.html';
    return $fileName;
  }

  /**
   * Получить имя cookie для получения ID пользователя
   * @return string Имя cookie для получения ID пользователя
   */
  abstract function getNameCookieUser ();

  /**
   * Получить URL сайта СП выбранного пользователем
   * @return string URL сайта СП выбранного пользователем
   */
  function getUrlSite () {
    return $this->urlSite;
  }

  /**
   * Округлять ли суммы на данном сайте СП
   * @return bool
   */
  abstract function rounding();

  /**
   * Получить статусы закупок которые следует игнорировать
   * @return array Статусы закупок которые следует игнорировать
   */
  abstract function getPurchaseStatusIgnore();

}