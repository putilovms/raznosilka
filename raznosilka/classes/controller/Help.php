<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Help.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Help Отвечает за вывод инструкции к разносилке
 */
class Controller_Help extends Controller {

  /**
   * Оглавление помощи
   */
  function index() {
    // Добавление мета тегов
    $title = 'Руководство пользователя';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('help');
  }

  /**
   * Системные требования
   */
  function system_req() {
    // Добавление мета тегов
    $title = 'Системные требования';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('system_req');
  }

  /**
   * Первые шаги
   */
  function first_steps() {
    // Добавление мета тегов
    $title = 'Первые шаги в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('first_steps');
  }

  /**
   * Регистрация
   */
  function register() {
    // Добавление мета тегов
    $title = 'Регистрация аккаунта в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('register');
  }

  /**
   * Активация аккаунта
   */
  function activation() {
    // Добавление мета тегов
    $title = 'Активация аккаунта в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('activation');
  }

  /**
   * Ввод логина и пароля от сайта СП
   * УБРАЛ ИЗ СПИСКА
   */
  function binding() {
    // Добавление мета тегов
    $title = 'Ввод логина и пароля от сайта СП в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('binding');
  }

  /**
   * Выгрузка SMS
   */
  function import_sms() {
    // Добавление мета тегов
    $title = 'Выгрузка SMS из телефона';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('import_sms');
  }

  /**
   * Выгрузка SMS на Android
   */
  function import_android() {
    // Добавление мета тегов
    $title = 'Выгрузка SMS на Android';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('import_android');
  }

  /**
   * Выгрузка SMS на iPhone
   */
  function import_iphone() {
    // Добавление мета тегов
    $title = 'Выгрузка SMS на iPhone';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('import_iphone');
  }

  /**
   * Загрузка SMS
   */
  function upload_sms() {
    // Добавление мета тегов
    $title = 'Загрузка SMS в Разносилку';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('upload_sms');
  }

  /**
   * Загрузка SMS с компьютера
   */
  function upload_pc() {
    // Добавление мета тегов
    $title = 'Загрузка SMS с компьютера в Разносилку';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('upload_pc');
  }

  /**
   * Загрузка SMS с телефона
   */
  function upload_phone() {
    // Добавление мета тегов
    $title = 'Загрузка SMS с телефона в Разносилку';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('upload_phone');
  }

  /**
   * Выбор закупки
   */
  function select_purchase() {
    // Добавление мета тегов
    $title = 'Выбор закупки в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('select_purchase');
  }

  /**
   * Проставление оплат
   */
  function filling_pay() {
    // Добавление мета тегов
    $title = 'Проставление оплат в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('filling_pay');
  }

  /**
   * Автопроставление
   */
  function auto_filling() {
    // Добавление мета тегов
    $title = 'Автопроставление оплат в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('auto_filling');
  }

  /**
   * Как написать службе поддержки?
   */
  function support() {
    // Добавление мета тегов
    $title = 'Как написать службе поддержки?';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('support');
  }

  /**
   * Обзор закупки
   */
  function purchase() {
    // Добавление мета тегов
    $title = 'Обзор закупки в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('purchase');
  }

  /**
   * Возврат SMS
   */
  function return_sms() {
    // Добавление мета тегов
    $title = 'Возврат SMS в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('return_sms');
  }

  /**
   * Смена логина и пароля от сайта СП
   * УБРАЛ ИЗ СПИСКА
   */
  function password_sp() {
    // Добавление мета тегов
    $title = 'Смена логина и пароля от сайта СП в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('password_sp');
  }

  /**
   * Удаление логина и пароля от сайта СП
   * УБРАЛ ИЗ СПИСКА
   */
  function password_sp_del() {
    // Добавление мета тегов
    $title = 'Удаление логина и пароля от сайта СП в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('password_sp_del');
  }

  /**
   * Повторный ввод логина и пароля от сайта СП после удаления
   * УБРАЛ ИЗ СПИСКА
   */
  function password_sp_set() {
    // Добавление мета тегов
    $title = 'Повторный ввод логина и пароля от сайта СП после удаления в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('password_sp_set');
  }

  /**
   * Получение пробного периода
   */
  function gift() {
    // Добавление мета тегов
    $title = 'Получение пробного периода в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('gift');
  }

  /**
   * Оплата услуги
   */
  function paying() {
    // Добавление мета тегов
    $title = 'Оплата услуги в Разносилке';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('paying');
  }

  /**
   * Описание главной страницы сервиса
   */
  function home() {
    // Добавление мета тегов
    $title = 'Описание главной страницы Разносилки';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('home');
  }

  /**
   * Расширение
   */
  function extension() {
    // Добавление мета тегов
    $title = 'Установка или включение расширения для Разносилки';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('extension');
  }

  /**
   * Работа с расширением
   */
  function extension_use() {
    // Добавление мета тегов
    $title = 'Работа с расширением для Разносилки';
    $description = 'Разносилка - это сервис, предназначенный для организаторов СП, позволяющий автоматически проставлять оплаты';
    $urlLogo = URL::to('files/images/logo-tag.png');
    $this->template->addMetaTag("meta", array("property" => "og:image", "content" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "og:title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "og:description", "content" => $description));
    $this->template->addMetaTag("link", array("rel" => "image_src", "href" => $urlLogo));
    $this->template->addMetaTag("meta", array("name" => "title", "content" => $title));
    $this->template->addMetaTag("meta", array("name" => "description", "content" => $description));
    $this->template->setTitle($title);
    $this->template->show('extension_use');
  }

}