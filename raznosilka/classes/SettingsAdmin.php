<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Settings.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Settings_User отвечает за настройки пользователя из личного кабинета
 */
class SettingsAdmin {
  /**
   * @var int ID пользователя который изменяет настройки
   */
  protected $id;
  /**
   * @var array Содержит настройки пользователя которые необходимо изменить
   */
  protected $data = array();
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  protected $db;

  /**
   * Конструктор определяет свойство $id и $db
   * @param int $id ID пользователя у которого меняются настройки
   */
  function __construct ($id) {
    $this->id = $id;
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
  }

  /**
   * Добавляет настройку, в свойство $data, которая должна быть изменена. Имя настройки
   * которую необходимо изменть должно совпадать с соответствующим именем поля таблицы user.
   * @param string $name Имя настройки
   * @param mixed $value Значение настройки
   */
  public function setSetting ($name, $value) {
    $this->data[$name] = trim($value);
  }

  /**
   * Устанавливает настройки для пользователя из свойства $data
   * @return bool Результат изменения настроек
   */
  function setSettings () {
    if (!empty($this->data)) {
      foreach ($this->data as $name => $value) {
        $this->db->setSetting($name, $value);
      }
      $this->data = array();
    }
    return true;
  }

  /**
   * Получение настроек сервиса из базы данных
   * @return array Настройки сервиса
   */
  public function getSettings () {
    $settings = $this->db->getAllSettings();
    $result = array();
    foreach ($settings as $setting) {
      $result[$setting[SETTINGS_NAME]] = $setting[SETTINGS_VALUE];
    }
    // var_dump($result);
    return $result;
  }

  /**
   * Очистить кэш
   * @return bool результат операции
   * @throws Exception
   */
  public function delCache () {
    $result = true;
    $dir = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_cache_path');
    if ($objs = glob($dir . "/*")) {
      foreach ($objs as $obj) {
        if (is_dir($obj)) {
          $result = Kit::deleteFiles_r($obj);
        } else {
          if (!@unlink($obj)){
            $result = false;
          }
        }
      }
    }
    return $result;
  }

}