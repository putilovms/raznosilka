<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Pager.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Pager Постраничный вывод результатов
 */
class Pager {

  /**
   * @var string URL подготовленный для последующей генерации ссылок пейджера
   */
  private $url;
  /**
   * @var int Всего записей к выводу
   */
  private $totalCount;
  /**
   * @var int Номер текущей старицы
   */
  private $page;
  /**
   * @var int Число записей на одну страницу
   */
  private $countOnPage;
  /**
   * @var int Число кнопок для навигации слева и справа от текущего местоположения пользователя
   */
  private $buttonCount;
  /**
   * @var array Массив с данными для которых создаётся пейджер
   */
  private $dataInput;

  /**
   * Тэг для получения текущей страницы из запроса GET
   */
  const PAGER_TAG = 'page';
  /**
   * Число кнопок для навигации слева и справа от текущего местоположения пользователя по умолчанию
   */
  const BUTTON_COUNT = 2;

  /**
   * Конструктор класса
   * @param $data array Массив с данными для которых создаётся пейджер
   * @param $countOnPage int Число записей на одну страницу
   */
  function __construct (array $data, $countOnPage) {
    $this->url = $this->getUrl($_SERVER['REQUEST_URI']);
    $this->dataInput = $data;
    $this->totalCount = count($data);
    $this->page = Pager::getPageParam();
    $this->countOnPage = $countOnPage;
    $this->buttonCount = self::BUTTON_COUNT;
  }

  /**
   * Задать пользовательские параметры для пейджера
   * @param $option int Опции:
   *  PAGER_PAGE - Номер текущей страницы (int)
   *  PAGER_BUTTON_COUNT - Количество выводимых кнопок (по умолчанию n = 2, т.е. n*2+1=5 кнопок будет выведено) (int)
   *  PAGER_URL - URL страницы для которой генерируется пейджер (string)
   * @param $value mixed Значение опции
   * @throws Exception
   */
  function setOpt($option, $value){
    switch ($option) {
      // Номер текущей страницы
      case PAGER_PAGE:
        $value = (int)$value;
        if (empty($value)) {
          throw new Exception("Номер страницы должен быть числом больше 0");
        }
        $this->page = $value;
        break;
      // Количество выводимых кнопок (по умолчанию 2, т.е. n*2+1=5 кнопок будет выведено)
      case PAGER_BUTTON_COUNT:
        $this->buttonCount = $value;
        break;
      // URL страницы для которой генерируется пейджер
      case PAGER_URL:
        $this->url = $this->getUrl($value);
        break;
      // Если задан не верная опция
      default :
        throw new Exception("Попытка изменить несуществующую опцию");
        break;
    }
  }

  /**
   * Получить номер текущкй страницы из get параметра.
   * Используется только для инициализации! Иначе использовать @see Pager::getPage()
   * @return int
   */
  static function getPageParam(){
    return (isset($_GET[Pager::PAGER_TAG])) ? (((int)$_GET[Pager::PAGER_TAG] > 0) ? (int)$_GET[Pager::PAGER_TAG] : 1) : 1;
  }

  /**
   * Получить номер текущей страницы
   * @return int Номер текущей страницы
   */
  function getPage(){
    return $this->page;
  }

  /**
   * Получить общее количество элементов
   * @return int Всего количество элементов
   */
  function getItemCount(){
    return $this->totalCount;
  }

