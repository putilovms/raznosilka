<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Registry_Request.php>
 * @copyright © М. С. Путилов, 2015
 */

  /**
   * Class Registry_Request отвечает за хранение переменных внутри одного запроса,
   * доступ к классу осуществляется через методы get() и set(), получение
   * объекта Registry_Request осуществляется через статический метод instance()
   */
  class Registry_Request extends Registry {
    /**
     * @var Registry_Request Хранит единственный экземпляр объекта
     */
    private static $instance;
    /**
     * @var array Содержит все переменные реестра
     */
    private $values = array();

    /**
     * Запрет на создание объекта
     */
    private function __construct () {}

    /**
     * Получение единственной копии объекта Registry_Request
     * @return Registry_Request
     */
    static function instance () {
      if (!isset(self::$instance)) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    /**
     * Запись переменной в реестр
     * @param string $key Ключ для записи переменной в реестр
     * @param mixed $val Значение переменной для записи в реестр
     * @param bool $overwrite Разрешение на перезапись переменной
     * @throws Exception
     * @return void
     */
    function set ($key, $val, $overwrite=false) {
      if (isset($this->values[$key]) == true AND $overwrite == false){
        throw new Exception("Попытка перезаписи существующей переменной '{$key}'");
      }
      $this->values[$key] = $val;
    }

    /**
     * Получение записанной переменной из реестра
     * @param string $key Ключ для получения из реестра переменной
     * @throws Exception
     * @return mixed|null Если ключ не найден, то возвращается NULL
     */
    function get ($key) {
      if (isset($this->values[$key])) {
        return $this->values[$key];
      }
      throw new Exception("Попытка получить несуществующую переменную '{$key}'");
      // return null;
    }

    /**
     * Отдаёт все настройки содержащиеся в реестре
     * @return array Все настройки содержащиеся в реестре
     */
    function getAll() {
      return $this->values;
    }
  }