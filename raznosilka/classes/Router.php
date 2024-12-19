<?php

  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Router.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Router обработка запроса и загрузка соответствующего контролера
   */
  class Router {
    /**
     * Имя контроллера по умолчанию.
     * index
     */
    const DEFAULT_CONTROLLER = "Index";
    /**
     * Имя действия (команды) по умолчанию.
     * index
     */
    const DEFAULT_ACTION = "index";

    /**
     * Загрузка контроллера и метода соотвествующего URL
     * @param string $url
     * @throws Exception
     */
    function findController ($url) {
      // Инициализация
      $logs = new Logs();
      // Анализируем путь
      try {
        // Назначаем значения по умолчанию.
        $controller = self::DEFAULT_CONTROLLER;
        $method = self::DEFAULT_ACTION;
        // Парсим путь
        $parseUrl = parse_url($url);
        $urlPath = $parseUrl['path'];
        if ($urlPath != '/') {
          $urlPath = explode('/', trim($urlPath, ' /'));
          $controller = array_shift($urlPath); // Получили имя контроллера
          $controller = ucfirst(strtolower($controller));
          if (!empty($urlPath)) $method = array_shift($urlPath); // Получили имя действия
          if (!empty($urlPath)) throw new Exception(); // Если у URL больше 2х уровней
        }
        // Имя контроллера
        $className = 'controller_' . $controller;
        // Получаем класс
        $classController = $this->getClass($className);
        if (!$classController) throw new Exception();
        // Проверка наличия метода
        if (!$this->checkMethod($classController, $method)) {
          throw new Exception();
        } else {
          $logs->pathLog($url);
        }
      } catch (Exception $e) {
        // Страница не найдена 404
        $controller = 'Error';
        $method = 'notFound';
        $className = 'controller_' . $controller;
        $classController = $this->getClass($className);
        if (!$classController) {
          throw new Exception('Не удалось получить объект контроллера');
        } else {
          $logs->pathLog($url, 'Ошибка 404. Страница не найдена.');
        }
      }
      // Запуск контроллера
      $this->runController($classController, $method);
    }

    /**
     * Получение объекта контроллера по его имени.
     * Вынесен для тестирования
     * @param string $className Имя класса
     * @return false|object
     */
    function getClass($className) {
      $fileName = CLASSES_PATH . "/" . implode('/', explode('_', $className)) . '.php';
      if (is_readable($fileName)){
        return new $className();
      }
      return false;
    }

    /**
     * Проверяет наличие метода у класса.
     * Вынесен для тестирования
     * @param string $class
     * @param string $method
     * @return bool Результат
     */
    function checkMethod($class, $method){
      return is_callable(array($class, $method));
    }

    /**
     * Запуск контроллера, вынесен для тестирования
     * @param string $class Имя класса
     * @param string $action Имя метода
     */
    function runController($class, $action){
      // Выполняем действие
      $class->$action();
    }

  }