<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\ToolsSMS.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class ToolsSMS Инструменты для работы с СМС
 */
class ToolsSMS {
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  private $db;
  /**
   * @var User Данные текущего пользователя
   */
  private $user;
  /**
   * @var array Массив шаблонов для бесполезных СМС
   */
  private $templatesUselessSMS;
  /**
   * @var array Массив шаблонов для СМС содержащих полезную информацию
   */
  private $templatesUsefulSMS;
  /**
   * @var array Массив шаблонов признаков начала СМС
   */
  private $templatesMarkStartSMS;
  /**
   * @var array Массив шаблонов признаков конца СМС
   */
  private $templatesMarkEndSMS;
  /**
   * @var int Текущее время формата UNIX
   */
  private $dateNow;

  /**
   * Конструктор класса
   * @param DataBase $db Ссылка на базу данных для тестирования
   * @param User $user Объект пользователя для тестирования
   */
  function __construct (DataBase $db = null, User $user = null) {
    if (is_null($db)) {
      $this->db = new DataBase(Registry_Request::instance()->get('db'));
    } else {
      $this->db = $db;
    }
    if (is_null($db)) {
      $this->user = Registry_Request::instance()->get('user');
    } else {
      $this->user = $user;
    }
    $this->dateNow = time();
    // Инициализация шаблонов
    $this->initTemplates();
  }

  /**
   * Инициализация шаблонов
   */
  function initTemplates () {
    // Получение шаблонов и изменение кодировки шаблонов
    // Начало склеенных СМС
    $this->templatesMarkStartSMS = $this->db->getTemplatesByType(TPL_MARK_START);
    foreach ($this->templatesMarkStartSMS as $key => $tpl) {
      $this->templatesMarkStartSMS[$key][TPL_TEMPLATE] = Kit::UW($tpl[TPL_TEMPLATE]);
    }
    // var_dump($this->templateMarkStartSMS);
    // Конец склеенных СМС
    $this->templatesMarkEndSMS = $this->db->getTemplatesByType(TPL_MARK_END);
    foreach ($this->templatesMarkEndSMS as $key => $tpl) {
      $this->templatesMarkEndSMS[$key][TPL_TEMPLATE] = Kit::UW($tpl[TPL_TEMPLATE]);
    }
    // var_dump($this->templateMarkEndSMS);
    // Полезные СМС
    $this->templatesUsefulSMS = $this->db->getTemplatesByType(TPL_USEFUL);
    foreach ($this->templatesUsefulSMS as $key => $tpl) {
      $this->templatesUsefulSMS[$key][TPL_TEMPLATE] = Kit::UW($tpl[TPL_TEMPLATE]);
    }
    // var_dump($this->templateUsefulSMS);
    // Бесполезные СМС
    $this->templatesUselessSMS = $this->db->getTemplatesByType(TPL_USELESS);
    foreach ($this->templatesUselessSMS as $key => $tpl) {
      $this->templatesUselessSMS[$key][TPL_TEMPLATE] = Kit::UW($tpl[TPL_TEMPLATE]);
    }
    // var_dump($this->templateUselessSMS);
  }

