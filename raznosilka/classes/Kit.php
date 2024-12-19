<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Kit.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Kit Содержит вспомогательные статические функции для сокращения кода
 */
class Kit {
  /**
   * Переводит строку из UTF-8 в WINDOWS-1251 кодировку
   * @param string $string Исходная строка в UTF-8 кодировке
   * @return string Строка в WINDOWS-1251 кодировке
   */
  static function UW ($string) {
    return iconv('UTF-8', 'WINDOWS-1251', $string);
  }

  /**
   * Переводит строку из WINDOWS-1251 в UTF-8 кодировку
   * @param string $string Исходная строка в WINDOWS-1251 кодировке
   * @return string Строка в UTF-8 кодировке
   */
  static function WU ($string) {
    return iconv('WINDOWS-1251', 'UTF-8', $string);
  }

  /**
   * Переводит массив строк из WINDOWS-1251 в UTF-8 кодировку
   * @param array $arr Массив строк в кодировке WINDOWS-1251
   * @return array Массив строк в кодировке UTF-8
   */
  static function arrWU ($arr) {
    $result = array();
    foreach ($arr as $value) {
      $result[] = iconv('WINDOWS-1251', 'UTF-8', $value);
    }
    return $result;
  }

  /**
   * Переводит массив строк из UTF-8 в WINDOWS-1251 кодировку
   * @param array $arr Массив строк в кодировке UTF-8
   * @return array Массив строк в кодировке WINDOWS-1251
   */
  static function arrUW ($arr) {
    $result = array();
    foreach ($arr as $value) {
      $result[] = iconv('UTF-8', 'WINDOWS-1251', $value);
    }
    return $result;
  }

  /**
   * Вспомогательная функция, которая вырезает из начала строки заданное количество
   * симоволов и возвращает вырезанный кусок. Строка передаётся по ссылке, поэтому
   * она так же изменяется. Работает с однобайтными символами (WINDOWS-1251).
   * @param string $string Исходная строка, передаётся по ссылке
   * @param int $countChar Количество символов
   * @return string Вырезанная часть из начала исходной строки
   */
  static function textСut (&$string, $countChar) {
    $result = substr($string, 0, $countChar);
    $string = substr($string, $countChar);
    return $result;
  }

  /**
   * Удаляет из строки HTML тегов, переносов и лишних пробелов
   * @param $str string Строка
   * @return string обработанная строка
   */
  static function plainText ($str) {
    // Удаляем все HTML теги
    $str = strip_tags($str);
    // Заменяем спецсимоволы пробелами
    $pattern = array("\r\n", "\r", "\n", "\t");
    $str = str_replace($pattern, ' ', $str);
    // Удаляем двойные пробелы
    $pattern = '/\s\s+/';
    $replacement = ' ';
    $str = preg_replace($pattern, $replacement, $str);
    // Очищаем начало и конец строки от пробелов
    $str = trim($str);
    return $str;
  }

  /**
   * Является ли строка целым числом
   * @param $value string Строка с числом
   * @param bool $sign - Является ли это число со знаком (отрицательным)
   * @return bool True если в строке целое число
   */
  static function isInt ($value, $sign = false) {
    $result = false;
    $pattern = ($sign) ? '#^(|\-)\d+$#' : '#^\d+$#';
    if (preg_match($pattern, $value)) {
      $result = true;
    }
    return $result;
  }

  /**
   * Получить адрес с которого пришёл пользователь
   * @return string Адрес с которого пришёл пользователь
   */
  static function getRefererURL () {
    if (isset($_SERVER['HTTP_REFERER'])) {
      $parseURL = parse_url($_SERVER['HTTP_REFERER']);
      $path = $parseURL['path'];
      if (isset($parseURL['query'])) {
        parse_str($parseURL['query'], $query);
      } else {
        $query = array();
      }
      $url = URL::to($path, $query);
      return $url;
    }
    return false;
  }

  /**
   * Получить количество строк в файле
   * @param $path string Путь к файлу
   * @return int Количество втрок в файле
   */
  static function getCountLinesToFile ($path) {
    if (!file_exists($path)) {
      return 0;
    }
    $fileArr = file($path);
    $lines = count($fileArr);
    return $lines;
  }

  /**
   * Получить дату последней модификации файла
   * @param $path string Путь к файлу
   * @return bool|int Время последней модификации (формат UNIX)
   */
  static function getTimeFileModify ($path) {
    $result = false;
    if (file_exists($path)) {
      $result = filemtime($path);
    }
    return $result;
  }

  /**
   * Получить размер файла в байтах
   * @param $path string Путь к файлу
   * @return bool|int Размер файла в байтах
   */
  public static function getSizeFile ($path) {
    $result = false;
    if (file_exists($path)) {
      $result = filesize($path);
    }
    return $result;
  }

