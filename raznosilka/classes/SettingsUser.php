<?php

/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Settings.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class SettingsUser отвечает за настройки пользователя из личного кабинета
 */
class SettingsUser {
  /**
   * @var int ID пользователя который изменяет настройки
   */
  protected $id;
  /**
   * @var array Содержит настройки пользователя которые необходимо изменить
   */
  protected $data = array();
  /**
   * @var DataBase Доступ к методам работы с БД
   */
  protected $db;

  /**
   * Конструктор определяет свойство $id и $db
   * @param int $id ID пользователя у которого меняются настройки
   */
  function __construct ($id) {
    $this->id = $id;
    $this->db = new DataBase(Registry_Request::instance()->get('db'));
  }

  /**
   * Устанавливает настройки для пользователя из свойства $data
   * @return bool Результат изменения настроек
   */
  function setSettings () {
    $result = false;
    if (!empty($this->data)) {
      $result = $this->db->setUserSettings($this->id, $this->data);
      if ($result) {
        $this->data = array();
      } else {
        trigger_error('Ошибка. Не удалось изменить настройки пользователя.');
      }
    }
    return $result;
  }

  /**
   * Смена пароля пользователя
   * @param string $new_pass Новый пароль
   * @return bool Результат смены пароля
   */
  public function changePassword ($new_pass) {
    $new_pass = trim($new_pass);
    $this->setSetting(USER_PASSWORD, $new_pass);
    $result = $this->setSettings();
    if ($result) {
      // Обновляем данные в реестре сессий
      Registry_Session::instance()->set(USER_PASSWORD, $new_pass, 1);
      // Лог
      $logs = new Logs();
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      $logs->actionLog($user->getUserInfo(), 'Пароль к сервису успешно изменён');
    } else {
      trigger_error('Ошибка. Не удалось именить пароль к сервису.');
    }
    return $result;
  }

  /**
   * Смена или ввод пароля для сайта СП
   * @param $spLogin string $login Логин к сайту СП
   * @param $spPassword string $password Пароль к сайту СП
   * @return bool Результат операции
   */
  public function setPasswordSP ($spLogin, $spPassword) {
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $spId = $user->getSpId();
    $site = Site::getSite($spId);
    $organizerId = -1;
    // Если ID организатора ещё не сохранён
    if (!$user->isBinding()) {
      // Получаем ID организатора
      $organizerId = $site->getOrganizerId();
      // Сохраняем данные для доступа к сайту СП в БД
      $this->setSetting(USER_ORG_ID, $organizerId);
    }
    $this->setSetting(USER_SP_LOGIN, $spLogin);
    $this->setSetting(USER_SP_PASSWORD, $spPassword);
    $result = $this->setSettings();
    // Логгирование
    $logs = new Logs();
    $siteName = $site->getNameSite();
    if ($result) {
      if ($user->isBinding()) {
        $logs->actionLog($user->getUserInfo(), "Логин и пароль успешно сохранены для сайта СП - {$siteName}.");
      } else {
        $logs->actionLog($user->getUserInfo(), "Логин и пароль успешно сохранены для сайта СП - {$siteName}. ID организатора #{$organizerId} сохранён.");
      }
    } else {
      trigger_error('Не удалось сохранить логин и пароль для сайта СП по неизвестной причине');
    }
    return $result;
  }

  /**
   * Удалить пароль от сайта СП
   * @return bool Результат удаления пароля
   */
  public function delPasswordSp () {
    $this->setSetting(USER_SP_LOGIN, '');
    $this->setSetting(USER_SP_PASSWORD, '');
    $result = $this->setSettings();
    if ($result) {
      //сброс сохранённых кук для доступа к сайту СП
      $site = Site::getSite();
      $site->delCookieFromRegistry();
      // сброс кэша с закупкой
      Cache::updateCache($this->id);
      // сброс выбранной закупки
      Registry_Session::instance()->del('purchase');
      // Лог
      $logs = new Logs();
      /** @var User $user */
      $user = Registry_Request::instance()->get('user');
      $logs->actionLog($user->getUserInfo(), 'Пароль от сайта СП успешно удалён');
    } else {
      trigger_error('Не удалось удалить пароль от сайта СП');
    }
    return $result;
  }

  /**
   * Получение настроек пользователя
   * @return array Массив со всеми настройками пользователя
   */
  public function getSettings () {
    $settings = $this->db->getUserById($this->id);
    // var_dump($settings);
    return $settings;
  }

