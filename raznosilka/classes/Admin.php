<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Admin.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Admin Класс для вывода различных разделов администрирования сайта
 */
class Admin {
  /**
   * Файл для обновления сервиса
   */
  const UPDATE_FILE = '/update.php';

  /**
   * @var DataBase Доступ к базе данных
   */
  private $db;

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
   * Конструктор класса.
   */
  function __construct () {
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
  }

  /**
   * Получить массив для вывода с пользователями Разносилки
   * @return array Массив для вывода с пользователями Разносилк, формата:
   *  ['users'] - список пользователей
   *    [x] - номер пользователя
   *      [USER_ID] - ID пользователя
   *      [USER_LOGIN] - логин пользователя
   *      [SP_SITE_URL] - URL к сайту СП
   *      [SP_SITE_NAME] - название сайта СП
   *      [USER_REG_DATE] - дата регистрации пользователя
   *      [USER_EMAIL] - Email пользователя
   *      [USER_BLOCKED] - Заблокирован или нет пользователь
   *      [USER_ORG_ID] - ID организатора
   *  ['pager'] - пейджер
   *  ['items_count'] - количество пользователей
   */
  public function getUsersView () {
    $result = array();
    // Получение списка пользователей
    $users = $this->db->getAllUsers();
    if ($users !== false) {
      // Пейджер
      $this->pager = new Pager($users, self::ITEM_ON_PAGE);
      $itemForView = $this->pager->getItemForView();
      $result['items_count'] = $this->pager->getItemCount();
      $result['pager'] = $this->pager->getHTML();
      foreach ($itemForView as $user) {
        $item = array();
        $item[USER_ID] = $user[USER_ID];
        $item[USER_LOGIN] = $user[USER_LOGIN];
        $item[SP_SITE_URL] = $user[SP_SITE_URL];
        $item[SP_SITE_NAME] = $user[SP_SITE_NAME];
        $item[USER_REG_DATE] = strftime('%H:%M %d.%m.%Y', strtotime($user[USER_REG_DATE]));
        $item[USER_EMAIL] = $user[USER_EMAIL];
        $item[USER_LAST_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($user[USER_LAST_TIME]));
        $item[USER_BLOCKED] = $user[USER_BLOCKED];
        $item[USER_ORG_ID] = $user[USER_ORG_ID];
        $item['activate'] = $user[USER_ACTIVATE];
        // URL для управления аккаунтом
        $url = URL::to('admin/manage_user', array('id' => $item[USER_ID]));
        $item['url'] = $url;
        // Получание данных об оплате услуги
        $order = new OrderUser($user[USER_ID]);
        $item['status'] = $order->getStatus();
        $item['date_done'] = strftime('%H:%M %d.%m.%Y', $order->getDateDone());
        $result['users'][] = $item;
      }
    }
    return $result;
  }

