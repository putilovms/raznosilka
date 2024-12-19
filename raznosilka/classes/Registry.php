<?php

  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Registry.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Registry абстрактный класс реестра
   */
  abstract class Registry {
    /**
     * Абстрактный метод для сохранения значения
     * @param string $key Название переменной для сохранения значения
     * @param mixed $val Значение переменной
     * @param bool $overwrite Разрешение на перезапись переменной
     * @return mixed
     */
    abstract function set ($key, $val, $overwrite=false);

    /**
     * Абстрактный метод для получения значения по ключу
     * @param string $key Название переменной для получения значения
     * @return mixed Возвращает значение переменной
     */
    abstract function get ($key);

  }
