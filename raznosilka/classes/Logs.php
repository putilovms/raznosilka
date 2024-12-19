<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\log.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Log предназначен для ведения логов
 */
class Logs {
  /**
   * Количество строк хранящихся в логе
   */
  const COUNT_LINE = 5000;
  /**
   * @var string Путь к папке логов
   */
  private $logsPath;
  /**
   * @var string Имя файла для журнала действий пользователя
   */
  private $actionLogName;
  /**
   * @var string Имя файла для журнала ошибок PHP
   */
  private $phpErrorLogName;
  /**
   * @var string Имя файла для журнала запросов к сайтам СП
   */
  private $requestLogName;
  /**
   * @var string Имя файла для журнала рассылки писем
   */
  private $mailLogName;
  /**
   * @var string Имя файла для журнала работы хрона
   */
  private $cronLogName;
  /**
   * @var string Имя файла для журнала путей
   */
  private $pathLogName;
  /**
   * @var string Имя файла для журнала платёжной системы
   */
  private $paymentLogName;
  /**
   * @var string Имя файла для журнала скрипта update.php
   */
  private $updateLogName;

  /**
   * Конструктор класса
   */
  function __construct () {
    // Инициализация имён журналов
    $this->logsPath = LOGS_PATH;
    $this->actionLogName = 'action.csv';
    $this->phpErrorLogName = 'php_errors.log';
    $this->requestLogName = 'request.csv';
    $this->mailLogName = 'mail.csv';
    $this->cronLogName = 'cron.csv';
    $this->pathLogName = 'path.csv';
    $this->paymentLogName = 'payment.csv';
    $this->updateLogName = 'update_errors.log';
  }

  /**
   * Добавить и сохранить информацию в журнал
   * @param $name string Имя файла журнала
   * @param $line array Данные для сохранения
   */
  function save ($name, array $line) {
    $logName = $this->logsPath . '/' . $name;
    // Подгортовка нового файла
    $this->createLogFile($logName);
    //$this->rotateCycleLogFile($logName);
    // Запись в лог файл
    $this->putCsvLogFile($logName, $line);
  }

