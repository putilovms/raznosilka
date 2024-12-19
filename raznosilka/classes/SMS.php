<?php
  /**
   * Проект Raznosilka
   * @author Михаил Сергеевич Путилов
   * <@package Raznosilka\SMS.php>
   * @copyright © М. С. Путилов, 2015
   */

  /**
   * Class SMS Описывает объект SMS
   */
  class SMS {
    /**
     * Временная вилка для предупреждения о слишком большом расхождении времени платежа и времени в SMS, минуты
     */
    const FORK_MINUTES = 120; // todo Получать данный параметр из настроек пользователя

    /**
     * @var int ID SMS
     */
    private $idSms;
    /**
     * @var string Время получения SMS на телефон
     */
    private $timeSms;
    /**
     * @var string Время платежа
     */
    private $timePay;
    /**
     * @var float Сумма платежа
     */
    private $sum;
    /**
     * @var string Номер карты плательщика
     */
    private $card;
    /**
     * @var string ФИО плательщика
     */
    private $fio;
    /**
     * @var string Комментарий плательщика
     */
    private $comment;
    /**
     * @var bool Возвращён ли платёж плательщику
     */
    private $return;
    /**
     * @var int ID платежа
     */
    private $idPay;
    /**
     * @var int Разница во времени между платежом и СМС
     */
    private $diffTimeOfPay;
    /**
     * @var int Разница суммы между платежом и СМС
     */
    private $diffSumOfPay;
    /**
     * @var int Количество баллов совпадения ФИО в СМС с ФИО в платеже
     */
    private $points = 0;

    /**
     * Конструктор класса, создаёт объект СМС из массива
     * @param array $sms Массив с данными СМС полученными из БД, формата
     * - [SMS_ID] - ID SMS
     * - [SMS_TIME_SMS] - Время получения SMS
     * - [SMS_TIME_PAY] - Время получения платежа
     * - [SMS_SUM_PAY] - Сумма платежа
     * - [SMS_CARD_PAYER] - Карта плательщика
     * - [SMS_FIO] - ФИО плательщика
     * - [SMS_COMMENT] - комментарий в СМС
     * - [SMS_RETURN] - возвращён ли платёж
     * - [PAY_ID] - ID платежа к которому привязана СМС
     */
    function __construct (array $sms) {
      $this->idSms = $sms[SMS_ID];
      $this->timeSms = $sms[SMS_TIME_SMS];
      $this->timePay = $sms[SMS_TIME_PAY];
      $this->sum = $sms[SMS_SUM_PAY];
      $this->card = (string)$sms[SMS_CARD_PAYER];
      $this->fio = (string)$sms[SMS_FIO];
      $this->comment = (string)$sms[SMS_COMMENT];
      $this->return = (boolean)$sms[SMS_RETURN];
      $this->idPay = $sms[PAY_ID];
    }

    /**
     * Получить статус SMS для анализатора
     * @return int Статус SMS:
     *  - NORMAL - Если СМС надёжная
     *  - WARNING - Если СМС ненадёжная (требующая выбора пользователя)
     *  - ERROR - Если СМС с комментарием
     */
    function getStatusSMSForAnalysis () {
      $status = NORMAL;
      if (!$this->isSure()) $status = WARNING;
      if ($this->isComment()) $status = ERROR;
      return $status;
    }

    /**
     * Получить статус SMS для поиска СМС
     * @return int Статус SMS:
     *  - NORMAL - Если СМС не использована
     *  - WARNING - Если СМС использована
     *  - ERROR - Если СМС с комментарием
     */
    public function getStatusSMSForSearch () {
      $status = NORMAL;
      if ($this->isComment()) $status = ERROR;
      if ($this->isUsed()) $status = WARNING;
      if ($this->isReturn()) $status = INACTIVE;
      return $status;
    }

    /**
     * Надёжная ли данная СМС
     * @return bool Надёжность СМС
     * - true - если СМС надёжна
     * - false - если СМС не надёжна
     */
    public function isSure () {
      if (($this->timePay != '0000-00-00 00:00:00') AND ($this->card != -1)) {
        return true;
      } else {
        return false;
      }
    }

    /**
     * Имеет ли SMS комментарий
     * @return bool Имеет ли SMS комментарий
     */
    public function isComment () {
      if (!empty($this->comment)) {
        return true;
      } else {
        return false;
      }
    }

    /**
     * Получить ID платежа
     * @return int ID платежа
     */
    function getIdPay () {
      return $this->idPay;
    }

    /**
     * Возвращён ли платёж плательщику
     * @return bool Истина если платёж возвращён плательщику
     */
    function isReturn () {
      return $this->return;
    }

    /**
     * Получить комментарий плательщика
     * @return string Комментарий плательщика
     */
    function getComment () {
      return $this->comment;
    }

    /**
     * Получить ФИО плательщика
     * @return string ФИО плательщика
     */
    function getFio () {
      return $this->fio;
    }

    /**
     * Получить карту с которой поступил платеж
     * @return string Карта с которой поступил платеж
     */
    function getCard () {
      return $this->card;
    }

    /**
     * Получить сумму платежа
     * @return float Сумма платежа
     */
    function getSum () {
      return $this->sum;
    }

    /**
     * Получить ID SMS
     * @return int ID SMS
     */
    function getIdSms () {
      return $this->idSms;
    }

    /**
     * Получить время получения SMS на телефон
     * @return string Время получения SMS на телефон
     */
    function getTimeSms () {
      return $this->timeSms;
    }

    /**
     * Получить время платежа
     * @return string Время платежа
     */
    function getTimePay () {
      return $this->timePay;
    }

    /**
     * Имеется ли расхождение по времени
     * @return bool Имеется ли расхождение по времени
     */
    function isDiverOfTime () {
      if ($this->diffTimeOfPay > (self::FORK_MINUTES * 60)) {
        return true;
      } else {
        return false;
      }
    }

    /**
     * Получить разницу во времени между платежом и СМС
     * @return int Разница во времени между платежом и СМС
     */
    function getDiffTimeOfPay () {
      return $this->diffTimeOfPay;
    }

    /**
     * Установить разницу между временем платежа и временем СМС
     * @param $diff int Разница во времени между платежом и СМС
     */
    function setDiffTimeOfPay ($diff) {
      $this->diffTimeOfPay = $diff;
    }

    /**
     * Получить разницу суммы между платежом и СМС
     * @return int Разница суммы между платежом и СМС
     */
    function getDiffSumOfPay () {
      return $this->diffSumOfPay;
    }

    /**
     * Установить разницу между суммой платежа и временем СМС
     * @param $diff int Разница суммы между платежом и СМС
     */
    function setDiffSumOfPay ($diff) {
      $this->diffSumOfPay = $diff;
    }

    /**
     * Получить номер карты плательщика или его ФИО
     * @return string Номер карты плательщика или его ФИО, подготовленный к выводу
     */
    function getPayer () {
      if ($this->card != -1) {
        return sprintf("%04d", $this->card);
      }
      if ($this->fio != '') {
        return mb_convert_case($this->fio, MB_CASE_TITLE, "UTF-8");
      }
      return '—';
    }

    /**
     * Получить ФИО подготовленные для вывода
     * @return string ФИО подготовленные для вывода
     */
    function getFioForView(){
      $result = '';
      if ($this->fio != '') {
        $result = mb_convert_case($this->fio, MB_CASE_TITLE, "UTF-8");
      }
      return $result;
    }

    /**
     * Получить номер карты подготовленный для вывода
     * @return string Номер карты подготовленный для вывода
     */
    function getCardForView(){
      $result = '';
      if ($this->card != -1) {
        $result = sprintf("%04d", $this->card);
      }
      return $result;
    }

    /**
     * Получить наиболее точное время СМС (время платежа или время получения СМС)
     * @return string Наиболее точное время СМС
     */
    function getTime () {
      if ($this->timePay != '0000-00-00 00:00:00') {
        return $this->timePay;
      } else {
        return $this->timeSms;
      }
    }

    /**
     * Использована ли данная SMS в платеже
     * @return bool Истина если СМС использована
     */
    public function isUsed () {
      $result = false;
      if ($this->getIdPay()) {
        $result = true;
      }
      return $result;
    }

    /**
     * Метод для обновления ID платежа к которому привязана SMS
     * @param $idPay int ID платежа
     */
    function setIdPay($idPay) {
      $this->idPay = $idPay;
    }

    /**
     * Задать количество баллов совпадения ФИО в СМС с ФИО в платеже
     * @param $points int Количество баллов совпадения ФИО в СМС с ФИО в платеже
     */
    function setPointsFio ($points) {
      $this->points = $points;
    }

    /**
     * Получить количество баллов совпадения ФИО в СМС с ФИО в платеже
     * @return int Количество баллов совпадения ФИО в СМС с ФИО в платеже
     */
    function getPointsFio(){
      return $this->points;
    }

    /**
     * Совпадает ли ФИО в инфорации о платеже и СМС
     * @return bool Истина если совпадают
     */
    function isCoincideFio(){
      $result = false;
      // 23 балла это полное совпадение по инициалам и полное по имени и отчеству
      // 22 балла это совпадение по 2 из 3 инициалов и полное по имени и отчеству
      if ($this->points >= 22) {
        $result = true;
      }
      return $result;
    }
  }