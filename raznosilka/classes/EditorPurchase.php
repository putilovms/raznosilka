<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka/EditorPurchase.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class EditorPurchase отвечает за модуль Редактора платежей
 */
class EditorPurchase {
  /**
   * @var Purchase Объект закупки с найденными платежами
   */
  private $purchase;
  /**
   * @var array Список фильтров для отображения а редакторе платежей
   */
  private $filters = array(
    'all' => 'Все',
    'problem' => 'Проблемные',
    'not_filling' => 'Не проставленные'
  );
  /**
   * @var string URL для редиректа
   */
  private $redirectURL;
  /**
   * @var int Код ошибки
   */
  private $error;
  /**
   * @var int Тип запроса к сайту СП, выбранный пользователем
   */
  private $userRequest;
  /**
   * @var PurchaseHelper Получение объекта выбранной закупки
   */
  private $purchaseHelper;
  /**
   * @var Site Содержит объект Site для доступа к данным сайта СП
   */
  private $site;

  /**
   * Конструктор класса
   */
  function __construct () {
    $this->purchaseHelper = new PurchaseHelper();
    // Получить тип запроса к сайту СП
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    $this->userRequest = (int)$userInfo[USER_REQUEST];
    $this->site = Site::getSite();
    // Получение закупки из кэша для обеспечения работы методов управления закупкой
    $purchase = Cache::getPurchaseFromCache();
    if ($purchase instanceof Purchase) {
      $this->purchase = $purchase;
    }
  }

  /**
   * Получить страницу с "Редактором закупок", либо с данные для запроса
   * @param $arg string Агрумент определяющий что будет отображаться
   * @return array Массив с данными для вывода "Редактора закупок", или с информацией
   * для запроса, формата:
   *  - ['info'] - данные для запроса @see PurchaseHelper::getRequestInfoEditorPurchase()
   *  - ['editor'] - данные для вывода "Редактора закупок" @see EditorPurchase::prepareEditorPurchase()
   *  - ['select'] - данные о выбранной закупке @see PurchaseHelper::getSelectPurchaseInfo()
   */
  function getPageEditorPurchase ($arg) {
    // Инициализация
    $info = $this->purchaseHelper->getRequestInfoEditorPurchase($arg);
    $result = array();
    $result['info'] = $info;
    $result['editor'] = array();
    $result['select'] = PurchaseHelper::getSelectPurchaseInfo();
    // Если закупка не выбрана
    if ($result['info']['error'] == PURCHASE_NOT_SELECT) {
      return $result;
    }
    // Пробуем загрузить КЭШ выбранной закупки
    $purchase = Cache::getPurchaseFromCache();
    if ($purchase instanceof Purchase) {
      // Закупка получена из кэша
      $result['info']['cache'] = true;
      $this->purchase = $purchase;
      $result['editor'] = $this->prepareEditorPurchase($arg);
    } else {
      // Если не удалось загрузить КЭШ выбранной закупки
      switch ($this->userRequest) {
        // Запросы к сайту СП при помощи расширения браузера
        case REQUEST_EXTENSIONS: {

          break;
        }
        // Запросы к сайту СП при помощи curl по умолчанию
        default : {
          $purchaseData = $this->purchaseHelper->getPurchaseFromSite($info);
          $result['info'] = $purchaseData['info'];
          // Если массив с закупкой получен
          if ($purchaseData['info']['error'] == ERROR_NONE) {
            $purchase = new Purchase($purchaseData['purchase']);
            if ($purchase instanceof Purchase) {
              $this->setPurchase($purchase);
              $result['editor'] = $this->prepareEditorPurchase($arg);
            } else {
              // Если не удалось получить объект
              $result['info']['error'] = ERROR_OTHER;
            }
          }
          break;
        }
      }
    }
    return $result;
  }

