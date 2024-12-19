<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Analysis.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Analysis Модуль анализатора. Ищет СМС для платежей и подготавливает
 * информацию к выводу.
 */
class Analysis {
  /**
   * @var Purchase Объект закупки с найденными платежами
   */
  private $purchase;
  /**
   * @var PurchaseHelper Получение объекта выбранной закупки
   */
  private $purchaseHelper;
  /**
   * @var int Тип запроса к сайту СП, выбранный пользователем
   */
  private $userRequest;
  /**
   * @var array Массив содержащие все суммы необхоимые для корректной работы скрипта
   */
  private $sum = array();

  /**
   * Конструктор класса.
   * Анализирует выбранную закупки с сохраняет её в реестр сессий.
   */
  function __construct () {
    $this->purchaseHelper = new PurchaseHelper();
    // Получить тип запроса к сайту СП
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    $this->userRequest = (int)$userInfo[USER_REQUEST];
  }

  /**
   * Получить данные для вывода страницы анализатора, либо данные для запроса,
   * для получения страницы анализатора.
   * @return array Массив с данными анализатора подготовленный к выводу
   * или информация для запроса, формата:
   *  ['info'] - информация о запросе @see PurchaseHelper::getRequestInfoSelectPurchase()
   *  ['analysis'] - данные для вывода анализатора @see Analysis::preparePageAnalyzer()
   *  ['select'] - данные о выбранной закупке @see PurchaseHelper::getSelectPurchaseInfo()
   *  ['sum'] - Массив содержащие все суммы необхоимые для корректной работы скрипта
   */
  function getPageAnalyzer () {
    // Инициализация
    $info = $this->purchaseHelper->getRequestInfoAnalysis();
    $result = array();
    $result['info'] = $info;
    $result['analysis'] = array();
    $result['sum'] = array();
    $result['select'] = PurchaseHelper::getSelectPurchaseInfo();
    // Если закупка не выбрана
    if ($result['info']['error'] == PURCHASE_NOT_SELECT) {
      return $result;
    }
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
            $result['analysis'] = $this->preparePageAnalyzer();
            $result['sum'] = $this->getSum();
          } else {
            // Если не удалось получить объект
            $result['info']['error'] = ERROR_OTHER;
          }
        }
        break;
      }
    }
    return $result;
  }

  /**
   * Получить массив закупки с найденными платежами подготовленный для вывода
   * @return array|false Массив закупки с найденными платежами подготовленный для вывода, формата:
   *  - ['url'] - URL к закупке
   *  - [PURCHASE_NAME] - Название закупки
   *  - ['display_label'] - Содержит HTML код для отображения или сокрытия сообщения, о том что
   *      нет платежей требующих вмешательства пользователя
   *  - ['count_found_pays'] - количество найденных платежей в результате работы модие)
   *  - ['lots'] - Массив с заказами
   *    - [x] - номер заказа
   *      - [USER_PURCHASE_NAME] - Имя участника закупки
   *      - [USER_PURCHASE_NICK] - Ник участника закупки
   *      - ['url'] - URL к профилю участника закупки
   *      - ['total'] - Сумма к внесению на сайт, руб
   *      - ['total_found'] - Сумма найденная в базе данных (уже внесённая), руб
   *      - ['total_pre_found'] - Предварительно найденная сумма (уже найдено + выбранные СМС), руб
   *      - ['class_total_pre_found'] - Класс для блока предварительно найденной суммы, normal/error
   *      - ['status'] - Статус заказа:
   *          normal - Если платежи в заказе не требуют выбора от пользователя
   *            (в платежах заказа нет СМС для выбора или СМС надёжная)
   *          warning - Если платёжи в заказе имеется ненадёжная СМС (требующая выбора пользователя)
   *          error - Если платежи в заказе имеется СМС с комментарием
   *      - ['filter_tag'] - Дополнительный статус заказа, необходимый для фильтрации
   *      - ['display'] - Содержит HTML код, для отображения или сокрытия блока с заказом
   *      - ['comment_org'] - Комментарий организатора
   *      - ['comments'] - Массив с комментариями участника закупки
   *      - ['active'] - bool Активен ли данный заказ
   *      - ['specified'] - Указал ли участник закупки оплаты для данного заказа, если должен был
   *          (т.е. у участника закупки есть сумма к оплате). Если не должен был
   *          указывать оплат, и их нет, то возвращается true.
   *      - ['pays'] - Массив с платежами
   *        - [x] - номер платежа
   *          - [PAY_TIME] - время платежа
   *          - [PAY_SUM] - сумма платежа, руб
   *          - [PAY_CARD_PAYER] - номер карты плательщика
   *          - ['filling'] - bool Проставлен ли платёж
   *          - ['error'] - bool Ошибочный ли платёж
   *          - ['status'] - статус платежа
   *          - ['filling_sms'] - Массив с проставленной СМС
   *            - [SMS_ID] - ID проставленной СМС
   *            - ['time'] - Наиболее точное время СМС
   *            - [SMS_SUM_PAY] - Сумма платежа в СМС, руб
   *            - ['payer'] - ФИО или номер карты плательщика
   *            - ['status'] - Статус проставленной СМС
   *            - [SMS_COMMENT] - Комментарий содержащийся в СМС
   *          - ['has_sms'] - имеет ли платёж найденные СМС для выбора пользователем
   *          - ['sms'] - Массив с найденными СМС для выбора пользователем
   *            - [x] - номер СМС
   *              - [SMS_ID] - ID найденной СМС
   *              - ['time'] - Наиболее точное время СМС
   *              - [SMS_SUM_PAY] - Сумма платежа в СМС, руб
   *              - ['payer'] - ФИО или номер карты плательщика
   *              - ['status'] - Статус найденной СМС
   *              - ['checked'] - выбрана ли данная СМС по умолчанию (выбрана если надёжная)
   *                  содержит код HTML для радио кнопки
   *              - [SMS_COMMENT] - Комментарий содержащийся в СМС
   *              - ['colspan'] - содержит количество ячеек в таблице, в зависимости от того,
   *                  содержит ли данная СМС сообщение или нет
   */
  function preparePageAnalyzer () {
    $result = false;
    // Инициализация
    $displayLabel = 1;
    $this->sum = array();
    // Информация о закупке
    $result['url'] = $this->purchase->getPurchaseUrl();
    $result[PURCHASE_NAME] = $this->purchase->getPurchaseName();
    $result['count_found_pays'] = 0;
    // Информация о заказах
    $lots = $this->purchase->getLots();
    if (!empty($lots)) {
      /** @var Lot $lot */
      foreach ($lots as $keyLot => $lot) {
        // Инициализация
        $result['lots'][$keyLot]['filter_tag'] = 'not-found';
        // Информация о участнике закупки
        $userPurchase = $lot->getUserPurchase();
        $result['lots'][$keyLot][USER_PURCHASE_NAME] = $userPurchase->getFio();
        $result['lots'][$keyLot][USER_PURCHASE_NICK] = $userPurchase->getNick();
        $result['lots'][$keyLot]['url'] = $userPurchase->getUrl();
        // Информация о заказе
        $total = $lot->getTotal();
        $result['lots'][$keyLot]['total'] = number_format($total, 2, ',', '');
        $totalFound = $lot->getTotalFound();
        $result['lots'][$keyLot]['total_found'] = number_format($totalFound, 2, ',', '');
        $totalPreFound = $lot->getTotalPreFound();
        $result['lots'][$keyLot]['total_pre_found'] = number_format($totalPreFound, 2, ',', '');
        $result['lots'][$keyLot]['class_total_pre_found'] = 'normal';
        if (round($totalPreFound) != round($total)) { // todo не убираю округление, так как оно связано с JavaScript
          $result['lots'][$keyLot]['class_total_pre_found'] = 'error';
        }
        $lotStatus = $lot->getStatusLotForAnalysis();
        // $lotStatus = 2;
        $result['lots'][$keyLot]['status'] = $this->statusToClass($lotStatus);
        // Формирование JSON массива
        $this->sum['lots'][$keyLot]['total'] = $total;
        $this->sum['lots'][$keyLot]['total_found'] = $totalFound;
        // Показать или скрыть заказ
        if ($lotStatus == NORMAL) {
          $result['lots'][$keyLot]['display'] = "hide";
        } else {
          $result['lots'][$keyLot]['display'] = "";
          $displayLabel = 0;
        }
        // Комментарий к оплате
        $result['lots'][$keyLot]['comment_pay'] = $lot->getCommentPay();
        // Комментарий организатора
        $result['lots'][$keyLot]['comment_org'] = $lot->getCommentOrg();
        // Комментарии участника закупки
        $result['lots'][$keyLot]['comments'] = array();
        $orders = $lot->getOrders();
        if (!empty($orders)) {
          /** @var Order $order */
          foreach ($orders as $order) {
            $comment = $order->getComment();
            if (!empty($comment)) {
              $result['lots'][$keyLot]['comments'][] = $comment;
            }
          }
        }
        // Информация о платежах
        $result['lots'][$keyLot]['active'] = $lot->isActiveLot();
        $result['lots'][$keyLot]['specified'] = $lot->isSpecifiedPays();
        $pays = $lot->getPays();
        if (!empty($pays)) {
          /** @var Pay $pay */
          foreach ($pays as $keyPay => $pay) {
            $result['lots'][$keyLot]['pays'][$keyPay][PAY_TIME] = strftime('%H:%M %d.%m.%Y', strtotime($pay->getTimePay()));
            $result['lots'][$keyLot]['pays'][$keyPay][PAY_CREATED] = strftime('%H:%M %d.%m.%Y', strtotime($pay->getTimeCreatedPay()));
            $result['lots'][$keyLot]['pays'][$keyPay][PAY_SUM] = number_format($pay->getSum(), 2, ',', '');
            $result['lots'][$keyLot]['pays'][$keyPay][PAY_CARD_PAYER] = sprintf("%04d", $pay->getCard());
            $result['lots'][$keyLot]['pays'][$keyPay]['filling'] = $pay->isFilling();
            $result['lots'][$keyLot]['pays'][$keyPay]['error'] = $pay->isError();
            $payStatus = $pay->getStatusPayForAnalysis();
            // $payStatus = 2;
            $result['lots'][$keyLot]['pays'][$keyPay]['status'] = $this->statusToClass($payStatus);
            // Получаем проставленную СМС
            $fillingSms = $pay->getFillingSms();
            if ($fillingSms instanceof SMS) {
              /** @var SMS $fillingSms */
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms'][SMS_ID] = $fillingSms->getIdSms();
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms']['time'] = strftime('%H:%M %d.%m.%Y', strtotime($fillingSms->getTime()));
              $smsSumPay = $fillingSms->getSum();
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms'][SMS_SUM_PAY] = number_format($smsSumPay, 2, ',', '');
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms'][SMS_CARD_PAYER] = $fillingSms->getCardForView();
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms'][SMS_FIO] = $fillingSms->getFioForView();
              $smsStatus = $fillingSms->getStatusSMSForAnalysis();
              // $smsStatus = 2;
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms']['status'] = $this->statusToClass($smsStatus);
              $result['lots'][$keyLot]['pays'][$keyPay]['filling_sms'][SMS_COMMENT] = $fillingSms->getComment();
            }
            // Получаем найденные СМС
            $result['lots'][$keyLot]['pays'][$keyPay]['has_sms'] = $pay->isHasFoundSms();
            $smss = $pay->getFoundSms();
            if (!empty($smss)) {
              // Количество найденных платежей
              $result['count_found_pays']++;
              $result['lots'][$keyLot]['filter_tag'] = 'found';
              /** @var SMS $sms */
              foreach ($smss as $keySms => $sms) {
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms][SMS_ID] = $sms->getIdSms();
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms]['time'] = strftime('%H:%M %d.%m.%Y', strtotime($sms->getTime()));
                $smsSumPay = $sms->getSum();
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms][SMS_SUM_PAY] = number_format($smsSumPay, 2, ',', '');
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms][SMS_CARD_PAYER] = $sms->getCardForView();
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms][SMS_FIO] = $sms->getFioForView();
                $smsStatus = $sms->getStatusSMSForAnalysis();
                // $smsStatus = 2;
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms]['status'] = $this->statusToClass($smsStatus);
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms]['checked'] = ($sms->isSure() ? 'checked' : '');
                $result['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms][SMS_COMMENT] = $sms->getComment();
                // Формирование JSON массива
                $this->sum['lots'][$keyLot]['pays'][$keyPay]['sms'][$keySms][SMS_SUM_PAY] = $smsSumPay;
              }
            }
          }
        }
      }
    }
    // Выводить или нет сообщение о том, что нет СМС требующих вмешательства
    $result['display_label'] = $displayLabel;
    return $result;
  }

  /**
   * Преобразовать код статуса в класс соотвествующий коду
   * @param $status int Код статуса заказа, платежа или СМС
   * @return string Класс соотвествующий коду
   */
  function statusToClass ($status) {
    switch ($status) {
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
   * Перекодировка данных для вывода анализатора в JSON для вывода через JS
   * @param $analysis array Массив с закупкой и найденными СМС для перекодировки в JSON
   * @return string Строка с JSON объектом в переменной JSON_REQUEST, для использования в JS
   */
  public function getJsonPageAnalyzer (array $analysis) {
    $result = json_encode($analysis);
    $result = 'var ' . PAGE_DATA_JS . ' = ' . $result . ';';
    return $result;
  }

  /**
   * Получить массив всех сумм закупки и найденных СМС для использования в JS
   * @return array Массив всех сумм для использования в JS
   */
  public function getSum () {
    $result = $this->sum;
    return $result;
  }

  /**
   * Задать закупку для которой будут найдены СМС
   * @param $purchase Purchase Объект с закупокй
   */
  function setPurchase (Purchase $purchase) {
    $this->purchase = $purchase;
    // Ищем платежи для выбранной закупки
    $this->purchase->findSmsToAllPays();
    // Сохраняем закупку в КЭШ
    Cache::savePurchase($this->purchase);
  }

}