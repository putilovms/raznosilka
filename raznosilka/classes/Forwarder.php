<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Forwarder.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Forwarder Пересылает результаты работы модулей между сбросами POST
   */
  class Forwarder {
    /**
     * @var Registry_Session содержит реестр сессии
     */
    private $regSes;
    /**
     * @var array Содержит передаваемые данные из сессии
     */
    private $buffer;

    /**
     * Конструктор класса
     */
    function __construct () {
      $this->regSes = Registry_Session::instance();
      $this->info = Registry_Request::instance()->get('info');
      $this->readBuffer();
    }

    /**
     * Считывем буфер из сессии и очищаем его, чтобы предотвратить накапливание
     * в буфере несчитанных данных.
     */
    function readBuffer () {
      $this->buffer = $this->regSes->get('forwarder');
      $this->regSes->del('forwarder');
    }

    /**
     * Сохранение данных
     * @param string $module Имя модуля (ключ) данные которого будут сохраняться
     * @param mixed $data Данные которые необходимо сохранить
     */
    function save ($module, $data) {
      $arr[$module] = $data;
      $arr['_time'] = $this->info->getTimeWork();
      $arr['_memory'] = $this->info->getMemoryUsage();
      $this->regSes->set('forwarder', $arr);
    }

    /**
     * Загрузка данных
     * @param string $module Имя модуля (ключ) данные которого будут загружаться
     * @return false|mixed Результат загрузки:
     * - mixed - данные
     * - false - если запрашиваемые данные не обраружены
     */
    function load ($module) {
      if (!empty($this->buffer[$module])) return $this->buffer[$module];
      return false;
    }

    /**
     * Получает данные о времени и памяти которые потребил скрипт
     * @param string $module Имя модуля (ключ) данные которого будут загружаться
     * @return false|array данные о выполнении скрипта
     * - _time - время выполнения
     * - _memory - память
     */
    function getInfo($module){
      if (!empty($this->buffer[$module])){
        $result['_time'] =  $this->buffer['_time'];
        $result['_memory'] =  $this->buffer['_memory'];
        return $result;
      }
      return false;
    }
  }