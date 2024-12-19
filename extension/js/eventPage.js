/**
 * Константы
 */

var PURCHASE_NOT_SELECT = 5; // Закупка не выбрана
var ERROR_AUTHORIZATION = 7; // Нет авторизации на сайте СП

/**
 * Переменные
 */

var checkStatus = new CheckStatus();

/**
 * Открыть вкладку с сервисом, если нажали на иконку расширения
 */
chrome.browserAction.onClicked.addListener(function () {
  chrome.tabs.create({url: getUrlService()});
});

/**
 * Получение сообщения от сервиса и отправка ответа
 */
chrome.runtime.onMessageExternal.addListener(requestHandler);

/**
 * Инициализация статуса расширения
 */
window.addEventListener('load', function () {
  checkStatus.init();
});

/**
 * Обновление статуса расширения, если меняются cookie
 */
chrome.cookies.onChanged.addListener(function (info) {
  checkStatus.updateSpCookie(info);
});

/**
 * Обработака полученного от страницы запроса
 * @param request Содержание запроса
 * @param sender Информация о контент скрипте пославшем сообщение
 * @param callback Функция вызываемая в качестве ответа
 */
function requestHandler(request, sender, callback) {
  // console.log(request);
  switch (request.typeRequest) {
    // Проверка наличия установленного и включенного расширения
    case 'existExtension':
      existExtension(callback);
      break;
    // Получение списка закупок с сайта СП
    case 'getListPurchase':
      requestService(request, callback);
      return true;
      break;
    // Обновление статуса пользователя
    case 'userInfo':
      checkStatus.updateUserStatus(request.userAuth);
      break;
    // Автоматический поиск СМС
    case 'autoAnalysis':
      if (request.error == PURCHASE_NOT_SELECT) {
        // Если закупка не выбрана, вернуть запрос
        callback(JSON.stringify({info: request}));
      } else {
        requestService(request, callback);
        return true;
      }
      break;
    // Проверка авторизации
    case 'authorizationExtension':
      callback(checkStatus.itWorks());
      break;
    // Автопроставление оплаты
    case 'autoFilling':
      requestService(request, callback);
      return true;
      break;
    // Проверка общей суммы
    case 'checkTotal':
      requestService(request, callback);
      return true;
      break;
    // Редактор закупок
    case 'editorPurchase':
      if (request.cache) {
        // Если данные получены из кэша, то вернуть запрос
        callback(JSON.stringify({info: request}));
      } else {
        // Если кэша нет, то получить закупку
        requestService(request, callback);
        return true;
      }
      break;
    // Обновление суммы в заказе
    case 'updateSum':
      requestService(request, callback);
      return true;
      break;
  }
}
/**
 * Запрос от страницы к сайту СП
 * @param request Данные для запроса
 * @param callback Ответ запросу
 */
function requestService(request, callback) {
  getBodyFromSite(request, passBody); // Получить тело страницы с сайта СП

  /**
   * Перенаправить полученный ответ сервису
   * @param body string Тело страницы с ответом
   * @param responseURL string Заголовки ответа
   */
  function passBody(body, responseURL) {
    // console.log(body);
    // console.log(responseURL);
    request.orgId = checkStatus.getSpUserId();
    request.urlResponse = responseURL;
    // console.log(request.urlResponse, request.urlRequest);
    getData(body, request, callback);
  }
}

/**
 * Запрос об активности расширения
 * @param callback
 */
function existExtension(callback) {
  callback(true);
}

/**
 * Запрос к сайту СП через расширение браузера
 * @param request Данные запроса
 * @param callback Возвращаемый результат
 */
function getBodyFromSite(request, callback) {
  var xhr = new XMLHttpRequest();
  var responseURL = '';

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        // Успех
        if (typeof xhr.responseURL !== 'undefined') {
          responseURL = xhr.responseURL;
        }
        callback(xhr.responseText, responseURL);
      } else {
        // Ошибка
      }
    }
  };

  xhr.open("POST", request.urlRequest, true);
  xhr.setRequestHeader("Content-Charset", "UTF-8");
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send(request.cmdSite);
}

/**
 * Обработка ответа от сайта СП при помощи сервиса
 * @param body Ответ полученный с сайта СП
 * @param request Данные запроса
 * @param callback Возвращаемый результат
 */
function getData(body, request, callback) {
  var xhr = new XMLHttpRequest();

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        // Успех
        //console.log(xhr.responseText);
        callback(xhr.responseText);
      } else {
        // Ошибка
      }
    }
  };

  xhr.open("POST", getUrlServiceCommand(), true);
  xhr.setRequestHeader("Content-Charset", "UTF-8");
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send(request.cmdService + encodeURIComponent(body) + '&request=' + encodeURIComponent(JSON.stringify(request)));
}

/**
 * Получить url сервиса
 */
function getUrlService() {
  return SERVICE_URL;
}

/**
 * Получить url API сервиса
 */
function getUrlServiceCommand() {
  return getUrlService() + COMMAND_URL;
}