  /**
   * Расклеивает склеенные SMS
   * @param array $arr Массив полученный из исходного файла
   * @return array Массив с расклеенными SMS:
   * ['separated'] - обработанный исходный массив
   * ['glued'] - только склеенные СМС
   * ['unglued'] - только расклеенные СМС
   */
  function separationGluedSMS ($arr) {
    // Инициализация
    $separated = array();
    $gluedSMS = array();
    $ungluedSMS = array();
    // Получаем шаблоны концов СМС
    $pattern = array();
    foreach ($this->templatesMarkEndSMS as $tpl) {
      $pattern[] = $tpl[TPL_TEMPLATE];
    }
    $pattern = "#(" . rtrim(implode("|", $pattern), "|") . ")#";
    // Перебираем все СМС
    foreach ($arr as $value) {
      // Определяем количество склеенных СМС
      $countSms = $this->countInOneSMS($value[SMS_UNKNOWN_TEXT]);
      // Сохраняем время получения СМС
      // Если имеются склеенные СМС
      if ($countSms > 1) {
        // Сохраняем склеенную СМС
        $gluedSMS[] = $value;
        // Инициализация
        $smsText = Kit::UW($value[SMS_UNKNOWN_TEXT]);
        $smsCut = array();
        // Отделяем начала СМС
        for ($x = 0; $x < $countSms; $x++) $smsCut[$x] = Kit::textСut($smsText, 67);
        // Склеиваем СМС
        $i = 0;
        // Массив для пометки восстановленных СМС
        $restore = array();
        for ($x = 0; $x < $countSms; $x++) $restore[$x] = 0;
        // Выполняем пока исходная строка содержит данные
        while ($smsText) {
          // Предохранитель от зацикливания
          if ($i === 0) {
            $fuse = false;
            foreach ($restore as $check) if (!$check) {
              $fuse = true;
            }
            if (!$fuse) {
              break;
            }
          }
          // Если текущая СМС ещё не восстановлена полностью
          if (!$restore[$i]) {
            // Находим концы СМС
            $smsTextTmp = $smsCut[$i] . $smsText;
            preg_match_all($pattern, $smsTextTmp, $matches, PREG_OFFSET_CAPTURE);
            // Если удалось найти конец СМС
            if (count($matches[0]) > 0) {
              // Определяем количество символов до ближайшего совпадения
              $len = $matches[0][0][1] + strlen($matches[0][0][0]) - strlen($smsCut[$i]);
              // Если количество символов меньше или равно 67 символов то вырезаем конец СМС
              if ($len <= 67) {
                $smsCut[$i] .= Kit::textСut($smsText, $len);
                // Отмечаем СМС как восстановленную
                $restore[$i] = 1;
              } else {
                // Если количество символов больше 67 то отрезаем 67 символов
                $smsCut[$i] .= Kit::textСut($smsText, 67);
              }
            } else {
              // Если концы строк обнаружить не удалось, то восстанавливаем СМС из остатков или добавляем к ней ещё 67 символов
              if (strlen($smsText) > 67) {
                $smsCut[$i] .= Kit::textСut($smsText, 67);
              } else {
                $smsCut[$i] .= Kit::textСut($smsText, strlen($smsText));
              }
            }
          }
          // Обеспечиваем чередование восстанавливаемых СМС
          if ($i == ($countSms - 1)) {
            $i = 0;
          } else {
            $i++;
          }
        }
        // Сохраняем результат восстановаления СМС
        $smsCut = Kit::arrWU($smsCut);
        foreach ($smsCut as $sms_value) {
          $info = $value;
          $info[SMS_UNKNOWN_TEXT] = $sms_value;
          $ungluedSMS[] = $info;
          $separated[] = $info;
        }
      } else {
        $separated[] = $value;
      }
    }
    $result['separated'] = $separated;
    $result['glued'] = $gluedSMS;
    $result['unglued'] = $ungluedSMS;
    return $result;
  }

  /**
   * Определяет количество SMS в одной SMS, т.е. определяет
   * склеена ли данная SMS
   * @param string $text Текст SMS
   * @return int Количество SMS
   */
  function countInOneSMS ($text) {
    // Инициализация
    $value = Kit::UW($text);
    $result = 0;
    // Перебираем все признаки начал СМС
    foreach ($this->templatesMarkStartSMS as $template) {
      $pattern = "#{$template[TPL_TEMPLATE]}#";
      preg_match_all($pattern, $value, $match);
      // var_dump($match);
      $result += count($match[0]);
    }
    return $result;
  }

