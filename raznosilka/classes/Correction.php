<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Correction.php>
 * @copyright © М. С. Путилов, 2015
 */

  /**
   * Class Correction Класс описывающий корректировку для закупки
   */
class Correction {
  /**
   * @var int ID корректировки
   */
  private $correctionId;
  /**
   * @var float Сумма корректировки
   */
  private $correctionSum = 0.00;
  /**
   * @var string Комментарий для корректировки
   */
  private $correctionComment;

  /**
   * Конструктор класса
   * @param $correction array Массив с корректировкой, полученный из БД
   */
  function __construct(array $correction){
    $this->correctionId = $correction[CORRECTION_ID];
    $this->correctionSum = $correction[CORRECTION_SUM];
    $this->correctionComment = $correction[CORRECTION_COMMENT];
  }

  /**
   * Получить ID корректировки
   * @return int ID корректировки
   */
  function getCorrectionId () {
    return $this->correctionId;
  }

  /**
   * Получить сумму корректировки
   * @return float Сумма корректировки
   */
  function getCorrectionSum () {
    return $this->correctionSum;
  }

  /**
   * Получить комментарий для корректировки
   * @return string Комментарий для корректировки
   */
  function getCorrectionComment () {
    return $this->correctionComment;
  }

  /**
   * Удаление текущей корректировки из базы данных.
   * Для удаления корректировки с обновлением объекта, необходимо воспользоваться
   * методом @see Lot::correctionDelete
   * @return bool Результат удаления
   */
  public function correctionDelete () {
    $db = new DataBase(Registry_Request::instance()->get('db'));
    $correctionId = $this->getCorrectionId();
    $result = $db->correctionDelete($correctionId);
    return $result;
  }
  
} 