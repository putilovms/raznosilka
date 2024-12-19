<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Cron.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Cron Плановые функции Разносилки
 */
class Controller_Cron extends Controller  {

  /**
   * Запуск хрона
   */
  function index() {
    $cron = new Cron();
    $cron->run();
  }

}