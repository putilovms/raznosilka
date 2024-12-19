<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\OrderUser.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class OrderUser Отвечает за состояние заказов пользователя
 */
class OrderUser {
  /**
   * @var int ID пользователя
   */
  private $userId;
  /**
   * @var DataBase объект для работы с базой данных
   */
  private $db;
  /**
   * @var bool Статус аккаунта (оплачен или нет)
   */
  private $status = false;
  /**
   * @var int Время до которого оплачен аккаунт в формате Unix
   */
  private $dateDone = null;
  /**
   * @var int Текущее время в формате Unix
   */
  private $dateNow;
  /**
   * @var array Массив с активными заказами пользователя
   */
  private $activeOrders = array();
  /**
   * @var array Массив со всеми заказами пользователя
   */
  private $allOrders = array();

  /**
   * Конструктор класса
   * @param $uid int ID пользователя
   */
  function __construct ($uid) {
    $this->userId = $uid;
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    $this->dateNow = time();
    $this->init();
  }

  /**
   * Инициализация всех свойств заказа - оплачена ли услуга, до какого числа оказывается услуга.
   */
  private function init () {
    // Получить активные заказы
    $this->activeOrders = $this->db->getUserActiveOrders($this->userId);
    // Имеется ли запущенный заказ
    if ($this->hasRunOrder()) {
      // Обновить статусы заказов
      $this->updateOrderStatus();
    } else {
      // Запустить заказ, если он есть
      $this->runOrder();
    }
    // Получить данные об услуге
    $this->serviceInfo();
    // Получить все заказы
    $this->allOrders = $this->db->getUserAllOrders($this->userId);
  }

  /**
   * Имеется ли запущенный заказ
   * @return bool Истина, если имеется запущенный заказ
   */
  function hasRunOrder () {
    $result = false;
    if (!empty($this->activeOrders)) {
      foreach ($this->activeOrders as $order) {
        if (!is_null($order[ORDER_RUN])) {
          return true;
        }
      }
    }
    return $result;
  }

  /**
   * Обновляет статусы заказов
   * @throws Exception
   */
  function updateOrderStatus () {
    if (!empty($this->activeOrders)) {
      $b = true;
      // Пока не закончатся не исполненные заказы или будет найден запущенный не истёкший заказ
      while ($b) {
        $b = false;
        foreach ($this->activeOrders as &$order) {
          // Если заказ не исполнен
          if (is_null($order[ORDER_DONE])) {
            $b = true;
            // Если услуга запущена
            if (!is_null($order[ORDER_RUN])) {
              $dateRun = strtotime($order[ORDER_RUN]);
              $day = $order[ORDER_DAY];
              $dateDone = $dateRun + ($day * 24 * 3600);
              if ($dateDone > $this->dateNow) {
                // Если услуга не истекла, то заказ не требует дальнейшего обновления
                break(2);
              } else {
                // Если услуга истекла, отмечаем заказ как исполненный
                if (!$this->db->doneOrder($order[ORDER_ID], $dateDone)) {
                  throw new Exception('Не удалось отметить заказ пользователя как исполненный');
                }
                $order[ORDER_DONE] = strftime('%Y-%m-%d %H:%M:%S', $dateDone);
                break;
              }
            }
            // Если услуга не запущена
            if (is_null($order[ORDER_RUN])) {
              $orderId = $order[ORDER_ID];
              if (!$this->db->runOrder($orderId, $dateDone)) {
                throw new Exception('Не удалось запустить заказ пользователя');
              }
              $order[ORDER_RUN] = strftime('%Y-%m-%d %H:%M:%S', $dateDone);
              break;
            }
          }
        }
      }
    }
  }

  /**
   * Запустить новую, не запущенную услугу
   * @throws Exception
   */
  function runOrder () {
    if (!empty($this->activeOrders)) {
      $orderId = $this->activeOrders[0][ORDER_ID];
      if ($this->db->runOrder($orderId, $this->dateNow)) {
        $this->activeOrders[0][ORDER_RUN] = strftime('%Y-%m-%d %H:%M:%S', $this->dateNow);
      } else {
        throw new Exception('Не удалось запустить заказ пользователя');
      }
    }
  }

