<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\service.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Service Предназначен для формирования контента для различных страниц сервиса
 */
class Service {

  const PHOTO_PATH = '/files/images/reviews';

  /**
   * Подготовить данные для вывода главной стринцы сервиса
   * @return array Массив с данными, формата:
   *  ['reviews'] - массив с отзывами
   */
  public function getIndexView () {
    $result = array();
    // Подготовка отзывов
    $result['reviews'] = $this->getReviews();
    return $result;
  }

  /**
   * Возвращает массив с отзывами
   * @return array Данные с отзывами в массиве формата:
   *  [x] - номер отзыва
   *    ['photo'] - URL на фотогрфию автора
   *    ['name'] - ФИО автора
   *    ['post'] - должность автора
   *    ['sp'] - короткое название сайта СП, с которого автор отзыва
   *    ['sp_url'] - URL к сайту СП, с которого автор отзыва
   *    ['author_url'] - URL на профиль автора
   *    ['review'] - текст отзыва
   */
  private function getReviews () {
    $reviews = array(
//      array(
//        'photo' => URL::to(self::PHOTO_PATH . '/' . 'test.png'),
//        'name' => 'Иван Иванов',
//        'post' => 'Организатор',
//        'sp' => 'sp63.ru',
//        'sp_url' => 'http://sp63.ru/',
//        'author_url' => 'https://vk.com/id2956688',
//        'review' => 'Отзыв'
//      ),
    );
    return $reviews;
  }
}