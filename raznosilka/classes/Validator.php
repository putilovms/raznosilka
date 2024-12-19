<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Validator.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Validator содержит методы для валидации форм
 */
class Validator {
  /**
   * Минимальная длина логина
   */
  const minLoginLen = 3;
  /**
   * Максимальная длина логина
   */
  const maxLoginLen = 30;
  /**
   * Максимальная длина email
   */
  const maxEmailLen = 50;
  /**
   * Минимальная длина пароля
   */
  const minPassLen = 6;
  /**
   * Максимальная длина пароля
   */
  const maxPassLen = 100;
  /**
   * Максимальное количество дней на разнесение оплат
   */
  const maxFillingDay = 10;
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  private $db;

  /**
   * Конструктор валидатора
   */
  function __construct () {
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
  }

  /**
   * Валидация формы регистрации
   * @param string $login Логин нового пользователя
   * @param string $email Емайл нового пользователя
   * @param string $pass_1 Пароль нового пользователя
   * @param string $pass_2 Повтор пароля нового пользователя
   * @param string $spId Выбранный сайт СП
   * @return array Возвращает ассоциативный массив содержащий общий результат валидации,
   * а так же результаты валидации и ошибки валидации для каждого инпута.
   */
  public function validateRegisterForm ($login, $email, $pass_1, $pass_2, $spId) {
    $reg_login = $this->validateLogin($login);
    $reg_email = $this->validateEmail($email);
    $reg_pass = $this->validatePass($pass_1, $pass_2);
    $reg_spId = $this->validateSpId($spId);
    $result['validate'] = $reg_login['validate'] && $reg_email['validate'] && $reg_pass['validate'] && $reg_spId['validate'];
    $result['data']['reg_login'] = $reg_login;
    $result['data']['reg_email'] = $reg_email;
    $result['data']['reg_pass'] = $reg_pass;
    $result['data']['reg_sp_id'] = $reg_spId;
    return $result;
  }

