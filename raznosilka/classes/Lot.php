<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Lot.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Lot Класс содержащий полную информацию о заказе участника
 */
class Lot {
  /**
   * @var string Комментарий организатора
   */
  private $commentOrg = '';
  /**
   * @var string Комментарий к оплате
   */
  private $commentPay = '';
  /**
   * @var float Уже внесено, руб
   */
  private $totalPut = 0.00;
  /**
   * @var float Сумма к внесению участником закупки, руб
   */
  private $total = 0.00;
  /**
   * @var array Массив объектов с товарами
   */
  private $orders = array();
  /**
   * @var Pay[] Массив объектов с платежами
   */
  private $pays = array();
  /**
   * @var UserPurchase Данные об учатнике
   */
  private $userPurchase;
  /**
   * @var array Массив объектов с корректировками
   */
  private $corrections = array();

  /**
   * Создаёт объект описывающий заказ участника
   * @param array $lot Массив полученный с сайта СП и дополненный
   * корректировками, формата:
   *  - ['total_put'] - Уже внесено денег
   *  - ['comment_org'] - Комментарий организатора
   *  - ['user'] - массив участника закупки
   *  - ['pays'] - массив платежей
   *  - ['orders'] - массив заказов
   *  - ['corrections'] - массив корректировок
   * @param bool $test Для тестирования, по умолчанию false
   * @throws Exception
   */
  function __construct (array $lot, $test = false) {
    $this->checkData($lot);
    $this->commentOrg = $lot['comment_org'];
    $this->commentPay = $lot['comment_pay'];
    $this->totalPut = $lot['total_put'];
    // Данные участника
    $this->userPurchase = new UserPurchase($lot['user'], $test);
    // Список платежей
    if (!empty($lot['pays'])) {
      foreach ($lot['pays'] as $pay) {
        $payObj = new Pay($pay);
        $this->pays[] = $payObj;
      }
    }
    // Список товаров
    if (!empty($lot['orders'])) {
      foreach ($lot['orders'] as $order) {
        $orderObj = new Order($order);
        $this->orders[] = $orderObj;
        // Получение суммы к оплате
        if ($orderObj->getState() != 3) {
          $fee = $orderObj->getOrgFee() / 100;
          $delivery = $orderObj->getDelivery();
          $price = $orderObj->getPrice();
          $this->total += ($fee * $price) + $price + $delivery;
        }
      }
    }
    // Скидка
    $this->total -= $lot['discount'];
    // Сумма ЦВЗ
    if ($lot['cvz_status'] == 'yes') {
      $this->total += $lot['cvz_sum'];
    }
    // Корректировки для данного заказа
    if (!empty($lot['corrections'])) {
      foreach ($lot['corrections'] as $correction) {
        $correctionObj = new Correction($correction);
        $this->corrections[] = $correctionObj;
      }
    }
  }

  /**
   * Проверка входящих данных
   * @param $lot array Массив с заказом для проверки
   * @throws Exception
   */
  function checkData (array $lot) {
    if (empty($lot['user'])) {
      throw new Exception("Поле 'user' не содержит данных");
    }
    if (!isset($lot['total_put'])) {
      throw new Exception("Не определено поле 'total_put'");
    }
    if (!isset($lot['comment_org'])) {
      throw new Exception("Не определено поле 'comment_org'");
    }
    if (!isset($lot['discount'])) {
      throw new Exception("Не определено поле 'discount'");
    }
    if (!isset($lot['cvz_status'])) {
      throw new Exception("Не определено поле 'cvz_status'");
    }
    if (!isset($lot['cvz_sum'])) {
      throw new Exception("Не определено поле 'cvz_sum'");
    }
    if (!isset($lot['comment_pay'])) {
      throw new Exception("Не определено поле 'comment_pay'");
    }
  }

