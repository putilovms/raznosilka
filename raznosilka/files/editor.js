/**
 * Инициализация обработчиков для кнопок "Редактора закупок"
 */
function initEditorButtons() {
  var buttons = document.getElementsByTagName('button');
  var forms = document.getElementsByTagName('form');
  var x;
  // Обработчики для кнопок
  if (buttons.length) {
    for (x = 0; x < buttons.length; x++) {
      // Отметить ошибочный платёж
      if (classie.has(buttons[x], 'error-set')) {
        buttons[x].addEventListener("click", payErrorSet, false);
      }
      // Удалить отметку об ошибочности
      if (classie.has(buttons[x], 'error-del')) {
        buttons[x].addEventListener("click", payErrorDel, false);
      }
      // Обновить сумму на сайте СП
      if (classie.has(buttons[x], 'update-sum')) {
        buttons[x].addEventListener("click", updateSum, false);
      }
      // Удалить корректировку
      if (classie.has(buttons[x], 'delete-correction')) {
        buttons[x].addEventListener("click", correctionDel, false);
      }
      // Удалить проставленную оплату
      if (classie.has(buttons[x], 'pay-del')) {
        buttons[x].addEventListener("click", payDel, false);
      }
      // Удалить потерянную проставленную оплату
      if (classie.has(buttons[x], 'lost-pay-del')) {
        buttons[x].addEventListener("click", lostPayDel, false);
      }
    }
  }
  // Обработчики для форм
  if (forms.length) {
    for (x = 0; x < forms.length; x++) {
      // Добавить корректировку
      if (classie.has(forms[x], 'correction-add')) {
        forms[x].addEventListener("submit", correctionAdd, false);
      }
    }
  }
}

/**
 * Устанавливает обработчик для проставления оплаты при помощи найденной в ручную СМС
 */
(function () {
  window.addEventListener("load", function () {
    var buttons = document.getElementsByClassName('sms-apply');
    if (buttons) {
      for (var i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener("click", smsApply, false);
      }
    }
  }, false);
})();

/**
 * Обработчик для кнопки "Проставить оплату найденной SMS"
 * для модуля "Поиск СМС"
 */
function smsApply() {
  var spinner = document.getElementById('search-overlay');
  var smsId = this.value;
  // Запрос
  if (typeof pageData !== "undefined") {
    var cmd = pageData.search.cmd + smsId;
    var url = pageData.search.redirect_url;
    classie.remove(spinner, 'hide');
    editorSendCmdServer(cmd, update, redirect);
  }

  /**
   * Обновить сумму на сайте СП
   * @param data string Данные полученные от сервера
   */
  function update(data) {
    var cmd;
    // Проверка входящих данных
    try {
      cmd = JSON.parse(data);
    } catch (e) {
      redirect(); // todo Не выдаётся уведомление об ошибке после редиректа при таком подходе
      return;
    }
    // Обновить сумму
    switch (USER_REQUEST) {
      // Запрос к сайту СП через расширение
      case REQUEST_EXTENSIONS:
        cmd.cmdService += '&notify=true' + '&body=';
        readyExtensionCheck(extensionRequest.bind(null, cmd, redirect, redirect), redirect);
        break;
      // Запрос к сайту СП через сервер по умолчанию
      default :
        cmd += '&notify=true';
        editorSendCmdServer(cmd, redirect, redirect);
        break;
    }
  }

  /**
   * Редирект обратно
   */
  function redirect() {
    location.href = url;
  }
}

/**
 * Обработчик для кнопки "Удалить потерянную проставленную оплату"
 */
function lostPayDel() {
  var lot = getLotInfo(this, true);
  // Запрос
  if (typeof pageData !== "undefined") {
    classie.remove(lot.spinner, 'hide');
    var cmd = pageData.editor.lost_lots[lot.keyLot].cmd.pays[lot.keyPay].lost_pay_del;
    editorSendCmdServer(cmd, successRequest, errorRequest);
  }

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    updateLostLot(data, lot.keyLot, lot.divLot);
    // Вывести сообщение об успехе
    viewNotify('Потерянная оплата успешно удалена', 'success');
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось удалить потерянную оплату', 'error');
  }
}

/**
 * Обработчик для кнопки "Удалить проставленную оплату"
 */
function payDel(event) {
  var that = this;
  var lot = getLotInfo(this);
  // Запрос
  if (typeof pageData !== "undefined") {
    classie.remove(lot.spinner, 'hide');
    var cmd = pageData.editor.lots[lot.keyLot].cmd.pays[lot.keyPay].pay_del + pageData.editor.view;
    editorSendCmdServer(cmd, successRequest, errorRequest);
  }

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    if (updateLot(data, lot.keyLot, lot.divLot)) {
      // Вывести сообщение об успехе
      viewNotify('Проставленная оплата успешно удалена', 'success');
      // Обновить суумму на сайте СП
      updateSum(event, that);
    } else {
      errorRequest();
    }
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось удалить проставленную оплату', 'error');
  }
}