  /**
   * Метод для валидации логина пользователя.
   * Настройки метода содержатся в константах класса minLoginLen и maxLoginLen.
   * @param string $login Логин пользователя
   * @return array Результат валидации в формате:
   * - validate - результат валидации
   * - message - массив с ошибками
   * - class - Класс для отображения
   */
  public function validateLogin ($login) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['class'] = null;
    if (empty($login)) {
      return $result;
    }
    $result['validate'] = true;
    // Проверка имени
    $login = trim($login);
    $login = mb_strtolower($login);
    $pattern = "#^[A-Za-zА-Яа-я0-9 -]+$#u";
    if (!preg_match($pattern, $login)) {
      $result['validate'] = false;
      $result['message'][] = 'В качестве логина используются недопустимые символы.';
      $result['class'] = 'error';
    }
    if (mb_strlen($login) < self::minLoginLen) {
      $result['validate'] = false;
      $result['message'][] = 'Логин слишком короткий.';
      $result['class'] = 'error';
    }
    if (mb_strlen($login) > self::maxLoginLen) {
      $result['validate'] = false;
      $result['message'][] = 'Логин слишком длинный.';
      $result['class'] = 'error';
    }
    $user = $this->getUserByLogin($login);
    if (count($user) > 0) {
      $result['validate'] = false;
      $result['message'][] = 'Пользователь с таким логином уже существует.';
      $result['class'] = 'error';
    }
    return $result;
  }

  /**
   * Метод обёртка для получения данных о пользователе, необходим для тестирования
   * @param string $login
   * @return array|null
   */
  function getUserByLogin ($login) {
    return $this->db->getUserByLogin($login);
  }

  /**
   * Проверка email
   * @param string $email Email
   * @param bool $fagot Валидация для восстановаления пароля. Если $fagot = true, то
   * валидация будет пройдена если пользователь с таким email существует, если
   * $fagot = false, то валидация будет пройдена если прользователя с таким email
   * не существует. Значение по умолчанию false.
   * @return array Результат валидации в формате:
   * - validate - результат валидации
   * - message - массив с ошибками
   * - class - Класс для отображения
   */
  public function validateEmail ($email, $fagot = false) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['class'] = null;
    if (empty($email)) {
      return $result;
    }
    $result['validate'] = true;
    // Проверка e-mail
    $email = trim($email);
    $pattern = '#.+@.+\..+#u';
    if (!preg_match($pattern, $email)) {
      $result['validate'] = false;
      $result['message'][] = 'Указан некорректный e-mail адрес.';
      $result['class'] = 'error';
    }
    if (mb_strlen($email) > self::maxEmailLen) {
      $result['validate'] = false;
      $result['message'][] = 'E-mail слишком длинный.';
      $result['class'] = 'error';
    }
    $user = $this->getUserByEmail($email);
    if ($fagot) {
      // Если форма восстановаления пароля
      if (count($user) == 0) {
        $result['validate'] = false;
        $result['message'][] = 'Пользователя с таким e-mail не существует.';
        $result['class'] = 'error';
        $logs = new Logs();
        $logs->actionLog(array(), 'Попытка восставновить пароль для несуществующего e-mail');
      }
    } else {
      // Если форма регистрации нового пользователя
      if (count($user) > 0) {
        $result['validate'] = false;
        $result['message'][] = 'Данный e-mail уже используется другим пользователем.';
        $result['class'] = 'error';
      }
    }
    return $result;
  }

  /**
   * Метод обёртка для получения данных о пользователе, необходим для тестирования
   * @param string $email
   * @return array|null
   */
  function getUserByEmail ($email) {
    return $this->db->getUserByEmail($email);
  }

  /**
   * Проверка соответствия двух паролей
   * @param string $pass_1 Пароль основной
   * @param string $pass_2 Пароль повтор
   * @return array Результат валидации в формате:
   * - validate - результат валидации
   * - message - массив с ошибками
   * - class - Класс для отображения
   */
  public function validatePass ($pass_1, $pass_2) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['class'] = null;
    if (empty($pass_1) or empty($pass_2)) {
      return $result;
    }
    $result['validate'] = true;
    // Проверка пароля
    $pass_1 = trim($pass_1);
    $pass_2 = trim($pass_2);
    if ($pass_1 != $pass_2) {
      $result['validate'] = false;
      $result['message'][] = 'Пароли не совпадают.';
      $result['class'] = 'error';
    }
    if (mb_strlen($pass_1) < self::minPassLen) {
      $result['validate'] = false;
      $result['message'][] = 'Пароль слишком короткий.';
      $result['class'] = 'error';
    }
    if (mb_strlen($pass_1) > self::maxPassLen) {
      $result['validate'] = false;
      $result['message'][] = 'Пароль слишком длинный.';
      $result['class'] = 'error';
    }
    return $result;
  }

  /**
   * Валидация авторизации пользователя
   * @param string $login Логин пользователя
   * @param string $pass Пароль пользователя
   * @return array Ассоциативный массив:
   * - validate - результат попытки авторизации пользователя
   * - message - возникшие ошибки
   * - user - массив с данными о пользователе
   * - class - класс для инпута
   */
  public function validateUserRegistration ($login, $pass) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['user'] = null;
    $result['class'] = null;
    if (empty($login) or empty($pass)) {
      return $result;
    }
    // Проверка логина и пароля пользователя
    $login = trim($login);
    $pass = trim($pass);
    // Проверяем имеется ли введёный логин и пароль в БД
    $user = $this->checkUserRegistration($login, $pass);
    if (!is_null($user) AND mb_strtolower($user[USER_LOGIN]) == mb_strtolower($login) and $user[USER_PASSWORD] == $pass) {
      $result['validate'] = true;
      $result['user'] = $user;
      return $result;
    }
    // Логгируем неудачный вход
    $logs = new Logs();
    $logs->actionLog(array(USER_LOGIN => $login), "Пользователю не удалось войти. Не верные учётные данные.");
    // Сообщение об ошибке
    $result['class'] = 'error';
    $result['message'][] = 'Введён неверный логин или пароль.';
    return $result;
  }

  /**
   * Метод обёртка для получения данных о пользователе, необходим для тестирования
   * @param string $login
   * @param string $pass
   * @return array|null
   */
  function checkUserRegistration ($login, $pass) {
    return $this->db->checkUserRegistration($login, $pass);
  }

  /**
   * Валидация попытки смены пароля
   * @param string $old_pass Старый пароль
   * @param string $new_pass_1 Новый пароль
   * @param string $new_pass_2 Повтор нового пароля
   * @return array Ассоциативный массив:
   * - validate - Результат валидации
   * - message - Возникшие ошибки
   * - class - Класс для отображения
   */
  public function validateChangePassword ($old_pass, $new_pass_1, $new_pass_2) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['class'] = null;
    if (empty($old_pass)) {
      return $result;
    }
    // Проверка
    $result['validate'] = true;
    // Проверка старого и нового пароля пользователя
    $old_pass = trim($old_pass);
    $new_pass_1 = trim($new_pass_1);
    $new_pass_2 = trim($new_pass_2);
    // Получаем данные о текущем пользователе
    $user = $this->getUserById();
    if ($user[USER_PASSWORD] != $old_pass) {
      $result['validate'] = false;
      $result['message'][] = 'Введён неверный старый пароль.';
      $result['class'] = 'error';
    }
    if (mb_strlen($new_pass_1) < self::minPassLen) {
      $result['validate'] = false;
      $result['message'][] = 'Новый пароль слишком короткий.';
      $result['class'] = 'error';
    }
    if (mb_strlen($new_pass_1) > self::maxPassLen) {
      $result['validate'] = false;
      $result['message'][] = 'Новый пароль слишком длинный.';
      $result['class'] = 'error';
    }
    if ($new_pass_1 != $new_pass_2) {
      $result['validate'] = false;
      $result['message'][] = 'Новые пароли не совпадают.';
      $result['class'] = 'error';
    }
    // Если валидация прошла успешно
    if ($result['validate']) {
      $result['message'][] = 'Пароль успешно изменён.';
      $result['class'] = "success";
    }
    return $result;
  }

  /**
   * Метод обёртка для получения данных о пользователе, необходим для тестирования
   * @return array|null
   */
  function getUserById () {
    return $this->db->getUserById(Registry_Session::instance()->get('user_id'));
  }

  /**
   * Валидация учётных данных для сайта СП
   * @param string $login Логин для сайта СП
   * @param string $pass Пароль для сайта СП
   * @return array Ассоциативный массив:
   * - validate - Результат валидации
   * - message - Возникшие ошибки
   * - class - Класс для отображения
   */
  public function validatePasswordSP ($login, $pass) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['class'] = null;
    $logs = new Logs();
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $spId = $user->getSpId();
    // Если всё не зпаолнено, то пользователь не вносил изменений в форму
    if (empty($login) and empty($pass)) {
      return $result;
    }
    // Проверка
    $result['validate'] = true;
    if (empty($login)) {
      $result['validate'] = false;
      $result['message'][] = 'Не введён логин для доступа к сайту СП.';
      $result['class'] = 'error';
    }
    if (empty($pass)) {
      $result['validate'] = false;
      $result['message'][] = 'Не введён пароль для доступа к сайту СП.';
      $result['class'] = 'error';
    }
    // Проверка возможности привязки аккаунта
    if ($result['validate']) {
      $site = $this->getSiteById($spId);
      $siteName = $site->getNameSite();
      if (!$site->checkAccessByLogin($login, $pass)) {
        $result['validate'] = false;
        $result['message'][] = 'Не удалось получить доступ к выбранному сайту СП.';
        $result['class'] = 'error';
        // Лог
        $logs->actionLog($user->getUserInfo(), "Пароль к сайту СП не сохранён. Не удалось получить доступ к сайту СП - {$siteName}.");
      }
      // Провека на отсутствие ID организатора в базе данных
      if ($result['validate']) {
        $orgId = $site->getOrganizerId();
        $userOrgId = $user->getOrgId();
        if($user->isBinding()) {
          if ($userOrgId != $orgId) {
            $result['validate'] = false;
            $result['message'][] = "Ваш аккаунт уже привязан к организатору с ID #{$userOrgId}, а указанные вами данные принадлежат другому организатору.";
            $result['class'] = 'error';
            // Сброс полученных кук другого организатора
            $site->delCookieFromRegistry();
            // Лог
            $logs->actionLog($user->getUserInfo(), "Пароль к сайту СП не сохранён. Попытка организатора с ID #{$userOrgId} ввести пароль от организатора с  ID #{$orgId}. Сайт СП - {$siteName}.");
          }
        } else {
          if (!$this->checkOrganizer($spId, $orgId)) {
            $result['validate'] = false;
            $result['message'][] = "Другой аккаунт уже саязан с организатором, учётные данные которого вы вводите. Введите данные другого организатора или войдите в «Разносилку» с аккаунта, к которому уже привязан данный организатор.";
            $result['class'] = 'error';
            // Сброс полученных кук другого организатора
            $site->delCookieFromRegistry();
            // Лог
            $logs->actionLog($user->getUserInfo(), "Пароль к сайту СП не сохранён. Другой аккаунт уже саязан с данным организатором. ID организатора: #{$orgId}. Сайт СП - {$siteName}.");
          }
        }
      }
    }
    return $result;
  }

  /**
   * Метод обёртка для получения объекта для доступа к сайту СП, необходим для тестирования
   * @param $spId int ID сайта СП
   * @return false|\Site
   */
  function getSiteById ($spId) {
    return Site::getSite($spId);
  }

  /**
   * Проверить, добавлен ли уже такой организатор в Разносилку
   * @param $spId int ID выбранного сайта СП
   * @param $orgId int ID организатора
   * @return bool Истина если такой организатор ещё не добавлен в Разносилку
   */
  function checkOrganizer ($spId, $orgId) {
    $result = false;
    $org = $this->db->getUserFromSpAndOrgId($spId, $orgId);
    if ($org === false) {
      $result = true;
    }
    return $result;
  }

  /**
   * Нормализовать количество дней на проставление введённые пользователем
   * @param $fillingDay string Количество дней на проставление введённые пользователем
   * @return int Нормализованное количество дней на проставление
   */
  public function normalizeFillingDay ($fillingDay) {
    $fillingDay = (int)$fillingDay;
    $fillingDay = ($fillingDay < 0) ? 0 : $fillingDay;
    $fillingDay = ($fillingDay > self::maxFillingDay) ? self::maxFillingDay : $fillingDay;
    return $fillingDay;
  }

  /**
   * Проверить выбранный сайт СП
   * @param $spId
   * @return mixed
   */
  private function validateSpId ($spId) {
    // Инициализация
    $result['validate'] = false;
    $result['message'] = null;
    $result['class'] = null;
    if (is_null($spId)) {
      return $result;
    }
    $result['validate'] = true;
    // Проверка ID сайта СП
    if (empty($spId)) {
      $result['validate'] = false;
      $result['message'][] = 'Сайт СП не выбран.';
      $result['class'] = 'error';
    }
    $issetSpId = $this->db->issetSpId($spId);
    if (!$issetSpId) {
      $result['validate'] = false;
      $result['message'][] = 'Невозможно выбрать данный сайт СП.';
      $result['class'] = 'error';
    }
    return $result;
  }

}