  /**
   * Получить статус заказа для анализатора
   * @return int Статус заказа:
   *  - NORMAL - Если платежи в заказе не требуют выбора от пользователя
   *    (в платежах заказа нет СМС для выбора или СМС надёжная)
   *  - WARNING - Если платёжи в заказе имеется ненадёжная СМС (требующая выбора пользователя)
   *  - ERROR - Если платежи в заказе имеется СМС с комментарием
   */
  function getStatusLotForAnalysis () {
    $status = NORMAL;
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        $statusPay = $pay->getStatusPayForAnalysis();
        // Присваиваем заказу наихудший статус платежа
        if ($statusPay > $status) {
          $status = $statusPay;
        }
      }
    }
    return $status;
  }

  /**
   * Получить статус заказа для редактора
   * @return int Статус заказа:
   *  - INACTIVE - если заказ не активный
   *  - NORMAL -
   *  - WARNING - если у заказа имеются не проставленные платежи, сумма найденная Разносилкой больше чем должен участник
   *  - ERROR - сумма найденная Разносилкой меньше чем должен участник
   */
  public function getStatusLotForEditorPurchase () {
    $status = NORMAL;
    // Если заказ не активен
    if (!$this->isActiveLot()) {
      $status = INACTIVE;
    }
    // Если не все платежи проставлены
    if (!$this->isAllFilling()) {
      $status = WARNING;
    }
    // Если найдено Разносилкой больше, чем должен участник
    if ($this->getDiffSumOfTotal() > 0) {
      $status = WARNING;
    }
    // Если найдено Разносилкой меньше, чем должен участник
    if ($this->getDiffSumOfTotal() < 0) {
      $status = ERROR;
    }
    // Если сумма найденная Разносилкой не совпадает с суммой внесённой на сайт СП
    if ($this->getDiffSumOfTotalFound() != 0) {
      $status = ERROR;
    }
    // Если участник не указал ни одной оплаты, хотя должен был
    if (!$this->isSpecifiedPays()) {
      $status = ERROR;
    }
    if ($this->isHasPayCommentInSMS()) {
      $status = ERROR;
    }
    return $status;
  }

  /**
   * Получить список всех состояний для данного заказа
   * @return array Список всех состояний для данного заказа:
   *  - ['problem'] - у данного заказа есть проблемы
   *  - ['not_filling'] - не все платежи проставлены
   *  - ['diff_total_sum'] - имеется разница между суммой которую должен сдать участник и суммой найденной Разносилкой
   *  - ['diff_total_found_sum'] - имеется разница между суммой внесённой на сайт СП и суммой найденной Разносилкой
   *  - ['not_specified'] - участник не указал платежи, хотя должен был
   */
  function getTagsLot () {
    $tags = array();
    $tags[] = 'all';
    // Если не все платежи проставлены
    if (!$this->isAllFilling()) {
      if (!in_array('problem', $tags)) {
        $tags[] = 'problem';
      }
      $tags[] = 'not_filling';
    }
    // Если сумма найденная Разносилкой не совпадает с суммой которую должен сдать участник закупки
    if ($this->getDiffSumOfTotal() != 0) {
      if (!in_array('problem', $tags)) {
        $tags[] = 'problem';
      }
      $tags[] = 'diff_total_sum';
    }
    // Если сумма найденная Разносилкой не совпадает с суммой внесённой на сайт СП
    if ($this->getDiffSumOfTotalFound() != 0) {
      if (!in_array('problem', $tags)) {
        $tags[] = 'problem';
      }
      $tags[] = 'diff_total_found_sum';
    }
    // Если участник не указал ни одной оплаты, хотя должен был
    if (!$this->isSpecifiedPays()) {
      if (!in_array('problem', $tags)) {
        $tags[] = 'problem';
      }
      $tags[] = 'not_specified';
    }
    // Если в заказе платёж проставленный SMS содержащей комментарий
    if ($this->isHasPayCommentInSMS()) {
      if (!in_array('problem', $tags)) {
        $tags[] = 'problem';
      }
      $tags[] = 'sms_comment';
    }
    return $tags;
  }

  /**
   * Имеется ли в заказе платёж проставленный SMS содержащей комментарий
   * @return bool True если имеется платёж просталвенный SMS содержащей комментарий
   */
  function isHasPayCommentInSMS () {
    $result = false;
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        if ($pay->isFilling()) {
          $sms = $pay->getFillingSms();
          if ($sms->isComment()) {
            $result = true;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Получение комментария организатора
   * @return string Комментарий организатора
   */
  function getCommentOrg () {
    return $this->commentOrg;
  }

  /**
   * Получение комментария к оплате
   * @return string Комментарий организатора
   */
  function getCommentPay () {
    return $this->commentPay;
  }

  /**
   * Получить сумму которая уже внесена на сайт, руб
   * @return float Уже внесено, руб
   */
  function getTotalPut () {
    return $this->totalPut;
  }

  /**
   * Задать сумму которая уже внесена на сайт СП
   * @param $totalPut float Сумма которая уже внесена на сайт СП, руб
   */
  function setTotalPut ($totalPut) {
    $this->totalPut = $totalPut;
  }

  /**
   * Получить сумму которую необходимо внести на сайт, руб
   * @return float Сумма к внесению, руб
   */
  function getTotal () {
    return $this->total;
  }

  /**
   * Получить массив платежей
   * @return Pay[] Массив платежей
   */
  public function getPays () {
    return $this->pays;
  }

  /**
   * Получить участника закупки
   * @return UserPurchase Объект с участником закупки
   */
  public function getUserPurchase () {
    return $this->userPurchase;
  }

  /**
   * Активен ли данный заказ
   * @return bool Активен ли данный заказ
   */
  function isActiveLot () {
    if (($this->total == 0) AND (empty($this->pays))) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Указал ли участник закупки оплаты для данного заказа, если должен был
   * (т.е. у участника закупки есть сумма к оплате). Если не должен был
   * указывать оплат, и их нет, то возвращается true.
   * @return bool Указал ли пользователь оплаты для данного заказа, если должен был
   */
  function isSpecifiedPays () {
    if (($this->total != 0) AND (empty($this->pays))) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Получить сумму найденную в базе данных (уже внесённую в Разносилку), руб
   * @return float Сумма найденная в базе данных (уже внесённая в Разносилку), руб
   */
  public function getTotalFound () {
    // Получение сумм уже проставленных платежей
    $totalFound = 0.00;
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        if ($pay->isFilling() and !$pay->isError()) {
          $totalFound += $pay->getFillingSms()->getSum();
        }
      }
    }
    // Получение сумм корректировок
    if (!empty($this->corrections)) {
      /** @var Correction $correction */
      foreach ($this->corrections as $correction) {
        $totalFound += $correction->getCorrectionSum();
      }
    }
    return $totalFound;
  }

  /**
   * Получить предварительно найденную сумму (уже найдено + выбранные СМС), руб
   * @return float Предварительно найденная сумма, руб
   */
  public function getTotalPreFound () {
    $totalPreFound = $this->getTotalFound();
    // Получаем суммы надёжных СМС
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        if ($pay->isSure()) {
          /** @var SMS[] $sms */
          $sms = $pay->getFoundSms();
          $totalPreFound += $sms[0]->getSum();
        }
      }
    }
    return $totalPreFound;
  }

  /**
   * Получить заказы
   * @return array Массив с заказами
   */
  public function getOrders () {
    return $this->orders;
  }

  /**
   * Имеются ли в заказе платежи для разнесения
   * @return bool Результат проверки
   */
  public function isForFilling () {
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        // Проверяем платежи на наличие выбранной СМС для проставления
        if ($pay->isSelectSms()) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Получить сумму к проставлению, руб
   * @return float Сумма к проставлению, руб
   */
  public function getTotalForFilling () {
    $totalFilling = $this->getTotalFound();
    // Получаем суммы надёжных СМС
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        if ($pay->isSelectSms()) {
          $sms = $pay->getSelectSms();
          $totalFilling += $sms->getSum();
        }
      }
    }
    return $totalFilling;
  }

  /**
   * Проставить все платежи в заказе с выбранными СМС
   * @param $purchaseId int ID закупки
   */
  function fillingAllPay ($purchaseId) { // todo что если сохранение в БД завершилось ошибкой
    if (!empty($this->pays)) {
      $userPurchaseId = $this->getUserPurchase()->getUserPurchaseId();
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        if ($pay->isSelectSms()) {
          $pay->fillingPay($purchaseId, $userPurchaseId);
        }
      }
    }
  }

  /**
   * Все ли платежи проставлены (или отмечены как ошибочные) в данном заказе
   * @return bool Возвращает:
   *  - true - если все платежи проставлены или отмечены как ошибочные
   *  - false - если есть непроставленные платежи
   */
  function isAllFilling () {
    $result = true;
    // Перебираем платежи
    if (!empty($this->pays)) {
      /** @var Pay $pay */
      foreach ($this->pays as $pay) {
        if (!$pay->isError()) {
          if (!$pay->isFilling()) {
            return false;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Получить разницу между тем сколько найдено Разносилкой и тем сколько должен участник
   * @return float Разница между тем сколько найдено Разносилкой и тем сколько должен участник
   */
  function getDiffSumOfTotal () {
    $site = Site::getSite();
    if ($site->rounding()) {
      $diffSum = round($this->getTotalFound()) - round($this->getTotal());
    } else {
      $diffSum = $this->getTotalFound() - $this->getTotal();
    }
    return $diffSum;
  }

  /**
   * Получить разницу между тем сколько найдено Разносилкой и тем сколько внесено на сайт СП
   * @return float Разница между тем сколько найдено Разносилкой и тем сколько внесено на сайт СП
   */
  function getDiffSumOfTotalFound () {
    $diffSum = $this->getTotalFound() - $this->getTotalPut();
    return $diffSum;
  }

  /**
   * Получить массив с корректировками для заказа
   * @return array Массив с корректировками для заказа
   */
  function getCorrections () {
    $corrections = $this->corrections;
    return $corrections;
  }

  /**
   * Добавить корректировку
   * @param $purchaseId int ID закупки
   * @param $sum float Сумма корректировки
   * @param $comment string Комментарий корректировки
   * @return bool Результат выполнения операции
   */
  public function addCorrection ($purchaseId, $sum, $comment) {
    $result = false;
    $sum = (float)$sum;
    if ($sum != 0) {
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $user = Registry_Request::instance()->get('user');
      $correction = array();
      $correction[USER_ID] =$user->getUserId();
      $correction[PURCHASE_ID] =$purchaseId;
      $correction[USER_PURCHASE_ID] = $this->getUserPurchase()->getUserPurchaseId();
      $correction[CORRECTION_SUM] = $sum;
      $correction[CORRECTION_COMMENT] = $comment;
      $idCorrection = $db->addCorrection($correction);
      if ($idCorrection !== false){
        // Обновление объекта
        $correctionNew = $db->getCorrectionById($idCorrection);
        $correctionObj = new Correction($correctionNew);
        $this->corrections[] = $correctionObj;
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Удаление корректировки из заказа и базы данных
   * @param $correctionNumber int Номер корректировки в массиве корректировок
   * @return bool Результат выполнения
   */
  public function correctionDelete ($correctionNumber) {
    /** @var Correction[] $corrections */
    $corrections = $this->getCorrections();
    if (!isset($corrections[$correctionNumber])) {
      return false;
    }
    $correction = $corrections[$correctionNumber];
    // Удалить корректировку
    $result = $correction->correctionDelete();
    // Обновление объекта
    if ($result) {
      unset ($this->corrections[$correctionNumber]);
    }
    return $result;
  }

  /**
   * Удалить потерянный платёж
   * @param $lostPayNumber int Номер потерянного платежа
   * @return bool Результат опреации
   */
  public function lostPayDelete ($lostPayNumber) {
    $pay = $this->pays[$lostPayNumber];
    // Удалить потерянный платёж
    $result = $pay->lostPayDelete();
    // Обновляем объект
    if ($result) {
      // Удалить данные об удалённом потерянном платеже
      unset($this->pays[$lostPayNumber]);
    }
    return $result;
  }

}