  /**
   * Управление пользователем по его ID
   * @param $id int ID пользователя
   * @return array Массив для вывода, вида:
   *  ['user'] - массив с данными пользователя
   *    ... - стандартные элементы возвращаемые БД
   *    ['activate'] - активирован ли пользователь
   *    ['bind'] - имеет ли аккаунт OrgID
   *    ['have_login'] - введён ли логин и пароль для сайта СП
   *    ['activate_url'] - урл для повторной отправки письма с активацией
   *    ['force_activate_url'] - Ссылка для принудительной активации
   *    ['force_forgot_url'] - ссылка для принудительной отправки письма для восстановления пароля
   *    ['count_sms'] - количество загруженных СМС
   *    ['count_purchase'] - количество закупок в списке пользователя
   *    ['count_pay'] - количество проставленных пользователем оплат
   *  ['sp'] - массив с данными сайта СП
   *  ['order'] - массив содержащий данные о заказах пользователя
   *    ['status'] - оплачено или нет в данный момент
   *    ['date_done'] - до какого числа оплачено
   *    ['class'] - класс для отображения заказа
   *    ['return'] - можно ли возвращать данный заказ
   *    ['url'] - url для возврата
   *    ['details_url'] - url для просмотра подробностей о платеже
   *    ['orders'] - список заказов пользователя
   *      [x] - номер заказа
   *        ... - стандартные поля из БД
   *  ['sum'] - Сумма полученная от пользователя сервисом
   *  ['sum_paid'] - Сумма заплаченная пользователем в платёжную систему
   */
  public function getUserView ($id) {
    $result = array();
    // получение данных о пользоватле
    $user = $this->db->getUserById((int)$id);
    if (!empty($user)) {
      $user[USER_REG_DATE] = strftime('%H:%M %d.%m.%Y', strtotime($user[USER_REG_DATE]));
      $user[USER_LAST_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($user[USER_LAST_TIME]));
      $user['activate'] = $user[USER_ACTIVATE];
      if (!$user['activate']) {
        // Ссылка для повторного письма с активацией
        $user['activate_url'] = URL::to('admin/reactivate', array('id' => $user[USER_ID]));
        // Ссылка для принудительной активации
        $user['force_activate_url'] = URL::to('admin/force_activate', array('id' => $user[USER_ID]));
      } else {
        // Ссылка для письма с восстановлением пароля
        $user['force_forgot_url'] = URL::to('admin/force_forgot', array('id' => $user[USER_ID]));
      }
      $user['bind'] = ($user[SP_ID] != 0) ? true : false;
      $user['have_login'] = (!empty($user[USER_SP_LOGIN]) and !empty($user[USER_SP_PASSWORD])) ? true : false;
      // Получение количества загруженных СМС
      $countSms = $sp = $this->db->getCountSmsByIdUser($user[USER_ID]);
      $user['count_sms'] = number_format($countSms, 0, ',', ' ');
      // Получение количества закупок в списке
      $countSms = $sp = $this->db->getCountPurchaseByIdUser($user[USER_ID]);
      $user['count_purchase'] = number_format($countSms, 0, ',', ' ');
      // Получение количества проставленных оплат
      $countSms = $sp = $this->db->getCountPayByIdUser($user[USER_ID]);
      $user['count_pay'] = number_format($countSms, 0, ',', ' ');
      // Доступ к сервису
      $user['blocked'] = $user[USER_BLOCKED];
      $query = array('uid' => $user[USER_ID], 'blocked' => !$user['blocked']);
      $user['blocked_url'] = URL::to('admin/blocked', $query);
      $result['user'] = $user;
      // Получение сайта СП
      if ($user[SP_ID] > 0) {
        $sp = $this->db->getSpById($user[SP_ID]);
        $result['sp'] = $sp;
      }
      // Получение данных о заказах пользователя
      $order = array();
      $orderUser = new OrderUser($user[USER_ID]);
      $order['status'] = $orderUser->getStatus();
      $order['date_done'] = strftime('%H:%M %d.%m.%Y', $orderUser->getDateDone());
      $order['orders'] = $orderUser->getAllOrders();
      // Получение суммы
      $result['sum'] = 0.00;
      $result['sum_paid'] = 0.00;
      $listPaymentSystem = PaymentSystem::getListPaymentSystems();
      foreach ($listPaymentSystem as $key => $value) {
        $paymentSystem = PaymentSystem::getPaymentSystem($key);
        $result['sum'] += $paymentSystem->getUserSumReceived($user[USER_ID]);
        $result['sum_paid'] += $paymentSystem->getUserSumPaid($user[USER_ID]);
      }
      // Форматирование
      $result['sum'] = number_format($result['sum'], 2, ',', '');
      $result['sum_paid'] = number_format($result['sum_paid'], 2, ',', '');
      // Обработка списка заказов
      if (!empty($order['orders'])) {
        foreach ($order['orders'] as &$value) {
          $value['details_url'] = '';
          if ($value[ORDER_TYPE] > ORDER_ADMIN) {
            $query = array('pid' => $value[PAYMENT_ID], 'uid' => $user[USER_ID]);
            $value['details_url'] = URL::to('admin/details', $query);
          }
          $value[ORDER_TYPE] = OrderUser::getTypeOrder($value[ORDER_TYPE]);
          $value[ORDER_ADD] = strftime('%H:%M %d.%m.%Y', strtotime($value[ORDER_ADD]));
          // Если заказ можно вернуть (отменить)
          $value['return'] = false;
          if (is_null($value[ORDER_RUN]) and is_null($value[ORDER_DONE]) and is_null($value[ORDER_RETURN])) {
            $value['return'] = true;
            $query = array('uid' => $user[USER_ID], 'oid' => $value[ORDER_ID]);
            $value['url'] = URL::to('admin/order_return', $query);
          }
          // Форматируем дату и определяем класс для отображения
          $value['class'] = 'normal';
          if (!is_null($value[ORDER_RUN])) {
            $value['class'] = 'warning';
            $value[ORDER_RUN] = strftime('%H:%M %d.%m.%Y', strtotime($value[ORDER_RUN]));
          } else {
            $value[ORDER_RUN] = '—';
          }
          if (!is_null($value[ORDER_DONE])) {
            $value['class'] = 'error';
            $value[ORDER_DONE] = strftime('%H:%M %d.%m.%Y', strtotime($value[ORDER_DONE]));
          } else {
            $value[ORDER_DONE] = '—';
          }
          if (!is_null($value[ORDER_RETURN])) {
            $value['class'] = 'error';
            $value[ORDER_RETURN] = strftime('%H:%M %d.%m.%Y', strtotime($value[ORDER_RETURN]));
          } else {
            $value[ORDER_RETURN] = '—';
          }
        }
        $order['orders'] = array_reverse($order['orders'], true);
      }
      $result['order'] = $order;
      $result['request_list'] = Sp::getSpRequestListForView();
    }
    return $result;
  }