  /**
   * Определение СМС и извлечение из определённых СМС информации
   * @param $arr
   * @return array Результат содержит два массива:
   * - processed - Массив с распознанными СМС и данными извлечёнными из них
   * - unknown - Массив с неопределёнными СМС
   */
  function processedSMS ($arr) {
    // Инициализация
    $processed = array();
    $unknown = array();
    // Перебираем СМС
    foreach ($arr as $value) {
      // Инициализация
      $match = false;
      $typeSms = 0;
      $data = array();
      // Определяем тип СМС и получаем данные
      $smsText = Kit::UW($value[SMS_UNKNOWN_TEXT]);
      // Перебираем шаблоны
      foreach ($this->templatesUsefulSMS as $key => $template) {
        $pattern = "#{$template[TPL_TEMPLATE]}#";
        $match = preg_match($pattern, $smsText, $data);
        // Если шаблон найден, то выходим из цикла
        if ($match) {
          $data = Kit::arrWU($data);
          $typeSms = $template[TPL_SUBTYPE];
          // Сбор статистики
          $this->templatesUsefulSMS[$key][TPL_COUNT_USED]++;
          $this->templatesUsefulSMS[$key][TPL_LAST_USED] = strftime('%Y-%m-%d %H:%M:%S', $this->dateNow);
          break;
        }
      }
      // Сохранение распознанных данных SMS
      $sms = $value;
      // Если СМС определена
      if ($match) {
        $sms['type'] = $typeSms;
        switch ($typeSms) {
          case 1:
            // $data:
            //  1 - Номер карты на которую произошло зачисление средств
            //  2 - Дата зачисления
            //  3 - Сумма зачисления
            //  4 - Карта с которой произошло зачисление средств
            $date = DateTime::createFromFormat('d.m.y H:i', $data[2]);
            $sms[SMS_TIME_PAY] = $date->format('Y-m-d H:i:00'); // Время поступления платежа
            $sms[SMS_SUM_PAY] = $data[3]; // Сумма платежа
            $sms[SMS_CARD_PAYER] = $data[4]; // Номер карты участника
            $sms[SMS_FIO] = ''; // ФИО плательщика
            $sms[SMS_COMMENT] = ''; // Комментарий в СМС
            break;
          case 2:
            // $data:
            //  1 - ФИО
            //  2 - Сумма зачисления
            $sms[SMS_TIME_PAY] = '0000-00-00 00:00:00'; // Время поступления платежа
            $sms[SMS_SUM_PAY] = $data[2]; // Сумма платежа
            $sms[SMS_CARD_PAYER] = '-1'; // Номер карты участника
            $sms[SMS_FIO] = $data[1]; // ФИО плательщика
            $sms[SMS_COMMENT] = ''; // Комментарий в СМС
            break;
          case 3:
            // $data:
            //  1 - ФИО
            //  2 - Сумма зачисления
            //  3 - Комментарий
            // Проверяем, является ли комментарий номером карты
            $sms[SMS_SUM_PAY] = $data[2]; // Сумма платежа
            $sms[SMS_FIO] = $data[1]; // ФИО плательщика
            if ($this->commentContainsCard($data[3])) {
              $sms[SMS_TIME_PAY] = $sms[SMS_TIME_SMS];
              $sms[SMS_CARD_PAYER] = $data[3]; // Номер карты участника
              $sms[SMS_COMMENT] = ''; // Комментарий в СМС
            } else {
              $sms[SMS_TIME_PAY] = '0000-00-00 00:00:00'; // Время поступления платежа
              $sms[SMS_CARD_PAYER] = '-1'; // Номер карты участника
              $sms[SMS_COMMENT] = $data[3]; // Комментарий в СМС
            }
            break;
          case 4:
            // $data:
            //  1 - Номер карты на которую произошло зачисление средств
            //  2 - Дата зачисления
            //  3 - Сумма зачисления
            $date = DateTime::createFromFormat('d.m.y H:i', $data[2]);
            $sms[SMS_TIME_PAY] = $date->format('Y-m-d H:i:00'); // Время поступления платежа
            $sms[SMS_SUM_PAY] = $data[3]; // Сумма платежа
            $sms[SMS_CARD_PAYER] = -1; // Номер карты участника
            $sms[SMS_FIO] = ''; // ФИО плательщика
            $sms[SMS_COMMENT] = ''; // Комментарий в СМС
            break;
          case 5:
            // Возвращает 3 параметра:
            // 1. Сумма зачисления
            // 2. ФИО
            // 3. Комментарий
            $sms[SMS_TIME_PAY] = '0000-00-00 00:00:00'; // Время поступления платежа
            $sms[SMS_SUM_PAY] = $data[1]; // Сумма платежа
            $sms[SMS_CARD_PAYER] = '-1'; // Номер карты участника
            $sms[SMS_FIO] = $data[2]; // ФИО плательщика
            $sms[SMS_COMMENT] = $data[3]; // Комментарий в СМС
            break;
          case 6:
            // Возвращает 2 параметра:
            // 1. Сумма зачисления
            // 2. ФИО
            $sms[SMS_TIME_PAY] = '0000-00-00 00:00:00'; // Время поступления платежа
            $sms[SMS_SUM_PAY] = $data[2]; // Сумма платежа
            $sms[SMS_CARD_PAYER] = '-1'; // Номер карты участника
            $sms[SMS_FIO] = $data[3]; // ФИО плательщика
            $sms[SMS_COMMENT] = ''; // Комментарий в СМС
            break;
        };
        $processed[] = $sms;
      } else {
        $sms['type'] = 0;
        $unknown[] = $sms;
      }
    }
    $result['processed'] = $processed;
    $result['unknown'] = $unknown;
    // Обновление статистики
    $this->db->updateTemplateStatistics($this->templatesUsefulSMS);
    return $result;
  }

