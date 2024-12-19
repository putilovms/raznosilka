<?php
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\Controller_Admin.php>
 * @copyright © М. С. Путилов, 2015
 */

/**
 * Class Controller_Admin Контроллер отвечающий за вывод меню управления сайтом
 */
class Controller_Admin extends Controller {

  /**
   * Вывод основной страницы администрирования сайта, загружается только для пользователя с id=1,
   * путь /admin
   */
  function index () {
    // Если пользователь является администратором - запуск админки
    $this->access(ADMIN);
    // загрузка шаблона
    $this->template->setTitle('Управление сервисом');
    $this->template->show('admin');
  }

  /**
   * Настройки сервиса
   */
  function settings () {
    $this->access(ADMIN);
    $settings = new SettingsAdmin(Registry_Session::instance()->get('user_id'));
    if (isset($_POST['submit'])) {
      // Смена режима работы сайта
      if (isset($_POST['mode'])) {
        $settings->setSetting('mode', $_POST['mode']);
      }
      // Смена системного емайла
      if (isset($_POST['system_email'])) {
        $settings->setSetting('system_email', $_POST['system_email']);
      }
      // Настройка - нужно ли активировать аккаунты
      isset($_POST['activate_account']) ? $settings->setSetting('activate_account', 1) : $settings->setSetting('activate_account', 0);
      // Настройка - загружать ли данные из кэша при отладке
      isset($_POST['load_from_cache']) ? $settings->setSetting('load_from_cache', 1) : $settings->setSetting('load_from_cache', 0);
      // Настройка - разрешена ли регистрация на сайте
      isset($_POST['register_account']) ? $settings->setSetting('register_account', 1) : $settings->setSetting('register_account', 0);
      // Сохранение настроек
      if ($settings->setSettings()) { // Todo придумать как выводить оповещение при ошибке
        $this->notify->sendNotify('Настройки успешно изменены.', SUCCESS_NOTIFY);
        $this->postReset();
      }
    }
    // Получение настроек
    $user = $settings->getSettings();
    $this->template->set('settings', $user);
    // загрузка шаблона
    $this->template->setTitle('Настройки сервиса');
    $this->template->show('settings');
  }

  /**
   * Отчёт о состоянии сервиса
   */
  function info () {
    $this->access(ADMIN);
    $admin = new Admin();
    $info = $admin->getServiceInfo();
    $this->template->set('info', $info);
    $this->template->setTitle('Отчёт о состоянии сервиса');
    $this->template->show('info');
  }

  /**
   * Сообщение о том, что на сайте ведутся технические работы
   */
  function maintenance () {
    header('HTTP/1.1 503 Service Unavailable'); // todo определять протокол динамически
    $this->template->setTitle('Сайт на обслуживании');
    $this->template->show('maintenance', 'empty');
  }

  /**
   * Для отмены автоматической загрузки сообщения о технических работах
   * @param string $mode Для соблюдения Strict Standards
   */
  function serviceMode ($mode) {
  }
  
  /**
   * Детектор нераспознанных SMS
   */
  function detector () {
    // Если пользователь является администратором - запустить детектор
    $this->access(ADMIN);
    $detector = new Detector();
    if (isset($_POST['action'])) {
      switch ($_POST['action']) {
        // Удаляем СМС
        case 'delete':
          $result = $detector->deleteSelectedSmsUnknown($_POST);
          // Выводим уведомление
          if ($result === false) {
            $this->notify->sendNotify('Не выбрано ни одной SMS для удаления.', ERROR_NOTIFY);
          } else {
            if ($result == 0) {
              $this->notify->sendNotify('Не удалось удалить выбранные SMS.', ERROR_NOTIFY);
            } else {
              $this->notify->sendNotify("Удалено неопределённых SMS: <b>{$result} шт</b> .", SUCCESS_NOTIFY);
            }
          }
          break;
        // Распознаём выбранные СМС
        case 'detect':
          $result = $detector->detectSelectedSmsUnknown($_POST);
          if ($result === false) {
            $this->notify->sendNotify('Во время распознавания SMS возникла ошибка.', ERROR_NOTIFY);
          } else {
            // Сохраняем данные между сбросом POST
            $this->forwarder->save('detect', $result);
          }
          break;
        // Распознаём все СМС
        case 'detect_all':
          $result = $detector->detectAllSmsUnknown();
          if ($result === false) {
            $this->notify->sendNotify('Во время распознавания SMS возникла ошибка.', ERROR_NOTIFY);
          } else {
            // Сохраняем данные между сбросом POST
            $this->forwarder->save('detect', $result);
          }
          break;
      }
      $this->postReset();
    }
    // Получаем данные между сбросом POST
    $data = $this->forwarder->load('detect'); // todo избавиться от форвардера и сделать отдельное окно для отчёта
    if ($data) {
      $this->template->set('detect', $data);
    }
    // Получение всех нераспознанных SMS для вывода
    $sms = $detector->getViewSmsUnknown();
    $this->template->set('detector', $sms);
    $this->template->setTitle('Детектор нераспознанных SMS');
    $this->template->show('detector');
  }

