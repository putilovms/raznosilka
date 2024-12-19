<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Cron.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Cron содержит методы для запуска по расписанию
 */
class Cron {

  /**
   * Периодичность рассылки, каждый х час
   */
  const DELIVERY_PERIOD_RUN = 1;
  /**
   * Время запуска хрона
   */
  const CRON_TIME_RUN = '08:00:00';
  /**
   * Количество писем отсылаемых за один раз
   */
  const COUNT_MAIL = 100;

  /**
   * @var SettingsAdmin Настройки Разносилки
   */
  private $settings;
  /**
   * @var DataBase Доступ к базе данных
   */
  private $db;
  /**
   * @var Logs Объект для веделния логов
   */
  private $logs;
  /**
   * @var string Режим работы сайта
   */
  private $mode;
  /**
   * @var int Текущая дата в UNIX формате
   */
  private $currentDate;

  /**
   * Конструктор класса
   */
  function __construct () {
    // Установка записи ошибок хрона в лог в любом режиме запуска сервиса
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    // Инициализация
    $userId = 1;
    $this->settings = new SettingsAdmin($userId);
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    $this->logs = new Logs();
    $this->mode = Registry_Request::instance()->get('mode');
    $this->currentDate = strtotime(date('Y-m-d'));
  }

  /**
   * Запуск хрона
   */
  public function run () {
    // Запускать только если на сайте не ведутся технические работы
    if ($this->mode != 'service') {
      // Инициализация
      $currentTime = time();
      $this->logs->cronLog('Запрос на запуск хрона');
      // Запускаем хрон (раз в сутки в 8:00)
      if ($this->hasCronStart($currentTime)) {
        $this->logs->cronLog('Запуск в режиме обслуживания');
        // Подготовка рассылки писем
        $this->prepareMailing();
        // Удаление устаревших данных
        $this->deleteOldData($currentTime);
      }
      // Запускаем рассылку (каждый час)
      if ($this->hasDeliveryStart($currentTime)) {
        $this->logs->cronLog('Запуск в режиме рассылки');
        // Рассылка писем
        $this->executeMailing(self::COUNT_MAIL);
      }

      // Запускаем архивацию журналов (каждый месяц)
      if ($this->hasLogArchiveStart($currentTime)) {
        $this->logs->cronLog('Запуск в режиме архивации журналов');
        // Архивация и удаление журналов
        $this->logs->archive();
        $this->logs->delAllLogs();
      }
    }
  }

  /**
   * Наступило ли время запуска крона (один раз в сутки)
   * @param $currentTime int Метка текущего времени
   * @return bool Истина если крон можно запускать
   */
  function hasCronStart ($currentTime) {
    $result = false;
    // Расчёт времени следующего запуска
    $cronLastRun = Registry_Request::instance()->get('cron_last_run');
    $cronNextRun = new DateTime($cronLastRun);
    list($hour, $minute, $second) = explode(":", self::CRON_TIME_RUN);
    $cronNextRun->setTime($hour, $minute, $second);
    $cronNextRun->modify('+1 day');
    // Если наступило время запуска крона
    if ($cronNextRun->getTimestamp() <= $currentTime) {
      // Обновляем время последнего запуска крона
      $date = new DateTime();
      $date->setTimestamp($currentTime);
      $this->settings->setSetting('cron_last_run', $date->format(DateTime::ISO8601));
      $this->settings->setSettings();
      $result = true;
    }
    return $result;
  }

  /**
   * Наступило ли время запуска рассылки (через указанное количество часов)
   * @param $currentTime int Метка текущего времени
   * @return bool Истина если рассылку можно запускать
   */
  function hasDeliveryStart ($currentTime) {
    $result = false;
    // Расчёт времени следующего запуска
    $deliveryLastRun = Registry_Request::instance()->get('delivery_last_run');
    $deliveryNextRun = new DateTime($deliveryLastRun);
    $hour = $deliveryNextRun->format('H');
    $deliveryNextRun->setTime($hour, 0, 0);
    $deliveryNextRun->modify('+' . self::DELIVERY_PERIOD_RUN . ' hour');
    // Если наступило время рассылки
    if ($deliveryNextRun->getTimestamp() <= $currentTime) {
      // Обновляем время последней рассылки
      $date = new DateTime();
      $date->setTimestamp($currentTime);
      $this->settings->setSetting('delivery_last_run', $date->format(DateTime::ISO8601));
      $this->settings->setSettings();
      $result = true;
    }
    return $result;
  }

