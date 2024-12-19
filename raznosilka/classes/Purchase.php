<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Purchase.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Purchase Описывает сущность - закупка
 */
class Purchase {
  /**
   * Временная вилка для поиска СМС, часов
   */
  const FORK_HOUR = 24; // todo Получать данный параметр из настроек пользователя
  /**
   * Метод сортировки:
   *  sortUserById - Сравнение по ID
   *  sortUserByNickRusEng - Сравнение по нику пользователя сначала кириллица, потом латинница
   *  sortUserByNickEngRus - Сравнение по нику пользователя сначала латинница, потом кириллица
   */
  const SORT_METHOD = 'sortUserByNickEngRus'; //todo сделать сортировку в зависимости от настроек пользователя

  /**
   * @var int ID закупки
   */
  private $purchaseId;
  /**
   * @var string Имя закупки
   */
  private $purchaseName;
  /**
   * @var string URL закупки
   */
  private $purchaseUrl;
  /**
   * @var Lot[] Массив объектов Lot, содержащих все данные о заказе
   */
  private $lots = array();
  /**
   * @var Lot[] Массив, содержащих все данные о потерянных платежах
   */
  private $lostLots = array();

  /**
   * Конструктор класса
   * @param array $purchaseData Массив с закупкой и данными о закупке полученный с сайта СП
   * @throws Exception
   */
  function __construct (array $purchaseData) {
    // Проверяем формат передаваемого массива
    if (empty($purchaseData['purchase']) or empty($purchaseData['url']) or empty($purchaseData[PURCHASE_ID]) or empty($purchaseData[PURCHASE_NAME])) {
      throw new Exception();
    }
    // Данные о закупке
    $this->purchaseUrl = $purchaseData['url'];
    $this->purchaseId = $purchaseData[PURCHASE_ID];
    $this->purchaseName = $purchaseData[PURCHASE_NAME];
    // Получение корректировок и добавление их в массив с закупкой
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $user = Registry_Request::instance()->get('user');
    $userId = $user->getUserId();
    $corrections = $db->getCorrectionToPurchase($userId, $this->purchaseId);
    if (is_array($corrections)) {
      foreach ($purchaseData['purchase'] as $purchaseKey => $purchase) {
        foreach ($corrections as $correction) {
          if ($purchase['user'][USER_PURCHASE_ID] == $correction[USER_PURCHASE_ID]) {
            $purchaseData['purchase'][$purchaseKey]['corrections'][] = $correction;
          }
        }
      }
    }
    // Сортируем массив с закупкой
    $purchaseData['purchase'] = $this->sortPurchaseArr($purchaseData['purchase'], self::SORT_METHOD);
    // Преобразуем массив c заказами в объекты
    foreach ($purchaseData['purchase'] as $lot) {
      $lotObj = new Lot($lot);
      $this->lots[] = $lotObj;
    }
    $this->init();
  }

  /**
   * Сортировка массива с закупками по ID или нику участника
   * @param $arr array Массив с закупкой получанный с сайта СП
   * @param $param string Параметр по которому будет произведена сортировка:
   * - sortUserById - по ID участника
   * - sortUserByNick - по нику учатника
   * @return array Отсортированный массив
   */
  function sortPurchaseArr ($arr, $param) {
    usort($arr, array($this, $param));
    return $arr;
  }

