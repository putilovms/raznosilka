<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\URL.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class URL отвечает за генерацию URL
 */
class URL {
  /**
   * Возвращает полный запрашиваемый URL вместе с текущим запросом
   * @param $url string запрашиваемый URL, например 'user/login'
   * @param array $query пользовательский запрос
   * @return string Полный запрашиваемый URL вместе с текущим запросом
   */
  static function to ($url, array $query = array()) {
    $query = (!empty($query)) ? ('?' . http_build_query($query)) : '';
    $result = URL::base() . '/' . trim($url, '/') . $query;
    return $result;
  }

  /**
   * Возвращает полный текущий URL вместе со строкой запроса
   * @return string Полный текущий URL вместе со строкой запроса
   */
  static function current () {
    $result = URL::base() . $_SERVER["REQUEST_URI"];
    return $result;
  }

  /**
   * Возвращает корневой URL
   * @return string Корневой URL
   */
  static function base () {
    $protocol = URL::getProtocol();
    $result = $protocol . "://" . URL::getServerName();
    return $result;
  }

  /**
   * Получить имя сервера (домен на котором находится сервис)
   * @return string Имя сервера
   * @throws Exception
   */
  static function getServerName () {
    if (!isset($_SERVER["SERVER_NAME"])) {
      throw new Exception('Не найдена константа SERVER_NAME');
    }
    if (!isset($_SERVER["SERVER_PORT"])) {
      throw new Exception('Не найдена константа SERVER_PORT');
    }
    $port = ($_SERVER["SERVER_PORT"] == 80 or $_SERVER["SERVER_PORT"] == 443) ? '' : ':' . $_SERVER["SERVER_PORT"];
    $result = $_SERVER["SERVER_NAME"] . $port;
    return $result;
  }

  /**
   * Получить текущий протокол, по которому работает сайт
   * @return string Протокол (http или https)
   */
  static function getProtocol () {
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') {
      $protocol = 'https';
    }
    return $protocol;
  }

}