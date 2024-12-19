<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\ListPurchase.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class ListPurchase Отвечает за вывод список закупок
 */
class ListPurchase {
  /**
   * Содержит объект Site для доступа к данным сайта СП
   * @var Site
   */
  private $site;
  /**
   * Работа с закупками
   * @var PurchaseHelper
   */
  private $purchase;

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
   * @var DataBase доступ к БД
   */
  private $db;
  /**
   * @var int|null  День на который проставляются платежи организатором после оплаты участниками
   */
  private $fillingDay;
  /**
   * @var int Тип запроса к сайту СП, выбранный пользователем
   */
  private $userRequest;

  /**
   * Конструктор класса
   */
  function __construct () {
    $this->site = Site::getSite();
    $this->purchase = new PurchaseHelper();
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    $this->userRequest = (int)$userInfo[USER_REQUEST];
    $this->fillingDay = $user->getFillingDay();
  }

  /**
   * Получение списка закупок из организаторской сайта СП полностью подготовленного к выводу
   * или получение информации для запроса к сайту СП, для получения списка закупок.
   * @param $filter string Строка для поиска закупки
   * @return array Массив закупок подготовленный к выводу или информация для запроса, формата:
   *  ['info'] - информация о запросе @see Site::getRequestInfoListPurchase()
   *  ['list'] - список закупок @see ListPurchase::prepareListPurchaseOrgArray()
   *  ['select'] - информация о выбранной закупке @see PurchaseHelper::getSelectPurchaseInfo()
   */
  public function getListPurchaseFromOrganizer ($filter = '') {
    // Инициализация
    $info = $this->site->getRequestInfoListPurchase($filter);
    $result = array();
    $result['info'] = $info;
    $result['list'] = array();
    switch ($this->userRequest) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {

        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        $listPurchase = $this->site->getListPurchaseFromSite($info);
        $result = $listPurchase;
        // Если список получен
        if ($result['info']['error'] == ERROR_NONE) {
          $result['list'] = $this->prepareListPurchaseOrgArray($listPurchase['list'], $filter);
        }
        break;
      }
    }
    // Выбранная закупка
    $result['select'] = PurchaseHelper::getSelectPurchaseInfo();;
    return $result;
  }

  /**
   * Подгатавливает массив закупок полученный с сайта СП для вывода
   * @param $list array Массив с закупками полученный с сайта СП
   * @param $filter string
   * @param array $pagerOpt Набор пользовательских опций для пейджера
   * @return array Массив подготовленный для вывода, формата:
   * ['purchase'] - список закупок
   *    [x] - номер закупки
   *      ['id'] - ID закупки
   *      ['name'] - Название закупки
   *      ['status'] - статус закупки (параметр получанный с сайта СП)
   *      ['pay_to'] - до какого числа должны оплатить УЗ
   *      ['url'] - url закупки
   *      ['class'] - класс для отображения закупки
   *      ['sum'] - найденная разносилкой сумма для данной закупки
   *      ['url_set'] - url для выбора закупки
   *  ['item_count'] - общее количество элементов
   *  ['pager'] - пейджер
   *  ['page'] - текущая страница
   */
  function prepareListPurchaseOrgArray ($list, $filter, $pagerOpt = array()) {
    $result = array();
    // Сортировка по дате оплаты
    $list = $this->sortListPurchase($list);
    // Фильтрация закупок
    $listFilter = array();
    $pattern = '|' . Kit::UW(mb_strtolower($filter)) . '|';
    $purchaseStatusIgnore = $this->site->getPurchaseStatusIgnore();
    foreach ($list as $purchase) {
      // Фильтрация активных закупок
      if (!in_array($purchase['status'], $purchaseStatusIgnore)) {
        // Поиск закупок по названию
        if (!empty($filter)) {
          $subject = Kit::UW(mb_strtolower($purchase['name']));
          if (preg_match($pattern, $subject)) {
            $listFilter[] = $purchase;
          }
        } else {
          $listFilter[] = $purchase;
        }
      }
    }
    // Пейджер
    $this->pager = new Pager($listFilter, self::ITEM_ON_PAGE);
    // Задать опции пейджера, если они заданы
    if (!empty($pagerOpt)) {
      foreach ($pagerOpt as $opt => $value) {
        $this->pager->setOpt($opt, $value);
      }
    }
    $itemForView = $this->pager->getItemForView();
    $result['item_count'] = $this->pager->getItemCount();
    $result['pager'] = $this->pager->getHTML();
    $result['page'] = $this->pager->getPage();
    // Получение текущего пользователя
    $user = Registry_Request::instance()->get('user');
    // Подгатовка массива для вывода
    foreach ($itemForView as $purchase) {
      // Определяем закупки которые необходимо разнести сегодня
      $dateNow = strtotime(date('Y-m-d'));
      $datePay = $purchase['pay_to'] + (3600 * 24 * $this->fillingDay);
      $purchase['class'] = ($dateNow == $datePay) ? 'now' : 'normal';
      // Конвертируем время для вывода
      $payTo = ($purchase['pay_to'] > 0) ? strftime('%Y-%m-%d', $purchase['pay_to']) : '0000-00-00';
      $purchase['pay_to'] = ($purchase['pay_to'] > 0) ? strftime('%d.%m.%Y', $purchase['pay_to']) : '—';
      // Получение найденной суммы
      $sum = $this->db->getFoundSumPurchase($user->getUserId(), $purchase['id']);
      $sum += $this->db->getFoundSumCorrection($user->getUserId(), $purchase['id']);
      $purchase['sum'] = number_format($sum, 2, ',', '');
      // URL для выбора закупки
      $purchase['url_set'] = URL::to('service/set_purchase', array('purchase' => $purchase['id']));
      // Добавляем или обновляем закупку в БД
      $this->purchase->addPurchase($purchase['name'], $purchase['id'], $payTo);
      // Сохраняем подготовленный для вывода результат
      $result['purchase'][] = $purchase;
    }
    return $result;
  }

  /**
   * Сортировка списка закупок по дате оплаты
   * @param $arr array Исходный массив
   * @return array Отсортированный массив
   */
  function sortListPurchase (array $arr) {
    /**
     * Сортировка по дате
     * @param array $a Первая закупка для сравнения
     * @param array $b Вторая закупка для сравнения
     * @return int Результат сравнения
     */
    function payTo (array $a, array $b) {
      if ($a['pay_to'] == $b['pay_to']) {
        return 0;
      }
      return ($a['pay_to'] > $b['pay_to']) ? -1 : 1;
    }

    usort($arr, 'payTo');
    return $arr;
  }

  /**
   * Подготавливает к выводу список закупок имеющихся в БД для текущего пользователя
   * @param $filter string Строка для поиска закупки
   * @return array Подготовленный к выводу список закупок текущего пользователя, формата:
   *  ['purchase'] - список закупок
   *    [x] - номер закупки
   *      [PURCHASE_ID] - ID закупки
   *      [USER_ID] - ID пользователя
   *      [PURCHASE_NAME] - Имя закупки
   *      ['sp_id'] - ID сайта СП
   *      ['class'] - имя класса
   *      ['sum'] - общая найденная сумма в закупке
   *      ['url_set'] - URL для выбора закупки
   *      ['name'] = [PURCHASE_NAME]
   *      ['url'] - URL к закупке
   *  ['item_count'] - общее количество элементов
   *  ['pager'] - пейджер
   *  ['page'] - текущая страница
   *  ['select'] - выбранная закупка
   */
  public function getListPurchaseFromService ($filter) { // todo заменить имена массивов на константы из БД (sp_id, name)
    $result = false;
    $listPurchase = $this->purchase->getListPurchaseFromService($filter);
    if ($listPurchase !== false) {
      $result = array();
      // Пейджер
      $this->pager = new Pager($listPurchase, self::ITEM_ON_PAGE);
      $itemForView = $this->pager->getItemForView();
      $result['item_count'] = $this->pager->getItemCount();
      $result['pager'] = $this->pager->getHTML();
      $result['page'] = $this->pager->getPage();
      // Подгатовка массива для вывода
      foreach ($itemForView as $purchaseKey => $purchase) {
        $purchase['class'] = 'normal';
        // Получение найденной суммы
        $sum = $this->db->getFoundSumPurchase($purchase[USER_ID], $purchase[PURCHASE_ID]);
        $sum += $this->db->getFoundSumCorrection($purchase[USER_ID], $purchase[PURCHASE_ID]);
        $purchase['sum'] = number_format($sum, 2, ',', '');
        // URL для выбора закупки
        $purchase['url_set'] = URL::to('service/set_purchase', array('purchase' => $purchase[PURCHASE_ID]));
        // Имя закупки
        $purchase['name'] = $purchase[PURCHASE_NAME];
        // Путь к закупке
        $purchase['url'] = $this->site->getPurchaseURL($purchase[PURCHASE_ID]);
        // Сохраняем подготовленный для вывода результат
        $result['purchase'][$purchaseKey] = $purchase;
      }
      // Выбранная закупка
      $result['select'] = PurchaseHelper::getSelectPurchaseInfo();
    }
    return $result;
  }

  /**
   * Перекодировка полученных данных в JSON для вывода через JS
   * @param $list array Массив со списком закупок для перекодировки в JSON
   * @return string Строка с JSON объектом в переменной JSON_PAGE, для использования в JS
   */
  public function getJsonListPurchaseFromOrganizer (array $list) {
    $result = json_encode($list);
    $result = 'var ' . PAGE_DATA_JS . ' = ' . $result . ';';
    return $result;
  }

}