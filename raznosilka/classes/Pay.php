<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Pay.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Pay Описывает платёж как объект
 */
class Pay {
  /**
   * @var string Время платежа в строковом формате
   */
  private $timePay;
  /**
   * @var float Сумма платежа, руб
   */
  private $sum;
  /**
   * @var string Карта с которой поступил платеж
   */
  private $card;
  /**
   * @var string Время создания платежа в строковом формате
   */
  private $timeCreatedPay;
  /**
   * @var SMS[] Массив с найденными СМС
   */
  private $foundSMS = array();
  /**
   * @var int|null ID платежа в БД, если он уже разнесён
   */
  private $idPay = null;
  /**
   * @var null|SMS Прикреплённая СМС к данному платежу, если он уже проставлен
   */
  private $fillingSMS = null;
  /**
   * @var bool Ошибочный платёж или нет
   */
  private $error;
  /**
   * @var null|SMS Выбранная пользователем СМС для проставления платежа
   */
  private $selectSMS = null;

  /**
   * Конструктор класса
   * @param array $pay Массив с информацией о платеже с сайта СП, формата
   *  - [PAY_TIME] - дата и время платежа
   *  - [PAY_SUM] - сумма платежа, руб
   *  - [PAY_CARD_PAYER] - карта с которой был зачислен платёж
   *  - [PAY_CREATED] - платёж создан
   * @throws Exception
   */
  function __construct (array $pay) {
    if (!isset($pay[PAY_TIME]) or !isset($pay[PAY_SUM]) or !isset($pay[PAY_CARD_PAYER]) or !isset($pay[PAY_CREATED])) {
      throw new Exception();
    }
    $this->timePay = $pay[PAY_TIME];
    $this->timeCreatedPay = $pay[PAY_CREATED];
    $this->sum = (float)$pay[PAY_SUM];
    $this->card = (string)$pay[PAY_CARD_PAYER];
  }

  /**
   * Выбрать СМС для проставления оплаты
   * @param $keySms int Номер найденной СМС, которую выбрал пользователь
   */
  function setSelectSms ($keySms) {
    if (!$this->isSelectSms()) {
      $this->selectSMS = $this->foundSMS[$keySms];
      // Сбрасываем данные о найденных СМС
      $this->foundSMS = array();
    }
  }

  /**
   * Очистить список найденных СМС для данного плтатежа
   */
  function eraseFoundSMS () {
    $this->foundSMS = array();
  }

