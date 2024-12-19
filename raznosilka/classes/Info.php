<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Info.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Info Предоставляет различную информацию о приложении
   */
  class Info {
    /**
     * @var int Время создания экземпляра класса, предполагается, что класс будет создан
     * во время старта скрипта, либо какой-либо операции.
     */
    private $startTime = 0;
    /**
     * @var int Время последнего запроса к методу getTimePiece. Служит для отсчёта отрезка
     * времени выполнения какой-либо операции.
     */
    private $lastTime = 0;

    /**
     * Запускает отсчёт времени после создания экземпляра класса.
     */
    function __construct () {
      $this->startTime = microtime(true);
    }

    /**
     * Получить время работы скрипта.
     * @return float Время выполнения скрипта
     */
    function getTimeWork () {
      $microTime = microtime(true);
      $time = $microTime - $this->startTime;
      return round($time, 3);
    }

    /**
     * Получить время работы скрипта в отрезке между запусками данного метода
     * @return float Время выполнения отрезка
     * @throws Exception
     */
    function getTimePiece () {
      $microTime = microtime(true);
      if ($this->lastTime > 0) {
        // Если метод уже запускался
        $time = $microTime - $this->lastTime;
      } else {
        // Если метод ещё не запускался
        $time = $microTime - $this->startTime;
      }
      $this->lastTime = $microTime;
      return round($time, 3);
    }

    /**
     * Использование памяти скриптом в мегабайтах
     * @return float Количество использованной памяти в Мб
     */
    function getMemoryUsage () {
      // Получаем в байтах
      $memory = memory_get_usage();
      // Отдаём в мегабайтах
      $memory = $memory / (1024 * 1024);
      return round($memory, 1);
    }
  }