  /**
   * Повторно определяет нераспознанные СМС
   * @param array $arr Массив с нераспознанными СМС
   * @return array Результат состоит из двух массивов:
   * - detected - Массив с СМС которые удалось распознать как ненужные, но известные
   * - not_detected - Массив с СМС которые не удалось распознать
   */
  function detectUnknownSMS ($arr) {
    // Инициализация
    $detect = array();
    $notDetect = array();
    // Перебираем СМС
    foreach ($arr as $value) {
      $match = false;
      $sms = Kit::UW($value[SMS_UNKNOWN_TEXT]);
      // Перебираем шаблоны
      foreach ($this->templatesUselessSMS as $key => $template) {
        $pattern = "#{$template[TPL_TEMPLATE]}#";
        $match = preg_match($pattern, $sms);
        // Если шаблон найден, то выходим из цикла
        if ($match) {
          // Сбор статистики
          $this->templatesUselessSMS[$key][TPL_COUNT_USED]++;
          $this->templatesUselessSMS[$key][TPL_LAST_USED] = strftime('%Y-%m-%d %H:%M:%S', $this->dateNow);
          break;
        }
      }
      // Сортируем СМС
      if ($match) {
        $detect[] = $value;
      } else {
        $notDetect[] = $value;
      }
    }
    $result['detected'] = $detect;
    $result['not_detected'] = $notDetect;
    // Обновление статистики
    $this->db->updateTemplateStatistics($this->templatesUselessSMS);
    return $result;
  }

  /**
   * Сохранение распознанных SMS в таблицу sms
   * @param array $arr Массив с распознанными СМС
   * @return array Массив с результатом сохранения распознанных СМС:
   * - ['save'] - Массив сохранённых распознанных СМС
   * - ['not_save'] - Массив не сохранённых распознанных СМС (уже имеющихся в БД)
   */
  function saveProcessedSMS ($arr) {
    // Инициализация
    $smsSave = array();
    $smsComment = array();
    // Удаляем повторяющиеся СМС в самом файле
    $uniqueArr = Kit::array_unique_r($arr); // todo для точного удаления повторяющихся СМС, проверять ТОЛЬКО строки массива добавляемые в БД
    $smsNotSave = Kit::array_diff_r($arr, $uniqueArr);
    // Перебираем все распознанные СМС
    foreach ($uniqueArr as $key => $sms) {
      // Добавляем ID пользователя, если его нет
      $sms[USER_ID] = (isset($sms[USER_ID])) ? $sms[USER_ID] : $this->user->getUserId();
      // Проверяем на наличие повторяющихся SMS
      if (!$this->db->smsExist($sms)) {
        // Если СМС содержит сообщение, сохраняем её и отсылаем пользователю уведомление
        if (!empty($sms[SMS_COMMENT])) {
          $smsComment[$key] = $sms;
        }
        $smsSave[$key] = $sms;
      } else {
        $smsNotSave[$key] = $sms;
      }
    }
    // Сохраняем СМС в БД
    if (!empty($smsSave)) {
      $this->db->addSMS($smsSave);
    }
    $result['save'] = $smsSave;
    $result['comment'] = $smsComment;
    $result['not_save'] = $smsNotSave;
    return $result;
  }

  /**
   * Отсылает уведомление пользователю с предупреждением о найденной СМС с сообщением
   * @param array $sms Массив содержащий данные о СМС
   */
  function sendMessage ($sms) {
    // Генерируем сообщение
    $message = "Получена SMS содержащая сообщение от участника.";
    $message .= "<table class='message-sms'>";
    $dateTime = strftime('%H:%M %d.%m.%Y', strtotime($sms[SMS_TIME_SMS]));
    $message .= "<tr><th>Время получения:</th><td>{$dateTime}</td></tr>";
    if ($sms[SMS_TIME_PAY] != '0000-00-00 00:00:00') {
      $dateTime = strftime('%H:%M %d.%m.%Y', strtotime($sms[SMS_TIME_PAY]));
      $message .= "<tr><th>Время оплаты:</th><td>{$dateTime}</td></tr>";
    }
    if (!empty($sms[SMS_FIO])) {
      $message .= "<tr><th>Ф.И.О. участника:</th><td>{$sms[SMS_FIO]}</td></tr>";
    }
    if ($sms[SMS_CARD_PAYER] >= 0) {
      $message .= "<tr><th>Карта участника:</th><td>{$sms[SMS_CARD_PAYER]}</td></tr>";
    }
    $message .= "<tr><th>Сумма оплаты:</th><td>{$sms[SMS_SUM_PAY]} руб.</td></tr>";
    $message .= "<tr><th>Сообщение:</th><td>\"{$sms[SMS_COMMENT]}\"</td></tr>";
    $message .= "</table>";
    // Получение ссылки на СМС
    $query = Search::getUserQuery($sms[SMS_TIME_SMS], 1, null, $sms[SMS_SUM_PAY], $sms[SMS_FIO]);
    $url = URL::to('service/search', $query);
    $message .= "<a href='{$url}'>Найти SMS</a>";
    // Отсылаем сообщение
    $messages = new Messages();
    $messages->postMessage(WARNING_MESSAGE, $message, $sms[USER_ID]);
  }

