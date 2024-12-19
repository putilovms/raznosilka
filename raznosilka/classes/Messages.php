<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Messages.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Messages Отвечает за вывод и генерацию сообщений для пользователя
 */
class Messages {
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  private $db;
  /**
   * @var User Информация о текущем пользователе
   */
  private $user;

  // Пейджер
  /**
   * @var Pager Объект для вывода пейджера
   */
  private $pager;
  /**
   * Количество строк показанных за один раз
   */
  const ITEM_ON_PAGE = 50;

  /**
   * Конструктор сообщений
   */
  function __construct () {
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    $this->user = Registry_Request::instance()->get('user');
  }

  /**
   * Возвращает количество новых сообщений
   * @param int $id ID пользователя
   * @return int Количество новых сообщений для пользователя
   */
  static function getCountNewMessages ($id) {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    return $db->getCountNewMessages($id);
  }

  /**
   * Возвращает массив всех сообщений для пользователя подготовленный для вывода
   * @param int $id ID пользователя
   * @return array Массив со всеми сообщениями для пользователя, формата:
   *  ['messages'] - списко сообщений
   *    [x] - номер сообщения
   *      ['message_id'] - ID сообщения
   *      ['user_id'] - ID пользователя для которого сообщения
   *      ['message_date'] - дата сообщения
   *      ['message_new'] - новое ли сообщение, bool
   *      ['message_text'] - текст сообщения
   *      ['message_type'] - тип сообщения
   *      ['class'] - класс сообщения:
   *        success - сообщений об удачном завершении операции
   *        info - информационное сообщение
   *        warning - предупреждение
   *        money - сообщений о финансовых операциях
   *  ['pager'] - пейджер
   */
  public function getMessages ($id) {
    $result = array();
    $messages = $this->db->getMessages($id);
    if (!empty($messages)) {
      $arr = array();
      // Инициализация пейджера
      $this->pager = new Pager($messages, self::ITEM_ON_PAGE);
      $itemForView = $this->pager->getItemForView();
      $result['pager'] = $this->pager->getHTML();
      foreach ($itemForView as $message) {
        // Дата
        $message[MESSAGE_DATE] = strftime('%H:%M %d.%m.%Y',strtotime($message[MESSAGE_DATE]));
        // Тип сообщения
        $message['class'] = '';
        switch ($message[MESSAGE_TYPE]) {
          case SUCCESS_MESSAGE :
            $message['class'] = 'success';
            break;
          case INFO_MESSAGE :
            $message['class'] = 'info';
            break;
          case WARNING_MESSAGE :
            $message['class'] = 'warning';
            break;
          case MONEY_MESSAGE :
            $message['class'] = 'money';
            break;
        }
        $result['messages'][] = $message;
        // Добавляем ID просмотренного сообщения
        $arr[] = $message[MESSAGE_ID];
      }
      // Установить выведенные сообщения как прочитанные
      $this->db->setMessagesRead($arr, $id);
      // Обновляем информацию о сообщениях у пользователя
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      $user->updateCountNewMessages();
    }
    return $result;
  }

  /**
   * Удаляет выбранные сообщения
   * @param array $arr Массив с id сообщений которые необходимо удалить
   * @param int $id ID пользователя
   * @return bool Результат
   */
  public function deleteMessages ($arr, $id) {
    if (!empty($arr)) {
      // Подгатавливаем массив с ID удаляемых сообщений
      foreach ($arr as $idMessage => $value) {
        if ($value == 'on' and is_int($idMessage)) {
          $messages[] = $idMessage;
        }
      }
      if (!empty($messages)) {
        return $this->db->deleteMessages($messages, $id);
      }
    }
    return false;
  }

  /**
   * Рассылка уведомлений всем активным польователям
   * @param int $type Тип сообщения
   * @param string $text Текст сообщения
   * @return false|int Результат:
   * - false - в случае неудачи
   * - Число отправленных сообщений в случае успеха
   */
  public function postMessages ($type, $text) {
    if ($type != '' and $text != '') {
      return $this->db->postMessages($type, $text);
    }
    return false;
  }

  /**
   * Посылает одно уведомление одному адресату
   * @param int $type Тип сообщения
   * @param string $text Текст сообщения
   * @param int $id ID получателя
   * @return bool Результат операции
   */
  public function postMessage ($type, $text, $id) {
    return $this->db->postMessage($type, $text, $id);
  }

  /**
   * Посылает одно уведомление текущему пользователю
   * @param int $type Тип сообщения
   * @param string $text Текст сообщения
   * @return bool Результат операции
   */
  public function postMessageCurrentUser ($type, $text) {
    $id = $this->user->getUserId();
    return $this->db->postMessage($type, $text, $id);
  }

} 