/**
 * Обработчик для кнопки "Удалить корректировку"
 */
function correctionDel(event) {
  var that = this;
  var lot = getLotInfo(this);
  // Запрос
  if (typeof pageData !== "undefined") {
    classie.remove(lot.spinner, 'hide');
    var cmd = pageData.editor.lots[lot.keyLot].cmd.corrections[lot.keyPay].correction_del + pageData.editor.view;
    editorSendCmdServer(cmd, successRequest, errorRequest);
  }

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    if  (updateLot(data, lot.keyLot, lot.divLot) ) {
      // Вывести сообщение об успехе
      viewNotify('Корректировка успешно удалена', 'success');
      // Обновить суумму на сайте СП
      updateSum(event, that);
    } else {
      errorRequest();
    }
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось удалить корректировку', 'error');
  }
}

/**
 * Обработчик для кнопки "Добавить корректировку"
 */
function correctionAdd(event) {
  var that = this;
  var lot = getLotInfo(this);
  // Запрос
  if (typeof pageData !== "undefined") {
    classie.remove(lot.spinner, 'hide');
    var cmd = serializeForm(this) + '&view=' + pageData.editor.view;
    editorSendCmdServer(cmd, successRequest, errorRequest);
  }
  // Отменить действие по умолчанию
  event = event || window.event;
  event.preventDefault();

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    if (updateLot(data, lot.keyLot, lot.divLot)) {
      // Вывести сообщение об успехе
      viewNotify('Корректировка успешно добавлена', 'success');
      // Обновить суумму на сайте СП
      updateSum(event, that);
    } else {
      errorRequest();
    }
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось добавить корректировку', 'error');
  }
}

/**
 * Обработчик для кнопки "Обновить сумму на сайте СП"
 */
function updateSum(event, element) {
  element = element || this;
  var lot = getLotInfo(element);

  // Запрос
  if (typeof pageData !== "undefined") {
    var cmd = pageData.editor.lots[lot.keyLot].cmd.update_sum;
    classie.remove(lot.spinner, 'hide');
    // Контроллер запросов
    switch (USER_REQUEST) {
      // Запрос к сайту СП через расширение
      case REQUEST_EXTENSIONS:
        cmd.cmdService += pageData.editor.view + '&body=';
        readyExtensionCheck(extensionRequest.bind(null, cmd, successRequest, errorRequest), errorRequest);
        break;
      // Запрос к сайту СП через сервер по умолчанию
      default :
        cmd += pageData.editor.view;
        editorSendCmdServer(cmd, successRequest, errorRequest);
        break;
    }
  }

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    if (updateLot(data, lot.keyLot, lot.divLot)) {
      // Вывести сообщение об успехе
      viewNotify('Сумма успешно проставлена на сайте СП', 'success');
    } else {
      errorRequest();
    }
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось проставить сумму на сайте СП', 'error');
  }

}

/**
 * Обработчик для кнопки "Удалить отметку ошибочности у платёжа"
 */
function payErrorDel() {
  var lot = getLotInfo(this);
  // Запрос
  if (typeof pageData !== "undefined") {
    var cmd = pageData.editor.lots[lot.keyLot].cmd.pays[lot.keyPay].error_del + pageData.editor.view;
    classie.remove(lot.spinner, 'hide');
    editorSendCmdServer(cmd, successRequest, errorRequest);
  }

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    if (updateLot(data, lot.keyLot, lot.divLot)){
      // Вывести сообщение об успехе
      viewNotify('Удаление отметки, о том что оплата ошибочная, выполнено успешно', 'success');
    } else {
      errorRequest();
    }
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось удалить отметку, о том что оплата ошибочная', 'error');
  }
}

/**
 * Обработчик для кнопки "Отметить платёж как ошибочный"
 */
function payErrorSet() {
  var lot = getLotInfo(this);
  // Запрос
  if (typeof pageData !== "undefined") {
    var cmd = pageData.editor.lots[lot.keyLot].cmd.pays[lot.keyPay].error_set + pageData.editor.view;
    classie.remove(lot.spinner, 'hide');
    editorSendCmdServer(cmd, successRequest, errorRequest);
  }

  /**
   * Обработчик, если операция прошла успешно
   * @param data string Данные для вывода заказа, полученные от сервера
   */
  function successRequest(data) {
    // Обновить заказ
    if (updateLot(data, lot.keyLot, lot.divLot)){
      // Вывести сообщение об успехе
      viewNotify('Оплата отмечена как ошибочная успешно', 'success');
    } else {
      errorRequest()
    }
  }

  /**
   * Обработчик, если операция окончилась с ошибкой
   */
  function errorRequest() {
    // Вывести сообщение об ошибке
    classie.add(lot.spinner, 'hide');
    viewNotify('Не удалось отметить оплату как ошибочную', 'error');
  }
}