  /**
   * Сохранение нераспознанных и неопределённых СМС в таблицу sms_unknown
   * @param array $arr Массив с нераспознанными СМС
   * @return array Массив с результатом сохранения неопознанных СМС:
   * - ['save'] - Массив сохранённых неопознанных СМС
   * - ['not_save'] - Массив не сохранённых неопознанных СМС (уже имеющихся в БД)
   */
  function saveUnknownSMS ($arr) {
    // Инициализация
    $smsSave = array();
    $smsNotSave = array();
    if (!empty($arr)) {
      foreach ($arr as $key => $sms) {
        // Добавляем ID пользователя, если его нет
        $sms[USER_ID] = (isset($sms[USER_ID])) ? $sms[USER_ID] : $this->user->getUserId();
        // Проверяем на наличие повторяющихся SMS
        if (!$this->db->smsUnknownExist($sms)) {
          $smsSave[$key] = $sms;
        } else {
          $smsNotSave[$key] = $sms;
        }
      }
    }
    // Сохраняем СМС в БД
    if (!empty($smsSave)) {
      $this->db->addUnknownSMS($smsSave);
    }
    $result['save'] = $smsSave;
    $result['not_save'] = $smsNotSave;
    return $result;
  }

  /**
   * Проверить, содержит ли комментарий номером карты
   * @param $comment string Строка с комментарием
   * @return bool Результат проверки
   */
  function commentContainsCard ($comment) {
    $result = false;
    $pattern = Kit::WU('#^\d{4}$#');
    if (preg_match($pattern, $comment)) {
      $result = true;
    }
    return $result;

  }

  /**
   * Получить расшифровку подтипа шаблона
   * @param $type int Тип шаблона
   * @param $subType int Подтип шаблона
   */
  static function getSubtypeTpl($type, $subType){
    $transcript = self::getSubtypeArr();
    return $transcript[$type][$subType];
  }

  /**
   * Получить массив подтипов шаблонов SMS
   * @return array Массив подтипов шаблонов SMS
   */
  static function getSubtypeArr(){
    $transcript = array(
      1 => array(
        1 => 'Подтип 1. Возвращает дату, сумму и номер карты',
        2 => 'Подтип 2. Возвращает ФИО и сумму',
        3 => 'Подтип 3. Возвращает ФИО, сумму и комментарий',
        4 => 'Подтип 4. Возвращает дату и сумму',
        5 => 'Подтип 5. Возвращает сумму, ФИО и комментарий',
        6 => 'Подтип 6. Возвращает сумму и ФИО',
      ),
      2 => array(
        1 => 'Подтип 1. Списание средств',
        2 => 'Подтип 2. Вход в систему',
        3 => 'Подтип 3. Пароль для подтверждения операции',
        4 => 'Подтип 4. Выдача наличных',
        5 => 'Подтип 5. Оплата услуг',
        6 => 'Подтип 6. Произведен платеж',
        7 => 'Подтип 7. Оплата мобильного банка',
        8 => 'Подтип 8. Истекает срок действия карты',
        9 => 'Подтип 9. Отмена выдачи наличных',
        10 => 'Подтип 10. Выдача наличных не выполнена',
        11 => 'Подтип 11. Отмена списания',
        12 => 'Подтип 12. Проверьте реквизиты',
        13 => 'Подтип 13. Покупка',
        14 => 'Подтип 14. Отмена покупки',
      ),
      3 => array(
        1 => 'Начало SMS',
      ),
      4 => array(
        1 => 'Конец SMS',
      )
    );
    return $transcript;
  }

  /**
   * Получить массив типов шаблонов SMS
   * @return array Массив типов шаблонов SMS
   */
  static function getTypeArr(){
    $type = array(
      TPL_USEFUL => 'Полезные SMS',
      TPL_USELESS => 'Бесполезные SMS',
      TPL_MARK_START => 'Начала SMS',
      TPL_MARK_END => 'Концы SMS',
    );
    return $type;
  }

}