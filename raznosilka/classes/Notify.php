<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Notify.php>
 * @copyright © М. С. Путилов, 2015
 */

  /**
   * Class Notify Класс отвечающий за вывод оповещений между сессиями
   */
class Notify {
  /**
   * @var Registry_Session содержит реестр сессии
   */
  private $regSes;
  /**
   * @var array Хранилище для оповещений между перезапусками сессий
   */
  private $backUp = array();

  /**
   * Констрктор уведомлений
   */
  function __construct () {
    $this->regSes = Registry_Session::instance();
  }

  /**
   * Добавляет сообщение в реестр сессий
   * @param string $text Текст сообщения
   * @param string $type Тип сообщения
   */
  function sendNotify($text, $type){
    $notifyAll = $this->getNotifyFromRegistry();
    $notifyAll[] = array('text' => $text, 'type' => $type);
    $this->setNotifyInRegistry($notifyAll);
  }

  /**
   * Конвертирует строку в сообщение
   * @param string $text Текст сообщения
   * @param string $type Тип сообщения
   * @return array Сообщение
   */
  static function convertNotify($text, $type){
    return array('text' => $text, 'type' => $type);
  }

  /**
   * Отдаёт массив с сообщениями
   * @return array Список сообщений для вывода
   */
  function getAllNotify(){
    $notify = $this->getNotifyFromRegistry();
    if (!is_null($notify)){
      $this->delAllNotify();
      return $notify;
    }
    return array();
  }

  /**
   * Метод для удаления всех уведомлений из реестра.
   * Вынесен для тестирования.
   * @return bool
   */
  function delAllNotify(){
    return $this->regSes->del('notify');
  }

  /**
   * Получает уведомления из реестра
   * @return array Пустой массив в случае отсутствия уведомлений
   */
  function getNotifyFromRegistry(){
    return (array) $this->regSes->get('notify');
  }

  /**
   * Сохраняет уведомления в реестре
   * @param array $notify Массив уведомлений
   */
  function setNotifyInRegistry($notify){
    $this->regSes->set('notify', $notify, true);
  }

  /**
   * Метод для сохранения значений между сбросами сессий
   */
  public function backUp () {
    $this->backUp = (array) $this->regSes->get('notify');
  }

  /**
   * Восстановление значений после сброса сессий
   */
  public function restore () {
    $this->regSes->set('notify',$this->backUp, true);
  }

} 