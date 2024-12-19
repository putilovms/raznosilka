<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\update.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Инициализация
 */

define('CONFIG_PATH', $_SERVER['DOCUMENT_ROOT'] . "/config/config.xml"); // Путь к настройкам
define('CLASSES_PATH', $_SERVER['DOCUMENT_ROOT'] . "/classes"); // Путь к классам
define('DATA_PATH', $_SERVER['DOCUMENT_ROOT'] . "/resources/data"); // Путь к ресурсам
define('LOGS_PATH', $_SERVER['DOCUMENT_ROOT'] . "/logs"); // Путь к логам

errorToLog();

// Подключение констант с именами полей таблиц
require_once $_SERVER['DOCUMENT_ROOT'] . "/resources/const.php";
// Подключить вспомогательную библиотеку
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Kit.php";

// Установка дерикторий include_path
set_include_path(CLASSES_PATH . PATH_SEPARATOR . get_include_path());

getDbConfig($dsn, $user, $pass);

// Соединение с БД
$dbOptions = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
$pdo = new PDO($dsn, $user, $pass, $dbOptions);

// Установка кодировки для БД
$db = new DataBase($pdo);
$db->setEncodingDb('UTF8');

/**
 * Обслуживание файловой системы
 */

updateFileSystem();

/**
 * Обслуживание БД
 */

// Создание таблиц, если их нет
createTable($db);

// Обновление схемы существующих таблиц
updateTable($pdo, $db);

// Наполнение таблиц
adminAdd($db); // Добавление админа
fillSpTable($db); // Наполнение таблицы sp
fillSettingsTable($db); // Наполнение таблицы settings

/**
 * Функции
 */

/**
 * Автоматическая загрузка классов
 * @param string $className Имя класса
 * @return mixed Возвращает результат операции загрузки
 */
function __autoload ($className) {
  $path = explode('_', $className);
  $fileName = array_pop($path);
  $path = implode('/', $path);
  $filePath = strtolower($path) . '/' . $fileName . '.php';
  $filePath = trim($filePath, '/');
  $result = require_once($filePath);
  return $result;
}

/**
 * Зпаись ошибок скрипта в лог файл
 */
function errorToLog () {
  // Запись ошибок в лог
  error_reporting(E_ALL);
  ini_set('display_errors', 0);
  ini_set('log_errors', 1);
  if (!file_exists(LOGS_PATH)) {
    throw new Exception("Не найден каталог для сохранения журналов '" . LOGS_PATH . "'");
  }
  ini_set('error_log', LOGS_PATH . '/update_errors.log');
}

/**
 * Возвращает необходимые параметры для соединения с БД
 * @param $dsn string Строка DSN
 * @param $user string Имя пользователя БД
 * @param $pass string Пароль от БД
 * @throws Exception
 */
function getDbConfig (&$dsn, &$user, &$pass) {
  // Получение настроек для подключения к базе данных
  if (!file_exists(CONFIG_PATH)) {
    throw new Exception("Не найден конфигурационный файл '" . CONFIG_PATH . "'");
  }
  $config = simplexml_load_file(CONFIG_PATH);
  if (!($config instanceof \SimpleXMLElement)) {
    throw new Exception("Файл '" . CONFIG_PATH . "' не является конфигурационным файлом");
  }
  $dsn = (string)$config->db->dsn;
  if (empty($dsn)) {
    throw new Exception("В конфигурационном файле не задана переменная dsn в секции db");
  }
  $user = (string)$config->db->user;
  if (empty($user)) {
    throw new Exception("В конфигурационном файле не задана переменная user в секции db");
  }
  $pass = $config->db->pass[0]; // может быть пустым
  if (!isset($pass)) {
    throw new Exception("В конфигурационном файле не задана переменная pass в секции db");
  }
}

/**
 * Создаём таблицы если их нет
 * @param DataBase $db Объект для работы с БД
 */
