<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\DataBase.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class DbOperations класс обёртка для методов работающих с БД
 */
class DataBase {
  /**
   * @var array Содержит ссылку на БД
   */
  private $db;
  /**
   * @var string Содержит ключ для шифрования паролей
   */
  private $key;

  /**
   * Конструктор для работы с базой данных
   * @param PDO $pdo Соединение с базой данных
   */
  function __construct ($pdo) {
    $this->db = $pdo;
    $this->key = hash('md5', 'Котоводство');
  }

  /**
   * Создаёт таблицу users
   * @return bool Результат создания таблицы
   */
  function createTableUsers () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `users` (
        `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_login` VARCHAR(30) NOT NULL,
        `user_email` VARCHAR(50) NOT NULL,
        `user_password` VARCHAR(152) NOT NULL,
        `user_sp_login` VARCHAR(100) NOT NULL,
        `user_sp_password` VARCHAR(152) NOT NULL,
        `sp_id` INT UNSIGNED NOT NULL,
        `user_org_id` INT NOT NULL DEFAULT '-1',
        `user_reminding` TINYINT(1) NOT NULL DEFAULT '1',
        `user_filling_day` TINYINT(4) NOT NULL DEFAULT '-1',
        `user_reg_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_activate` TINYINT(1) NOT NULL DEFAULT '0',
        `user_tmp_email` VARCHAR(50) NOT NULL,
        `user_session_id` VARCHAR(32) NOT NULL,
        `user_last_time` TIMESTAMP NULL DEFAULT NULL,
        `user_gift` TINYINT(1) NOT NULL DEFAULT '1',
        `user_blocked` TINYINT(1) NOT NULL DEFAULT '0',
        `user_tz` VARCHAR(50) NOT NULL DEFAULT  'Europe/Moscow',
        `user_request` SMALLINT(6) NOT NULL DEFAULT '1',
        PRIMARY KEY (`user_id`)
      )
      ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу sp
   * @return bool Результат создания таблицы
   */
  function createTableSp () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `sp` (
        `sp_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `sp_site_name` VARCHAR(100) NOT NULL,
        `sp_site_url` VARCHAR(100) NOT NULL,
        `sp_filling_day` TINYINT(4) NOT NULL,
        `sp_full_name` VARCHAR(255) NOT NULL,
        `sp_tz` VARCHAR(50) NOT NULL,
        `sp_request` SMALLINT(6) NOT NULL,
        `sp_active` TINYINT(1) NOT NULL,
        PRIMARY KEY (`sp_id`)
      )
      ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу sms
   * @return bool Результат создания таблицы
   */
  function createTableSms () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `sms` (
        `id_sms` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `sms_time` DATETIME NOT NULL,
        `sms_time_pay` DATETIME NOT NULL,
        `sms_sum` DECIMAL(10,2) UNSIGNED NOT NULL,
        `sms_card_payer` INT(11) NOT NULL DEFAULT '-1',
        `sms_fio` VARCHAR(100) NOT NULL,
        `sms_comment` VARCHAR(100) NOT NULL,
        `sms_return` TINYINT(1) NOT NULL,
        `id_pay` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id_sms`),
        UNIQUE KEY `user_id` (`user_id`,`sms_time`,`sms_time_pay`,`sms_sum`,`sms_card_payer`,`sms_fio`,`sms_comment`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу pay
   * @return bool Результат создания таблицы
   */
  function createTablePay () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `pay` (
        `id_pay` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `purchase_id` INT(10) UNSIGNED NOT NULL,
        `user_purchase_id` INT(10) UNSIGNED NOT NULL,
        `pay_time` DATETIME NOT NULL,
        `pay_sum` DECIMAL(10,2) UNSIGNED NOT NULL,
        `pay_card_payer` INT(10) NOT NULL,
        `pay_created` DATETIME NOT NULL,
        `id_sms` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id_pay`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу purchase
   * @return bool Результат создания таблицы
   */
  function createTablePurchase () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `purchase` (
        `purchase_id` INT(10) UNSIGNED NOT NULL,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `purchase_name` VARCHAR(100) NOT NULL,
        `purchase_pay_to` DATE NOT NULL,
        `sp_id` INT(10) UNSIGNED NOT NULL,
        UNIQUE KEY `purchase_id` (`purchase_id`,`user_id`,`sp_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу users_purchase
   * @return bool Результат создания таблицы
   */
  function createTableUsersPurchase () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `users_purchase` (
        `user_purchase_id` INT(10) UNSIGNED NOT NULL,
        `user_purchase_name` VARCHAR(200) NOT NULL,
        `user_purchase_nick` VARCHAR(100) NOT NULL,
        `sp_id` INT(10) UNSIGNED NOT NULL,
        UNIQUE KEY `user_purchase_id` (`user_purchase_id`,`sp_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу correction
   * @return bool Результат создания таблицы
   */
  function createTableCorrection () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `correction` (
        `correction_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `purchase_id` INT(10) UNSIGNED NOT NULL,
        `user_purchase_id` INT(10) UNSIGNED NOT NULL,
        `correction_sum` DECIMAL(10,2) NOT NULL,
        `correction_comment` VARCHAR(256) NOT NULL,
        PRIMARY KEY (`correction_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Шифрует проли для базы данных
   * @param string $pass Пароль который необходимо зашифровать
   * @return string Возвращает зашифрованный пароль в строке MIME base64
   * @throws Exception
   */
  public function encodePassword ($pass) {
    $pass = iconv('UTF-8', 'WINDOWS-1251', $pass);
    $result = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $pass, MCRYPT_MODE_ECB);
    if ($result === false) {
      throw new Exception('Не удалось зашифровать пароль');
    }
    $result = base64_encode($result);
    return $result;
  }

  /**
   * Проверка наличия регистрации по логину и паролю
   * @param string $login Логин пользователя
   * @param string $pass Пароль пользователя
   * @return null|array Если пользователя с заданными данными нет, то варзвращается false
   * иначе возвращается массив в данными об учётной записи пользователя.
   */
  function checkUserRegistration ($login, $pass) {
    $pass = $this->encodePassword($pass);
    $sql = "
      SELECT *
      FROM  `users`
      WHERE  `" . USER_LOGIN . "` =  ?
        AND  `" . USER_PASSWORD . "` =  ?
      LIMIT 1
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($login, $pass));
    $result = $stm->fetchAll();
    if (!empty($result)) {
      $result[0][USER_PASSWORD] = $this->decodePassword($result[0][USER_PASSWORD]);
      $result[0][USER_SP_PASSWORD] = $this->decodePassword($result[0][USER_SP_PASSWORD]);
      return $result[0];
    }
    return null;
  }

  /**
   * Декодирует пароль
   * @param string $pass Зашифрованный пароль в строке MIME base64
   * @return string Пароль
   * @throws Exception
   */
  public function decodePassword ($pass) {
    $result = $pass;
    if (!empty($pass)) {
      $pass = base64_decode($pass);
      $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $pass, MCRYPT_MODE_ECB);
      if ($result === false) {
        throw new Exception('Не удалось расшифровать пароль');
      }
      // $result = str_replace("\0","",$result);
      $result = rtrim($result, "\0");
      $result = iconv('WINDOWS-1251', 'UTF-8', $result);
    }

    return $result;
  }

  /**
   * Добавляет пользователя сервиса в БД
   * @param array $user Данные о новом пользователе, формата:
   *  - USER_LOGIN - Логин нового пользователя
   *  - USER_EMAIL - Емайл нового пользователя
   *  - USER_PASSWORD - Пароль нового пользователя
   *  - USER_REG_DATE - Время создания аккаунта в текстовом формате
   *  - USER_ACTIVATE - Статус активации аккаунта
   *  - SP_ID - ID сайта СП прошедший валицацию
   *  - USER_FILLING_DAY - количество дней на проставление оплат
   *  - USER_TIME_ZONE - временная зона для пользователя
   *  - USER_REQUEST - тип запроса к сайту СП
   * @return array|false Если пользователь добавлен в БД, то возвращается массив с данными
   * о его учётной записи, если добавить не удалось, то выводится возвращаетмя false.
   * @throws Exception
   */
  function addUser (array $user) {
    $user[USER_PASSWORD] = $this->encodePassword($user[USER_PASSWORD]);
    $sql = "
      INSERT INTO `users` SET
        `" . USER_LOGIN . "` =  ?,
        `" . USER_EMAIL . "` =  ?,
        `" . USER_PASSWORD . "` =  ?,
        `" . USER_REG_DATE . "` =  ?,
        `" . USER_ACTIVATE . "` =  ?,
        `" . SP_ID . "` = ?,
        `" . USER_FILLING_DAY . "` = ?,
        `" . USER_TIME_ZONE . "` = ?,
        `" . USER_REQUEST . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array(
      $user[USER_LOGIN],
      $user[USER_EMAIL],
      $user[USER_PASSWORD],
      $user[USER_REG_DATE],
      $user[USER_ACTIVATE],
      $user[SP_ID],
      $user[USER_FILLING_DAY],
      $user[USER_TIME_ZONE],
      $user[USER_REQUEST]
    ));
    if ($result) {
      $id = $this->db->lastInsertId();
      $result = $this->getUserById($id);
      if (!is_null($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получает данные о пользователя по id.
   * @param string $id ID пользователя
   * @return array|null Массив с найденными пользователями
   */
  public function getUserById ($id) {
    $sql = "
      SELECT * FROM `users` WHERE `" . USER_ID . "` = ? LIMIT 1
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($id));
    $result = $stm->fetchAll();
    if (!empty($result)) {
      $result[0][USER_PASSWORD] = $this->decodePassword($result[0][USER_PASSWORD]);
      $result[0][USER_SP_PASSWORD] = $this->decodePassword($result[0][USER_SP_PASSWORD]);
      return $result[0];
    }
    return null;
  }

  /**
   * Получает данные о пользователя по логину.
   * @param string $login Логин пользователя
   * @return array|null Массив с найденными пользователями
   */
  public function getUserByLogin ($login) {
    $sql = "
      SELECT * FROM `users` WHERE `" . USER_LOGIN . "` = ? LIMIT 1
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($login));
    $result = $stm->fetchAll();
    if (!empty($result)) {
      return $result[0];
    }
    return null;
  }

  /**
   * Получает данные о пользователя по email.
   * @param string $email Email пользователя
   * @return array|null Массив с найденными пользователями
   */
  public function getUserByEmail ($email) {
    $sql = "
      SELECT * FROM `users` WHERE `" . USER_EMAIL . "` = ? LIMIT 1
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($email));
    $result = $stm->fetchAll();
    if (!empty($result)) {
      $result[0][USER_PASSWORD] = $this->decodePassword($result[0][USER_PASSWORD]);
      $result[0][USER_SP_PASSWORD] = $this->decodePassword($result[0][USER_SP_PASSWORD]);
      return $result[0];
    }
    return null;
  }

  /**
   * Задаёт какой набор символов будет использоваться при соединении с БД
   * @param string $encoding Имя кодировки
   * @throws Exception
   * @return bool Результат
   */
  public function setEncodingDb ($encoding) {
    if (empty($encoding)) {
      throw new Exception('Не задана кодировка');
    }
    $sql = "SET NAMES {$encoding}";
    $stm = $this->db->prepare($sql);
    return $stm->execute();
  }

  /**
   * Изменяет настройки хранящиеся в массиве $settings пользователя с ID = $id
   * @param int $id Id пользователя
   * @param array $settings Настройки пользователя
   * - ключ содержит название поля таблицы users которое необходимо изменить
   * - значение поля массива содержит значение настройки
   * @return bool Результат установки новых значений
   */
  public function setUserSettings ($id, array $settings) {
    $sql = "UPDATE `users` SET";
    $arr = array();
    foreach ($settings as $key => $value) {
      // Подготовка пароля для записи в базу данных
      if (($key == USER_SP_PASSWORD or $key == USER_PASSWORD) and !empty($value)) {
        $value = $this->encodePassword($value);
      }
      $sql .= " `{$key}` = ? ,";
      $arr[] = $value;
    }
    $sql = rtrim($sql, ",");
    $sql .= "WHERE user_id = {$id}";
    $stm = $this->db->prepare($sql);
    return $stm->execute($arr);
  }

  /**
   * Создаёт таблицу sms_unknown если её нет
   * @return bool Результат создания таблицы
   */
  public function createTableSmsUnknown () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `sms_unknown` (
        `sms_unknown_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `sms_unknown_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `sms_unknown_time` DATETIME NOT NULL,
        `sms_unknown_text` VARCHAR(1000) NOT NULL,
        `sms_unknown_new` TINYINT(1) NOT NULL DEFAULT '1',
        PRIMARY KEY (`sms_unknown_id`)
      )
      ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создаёт таблицу messages если её нет
   * @return bool Результат создания таблицы
   */
  public function createTableMessages () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `messages` (
        `message_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `message_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `message_new` TINYINT(1) NOT NULL,
        `message_text` VARCHAR(1000) NOT NULL,
        `message_type` TINYINT(3) UNSIGNED NOT NULL,
        PRIMARY KEY (`message_id`)
      )
      ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Активация нового пользователя
   * @param int $id ID пользователя
   * @return null|array Результат активации пользователя:
   * - false - в случае неудачи
   * - array - обновлённые данные о пользователе, в случае успешной активации
   */
  public function activate ($id) {
    $sql = "
      UPDATE `users` SET `" . USER_ACTIVATE . "` = 1 WHERE `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($id));
    // Получаем данные о пользователе
    if ($result) {
      $user = $this->getUserById($id);
      return $user;
    }
    return false;
  }

  /**
   * Получает из БД количество новых сообщений для пользователя
   * @param int $id
   * @return int возвращает количество новых сообщений для пользователя
   */
  public function getCountNewMessages ($id) {
    $sql = "
      SELECT COUNT( * ) AS `messages`
        FROM  `messages`
        WHERE  `" . USER_ID . "` = ?
        AND  `" . MESSAGE_NEW . "` = 1
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($id));
    $result = $stm->fetchAll();
    return (int)$result[0]['messages'];
  }

  /**
   * Получает из БД все сообщения для пользователя по его ID
   * @param int $id ID пользователя
   * @return array Список сообщений для пользователя
   */
  public function getMessages ($id) {
    $sql = "
      SELECT *
        FROM  `messages`
        WHERE  `" . USER_ID . "` = ?
        ORDER BY  `" . MESSAGE_DATE . "` DESC
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($id));
    $result = $stm->fetchAll();
    return $result;
  }

  /**
   * Устанавливает сообщения пользователя с указанными ID как прочитанные
   * @param array $arr Массив с ID сообщений, которые необходимо пометить как прочитанные
   * @param $id int ID пользователя
   * @return bool результат выполнения операции
   */
  public function setMessagesRead (array $arr, $id) {
    // Создаём шаблон для подстановки значений
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "
      UPDATE `messages`
        SET `" . MESSAGE_NEW . "` = '0'
        WHERE  `" . USER_ID . "` = ?
          AND `" . MESSAGE_ID . "` IN ({$in})
    ";
    // Добавляем ID пользователя в начало массива
    array_unshift($arr, $id);
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($arr);
    return $result;
  }

  /**
   * Удалить выделенные сообщения пользователя
   * @param array $arr Массив с ID выделенных сообщений
   * @param int $id ID пользователя
   * @return bool результат выполнения операции
   */
  public function deleteMessages ($arr, $id) {
    // Создаём шаблон для подстановки значений
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "
      DELETE FROM `messages` WHERE `" . USER_ID . "`= ? AND `" . MESSAGE_ID . "` IN ({$in})
    ";
    // Добавляем ID пользователя в начало массива
    array_unshift($arr, $id);
    //      var_dump($sql);
    //      var_dump($arr);
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($arr);
    return $result;
  }

  /**
   * Общая рассылка сообщений
   * @param int $type Тип сообщения
   * @param string $text Текст сообщения
   * @return bool|int Результат
   * - false - в случае неудачи
   * - количество сообщений - в случае успеха
   */
  public function postMessages ($type, $text) {
    // получить все id активных пользователей
    $sql = "
      SELECT `" . USER_ID . "` FROM `users` WHERE `" . USER_ACTIVATE . "` = 1
    ";
    $stm = $this->db->query($sql);
    $result = $stm->fetchAll();
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    //      var_dump($result);
    if (!empty($result)) { // todo оптимизировать одним запросом
      $i = 0;
      // Отослать всем id сообщение
      foreach ($result as $id) {
        $sql = "
          INSERT INTO `messages`  VALUES (NULL, ?, ?, '1', ?, ?)
        ";
        $stm = $this->db->prepare($sql);
        $stm->execute(array($id[USER_ID], $date, $text, $type));
        $i++;
      }
      return (int)$i;
    }
    return false;
  }

  /**
   * Отсылает сообщение пользователю по его ID
   * @param int $type Тип сообщения
   * @param string $text Текст сообщения
   * @param int $id ID получателя
   * @return bool Результат
   */
  public function postMessage ($type, $text, $id) {
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $sql = "
      INSERT INTO `messages`  VALUES (NULL, ?, ?, '1', ?, ?)
    ";
    $stm = $this->db->prepare($sql);
    return $stm->execute(array($id, $date, $text, $type));
  }

  /**
   * Проверяет наличие SMS по полному её описанию
   * @param array $sms Массив с данными SMS
   * @return bool Результат поиска:
   * - true - если SMS с такими параметрами существует
   * - false - если SMS не найдена
   */
  public function smsExist (array $sms) {
    $sql = "
      SELECT *
        FROM  `sms`
        WHERE  `" . USER_ID . "` = ?
          AND  `" . SMS_TIME_SMS . "` =  ?
          AND  `" . SMS_TIME_PAY . "` =  ?
          AND  `" . SMS_SUM_PAY . "` =  ?
          AND  `" . SMS_CARD_PAYER . "` = ?
          AND  `" . SMS_FIO . "` =  ?
          AND  `" . SMS_COMMENT . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $input[] = $sms[USER_ID];
    $input[] = $sms[SMS_TIME_SMS];
    $input[] = $sms[SMS_TIME_PAY];
    $input[] = $sms[SMS_SUM_PAY];
    $input[] = $sms[SMS_CARD_PAYER];
    $input[] = $sms[SMS_FIO];
    $input[] = $sms[SMS_COMMENT];
    $stm->execute($input);
    $result = $stm->fetchAll();
    if (count($result) > 0) {
      return true; // todo возвращать ID
    } else {
      return false;
    }
  }

  /**
   * Добавляет новые СМС в БД (несколько одним запросом)
   * @param array $arrSms Массив с SMS которые надо добавить в БД
   * @return bool Результат добавления
   */
  public function addSMS (array $arrSms) {
    $sql = "
      INSERT INTO `sms` (
        `" . SMS_ID . "` ,
        `" . USER_ID . "` ,
        `" . SMS_TIME_SMS . "` ,
        `" . SMS_TIME_PAY . "` ,
        `" . SMS_SUM_PAY . "` ,
        `" . SMS_CARD_PAYER . "` ,
        `" . SMS_FIO . "` ,
        `" . SMS_COMMENT . "` ,
        `" . SMS_RETURN . "` ,
        `" . PAY_ID . "`
      )
      VALUES ";
    $input = array();
    foreach ($arrSms as $sms) {
      $sql .= " (NULL, ?, ?, ?, ?, ?, ?, ?, '0', '0'),";
      $input[] = $sms[USER_ID];
      $input[] = $sms[SMS_TIME_SMS];
      $input[] = $sms[SMS_TIME_PAY];
      $input[] = $sms[SMS_SUM_PAY];
      $input[] = $sms[SMS_CARD_PAYER];
      $input[] = $sms[SMS_FIO];
      $input[] = $sms[SMS_COMMENT];
    }
    $sql = rtrim($sql, ",");
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    return $result;
  }

  /**
   * Проверяет наличие неопределённой SMS по полному её описанию
   * @param array $sms Массив с данными неопределённой SMS
   * @return bool Результат поиска:
   * - true - если SMS с такими параметрами существует
   * - false - если SMS не найдена
   */
  public function smsUnknownExist (array $sms) {
    $sql = "
      SELECT *
        FROM  `sms_unknown`
        WHERE  `" . USER_ID . "` = ?
          AND  `" . SMS_UNKNOWN_TIME . "` =  ?
          AND  `" . SMS_UNKNOWN_TEXT . "` =  ?
    ";
    $stm = $this->db->prepare($sql);
    $input[] = $sms[USER_ID];
    $input[] = $sms[SMS_TIME_SMS];
    $input[] = $sms[SMS_UNKNOWN_TEXT];
    $stm->execute($input);
    $result = $stm->fetchAll();
    if (count($result) > 0) {
      return true; // todo возвращать ID
    } else {
      return false;
    }
  }

  /**
   * Добавляет неопределённые СМС в БД (несколько за раз)
   * @param array $arrSms Массив c SMS которые необходимо добавить в БД
   * @return bool Результат добавления
   */
  public function addUnknownSMS (array $arrSms) {
    $sql = "
      INSERT INTO `sms_unknown` (
        `" . SMS_UNKNOWN_ID . "` ,
        `" . USER_ID . "` ,
        `" . SMS_UNKNOWN_TIME . "` ,
        `" . SMS_UNKNOWN_TEXT . "`
      )
      VALUES";
    $input = array();
    foreach ($arrSms as $sms) {
      $sql .= " (NULL, ?, ?, ?),";
      $input[] = $sms[USER_ID];
      $input[] = $sms[SMS_TIME_SMS];
      $input[] = $sms[SMS_UNKNOWN_TEXT];
    }
    $sql = rtrim($sql, ",");
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    return $result;
  }

  /**
   * Количество записей в таблице sms
   * @return int Количество записей в таблице
   */
  public function getCountRecordsSms () {
    $sql = "SELECT COUNT(*) AS `count` FROM `sms`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице pay
   * @return int Количество записей в таблице
   */
  public function getCountRecordsPay () {
    $sql = "SELECT COUNT(*) AS `count` FROM `pay`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице correction
   * @return int Количество записей в таблице
   */
  public function getCountRecordsCorrection () {
    $sql = "SELECT COUNT(*) AS `count` FROM `correction`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице sms_unknown
   * @return int Количество записей в таблице
   */
  public function getCountRecordsSmsUnknown () {
    $sql = "SELECT COUNT(*) AS `count` FROM `sms_unknown`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице purchase
   * @return int Количество записей в таблице
   */
  public function getCountRecordsPurchase () {
    $sql = "SELECT COUNT(*) AS `count` FROM `purchase`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице sp
   * @return int Количество записей в таблице
   */
  public function getCountRecordsSp () {
    $sql = "SELECT COUNT(*) AS `count` FROM `sp`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице users
   * @return int Количество записей в таблице
   */
  public function getCountRecordsUsers () {
    $sql = "SELECT COUNT(*) AS `count` FROM `users`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Количество записей в таблице users_purchase
   * @return int Количество записей в таблице
   */
  public function getCountRecordsUsersPurchase () {
    $sql = "SELECT COUNT(*) AS `count` FROM `users_purchase`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Получение всех SMS из таблицы sms_unknown
   */
  public function getAllSmsUnknown () {
    $sql = "
      SELECT * FROM `sms_unknown`
      ORDER BY  `" . SMS_UNKNOWN_TIME . "` DESC
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return $result;
  }

  /**
   * Удаляет нераспознанные СМС по ID
   * @param array $arr Массив с ID удаляемых СМС
   * @return false|int количество удалённых СМС
   */
  public function deleteSmsUnknown (array $arr) {
    // Создаём шаблон для подстановки значений
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "
      DELETE FROM `sms_unknown` WHERE `" . SMS_UNKNOWN_ID . "` IN ({$in})
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($arr);
    if ($result) {
      $result = $stm->rowCount();
    }
    return $result;
  }

  /**
   * Получает нераспознанные СМС по ID
   * @param array $arr Массив с ID SMS которые надо получить
   * @return bool|array Результат выполнения операции
   * - Массив с нераспознанными СМС
   * - false - в случае неудачи или если массив пуст
   */
  public function getSmsUnknownById (array $arr) {
    // Создаём шаблон для подстановки значений
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "
      SELECT * FROM `sms_unknown` WHERE `" . SMS_UNKNOWN_ID . "` IN ({$in})
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($arr);
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получает список всех сайтов СП
   * @return array|false Результата выполнения метода
   */
  public function getAllSP () {
    $sql = "SELECT * FROM `sp`";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получает список доступных сайтов СП
   * @return array|false Результата выполнения метода
   */
  public function getActiveSP () {
    $sql = "SELECT * FROM `sp` WHERE  `" . SP_ACTIVE . "` =1";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return false;
  }

  /**
   * Получение данных о сайте СП по ID
   * @param int $id ID сайта СП
   * @return array|false Результата выполнения:
   * - false - ничего не найдено
   * - array - массив с данными о сайте СП
   */
  public function getSpById ($id) {
    $sql = "
      SELECT * FROM `sp` WHERE `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($id));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Получение закупки по её ID и ID сайта СП
   * @param $purchaseId int ID закупки
   * @param $userId int ID пользователя
   * @param $spId int ID сайта СП
   * @return array|false Массив с данными о закупке
   */
  public function getPurchase ($purchaseId, $userId, $spId) {
    $sql = "
      SELECT * FROM `purchase` WHERE `" . PURCHASE_ID . "` = ? AND `" . USER_ID . "` = ? AND `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($purchaseId, $userId, $spId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Добавляет новую закупку в БД
   * @param $purchaseName string Название закупки
   * @param $purchaseId int ID закупки
   * @param $userId int ID пользователя
   * @param $spId int ID сайта СП
   * @param $payTo string Время до которого должны оплатить УЗ
   * @return bool Результат операции
   */
  public function addPurchase ($purchaseName, $purchaseId, $userId, $spId, $payTo) {
    $sql = "INSERT INTO `purchase` VALUES (?, ?, ?, ?, ?)";
    $stm = $this->db->prepare($sql);
    return $stm->execute(array($purchaseId, $userId, $purchaseName, $payTo, $spId));
  }

  /**
   * Обновляет имя закупки
   * @param $purchaseName string Название закупки
   * @param $purchaseId int ID закупки
   * @param $userId int ID пользователя
   * @param $spId int ID сайта СП
   * @param $payTo string Время до которого должны оплатить УЗ
   * @return bool Результат операции
   */
  public function updatePurchase ($purchaseName, $purchaseId, $userId, $spId, $payTo) {
    $sql = "
      UPDATE `purchase` SET
        `" . PURCHASE_NAME . "` = ?,
        `" . PURCHASE_PAY_TO . "` = ?
      WHERE `" . PURCHASE_ID . "` = ?
        AND `" . USER_ID . "` = ?
        AND `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($purchaseName, $payTo, $purchaseId, $userId, $spId));
    return $result;
  }

  /**
   * Получение всех закупок указанного пользователя
   * @param $userId int ID пользователя
   * @param $spId int ID сайта СП
   * @param $filter
   * @return array|false Список закупок
   */
  public function getAllPurchaseOfUser ($userId, $spId, $filter = '') {
    $input = array();
    $sql = "SELECT * FROM `purchase` WHERE `" . USER_ID . "` = ? AND `" . SP_ID . "` = ?";
    $input[] = $userId;
    $input[] = $spId;
    if (!empty($filter)) {
      $sql .= " AND `" . PURCHASE_NAME . "` LIKE ? ";
      $input[] = "%{$filter}%";
    }
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получить платёж по всем данным
   * @param $userId int ID пользователя
   * @param $purchaseId int ID закупки
   * @param $purchaseUserId int ID участника закупки
   * @param $timePay string Время платежа в строковом формате
   * @param $sum float Сумма платежа
   * @param $card string Карта с которой было поступление средств
   * @param $timeCreatedPay string Время платежа в строковом формате
   * @return array|false Массив с найденным платежом
   */
  public function getPay ($userId, $purchaseId, $purchaseUserId, $timePay, $sum, $card, $timeCreatedPay) {
    // todo сделать параметры через ассоциативный массив
    $sql = "
      SELECT *
        FROM `pay`
        WHERE `" . USER_ID . "` = ?
          AND `" . PURCHASE_ID . "` = ?
          AND `" . USER_PURCHASE_ID . "` = ?
          AND `" . PAY_TIME . "` = ?
          AND `" . PAY_SUM . "` = ?
          AND `" . PAY_CARD_PAYER . "` = ?
          AND `" . PAY_CREATED . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userId, $purchaseId, $purchaseUserId, $timePay, $sum, $card, $timeCreatedPay));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Поиск неиспользованных SMS для платежа
   * @param $userId int ID пользователя
   * @param $timePay string Время платежа в строковом формате
   * @param $fork int Временная вилка, часов
   * @param $sum float Сумма платежа
   * @param $card string Карта с которой было поступление средств
   * @return array|false Массив с найденными SMS
   */
  public function findSms ($userId, $timePay, $fork, $sum, $card) {
    // todo сделать параметры через ассоциативный массив
    // Расчёт временной вилки
    $date = new DateTime();
    $dateMin = $date->setTimestamp(strtotime($timePay))->modify('-' . $fork . ' hour')->format('Y-m-d H:i:s');
    $dateMax = $date->setTimestamp(strtotime($timePay))->modify('+' . $fork . ' hour')->format('Y-m-d H:i:s');
    $sql = "
      SELECT * FROM `sms`
        WHERE (
            ( `" . SMS_TIME_PAY . "` >= ? AND `" . SMS_TIME_PAY . "` <= ? AND `" . SMS_CARD_PAYER . "` = ? )
            OR
            ( `" . SMS_TIME_SMS . "` >= ? AND `" . SMS_TIME_SMS . "` <= ? AND `" . SMS_CARD_PAYER . "` = '-1' AND `" . SMS_FIO . "` != '')
          )
          AND `" . USER_ID . "` = ?
          AND `" . SMS_SUM_PAY . "` = ?
          AND `" . SMS_RETURN . "` = 0
          AND `" . PAY_ID . "` = 0
        ORDER BY `" . SMS_TIME_PAY . "` , `" . SMS_TIME_SMS . "`
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $dateMin;
    $input[] = $dateMax;
    $input[] = $card;
    $input[] = $dateMin;
    $input[] = $dateMax;
    $input[] = $userId;
    $input[] = $sum;
    $result = $stm->execute($input);
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получить участника закупки по его ID
   * @param $userPurchaseId int ID участника закупки
   * @param $spId int ID сайта СП на котором работает пользователь
   * @return array|false Массив с найденным участником закупки
   */
  public function getUserPurchase ($userPurchaseId, $spId) {
    $sql = "
      SELECT * FROM `users_purchase`
      WHERE `" . USER_PURCHASE_ID . "` = ?
        AND `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userPurchaseId, $spId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Добавить участника закупки в БД
   * @param $userPurchaseId int ID участника закупки
   * @param $fio string Имя учатника закупки (ФИО)
   * @param $nick string Ник учатника закупки
   * @param $spId int ID сайта СП на котором работает пользователь
   * @return bool Результат операции
   */
  public function addUserPurchase ($userPurchaseId, $fio, $nick, $spId) {
    // todo Передавать аргументы ассоциативным массивом
    $sql = "
      INSERT INTO `users_purchase` VALUES (
        ?, ?, ?, ?
      )
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userPurchaseId, $fio, $nick, $spId));
    return $result;
  }

  /**
   * Обновить участника закупки в БД
   * @param $userPurchaseId int ID участника закупки
   * @param $fio string Имя учатника закупки (ФИО)
   * @param $nick string Ник учатника закупки
   * @param $spId int ID сайта СП на котором работает пользователь
   * @return bool Результат операции
   */
  public function updateUserPurchase ($userPurchaseId, $fio, $nick, $spId) {
    $sql = "
      UPDATE `users_purchase` SET
        `" . USER_PURCHASE_NAME . "` = ?,
        `" . USER_PURCHASE_NICK . "` = ?
      WHERE `" . USER_PURCHASE_ID . "` = ?
        AND `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($fio, $nick, $userPurchaseId, $spId));
    return $result;
  }

  /**
   * Получение SMS по её ID
   * @param $idSms int ID SMS
   * @return array|false Массив с найденной SMS
   */
  public function getSmsById ($idSms) {
    $sql = "
      SELECT * FROM `sms` WHERE `" . SMS_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($idSms));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Получить массив с корректировками для закупки
   * @param $userId int ID пользователя Разносилки
   * @param $purchaseId int ID закупки для которой ищутся корректировки
   * @return array|false Массив с корректировками для закупки
   */
  public function getCorrectionToPurchase ($userId, $purchaseId) {
    $sql = "
      SELECT * FROM `correction` WHERE
        `" . USER_ID . "` = ?
        AND `" . PURCHASE_ID . "` = ?
        ORDER BY `" . CORRECTION_ID . "` ASC
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userId, $purchaseId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Проставить платёж
   * @param array $pay Массив с данными платежа, формата:
   *  - [USER_ID] - ID пользователя
   *  - [PURCHASE_ID] - ID закупки
   *  - [USER_PURCHASE_ID] - ID участника закупки
   *  - [PAY_TIME] - Время платежа
   *  - [PAY_SUM] - Сумма платежа
   *  - [PAY_CARD_PAYER] - Карта плательщика
   *  - [PAY_CREATED] - Время создания платежа
   *  - [SMS_ID] - ID SMS
   * @return false|int ID добавленного платежа
   */
  public function fillingPay (array $pay) {
    // Добавление платежа
    $payId = $this->addPay($pay);
    if ($payId !== false) {
      // Привязка СМС к платежу
      $this->updateSms($payId, $pay[SMS_ID]); // todo если неудача, то удалить платёж
    }
    return $payId;
  }

  /**
   * Добавить платёж (разделено для тестирования).
   * Для добавления платежа использовать @see fillingPay.
   * @param array $pay Массив с данными платежа, формата:
   *  - [USER_ID] - ID пользователя
   *  - [PURCHASE_ID] - ID закупки
   *  - [USER_PURCHASE_ID] - ID участника закупки
   *  - [PAY_TIME] - Время платежа
   *  - [PAY_SUM] - Сумма платежа
   *  - [PAY_CARD_PAYER] - Карта плательщика
   *  - [PAY_CREATED] - Время создания платежа
   *  - [SMS_ID] - ID SMS
   * @return false|int ID добавленого платежа
   */
  function addPay (array $pay) {
    $sql = "
      INSERT INTO `pay` (
        `" . PAY_ID . "` ,
        `" . USER_ID . "` ,
        `" . PURCHASE_ID . "` ,
        `" . USER_PURCHASE_ID . "` ,
        `" . PAY_TIME . "` ,
        `" . PAY_SUM . "` ,
        `" . PAY_CARD_PAYER . "` ,
        `" . PAY_CREATED . "` ,
        `" . SMS_ID . "`
      ) VALUES (
        NULL, ?, ?, ?, ?, ?, ?, ?, ?
      )
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $pay[USER_ID];
    $input[] = $pay[PURCHASE_ID];
    $input[] = $pay[USER_PURCHASE_ID];
    $input[] = $pay[PAY_TIME];
    $input[] = $pay[PAY_SUM];
    $input[] = $pay[PAY_CARD_PAYER];
    $input[] = $pay[PAY_CREATED];
    $input[] = $pay[SMS_ID];
    if ($stm->execute($input)) {
      return (int)$this->db->lastInsertId();
    }
    return false;
  }

  /**
   * Привязать СМС к платежу (разделено для тестирования).
   * Для добавления платежа использовать @see fillingPay.
   * @param $payId int ID платежа
   * @param $smsId int ID СМС
   * @return bool Результат выполнения
   */
  function updateSms ($payId, $smsId) {
    $sql = "
      UPDATE `sms` SET `" . PAY_ID . "` = ? WHERE `" . SMS_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    return $stm->execute(array($payId, $smsId));
  }

  /**
   * Удалить платёж
   * @param $payId int ID платежа который необходимо удалить
   * @return bool Результата удления
   */
  public function payErrorDelete ($payId) {
    $result = false;
    $sql = "
      DELETE FROM `pay` WHERE `" . PAY_ID . "` = ? AND `" . SMS_ID . "` = 0
    ";
    $stm = $this->db->prepare($sql);
    if ($stm->execute(array($payId))) {
      if ($stm->rowCount() > 0) {
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Добавить корректировку для платежа
   * @param array $correction Массив с данными корректировки, формата:
   *  - [USER_ID] - ID пользователя Разносилки
   *  - [PURCHASE_ID] - ID закупки
   *  - [USER_PURCHASE_ID] - ID участника закупки
   *  - [CORRECTION_SUM] - сумма корректировки (float)
   *  - [CORRECTION_COMMENT] - комментарий для корректировки
   * @return false|int В случае успеха ID добавленной корректировки
   */
  public function addCorrection (array $correction) {
    $sql = "
      INSERT INTO `correction` (
        `" . CORRECTION_ID . "` ,
        `" . USER_ID . "` ,
        `" . PURCHASE_ID . "` ,
        `" . USER_PURCHASE_ID . "` ,
        `" . CORRECTION_SUM . "` ,
        `" . CORRECTION_COMMENT . "`
      ) VALUES (
        NULL, ?, ?, ?, ?, ?
      )
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $correction[USER_ID];
    $input[] = $correction[PURCHASE_ID];
    $input[] = $correction[USER_PURCHASE_ID];
    $input[] = $correction[CORRECTION_SUM];
    $input[] = $correction[CORRECTION_COMMENT];
    if ($stm->execute($input)) {
      return (int)$this->db->lastInsertId();
    }
    return false;
  }

  /**
   * Получить корректировку по её ID
   * @param $correctionId int ID корректировки
   * @return array|false Массив с корректировкой
   */
  public function getCorrectionById ($correctionId) {
    $sql = "
      SELECT * FROM `correction` WHERE `" . CORRECTION_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($correctionId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Удаление корректировки
   * @param $correctionId int ID корректировки
   * @return bool Результат операции
   */
  public function correctionDelete ($correctionId) {
    $result = false;
    $sql = "
      DELETE FROM `correction` WHERE `" . CORRECTION_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    if ($stm->execute(array($correctionId))) {
      if ($stm->rowCount() > 0) {
        $result = true;
      }
    }
    return $result;
  }

  /**
   * Поиск SMS по заданным параметрам
   * @param $param array Массив с параметрами:
   *  - [USER_ID] - ID пользователя Разносилки, int
   *  - [SMS_TIME_SMS] - дата и время платежа, string
   *  - ['fork'] - диапазон поиска (дней), int
   *  - [SMS_CARD_PAYER] - номер карты, int
   *  - [SMS_SUM_PAY] - сумма платежа, float
   *  - [SMS_FIO] - Ф.И.О. плательщика, string
   *  - ['type'] - тип SMS, int:
   *    - 0 - любой
   *    - 1 - Только с номером карты
   *    - 2 - Только с ФИО
   *  - ['status'] - статус SMS, int:
   *    - 0 - любой
   *    - 1 - использованная СМС
   *    - 2 - Не использованная СМС
   *  - ['message'] - только SMS с сообщением, bool
   *  - ['return'] - только врзвращённые SMS, bool
   * @return array|false Массив с найденными SMS
   */
  public function searchSMS ($param) {
    // Составляем запрос для поиска SMS
    $sql = "SELECT * FROM `sms` WHERE `" . USER_ID . "` = ? ";
    $input[] = $param[USER_ID];
    // Добавить условие поиска по времени
    if (!empty($param[SMS_TIME_SMS])) {
      $date = new DateTime();
      $dateMin = $date->setTimestamp(strtotime($param[SMS_TIME_SMS]))->modify('-' . $param['fork'] . ' day')->format('Y-m-d H:i:s');
      $dateMax = $date->setTimestamp(strtotime($param[SMS_TIME_SMS]))->modify('+' . $param['fork'] . ' day')->format('Y-m-d H:i:s');
      $sql .= " AND `" . SMS_TIME_SMS . "` BETWEEN ? AND ? ";
      $input[] = $dateMin;
      $input[] = $dateMax;
    }
    // Добавить условие поиска по номеру карты
    if (!empty($param[SMS_CARD_PAYER])) {
      $sql .= " AND `" . SMS_CARD_PAYER . "` = ? ";
      $input[] = $param[SMS_CARD_PAYER];
    }
    // Добавить условие поиска по сумме
    if (!empty($param[SMS_SUM_PAY])) {
      $sql .= " AND `" . SMS_SUM_PAY . "` = ? ";
      $input[] = $param[SMS_SUM_PAY];
    }
    // Добавить условие поиска по ФИО
    if (!empty($param[SMS_FIO])) {
      $sql .= " AND `" . SMS_FIO . "` LIKE ? ";
      $input[] = "%{$param[SMS_FIO]}%";
    }
    // Фмльтр по типу СМС
    switch ($param['type']) {
      // Номер карты
      case 1 :
        $sql .= " AND `" . SMS_CARD_PAYER . "` != '-1' AND `" . SMS_FIO . "` = '' ";
        break;
      // ФИО
      case 2 :
        $sql .= " AND `" . SMS_CARD_PAYER . "` = '-1' AND `" . SMS_FIO . "` != '' ";
        break;
    }
    // Фмльтр по статусу СМС
    switch ($param['status']) {
      // использованная СМС
      case 1 :
        $sql .= " AND (`" . PAY_ID . "` != '0' OR `" . SMS_RETURN . "` = '1') ";
        break;
      // Не использованная СМС
      case 2 :
        $sql .= " AND `" . PAY_ID . "` = '0' AND `" . SMS_RETURN . "` = '0' ";
        break;
    }
    // Только СМС с сообщением
    if ($param['message']) {
      $sql .= " AND `" . SMS_COMMENT . "`  != '' ";
    }
    // Только возвращённые СМС
    if ($param['return']) {
      $sql .= " AND `" . SMS_RETURN . "`  = '1' ";
    }
    $sql .= " ORDER BY `" . SMS_TIME_SMS . "`";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получить платёж по его ID
   * @param $idPay int ID платежа
   * @return bool|array Массив содержащий данные платежа
   */
  public function getPayById ($idPay) {
    $sql = "
      SELECT * FROM `pay` WHERE `" . PAY_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($idPay));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Удаление платежа и освобождение привязанной к нему СМС
   * @param $payId int ID платежа
   * @param $smsId int ID SMS
   * @return bool Результат операции
   */
  public function payDelete ($payId, $smsId) {
    $result = false;
    // Удаление платежа
    $sql = "
      DELETE FROM `pay` WHERE `" . PAY_ID . "` = ? AND `" . SMS_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    if ($stm->execute(array($payId, $smsId))) {
      if ($stm->rowCount() > 0) {
        $result = true;
      }
    }
    // Освобождение СМС
    if ($result) {
      $result = $this->updateSms(0, $smsId);
    }
    return $result;
  }

  /**
   * Получение актуальной ID сессии
   * @param $userId int ID пользователя
   * @return false|string ID сессии
   */
  public function getUserSID ($userId) {
    $sql = "
      SELECT  `" . USER_SESSION_ID . "` FROM  `users` WHERE  `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0][USER_SESSION_ID];
      }
    }
    return false;
  }

  /**
   * Перезаписать ID текущей сессии
   * @param $sid string ID текущей сессии
   * @param $uid int ID пользователя
   * @return bool Результат операции
   */
  public function setUserSID ($sid, $uid) {
    $sql = "
      UPDATE `users` SET `" . USER_SESSION_ID . "` = ? WHERE  `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($sid, $uid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Отметить СМС как возвращённую
   * @param $idUser int ID пользователя
   * @param $idSms int ID SMS
   * @return bool Результат операции
   */
  public function setReturnSms ($idUser, $idSms) {
    $sql = "
      UPDATE `sms` SET `" . SMS_RETURN . "` = 1 WHERE `" . USER_ID . "` = ? AND `" . SMS_ID . "` = ? AND `" . PAY_ID . "` = 0 AND `" . SMS_RETURN . "` = 0
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($idUser, $idSms));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Отменить возврат СМС
   * @param $idUser int ID пользователя
   * @param $idSms int ID SMS
   * @return bool Результат операции
   */
  public function delReturnSms ($idUser, $idSms) {
    $sql = "
      UPDATE `sms` SET `" . SMS_RETURN . "` = 0 WHERE `" . USER_ID . "` = ? AND `" . SMS_ID . "` = ? AND `" . PAY_ID . "` = 0 AND `" . SMS_RETURN . "` = 1
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($idUser, $idSms));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Получить общую найденную сумму по данной закупке из платежей
   * @param $userId int ID пользователя
   * @param $purchaseId int ID закупки
   * @return float Общая найденная сумма из платежей
   */
  public function getFoundSumPurchase ($userId, $purchaseId) { // todo попробовать оптимизировать запрос, изучить временные таблицы (в данный момент перебирает все СМС)
    $sql = "
      SELECT SUM(`sms`.`" . SMS_SUM_PAY . "`) AS `sum`
        FROM  `sms`
      INNER JOIN  `pay` ON  `sms`.`" . PAY_ID . "` =  `pay`.`" . PAY_ID . "`
        WHERE  `sms`.`" . USER_ID . "` =  ?
          AND  `pay`.`" . PURCHASE_ID . "` =  ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userId, $purchaseId));
    if ($result) {
      $result = $stm->fetchAll();
      return $result[0]['sum'] ? $result[0]['sum'] : 0;
    }
    return false;
  }

  /**
   * Получить общую найденную сумму по данной закупке из корректировок
   * @param $userId int ID пользователя
   * @param $purchaseId int ID закупки
   * @return float Общая найденная сумма из корректировок
   */
  public function getFoundSumCorrection ($userId, $purchaseId) {
    $sql = "
      SELECT  SUM(`" . CORRECTION_SUM . "`) AS `sum` FROM `correction` WHERE `" . USER_ID . "` =  ? AND `" . PURCHASE_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userId, $purchaseId));
    if ($result) {
      $result = $stm->fetchAll();
      return $result[0]['sum'] ? $result[0]['sum'] : 0;
    }
    return false;
  }

  /**
   * Получить пользователя по его ID на сайте СП
   * @param $spId int ID выбранного сайта СП
   * @param $orgId int ID организатора
   * @return false|array Массив с данными пользователя
   */
  public function getUserFromSpAndOrgId ($spId, $orgId) {
    $sql = "
      SELECT * FROM  `users` WHERE  `" . SP_ID . "` = ? AND  `" . USER_ORG_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($spId, $orgId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        $result[0][USER_PASSWORD] = $this->decodePassword($result[0][USER_PASSWORD]);
        $result[0][USER_SP_PASSWORD] = $this->decodePassword($result[0][USER_SP_PASSWORD]);
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Получить всех пользователей разносилки
   * @return array|false Массив со списком всех пользователей
   */
  public function getAllUsers () {
    $sql = "
      SELECT `users`.*, `sp`.`" . SP_SITE_NAME . "`, `sp`.`" . SP_SITE_URL . "`
        FROM `users` LEFT OUTER JOIN  `sp`
          ON `users`.`" . SP_ID . "` =  `sp`.`" . SP_ID . "`
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получить количество загруженных пользователем СМС
   * @param $userId int ID пользователя
   * @return int Количество загруженных пользователем СМС
   */
  public function getCountSmsByIdUser ($userId) {
    $sql = "SELECT COUNT(*) AS `count` FROM `sms` WHERE  `" . USER_ID . "` =  ? ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($userId));
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Получить количество добавленных пользователем закупок
   * @param $userId int ID пользователя
   * @return int Количество добавленных пользователем закупок
   */
  public function getCountPurchaseByIdUser ($userId) {
    $sql = "SELECT COUNT(*) AS `count` FROM `purchase` WHERE  `" . USER_ID . "` =  ? ";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($userId));
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Получить количество проставленных пользователем оплат (без учёта ошибочных)
   * @param $userId int ID пользователя
   * @return int Количество проставленных пользователем оплат (без учёта ошибочных)
   */
  public function getCountPayByIdUser ($userId) {
    $sql = "SELECT COUNT(*) AS `count` FROM `pay` WHERE  `" . USER_ID . "` =  ? AND `" . SMS_ID . "` !=  0";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($userId));
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Создание таблицы
   * @return bool
   */
  public function createTableDelivery () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `delivery` (
        `delivery_id` INT(11) NOT NULL AUTO_INCREMENT,
        `delivery_email` VARCHAR(50) NOT NULL,
        `delivery_subject` VARCHAR(255) NOT NULL,
        `delivery_body` TEXT NOT NULL,
        PRIMARY KEY (`delivery_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Создание таблицы settings
   * @return bool
   */
  public function createTableSettings () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `settings` (
        `settings_name` VARCHAR(255) NOT NULL,
        `settings_value` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`settings_name`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Добавить в БД письма для рассылки
   * @param array $deliverys Массив с письмами
   * @return bool Результат операции
   */
  public function addDelivery (array $deliverys) {
    $sql = "
      INSERT INTO `delivery` (
        `" . DELIVERY_ID . "` ,
        `" . DELIVERY_EMAIL . "` ,
        `" . DELIVERY_SUBJECT . "` ,
        `" . DELIVERY_BODY . "`
      )
      VALUES";
    $input = array();
    foreach ($deliverys as $delivery) {
      $sql .= " (NULL, ?, ?, ?),";
      $input[] = $delivery[DELIVERY_EMAIL];
      $input[] = $delivery[DELIVERY_SUBJECT];
      $input[] = $delivery[DELIVERY_BODY];
    }
    $sql = rtrim($sql, ",");
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Получить первые в очереди письма для рассылки
   * @param $countMail int Количество писем
   * @return array|bool Массив с письмами
   */
  public function getDelivery ($countMail) {
    $sql = "
      SELECT * FROM  `delivery` ORDER BY  `" . DELIVERY_ID . "` ASC  LIMIT 0 , ?
    ";
    $stm = $this->db->prepare($sql);
    $stm->bindValue(1, $countMail, PDO::PARAM_INT);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Удалить письма из рассылки по списку их ID
   * @param array $deleteList Массив содержащий ID писем на рассылку
   * @return bool Результат выполнения операции
   */
  public function delDelivery (array $deleteList) {
    $in = str_repeat('?,', count($deleteList) - 1) . '?';
    $sql = "
      DELETE FROM `delivery` WHERE `" . DELIVERY_ID . "` IN ($in)
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($deleteList);
    return $result;
  }

  /**
   * Удалить все старые неактивированные аккаунты
   * @param $currentTime int Метка текущего времени и даты
   * @return bool|int Количество удалённых аккаунтов
   */
  public function deleteAllOldNotActivateUser ($currentTime) {
    // Подготовка даты
    $date = new DateTime();
    $dateDelete = $date->setTimestamp($currentTime)->modify('-30 day')->format('Y-m-d H:i:s');
    // Запрос
    $sql = "
      DELETE FROM `users`
      WHERE `" . USER_REG_DATE . "` < ?
        AND `" . USER_ACTIVATE . "` = 0
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($dateDelete));
    if ($result) {
      $result = $stm->rowCount();
    }
    return $result;
  }

  /**
   * Получить статус базы данных
   * @return array|bool Массив с данными по всем таблицам БД
   */
  public function getStatusDB () {
    $sql = "
      SHOW TABLE STATUS
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Проверить наличие поля в таблице
   * @param $table string Имя таблицы
   * @param $field string Имя поля
   * @return bool Результат проверки
   */
  public function issetField ($table, $field) {
    $sql = "
      SHOW COLUMNS FROM `$table` WHERE `Field` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($field));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Импортировать список сайтов СП
   * @param array $spList Массив со списком сайтов СП, формата:
   *  - [x] - сайт СП
   *    - [SP_ID] - ID сайта СП
   *    - [SP_SITE_NAME] - Короткое название сайта СП
   *    - [SP_SITE_URL] - URL сайта СП
   *    - [SP_FILLING_DAY] - Количество дней на проставление оплат по правилам сайта СП
   *    - [SP_FULL_NAME] - Полное название сайта СП
   *    - [SP_TIME_ZONE] - Временная зона сайта СП
   *    - [SP_REQUEST] - Тип запроса к сайту СП
   *    - [SP_ACTIVE] - Доступен ли сайт СП
   * @return bool Результат добавления
   */
  public function importSpList (array $spList) {
    $sql = "
      INSERT INTO `sp` (
        `" . SP_ID . "` ,
        `" . SP_SITE_NAME . "` ,
        `" . SP_SITE_URL . "` ,
        `" . SP_FILLING_DAY . "` ,
        `" . SP_DESCRIPTION . "` ,
        `" . SP_TIME_ZONE . "` ,
        `" . SP_REQUEST . "` ,
        `" . SP_ACTIVE . "`
      )
      VALUES";
    $input = array();
    foreach ($spList as $sp) {
      $sql .= " (?, ?, ?, ?, ?, ?, ?, ?),";
      $input[] = $sp[SP_ID];
      $input[] = $sp[SP_SITE_NAME];
      $input[] = $sp[SP_SITE_URL];
      $input[] = $sp[SP_FILLING_DAY];
      $input[] = $sp[SP_DESCRIPTION];
      $input[] = $sp[SP_TIME_ZONE];
      $input[] = $sp[SP_REQUEST];
      $input[] = $sp[SP_ACTIVE];
    }
    $sql = rtrim($sql, ",");
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Добавить сайт СП
   * @param array $spList Массив с данными сайта СП, формата:
   *  - [SP_SITE_NAME] - Короткое название сайта СП
   *  - [SP_SITE_URL] - URL сайта СП
   *  - [SP_FILLING_DAY] - Количество дней на проставление оплат по правилам сайта СП
   *  - [SP_FULL_NAME] - Полное название сайта СП
   *  - [SP_TIME_ZONE] - Временная зона сайта СП
   *  - [SP_REQUEST] - Тип запроса к сайту СП
   *  - [SP_ACTIVE] - Доступен ли сайт СП
   * @return bool Результат добавления
   */
  public function addSp (array $spList) {
    $sql = "
      INSERT INTO `sp` (
        `" . SP_ID . "` ,
        `" . SP_SITE_NAME . "` ,
        `" . SP_SITE_URL . "` ,
        `" . SP_FILLING_DAY . "` ,
        `" . SP_DESCRIPTION . "` ,
        `" . SP_TIME_ZONE . "` ,
        `" . SP_REQUEST . "` ,
        `" . SP_ACTIVE . "`
      ) VALUES(
        NULL, ?, ?, ?, ?, ?, ?, ?
      )";
    $input = array();
    $input[] = $spList[SP_SITE_NAME];
    $input[] = $spList[SP_SITE_URL];
    $input[] = $spList[SP_FILLING_DAY];
    $input[] = $spList[SP_DESCRIPTION];
    $input[] = $spList[SP_TIME_ZONE];
    $input[] = $spList[SP_REQUEST];
    $input[] = $spList[SP_ACTIVE];
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Получить настройку сервиса из базы данных
   * @param $name string Имя настройки
   * @return false|array Массив со значением настройки
   */
  public function getSetting ($name) {
    $sql = "
      SELECT * FROM `settings` WHERE `" . SETTINGS_NAME . "` LIKE ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($name));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result[0])) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Добавить настройку сервиса в базу данных
   * @param $name string Имя настройки
   * @param $value string Значение настройки
   * @return false|int В случае успеха ID добавленной настройки
   */
  public function addSetting ($name, $value) {
    $sql = "
      INSERT INTO `settings` (
        `" . SETTINGS_NAME . "` ,
        `" . SETTINGS_VALUE . "`
      )
      VALUES (
        ?, ?
      );
    ";
    $stm = $this->db->prepare($sql);
    if ($stm->execute(array($name, $value))) {
      return (int)$this->db->lastInsertId();
    }
    return false;
  }

  /**
   * Очистка таблицы sp
   * @return bool результат выполнения операции
   */
  public function truncateSpTable () {
    $sql = "TRUNCATE `sp`";
    return $this->db->prepare($sql)->execute();
  }

  /**
   * Получить все настройки сервиса
   * @return array|bool Настройки сервиса
   */
  public function getAllSettings () {
    $sql = "
      SELECT * FROM `settings`
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Изменяет настройки сервиса
   * @param $name
   * @param $value
   * @return bool Результат установки новых значений
   */
  public function setSetting ($name, $value) {
    $sql = "UPDATE `settings` SET `" . SETTINGS_VALUE . "` = ? WHERE `" . SETTINGS_NAME . "` = ?";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($value, $name));
    return $result;
  }

  /**
   * Установить дату последнего посещения пользователя
   * @param $uid int ID пользователя
   * @return bool Результат выполнения операции
   */
  public function setLastTime ($uid) {
    $date = date('Y-m-d H:i:s');
    $sql = "UPDATE `users` SET `" . USER_LAST_TIME . "` = ? WHERE  `" . USER_ID . "` = ?";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($date, $uid));
    return $result;
  }

  /**
   * Создание таблицы orders
   * @return bool Результат операции
   */
  public function createTableOrders () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `orders` (
        `order_id` INT(11) NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) NOT NULL,
        `order_type` TINYINT(3) UNSIGNED NOT NULL,
        `order_day` SMALLINT(11) UNSIGNED NOT NULL,
        `order_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `order_run` TIMESTAMP NULL DEFAULT NULL,
        `order_done` TIMESTAMP NULL DEFAULT NULL,
        `order_return` TIMESTAMP NULL DEFAULT NULL,
        `payment_id` INT(11) NOT NULL,
        PRIMARY KEY (`order_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Отметить подарок пользователя как использованный
   * @param $uid int ID пользователя
   * @return bool Результат операции
   */
  public function useGift ($uid) {
    $sql = "UPDATE  `users` SET  `" . USER_GIFT . "` = '0' WHERE  `" . USER_ID . "` = ?;";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($uid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Добавить новый заказ на предоставлении услуги
   * @param array $order Массив с данными для добавления заказа формата:
   *  - [USER_ID] - ID пользователя
   *  - [ORDER_TYPE] - тип заказа
   *  - [ORDER_DAY] - Количество дней заказа услуги
   * @param int $paymentId ID платежа, если услуга оплачена через систему платежей
   * @return false|int ID добавленного заказа
   */
  public function addOrder (array $order, $paymentId = 0) {
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $sql = "
      INSERT INTO `orders` (
        `" . ORDER_ID . "` ,
        `" . USER_ID . "` ,
        `" . ORDER_TYPE . "` ,
        `" . ORDER_DAY . "` ,
        `" . ORDER_ADD . "` ,
        `" . PAYMENT_ID . "`
      ) VALUES (
        NULL, ?, ?, ?, ?, ?
      )
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $order[USER_ID];
    $input[] = $order[ORDER_TYPE];
    $input[] = $order[ORDER_DAY];
    $input[] = $date;
    $input[] = $paymentId;
    if ($stm->execute($input)) {
      return (int)$this->db->lastInsertId();
    }
    return false;
  }

  /**
   * Получить список всех заказов пользователя
   * @param $uid int ID пользователя
   * @return array|bool Массив с заказами пользователя
   */
  public function getUserAllOrders ($uid) {
    $sql = "
      SELECT * FROM  `orders` WHERE  `" . USER_ID . "` = ? ORDER BY `" . ORDER_ID . "`
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($uid));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result;
      }
    }
    return false;
  }

  /**
   * Получить список активных заказов пользователя
   * @param $uid int ID пользователя
   * @return array|bool Массив с заказами пользователя
   */
  public function getUserActiveOrders ($uid) {
    $sql = "
      SELECT * FROM  `orders` WHERE  `" . USER_ID . "` = ? AND `" . ORDER_DONE . "` IS NULL AND  `" . ORDER_RETURN . "` IS NULL ORDER BY `" . ORDER_ID . "`
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($uid));
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return false;
  }

  /**
   * Запустить услугу
   * @param $orderId int ID заказа
   * @param $date int Дата запуска в формате UNIX
   * @return bool Результат операции
   */
  public function runOrder ($orderId, $date) {
    $date = strftime('%Y-%m-%d %H:%M:%S', $date);
    $sql = "UPDATE  `orders` SET  `" . ORDER_RUN . "` = ? WHERE  `" . ORDER_ID . "` = ?;";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($date, $orderId));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Выполнить заказ
   * @param $orderId int ID заказа
   * @param $date int Дата выполнения в формате UNIX
   * @return bool Результат операции
   */
  public function doneOrder ($orderId, $date) {
    $date = strftime('%Y-%m-%d %H:%M:%S', $date);
    $sql = "UPDATE  `orders` SET  `" . ORDER_DONE . "` = ? WHERE  `" . ORDER_ID . "` = ?;";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($date, $orderId));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Отметить заказ как возаращённый
   * @param $oid int ID заказа
   * @return bool Результат операции
   */
  public function returnOrder ($oid) {
    $date = strftime('%Y-%m-%d %H:%M:%S', time());
    $sql = "
      UPDATE  `orders` SET  `" . ORDER_RETURN . "` = ?
        WHERE `" . ORDER_RUN . "` IS NULL
          AND `" . ORDER_DONE . "` IS NULL
          AND `" . ORDER_RETURN . "` IS NULL
          AND `" . ORDER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($date, $oid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Отменить возврат заказа
   * @param $oid int ID заказа
   * @return bool Результат операции
   */
  public function cancelReturnOrder ($oid) {
    $sql = "
      UPDATE  `orders` SET  `" . ORDER_RETURN . "` = NULL
        WHERE `" . ORDER_RUN . "` IS NULL
          AND `" . ORDER_DONE . "` IS NULL
          AND `" . ORDER_RETURN . "` IS NOT NULL
          AND `" . ORDER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($oid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Получить список пользователей с запущенными заказами в указанный момент
   * @param $date string Дата, в момент которой ищутся запущенные заказы
   * @return bool|array Список заказов, запущенных на указанный момент
   */
  public function getAllRunOrdersToDate ($date) {
    $date = strtotime($date);
    $date = strftime('%Y-%m-%d %H:%M:%S', $date);
    $sql = "
    SELECT * FROM  `orders`
      WHERE  `" . ORDER_RUN . "` < ?
        AND  `" . ORDER_RUN . "` IS NOT NULL
        AND  `" . ORDER_RETURN . "` IS NULL
        AND (`" . ORDER_DONE . "` > ? OR  `" . ORDER_DONE . "` IS NULL)
      GROUP BY `" . USER_ID . "`
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($date, $date));
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return $result;
  }

  /**
   * Количество записей в таблице orders
   * @return int Количество записей в таблице
   */
  public function getCountRecordsOrders () {
    $sql = "SELECT COUNT(*) AS `count` FROM `orders`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Получить список всех заказов
   * @return array|bool Массив с заказами
   */
  public function getAllOrders () {
    $sql = "
      SELECT * FROM  `orders` ORDER BY `" . ORDER_ID . "`
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return false;
  }

  /**
   * Заблокировать или разблокировать пользователя
   * @param $uid int ID пользователя
   * @param $blocked bool Разблокировка или блокировка (0 или 1)
   * @return bool Результат операции
   */
  public function blockedUser ($uid, $blocked) {
    $sql = "
      UPDATE  `users` SET  `" . USER_BLOCKED . "` =  ? WHERE  `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($blocked, $uid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Создание таблицы yandex_kassa
   * @return bool Результат операции
   */
  public function createTableYandexKassa () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `yandex_kassa` (
        `invoiceId` INT(11) NOT NULL,
        `customerNumber` INT(11) NOT NULL,
        `paymentDatetime` TIMESTAMP NULL DEFAULT NULL,
        `orderCreatedDatetime` TIMESTAMP NULL DEFAULT NULL,
        `shopArticleId` INT(11) NOT NULL,
        `orderSumAmount` FLOAT NOT NULL,
        `shopSumAmount` FLOAT NOT NULL,
        `paymentType` VARCHAR(2) NOT NULL,
        PRIMARY KEY (`invoiceId`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Добавить платёжную информацию от Яндекс.Кассы
   * @param $data array Данные о платеже формата:
   *  [INVOICE_ID] - Уникальный номер транзакции в сервисе Яндекс.Денег
   *  [CUSTOMER_NUMBER] - Идентификатор плательщика (присланный в платежной форме) на стороне магазина
   *  [PAYMENT_DATETIME] - Момент регистрации оплаты заказа в Яндекс.Деньгах
   *  [ORDER_CREATED_DATETIME] - Момент регистрации заказа в сервисе Яндекс.Денег
   *  [SHOP_ARTICLE_ID] - Идентификатор товара, выдается Яндекс.Деньгами
   *  [ORDER_SUM_AMOUNT] - Стоимость заказа
   *  [SHOP_SUM_AMOUNT] - Сумма к выплате на счет магазина (стоимость заказа минус комиссия Яндекс.Денег)
   *  [PAYMENT_TYPE] - Способ оплаты заказа
   * @return bool
   */
  public function addPayingDataYandexKassa (array $data) {
    $sql = "
      INSERT INTO `yandex_kassa` (
        `" . INVOICE_ID . "` ,
        `" . CUSTOMER_NUMBER . "` ,
        `" . PAYMENT_DATETIME . "` ,
        `" . ORDER_CREATED_DATETIME . "` ,
        `" . SHOP_ARTICLE_ID . "` ,
        `" . ORDER_SUM_AMOUNT . "` ,
        `" . SHOP_SUM_AMOUNT . "` ,
        `" . PAYMENT_TYPE . "`
      ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?
      )
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $data[INVOICE_ID];
    $input[] = $data[CUSTOMER_NUMBER];
    $input[] = $data[PAYMENT_DATETIME];
    $input[] = $data[ORDER_CREATED_DATETIME];
    $input[] = $data[SHOP_ARTICLE_ID];
    $input[] = $data[ORDER_SUM_AMOUNT];
    $input[] = $data[SHOP_SUM_AMOUNT];
    $input[] = $data[PAYMENT_TYPE];
    try {
      $result = $stm->execute($input);
    } catch (Exception $e) {
      trigger_error($e->getMessage());
      $result = false;
    }
    return $result;
  }

  /**
   * Получить данные о платеже через Яндекс.Кассу по его ID
   * @param $paymentId int ID платежа
   * @return false|array Данные о платеже
   */
  public function getPaymentByIdYandexKassa ($paymentId) {
    $sql = "
      SELECT * FROM  `yandex_kassa` WHERE  `" . INVOICE_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($paymentId));
    if ($result) {
      $result = $stm->fetchAll();
      if (!empty($result)) {
        return $result[0];
      }
    }
    return false;
  }

  /**
   * Получить сумму полученную сервисом от платёжной системы
   * @return float Сумма полученная сервисом от платёжной системы
   */
  public function getSumReceivedYandexKassa () {
    $sql = "SELECT SUM(`" . SHOP_SUM_AMOUNT . "`) AS  `sum` FROM  `yandex_kassa`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (float)$result[0]['sum'];
  }

  /**
   * Получить сумму заплаченную пользователями платёжной системе
   * @return float Сумма заплаченная пользователями платёжной системе
   */
  public function getSumPaidYandexKassa () {
    $sql = "SELECT SUM(`" . ORDER_SUM_AMOUNT . "`) AS  `sum` FROM  `yandex_kassa`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (float)$result[0]['sum'];
  }

  /**
   * Получить сумму полученную сервисом от платёжной системы от определённого пользователя
   * @param $uid int ID пользователя
   * @return float Сумма полученная сервисом от платёжной системы от определённого пользователя
   */
  public function getUserSumReceivedYandexKassa ($uid) {
    $sql = "SELECT SUM(`" . SHOP_SUM_AMOUNT . "`) AS  `sum` FROM  `yandex_kassa` WHERE  `" . CUSTOMER_NUMBER . "` = ?";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($uid));
    $result = $stm->fetchAll();
    return (float)$result[0]['sum'];
  }

  /**
   * Получить сумму заплаченную определённым пользователем в платёжную систему
   * @param $uid int ID пользователя
   * @return float Сумма заплаченная определённым пользователем в платёжную систему
   */
  public function getUserSumPaidYandexKassa ($uid) {
    $sql = "SELECT SUM(`" . ORDER_SUM_AMOUNT . "`) AS  `sum` FROM  `yandex_kassa` WHERE  `" . CUSTOMER_NUMBER . "` = ?";
    $stm = $this->db->prepare($sql);
    $stm->execute(array($uid));
    $result = $stm->fetchAll();
    return (float)$result[0]['sum'];
  }

  /**
   * Удалить заказ, если он добавлен администратором и возвращён
   * @param $oid int ID заказа
   * @param $uid int ID пользователя
   * @return bool Результат удаления
   */
  public function deleteOrder ($oid, $uid) {
    $sql = "
      DELETE FROM `orders` WHERE `" . ORDER_ID . "` = ? AND `" . ORDER_TYPE . "` = 2 AND `" . ORDER_RETURN . "` IS NOT NULL AND `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($oid, $uid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Получить количество записей в таблице yandex_kassa
   * @return int Количество записей
   */
  public function getCountRecordsYandexKassa () {
    $sql = "SELECT COUNT(*) AS `count` FROM `yandex_kassa`";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['count'];
  }

  /**
   * Получить список всех проставленных платежей в закупке
   * @param $userId int ID пользователя
   * @param $purchaseId int ID закупки
   * @return array|bool Список всех проставленных платежей в закупке
   */
  public function getAllPayFromPurchase ($userId, $purchaseId) {
    $sql = "
      SELECT * FROM  `pay` WHERE  `" . USER_ID . "` = ? AND  `" . PURCHASE_ID . "` = ?";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($userId, $purchaseId));
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return false;
  }

  /**
   * Смена email
   * @param int $id ID пользователя
   * @return null|array Результат смены email:
   * - false - в случае неудачи
   * - array - обновлённые данные о пользователе, в случае успешной смены email
   */
  public function changeEmail ($id) {
    $sql = "
      UPDATE `users` SET `" . USER_EMAIL . "` =  `" . USER_TMP_EMAIL . "` ,`" . USER_TMP_EMAIL . "` =  '' WHERE `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($id));
    // Получаем данные о пользователе
    if ($result) {
      $user = $this->getUserById($id);
      return $user;
    }
    return false;
  }

  /**
   * Установить временную зону для базы данных
   * @param $timeZone string Временная зона
   * @return bool Результат операции
   */
  public function setTimeZone ($timeZone) {
    $sql = "
      SET `time_zone`= ?
    ";
    $stm = $this->db->prepare($sql);
    try {
      $result = $stm->execute(array($timeZone));
    } catch (Exception $e) {
      return false;
    }
    return $result;
  }

  /**
   * Получить количество новых нераспознанных СМС
   * @return int Количество новых нераспознанных СМС
   */
  public function getCountNewSmsUnknown () {
    $sql = "
      SELECT COUNT( * ) AS `sms`
        FROM  `sms_unknown`
        WHERE `" . SMS_UNKNOWN_NEW . "` = 1
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll();
    return (int)$result[0]['sms'];
  }

  /**
   * Отметить неопределённые SMS как просмотренные
   * @param array $arr Массив с ID просмотренными СМС
   */
  public function readSmsUnknown (array $arr) {
    // Создаём шаблон для подстановки значений
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "
      UPDATE  `sms_unknown` SET  `" . SMS_UNKNOWN_NEW . "` = '0' WHERE `" . SMS_UNKNOWN_ID . "` IN ({$in})
    ";
    $stm = $this->db->prepare($sql);
    $stm->execute($arr);
  }

  /**
   * Удалить все старые просмотренные неопределённые СМС
   * @param $currentTime int Метка текущего времени и даты
   * @return bool|int Количество удалённых СМС
   */
  public function deleteAllOldUnknownSms ($currentTime) {
    // Подготовка даты
    $date = new DateTime();
    $dateDelete = $date->setTimestamp($currentTime)->modify('-30 day')->format('Y-m-d H:i:s');
    // Запрос
    $sql = "
      DELETE FROM `sms_unknown`
      WHERE `" . SMS_UNKNOWN_ADD . "` < ?
        AND `" . SMS_UNKNOWN_NEW . "` = '0'
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($dateDelete));
    if ($result) {
      $result = $stm->rowCount();
    }
    return $result;
  }

  /**
   * Создание таблицы `templates`
   * @return bool Результат операции
   */
  public function createTableTemplates () {
    $sql = "
      CREATE TABLE IF NOT EXISTS `templates` (
        `tpl_id` INT(11) NOT NULL AUTO_INCREMENT,
        `tpl_type` INT(11) NOT NULL,
        `tpl_subtype` INT(11) NOT NULL,
        `tpl_template` VARCHAR(500) NOT NULL,
        `tpl_description` VARCHAR(500) NOT NULL,
        `tpl_active` TINYINT(1) NOT NULL,
        `tpl_count_used` INT(10) UNSIGNED NOT NULL DEFAULT '0',
        `tpl_last_used` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`tpl_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    return $result;
  }

  /**
   * Получить шаблоны для СМС определённого типа
   * @param $type int Тип шаблонов для СМС
   * @param bool $all Выводить все или только активные шаблоны (по умолчанию, только активные)
   * @return array|bool Массив с шаблонами
   */
  public function getTemplatesByType ($type, $all = false) {
    $sql = "SELECT * FROM  `templates` WHERE  `" . TPL_TYPE . "` = ?";
    if (!$all) {
      $sql .= " AND `" . TPL_ACTIVE . "` = 1";
    }
    $sql .= " ORDER BY `" . TPL_LAST_USED . "` DESC ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($type));
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return false;
  }

  /**
   * Обновление статистики использования шаблонов
   * @param array $templates Массив с данными о шаблонах
   */
  public function updateTemplateStatistics (array $templates) {
    if (!empty($templates)) {
      foreach ($templates as $tpl) {
        $sql = "
          UPDATE `templates` SET  `" . TPL_COUNT_USED . "` = ?, `" . TPL_LAST_USED . "` = ? WHERE  `" . TPL_ID . "` = ?
        ";
        $stm = $this->db->prepare($sql);
        $stm->execute(array($tpl[TPL_COUNT_USED], $tpl[TPL_LAST_USED], $tpl[TPL_ID]));
      }
    }
  }

  /**
   * Изменить шаблон
   * @param $tid int ID шаблона
   * @param $tpl string Текст шаблона
   * @param $active int Включен или выключен шаблон
   * @param $description string Описание шаблона
   * @return bool Результат операции
   */
  public function editTpl ($tid, $tpl, $active, $description) {
    $sql = "
      UPDATE `templates` SET  `" . TPL_TEMPLATE . "` = ?, `" . TPL_ACTIVE . "` = ?, `" . TPL_DESCRIPTION . "` = ? WHERE  `" . TPL_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($tpl, $active, $description, $tid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Удалить шаблон
   * @param $tid int ID шаблона
   * @return bool Результат операции
   */
  public function deleteTpl ($tid) {
    $sql = "
      DELETE FROM `templates` WHERE `" . TPL_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    if ($stm->execute(array($tid))) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return false;
  }

  /**
   * Добавить шаблон
   * @param $type int Тип шаблона
   * @param $subtype int Подтип шаблона
   * @param $active int Включен или нет шблон
   * @param $template string Шаблон
   * @param $description string Описание шаблона
   * @return bool Результат операции
   */
  public function addTpl ($type, $subtype, $active, $template, $description) {
    $sql = "
      INSERT INTO `templates` (
        `" . TPL_ID . "` ,
        `" . TPL_TYPE . "` ,
        `" . TPL_SUBTYPE . "` ,
        `" . TPL_TEMPLATE . "` ,
        `" . TPL_DESCRIPTION . "` ,
        `" . TPL_ACTIVE . "` ,
        `" . TPL_COUNT_USED . "` ,
        `" . TPL_LAST_USED . "`
      )
      VALUES (
        NULL ,  ?,  ?,  ?,  ?,  ?,  '0', NULL
      );
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $type;
    $input[] = $subtype;
    $input[] = $template;
    $input[] = $description;
    $input[] = $active;
    try {
      $result = $stm->execute($input);
    } catch (Exception $e) {
      trigger_error($e->getMessage());
      $result = false;
    }
    return $result;

  }

  /**
   * Сбросить статистику шаблонов СМС
   * @return bool Результат операции
   */
  public function statResetAllTpl () {
    $sql = "
      UPDATE  `templates` SET  `" . TPL_COUNT_USED . "` =  '0', `" . TPL_LAST_USED . "` = NULL
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Получить все шаблоны СМС
   * @return array|false Массив со всеми шаблонами СМС
   */
  public function getAllTemplates () {
    $sql = "SELECT * FROM  `templates`";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute();
    if ($result) {
      $result = $stm->fetchAll();
      return $result;
    }
    return false;
  }

  /**
   * Удалить все шаблоны
   * @return bool Результат операции
   */
  public function delAllTemplates () {
    $sql = "TRUNCATE `templates`";
    return $this->db->prepare($sql)->execute();
  }

  /**
   * Импорт шаблонов СМС
   * @param array $templates Список испортируемых шаблонов в формате:
   *  [x] - номер шаблона
   *    [...] - те же поля что и в БД
   * @return bool Результат операции
   */
  public function importTpl (array $templates) {
    $sql = "
      INSERT INTO `templates` (
        `" . TPL_ID . "` ,
        `" . TPL_TYPE . "` ,
        `" . TPL_SUBTYPE . "` ,
        `" . TPL_TEMPLATE . "` ,
        `" . TPL_DESCRIPTION . "` ,
        `" . TPL_ACTIVE . "` ,
        `" . TPL_COUNT_USED . "` ,
        `" . TPL_LAST_USED . "`
      )
      VALUES";
    $input = array();
    foreach ($templates as $tpl) {
      $sql .= " (NULL, ?, ?, ?, ?, ?, ?, ?),";
      $input[] = $tpl[TPL_TYPE];
      $input[] = $tpl[TPL_SUBTYPE];
      $input[] = $tpl[TPL_TEMPLATE];
      $input[] = $tpl[TPL_DESCRIPTION];
      $input[] = $tpl[TPL_ACTIVE];
      $input[] = $tpl[TPL_COUNT_USED];
      $input[] = $tpl[TPL_LAST_USED];
    }
    $sql = rtrim($sql, ",");
    $stm = $this->db->prepare($sql);
    $result = $stm->execute($input);
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Удалить все нераспознанные СМС
   * @return bool Результат операции
   */
  public function delAllSmsUnknown () {
    $sql = "TRUNCATE `sms_unknown`";
    return $this->db->prepare($sql)->execute();
  }

  /**
   * Изменить способ запроса к сайту СП
   * @param $uid int ID пользователя
   * @param $request int Тип запроса
   * @return bool Результат операции
   */
  public function setUserRequest ($uid, $request) {
    $sql = "
      UPDATE  `users` SET  `" . USER_REQUEST . "` =  ? WHERE `" . USER_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($request, $uid));
    if ($result) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return $result;
  }

  /**
   * Проверить наличие сайта СП по его ID
   * @param $spId int ID сайта СП
   * @return bool Результат операции
   */
  public function issetSpId ($spId) {
    $sql = "
      SELECT * FROM  `sp` WHERE  `" . SP_ID . "` = ? AND `" . SP_ACTIVE . "` = 1
    ";
    $stm = $this->db->prepare($sql);
    $result = $stm->execute(array($spId));
    if ($result) {
      $result = $stm->fetchAll();
      return !empty($result);
    }
    return $result;
  }

  /**
   * Изменить информацию о сайте СП
   * @param array $spArr Массив с данными для изменений
   * @return bool Результат операции
   */
  public function editSp (array $spArr) {
    $sql = "
      UPDATE `sp` SET
        `" . SP_SITE_NAME . "` = ? ,
        `" . SP_SITE_URL . "` = ? ,
        `" . SP_FILLING_DAY . "` = ? ,
        `" . SP_DESCRIPTION . "` = ? ,
        `" . SP_TIME_ZONE . "` = ? ,
        `" . SP_REQUEST . "` = ?,
        `" . SP_ACTIVE . "` = ?
      WHERE `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    $input = array();
    $input[] = $spArr[SP_SITE_NAME];
    $input[] = $spArr[SP_SITE_URL];
    $input[] = $spArr[SP_FILLING_DAY];
    $input[] = $spArr[SP_DESCRIPTION];
    $input[] = $spArr[SP_TIME_ZONE];
    $input[] = $spArr[SP_REQUEST];
    $input[] = $spArr[SP_ACTIVE];
    $input[] = $spArr[SP_ID];
    if ($stm->execute($input)) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return false;
  }

  /**
   * Удалить сайт СП
   * @param $id int ID сайта СП
   * @return bool Результат операции
   */
  public function deleteSp ($id) {
    $sql = "
      DELETE FROM `sp` WHERE `" . SP_ID . "` = ?
    ";
    $stm = $this->db->prepare($sql);
    if ($stm->execute(array($id))) {
      return $stm->rowCount() > 0 ? true : false;
    }
    return false;
  }

}
