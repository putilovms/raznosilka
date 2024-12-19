<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Registry_Session.php>
 * @copyright © М. С. Путилов, 2015
 */

  /**
   * Class Registry_Session отвечает за хранение переменных между сессиями,
   * доступ к классу осуществляется через методы get() и set(), получение
   * объекта Registry_Session осуществляется через статический метод instance()
   */
  class Registry_Session extends Registry {
    /**
     * @var Registry_Session Хранит единственный экземпляр объекта
     */
    private static $instance;

    /**
     * Запрет на создание объекта
     */
    private function __construct () {
    }

    /**
     * Получение единственной копии объекта Registry_Session
     * @return Registry_Session
     */
    static function instance () {
      if (!isset(self::$instance)) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    /**
     * Запись переменной в сессию
     * @param string $key Ключ для записи переменной в реестр
     * @param mixed $val Значение переменной для записи в реестр
     * @param bool $overwrite Разрешение на перезапись переменной
     * @throws Exception
     * @return void
     */
    function set ($key, $val, $overwrite=false) {
      if (isset($_SESSION[$key]) == true AND $overwrite == false){
        throw new Exception("Попытка перезаписи существующей переменной '\$_SESSION[{$key}]'");
      }
      $_SESSION[$key] = $val;
    }

    /**
     * Получение записанной переменной из сессии
     * @param string $key Ключ для получения из реестра переменной
     * @return mixed|null Если ключ не найден, то возвращается NULL
     */
    function get ($key) {
      if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
      }
      return null;
    }

    /**
     * Удаляет переменную из реестра сессии
     * @param string $key Ключ для удаления переменной из реестра сессий
     * @return bool Результат операции
     */
    function del($key){
      if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
        return true;
      }
      return false;
    }

  }