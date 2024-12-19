<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Controller_Index.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Index Контроллер главной страницы сайта
 */
class Controller_Index extends Controller {

  /**
   * Вывод главной страницы сайта. Для зарегистрированного пользователя загружается
   * сервис, а для незарегистрированного приветственная страница. Путь / и /index
   */
  function index () {
    if ($this->user->isAuth()) {
      // Вывод для зарегистрированного пользователя - страница сервиса
      $controller = new Controller_Service();
      $controller->index();
    } else {
      // Добавление мета тегов
      $title = 'Разносилка - сервис для проставления оплат';
      $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
      $urlLogo = URL::to('files/images/logo-tag.png');
      $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
      $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
      $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
      $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
      $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
      $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
      // Вывод для незарегистрированного пользователя - страница приветствия
      $service = new Service();
      $info = $service->getIndexView();
      $this->template->set('info', $info);
      $this->template->setTitle($title);
      $this->template->show('index');
    }
  }
}