  /**
   * Проверить, выбрана ли СМС для проставления платежа
   * @return bool Выбрана ли СМС для проставления платежа
   */
  function isSelectSms () {
    if (!is_null($this->selectSMS)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Получить статус платежа для анализатора
   * @return int Статус платежа:
   *  - NORMAL - Если платёж не требует выбора от пользователя
   *    (в платееженет СМС для выбора или СМС надёжная)
   *  - WARNING - Если платёж имеет ненадёжную СМС (требующая выбора пользователя)
   *  - ERROR - Если платеж имеетс СМС с комментарием
   */
  function getStatusPayForAnalysis () {
    $status = NORMAL;
    if (!empty($this->foundSMS)) {
      /** @var SMS $sms */
      foreach ($this->foundSMS as $sms) {
        $statusSMS = $sms->getStatusSMSForAnalysis();
        // Присваиваем платежу наихудший статус СМС
        if ($statusSMS > $status) {
          $status = $statusSMS;
        }
      }
    }
    return $status;
  }

  /**
   * Получить статус платежа для редактора
   * @return int Статус платежа:
   *  - INACTIVE - Если платёж отмечен как ошибочный
   *  - NORMAL - Если платёж проставлен и сумма SMS совпадает с суммой указанной УЗ
   *  - WARNING - Если платёж проставлен, но сумма не совпадает с суммой указанной УЗ
   *  - ERROR - Если платеж не проставлен
   */
  function getStatusPayForEditorPurchase () {
    $status = NORMAL;
    // Если платёж отмечен как ошибочные
    if ($this->isError()) {
      $status = INACTIVE;
    } else {
      // Если платёж проставлен
      if ($this->isFilling()) {
        // Если сумма SMS больше, чем указана в платеже
        if ($this->fillingSMS->getDiffSumOfPay() > 0) {
          $status = WARNING;
        }
        // Если сумма SMS меньше, чем указана в платеже
        if ($this->fillingSMS->getDiffSumOfPay() < 0) {
          $status = WARNING;
        }
        // Если SMS содержит сообщение
        if ($this->fillingSMS->isComment()) {
          $status = ERROR;
        }
      } else {
        // Если платёж не проставлен
        $status = ERROR;
      }
    }
    return $status;
  }

  /**
   * Добавить найденную СМС
   * @param SMS $sms Объект с найденной СМС
   */
  public function addFoundSms (SMS $sms) {
    // Задать добавленной SMS разницу в времени между платежом и данной SMS
    if ($sms->isSure()) {
      // Если SMS надёжная
      $diffTime = abs((strtotime($this->timePay) - strtotime($sms->getTimePay())));
    } else {
      // Если SMS не надёжная
      $diffTime = abs((strtotime($this->timePay) - strtotime($sms->getTimeSms())));
    }
    $sms->setDiffTimeOfPay($diffTime);
    $this->foundSMS[] = $sms;
  }

  /**
   * Рассчитать количество баллов соответствия ФИО в SMS и ФИО платильщика (чем больше, тем вероятнее совпадение по ФИО)
   * @param $fioSMS string ФИО из SMS
   * @param $fioPay string ФИО с сайта СП
   * @return int Количество баллов соответствия ФИО в SMS и ФИО платильщика
   */
  function calculatePointsSMS ($fioSMS, $fioPay) {
    $result = 0;
    if (!empty($fioSMS)) {
      // Получаем массивы слов
      $wordsSMS = Kit::strToWordArr($fioSMS);
      $wordsPay = Kit::strToWordArr($fioPay);
      if (!empty($wordsSMS) and !empty($wordsPay)) {
        // Ищем совпадения
        foreach ($wordsSMS as $wordSMS) {
          $wordSMS = mb_strtolower($wordSMS, 'UTF-8');
          foreach ($wordsPay as $wordPay) {
            $wordPay = mb_strtolower($wordPay, 'UTF-8');
            if ($wordSMS == $wordPay) {
              $result += 10;
              break;
            }
          }
        }
      }
      // Получаем массивы инициалов
      $charsSMS = Kit::strToCharArr($fioSMS);
      $charsPay = Kit::strToCharArr($fioPay);
      if (!empty($charsSMS) and !empty($charsPay)) {
        // Ищем совпадения
        foreach ($charsSMS as $charSMS) {
          $charSMS = mb_strtolower($charSMS, 'UTF-8');
          foreach ($charsPay as $charPay) {
            $charPay = mb_strtolower($charPay, 'UTF-8');
            if ($charSMS == $charPay) {
              $result += 1;
              break;
            }
          }
        }
      }
    }
    return $result;
  }

  /**
   * Получить надёжная ли СМС у платежа
   * @return bool Разнесён платёж или нет
   */
  function isSure () {
    if (count($this->foundSMS) == 1) {
      /** @var SMS $sms */
      $sms = $this->foundSMS[0];
      return $sms->isSure();
    }
    return false;
  }

  /**
   * Анализ найденных СМС.
   * Сортировка по дате, выбор надёжной СМС.
   * @param UserPurchase $userPurchase Объект с данными об участнике закупки
   */
  public function analyseSms (UserPurchase $userPurchase) {
    if (!empty($this->foundSMS)) {
      // Сортируем по разнице во времени между платежом и SMS
      usort($this->foundSMS, array($this, 'sortSmsByDiff'));
      // Получаем список надёжных СМС
      $sureSms = array();
      /** @var SMS $sms */
      foreach ($this->foundSMS as $sms) {
        if ($sms->isSure()) {
          $sureSms[] = $sms;
        }
      }
      if (!empty($sureSms)) {
        // Если имеются надёжные СМС, то выбираем самую вероятную
        $this->foundSMS = array($sureSms[0]);
      } else {
        // Если надёжных нет, то ищем самую вероятную по ФИО
        foreach ($this->foundSMS as $sms) {
          $points = $this->calculatePointsSMS($sms->getFio(), $userPurchase->getFio());
          $sms->setPointsFio($points);
        }
        // Отсортировать по баллам
        usort($this->foundSMS, array($this, 'sortSmsByPoints'));
        // Выбрать с наибольшим баллом
        if ($this->foundSMS[0]->isCoincideFio()) {
          $this->foundSMS = array($this->foundSMS[0]);
        } else {
          $this->foundSMS = array();
        }
      }
    }
  }

  /**
   * Получить список ID использованных платежом SMS
   * @return array Массив с ID использованных платежом SMS
   */
  public function getIdUsedSms () {
    $result = array();
    if (!empty($this->foundSMS)) {
      /** @var SMS $sms */
      foreach ($this->foundSMS as $sms) {
        $result[] = $sms->getIdSms();
      }
    }
    return $result;
  }

  /**
   * Проставлен ли платёж
   * @return bool Проставлен ли платёж
   */
  public function isFilling () {
    if (is_null($this->fillingSMS)) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Имеет ли платёж найденные СМС
   * @return bool Имеет ли платёж найденные СМС
   */
  function isHasFoundSms () {
    if (!empty($this->foundSMS)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Проставление и сохранение платежа в БД
   * @param $purchaseId int ID закупки
   * @param $userPurchaseId int ID участника закупки
   */
  public function fillingPay ($purchaseId, $userPurchaseId) {
    // Сохранение платежа в БД
    if ($this->isSelectSms()) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $user = Registry_Request::instance()->get('user');
      // Данные о платеже
      $pay = array();
      $pay[USER_ID] = $user->getUserId();
      $pay[PURCHASE_ID] = $purchaseId;
      $pay[USER_PURCHASE_ID] = $userPurchaseId;
      $pay[PAY_TIME] = $this->getTimePay();
      $pay[PAY_SUM] = $this->getSum();
      $pay[PAY_CARD_PAYER] = $this->getCard();
      $pay[PAY_CREATED] = $this->getTimeCreatedPay();
      $pay[SMS_ID] = $this->getSelectSms()->getIdSms();
      // Добавляем платёж
      $idPay = $db->fillingPay($pay);
      if ($idPay !== false) {
        // Обновление объекта платежа
        $this->selectSMS = null;
        $this->init ($purchaseId, $userPurchaseId);
      }
    }
  }

  /**
   * Получить время платежа в строковом формате
   * @return string Время платежа в строковом формате
   */
  function getTimePay () {
    return $this->timePay;
  }

  /**
   * Получить сумму платежа
   * @return float Сумма платежа, руб
   */
  function getSum () {
    return $this->sum;
  }

  /**
   * Получить карту с которой поступил платеж
   * @return string Карта с которой поступил платеж
   */
  function getCard () {
    return $this->card;
  }

  /**
   * Получить время создания платежа в строковом формате
   * @return string Время создания платежа в строковом формате
   */
  function getTimeCreatedPay () {
    return $this->timeCreatedPay;
  }

  /**
   * Получить выбранную пользователем СМС для проставления платежа
   * @return null|SMS Выбранная пользователем СМС для проставления платежа
   */
  function getSelectSms () {
    return $this->selectSMS;
  }

  /**
   * Получить ID платежа (если платёж уже внесён в БД)
   * @return int|null ID платежа
   */
  function getIdPay () {
    return $this->idPay;
  }

  /**
   * Ошибочный ли платёж
   * @return bool Ошибочный ли платёж
   */
  public function isError () {
    return $this->error;
  }

  /**
   * Инициализация платежей полсле получения их с сайта СП.
   * Определение проставлен ли платёж, отмечен как ошибочный, получение SMS
   * которой проставлен платёж.
   * @param $purchaseId int ID закупки
   * @param $userPurchaseId int ID участника закупки, который заполнил данный платёж
   */
  function init ($purchaseId, $userPurchaseId) {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $user = Registry_Request::instance()->get('user');
    $userId = $user->getUserId();
    // Определение - проставлен ли данный платёж
    $pay = $db->getPay($userId, $purchaseId, $userPurchaseId, $this->timePay, $this->sum, $this->card, $this->timeCreatedPay);
    if ($pay !== false) {
      // Если проставлен
      $this->idPay = $pay[PAY_ID];
      $idSms = $pay[SMS_ID];
      if ($idSms > 0) {
        // Если платёж с прикреплённой СМС
        $this->error = false;
        $sms = $db->getSmsById($idSms);
        // Если SMS привязанная к платежу не найдена, то вывести ошибку
        if ($sms === false) {
          $controller = new Controller_Error();
          $controller->index(__LINE__, __FILE__);
        }
        $this->fillingSMS = new SMS($sms);
        // Задать разницу между суммами платежа и данной SMS
        $site = Site::getSite();
        if ($site->rounding()) {
          $diffSum = round($this->fillingSMS->getSum()) - round($this->getSum());
        } else {
          $diffSum = $this->fillingSMS->getSum() - $this->getSum();
        }
        $this->fillingSMS->setDiffSumOfPay($diffSum);
      } else {
        // Если платёж ошибочный
        $this->error = true;
      }
    }
  }

  /**
   * Получить проставленную СМС
   * @return null|SMS Проставленная СМС
   */
  function getFillingSms () {
    return $this->fillingSMS;
  }

  /**
   * Получить массив найденных СМС
   * @return array Массив найденных СМС
   */
  function getFoundSms () {
    return $this->foundSMS;
  }

  /**
   * Удаление у платежа отметки, о том что он ошибочен
   * @return bool Результат выполнения
   */
  public function errorDelete () {
    $result = false;
    if ($this->isError()) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $result = $db->payErrorDelete($this->getIdPay());
      // Обновляем объект платежа
      if ($result) {
        $this->idPay = null;
        $this->error = null;
      }
    }
    return $result;
  }

  /**
   * Отметить платёж как ошибочный
   * @param $purchaseId int ID закупки
   * @param $userPurchaseId int ID участника закупки
   * @return bool Результат выполнения
   */
  public function errorSet ($purchaseId, $userPurchaseId) {
    $result = false;
    if (!$this->isError() and !$this->isFilling()) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $user = Registry_Request::instance()->get('user');
      // Данные о платеже
      $pay = array();
      $pay[USER_ID] = $user->getUserId();
      $pay[PURCHASE_ID] = $purchaseId;
      $pay[USER_PURCHASE_ID] = $userPurchaseId;
      $pay[PAY_TIME] = $this->getTimePay();
      $pay[PAY_SUM] = $this->getSum();
      $pay[PAY_CARD_PAYER] = $this->getCard();
      $pay[PAY_CREATED] = $this->getTimeCreatedPay();
      $pay[SMS_ID] = 0;
      // Добавляем платёж
      $idPay = $db->addPay($pay);
      // Обновление объекта платежа
      if ($idPay !== false) {
        $result = true;
        $this->idPay = $idPay;
        $this->error = true;
      }
    }
    return $result;
  }

  /**
   * Удаление информации о проставлении данного платежа
   * @return bool Результат удаления информации о проставлении платежа
   */
  public function payDelete () {
    $result = false;
    if ($this->isFilling()) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $result = $db->payDelete($this->getIdPay(), $this->fillingSMS->getIdSms());
      // Обновляем объект платежа
      if ($result) {
        $this->fillingSMS = null;
      }
    }
    return $result;
  }

  /**
   * Удаление информации о проставлении потерянного платежа
   * @return bool Результат удаления информации о проставлении потерянного платежа
   */
  public function lostPayDelete () {
    $result = false;
    if ($this->isFilling()) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $result = $db->payDelete($this->getIdPay(), $this->fillingSMS->getIdSms());
    }
    return $result;
  }

  /**
   * Сортировка массива с СМС по разнице во времени
   * @param SMS $a Первая СМС для сравнения
   * @param SMS $b Вторая СМС для сравнения
   * @return int Результат сравнения
   */
  private function sortSmsByDiff (SMS $a, SMS $b) {
    $a = $a->getDiffTimeOfPay();
    $b = $b->getDiffTimeOfPay();
    if ($a == $b) {
      return 0;
    }
    return ($a < $b) ? -1 : 1;
  }

  /**
   * Сортировка массива с СМС по баллам совпадения ФИО
   * @param SMS $a Первая СМС для сравнения
   * @param SMS $b Вторая СМС для сравнения
   * @return int Результат сравнения
   */
  private function sortSmsByPoints (SMS $a, SMS $b) {
    $a = $a->getPointsFio();
    $b = $b->getPointsFio();
    if ($a == $b) {
      return 0;
    }
    return ($a > $b) ? -1 : 1;
  }

}