  /**
   * Подготовка данных для рассылки
   */
  function prepareMailing () {
    // Получаем список всех пользователей
    $users = $this->db->getAllUsers();
    if (!empty($users)) {
      // Инициализация
      $deliverys = array();
      // Перебираем пользователей
      foreach ($users as $keyUser => $user) {
        // Рассылка напоминаний если есть согласие и выбран сайт СП
        if ($user[USER_REMINDING] and ($user[SP_ID] > 0)) {
          $this->remindingPurchase($user, $deliverys);
        }
        // Рассылка напоминаний об оплате сервиса
        $this->remindingOrder($user, $deliverys);
      }
      // Добавляем письма в рассылку
      if (!empty($deliverys)) {
        $result = $this->db->addDelivery($deliverys);
        // Лог
        if ($result) {
          $countMail = count($deliverys);
          $this->logs->cronLog("Обслуживание. Добавлено писем в рассылочную базу - {$countMail} шт.");
        } else {
          trigger_error('Ошибка. Не удалось добавить письма в рассылочную базу');
        }
      }
    }
  }

  /**
   * Отослать заданное количество писем из БД
   * @param $countMail int количество писем отсылаемых за один запуск
   */
  function executeMailing ($countMail) {
    //получение списка писем для рассылки
    $deliverys = $this->db->getDelivery($countMail);
    if (!empty($deliverys)) {
      // Инициализация
      $mail = new Mail();
      $deleteList = array();
      // Рассылаем письма
      foreach ($deliverys as $keyDelivery => $delivery) {
        $result = $mail->sendMail($delivery[DELIVERY_EMAIL], $delivery[DELIVERY_SUBJECT], $delivery[DELIVERY_BODY]);
        // Если удалось отослать письмо, то добавляем ID письма в список на удаление
        if ($result) {
          $deleteList[] = $delivery[DELIVERY_ID];
        }
      }
      // Удаляем успешно разосланные письма из БД
      if (!empty($deleteList)) {
        $this->db->delDelivery($deleteList);
        // Лог
        $count = count($deleteList);
        $this->logs->cronLog("Рассылка. Разослано писем - {$count} шт.");
      }
    }
  }

  /**
   * Удаление устаревших данных из базы данных
   * @param $currentTime int Метка текущего времени
   */
  function deleteOldData ($currentTime) {
    // Удаление устаревших неактивированных аккаунтов
    $result = $this->db->deleteAllOldNotActivateUser($currentTime);
    if ($result !== false) {
      if ($result > 0) {
        $this->logs->cronLog("Обслуживание. Удалено устаревших и не активированных учётных записей - {$result} шт.");
      }
    } else {
      trigger_error('Ошибка. Не удалось удалить устаревшие и не активированные учётные записи');
    }
    // Удаление устаревших нераспознанных СМС
    $result = $this->db->deleteAllOldUnknownSms($currentTime);
    if ($result !== false) {
      if ($result > 0) {
        $this->logs->cronLog("Обслуживание. Удалено устаревших нераспознанных SMS - {$result} шт.");
      }
    } else {
      trigger_error('Ошибка. Не удалось удалить устаревшие и не активированные учётные записи');
    }

  }

  /**
   * Наступило ли время запуска архивации журналов
   * @param $currentTime int Метка текущего времени
   * @return bool Истина если архивацию можно запускать
   */
  private function hasLogArchiveStart ($currentTime) {
    $result = false;
    // Расчёт времени следующего запуска
    $logArchiveLastRun = Registry_Request::instance()->get('log_archive_last_run');
    $logArchiveNextRun = new DateTime($logArchiveLastRun);
    $year = $logArchiveNextRun->format('Y');
    $month = $logArchiveNextRun->format('m');
    $logArchiveNextRun->setTime(0, 0, 0);
    $logArchiveNextRun->setDate($year, $month, 1);
    $logArchiveNextRun->modify('+' . self::DELIVERY_PERIOD_RUN . ' month');
    // Если наступило время рассылки
    if ($logArchiveNextRun->getTimestamp() <= $currentTime) {
      // Обновляем время последней рассылки
      $date = new DateTime();
      $date->setTimestamp($currentTime);
      $this->settings->setSetting('log_archive_last_run', $date->format(DateTime::ISO8601));
      $this->settings->setSettings();
      $result = true;
    }
    return $result;
  }

