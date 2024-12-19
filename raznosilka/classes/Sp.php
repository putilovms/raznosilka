<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Sp.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Sp Обработка списка с сайтами закупок
 */
class Sp {
  /**
   * @var DataBase База данных
   */
  private $db;

  /**
   * Конструктор класса
   */
  function __construct () {
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
  }

  /**
   * Получить список доступных сайтов СП для вывода
   * @return array Массив для вывода
   */
  public function getSpList () {
    $result = $this->db->getActiveSP();
    return $result;
  }

  /**
   * Получить список закупок для вывода редактора закупок
   */
  function getViewEditorSpList () { // todo описание отдаваемого массива
    $result = array();
    $list = $this->db->getAllSP();
    // Подготовка информации для вывода
    if (!empty($list)) {
      foreach ($list as $sp) {
        if ($sp[SP_ACTIVE]) {
          $sp['class'] = 'normal';
        } else {
          $sp['class'] = 'inactive';
        }
        $query = array('id' => $sp[SP_ID]);
        $url = URL::to('admin/delete_sp', $query);
        $sp['url'] = $url;
        $result['sp'][] = $sp;
      }
    }
    // Получение списка временных зон
    $timeZoneHelper = new TimeZoneHelper();
    $result['time_zones'] = $timeZoneHelper->getTimeZoneListForView();
    $result['request_list'] = Sp::getSpRequestListForView();
    return $result;
  }

  /**
   * Получить список запросов к сайтам СП для вывода
   * @return array Массив для вывода типов запросов к сайтам СП, формата:
   *  - ключ - int тип запроса
   *  - значение - string название типа запроса
   */
  static function getSpRequestListForView () {
    $requestList = array(
      REQUEST_CURL => 'Прямой запрос',
      REQUEST_EXTENSIONS => 'Через расширение',
    );
    return $requestList;
  }

  /**
   * Изменить информацию о сайте СП
   * @param array $spArr Массив с новой информацией о сайте СП
   * @return bool Результат изменения
   */
  public function editSp (array $spArr) {
    // Инициализация
    $result = false;
    $spArr[SP_ID] = (int)$spArr[SP_ID];
    $spArr[SP_FILLING_DAY] = (int)$spArr[SP_FILLING_DAY];
    $spArr[SP_REQUEST] = (int)$spArr[SP_REQUEST];
    $spArr[SP_ACTIVE] = (int)$spArr[SP_ACTIVE];
    // Проверка входящих данных
    if (!empty($spArr[SP_ID]) and !empty($spArr[SP_SITE_NAME]) and !empty($spArr[SP_SITE_URL]) and !empty($spArr[SP_DESCRIPTION]) and !empty($spArr[SP_TIME_ZONE]) and !empty($spArr[SP_REQUEST])) {
      $result = $this->db->editSp($spArr);
    }
    return $result;
  }

  /**
   * Экспорт списка сайтов СП
   */
  public function exportSp () {
    // Получение всех шаблонов из БД
    $spList = $this->db->getAllSP();
    // Подготовка файла для экспорта
    $tmpPath = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_path');
    $fileName = 'sp_' . date('dmy') . '.csv';
    $filePath = $tmpPath . '/' . $fileName;
    $file = fopen($filePath, 'w');
    // Создание CSV
    foreach ($spList as $sp) {
      fputcsv($file, $sp, ";");
    }
    fclose($file);
    // Передача CSV через браузер
    Kit::fileToBrowser($filePath);
  }

  /**
   * Импортировать списка сайтов СП
   * @param $file array Данные о загружаемом файле из $_FILES
   * @return bool Результат операции
   */
  public function importSp ($file) {
    // Сохраняем файл
    $tmpPath = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_path');
    $tmpName = $file['tmp_name'];
    $saveName = $tmpPath . '/' . md5($tmpName) . ".tmp";
    $result = move_uploaded_file($tmpName, $saveName);
    if (!$result) {
      $controller = new Controller_Error();
      $controller->index(__LINE__, __FILE__);
    }
    // Получаем шаблоны из файла
    $spList = array();
    $handle = fopen($saveName, "r"); // todo нет проверки на ошибку
    while (($data = fgetcsv($handle, 0, ";")) !== false) {
      // Проверка на количество столбцов'
      if (count($data) === 8) {
        $spList[] = array(
          SP_ID => (int)$data[0],
          SP_SITE_NAME => $data[1],
          SP_SITE_URL => $data[2],
          SP_FILLING_DAY => (int)$data[3],
          SP_DESCRIPTION => $data[4],
          SP_TIME_ZONE => $data[5],
          SP_REQUEST => (int)$data[6],
          SP_ACTIVE => (int)$data[7],
        );
      } else {
        $result = false;
        break;
      }
    }
    if ($result) {
      // Очистка таблицы с шаблонами СМС
      $result = $this->db->truncateSpTable();
      // Сохранение шаблонов СМС
      if ($result) {
        $result = $this->db->importSpList($spList);
      }
    }
    fclose($handle);
    @unlink($saveName);
    return $result;
  }

  /**
   * Получить данные для добавления сайта СП
   * @return array Данные для добавления сайта СП
   */
  public function getViewAdd () {
    $result = array();
    // Получение списка временных зон
    $timeZoneHelper = new TimeZoneHelper();
    $result['time_zones'] = $timeZoneHelper->getTimeZoneListForView();
    $result['request_list'] = Sp::getSpRequestListForView();
    return $result;
  }

  /**
   * Добавить новый сайт СП
   * @param $spArr array Информация о новом сайте СП
   * @return bool Резульат добавления
   */
  public function addSp (array $spArr) {
    // Инициализация
    $result = false;
    $spArr[SP_FILLING_DAY] = (int)$spArr[SP_FILLING_DAY];
    $spArr[SP_REQUEST] = (int)$spArr[SP_REQUEST];
    $spArr[SP_ACTIVE] = (int)$spArr[SP_ACTIVE];
    // Проверка входящих данных
    if (!empty($spArr[SP_SITE_NAME]) and !empty($spArr[SP_SITE_URL]) and !empty($spArr[SP_DESCRIPTION]) and !empty($spArr[SP_TIME_ZONE]) and !empty($spArr[SP_REQUEST])) {
      $result = $this->db->addSp($spArr);
    }
    return $result;
  }

  /**
   * Удалить сайт СП
   * @param $id int ID сайта СП
   * @return bool Результат операции
   */
  public function deleteSp ($id) {
    $result = false;
    $id = (int)$id;
    if (!empty($id)) {
      $result = $this->db->deleteSp($id);
    }
    return $result;
  }

}
