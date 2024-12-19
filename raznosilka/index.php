<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\index.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Точка входа
 */

// Путь к логам
define('LOGS_PATH', $_SERVER['DOCUMENT_ROOT'] . "/logs");

// Обработка ошибок по умолчанию
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php_errors.log');

// Путь к настройкам
define('CONFIG_PATH', $_SERVER['DOCUMENT_ROOT'] . "/config/config.xml");
// Путь к классам
define('CLASSES_PATH', $_SERVER['DOCUMENT_ROOT'] . "/classes");

// Установка дерикторий include_path
set_include_path(CLASSES_PATH . PATH_SEPARATOR . get_include_path());

// Запуск сервсиса
$init = new Initializer();
$init->init();

/**
 * Автоматическая загрузка классов
 * @param string $className Имя класса
 * @return mixed Возвращает результат операции загрузки
 */
function __autoload ($className) {
  $path = explode('_', $className);
  $fileName = array_pop($path);
  $path = implode('/', $path);
  $filePath = strtolower($path) . '/' . $fileName . '.php';
  $filePath = trim($filePath,'/');
  $result = require_once($filePath);
  return $result;
}