  /**
   * Инициализация полученных данных с сайта СП
   */
  function init () {
    if (!empty($this->lots)) {
      $idPayPurchase = array();
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $userPurchase = $lot->getUserPurchase();
        // Перебираем платежи
        $pays = $lot->getPays();
        if (!empty($pays)) {
          /** @var Pay $pay */
          foreach ($pays as $pay) {
            $pay->init($this->purchaseId, $userPurchase->getUserPurchaseId());
            // Получаем ID проставленных платежей, найденных в закупке
            if (!is_null($pay->getIdPay())) {
              $idPayPurchase[] = $pay->getIdPay();
            }
          }
        }
      }
      // Инициализируем платежи которые есть в Разносилке, но нет в закупке
      $this->initLostPays($idPayPurchase);
    }
  }

  /**
   * Получить массив объектов Lot, содержащих все данные о заказе
   * @return array Массив объектов Lot, содержащих все данные о заказе
   */
  function getLots () {
    return $this->lots;
  }

  /**
   * Получить URL закупки
   * @return string URL закупки
   */
  function getPurchaseUrl () {
    return $this->purchaseUrl;
  }

  /**
   * Получить имя закупки
   * @return string Имя закупки
   */
  function getPurchaseName () {
    return $this->purchaseName;
  }

  /**
   * Получить ID закупки
   * @return int ID закупки
   */
  function getPurchaseId () {
    return $this->purchaseId;
  }

  /**
   * Поиск SMS для всех платежей
   */
  function findSmsToAllPays () {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $user = Registry_Request::instance()->get('user');
    $userId = $user->getUserId();
    $arrUsedSmsId = array();
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        // Перебираем все платежи указанные пользователем
        $pays = $lot->getPays();
        if (!empty($pays)) {
          /** @var Pay $pay */
          foreach ($pays as $pay) {
            // Если платёж не ошибочный
            if (!$pay->isError()) {
              // Если платёж ещё не проставлен
              if (!$pay->isFilling()) {
                // Поиск СМС для платежа
                $sms = $db->findSms($userId, $pay->getTimePay(), self::FORK_HOUR, $pay->getSum(), $pay->getCard());
                if ($sms !== false) {
                  foreach ($sms as $value) {
                    // Преобразуем найденные СМС в объекты
                    $smsObj = new SMS($value);
                    // Проверяем, была ли уже использована данная СМС в качестве варианта для другого платежа
                    if (!in_array($smsObj->getIdSms(), $arrUsedSmsId)) {
                      // Добавляем найденные СМС в платёж
                      $pay->addFoundSms($smsObj);
                    }
                  }
                  // Анализируем добавленные СМС
                  $pay->analyseSms($lot->getUserPurchase());
                  // Добавляем использованные СМС в массив
                  $arrUsedSmsId = array_merge($pay->getIdUsedSms(), $arrUsedSmsId);
                }
              } else {
                // Если оплата уже проставлена
              }
            } else {
              // Если платёж ошибочный
            }
          }
        }
      }
    }
  }

  /**
   * Получить количество активных участников (как на сайте СП).
   * То есть таких участников у которых сумма к внесению не равна нулю или имеется
   * заполненный участником закупки отчёт о платеже.
   * @return int Колчисество активных участников закупки.
   */
  public function getCountActiveLots () {
    $result = 0;
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        if ($lot->isActiveLot()) {
          $result++;
        }
      }
    }
    return $result;
  }

  /**
   * Получить количество активных товаров в закупке (как на сайте СП).
   * То есть таких товаров по которым не проставлены отказы.
   * @return int Количество активных товаров в закупке
   */
  public function getCountActiveOrders () {
    $result = 0;
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $orders = $lot->getOrders();
        if (!empty($orders)) {
          /** @var Order $order */
          foreach ($orders as $order) {
            if ($order->isActiveOrder()) {
              $result++;
            }
          }
        }
      }
    }
    return $result;
  }

  /**
   * Получить количество заказов.
   * @return int Количество заказов
   */
  public function getCountLots () {
    $result = count($this->lots);
    return $result;
  }

  /**
   * Получить количество товаров в закупке.
   * @return int Количество товаров в закупке
   */
  function getCountOrders () {
    $result = 0;
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $result += count($lot->getOrders());
      }
    }
    return $result;
  }

  /**
   * Получить общее количество денег к внесению на сайт учистниками закупок.
   * @return float Сумма к внесению участниками на сайт, руб.
   */
  public function getCountTotalMoney () {
    $result = 0.0;
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $result += $lot->getTotal();
      }
    }
    return $result;
  }

  /**
   * Получить общее количество денег которые уже внесены в Разносилку
   * @return float Сумма, уже внесённая в Разносилку, руб.
   */
  public function getCountTotalFoundMoney () {
    $result = 0.0;
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $result += $lot->getTotalFound();
      }
    }
    return $result;
  }

  /**
   * Получить общее количество денег которые уже внесены на сайт СП
   * @return float Сумма, уже внесённая на сайт СП, руб.
   */
  public function getCountTotalPutMoney () {
    $result = 0.0;
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $result += $lot->getTotalPut();
      }
    }
    return $result;
  }

  /**
   * Получить количество тегов для закупки
   * @return array Массив с количеством тегов для закупки
   */
  public function getCountTags () {
    $result = array();
    if (!empty($this->lots)) {
      /** @var Lot $lot */
      foreach ($this->lots as $lot) {
        $tags = $lot->getTagsLot();
        foreach ($tags as $tag) {
          if (isset($result[$tag])) {
            $result[$tag]++;
          } else {
            $result[$tag] = 1;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Удалить потерянный платёж
   * @param $lostLotNumber int Номер потерянного лота
   * @param $lostPayNumber int Номер потерянного платежа
   * @return bool Результат опреации
   */
  public function lostPayDelete ($lostLotNumber, $lostPayNumber) {
    $lostLot = $this->lostLots[$lostLotNumber];
    $result = $lostLot->lostPayDelete($lostPayNumber);
    // Обновляем объект
    if ($result) {
      // Если потерянных платежей больше нет
      $pays = $this->lostLots[$lostLotNumber]->getPays();
      if (empty($pays)) {
        // Удаляем информацию о лоте
        unset($this->lostLots[$lostLotNumber]);
      }
    }
    return $result;
  }

  /**
   * Сравнение по ID
   * @param $a array Первый заказ для сравнения
   * @param $b array Второй заказ для сравнения
   * @return int Результат сравнения
   */
  private function sortUserById (array $a, array $b) {
    if ($a['user'][USER_PURCHASE_ID] == $b['user'][USER_PURCHASE_ID]) {
      return 0;
    }
    return ($a['user'][USER_PURCHASE_ID] < $b['user'][USER_PURCHASE_ID]) ? -1 : 1;
  }

  /**
   * Сравнение по нику пользователя - сначала цифры, потом кириллические, потом латинские символы
   * @param $a array Первый заказ для сравнения
   * @param $b array Второй заказ для сравнения
   * @return int Результат сравнения
   */
  private function sortUserByNickRusEng (array $a, array $b) {
    $eng = '|^[A-Za-z]|';
    $rus = '|^[А-Яа-яЁё]|';
    $dig = '|^[^A-Za-zА-Яа-яЁё]|';

    // Инициализация, не разбирался, возможно ошибка
    $ap = 1;
    $bp = 1;

    if (preg_match($dig, $a['user'][USER_PURCHASE_NICK])) {
      $ap = 1;
    }
    if (preg_match($rus, $a['user'][USER_PURCHASE_NICK])) {
      $ap = 2;
    }
    if (preg_match($eng, $a['user'][USER_PURCHASE_NICK])) {
      $ap = 3;
    }
    if (preg_match($dig, $b['user'][USER_PURCHASE_NICK])) {
      $bp = 1;
    }
    if (preg_match($rus, $b['user'][USER_PURCHASE_NICK])) {
      $bp = 2;
    }
    if (preg_match($eng, $b['user'][USER_PURCHASE_NICK])) {
      $bp = 3;
    }

    $a['user'][USER_PURCHASE_NICK] = mb_strtolower($a['user'][USER_PURCHASE_NICK], 'UTF-8');
    $b['user'][USER_PURCHASE_NICK] = mb_strtolower($b['user'][USER_PURCHASE_NICK], 'UTF-8');

    if ($ap == $bp) {
      $result = strcmp($a['user'][USER_PURCHASE_NICK], $b['user'][USER_PURCHASE_NICK]);
    } elseif ($ap == 1) {
      $result = -1;
    } elseif ($ap == 2 and $bp == 3) {
      $result = -1;
    } elseif ($ap == 2 and $bp == 1) {
      $result = 1;
    } elseif ($ap == 3) {
      $result = 1;
    } else {
      $result = 0;
    }

    return $result;
  }

  /**
   * Сравнение по нику пользователя - сначала цифры, потом латинские, потом кириллические символы
   * @param $a array Первый заказ для сравнения
   * @param $b array Второй заказ для сравнения
   * @return int Результат сравнения
   */
  private function sortUserByNickEngRus (array $a, array $b) {
    $a['user'][USER_PURCHASE_NICK] = mb_strtolower($a['user'][USER_PURCHASE_NICK], 'UTF-8');
    $b['user'][USER_PURCHASE_NICK] = mb_strtolower($b['user'][USER_PURCHASE_NICK], 'UTF-8');
    $result = strcmp($a['user'][USER_PURCHASE_NICK], $b['user'][USER_PURCHASE_NICK]);
    return $result;
  }

  /**
   * Инициализировать платежи которые есть в Разносилке, но нет в закупке.
   * @param $idPaysPurchase array Массив с ID платежей найденных и в закупке, и в Разносилке
   */
  private function initLostPays (array $idPaysPurchase) {
    if (!empty($idPaysPurchase)) {
      // Инициализация переменных
      $idPaysDB = array();
      $lostPaysId = array();
      $lostPays = array();
      // Получаем ID проставленных платежей, найденных в Разносилке
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      $userId = $user->getUserId();
      $db = new DataBase(Registry_Request::instance()->get('db'));
      $pays = $db->getAllPayFromPurchase($userId, $this->purchaseId);
      // Подготовка массива для сравнения
      if (!empty($pays)) {
        foreach ($pays as $key => $pay) {
          $idPaysDB[$key] = $pay[PAY_ID];
        }
      }
      // Получаем ID платежей которые есть в Разносилке, но нет в закупке
      if (!empty($idPaysDB)) {
        $lostPaysId = array_diff($idPaysDB, $idPaysPurchase);
      }
      // Инициализация массива потерянных платежей
      if (!empty($lostPaysId)) {
        // Подготовка массива для инициализации
        foreach ($lostPaysId as $key => $payId) {
          $lostPays[$pays[$key][USER_PURCHASE_ID]][] = $pays[$key];
        }
        // Создание заказов
        foreach ($lostPays as $userId => $pays) {
          $lot['total_put'] = 0;
          $lot['comment_org'] = '';
          // Данные о пользователе
          $userPurchase = $db->getUserPurchase($userId, $user->getSpId());
          $site = Site::getSite($user->getSpId());
          $userPurchase['url'] = $site->getUserPurchaseURL($userId);
          $lot['user'] = $userPurchase;
          // Другие данные
          $lot['pays'] = $pays;
          $lot['orders'] = array();
          $this->lostLots[] = new Lot($lot);
        }
        // Инициализация
        /** @var Lot $lot */
        foreach ($this->lostLots as $lot) {
          $userPurchase = $lot->getUserPurchase();
          // Перебираем платежи
          $pays = $lot->getPays();
          if (!empty($pays)) {
            /** @var Pay $pay */
            foreach ($pays as $pay) {
              $pay->init($this->purchaseId, $userPurchase->getUserPurchaseId());
            }
          }
        }
      }
    }
  }

  /**
   * Получить массив с заказами содержащие потерянные оплаты
   * @return Lot[] Массив с заказами содержащие потерянные оплаты
   */
  function getLostLots () {
    return $this->lostLots;
  }

}