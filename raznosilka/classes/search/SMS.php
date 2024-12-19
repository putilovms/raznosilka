<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\SMS.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Search_SMS Дочерний класс Search отвечающий за вывод редактора СМС
 */
class Search_SMS extends Search {

  /**
   * Инициализация поиска
   * @param $cmd array Значения полей фильтра
   * @return bool Результат выполнения инициализации
   */
  function init (array $cmd) {
    // Инициализация значений полей для фильтра поиска
    $this->setFormValue($cmd);
    // Получение найденных СМС для вывода
    $this->getSearchSmsForView();
    return true;
  }

}