  /**
   * Добавляет настройку, в свойство $data, которая должна быть изменена. Имя настройки
   * которую необходимо изменть должно совпадать с соответствующим именем поля таблицы user.
   * @param string $name Имя настройки
   * @param mixed $value Значение настройки
   */
  public function setSetting ($name, $value) {
    $this->data[$name] = trim($value);
  }

  /**
   * Получение данных для вывода сведений о пользователе
   * @return array Возвращает массив для вывода, формата:
   *  - [USER_ID] - ID пользователя
   *  - [USER_LOGIN] - логин пользователя
   *  - [USER_EMAIL] - емайл пользователя
   *  - ['status_account'] - состояние аккаунте:
   *    - 0 - Не активирован
   *    - 1 - Не получен OrgID
   *    - 2 - Не введён логин и пароль к сайту СП
   *    - 3 - Готов к работе
   *  - ['status'] - Оплачен аккаунт или нет
   *  - ['date_done'] - До какого числа оплачен аккаунт
   * @throws Exception
   */
  public function getViewUserInfo () {
    // Инициализация
    $result = array();
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $userInfo = $user->getUserInfo();
    $result[USER_ID] = $userInfo[USER_ID];
    $result[USER_LOGIN] = $userInfo[USER_LOGIN];
    $result[USER_EMAIL] = $userInfo[USER_EMAIL];
    $status = 0;
    switch ($user->getUserRequest()) {
      // Запросы к сайту СП при помощи расширения браузера
      case REQUEST_EXTENSIONS: {
        if ($user->isActivate()) {
          $status = 3;
        }
        break;
      }
      // Запросы к сайту СП при помощи curl по умолчанию
      default : {
        if ($user->isActivate() and !$user->isBinding()) {
          $status = 1;
        }
        if ($user->isActivate() and $user->isBinding() and !$user->isHaveLogin()) {
          $status = 2;
        }
        if ($user->isActivate() and $user->isBinding() and $user->isHaveLogin()) {
          $status = 3;
        }
        break;
      }
    }
    $result['status_account'] = $status;
    $result['status'] = $userInfo['status'];
    $result['date_done'] = strftime('%H:%M %d.%m.%Y', $userInfo['date_done']);
    return $result;
  }

  /**
   * Изменить настройки рассылки
   * @param $post array Данные запроса post
   * @return array Данные для вывода, формата:
   *  ['result'] - результат сохранения настроек
   *  ['user'] - информация о пользователе
   */
  public function notifySettings (array $post) {
    $result = array();
    $result['result'] = null;
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $result['user'] = $user->getUserInfo();
    // Подготовка значений для вывода
    $result['user'][USER_FILLING_DAY] = ($result['user'][USER_FILLING_DAY] > -1) ? $result['user'][USER_FILLING_DAY] : '';
    // Если нажали кнопку сохранить и пользователь имеет OrgID
    if (isset($post['submit'])) {
      // Напоминания
      (isset($post['reminding'])) ? $this->setSetting(USER_REMINDING, 1) : $this->setSetting(USER_REMINDING, 0);
      // Кол-во дней на проставление оплат
      if (isset($post['filling_day'])) {
        $validator = new Validator();
        $fillingDay = $validator->normalizeFillingDay($post['filling_day']);
        $this->setSetting(USER_FILLING_DAY, $fillingDay);
      }
      // Сохранение настроек
      $result['result'] = $this->setSettings();
    }
    return $result;
  }

  /**
   * Изменить пользовательские настройки сервиса
   * @param $post array Данные запроса post
   * @return array Данные для вывода, формата:
   *  ['result'] - результат сохранения настроек
   *  ['disabled'] - доступны или нет настройки
   *  ['user'] - информация о пользователе
   */
  public function serviceSettings (array $post) {
    $result = array();
    $result['result'] = null;
    /** @var User $user */
    $user = Registry_Request::instance()->get('user');
    $result['user'] = $user->getUserInfo();
    // Получение списка временных зон
    $timeZoneHelper = new TimeZoneHelper();
    $result['time_zones'] =  $timeZoneHelper->getTimeZoneListForView();
    // Если нажали кнопку сохранить и пользователь имеет OrgID
    if (isset($post['submit'])) {
      // Временна зона
      if (isset($post['time_zone'])) {
        if ($timeZoneHelper->validateTimeZone($post['time_zone'])) {
          $this->setSetting(USER_TIME_ZONE, $post['time_zone']);
        }
      }
      // Сохранение настроек
      $result['result'] = $this->setSettings();
    }
    return $result;
  }
}