/**
 * Получить информацию о заказе
 * @param button Element Кнопка управления заказом
 * @returns {Object}
 */
function getLotInfo(button, lost) {
  var result = {};
  result.keyLot = button.name.toString().split('-')[0];
  result.keyPay = button.name.toString().split('-')[1];
  if (lost) {
    result.divLot = document.getElementById('lost-lot-' + result.keyLot);
  } else {
    result.divLot = document.getElementById('lot-' + result.keyLot);
  }
  result.spinner = result.divLot.getElementsByClassName('lot-overlay')[0];
  return result;
}

/**
 * Обновить заказ
 * @param data Object Данные для обновления заказа
 * @param keyLot int Номер заказа
 * @param lot Element Заказ который необходимо обновить
 */
function updateLot(data, keyLot, lot) {
  var editor = new Editor();
  try {
    data = JSON.parse(data);
  } catch (e) {
    return false;
  }
  // Обновить данные
  pageData.editor.lots[keyLot] = data.lot;
  pageData.statistic = data.statistic;
  pageData.filters = data.filters;
  // Обновить фильтр
  editor.editorFilter(pageData.editor.view, pageData.filters);
  // Обновить заказ
  var newLot = editor.getLot(keyLot, pageData.editor.lots[keyLot], editor._getPay);
  lot.parentNode.replaceChild(newLot, lot);
  viewLabel();
  // Обновить статистику
  editor.editorStatistic(pageData.statistic);
  // Обновить обработчики для лота
  initEditorButtons();
  return true;
}

/**
 * Вывести сообщение, если не осталось ни одного заказа для вывода.
 */
function viewLabel() {
  var lotsWrapper = document.getElementById('lots-wrapper');
  var label = document.getElementById('no-pay');
  var lots = lotsWrapper.getElementsByClassName('lot');
  var displayLabel = true;
  // Запустить фильтр
  for (var i = 0; i < lots.length; i++) {
    if (!classie.has(lots[i], 'hide')) {
      displayLabel = false;
    }
  }
  displayLabel ? classie.remove(label, 'hide') : classie.add(label, 'hide');
}

/**
 * Обновить потерянный заказ
 * @param data Object Данные для обновления потерянного заказа
 * @param keyLot int Номер потерянного заказа
 * @param lot Element Потерянный заказ который необходимо обновить
 */
function updateLostLot(data, keyLot, lot) {
  var editor = new Editor();
  if (data === 'true') {
    // pageData.editor.lost_lots.splice(keyLot, 1);
    delete pageData.editor.lost_lots[keyLot];
    // Обновить заказ
    lot.parentNode.removeChild(lot);
    // Если потерянных платежей нет, то удлить контейнер
    if (emptyArray(pageData.editor.lost_lots)) {
      var lostLotsWrapper = document.getElementById('lost-lots-wrapper');
      lostLotsWrapper.parentNode.removeChild(lostLotsWrapper);
    }
  } else {
    data = JSON.parse(data);
    // Обновить данные
    pageData.editor.lost_lots[keyLot] = data.lot;
    // Обновить заказ
    var newLot = editor.getLostLot(keyLot, pageData.editor.lost_lots[keyLot]);
    lot.parentNode.replaceChild(newLot, lot);
  }
  // Обновить обработчики для лота
  initEditorButtons();
}

/**
 * Запрос через расширение
 */
function extensionRequest(cmd, callbackSuccess, callbackError) {
  chrome.runtime.sendMessage(EXTENSION_ID, cmd, function (response) {
    response !== 'false' ? callbackSuccess(response) : callbackError();
  });
}

/**
 * Команда к серверу
 * @param cmd string Команда к серверу
 * @param callbackSuccess Функция обратного вызова, при успехе
 * @param callbackError Функкция обратного вызова, при неудаче
 */
function editorSendCmdServer(cmd, callbackSuccess, callbackError) {
  var request = getXmlHttpRequest();

  request.onreadystatechange = function () {
    if (request.readyState == 4) {
      if (request.status == 200) {
        if (request.responseText !== 'false') {
          callbackSuccess(request.responseText);
        } else {
          callbackError();
        }
      } else {
        callbackError();
      }
    }
  };

  request.open("POST", COMMAND_URL, true);
  request.setRequestHeader("Content-Charset", "UTF-8");
  request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  request.send(cmd);
}