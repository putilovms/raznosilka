<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\TimeZoneHelper.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class TimeZoneHelper Класс помогающий работать с временными зонами
 */
class TimeZoneHelper {

  /**
   * Получить массив с данными о временных зонах для вывода
   */
  public function getTimeZoneListForView () { // todo описать отдаваемый массив
    $result = array();
    $list = $this->getTimeZoneList();
    // Подготовка значений для вывода
    foreach ($list as $zone) {
      $time = new DateTime(NULL, new DateTimeZone($zone));
      $hour = $time->format('P');
      $parts = explode('/', $zone);
      $continent = $this->translateContinent($parts[0]);
      $city = $this->translateCity($parts[1]);
      $result[$hour][$city] = array(
        'time_zone' => $zone,
        'continent' => $continent,
        'city' => $city,
        'hour' => $hour
      );
    }
    // Сортировка
    ksort($result, SORT_NUMERIC);
    foreach ($result as &$zone) {
      ksort($zone);
    }
    return $result;
  }

  /**
   * Получить список временных зон для России
   * @return array Саисок временных зон
   */
  function getTimeZoneList () {
    $list = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, 'RU');
    return $list;
  }

  /**
   * Перевести название континента
   * @param $str string Континент
   * @return string Перевод
   */
  function translateContinent ($str) {
    $result = $str;
    $translate = array(
      'Europe' => 'Европа',
      'Asia' => 'Азия',
    );
    if (isset($translate[$str])) {
      $result = $translate[$str];
    }
    return $result;
  }

  /**
   * Перевести название города
   * @param $str string Город
   * @return string Перевод
   */
  function translateCity ($str) {
    $result = $str;
    $translate = array(
      'Kaliningrad' => 'Калининград',
      'Moscow' => 'Москва',
      'Simferopol' => 'Симферополь',
      'Volgograd' => 'Волгоград',
      'Samara' => 'Самара',
      'Yekaterinburg' => 'Екатеринбург',
      'Novosibirsk' => 'Новосибирск',
      'Omsk' => 'Омск',
      'Krasnoyarsk' => 'Красноярск',
      'Novokuznetsk' => 'Новокузнецк',
      'Chita' => 'Чита',
      'Irkutsk' => 'Иркутск',
      'Khandyga' => 'Хандыга',
      'Yakutsk' => 'Якутск',
      'Magadan' => 'Магадан',
      'Sakhalin' => 'Сахалин',
      'Ust-Nera' => 'Усть-Нера',
      'Vladivostok' => 'Владивосток',
      'Srednekolymsk' => 'Среднеколымск',
      'Anadyr' => 'Анадырь',
      'Kamchatka' => 'Камчатка',
    );
    if (isset($translate[$str])) {
      $result = $translate[$str];
    }
    return $result;
  }

  /**
   * Проверить, является ли введёная временная зона допустимой
   * @param $timeZone string Временная зона для проверки
   * @return bool Результат проверки
   */
  public function validateTimeZone ($timeZone) {
    $result = false;
    $list = $this->getTimeZoneList();
    if (in_array($timeZone, $list)) {
      $result = true;
    }
    return $result;
  }

}