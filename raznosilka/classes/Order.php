<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Order.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Order Описывает товар (Order) в заказе (Lot) пользователя (UserPurchase)
   */
  class Order {
    /**
     * @var int Оргсбор
     */
    private $orgFee = 0;
    /**
     * @var int Статус закупки
     */
    private $state = 0;
    /**
     * @var float Сумма доставки, руб
     */
    private $delivery = 0;
    /**
     * @var string Комментарий участника
     */
    private $comment = '';
    /**
     * @var string Название товара
     */
    private $name = '';
    /**
     * @var float Цена товара, руб
     */
    private $price = 0;
    /**
     * @var int ID закупки
     */
    private $id = 0;

    /**
     * Создание товара
     * @param array $order Массив с данными о товаре из сайта СП, формата:
     *  - ['id'] - ID заказа
     *  - ['org_fee'] - оргсбор, %
     *  - ['state'] - статус закупки
     *  - ['delivery'] - сумма доставки, руб
     *  - ['comment_lot'] - комментарий участника
     *  - ['name_lot'] - название товара
     *  - ['price'] - цена товара, руб
     */
    function __construct (array $order) {
      $this->id = $order['id'];
      $this->orgFee = $order['org_fee'];
      $this->state = $order['state'];
      $this->delivery = $order['delivery'];
      $this->comment = $order['comment_lot'];
      $this->name = $order['name_lot'];
      $this->price = $order['price'];
    }

    /**
     * Получить процент оргсбора
     * @return int Оргсбор
     */
    function getOrgFee(){
      return $this->orgFee;
    }

    /**
     * Получить статус закупки
     * @return int Статус закупки
     */
    function getState(){
      return $this->state;
    }

    /**
     * Получить сумму доставки, руб
     * @return float Сумма доставки, руб
     */
    function getDelivery(){
      return $this->delivery;
    }

    /**
     * Получение комментария участника
     * @return string Комментарий участника
     */
    function getComment () {
      return $this->comment;
    }

    /**
     * Получение названия товара
     * @return string Названия товара
     */
    function getName () {
      return $this->name;
    }

    /**
     * Цена товара, руб
     * @return float Цена товара, руб
     */
    function getPrice(){
      return $this->price;
    }

    /**
     * Получить ID товара
     * @return int ID товара
     */
    function getOrderId(){
      return $this->id;
    }

    /**
     * Активен ли данный товар.
     * Товар не активен если проставлен отказ (state = 3)
     * @return bool
     */
    public function isActiveOrder () {
      if ($this->state != 3) {
        return true;
      } else {
        return false;
      }
    }

  }