function createTable (DataBase $db) {
  $db->createTableUsers();
  $db->createTableSp();
  $db->createTableSms();
  $db->createTablePay();
  $db->createTablePurchase();
  $db->createTableUsersPurchase();
  $db->createTableCorrection();
  $db->createTableSmsUnknown();
  $db->createTableMessages();
  $db->createTableDelivery();
  $db->createTableSettings();
  $db->createTableOrders();
  $db->createTableYandexKassa();
  $db->createTableTemplates();
}

/**
 * Добавление админа в БД
 * @param DataBase $db Объект для работы с БД
 */
function adminAdd (DataBase $db) {
  // Добавляем админа, если таблица пользователей пустая
  $user = array();
  $countUsers = $db->getCountRecordsUsers();
  if ($countUsers == 0) {
    $user[USER_LOGIN] = 'admin';
    $user[USER_EMAIL] = 'putilovms@yandex.ru';
    $user[USER_PASSWORD] = '12345';
    $user[USER_REG_DATE] = strftime('%Y-%m-%d %H:%M:%S', time());
    $user[USER_ACTIVATE] = 1;
    $user[SP_ID] = 0; // возможно тут ошибка, кажется должно быть 1
    $user[USER_FILLING_DAY] = 2;
    $user[USER_TIME_ZONE] = 'Europe/Samara';
    $user[USER_REQUEST] = REQUEST_CURL;
    $db->addUser($user);
  }
}

/**
 * Обновление схемы таблиц
 * @param PDO $pdo
 * @param DataBase $db
 */
