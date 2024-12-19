<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Info.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Info Вывод различной информации
 */
class Controller_Info extends Controller {

  /**
   * Помощь
   */
  function index () {
    $controller = new Controller_Error;
    $controller->notFound();
  }

  /**
   * Пользовательское соглашение
   */
  function user_agreement(){
    // Добавление мета тегов
    $title = 'Пользовательское соглашение';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    // Вывод пользовательского соглашения
    $this->template->show('user_agreement');
  }

  /**
   * Политика обработки персональных данных
   */
  function confidential(){
    // Добавление мета тегов
    $title = 'Пользовательское соглашение';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    // Вывод пользовательского соглашения
    $this->template->show('confidential');
  }

  /**
   * Список совместимых сайтов СП
   */
  function sp(){
    $sp = new Sp();
    $list = $sp->getSpList();
    $this->template->set('list', $list);
    // Добавление мета тегов
    $title = 'Список сайтов СП с которыми работает Разносилка';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('sp');
  }

  /**
   * О проекте
   */
  function project(){
    $title = 'О проекте Разносилка';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $this->template->addMetaTag("meta", array("property"=>"og:image", "content"=>URL::to('files/images/logo-tag.png')));
    $this->template->addMetaTag("meta", array("name"=>"og:title", "content"=>$title));
    $this->template->addMetaTag("meta", array("name"=>"og:description", "content"=>$description));
    $this->template->addMetaTag("link", array("rel"=>"image_src", "href"=>URL::to('files/images/logo-tag.png')));
    $this->template->addMetaTag("meta", array("name"=>"title", "content"=>$title));
    $this->template->addMetaTag("meta", array("name"=>"description", "content"=>$description));
    $this->template->setTitle($title);
    $this->template->show('project');
  }

  /**
   * Заявка на добавление нового сайта СП
   */
  function add_sp(){
    // Добавление мета тегов
    $title = 'Что делать, если вашего сайта СП нет в списке Разносилки';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('add_sp');
  }

}