  /**
   * Напомнить о проставлении оплат
   * @param array $user Массив с данными о пользователе для которого рассылаются напоминания
   * @param array $deliverys Массив со всеми напоминаниями, в который будут добавлены напоминания для данного пользователя
   */
  function remindingPurchase (array $user, array &$deliverys) {
    $date = new DateTime();
    $mail = new Mail();
    // Получаем саписок закупок пользователя
    $purchases = $this->db->getAllPurchaseOfUser($user[USER_ID], $user[SP_ID]); // todo возможно стоит оптимизировать запрос, чтобы перебирать меньше данных
    if (!empty($purchases)) {
      $reminding = array();
      $remindingForgot = array();
      // Получаем закупки о которых нужно напомнить
      foreach ($purchases as $keyPurchase => $purchase) {
        // Если у закупки указана дата окончания оплат УЗ
        if ($purchase[PURCHASE_PAY_TO] != '0000-00-00') {
          // Получаем дату напоминания о закупках к проставлению
          $remindingDate = $date->setTimestamp(strtotime($purchase[PURCHASE_PAY_TO]))->modify('+' . $user[USER_FILLING_DAY] . ' day')->getTimestamp();
          // Если сегодня нужно проставлять оплаты
          if ($this->currentDate == $remindingDate) {
            $reminding[] = $purchase;
          }
          // Получаем дату напоминания о непроставленной закупке
          $remindingForgotDate = $date->setTimestamp(strtotime($purchase[PURCHASE_PAY_TO]))->modify('+' . ($user[USER_FILLING_DAY] + 1) . ' day')->getTimestamp();
          // Если сегодня следующий день, после того как закупка должна быть проставлена
          if ($this->currentDate == $remindingForgotDate) {
            $sum = $this->db->getFoundSumPurchase($user[USER_ID], $purchase[PURCHASE_ID]);
            $sum += $this->db->getFoundSumCorrection($user[USER_ID], $purchase[PURCHASE_ID]);
            // Если в закупке не проставлена ни одна оплата
            if ($sum == 0) {
              $remindingForgot[] = $purchase;
            }
          }
        }
      }
      // Если есть закупки о которых нужно напомнить
      if (!empty($reminding)) {
        // Подготовка данных для напоминания
        $delivery[DELIVERY_EMAIL] = $user[USER_EMAIL];
        $delivery[DELIVERY_SUBJECT] = 'Не забудьте проставить оплаты';
        $delivery[DELIVERY_BODY] = $mail->prepareRemindingPurchaseBody($reminding);
        $deliverys[] = $delivery;
      }
      // Если есть закупки в которых забыли проставить оплаты
      if (!empty($remindingForgot)) {
        // Подготовка данных для напоминания
        $delivery[DELIVERY_EMAIL] = $user[USER_EMAIL];
        $delivery[DELIVERY_SUBJECT] = 'Возможно, вы забыли проставить оплаты';
        $delivery[DELIVERY_BODY] = $mail->prepareRemindingForgotPurchaseBody($remindingForgot);
        $deliverys[] = $delivery;
      }
    }
  }

  /**
   * Напомнить об оплате сервиса
   * @param array $user Массив с данными о пользователе для которого рассылаются напоминания
   * @param array $deliverys Массив со всеми напоминаниями, в который будут добавлены напоминания для данного пользователя
   */
  function remindingOrder (array $user, array &$deliverys) {
    $mail = new Mail();
    $order = new OrderUser($user[USER_ID]);
    // Если пользователю уже можно напомнить о продлении услуги
    if ($order->isCanNotifyPay()) {
      // Если начало напоминания совпадает с сегодняшним днём
      $dateNotifyPay = strtotime(strftime("%Y-%m-%d", $order->getDateNotifyPay()));
      if ($this->currentDate == $dateNotifyPay) {
        // Подготовка данных для напоминания
        $payInfo['date_done'] = strftime('%H:%M %d.%m.%Y', $order->getDateDone());
        // Генерация письма
        $delivery[DELIVERY_EMAIL] = $user[USER_EMAIL];
        $delivery[DELIVERY_SUBJECT] = 'Не забудьте оплатить «Разносилку»';
        $delivery[DELIVERY_BODY] = $mail->prepareRemindingPayBody($payInfo);
        $deliverys[] = $delivery;
        // Уведомление
        $url_pay = URL::to('pay');
        $url_paying = URL::to('help/paying');
        $text = "
          <p>Срок предоставления услуги истекает после {$payInfo['date_done']}.</p>
          <p>Вы можете оплатить услугу на странице «<a href='{$url_pay}'>Цены и оплата</a>».</p>
          <p>О том, как оплатить услугу читайте в разделе «<a href='{$url_paying}'>Оплата услуги</a>».</p>
        ";
        $this->db->postMessage(MONEY_MESSAGE, $text, $user[USER_ID]);
      }
    }
  }

}