function updateTable (PDO $pdo, DataBase $db) {
  /*
   * Примеры
   */

  // Добавление к таблице users поля user_blocked
  //  if (!$db->issetField('users', USER_BLOCKED)) {
  //    $sql = "ALTER TABLE  `users` ADD  `" . USER_BLOCKED . "` TINYINT(1) NOT NULL DEFAULT '0'";
  //    $pdo->prepare($sql)->execute();
  //  }

  // Изменение всех номеров карты равные 0 на -1
  //  $sql = "UPDATE  `sms` SET  `sms_card_payer` =  '-1' WHERE  `sms_card_payer` =  '0'";
  //  $pdo->prepare($sql)->execute();

  // Удаление из таблицы users поля user_validate
  //  if ($db->issetField('users', 'user_validate')) {
  //    $sql = "ALTER TABLE  `users` DROP `user_validate`";
  //    $pdo->prepare($sql)->execute();
  //  }

  // Изменение типа поля user_activate
  //  $sql = "ALTER TABLE  `users` CHANGE  `user_activate`  `user_activate` TINYINT(1) NOT NULL DEFAULT '0'";
  //  $pdo->prepare($sql)->execute();

  /*
   * Обновление
   */

  // Удаление из таблицы users поля user_validate
  if ($db->issetField('users', 'user_validate')) {
    $sql = "ALTER TABLE  `users` DROP `user_validate`";
    $pdo->prepare($sql)->execute();
  }

  // Изменение типа поля user_activate
  $sql = "ALTER TABLE  `users` CHANGE  `user_activate`  `user_activate` TINYINT(1) NOT NULL DEFAULT '0'";
  $pdo->prepare($sql)->execute();

  // Изменение типа полей содержащих локальное время
  $sql = "ALTER TABLE  `users` CHANGE  `user_reg_date`  `user_reg_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `users` CHANGE  `user_last_time`  `user_last_time` TIMESTAMP NULL DEFAULT NULL";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `messages` CHANGE  `message_date`  `message_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `yandex_kassa` CHANGE  `paymentDatetime`  `paymentDatetime` TIMESTAMP NULL DEFAULT NULL";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `yandex_kassa` CHANGE  `orderCreatedDatetime`  `orderCreatedDatetime` TIMESTAMP NULL DEFAULT NULL";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `orders` CHANGE  `order_add`  `order_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `orders` CHANGE  `order_run`  `order_run` TIMESTAMP NULL DEFAULT NULL";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `orders` CHANGE  `order_done`  `order_done` TIMESTAMP NULL DEFAULT NULL";
  $pdo->prepare($sql)->execute();
  $sql = "ALTER TABLE  `orders` CHANGE  `order_return`  `order_return` TIMESTAMP NULL DEFAULT NULL";
  $pdo->prepare($sql)->execute();

  // Обновление данных
  $sql = "UPDATE  `users` SET  `user_activate` =  '1'";
  $pdo->prepare($sql)->execute();
  $sql = "UPDATE `users` SET  `user_last_time` = NOW() WHERE `user_last_time` = '0000-00-00 00:00:00'";
  $pdo->prepare($sql)->execute();
  $sql = "UPDATE `yandex_kassa` SET  `paymentDatetime` = NULL WHERE `paymentDatetime` = '0000-00-00 00:00:00'";
  $pdo->prepare($sql)->execute();
  $sql = "UPDATE `yandex_kassa` SET  `orderCreatedDatetime` = NULL WHERE `orderCreatedDatetime` = '0000-00-00 00:00:00'";
  $pdo->prepare($sql)->execute();
  $sql = "UPDATE `orders` SET  `order_run` = NULL WHERE `order_run` = '0000-00-00 00:00:00'";
  $pdo->prepare($sql)->execute();
  $sql = "UPDATE `orders` SET  `order_done` = NULL WHERE `order_done` = '0000-00-00 00:00:00'";
  $pdo->prepare($sql)->execute();
  $sql = "UPDATE `orders` SET  `order_return` = NULL WHERE `order_return` = '0000-00-00 00:00:00'";
  $pdo->prepare($sql)->execute();

  // Добавление к таблице users поля user_tz
  if (!$db->issetField('users', USER_TIME_ZONE)) {
    $sql = "ALTER TABLE  `users` ADD  `" . USER_TIME_ZONE . "` VARCHAR( 50 ) NOT NULL DEFAULT  'Europe/Moscow';";
    $pdo->prepare($sql)->execute();
  }
  // Добавление к таблице sp поля sp_tz
  if (!$db->issetField('sp', SP_TIME_ZONE)) {
    $sql = "ALTER TABLE  `sp` ADD  `" . SP_TIME_ZONE . "` VARCHAR( 50 ) NOT NULL";
    $pdo->prepare($sql)->execute();
  }

  // Добавление к таблице `sms_unknown`  поля `sms_unknown_new`
  if (!$db->issetField('sms_unknown', SMS_UNKNOWN_NEW)) {
    $sql = "ALTER TABLE  `sms_unknown` ADD  `" . SMS_UNKNOWN_NEW . "` TINYINT( 1 ) NOT NULL DEFAULT  '1'";
    $pdo->prepare($sql)->execute();
  }

  // Добавление к таблице `sms_unknown`  поля `sms_unknown_add`
  if (!$db->issetField('sms_unknown', SMS_UNKNOWN_ADD)) {
    $sql = "ALTER TABLE  `sms_unknown` ADD  `" . SMS_UNKNOWN_ADD . "` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ";
    $pdo->prepare($sql)->execute();
  }

  // исправление таблицы  sms_unknown поля sms_unknown_add
  if ($db->issetField('sms_unknown', SMS_UNKNOWN_ADD)) {
    $sql = "UPDATE `sms_unknown` SET `" . SMS_UNKNOWN_ADD . "` = NOW() WHERE `" . SMS_UNKNOWN_ADD . "`= '0000-00-00 00:00:00'";
    $pdo->prepare($sql)->execute();
  }

  // удалить лишние настройки
  $sql = "DELETE FROM `settings` WHERE `settings_name` = 'tpl_sms_path'";
  $pdo->prepare($sql)->execute();

  // Добавление к таблице `sms_unknown`  поля `sms_unknown_add`
  if (!$db->issetField('sms_unknown', SMS_UNKNOWN_ADD)) {
    $sql = "ALTER TABLE  `sms_unknown` ADD  `" . SMS_UNKNOWN_ADD . "` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ";
    $pdo->prepare($sql)->execute();
  }

  // update 2.13

  // Добавление к таблице `users`  поля `user_request`
  if (!$db->issetField('users', USER_REQUEST)) {
    $sql = "ALTER TABLE  `users` ADD  `" . USER_REQUEST . "` SMALLINT(6) NOT NULL DEFAULT '1'";
    $pdo->prepare($sql)->execute();
  }

  // Добавление к таблице `sp`  поля `sp_request`
  if (!$db->issetField('sp', SP_REQUEST)) {
    $sql = "ALTER TABLE  `sp` ADD  `" . SP_REQUEST . "` SMALLINT(6) NOT NULL";
    $pdo->prepare($sql)->execute();
  }

  // update 2.14

  // Добавление к таблице `sp`  поля `sp_active`
  if (!$db->issetField('sp', SP_ACTIVE)) {
    $sql = "ALTER TABLE  `sp` ADD  `" . SP_ACTIVE . "` tinyint(1) NOT NULL";
    $pdo->prepare($sql)->execute();
  }
}