  /**
   * Получить массив для вывода списка закупок
   * @param $arg string Агрумент определяющий что будет отображаться, может иметь следующие значения:
   * - all - все заказы
   * - sms - только заказы с платежами у которых проставлены SMS
   * - nosms - тольео заказы у которых имеются не проставленные платежи
   * @return array|false Массив содержащий данные о закепке для вывода, формата:
   *  - ['url'] - URL к закупке
   *  - [PURCHASE_NAME] - Название закупки
   *  - ['filters'] - список фильтров
   *  - ['view'] - режим вывода
   *  - ['lots'] - Массив с заказами
   *    - [x] - номер заказа
   *      - [...] - @see EditorPurchase::getPurchaseLot()
   *  - ['lost_lots'] - Массив с заказами содержащие потерянные заказы
   *    - [x] - номер заказа
   *      - [...] - @see EditorPurchase::getPurchaseLostLot()
   *  - ['statistic'] - статистика по закупке @see EditorPurchase::getPurchaseStatistic()
   */
  public function prepareEditorPurchase ($arg) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      // Инициализация
      $displayLabel = 1;
      $result['lots'] = array();
      $result['lost_lots'] = array();
      // Информация о закупке
      $result['url'] = $this->purchase->getPurchaseUrl();
      $result[PURCHASE_NAME] = $this->purchase->getPurchaseName();
      // Сводная информация
      $result['statistic'] = $this->getPurchaseStatistic();
      // Информация о режиме отображения
      $result['filters'] = $this->getFiltersForView();
      if (!array_key_exists($arg, $this->filters)) {
        $arg = 'all';
      }
      $result['view'] = $arg;
      // Информация о заказах
      $lots = $this->purchase->getLots();
      if (!empty($lots)) {
        /** @var Lot $lot */
        foreach ($lots as $keyLot => $lot) {
          $result['lots'][$keyLot] = $this->getPurchaseLot($keyLot, $lot, $arg, $displayLabel);
        }
      }
      // Вывод заказов с потерянными платежами
      $lostLots = $this->purchase->getLostLots();
      if (!empty($lostLots)) {
        /** @var Lot $lot */
        foreach ($lostLots as $keyLot => $lot) {
          $result['lost_lots'][$keyLot] = $this->getPurchaseLostLot($keyLot, $lot);
        }
      }
      $result['display_label'] = $displayLabel;
    }
    return $result;
  }

  /**
   * Получить статистику по закупке
   * @return array Массив со статистикой по закупке, формата:
   *  - ['count_active_lots'] - количество активных участников (как на сайте СП)
   *  - ['count_active_orders'] - количество активных товаров в закупке (как на сайте СП)
   *  - ['count_total_money'] - общее количество денег к внесению на сайт учистниками закупок
   *  - ['count_total_found_money'] - общее количество денег которые уже внесены в Разносилку
   *  - ['count_total_put_money'] - общее количество денег которые уже внесены на сайт СП
   */
  function getPurchaseStatistic () {
    $result = array();
    $result['count_active_lots'] = $this->purchase->getCountActiveLots();
    $result['count_active_orders'] = $this->purchase->getCountActiveOrders();
    $countTotalMoney = $this->purchase->getCountTotalMoney();
    $result['count_total_money'] = number_format($countTotalMoney, 2, ',', '');
    $countTotalFoundMoney = $this->purchase->getCountTotalFoundMoney();
    $result['count_total_found_money'] = number_format($countTotalFoundMoney, 2, ',', '');
    $countTotalPutMoney = $this->purchase->getCountTotalPutMoney();
    $result['count_total_put_money'] = number_format($countTotalPutMoney, 2, ',', '');
    return $result;
  }

  /**
   * Получить данные для вывода заказа
   * @param $keyLot int Номер заказа
   * @param Lot $lot Объект с данными о заказе
   * @param $arg string Занчение фильтра
   * @param null|boolean $displayLabel Перезаписываемая переменная, записывает 0, если заказ отображается
   * @return array Данные для вывода заказа, формата:
   *  - [USER_PURCHASE_NAME] - Имя участника закупки
   *  - [USER_PURCHASE_NICK] - Ник участника закупки
   *  - ['url'] - URL к профилю участника закупки
   *  - ['total'] - Сумма к внесению на сайт, руб
   *  - ['total_found'] - Сумма найденная в базе данных (уже внесённая), руб
   *  - ['total_put'] - Сумма внесённая на сайт СП, руб
   *  - ['status'] - Статус заказа:
   *  - ['lost_lot'] - является ли заказ потерянным
   *  - ['active'] - bool Активен ли данный заказ
   *  - ['specified'] - Указал ли участник закупки оплаты для данного заказа, если должен был
   *      (т.е. у участника закупки есть сумма к оплате). Если не должен был
   *      указывать оплат, и их нет, то возвращается true.
   *  - ['comment_org'] - Комментарий организатора
   *  - ['comments'] - Массив с комментариями участника закупки
   *    - [x] - комментарий участника
   *  - ['diff_sum_total'] - Разница между суммой которую должен сдать участник и суммой найденной
   *      Разносилкой  (отформатированная строка)
   *  - ['diff_sum_total_plain'] - Разница между суммой которую должен сдать участник и суммой
   *      найденной Разносилкой  (число)
   *  - ['class_diff_sum_total'] - класс для блока с найденной Разносилкой суммы (normal, warning, error)
   *  - ['class_diff_sum_total_found'] - класс для блока с внесённой суммой на сайт СП (normal, error)
   *  - ['corrections'] - Массив с корректировками
   *    - [x] - номер корректировки
   *      - [CORRECTION_ID] - ID корректировки
   *      - [CORRECTION_COMMENT] - Комментарий для корректировки
   *      - [CORRECTION_SUM] - Сумма корректировки, руб
   *  - ['pays'] - Массив с платежами
   *    - [x] - номер платежа
   *      - [PAY_TIME] - время платежа
   *      - [PAY_SUM] - сумма платежа, руб
   *      - [PAY_CARD_PAYER] - номер карты плательщика
   *      - ['filling'] - bool Проставлен ли платёж
   *      - ['error'] - bool Ошибочный ли платёж
   *      - ['status'] - статус платежа
   *      - ['lost_pay'] - является ли платёж потерянным
   *      - ['filling_sms'] - Массив с проставленной СМС
   *        - [SMS_ID] - ID проставленной СМС
   *        - ['time'] - Наиболее точное время СМС
   *        - [SMS_SUM_PAY] - Сумма платежа в СМС, руб
   *        - ['payer'] - ФИО или номер карты плательщика
   *        - ['status'] - Статус проставленной СМС
   *        - [SMS_COMMENT] - Комментарий содержащийся в СМС
   *        - ['diff_sum'] - Разница между суммой платежа и SNS (отформатированная строка)
   *        - ['diff_sum_plain'] - Разница между суммой платежа и SNS (число)
   *  - ['cmd'] - команды для управления заказом
   *    - [x] - номер платежа
   *      - ['error_set'] - отметить платёж как ошибочный
   *      - ['error_del'] - удалить отметку у платежма, о его ошибочности

   */
  function getPurchaseLot ($keyLot, Lot $lot, $arg, &$displayLabel = null) {
    // Инициализация
    $result = array();
    // Получение заказа
    $tags = $lot->getTagsLot();
    $result['filter_tag'] = implode(' ', $tags);
    if (in_array($arg, $tags)) {
      $result['display'] = "";
      $displayLabel = 0;
    } else {
      $result['display'] = "hide";
    }
    // Информация о участнике закупки
    $userPurchase = $lot->getUserPurchase();
    $result[USER_PURCHASE_NAME] = $userPurchase->getFio();
    $result[USER_PURCHASE_NICK] = $userPurchase->getNick();
    $result['url'] = $userPurchase->getUrl();
    // Информация о заказе
    $total = $lot->getTotal();
    $result['total'] = number_format($total, 2, ',', '');
    $totalFound = $lot->getTotalFound();
    $result['total_found'] = number_format($totalFound, 2, ',', '');
    $totalPut = $lot->getTotalPut();
    $result['total_put'] = number_format($totalPut, 2, ',', '');
    $lotStatus = $lot->getStatusLotForEditorPurchase();
    // $lotStatus = 2;
    $result['status'] = $this->statusToClass($lotStatus);
    $result['lost_lot'] = false;
    // Информация о разнице в суммах
    $diffSumOfTotal = $lot->getDiffSumOfTotal();
    $result['diff_sum_total'] = ($diffSumOfTotal > 0 ? "+" : "–") . number_format(abs($diffSumOfTotal), 2, ',', '');
    $result['diff_sum_total_plain'] = $diffSumOfTotal;
    $result['class_diff_sum_total'] = 'normal';
    if ($diffSumOfTotal > 0) {
      $result['class_diff_sum_total'] = 'warning';
    }
    if ($diffSumOfTotal < 0) {
      $result['class_diff_sum_total'] = 'error';
    }
    $diffSumOfTotalFound = $lot->getDiffSumOfTotalFound();
    $result['class_diff_sum_total_found'] = 'normal';
    if ($diffSumOfTotalFound != 0) {
      $result['class_diff_sum_total_found'] = 'error';
    }
    // Информация о корректировках
    $corrections = $lot->getCorrections();
    $result['corrections'] = array();
    if (!empty($corrections)) {
      /** @var Correction $correction */
      foreach ($corrections as $keyCorrection => $correction) {
        $result['corrections'][$keyCorrection][CORRECTION_ID] = $correction->getCorrectionId();
        $correctionComment = $correction->getCorrectionComment();
        $result['corrections'][$keyCorrection][CORRECTION_COMMENT] = empty($correctionComment) ? '—' : $correctionComment;
        $correctionSum = $correction->getCorrectionSum();
        $result['corrections'][$keyCorrection][CORRECTION_SUM] = number_format($correctionSum, 2, ',', '');
        // Команда удаления корректировки
        $result['cmd']['corrections'][$keyCorrection]['correction_del'] = $this->getRequestCorrectionDel($keyLot, $keyCorrection);
      }
    }
    // Информация о платежах
    $result['active'] = $lot->isActiveLot();
    $result['specified'] = $lot->isSpecifiedPays();
    $pays = $lot->getPays();
    if (!empty($pays)) {
      foreach ($pays as $keyPay => $pay) {
        $result['pays'][$keyPay]['lost_pay'] = false;
        $result['pays'][$keyPay][PAY_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($pay->getTimePay()));
        $result['pays'][$keyPay][PAY_CREATED] = strftime('%H:%M %d.%m.%Y', strtotime($pay->getTimeCreatedPay()));
        $result['pays'][$keyPay][PAY_SUM] = number_format($pay->getSum(), 2, ',', '');
        $result['pays'][$keyPay][PAY_CARD_PAYER] = sprintf("%04d", $pay->getCard());
        $result['pays'][$keyPay]['filling'] = $pay->isFilling();
        $result['pays'][$keyPay]['error'] = $pay->isError();
        // Команда чтобы удалить отметку об ошибочности
        if ($pay->isError()) {
          $result['cmd']['pays'][$keyPay]['error_del'] = $this->getRequestErrorDel($keyLot, $keyPay);
        }
        $payStatus = $pay->getStatusPayForEditorPurchase();
        // $payStatus = 2;
        $result['pays'][$keyPay]['status'] = $this->statusToClass($payStatus);
        // Получаем проставленную СМС
        $fillingSms = $pay->getFillingSms();
        if ($fillingSms instanceof SMS) {
          /** @var SMS $fillingSms */
          $result['pays'][$keyPay]['filling_sms'][SMS_ID] = $fillingSms->getIdSms();
          $result['pays'][$keyPay]['filling_sms']['time'] = strftime('%H:%M %d.%m.%Y', strtotime($fillingSms->getTime()));
          $smsSumPay = $fillingSms->getSum();
          $result['pays'][$keyPay]['filling_sms'][SMS_SUM_PAY] = number_format($smsSumPay, 2, ',', '');
          $result['pays'][$keyPay]['filling_sms'][SMS_CARD_PAYER] = $fillingSms->getCardForView();
          $result['pays'][$keyPay]['filling_sms'][SMS_FIO] = $fillingSms->getFioForView();
          $result['pays'][$keyPay]['filling_sms']['status'] = $this->statusToClass($payStatus);
          $result['pays'][$keyPay]['filling_sms'][SMS_COMMENT] = $fillingSms->getComment();
          // Разница между суммой платежа и SMS
          $diffSumOfPay = $fillingSms->getDiffSumOfPay();
          $result['pays'][$keyPay]['filling_sms']['diff_sum'] = ($diffSumOfPay > 0 ? "+" : "–") . number_format(abs($diffSumOfPay), 2, ',', '');
          $result['pays'][$keyPay]['filling_sms']['diff_sum_plain'] = $diffSumOfPay;
          // Команда для удаления проставленной оплаты
          $result['cmd']['pays'][$keyPay]['pay_del'] = $this->getRequestPayDel($keyLot, $keyPay);
        }
        // Команда чтобы отметить платёж как ошибочный
        if (!$pay->isFilling()) {
          $result['cmd']['pays'][$keyPay]['error_set'] = $this->getRequestErrorSet($keyLot, $keyPay);
        }
      }
    }
    // Комментарий к оплате
    $result['comment_pay'] = $lot->getCommentPay();
    // Комментарий организатора
    $result['comment_org'] = $lot->getCommentOrg();
    // Комментарии участника закупки
    $result['comments'] = array();
    $orders = $lot->getOrders();
    if (!empty($orders)) {
      /** @var Order $order */
      foreach ($orders as $order) {
        $comment = $order->getComment();
        if (!empty($comment)) {
          $result['comments'][] = $comment;
        }
      }
    }
    // Обновить сумму на сайте СП
    $result['cmd']['update_sum'] = $this->getRequestUpdateSum($keyLot, $this->purchase->getPurchaseId(), $userPurchase->getUserPurchaseId(), $totalFound);
    return $result;
  }

  /**
   * Получить потерянный заказ
   * @param $keyLot int Номер лота
   * @param $lot Lot Объект содержащий потерянный заказ
   * @return array Массив содержащий данные для вывода потерянного заказа, формата:
   *  - [USER_PURCHASE_NAME] - Имя участника закупки
   *  - [USER_PURCHASE_NICK] - Ник участника закупки
   *  - ['url'] - URL к профилю участника закупки
   *  - ['status'] - Статус заказа:
   *  - ['lost_lot'] - является ли заказ потерянным
   *  - ['pays'] - Массив с потерянными платежами
   *    - [x] - номер платежа
   *      - [PAY_TIME] - время платежа
   *      - [PAY_SUM] - сумма платежа, руб
   *      - [PAY_CARD_PAYER] - номер карты плательщика
   *      - ['filling'] - bool Проставлен ли платёж
   *      - ['status'] - статус платежа
   *      - ['lost_pay'] - является ли платёж потерянным
   *      - ['filling_sms'] - Массив с проставленной СМС
   *        - [SMS_ID] - ID проставленной СМС
   *        - ['time'] - Наиболее точное время СМС
   *        - [SMS_SUM_PAY] - Сумма платежа в СМС, руб
   *        - ['payer'] - ФИО или номер карты плательщика
   *        - ['status'] - Статус проставленной СМС
   *        - [SMS_COMMENT] - Комментарий содержащийся в СМС
   *        - ['diff_sum'] - Разница между суммой платежа и SNS (отформатированная строка)
   *        - ['diff_sum_plain'] - Разница между суммой платежа и SNS (число)
   */
  function getPurchaseLostLot ($keyLot, Lot $lot) {
    $result = array();
    // Информация о участнике закупки
    $userPurchase = $lot->getUserPurchase();
    $result[USER_PURCHASE_NAME] = $userPurchase->getFio();
    $result[USER_PURCHASE_NICK] = $userPurchase->getNick();
    $result['url'] = $userPurchase->getUrl();
    $result['status'] = $this->statusToClass(INACTIVE);
    $result['lost_lot'] = true;
    $result['active'] = true;
    $result['specified'] = true;
    $result['filter_tag'] = "";
    $result['display'] = "";
    // Информация о платежах
    $pays = $lot->getPays();
    if (!empty($pays)) {
      foreach ($pays as $keyPay => $pay) {
        $result['pays'][$keyPay]['lost_pay'] = true;
        $result['pays'][$keyPay][PAY_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($pay->getTimePay()));
        $result['pays'][$keyPay][PAY_CREATED] = strftime('%H:%M %d.%m.%Y', strtotime($pay->getTimeCreatedPay()));
        $result['pays'][$keyPay][PAY_SUM] = number_format($pay->getSum(), 2, ',', '');
        $result['pays'][$keyPay][PAY_CARD_PAYER] = sprintf("%04d", $pay->getCard());
        $result['pays'][$keyPay]['filling'] = $pay->isFilling();
        $result['pays'][$keyPay]['status'] = $this->statusToClass(INACTIVE);
        // Получаем проставленную СМС
        $fillingSms = $pay->getFillingSms();
        if ($fillingSms instanceof SMS) {
          /** @var SMS $fillingSms */
          $result['pays'][$keyPay]['filling_sms'][SMS_ID] = $fillingSms->getIdSms();
          $result['pays'][$keyPay]['filling_sms']['time'] = strftime('%H:%M %d.%m.%Y', strtotime($fillingSms->getTime()));
          $smsSumPay = $fillingSms->getSum();
          $result['pays'][$keyPay]['filling_sms'][SMS_SUM_PAY] = number_format($smsSumPay, 2, ',', '');
          $result['pays'][$keyPay]['filling_sms'][SMS_CARD_PAYER] = $fillingSms->getCardForView();
          $result['pays'][$keyPay]['filling_sms'][SMS_FIO] = $fillingSms->getFioForView();
          $result['pays'][$keyPay]['filling_sms']['status'] = $this->statusToClass(INACTIVE);
          $result['pays'][$keyPay]['filling_sms'][SMS_COMMENT] = $fillingSms->getComment();
          // Разница между суммой платежа и SMS
          $diffSumOfPay = $fillingSms->getDiffSumOfPay();
          $result['pays'][$keyPay]['filling_sms']['diff_sum'] = ($diffSumOfPay > 0 ? "+" : "–") . number_format(abs($diffSumOfPay), 2, ',', '');
          $result['pays'][$keyPay]['filling_sms']['diff_sum_plain'] = $diffSumOfPay;
          // Команда для удаления проставленной оплаты
          $result['cmd']['pays'][$keyPay]['lost_pay_del'] = $this->getRequestLostPayDel($keyLot, $keyPay);
        }
      }
    }
    return $result;
  }

  /**
   * Преобразовать код статуса в класс соотвествующий коду
   * @param $status int Код статуса заказа, платежа или СМС
   * @return string Класс соотвествующий коду
   */
  function statusToClass ($status) {
    switch ($status) {
      case INACTIVE :
        $result = 'inactive';
        break;
      case WARNING :
        $result = 'warning';
        break;
      case ERROR :
        $result = 'error';
        break;
      default:
        $result = 'normal';
        break;
    }
    return $result;
  }

  /**
   * Получить список фильтров для вывода с количеством лотов
   * @return array Список фильтров с количеством лотов
   */
  function getFiltersForView () {
    $tags = $this->purchase->getCountTags();
    $filters = $this->filters;
    foreach ($filters as $filterKey => $filter) {
      if (isset($tags[$filterKey])) {
        $filters[$filterKey] .= " ({$tags[$filterKey]})";
      } else {
        $filters[$filterKey] .= " (0)";
      }
    }
    return $filters;
  }

  /**
   * Получить массив с фильтрами
   * @return array Массив с фильтрами
   */
  function getFilters () {
    $filters = $this->filters;
    return $filters;
  }

  /**
   * Обработка команды удаления ошибочного платежа
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @return bool Результат опреации
   */
  public function payErrorDelete ($lotNumber, $payNumber) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber) and Kit::isInt($payNumber)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        $lot = $lots[$lotNumber];
        /** @var Pay[] $pays */
        $pays = $lot->getPays();
        $pay = $pays[$payNumber];
        // Удаление платежа
        $result = $pay->errorDelete();
      }
    }
    return $result;
  }

  /**
   * Удаление платежа
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @return bool Результат опреации
   */
  public function payDelete ($lotNumber, $payNumber) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber) and Kit::isInt($payNumber)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        $lot = $lots[$lotNumber];
        /** @var Pay[] $pays */
        $pays = $lot->getPays();
        $pay = $pays[$payNumber];
        // Удалить платёж
        $result = $pay->payDelete();
      }
    }
    return $result;
  }

  /**
   * Подготовка параметров для поиска SMS
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @return bool URL для поиска SMS
   */
  function initSearchSMS ($lotNumber, $payNumber) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber) and Kit::isInt($payNumber)) {
        $refererURL = Kit::getRefererURL();
        if (!empty($refererURL)) {
          $regSess = Registry_Session::instance();
          $regSess->set('editorPurchaseURL', $refererURL, true);
          // Подготовка параметров
          /** @var Lot[] $lots */
          $lots = $this->purchase->getLots();
          $lot = $lots[$lotNumber];
          /** @var Pay[] $pays */
          $pays = $lot->getPays();
          $pay = $pays[$payNumber];
          // Получение URL для поиска SMS
          $datetime = $pay->getTimePay();
          $card = $pay->getCard();
          $sum = $pay->getSum();
          $query = Search::getQuery($datetime, $card, $sum, $lotNumber, $payNumber);
          $url = URL::to('purchase/search', $query);
          $this->setRedirectURL($url);
          $result = true;
        }
      }
    }
    return $result;
  }

  /**
   * Проставить платёж выбранной СМС
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @param $idSms int ID SMS которой будет проставлен платёж
   * @return bool Результат проставления
   */
  public function payFilling ($lotNumber, $payNumber, $idSms) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($idSms) and Kit::isInt($lotNumber) and Kit::isInt($payNumber)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        $lot = $lots[$lotNumber];
        /** @var Pay[] $pays */
        $pays = $lot->getPays();
        $pay = $pays[$payNumber];
        // Если платёж не проставлен
        if (!$pay->isFilling() and !$pay->isError()) {
          // Получить SMS
          $db = new DataBase(Registry_Request::instance()->get('db'));
          $sms = $db->getSmsById($idSms);
          if ($sms !== false) {
            $smsObj = new SMS($sms);
            // Если SMS не использована
            if (!$smsObj->isUsed()) {
              $purchaseId = $this->purchase->getPurchaseId();
              $userPurchaseId = $lot->getUserPurchase()->getUserPurchaseId();
              // Проставить платёж
              $pay->eraseFoundSMS();
              $pay->addFoundSms($smsObj);
              $pay->setSelectSms(0);
              $pay->fillingPay($purchaseId, $userPurchaseId);
              $result = true;
            }
          }
        }
      }
    }
    // Получить URL для редиректа
    $this->setRedirectURL(Search_Pay::getRedirectUrl());
    return $result;
  }

  /**
   * Задать адрес для редиректа
   * @param $url string Адрес для редиректа
   */
  function setRedirectURL ($url) {
    $this->redirectURL = $url;
  }

  /**
   * Получить адрес для редиректа
   * @return string Адрес для редиректа
   */
  function getRedirectURL () {
    // Если URL для редиректа ещё не задан
    if (!isset($this->redirectURL)) {
      $url = Kit::getRefererURL();
      $this->setRedirectURL($url);
    }
    return $this->redirectURL;
  }

  /**
   * Обработка команды отметки платежа как ошибочного
   * @param $lotNumber int Номер лота
   * @param $payNumber int Номер платежа
   * @return bool Результат опреации
   */
  function payErrorSet ($lotNumber, $payNumber) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber) and Kit::isInt($payNumber)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        $lot = $lots[$lotNumber];
        /** @var Pay[] $pays */
        $pays = $lot->getPays();
        $pay = $pays[$payNumber];
        // Отметить платёж как ошибочный
        $purchaseId = $this->purchase->getPurchaseId();
        $userPurchaseId = $lot->getUserPurchase()->getUserPurchaseId();
        $result = $pay->errorSet($purchaseId, $userPurchaseId);
      }
    }
    return $result;
  }

  /**
   * Обработка команды удаления корректировки
   * @param $lotNumber int Номер лота
   * @param $correctionNumber int Номер корректировки
   * @return bool Результат выполнения операции
   */
  function correctionDelete ($lotNumber, $correctionNumber) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber) and Kit::isInt($correctionNumber)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        $lot = $lots[$lotNumber];
        $result = $lot->correctionDelete($correctionNumber);
      }
    }
    return $result;
  }

  /**
   * Обработка команды добавления корректировки
   * @param $lotNumber int Номер лота
   * @param $sum float Сумма корректировки
   * @param $comment string Комментарий для корректировки
   * @return bool Результат выполнения операции
   */
  function correctionAdd ($lotNumber, $sum, $comment) {
    $result = false;
    $sum = (float)$sum;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lotNumber) and ($sum != 0)) {
        /** @var Lot[] $lots */
        $lots = $this->purchase->getLots();
        $lot = $lots[$lotNumber];
        // Добавить корректировку
        $purchaseId = $this->purchase->getPurchaseId();
        $result = $lot->addCorrection($purchaseId, $sum, $comment);
      }
    }
    return $result;
  }

  /**
   * Получить код ошибки
   * @return int|null Код ошибки
   */
  function getError () {
    return $this->error;
  }

  /**
   * Удаление потерянного платежа
   * @param $lostLotNumber int Номер потерянного лота
   * @param $lostPayNumber int Номер потерянного платежа
   * @return bool Результат опреации
   */
  public function lostPayDelete ($lostLotNumber, $lostPayNumber) {
    $result = false;
    if ($this->purchase instanceof Purchase) {
      if (Kit::isInt($lostLotNumber) and Kit::isInt($lostPayNumber)) {
        $result = $this->purchase->lostPayDelete($lostLotNumber, $lostPayNumber);
      }
    }
    return $result;

  }

  /**
   * Перекодировка данных для вывода "Редактора закупок" через JS
   * @param $editor array Массив с закупкой для перекодировки в JSON
   * @return string Строка с JSON объектом в переменной JSON_REQUEST, для использования в JS
   */
  public function getJsonPageEditorPurchase (array $editor) {
    $result = json_encode($editor);
    $result = 'var ' . PAGE_DATA_JS . ' = ' . $result . ';';
    return $result;
  }

  /**
   * Задать закупку для которой будет выведен "Редактор закупок"
   * @param $purchase Purchase Объект с закупокй
   */
  function setPurchase (Purchase $purchase) {
    $this->purchase = $purchase;
    // Сохраняем закупку в КЭШ
    Cache::savePurchase($this->purchase);
  }

  /**
   * Получить команду для отметки платежа как ошибочного
   * @param $keyLot int Номер лота
   * @param $keyPay int Номер платежа
   * @return string Команда для отметки платежа как ошибочного
   */
  private function getRequestErrorSet ($keyLot, $keyPay) {
    $result = Command::CMD_ERROR_SET;
    $result = sprintf($result, $keyLot, $keyPay);
    return $result;
  }

  /**
   * Получить команду для удаления отметки ошибочности платежа
   * @param $keyLot int Номер лота
   * @param $keyPay int Номер платежа
   * @return string Команда для удаления отметки ошибочности платежа
   */
  private function getRequestErrorDel ($keyLot, $keyPay) {
    $result = Command::CMD_ERROR_DEL;
    $result = sprintf($result, $keyLot, $keyPay);
    return $result;
  }

  /**
   * Получить команду для обновления проставленной на сайте СП суммы в заказе
   * @param $keyLot int Номер лота
   * @param $purchaseId int ID закупки
   * @param $userPurchaseId int ID пользователя закупки
   * @param $sum float Сумма для проставления на сайт СП
   * @return string|array Информация для запроса к сайту СП для обновления оплаты, формата:
   *  - в зависимости от типа запроса к сайту СП:
   *    - строка с командой к сервису для обновления оплаты
   *    - @see Site::getRequestInfoUpdateSum()
   */
  function getRequestUpdateSum ($keyLot, $purchaseId, $userPurchaseId, $sum) {
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        $result = $this->site->getRequestInfoUpdateSum($keyLot, $purchaseId, $userPurchaseId, $sum);
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        $result = Command::CMD_UPDATE_SUM;
        $result = sprintf($result, $keyLot);
        break;
      }
    }
    return $result;
  }

  /**
   * Получить команду для удаления корректировки
   * @param $keyLot int Номер лота
   * @param $keyCorrection int Номер корректировки
   * @return string Команда для удаления корректировки
   */
  private function getRequestCorrectionDel ($keyLot, $keyCorrection) {
    $result = Command::CMD_CORRECTION_DEL;
    $result = sprintf($result, $keyLot, $keyCorrection);
    return $result;
  }

  /**
   * Получить команду для удаления проставленной оплаты
   * @param $keyLot int Номер лота
   * @param $keyPay int Номер платежа
   * @return string Команда для удаления проставленной оплаты
   */
  private function getRequestPayDel ($keyLot, $keyPay) {
    $result = Command::CMD_PAY_DEL;
    $result = sprintf($result, $keyLot, $keyPay);
    return $result;
  }

  /**
   * Получить команду для удаления потерянной проставленной оплаты
   * @param $keyLot int Номер лота
   * @param $keyPay int Номер платежа
   * @return string Команда для удаления потерянной проставленной оплаты
   */
  private function getRequestLostPayDel ($keyLot, $keyPay) {
    $result = Command::CMD_LOST_PAY_DEL;
    $result = sprintf($result, $keyLot, $keyPay);
    return $result;
  }

}