  /**
   * Принудительная активация пользователя администратором
   * @param $id int ID пользователя
   * @return bool Результат выполнения активации
   */
  public function forceActivate ($id) {
    $result = false;
    $user = $this->db->getUserById((int)$id);
    if (!empty($user)) {
      if (!$user[USER_ACTIVATE]) {
        // Активировать пользователя
        $user = $this->db->activate($id);
        $mail = new Mail();
        $mail->sendUserWelcomeMail($user);
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Принудительная отправка письма администратором для восстановления пароля пользователя
   * @param $id int ID пользователя
   * @return bool Результат выполнения операции
   */
  public function forceForgot ($id) {
    $result = false;
    $user = $this->db->getUserById((int)$id);
    // Если пользователь найден
    if (!empty($user)) {
      // Если пользователь активирован
      if ($user[USER_ACTIVATE]) {
        // Отослать письмо
        $mail = new Mail();
        $result = $mail->sendUserForgotMail($user);
      }
    }
    return $result;
  }

  /**
   * Повторная отсылка письма со ссылкой для активации
   * @param $id int ID пользователя
   * @return bool Результат выполнения операции
   */
  public function reactivate ($id) {
    $result = false;
    $user = $this->db->getUserById((int)$id);
    // Если пользователь найден
    if (!empty($user)) {
      // Если пользователь не активирован
      if (!$user[USER_ACTIVATE]) {
        // Отослать письмо
        $mail = new Mail();
        $mail->sendUserActivateMail($user);
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Получить информацию о сервисе
   * @return array Массив для вывода, формата:
   */
  public function getServiceInfo () { // todo добавить формат массива
    $result = array();
    // Получение данных о БД
    $dbInfo = $this->getCountTablesRecords();
    $result['db'] = $dbInfo;
    // Получение данных о сервере
    $result['server']['server_version'] = $_SERVER['SERVER_SOFTWARE'];
    // Получение данных о PHP
    $result['php']['php_version'] = phpversion();
    $result['php']['memory_limit'] = (ini_get('memory_limit') < 0) ? "&infin;" : ini_get('memory_limit');
    $result['php']['file_uploads'] = ini_get('max_file_uploads');
    $result['php']['max_filesize'] = ini_get('upload_max_filesize');
    // Подключенные модули
    $modules = get_loaded_extensions();
    // var_dump($modules);
    $result['php']['curl'] = in_array('curl', $modules);
    $result['php']['mcrypt'] = in_array('mcrypt', $modules);
    $result['php']['zip'] = in_array('zip', $modules);
    // Получение данных о MySQL
    $db = Registry_Request::instance()->get('db');
    $mysqlVersion = $db->getAttribute(PDO::ATTR_SERVER_VERSION);
    $result['mysql']['mysql_version'] = $mysqlVersion;
    $result['mysql']['size_db'] = number_format($this->getSizeDB(), 2, ',', '');
    // Получение даты последнего запуска хрона
    $cronLastRun = strtotime(Registry_Request::instance()->get('cron_last_run'));
    $result['cron']['cron_last_run'] = strftime('%H:%M %d.%m.%Y', $cronLastRun);
    $deliveryLastRun = strtotime(Registry_Request::instance()->get('delivery_last_run'));
    $result['cron']['delivery_last_run'] = strftime('%H:%M %d.%m.%Y', $deliveryLastRun);
    // Получение данных о заказах
    $ordersInfo = $this->getOrdersInfo();
    $result['orders'] = $ordersInfo;
    // Получение данных о платёжной системе
    $paymentSystem = PaymentSystem::getPaymentSystem();
    $result['payment_system'] = $paymentSystem->getAdminInfoForView();
    // Получение данных о ползователях
    $usersInfo = $this->getUsersInfo();
    $result['users_info'] = $usersInfo;
    return $result;
  }

  /**
   * Получить количество записей во всех таблицах сервиса
   * @return array Количество записей в таблицах сервиса
   */
  public function getCountTablesRecords () {
    $result['correction'] = $this->db->getCountRecordsCorrection();
    $result['pay'] = $this->db->getCountRecordsPay();
    $result['purchase'] = $this->db->getCountRecordsPurchase();
    $result['sms'] = $this->db->getCountRecordsSms();
    $result['sms_unknown'] = $this->db->getCountRecordsSmsUnknown();
    $result['sp'] = $this->db->getCountRecordsSp();
    $result['users'] = $this->db->getCountRecordsUsers();
    $result['users_purchase'] = $this->db->getCountRecordsUsersPurchase();
    $result['orders'] = $this->db->getCountRecordsOrders();
    // Получение количество записей в таблице текущей платёжной системе
    $paymentSystem = PaymentSystem::getPaymentSystem();
    $result['payment_system'] = $paymentSystem->getCountRecordsPaymentSystem();
    return $result;
  }

  /**
   * Получить размер базы данных в мегабайтах
   * @return float Размер базы данных в мегабайтах
   */
  function getSizeDB () {
    $status = $this->db->getStatusDB();
    $size = 0;
    if ($status !== false) {
      foreach ($status as $table) {
        $size += $table["Data_length"] + $table["Index_length"];
      }
    }
    // Переводим байты в мегабайты
    $size = round($size / (1024 * 1024), 2);
    return $size;
  }

  /**
   * Добавить пользователю заказ на услугу вручную
   * @param $id Int ID пользователя
   * @param $day int Количество дней услуги
   * @return bool Результат операции
   */
  public function addOrderManual ($id, $day) {
    $result = false;
    $id = (int)$id;
    $day = (int)$day;
    if (!empty($day) and !empty($id)) {
      if ($day > 30) {
        return false;
      }
      $order = new OrderUser($id);
      $result = $order->addOrder($day);
    }
    return $result;
  }

  /**
   * Возврат заказа
   * @param $uid int ID пользователя
   * @param $oid int ID заказа
   * @return bool Результат операции
   */
  public function returnOrder ($uid, $oid) {
    $result = false;
    $uid = (int)$uid;
    $oid = (int)$oid;
    if (!empty($oid) and !empty($uid)) {
      $order = new OrderUser($uid);
      $result = $order->returnOrder($oid);
    }
    return $result;
  }

  /**
   * Отмана заказа возврата
   * @param $uid int ID пользователя
   * @param $oid int ID заказа
   * @return bool Результат операции
   */
  public function cancelReturnOrder ($uid, $oid) {
    $result = false;
    $uid = (int)$uid;
    $oid = (int)$oid;
    if (!empty($oid) and !empty($uid)) {
      $order = new OrderUser($uid);
      $result = $order->cancelReturnOrder($oid);
    }
    return $result;
  }

  /**
   * Начислить компенсацию всем пользователям с активированными аккаунтами в указанный момент
   * @param $date string Дата в строковом формате, для поиска запущенных аккаунтов
   * @param $day int Количество дней компенсации
   * @param $type string Тип компенсации (для отправки сообщения пользователям)
   */
  public function compensation ($date, $day, $type) {
    $result['result'] = false;
    $result['count'] = 0;
    $day = (int)$day;
    if (!empty($type) and !empty($date) and !empty($day)) {
      // Получить список пользователей с запущенными заказами в указанный момент
      $orders = $this->db->getAllRunOrdersToDate($date);
      if (!empty($orders)) {
        foreach ($orders as $order) {
          $orderUser = new OrderUser($order[USER_ID]);
          $orderUser->addOrder($day, $type);
          $result['count']++;
        }
        $result['result'] = true;
      }
      //      var_dump($orders);
    }
    return $result;
  }

  /**
   * Поучить статистическую информацию о заказах
   * @return array Массив со статистическими данными о заказах пользователей, формата:
   */
  private function getOrdersInfo () {
    // Инициализация
    $result = array();
    $result['run'] = 0;
    $result['sum'] = 0;
    $result['sum_paid'] = 0;
    $result['payment_order'] = 0;
    $result['payment_day'] = 0;
    $result['manual_order'] = 0;
    $result['manual_day'] = 0;
    // Получить все заказы
    $orders = $this->db->getAllOrders();
    if (!empty($orders)) {
      foreach ($orders as $order) {
        // Куплено
        if (($order[ORDER_TYPE] > ORDER_ADMIN) and is_null($order[ORDER_RETURN])) {
          $result['payment_order']++;
          $result['payment_day'] += $order[ORDER_DAY];
        }
        // Добавлено администратором
        if (($order[ORDER_TYPE] == ORDER_ADMIN) and is_null($order[ORDER_RETURN])) {
          $result['manual_order']++;
          $result['manual_day'] += $order[ORDER_DAY];
        }
        // Запущенные
        if (!is_null($order[ORDER_RUN]) and is_null($order[ORDER_DONE])) {
          $result['run']++;
        }
      }
    }
    // Получение суммы
    $listPaymentSystem = PaymentSystem::getListPaymentSystems();
    foreach ($listPaymentSystem as $key => $value) {
      $paymentSystem = PaymentSystem::getPaymentSystem($key);
      $result['sum'] += $paymentSystem->getSumReceived();
      $result['sum_paid'] += $paymentSystem->getSumPaid();
    }
    // Форматирование
    $result['sum'] = number_format($result['sum'], 2, ',', '');
    $result['sum_paid'] = number_format($result['sum_paid'], 2, ',', '');
    return $result;
  }

  /**
   * Заблокировать или разблокировать пользователя
   * @param $uid int ID пользователя
   * @param $blocked bool Разблокировка или блокировка (0 или 1)
   * @return bool Результат операции
   */
  public function blocked ($uid, $blocked) {
    $result = $this->db->blockedUser($uid, $blocked);
    // Отсылаем уведомление о блокировки пользователю по email
    if ($result) {
      $mail = new Mail();
      $user = $this->db->getUserById($uid);
      if ($blocked) {
        // Если пользователь заблокирован
        $mail->sendUserBlocked($user);
      } else {
        // Если пользователь разблокирован
        $mail->sendUserUnblocked($user);
      }
    }
    return $result;
  }

  /**
   * Получить данные для вывода о платеже
   * @param $paymentId int ID платежа
   * @param $userId int ID пользователя
   * @return array Массив с данными формата:
   *  ['payment'] - данные платежа из БД
   *  ['url'] - url для возврата
   */
  public function getViewPaymentDetails ($paymentId, $userId) {
    $result = array();
    $paymentId = (int)$paymentId;
    $userId = (int)$userId;
    if (!empty($paymentId) and !empty($userId)) {
      // Получение данных о заказе
      $paymentSystem = PaymentSystem::getPaymentSystem();
      $payment = $paymentSystem->getPaymentInfoHTML($paymentId);
      $result['payment'] = $payment;
      $result['url'] = URL::to('admin/manage_user', array('id' => $userId));
    }
    return $result;
  }

  /**
   * Удалить заказ
   * @param $uid int ID пользователя
   * @param $oid int ID заказа
   * @return bool Результат операции
   */
  public function deleteOrder ($uid, $oid) {
    $result = false;
    if (!empty($oid) and !empty($uid)) {
      $order = new OrderUser($uid);
      $result = $order->deleteOrder($oid);
    }
    return $result;
  }

  /**
   * Возвращает истину, если найден файл update.php
   * @return bool Истина, если найден файл update.php
   */
  public function foundUpdateScript () {
    $result = false;
    $file = $_SERVER['DOCUMENT_ROOT'] . self::UPDATE_FILE;
    if (file_exists($file)) {
      $result = true;
    }
    return $result;
  }

  /**
   * Удалить файл update.php
   */
  public function delUpdateScript () {
    if ($this->foundUpdateScript()) {
      $file = $_SERVER['DOCUMENT_ROOT'] . self::UPDATE_FILE;
      @unlink($file);
    }
  }

  /**
   * Получить общие сведения о пользователях сервиса
   * @return array Общие сведения о пользователях сервиса, формата:
   *  ['users_count'] - всего пользователей
   *  ['activate_count'] - всего активированных пользователей
   *  ['bind_count'] - всего аккаунтов имеющих OrgID
   *  ['have_login_count'] - всего пользователей имеющих доступ к сайту СП
   *  ['blocked_count'] - всего заблокированных пользователей
   */
  private function getUsersInfo () {
    // Инициализация
    $result = array();
    $result['users_count'] = 0;
    $result['activate_count'] = 0;
    $result['bind_count'] = 0;
    $result['have_login_count'] = 0;
    $result['blocked_count'] = 0;
    // Получение списка пользователей
    $users = $this->db->getAllUsers();
    if ($users !== false) {
      $result['users_count'] = count($users);
      foreach ($users as $user) {
        ($user[USER_ACTIVATE]) ? $result['activate_count']++ : null;
        ($user[SP_ID] != 0) ? $result['bind_count']++ : null;
        (!empty($user[USER_SP_LOGIN]) and !empty($user[USER_SP_PASSWORD])) ? $result['have_login_count']++ : null;
        ($user[USER_BLOCKED]) ? $result['blocked_count']++ : null;
      }
    }
    return $result;
  }

  /**
   * Получить данные о шаблонах СМС для вывода
   * @param string $arg Какие шаблоны будут выведены
   * @return array Данные для вывода, формата:
   *  - ['type'] - выбранный тип шаблонов
   *  - ['templates'] - список шаблонов для вывода
   *    - ['class'] - класс для отображения активного/неактивного шаблона
   *    - ['url'] - URL для удаления шаблона
   *    - [...] - получено из БД
   */
  public function getViewTemplates ($arg) {
    // Инициализация
    $result = array();
    // Получаем тип шаблонов к выводу
    switch ($arg) {
      case TPL_USELESS:
        $result['type'] = TPL_USELESS;
        break;
      case TPL_MARK_START:
        $result['type'] = TPL_MARK_START;
        break;
      case TPL_MARK_END:
        $result['type'] = TPL_MARK_END;
        break;
      default :
        $result['type'] = TPL_USEFUL;
        break;
    }
    // Получение информации для вывода
    $templates = $this->db->getTemplatesByType($result['type'], true);
    $result['select'] = $this->getCountTplEachType();
    // Подготовка информации для вывода
    if (!empty($templates)) {
      foreach ($templates as $key => $value) {
        $type = $value[TPL_TYPE];
        $subType = $value[TPL_SUBTYPE];
        $templates[$key][TPL_SUBTYPE] = ToolsSMS::getSubtypeTpl($type, $subType);
        if ($value[TPL_ACTIVE]) {
          $templates[$key]['class'] = 'normal';
        } else {
          $templates[$key]['class'] = 'inactive';
        }
        if (!is_null($value[TPL_LAST_USED])) {
          $templates[$key][TPL_LAST_USED] = strftime('%H:%M %d.%m.%Y', strtotime($value[TPL_LAST_USED]));
        } else {
          $templates[$key][TPL_LAST_USED] = '—';
        }
        $query = array('tid' => $value[TPL_ID]);
        $url = URL::to('admin/delete_tpl', $query);
        $templates[$key]['url'] = $url;
      }
    }
    $result['tpl'] = $templates;
    return $result;
  }

  /**
   * Изменить шаблон
   * @param $tid int ID шаблона
   * @param $tpl string Текст шаблона
   * @param $active int Включен или выключен шаблон
   * @param $description string Описание шаблона
   * @return bool Результат операции
   */
  public function editTpl ($tid, $tpl, $active, $description) {
    $result = false;
    $tid = (int)$tid;
    if (!empty($tid)) {
      $result = $this->db->editTpl($tid, $tpl, $active, $description);
    }
    return $result;
  }

  /**
   * Удалить шаблон
   * @param $tid int ID шаблона
   * @return bool Результат операции
   */
  public function deleteTpl ($tid) {
    $result = false;
    $tid = (int)$tid;
    if (!empty($tid)) {
      $result = $this->db->deleteTpl($tid);
    }
    return $result;
  }

  /**
   * Получить данные для выаоды формы добавления шаблона СМС
   * @param $type int Тип выбранного в данный момент шаблона
   * @return array Массив с данными, формата:
   *  - ['type'] - Список типов шаблонов СМС
   *  - ['subtype'] - Список подтипов шаблонов СМС
   *  - ['json'] - JSON массив подтипов шаблонов СМС
   *  - ['select'] - выбранный в данный момент тип шаблона СМС
   */
  public function getViewAdd ($type) {
    // Инициализация
    $result = array();
    $type = (int)$type;
    $type = $type ? $type : 1;
    // Получение названий шаблонов
    $result['type'] = ToolsSMS::getTypeArr();
    $result['subtype'] = ToolsSMS::getSubtypeArr();
    // Получение выбранного типа шаблона
    $result['select'] = $type;
    // Получение JSON массива
    $result['json'] = 'var subType = ' . json_encode($result['subtype']);
    return $result;
  }

  /**
   * Добавить новый шаблон
   * @param $type int Тип шаблона
   * @param $subtype int Подтип шаблона
   * @param $active int Включен или нет шблон
   * @param $template string Шаблон
   * @param $description string Описание шаблона
   * @return bool Результат операции
   */
  public function addTpl ($type, $subtype, $active, $template, $description) {
    // Инициализация
    $result = false;
    $type = (int)$type;
    $subtype = (int)$subtype;
    $active = (int)$active;
    // Валидация
    if (!empty($type) and !empty($subtype) and !empty($template)) {
      $typeArr = ToolsSMS::getTypeArr();
      $subtypeArr = ToolsSMS::getSubtypeArr();
      if (array_key_exists($type, $typeArr)) {
        if (array_key_exists($subtype, $subtypeArr[$type])) {
          $result = $this->db->addTpl($type, $subtype, $active, $template, $description);
        }
      }
    }
    return $result;
  }

  /**
   * Сброс статистики
   * @return bool Результат операции
   */
  public function statResetAllTpl () {
    $result = $this->db->statResetAllTpl();
    return $result;
  }

  /**
   * Экспорт шаблонов СМС
   */
  public function exportTpl () {
    // Получение всех шаблонов из БД
    $templates = $this->db->getAllTemplates();
    // Подготовка файла для экспорта
    $tmpPath = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_path');
    $fileName = 'smstpl_' . date('dmy') . '.csv';
    $filePath = $tmpPath . '/' . $fileName;
    $file = fopen($filePath, 'w');
    // Создание CSV
    foreach ($templates as $tpl) {
      fputcsv($file, $tpl, ";");
    }
    fclose($file);
    // Передача CSV через браузер
    Kit::fileToBrowser($filePath);
  }

  /**
   * Импортировать шаблоны СМС
   * @param $file array Данные о загружаемом файле из $_FILES
   * @return bool Результат операции
   */
  public function importTpl ($file) {
    // Сохраняем файл
    $tmpPath = $_SERVER['DOCUMENT_ROOT'] . Registry_Request::instance()->get('tmp_path');
    $tmpName = $file['tmp_name'];
    $saveName = $tmpPath . '/' . md5($tmpName) . ".tmp";
    $result = move_uploaded_file($tmpName, $saveName);
    if (!$result) {
      $controller = new Controller_Error();
      $controller->index(__LINE__, __FILE__);
    }
    // Получаем шаблоны из файла
    $templates = array();
    $handle = fopen($saveName, "r"); // todo нет проверки на ошибку
    while (($data = fgetcsv($handle, 0, ";")) !== false) {
      // Проверка на количество столбцов'
      if (count($data) === 8) {
        // Заполнение массива шаблонов
        if (empty($data[7])) {
          $data[7] = null;
        }
        $templates[] = array(
          TPL_ID => (int)$data[0],
          TPL_TYPE => (int)$data[1],
          TPL_SUBTYPE => (int)$data[2],
          TPL_TEMPLATE => $data[3],
          TPL_DESCRIPTION => $data[4],
          TPL_ACTIVE => (int)$data[5],
          TPL_COUNT_USED => (int)$data[6],
          TPL_LAST_USED => $data[7],
        );
      } else {
        $result = false;
        break;
      }
    }
    // Валидация шаблонов
    if ($result) {
      $result = $this->validateTpl($templates);
      if ($result) {
        // Очистка таблицы с шаблонами СМС
        $result = $this->db->delAllTemplates();
        // Сохранение шаблонов СМС
        if ($result) {
          $result = $this->db->importTpl($templates);
        }
      }
    }
    fclose($handle);
    @unlink($saveName);
    return $result;
  }

  /**
   * Валидация импортируемых шаблонов
   * @param $templates array Импортируемые шаблоны
   * @return bool Результат валидации
   */
  private function validateTpl ($templates) {
    $result = true;
    $typeArr = ToolsSMS::getTypeArr();
    $subtypeArr = ToolsSMS::getSubtypeArr();
    foreach ($templates as $tpl) {
      // проверка валидности типа
      if (array_key_exists($tpl[TPL_TYPE], $typeArr)) {
        // проверка валидности подтипа
        if (!array_key_exists($tpl[TPL_SUBTYPE], $subtypeArr[$tpl[TPL_TYPE]])) {
          $result = false;
        }
      } else {
        $result = false;
      }
      // проверка наличия шаблона
      if (empty($tpl[TPL_TEMPLATE])) {
        $result = false;
      }
    }
    return $result;
  }

  /**
   * Получить список типов шаблонов с колчиством каждого типа
   * @return array Список типов шаблонов с колчиством каждого типа
   */
  function getCountTplEachType () {
    $type = ToolsSMS::getTypeArr();
    foreach ($type as $key => $val) {
      $templates = $this->db->getTemplatesByType($key, true);
      $type[$key] = $type[$key] . ' (' . count($templates) . ')';
    }
    return $type;
  }

  /**
   * Изменить способ запроса к сайту СП
   * @param $uid int ID пользователя
   * @param $request int Тип запроса
   * @return bool Результат операции
   */
  public function setUserRequest ($uid, $request) {
    $result = false;
    if (!empty($uid) and !empty($request)) {
      $result = $this->db->setUserRequest($uid, $request);
    }
    return $result;
  }

}