  /**
   * Удаление кэша с сайта СП
   */
  function cache_del () {
    $this->access(ADMIN);
    $settings = new SettingsAdmin(Registry_Session::instance()->get('user_id'));
    $result = $settings->delCache();
    if ($result) {
      $this->notify->sendNotify("Кэш успешно очищен", SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось очистить кэш', ERROR_NOTIFY);
    }
    $url = URL::to('admin/settings');
    $this->headerLocation($url);
  }

  /**
   * Управление пользователями
   */
  function users () {
    $this->access(ADMIN);
    $admin = new Admin();
    $users = $admin->getUsersView();
    $this->template->set('data', $users);
    $this->template->setTitle('Управление пользователями');
    $this->template->show('users');
  }

  /**
   * Повторная отсылка письма со ссылкой для активации
   */
  function reactivate () {
    $this->access(ADMIN);
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (!empty($id)) {
      $admin = new Admin();
      $result = $admin->reactivate($id);
      if ($result) {
        $this->notify->sendNotify('Повторное письмо со ссылкой для активации выслано пользователю.', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось выслать пользователю письмо со ссылкой для активации.', ERROR_NOTIFY);
      }
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Принудительная активация пользователя администратором
   */
  function force_activate () {
    $this->access(ADMIN);
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (!empty($id)) {
      $admin = new Admin();
      $result = $admin->forceActivate($id);
      if ($result) {
        $this->notify->sendNotify('Аккаунт пользователя успешно активирован.', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось активировать аккаунт пользователя.', ERROR_NOTIFY);
      }
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Принудительная отправка письма администратором для восстановления пароля пользователя
   */
  function force_forgot () {
    $this->access(ADMIN);
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (!empty($id)) {
      $admin = new Admin();
      $result = $admin->forceForgot($id);
      if ($result) {
        $this->notify->sendNotify('Письмо со ссылкой для восстановления пароля выслано пользователю.', SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось выслать пользователю письмо со ссылкой для восстановления пароля.', ERROR_NOTIFY);
      }
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Управление пользователем
   */
  function manage_user () {
    $this->access(ADMIN);
    $this->postReset();
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (!empty($id)) {
      $admin = new Admin();
      $user = $admin->getUserView($id);
      $this->template->set('data', $user);
      $this->template->setTitle('Управление пользователем');
      $this->template->show('manage_user');
    } else {
      $url = URL::to('admin/users');
      $this->headerLocation($url);
    }
  }

  /**
   * Добавить пользователю заказ вручную
   */
  function order_add () {
    $this->access(ADMIN);
    $day = isset($_GET['day']) ? (int)$_GET['day'] : 0;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!empty($day) and !empty($id)) {
      $admin = new Admin();
      $result = $admin->addOrderManual($id, $day);
      if ($result) {
        $this->notify->sendNotify("Заказ успешно добавлен пользователю", SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось добавить заказ пользователю', ERROR_NOTIFY);
      }
      $url = Kit::getRefererURL();
      $this->headerLocation($url);
    } else {
      $url = URL::to('admin/users');
      $this->headerLocation($url);
    }
  }

  /**
   * Контроллер обработки действий с заказами
   */
  function order () {
    $this->access(ADMIN);
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $oid = isset($_POST['oid']) ? (int)$_POST['oid'] : 0;
    $uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
    if (!empty($oid) and !empty($uid) and !empty($action)) {
      $admin = new Admin();
      switch ($action) {
        // Возврат заказа
        case 'return':
          $result = $admin->returnOrder($uid, $oid);
          if ($result) {
            $this->notify->sendNotify("Заказ успешно возвращён", SUCCESS_NOTIFY);
          } else {
            $this->notify->sendNotify('Не удалось возвратить заказ', ERROR_NOTIFY);
          }
          break;
        // Отмена возврата заказа
        case 'cancel':
          $result = $admin->cancelReturnOrder($uid, $oid);
          if ($result) {
            $this->notify->sendNotify("Отмена возврата прошла успешно", SUCCESS_NOTIFY);
          } else {
            $this->notify->sendNotify('Не удалось отменить возврат заказа', ERROR_NOTIFY);
          }
          break;
        // Удалить заказ
        case 'delete':
          $result = $admin->deleteOrder($uid, $oid);
          if ($result) {
            $this->notify->sendNotify("Заказ успешно удалён", SUCCESS_NOTIFY);
          } else {
            $this->notify->sendNotify('Не удалось удалить заказ', ERROR_NOTIFY);
          }
          break;
      }
      $url = Kit::getRefererURL();
      $this->headerLocation($url);
    } else {
      $url = URL::to('admin/users');
      $this->headerLocation($url);
    }
  }

  /**
   * Контроллер изменения типа запроса пользователя к сайту СП
   */
  function request () {
    $this->access(ADMIN);
    $request = isset($_POST['request']) ? $_POST['request'] : 0;
    $uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
    if (!empty($request) and !empty($uid)) {
      $admin = new Admin();
      $result = $admin->setUserRequest($uid, $request);
      if ($result) {
        $this->notify->sendNotify("Способ запроса к сайту СП успешно изменён", SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось изменить способ запроса к сайту СП', ERROR_NOTIFY);
      }
      $url = Kit::getRefererURL();
      $this->headerLocation($url);
    } else {
      $url = URL::to('admin/users');
      $this->headerLocation($url);
    }
  }

  /**
   * Компенсации пользователям
   */
  function compensation () {
    $this->access(ADMIN);
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $date = isset($_POST['datetime']) ? $_POST['datetime'] : '';
    $day = isset($_POST['day']) ? (int)$_POST['day'] : 0;
    if (!empty($type) and !empty($date) and !empty($day)) {
      $admin = new Admin();
      $result = $admin->compensation($date, $day, $type);
      if ($result['result']) {
        $this->notify->sendNotify("Компенсация успешно начислена. Всего пользователей получили компенсацию: {$result['count']} чел.", SUCCESS_NOTIFY);
      } else {
        $this->notify->sendNotify('Не удалось начислить компенсацию', ERROR_NOTIFY);
      }
    }
    $this->postReset();
    $this->template->setTitle('Компенсации пользователям');
    $this->template->show('compensation');
  }

  /**
   * Заблокировать или разблокировать пользователя
   */
  function blocked () {
    $this->access(ADMIN);
    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
    $blocked = isset($_GET['blocked']) ? (int)$_GET['blocked'] : 0;
    if (!empty($uid)) {
      $admin = new Admin();
      $result = $admin->blocked($uid, $blocked);
      if ($result) {
        $message = $blocked ? 'Пользователь заблокирован' : 'Пользователь разблокирован';
        $this->notify->sendNotify($message, SUCCESS_NOTIFY);
      } else {
        $message = $blocked ? 'Не удалось заблокировать пользователя' : 'Не удалось разблокировать пользователя';
        $this->notify->sendNotify($message, ERROR_NOTIFY);
      }
      $url = Kit::getRefererURL();
      $this->headerLocation($url);
    } else {
      $url = URL::to('admin/users');
      $this->headerLocation($url);
    }
  }

  /**
   * Подробности о платеже
   */
  function details () {
    $this->access(ADMIN);
    $paymentId = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
    $userId = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
    $info = array();
    if (!empty($paymentId) and !empty($userId)) {
      $admin = new Admin();
      $info = $admin->getViewPaymentDetails($paymentId, $userId);
    }
    $this->template->set('info', $info);
    $this->template->setTitle('Подробности о платеже');
    $this->template->show('details');
  }

  /**
   * Удаление update.php
   */
  function update_del () {
    $this->access(ADMIN);
    // Получаем URL для редиректа
    $url = Kit::getRefererURL();
    // Если URL получен
    if (!empty($url)) {
      $admin = new Admin();
      $admin->delUpdateScript();
      // Редирект на целевую страницу
      $this->headerLocation($url);
    }
    // Вывод ошибки, если URL для редиректа не получен
    $controller = new Controller_Error;
    $controller->index(__LINE__, __FILE__);
  }

  /**
   * Управление шаблонами SMS
   */
  function templates () {
    $this->access(ADMIN);
    $admin = new Admin();
    $type = isset($_GET['type']) ? (int)$_GET['type'] : 0;
    $info = $admin->getViewTemplates($type);
    $this->template->set('info', $info);
    $this->template->setTitle('Управление шаблонами SMS');
    $this->template->show('templates');
  }

  /**
   * Изменить шаблон
   */
  function edit_tpl () {
    // var_dump($_POST);
    $this->access(ADMIN);
    $tid = isset($_POST['tid']) ? (int)$_POST['tid'] : 0;
    $tpl = isset($_POST['template']) ? $_POST['template'] : '';
    $active = isset($_POST['active']) ? $_POST['active'] : 0;
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $admin = new Admin();
    $result = $admin->editTpl($tid, $tpl, $active, $description);
    if ($result) {
      $this->notify->sendNotify("Шаблон успешно изменён", SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось изменить шаблон', ERROR_NOTIFY);
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Удаление шаблона
   */
  function delete_tpl () {
    $this->access(ADMIN);
    $tid = isset($_GET['tid']) ? (int)$_GET['tid'] : 0;
    $admin = new Admin();
    $result = $admin->deleteTpl($tid);
    if ($result) {
      $this->notify->sendNotify("Шаблон успешно удалён", SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось удалить шаблон', ERROR_NOTIFY);
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Добавление шаблона
   */
  function add_tpl () {
    $this->access(ADMIN);
    $admin = new Admin();
    $type = isset($_POST['type']) ? (int)$_POST['type'] : 1;
    if (!empty($_POST)) {
      $subtype = isset($_POST['subtype']) ? (int)$_POST['subtype'] : 0;
      $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
      $template = isset($_POST['template']) ? $_POST['template'] : '';
      $description = isset($_POST['description']) ? $_POST['description'] : '';
      $result = $admin->addTpl($type, $subtype, $active, $template, $description);
      if ($result) {
        $this->notify->sendNotify("Шаблон успешно добавлен", SUCCESS_NOTIFY);
        $this->headerLocation(URL::to('admin/templates'));
      } else {
        $this->notify->sendNotify('Не удалось добавить шаблон', ERROR_NOTIFY);
      }
    }
    $info = $admin->getViewAdd($type);
    $this->template->set('info', $info);
    $this->template->setTitle('Добавлние шаблона SMS');
    $this->template->show('add_tpl');
  }

  /**
   * Сброс статистики по шаблонам
   */
  function stat_tpl_reset () {
    $this->access(ADMIN);
    $admin = new Admin();
    $result = $admin->statResetAllTpl();
    if ($result) {
      $this->notify->sendNotify("Статистика использования шаблонов сброшена", SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось сбросить статистику использования шаблонов', ERROR_NOTIFY);
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Экспорт шаблонов SMS
   */
  function export_tpl () {
    $this->access(ADMIN);
    $admin = new Admin();
    $admin->exportTpl();
  }

  /**
   * Импорт шаблонов SMS
   */
  function import_tpl () {
    $this->access(ADMIN);
    if (isset($_FILES['file'])) {
      $admin = new Admin();
      $result = $admin->importTpl($_FILES['file']);
      if ($result) {
        $this->notify->sendNotify("Шаблоны успешно импортированы", SUCCESS_NOTIFY);
        $this->headerLocation(URL::to('admin/templates'));
      } else {
        $this->notify->sendNotify('Не удалось импортировать шаблоны', ERROR_NOTIFY);
        $this->postReset();
      }
    }
    $this->template->setTitle('Импорт шаблонов SMS');
    $this->template->show('import_tpl');
  }

  /**
   * Управление сайтами СП
   */
  function sp () {
    $this->access(ADMIN);
    $sp = new Sp();
    $info = $sp->getViewEditorSpList();
    $this->template->set('info', $info);
    $this->template->setTitle('Управление сайтами СП');
    $this->template->show('sp');
  }

  /**
   * Изменить сайт СП
   */
  function edit_sp () {
    // var_dump($_POST);
    $this->access(ADMIN);
    // Инициализация
    $spArr = array();
    $spArr[SP_ID] = isset($_POST[SP_ID]) ? (int)$_POST[SP_ID] : 0;
    $spArr[SP_SITE_NAME] = isset($_POST[SP_SITE_NAME]) ? $_POST[SP_SITE_NAME] : '';
    $spArr[SP_SITE_URL] = isset($_POST[SP_SITE_URL]) ? $_POST[SP_SITE_URL] : '';
    $spArr[SP_DESCRIPTION] = isset($_POST[SP_DESCRIPTION]) ? $_POST[SP_DESCRIPTION] : '';
    $spArr[SP_FILLING_DAY] = isset($_POST[SP_FILLING_DAY]) ? $_POST[SP_FILLING_DAY] : 0;
    $spArr[SP_REQUEST] = isset($_POST[SP_REQUEST]) ? $_POST[SP_REQUEST] : 0;
    $spArr[SP_TIME_ZONE] = isset($_POST[SP_TIME_ZONE]) ? $_POST[SP_TIME_ZONE] : '';
    $spArr[SP_ACTIVE] = isset($_POST[SP_ACTIVE]) ? $_POST[SP_ACTIVE] : 0;
    $sp = new Sp();
    $result = $sp->editSp($spArr);
    if ($result) {
      $this->notify->sendNotify("Сайт СП успешно изменён", SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось изменить сайт СП', ERROR_NOTIFY);
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  /**
   * Добавление сайта СП
   */
  function add_sp () {
    $this->access(ADMIN);
    $sp = new Sp();
    if (!empty($_POST)) {
      // Инициализация
      $spArr = array();
      $spArr[SP_SITE_NAME] = isset($_POST[SP_SITE_NAME]) ? $_POST[SP_SITE_NAME] : '';
      $spArr[SP_SITE_URL] = isset($_POST[SP_SITE_URL]) ? $_POST[SP_SITE_URL] : '';
      $spArr[SP_DESCRIPTION] = isset($_POST[SP_DESCRIPTION]) ? $_POST[SP_DESCRIPTION] : '';
      $spArr[SP_FILLING_DAY] = isset($_POST[SP_FILLING_DAY]) ? $_POST[SP_FILLING_DAY] : 0;
      $spArr[SP_REQUEST] = isset($_POST[SP_REQUEST]) ? $_POST[SP_REQUEST] : 0;
      $spArr[SP_TIME_ZONE] = isset($_POST[SP_TIME_ZONE]) ? $_POST[SP_TIME_ZONE] : '';
      $spArr[SP_ACTIVE] = isset($_POST[SP_ACTIVE]) ? $_POST[SP_ACTIVE] : 0;
      $result = $sp->addSp($spArr);
      if ($result) {
        $this->notify->sendNotify("Сайт СП успешно добавлен", SUCCESS_NOTIFY);
        $this->headerLocation(URL::to('admin/sp'));
      } else {
        $this->notify->sendNotify('Не удалось добавить сайт СП', ERROR_NOTIFY);
      }
    }
    $info = $sp->getViewAdd();
    $this->template->set('info', $info);
    $this->template->setTitle('Добавлние шаблона SMS');
    $this->template->show('add_sp');
  }

  /**
   * Экспорт списка сайтов СП
   */
  function export_sp () {
    $this->access(ADMIN);
    $sp = new Sp();
    $sp->exportSp();
  }

  /**
   * Импорт списка сайтов СП
   */
  function import_sp () {
    $this->access(ADMIN);
    if (isset($_FILES['file'])) {
      $sp = new Sp();
      $result = $sp->importSp($_FILES['file']);
      if ($result) {
        $this->notify->sendNotify("Список сайтов СП успешно импортирован", SUCCESS_NOTIFY);
        $this->headerLocation(URL::to('admin/sp'));
      } else {
        $this->notify->sendNotify('Не удалось импортировать список сайтов СП', ERROR_NOTIFY);
        $this->postReset();
      }
    }
    $this->template->setTitle('Импорт списка сайтов СП');
    $this->template->show('import_sp');
  }

  /**
   * Удаление сайта СП
   */
  function delete_sp () {
    $this->access(ADMIN);
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $sp = new Sp();
    $result = $sp->deleteSp($id);
    if ($result) {
      $this->notify->sendNotify("Сайт СП успешно удалён", SUCCESS_NOTIFY);
    } else {
      $this->notify->sendNotify('Не удалось удалить сайт СП', ERROR_NOTIFY);
    }
    $url = Kit::getRefererURL();
    $this->headerLocation($url);
  }

  //  function test_mail(){
  //    $mail = new Mail();
  //    $a = $mail->getExamplesAllTpl();
  //    $tpl = 'reminding_forgot';
  //    print('<pre>' . $a['plain'][$tpl] . '</pre>');
  //    print($a['html'][$tpl]);
  //  }

  //  function test(){
  //    $db = new DataBase(Registry_Request::instance()->get('db'));
  //    $spId = '1';
  //    $result = $db->issetSpId($spId);
  //    var_dump($result);
  //  }

}