  /**
   * Метод логирующий информацию о вхождении пользователя в систему
   * @param array $user Массив с информацией о текущем пользователе
   * @param $action string Действие пользователя
   */
  function actionLog (array $user, $action) {
    // Извелечение информации для логирования
    $log['date'] = date('Y-m-d H:i:s');
    $log[USER_ID] = isset($user[USER_ID]) ? $user[USER_ID] : '—';
    $log[USER_LOGIN] = isset($user[USER_LOGIN]) ? $user[USER_LOGIN] : '—';
    $log['user_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '—';
    $log['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '—';
    $log['action'] = $action;
    $this->save($this->actionLogName, $log);
  }

  /**
   * Метод логирующий информацию о запросах к сайтам СП
   * @param $url string URL адрес запроса
   * @param $cmd string Команда к сайту СП
   * @param $type string Тип запроса
   */
  function requestLog ($url, $cmd, $type) {
    $log['date'] = date('Y-m-d H:i:s');
    // Данные о пользователе
    /** @var User $userObj */
    $userObj = Registry_Request::instance()->get('user');
    $user = $userObj->getUserInfo();
    $log[USER_ID] = isset($user[USER_ID]) ? $user[USER_ID] : '—';
    $log[USER_LOGIN] = isset($user[USER_LOGIN]) ? $user[USER_LOGIN] : '—';
    $log['user_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '—';
    $log['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '—';
    // Данные о запросе
    $parseURL = parse_url($url);
    $log['host'] = $parseURL['host'];
    $log['path'] = isset($parseURL['path']) ? $parseURL['path'] : '—';
    $query = isset($parseURL['query']) ? $parseURL['query'] : '—';
    $log['query'] = !empty($cmd) ? $cmd : $query;
    $log['type'] = $type;
    $this->save($this->requestLogName, $log);
  }

  /**
   * Метод логирующий информацию об отправки письма
   * @param $to string
   * @param $from string
   * @param $subject string
   */
  function mailLog ($to, $from, $subject) {
    $log['date'] = date('Y-m-d H:i:s');
    $log['to'] = $to;
    $log['from'] = $from;
    $log['subject'] = $subject;
    $this->save($this->mailLogName, $log);
  }

  /**
   * Метод логирующий работу хрона
   * @param string $action Действие хрона
   */
  function cronLog ($action) {
    $log['date'] = date('Y-m-d H:i:s');
    $log['action'] = $action;
    $this->save($this->cronLogName, $log);
  }

  /**
   * Метод логирующий работу платёжной системы
   * @param string $action Действие платёжной системы
   */
  function paymentLog ($action) {
    $log['date'] = date('Y-m-d H:i:s');
    $log['action'] = $action;
    $this->save($this->paymentLogName, $log);
  }

  /**
   * Метод логирующий пути перемещения пользователей
   * @param string $path Запрашиваемый URL
   * @param string $error Ошибка, если есть (например 404 и т.д.)
   * @throws Exception
   */
  function pathLog ($path, $error = '') {
    $log['date'] = date('Y-m-d H:i:s');
    // Данные о пользователе
    /** @var User $userObj */
    $userObj = Registry_Request::instance()->get('user');
    $user = $userObj->getUserInfo();
    $log[USER_ID] = isset($user[USER_ID]) ? $user[USER_ID] : '—';
    $log[USER_LOGIN] = isset($user[USER_LOGIN]) ? $user[USER_LOGIN] : '—';
    $log['user_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '—';
    $log['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '—';
    $log['path'] = $path;
    $log['error'] = !empty($error) ? $error : '—';
    $this->save($this->pathLogName, $log);
  }

  /**
   * Создаёт новй файл для записи логов с BOM для корректного отображения
   * UTF-8 кодировки в Excel
   * @param string $fileName Путь к лог-файлу
   */
  function createLogFile ($fileName) {
    if (!file_exists($fileName)) {
      $fp = fopen($fileName, 'w');
      // fputs($fp, "\xEF\xBB\xBF");
      fclose($fp);
    }
  }

  /**
   * Обеспечивает ротацию данных в лог-файле.
   * Удаляет верхние (наиболее старые) строки, когда лог-файл переполнен.
   * @param string $logName Путь к лог-файлу
   */
  function rotateCycleLogFile ($logName) {
    $lines = file($logName);
    while (count($lines) >= self::COUNT_LINE) array_shift($lines);
    file_put_contents($logName, $lines);
  }

  /**
   * Запись в лог-файл
   * @param string $logName Путь к лог-файлу
   * @param array $log Информация для записи в лог-файл
   */
  function putCsvLogFile ($logName, $log) {
    $fp = fopen($logName, 'a');
    fputcsv($fp, $log, ';');
    fclose($fp);
  }

  /**
   * Подгатавливает список логов для вывода
   * @return array Массив с даннвми для вывода, формата:
   *  [x] - журнал
   *    ['file_name'] - имя файла с журналом
   *    ['description'] - описание журнала
   *    ['page_url'] - путь к странице для вывода содержания журнала
   *    ['lines'] - Количество строк в журнале
   *    ['modify'] - Время последнего изменения журнала
   */
  public function getListLogs () {
    // Инициализация
    $result = array();
    // Данные о журналах
    $logs = array(
      0 => array(
        'file_name' => $this->actionLogName,
        'description' => 'Журнал действий пользователей',
        'page_url' => 'action'
      ),
      1 => array(
        'file_name' => $this->phpErrorLogName,
        'description' => 'Журнал ошибок PHP',
        'page_url' => 'error'
      ),
      2 => array(
        'file_name' => $this->requestLogName,
        'description' => 'Журнал запросов к сайтам СП',
        'page_url' => 'request'
      ),
      3 => array(
        'file_name' => $this->mailLogName,
        'description' => 'Журнал отправки почты',
        'page_url' => 'mail'
      ),
      4 => array(
        'file_name' => $this->cronLogName,
        'description' => 'Журнал работы хрона',
        'page_url' => 'cron'
      ),
      5 => array(
        'file_name' => $this->pathLogName,
        'description' => 'Журнал URL',
        'page_url' => 'path'
      ),
      6 => array(
        'file_name' => $this->paymentLogName,
        'description' => 'Журнал платёжной системы',
        'page_url' => 'payment'
      ),
      7 => array(
        'file_name' => $this->updateLogName,
        'description' => 'Журнал обновления сервиса',
        'page_url' => 'update'
      ),
    );
    // Подготовка данных для вывода
    foreach ($logs as $log) {
      $view['file_name'] = $log['file_name'];
      $view['description'] = $log['description'];
      $view['page_url'] = URL::to('reports/' . $log['page_url']);
      $filePath = $this->logsPath . '/' . $log['file_name'];
      $view['lines'] = Kit::getCountLinesToFile($filePath);
      $time = Kit::getTimeFileModify($filePath);
      $view['modify'] = ($time !== false) ? strftime('%H:%M %d.%m.%Y', $time) : '—';
      $size = $time = Kit::getSizeFile($filePath);
      $view['size'] = ($size !== false) ? (number_format($size / (1024), 1, ',', ' ') . ' Кб') : '—';
      $result[] = $view;
    }
    return $result;
  }

  /**
   * Подготовить массив для вывода журнала действий пользователя
   * @return array Массив с данными для вывода журнала действий пользователя
   */
  function getActionLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->actionLogName;
    if (file_exists($filePath)) {
      $handle = fopen($filePath, "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $item = array();
        $item['time'] = strftime('%H:%M:%S %d.%m.%Y', strtotime($data[0]));
        $item['uid'] = $data[1];
        $item['login'] = $data[2];
        $item['ip'] = $data[3];
        $item['browser'] = $data[4];
        $item['action'] = $data[5];
        $result[] = $item;
      }
      fclose($handle);
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Подготовить массив для вывода журнала ошибок PHP
   * @return array Массив с данными для вывода журнала ошибок PHP
   */
  function getPhpErrorLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->phpErrorLogName;
    if (file_exists($filePath)) {
      $file = file($filePath);
      if (!empty($file)) {
        foreach ($file as $str) {
          $item = array();
          $item['error'] = $str;
          $result[] = $item;
        }
      }
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Подготовить массив для вывода журнала ошибок скрипта update.php
   * @return array Массив с данными для вывода журнала ошибок скрипта update.php
   */
  function getUpdateErrorLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->updateLogName;
    if (file_exists($filePath)) {
      $file = file($filePath);
      if (!empty($file)) {
        foreach ($file as $str) {
          $item = array();
          $item['error'] = $str;
          $result[] = $item;
        }
      }
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Подготовить массив для вывода журнала рассылки почты
   * @return array Массив с данными для вывода журнала рассылки почты
   */
  public function getMailLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->mailLogName;
    if (file_exists($filePath)) {
      $handle = fopen($filePath, "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $item = array();
        $item['time'] = strftime('%H:%M:%S %d.%m.%Y', strtotime($data[0]));
        $item['to'] = $data[1];
        $item['from'] = $data[2];
        $item['subject'] = $data[3];
        $result[] = $item;
      }
      fclose($handle);
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Подготовить массив для вывода журнала запросов к сайтам СП
   * @return array Массив с данными для вывода журнала ошибок сервиса
   */
  function getRequestLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->requestLogName;
    if (file_exists($filePath)) {
      $handle = fopen($filePath, "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $item = array();
        $item['time'] = strftime('%H:%M:%S %d.%m.%Y', strtotime($data[0]));
        $item['uid'] = $data[1];
        $item['login'] = $data[2];
        $item['ip'] = $data[3];
        $item['browser'] = $data[4];
        $item['host'] = $data[5];
        $item['path'] = $data[6];
        $item['query'] = $data[7];
        $item['type'] = $data[8];
        $result[] = $item;
      }
      fclose($handle);
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Вывод журнала работы хрона
   * @return array Массив с данными для вывода журнала работы хрона
   */
  public function getCronLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->cronLogName;
    if (file_exists($filePath)) {
      $handle = fopen($filePath, "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $item = array();
        $item['time'] = strftime('%H:%M:%S %d.%m.%Y', strtotime($data[0]));
        $item['action'] = $data[1];
        $result[] = $item;
      }
      fclose($handle);
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Вывод журнала работы платёжной системы
   * @return array Массив с данными для вывода журнала платёжной системы
   */
  public function getPaymentLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->paymentLogName;
    if (file_exists($filePath)) {
      $handle = fopen($filePath, "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $item = array();
        $item['time'] = strftime('%H:%M:%S %d.%m.%Y', strtotime($data[0]));
        $item['action'] = htmlspecialchars($data[1]);
        $result[] = $item;
      }
      fclose($handle);
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Вывод журнала путей
   * @return array Массив с данными для вывода журнала путей
   */
  public function getPathLogForView () {
    $result = array();
    $filePath = $this->logsPath . '/' . $this->pathLogName;
    if (file_exists($filePath)) {
      $handle = fopen($filePath, "r"); // todo нет проверки на ошибку
      while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $item = array();
        $item['time'] = strftime('%H:%M:%S %d.%m.%Y', strtotime($data[0]));
        $item['uid'] = $data[1];
        $item['login'] = $data[2];
        $item['ip'] = $data[3];
        $item['browser'] = $data[4];
        $item['path'] = $data[5];
        $item['error'] = $data[6];
        $result[] = $item;
      }
      fclose($handle);
    }
    $result = array_reverse($result, true);
    return $result;
  }

  /**
   * Удалить файл с журналом
   * @param $arg string Команда с именем журнала
   * @return bool Результат удаления журнала
   */
  public function delLog ($arg) {
    $result = false;
    switch ($arg) {
      case 'action' :
        $result = @unlink($this->logsPath . '/' . $this->actionLogName);
        break;
      case 'error' :
        $result = @unlink($this->logsPath . '/' . $this->phpErrorLogName);
        break;
      case 'request' :
        $result = @unlink($this->logsPath . '/' . $this->requestLogName);
        break;
      case 'mail' :
        $result = @unlink($this->logsPath . '/' . $this->mailLogName);
        break;
      case 'cron' :
        $result = @unlink($this->logsPath . '/' . $this->cronLogName);
        break;
      case 'path' :
        $result = @unlink($this->logsPath . '/' . $this->pathLogName);
        break;
      case 'payment' :
        $result = @unlink($this->logsPath . '/' . $this->paymentLogName);
        break;
      case 'update' :
        $result = @unlink($this->logsPath . '/' . $this->updateLogName);
        break;
    }
    return $result;
  }

  /**
   * Удалить все журналы
   * @return bool Результат удаления журналов
   */
  public function delAllLogs () {
    $result = true;
    if ($objs = glob($this->logsPath . "/*")) {
      foreach ($objs as $obj) {
        if (is_file($obj)) {
          if (!unlink($obj)) {
            $result = false;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Заархивировать все журналы
   */
  function archive () {
    // Проверяем наличие расширения ZIP
    if (extension_loaded('zip')) {
      // Создаём архив
      $zip = new ZipArchive();
      $zipName = $this->logsPath . '/archive/' . "logs_" . date('dmy') . ".zip";
      if ($zip->open($zipName, ZIPARCHIVE::OVERWRITE) === TRUE) {
        // Перебираем все файлы в каталоге с журналами
        if ($objs = glob($this->logsPath . "/*")) {
          foreach ($objs as $obj) {
            if (is_file($obj)) {
              // добавляем файлы в zip архив
              $zip->addFile($obj, basename($obj));
            }
          }
        }
        $zip->close();
      } else {
        trigger_error('Ошибка. Не удалось создать архив ZIP.');
      }
    } else {
      trigger_error('Ошибка. Расширение ZIP для PHP не установлено.');
    }
  }

}