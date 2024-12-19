<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Initializer.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Initializer отвечает за инициализацию перед запуском
 */
class Initializer {
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  private $db;
  /**
   * @var Registry_Request Доступ к системному реестру
   */
  private $regReq;

  /**
   * Конструктор
   */
  function __construct () {
    $this->regReq = Registry_Request::instance();
  }

  /**
   * Установка режима работы сайта
   * @param string $mode Установка режима работы сервиса
   * - debug - отладка и разработка
   * - normal - рабочий режим
   * - service - обслуживание
   * @throws Exception
   */
  private function setMode ($mode) { //todo возможно необходимо совместить этот метод с инициализацией, т.е инициализировать в зависимости от режима
    // Задаём путь для записи логов
    // ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('logs_path') . '/php_errors.log');
    switch ($mode) {
      case 'service':
        // Записывать ошибки в лог
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        break;
      case 'debug':
        // Отображать ошибки на экране
        ini_set('display_errors', 1);
        ini_set('log_errors', 0);
        break;
      case 'normal':
        // Записывать ошибки в лог
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        break;
      default:
        throw new Exception("Несуществующий режим работы сервиса '{$mode}'");
        break;
    }
  }

  /**
   * Запуск инициализации
   */
  function init () {
    // Стартуем отсчёт времени выполнения скрипта
    $this->regReq->set('info', new Info());
    // Установка локали
    setLocale(LC_ALL, 'ru_RU.CP1251');
    // Стартуем сессию, это единственное место где стартуется сессия
    // (кроме повторного старта после её уничтожения)
    session_start();
    // Настройка кодировки
    mb_internal_encoding('UTF-8');
    // Установка Perl синтаксиса для регулярных выражений
    mb_regex_set_options('z');
    // Отключение генерирование ошибок модулем XML
    libxml_use_internal_errors(true);
    // Подключение констант с именами полей таблиц
    require_once $_SERVER['DOCUMENT_ROOT'] . "/resources/const.php";
    // Получение настроек для подключения к БД
    $this->getConfigFromDb();
    // Инициализация подключения к БД
    $this->initDataBase();
    // Получение настроек из БД
    $this->getSettings();
    // Проверка наличия каталогов
    $this->checkAllDir();
    // Режим работы сайта
    // (это происходит здесь, чтобы была возможность менять режим работы сайта прямо из админки)
    $mode = $this->regReq->get('mode');
    $this->setMode($mode);
    // Инициализация системных сведений об администраторе
    $this->initAdmin();
    // Создание пользователя
    $user = new User();
    $this->regReq->set('user', $user);
    //Запуск роутера
    $router = new Router();
    $router->findController($_SERVER['REQUEST_URI']);
  }

  /**
   * Получение настроек сайта из xml файла, данный метод должен быть запущен первым.
   * В данном методе должно находиться исключительно получение настроек из файла
   * и их проверка, итерпретация должна быть реализована в других методах.
   */
  private function getConfigFromDb () {
    // Получение данных для доступа к базе данных из конфигурационного файла
    if (!file_exists(CONFIG_PATH)) {
      throw new Exception("Не найден конфигурационный файл '" . CONFIG_PATH . "'");
    }
    $config = simplexml_load_file(CONFIG_PATH);
    if (!($config instanceof \SimpleXMLElement)) {
      throw new Exception("Файл '" . CONFIG_PATH . "' не является конфигурационным файлом");
    }
    // dsn
    $data = (string)$config->db->dsn;
    if (empty($data)) {
      throw new Exception("В конфигурационном файле не задана переменная dsn в секции db");
    }
    $this->regReq->set('db_dsn', $data);
    // user
    $data = (string)$config->db->user;
    if (empty($data)) {
      throw new Exception("В конфигурационном файле не задана переменная user в секции db");
    }
    $this->regReq->set('db_user', $data);
    // pass
    $data = $config->db->pass[0]; // может быть пустым
    if (!isset($data)) {
      throw new Exception("В конфигурационном файле не задана переменная pass в секции db");
    }
    $this->regReq->set('db_pass', (string)$data);
  }

  /**
   * Инициализирует базу данных, создавая соединение и таблицы если их нет
   */
  private function initDataBase () {
    // Соединение с БД
    $dbOptions = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
    $db = new PDO($this->regReq->get('db_dsn'), $this->regReq->get('db_user'), $this->regReq->get('db_pass'), $dbOptions);
    $this->regReq->set('db', $db);
    // Установка кодировки для БД
    $this->db = new DataBase($db);
    $this->db->setEncodingDb('UTF8');
  }

  /**
   * Проверяет наличие администратора в БД и получает данные о нём
   * @throws Exception В случае если администратор не найден
   */
  private function initAdmin () {
    $adminId = 1;
    $admin = $this->db->getUserById($adminId);
    if (is_null($admin)) {
      throw new Exception('Администратор сайта не найден');
    }
    $this->regReq->set('email_admin', $admin[USER_EMAIL]);
  }

  /**
   * Получение настроек из базы данных
   * @throws Exception
   */
  private function getSettings () {
    $settings = $this->db->getAllSettings();
    if (empty($settings)) {
      throw new Exception("Не удалось получить настройки из базы данных, запустить update.php");
    }
    foreach ($settings as $setting) {
      $this->regReq->set($setting[SETTINGS_NAME], $setting[SETTINGS_VALUE]); // todo добавить константы для получения настроек
    }
  }

  /**
   * Проверить наличие необходимых каталогов для правильной работы сервиса.
   * Каталоги не создаются автоматически, так как требуют добавления файла .htaccess, ограничивающего доступ
   * @throws Exception В случае если каталог не найден
   */
  private function checkAllDir () {
    // Получение списка каталогов
    $list = array();
    $list[] = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tpl_path');
    $list[] = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tpl_mail_path');
    $list[] = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('layer_path');
    $list[] = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_path');
    $list[] = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_cache_path');
    foreach ($list as $dir) {
      if (!file_exists($dir)) {
        throw new Exception("Каталог '{$dir}' не найден");
      }
    }
  }
}