/**
 * Обновление файловой системы
 */
function updateFileSystem () { // todo можно сделать добавление/удаленеи в цикле, и добавлять только пути в массив
  // Удалить ненужный файл
  $path = $_SERVER['DOCUMENT_ROOT'] . '/templates/error/blocked.tpl.php';
  if (file_exists($path)) {
    @unlink($path);
  }
  $path = $_SERVER['DOCUMENT_ROOT'] . '/templates/user/binding.tpl.php';
  if (file_exists($path)) {
    @unlink($path);
  }
  // Создать каталоги
  $path = $_SERVER['DOCUMENT_ROOT'] . '/logs/archive';
  if (!file_exists($path)) {
    mkdir($path, 0755, true);
  }
  // Удалить ненужный каталог с файлами
  $path = $_SERVER['DOCUMENT_ROOT'] . '/resources/templates';
  if (file_exists($path)) {
    Kit::deleteFiles_r($path);
  }

  // update 2.14

  // Удалить дубли шаблонов после переименования
  $path = $_SERVER['DOCUMENT_ROOT'] . '/templates/admin/add.tpl.php';
  if (file_exists($path)) {
    @unlink($path);
  }
  $path = $_SERVER['DOCUMENT_ROOT'] . '/templates/admin/import.tpl.php';
  if (file_exists($path)) {
    @unlink($path);
  }
  // удалить ненужный больше файл с сайтами СП
  $path = $_SERVER['DOCUMENT_ROOT'] . '/resources/data/sp.xml';
  if (file_exists($path)) {
    @unlink($path);
  }
}

/**
 * Заполнение таблицы sp
 * @param DataBase $db
 */
function fillSpTable (DataBase $db) {
  // Добавляем тестовый сайт СП, если таблица сайтов пуста
  $sp = array();
  $countSp = $db->getCountRecordsSp();
  if ($countSp == 0) {
    $db->truncateSpTable(); // сброс счётчика авто инкремента
    $sp[SP_SITE_NAME] = 'test';
    $sp[SP_SITE_URL] = 'https://test.ru';
    $sp[SP_FILLING_DAY] = 2;
    $sp[SP_DESCRIPTION] = 'Тестовый сайт СП';
    $sp[SP_TIME_ZONE] = 'Europe/Samara';
    $sp[SP_REQUEST] = REQUEST_EXTENSIONS;
    $sp[SP_ACTIVE] = 1;
    $db->addSp($sp);
  }
}

/**
 * Заполнение таблицы settings
 * @param DataBase $db
 * @throws Exception
 */
function fillSettingsTable (DataBase $db) {
  $path = DATA_PATH . "/settings.xml";
  if (!file_exists($path)) {
    throw new Exception("Файл с данными для таблицы `settings` '{$path}' не найден");
  }
  $settings = simplexml_load_file($path);
  if (!($settings instanceof \SimpleXMLElement)) {
    throw new Exception("Файл '{$path}' не является xml файлом");
  }
  // Добавление недостающих записей
  foreach ($settings as $element) {
    $item = array();
    /** @var SimpleXMLElement $element */
    foreach ($element->attributes() as $key => $val) $item[$key] = (string)$val;
    $result = $db->getSetting($item[SETTINGS_NAME]);
    // Если настройки нет в базе данных, то добавить её
    if ($result === false) {
      $db->addSetting($item[SETTINGS_NAME], $item[SETTINGS_VALUE]);
    }
  }
}