  /**
   * Определяем статус услуги и дату до которой она предоставляется
   */
  function serviceInfo () {
    if (!empty($this->activeOrders)) {
      // Поиск запущенной услуги
      foreach ($this->activeOrders as $order) {
        // Если есть запущенная услуга
        if (!is_null($order[ORDER_RUN]) and is_null($order[ORDER_DONE])) {
          $dateRun = strtotime($order[ORDER_RUN]);
          $this->status = true;
          break;
        }
      }
      // Просчитать дату до которой оплачена услуга
      if ($this->status) {
        $day = 0;
        foreach ($this->activeOrders as $order) {
          if (is_null($order[ORDER_DONE])) {
            $day += $order[ORDER_DAY];
          }
        }
        $this->dateDone = $dateRun + ($day * 24 * 3600);
      }
    }
  }

  /**
   * Получить все заказы пользователя
   * @return array Все заказы пользователя
   */
  function getAllOrders () {
    return $this->allOrders;
  }

  /**
   * Получить дату до которой будет оказываться услуга
   * @return int Дата до которой будет оказываться услуга
   */
  function getDateDone () {
    return $this->dateDone;
  }

  /**
   * Получить статус аккаунта (оплачен или нет)
   * @return bool Статус аккаунта (оплачен или нет)
   */
  function getStatus () {
    return $this->status;
  }

  /**
   * Можно ли напоминать о предоплате?
   * @return bool Истина, если можно напоминать
   */
  function isCanNotifyPay () {
    $prePay = false;
    if (!is_null($this->getDateNotifyPay())) {
      $prePayDate = $this->getDateNotifyPay();
      $prePay = ($this->dateNow > $prePayDate) ? true : false;
    }
    return $prePay;
  }

  /**
   * Получить дату после которой можно напоминать пользователелю о скором истечении оплаченной услуги
   * @return int|null Дата после которой возможно напоминанаие
   */
  function getDateNotifyPay () {
    $datePrePay = null;
    if (!is_null($this->getDateDone())) {
      $datePrePay = $this->getDateDone() - (DAY_NOTIFY_PAY * 24 * 3600);
    }
    return $datePrePay;
  }

  /**
   * Получить информацию для вывода страницы с оплатами
   * @return array Массив с данными для вывода, формата:
   *  - ['user'] - Статус пользователя @see User::getPayingStatus()
   *  - ['date_done'] - дата до которой оплачен аккаунт
   *  - ['day'] - количество оплаченных дней
   *  - ['payment_form'] - форма платёжной системы
   */
  static function getPayPage () {
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $result = array();
    // Получить статус пользователя
    $result['user'] = $user->getPayingStatus();
    $result['payment_form'] = '';
    $payment = PaymentSystem::getPaymentSystem();
    if ($payment instanceof PaymentSystem) {
      $result['payment_form'] = $payment->getPaymentFormHTML();
    }
    $date = $user->getPayingDate();
    $result['status'] = $user->isPaying();
    $result['date_done'] = (!is_null($date)) ? strftime('%H:%M %d.%m.%Y', $date) : '';
    $result['day'] = (!is_null($date)) ? Kit::DateDiff($date) : '';
    return $result;
  }
  
  /**
   * Активировать подарок (30 дней бесплатного использования сервиса, для нового пользователя)
   */
  function activateGift () {
    $result = false;
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    if ($user->hasGift()) {
      // Создаём заказ
      $order = array();
      $order[USER_ID] = $this->userId;
      $order[ORDER_TYPE] = ORDER_GIFT;
      $order[ORDER_DAY] = DAY_GIFT;
      $result = $this->db->addOrder($order);
      if ($result) {
        $this->db->useGift($this->userId);
        // Обновляем свойства объекта
        $this->init();
        // Отсылаем сообщение
        $info['day'] = DAY_GIFT;
        $info['date_done'] = strftime('%H:%M %d.%m.%Y', $this->getDateDone());
        $info['url_step'] = URL::to('help/first_steps');
        $info['url_help'] = URL::to('help');
        $info['mail_support'] = 'support@raznosilka.ru';
        $info['url_forum'] = 'http://forum.raznosilka.ru/';
        $this->sendMessage('gift', $info);
        // Лог
        $log = new Logs();
        $userInfo = $user->getUserInfo();
        $log->actionLog($userInfo, 'Активирован пробный период');
        // Отсылаем письмо
        $mail = new Mail();
        $mail->sendUserGiftMail($userInfo, $info);
      }
    }
    return $result;
  }