  /**
   * Получить данные для вывода пейджера
   * @return Array Массив с данными для вывода пейджера:
   *  - [x] - строка с кнопкой пейджера
   *    - ['url'] - URL для кнопки пейджера
   *    - ['text'] - текст кнопки пейджера
   *    - ['title'] - всплывающая подсказка
   */
  function getPager () {
    $result = array();
    // вычисляем количество страниц для вывода
    $pages = intval($this->totalCount / $this->countOnPage);
    // Если страница для вывода одна
    if ($pages == 0) {
      return $result;
    }
    // Если записей больше чем страниц для вывода
    if (($pages * $this->countOnPage) < $this->totalCount) {
      $pages++;
    }
    // Корректируем страницу для вывода
    $page = $this->page;
    if ($page > $pages) {
      $page = $pages;
    }
    // Получаем количество кнопок для вывода
    if ($pages < ($this->buttonCount * 2 + 1)) {
      $buttonCount = intval($pages) - 1;
    } else {
      $buttonCount = $this->buttonCount;
    }
    // Вывод кнопок "В начало" и "В конец"
    $startButton = $page - $buttonCount;
    $endButton = $page + $buttonCount;
    if ($startButton < 1) {
      $startButton = 1;
      $endButton = $startButton + $buttonCount * 2;
    }
    if ($endButton > $pages) {
      $endButton = $pages;
      $startButton = $endButton - $buttonCount * 2;
      if ($startButton < 1) {
        $startButton = 1;
      }
    }
    // Результат
    if ($startButton != 1) {
      $button['url'] = $this->url . '1';
      $button['text'] = '«';
      $button['title'] = 'В начало';
      $button['class'] = '';
      $result[] = $button;
    }
    if ($page > 1) {
      $button['url'] = $this->url . ($page - 1);
      $button['text'] = '←';
      $button['title'] = 'Назад';
      $button['class'] = '';
      $result[] = $button;
    }
    for ($i = $startButton; $i <= $endButton; $i++) {
      if ($i == $page) {
        $button['url'] = null;
        $button['text'] = $i;
        $button['title'] = "";
        $button['class'] = "";
        $result[] = $button;
      } else {
        $button['url'] = $this->url . $i;
        $button['text'] = $i;
        $button['title'] = "";
        $button['class'] = '';
        $result[] = $button;
      }
    }
    if ($page < $pages) {
      $button['url'] = $this->url . ($page + 1);
      $button['text'] = '→';
      $button['title'] = "Вперёд";
      $button['class'] = '';
      $result[] = $button;
    }
    if ($endButton != $pages) {
      $button['url'] = $this->url . $pages;
      $button['text'] = '»';
      $button['title'] = "В конец";
      $button['class'] = '';
      $result[] = $button;
    }
    return $result;
  }

  /**
   * Получить URL для дальнейшего использования его в пейджере
   * @param $url string Исходный URL
   * @return string URL для ссылок пейджера
   */
  function getUrl ($url) {
    $parseUrl = parse_url($url);
    $parseQuery = isset($parseUrl['query']) ? $parseUrl['query'] : '';
    parse_str($parseQuery, $query);
    // удаляем указатель на номер страницы, если он был
    if (isset($query[self::PAGER_TAG])) {
      unset($query[self::PAGER_TAG]);
    }
    $query[self::PAGER_TAG] = '';
    $result = URL::to($parseUrl['path'], $query);
    return $result;
  }

  /**
   * Получить HTML код для вывода пейджера
   * @return string HTML код для вывода пейджера
   */
  function getHTML () {
    $result = '';
    $pager = $this->getPager();
    if (!empty($pager)) {
      $result .= '<div class="pager">';
      foreach ($pager as $button) {
        if (!empty($button['url'])) {
          $result .= "<a class='pager-item {$button['class']}' title='{$button['title']}'  href='{$button['url']}'>{$button['text']}</a>";
        } else {
          $result .= "<span class='pager-current-item'>{$button['text']}</span>";
        }
      }
      $result .= '</div>';
    }
    return $result;
  }

  /**
   * Получить обрезанный массив с данными для вывода
   * @return array обрезанный массив с данными для вывода
   */
  public function getItemForView () {
    $result = array();
    if (!empty($this->dataInput)) {
      // Получаем список для вывода
      $start = ($this->page - 1) * $this->countOnPage;
      $count = $this->countOnPage;
      $result = array_slice($this->dataInput, $start, $count, true);
    }
    return $result;
  }

}