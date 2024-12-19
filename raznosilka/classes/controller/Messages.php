<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\Controller_Messages.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class Controller_Messages Контроллер отвечает за вывод сообщений
   */
  class Controller_Messages extends Controller {
    /**
     * @var Messages содержит класс для работы с уведомлениями
     */
    private $messages;

    /**
     * Конструктор класса
     */
    function __construct () {
      parent::__construct();
      $this->messages = new Messages();
    }

    /**
     * Список сообщений /messages
     */
    function index () {
      $this->access(USER_AUTH);
      $this->access(NOT_BLOCKED);
      $messages = $this->messages->getMessages($this->user->getUserId());
      $this->template->set('messages', $messages);
      $this->template->setTitle('Уведомления');
      $this->template->show('messages');
    }

    /**
     * Удаление сообщений
     */
    function delete () {
      $this->access(USER_AUTH);
      $this->access(NOT_BLOCKED);
      if (!empty($_POST)) {
        $result = $this->messages->deleteMessages($_POST, $this->user->getUserId());
        if ($result) {
          $this->notify->sendNotify('Выбранные уведомления успешно удалены.', SUCCESS_NOTIFY);
        } else {
          $this->notify->sendNotify('Не удалось удалить выбранные уведомления.', ERROR_NOTIFY);
        }
      } else {
        $this->notify->sendNotify('Не выбрано ни одного уведомления для удаления.', ERROR_NOTIFY);
      }
      $url = URL::to('messages');
      $this->headerLocation($url);
    }

    /**
     * Форма рассылки уведомлений
     */
    function post () {
      $this->access(ADMIN);
      $this->template->setTitle('Рассылка уведомлений');
      $this->template->show('post_messages');
    }

    /**
     * Рассылка уведомлений
     */
    function posting () {
      $this->access(ADMIN);
      $type = (string)isset($_POST['messages_type']) ? $_POST['messages_type'] : '';
      $text = isset($_POST['messages_text']) ? $_POST['messages_text'] : '';
      $result = $this->messages->postMessages($type, $text);
      if ($result) {
        $this->notify->sendNotify("Рассылка сообщений выполнена успешно. Разослано сообщений: {$result}", SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Рассылка сообщений не выполнена.', ERROR_NOTIFY);
      }
      $url = URL::to('messages/post');
      $this->headerLocation($url);
    }
  }