  /**
   * Получить тип ордера в строковом формате
   * @param $type int Тип ордера полученый из БД
   * @return string Тип ордера в строковом формате
   */
  static function getTypeOrder ($type) {
    $result = $type;
    switch ($type) {
      case ORDER_GIFT :
        $result = 'Пробный период';
        break;
      case ORDER_ADMIN:
        $result = 'Вручную';
        break;
      case ORDER_YANDEX_KASSA:
        $result = 'Яндекс.Касса';
        break;
    }
    return $result;
  }

  /**
   * Добавить заказ на услугу
   * @param $day int Количество дней
   * @param string $typeMessage Причина добавления пользователю заказа (по умолчанию - manual)
   * @param int $paymentId ID записи с данными о платеже
   * @param int $typeOrder Тип заказа (по умолчани - добавлен администратором)
   * @return false|int ID добавленного заказа
   * @throws Exception
   */
  public function addOrder ($day, $typeMessage = 'manual', $paymentId = 0, $typeOrder = ORDER_ADMIN) {
    // Создаём заказ
    $order = array();
    $order[USER_ID] = $this->userId;
    $order[ORDER_TYPE] = $typeOrder;
    $order[ORDER_DAY] = $day;
    $result = $this->db->addOrder($order, $paymentId);
    if ($result) {
      // Обновляем свойства объекта
      $this->init();
      // Отсылаем сообщение
      $info['day'] = $day;
      $info['date_done'] = strftime('%H:%M %d.%m.%Y', $this->getDateDone());
      $this->sendMessage($typeMessage, $info);
    }
    return $result;
  }

  /**
   * Возврат заказа
   * @param $oid int ID заказа
   * @return bool Результат возврата
   */
  public function returnOrder ($oid) {
    $result = $this->db->returnOrder($oid);
    return $result;
  }

  /**
   * Отменить возврат заказа
   * @param $oid int ID заказа
   * @return bool Результат возврата
   */
  public function cancelReturnOrder ($oid) {
    $result = $this->db->cancelReturnOrder($oid);
    return $result;
  }

  /**
   * Отправить сообщение пользователю
   * @param $type string Тип сообщения:
   *  - gift - активация пробного периода
   *  - manual - добавлено администратором вручную
   *  - unplanned - компенсация за незапланированные технические работы
   *  - YandexKassa - добавлено через Яндекс.Кассу
   * @param array $info Дополнительная информация
   * @throws Exception
   */
  public function sendMessage ($type, array $info = array()) {
    switch ($type) {
      case 'gift':
        $message = "<p>Вы получили {$info['day']} д. бесплатного использования сервиса.</p>";
        $message .= "<p>Пробный период истекает {$info['date_done']}.</p>";
        $message .= "<p>О том, как пользоваться сервисом вы можете прочитать в  разделе «<a href='{$info['url_help']}' target='_blank'>Руководство пользователя</a>».</p>";
        $message .= "<p>О том, как проставить свои первые оплаты при помощи «Разносилки» вы можете узнать из раздела «<a href='{$info['url_step']}' target='_blank'>Первые шаги</a>».</p>";
        $message .= "<p>Связаться со службой поддержки вы можете по адресу <a href='mailto:{$info['mail_support']}' target='_blank'>{$info['mail_support']}</a>.</p>";
        $message .= "<p>Задать свои вопросы или сообщить об ошибке вы можете на <a href='{$info['url_forum']}' target='_blank'>форуме</a>.</p>";
        break;
      case 'manual':
        $message = "<p>Вы получили {$info['day']} д. использования сервиса.</p>";
        $message .= "<p>Услуга предоставляется до {$info['date_done']}.</p>";
        break;
      case 'unplanned':
        $message = "<p>Вы получили {$info['day']} д. использования сервиса в качестве компенсации за незапланированные технические работы.</p>";
        $message .= "<p>Услуга предоставляется до {$info['date_done']}.</p>";
        break;
      case 'YandexKassa':
        $message = "<p>Вы оплатили {$info['day']} д. использования сервиса.</p>";
        $message .= "<p>Услуга предоставляется до {$info['date_done']}.</p>";
        $message .= "<p>Спасибо за ваш выбор!</p>";
        break;
      default:
        throw new Exception('Не существующий тип сообщения');
        break;
    }
    $messages = new Messages();
    $messages->postMessage(MONEY_MESSAGE, $message, $this->userId);
  }

  /**
   * Удалить заказ
   * @param $oid int ID заказа
   * @return bool Результат операции
   */
  public function deleteOrder ($oid) {
    $result = $this->db->deleteOrder($oid, $this->userId);
    return $result;
  }

}