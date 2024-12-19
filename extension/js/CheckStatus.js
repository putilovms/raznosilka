/**
 * Класс для определения соединения с сайтом СП
 * @constructor
 */
function CheckStatus() {
  var uSp = null; // ID пользователя на сайте СП
  var infoSp = {}; // Данные для получения cookie сайта СП
  var userAuth = null; // Статус авторизации пользователя
  var statusSp = false; // Статус соединения с сайтом СП
  var statusService = false; // Статус соединения с сервисом

  console.log('Создан класс CheckStatus()');

  /**
   * Обновить иконку расширения
   */
  function updateIcon() {
    // Если нет соединения с сервисом
    if (!statusService) {
      chrome.browserAction.setIcon({path: "not_login.png"});
      chrome.browserAction.setTitle({title: "Пожалуйста, войдите в «Разносилку»"});
    }
    // Если есть соединение с сервисом, но нет соединения с сайтом СП
    if (statusService && !statusSp) {
      chrome.browserAction.setIcon({path: "not_login.png"});
      chrome.browserAction.setTitle({title: "Пожалуйста, войдите на сайт СП"});
    }
    // Если есть соединение с сервисом и сайтом СП
    if (statusService && statusSp) {
      chrome.browserAction.setIcon({path: "login.png"});
      chrome.browserAction.setTitle({title: "Всё готово к работе"});
    }
  }

  /**
   * Обновить полученные от сервиса данные для сайта СП
   * @param info JSON объект с информацией для сайта СП
   */
  function updateSpInfo(info) {
    // console.log('Получены данные о сайте СП - ' + JSON.stringify(info));
    // если данные обновились
    if (JSON.stringify(info) !== JSON.stringify(infoSp)) {
      console.log('Обновление информации о сайте СП');
      infoSp = info;
      // Обновить статус соединения с сайтом СП
      updateSpStatus();
    }
  }

  /**
   * Обновление статуса сайта СП, если обновились данные о сайте СП полученные от сервиса
   */
  function updateSpStatus() {
    chrome.cookies.getAll({}, function (cookies) {
      for (var i in cookies) {
        if (cookies[i].name === infoSp.name && ~cookies[i].domain.indexOf(infoSp.domain)) {
          uSp = cookies[i].value;
          uSp > 1 ? setStatusSp(true) : setStatusSp(false);
        }
      }
    });
  }

  /**
   * Нет соединения с сервисом
   */
  function noConnectService() {
    // console.log('noConnectService');
    uSp = null;
    infoSp = {};
    statusSp = false;
  }

  /**
   * Получить данные о cookie для сайта СП от сервиса
   */
  function getCookieInfo() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          try {
            // Есть доступ к сервису
            updateSpInfo(window.JSON.parse(xhr.responseText));
            console.log('Данные с информацией с сайте СП получены');
            setStatusService(true);
          }
          catch (e) {
            // Нет доступа к сервису
            console.log('Не удалось получить данные с информацией с сайте СП');
            // console.log(xhr.responseText);
            noConnectService(); // Сбросить все данные о сайте СП
            setStatusService(false);
          }
        }
      }
    };
    xhr.open("POST", getUrlServiceCommand(), true);
    xhr.setRequestHeader("Content-Charset", "UTF-8");
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send('cmd=get_cookie_info');
  }

  /**
   * Задать статус соединения с сайтом СП
   * @param newStatus boolean Статус соединения с сайтом СП
   */
  function setStatusSp(newStatus) {
    // Если статус обновился
    if (statusSp !== newStatus) {
      statusSp = newStatus;
      console.log('StatusSp ' + statusSp);
      updateIcon();
    }
  }

  /**
   * Задать статус соединения с сервисом
   * @param newStatus boolean Статус соединения сервисом
   */
  function setStatusService(newStatus) {
    // Если статус обновился
    if (statusService !== newStatus) {
      statusService = newStatus;
      console.log('StatusService ' + statusService);
      updateIcon();
    }
  }

  /**
   * Инициализация класса
   */
  this.init = function () {
    console.log('Инициализация');
    getCookieInfo();
    // window.setInterval(getCookieInfo, 30000);
  };

  /**
   * Обновление статуса сайта СП, если обновились куки и изменился ID пользователя сайта СП
   * @param info Информация об изменившейся cookie
   */
  this.updateSpCookie = function (info) {
    // console.log('Запрос на обновление cookie сайта СП');
    if (info.cookie.name === infoSp.name && ~info.cookie.domain.indexOf(infoSp.domain)) {
      console.log('Обновление cookie сайта СП');
      uSp = info.cookie.value;
      uSp > 1 ? setStatusSp(true) : setStatusSp(false);
    }
  };

  /**
   * Обновить статус пользователя сервиса
   * @param userStatus Авторизирован пользователь или нет
   */
  this.updateUserStatus = function (userStatus) {
    // console.log('Запрос на обновление статуса пользователя сервиса');
    // Если статус ползователя сменился
    if (userAuth !== userStatus) {
      console.log('Обновление статуса пользователя сервиса');
      userAuth = userStatus;
      // Обновить информацию о сервисе
      getCookieInfo();
    }
  };

  /**
   * Получить текущий статус готовности работы приложения
   * @returns {number} Статус готовности работы приложения
   */
  this.itWorks = function () {
    console.log('StatusService ' + statusService + '; ' + 'StatusSp ' + statusSp);
    return (statusService && statusSp) ? 1 : 0;
  };

  /**
   * Получить ID текущего пользователя СП
   * @returns {int}
   */
  this.getSpUserId = function () {
    return uSp;
  };

}