  /**
   * Получить разницу между сегодняшней и заданной датой
   * @param $date int Дата до которой идёт отсчёт, в Unix формате
   * @return string Разница между сегодняшней и заданной датой
   */
  static function DateDiff ($date) {
    // Дата до которой считается разница
    $dateTo = new DateTime();
    $dateTo->setTimestamp($date);
    // Дата от которой считается разница
    $dateNow = new DateTime();
    $interval = $dateNow->diff($dateTo);
    $result = $interval->format('%r%a');
    return $result;
  }

  /**
   * Перевести массив в строку рекурсивно
   * @param array $array Исходный массив
   * @return string Массив в виде строки
   */
  static function arrayToString_r (array $array) {
    static $result = '';
    foreach ($array as $key => $val) {
      if (is_array($val)) {
        $result .= "{$key} = [";
        Kit::arrayToString_r($val);
        $result .= "], ";
      } else {
        $result .= "{$key} = {$val}, ";
      }
    }
    $result = rtrim($result, ', ');
    return $result;
  }

  /**
   * Удалить из строки все символы кроме букв
   * @param $str string Исходная строка
   * @return string Очищенная строка
   */
  static function onlyLetters ($str) {
    // Удаляем из строки все лишние символы
    $pattern = '/[^А-Яа-яЁё]/';
    $replacement = ' ';
    $str = preg_replace($pattern, $replacement, $str);
    // Удаляем двойные пробелы
    $pattern = '/\s\s+/';
    $replacement = ' ';
    $str = preg_replace($pattern, $replacement, $str);
    // Очищаем начало и конец строки от пробелов
    $str = trim($str);
    return $str;
  }

  /**
   * Получить массив слов из строки
   * @param $str string Исходная строка
   * @return array Массив слов
   */
  static function strToWordArr ($str) {
    $math = array();
    if (!empty($str)) {
      $str = Kit::UW($str);
      $pattern = Kit::UW('#([А-Яа-яЁё]+)#');
      preg_match_all($pattern, $str, $math);
      if (!empty($math[0])) {
        $math = Kit::arrWU($math[0]);
      }
    }
    return $math;
  }

  /**
   * Получить массив первых букв слов из строки
   * @param $str string Исходная строка
   * @return array Массив первых букв слов из строки
   */
  static function strToCharArr ($str) {
    $math = array();
    if (!empty($str)) {
      $str = Kit::UW($str);
      $pattern = Kit::UW('#([А-Яа-яЁё]{1})[А-Яа-яЁё]*#');
      preg_match_all($pattern, $str, $math);
      if (!empty($math[1])) {
        $math = Kit::arrWU($math[1]);
      }
    }
    return $math;
  }

  /**
   * Рекурсивно удалить все папку с вложенными файлами и каталогами
   * @param $dir string Путь к каталогу, который необходимо удалить
   * @return bool Результат операции
   */
  static function deleteFiles_r ($dir) {
    static $result = true;
    rtrim($dir, '\/');
    $files = glob($dir . '/*', GLOB_MARK);
    foreach ($files as $file) {
      if (is_dir($file)) {
        $result = self::deleteFiles_r($file);
      } else {
        if (!@unlink($file)) {
          $result = false;
        }
      }
    };
    if (!@rmdir($dir)) {
      $result = false;
    }
    return $result;
  }

  /**
   * Убрать повторяющиеся значения из двумерного массива
   * @param $array array Массив из которого нужно убрать повторяющиеся значения
   * @return array Массив из которого убраны повторяющиеся значения
   */
  static function array_unique_r (array $array) {
    // Сериализуем массив
    $array = array_map("serialize", $array);
    // Удаляем повторяющиеся значения
    $array = array_unique($array);
    // Восстанавливаем массив
    $result = array_map("unserialize", $array);
    return $result;
  }

  /**
   * Возвращает значения массива A отсутствующие в миссиве B (проверка выполняется только по ключам)
   * @param array $arrA Исходный массив
   * @param array $arrB Массив, с которым идет сравнение
   * @return array Массив содержащий элементы массива A, отсутствующие в миссиве B
   */
  static function array_diff_r (array $arrA, array $arrB) {
    $result = array();
    // Находим отсутствующие ключи в массиве B
    foreach ($arrA as $key => $value) {
      if (!array_key_exists($key, $arrB)) {
        $result[$key] = $value;
      }
    }
    return $result;
  }

  /**
   * Передать файл для скачивания через браузер
   * @param $file string имя файла который надо передать пользователю
   */
  static function fileToBrowser ($file) {
    if (file_exists($file)) {
      // сбрасываем буфер
      if (ob_get_level()) {
        ob_end_clean();
      }
      // заставляем браузер показать окно сохранения файла
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename=' . basename($file));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      // читаем файл и отправляем его пользователю
      readfile($file);
      @unlink($file);
      exit;
    }
  }

}