/**
 * Константы
 */

// БД
var PAY_TIME = 'pay_time'; // Время платежа указанное участником
var PAY_CARD_PAYER = 'pay_card_payer'; // карта указанная участником в платеже
var PAY_SUM = 'pay_sum'; // Сумма указанная участником
var PAY_CREATED = 'pay_created'; // Время создания отчёта о платеже на сайте СП
var USER_PURCHASE_NICK = 'user_purchase_nick'; // Ник участника закупки
var USER_PURCHASE_NAME = 'user_purchase_name'; // ФИО участника закупки
var SMS_FIO = 'sms_fio'; // ФИО плательщика
var SMS_CARD_PAYER = 'sms_card_payer'; // Номер карты участника
var SMS_COMMENT = 'sms_comment'; // Комментарий в СМС
var SMS_SUM_PAY = 'sms_sum'; // Сумма платежа
var CORRECTION_COMMENT = 'correction_comment'; // Комментарий к корректировке
var CORRECTION_SUM = 'correction_sum'; // Сумма корректировки

/**
 * Расширение
 */

/**
 * Запрос к сайту СП через расширение
 */
(function () {
  window.addEventListener("load", init, false);

  /**
   * Инициализация
   */
  function init() {
    // Если данных для запроса присутствуют
    if (typeof pageData !== 'undefined') {
      // Если запрос к сайту СП через расширение
      if (USER_REQUEST === REQUEST_EXTENSIONS) {
        // Если расширение готово к работе
        readyExtensionCheck(sendRequest, responseExtension.bind(null, JSON.stringify(pageData), true), true);
      } else {
        // Если запрос к сайту СП через сервер
        responseExtension(JSON.stringify(pageData));
      }
    }

    /**
     * Послать запрос к сайту СП через расширение
     */
    function sendRequest() {
      if (typeof pageData.info !== 'undefined') {
        chrome.runtime.sendMessage(EXTENSION_ID, pageData.info, responseExtension);
      }
    }
  }

  /**
   * Обработка ответа от расширения
   * @param response Содержание ответа
   * @param error boolean Ошибка при получении данных
   */
  function responseExtension(response, error) {
    // console.log(response);
    // console.log(error);
    // Проверка входящих данных
    try {
      response = JSON.parse(response);
    } catch (e) {
      viewGeneralError();
      return;
    }
    // console.log(response);
    if (typeof pageData.info !== 'undefined') {
      if (typeof response.info !== 'undefined' && typeof response.info.typeRequest !== 'undefined') {
        switch (response.info.typeRequest) {
          // Получение списка закупок с сайта СП
          case 'getListPurchase':
            var listPurchase = new ListPurchase();
            listPurchase.listPurchaseOrg(response, error);
            return;
            break;
          // Автоматический поиск СМС
          case 'autoAnalysis':
            var analysis = new Analysis();
            analysis.view(response, error);
            return;
            break;
          // Редактор закупок
          case 'editorPurchase':
            var editor = new Editor();
            editor.view(response, error);
            return;
            break;
        }
      }
    }
    viewGeneralError();
  }
})();

/**
 * Проверка возможности передачи данных расширению
 * @param callbackSuccess function Ссылка на функцию которая будет выполнена при наличии возможности отправить данные
 * @param callbackError function Ссылка на функцию которая будет выполнена при отсутствии возможности отправить данные
 * @param reportError boolean Выводить ли ошибки
 */
function readyExtensionCheck(callbackSuccess, callbackError, reportError) {
  // Доступность Crome API
  if (typeof chrome !== 'undefined') {
    // Проверка наличия установленного расширения
    existExtension(exist);
    //chrome.runtime.sendMessage(EXTENSION_ID, {typeRequest: 'existExtension'}, exist);
  } else {
    callbackError();
  }

  /**
   * Проверка наличия установленного и включенного расширения
   * @param response Ответ true если расширение доступно
   */
  function exist(response) {
    if (response) {
      // Расширение установлено и включено
      chrome.runtime.sendMessage(EXTENSION_ID, {typeRequest: 'authorizationExtension'}, authorization);
    } else {
      // Расширение не установлено или выключено
      callbackError();
    }
  }

  /**
   * Проверка авторизации на сайте СП
   * @param response Ответ true если пользователь авторизирован на сайте СП
   */
  function authorization(response) {
    if (response) {
      // Расширение готово к работе
      callbackSuccess();
    } else {
      // Расширение не готово к работе
      callbackError();
      if (reportError) {
        viewError(ERROR_AUTHORIZATION);
      }
    }
  }
}

/**
 * Вывести общую ошибку
 */
function viewGeneralError() {
  var status = document.getElementById('load-status');
  window.viewError(ERROR_OTHER);
  if (status) {
    status.innerText = 'Ошибка: что-то пошло не так...';
  }
}

/**
 * Класс для вывода списка закупок
 * @constructor
 */
function ListPurchase() {
  var table = document.getElementById('list-purchase');
  var status = document.getElementById('load-status');

  /**
   * Вывести пейджер
   * @param pager
   */
  function viewPager(pager) {
    var pagerTop = document.getElementById('pager-top');
    var pagerBottom = document.getElementById('pager-bottom');
    pagerTop.innerHTML = pager;
    pagerBottom.innerHTML = pager;
  }

  /**
   * Вывести полученный с сайта СП список закупок оргинизатора
   */
  function viewListPurchaseOrg(list) {
    // Инициализация
    var tr, td, element;
    // Вывод количества закупок
    var totalPurchase = document.getElementById('total-purchase');
    totalPurchase.innerText = list.item_count;
    // Вывод пейджера
    viewPager(list.pager);
    // Вывод списка закупок
    if (list.item_count > 0) {
      // Удалить статус загрузки
      table.deleteRow(status.parentNode.rowIndex);
      // Вывести список закупок
      for (var index = 0; index < list.purchase.length; ++index) {
        tr = elementCreate('tr', {class: list.purchase[index].class});
        // Добавляем колонку с названием закупки
        td = elementCreate('td', {class: "purchase-name"});
        element = elementCreate('a', {href: list.purchase[index].url_set}, list.purchase[index].name);
        td.appendChild(element);
        tr.appendChild(td);
        // Добавляем колонку с датой оплаты
        td = elementCreate('td', {class: "time center"}, list.purchase[index].pay_to);
        tr.appendChild(td);
        // Добавляем колонку с суммой
        td = elementCreate('td', {class: "sum center"}, list.purchase[index].sum + ' \u20BD');
        tr.appendChild(td);
        // Добавляем колонку со ссылкой
        td = elementCreate('td', {class: "link center"});
        element = elementCreate('span', {class: "button"});
        td.appendChild(element);
        element = elementCreate('a', {href: list.purchase[index].url, class: "external-link", target: "_blank"});
        td.children[0].appendChild(element);
        element = elementCreate('div', {class: "glyph external-link"});
        td.children[0].children[0].appendChild(element);
        element = elementCreate('span', {}, "Перейти в закупку на сайте СП");
        td.children[0].appendChild(element);
        tr.appendChild(td);
        // Добавляем строку в таблицу
        table.tBodies[0].appendChild(tr);
      }
    } else {
      // Если список закупок пуст
      status.innerText = 'Ваш список закупок пуст.';
    }
  }

  /**
   * Вывод ошибки
   * @param error int Код ошибки
   */
  function viewError(error) {
    // Если ошибка
    window.viewError(error);
    status.innerText = 'Не удалось получить список закупок.';
  }

  /**
   * Обработка данных для вывода списока закупок оргинизатора
   * @param response Object Данные ответа
   * @param error boolean Ошибка при получении данных
   */
  this.listPurchaseOrg = function (response, error) {
    // console.log(response, error);
    if (!error) {
      if (response.info.error === ERROR_NONE) {
        // Выводим список закупок
        viewListPurchaseOrg(response.list);
        return;
      }
    }
    viewError(response.info.error);
  }
}

/**
 * Обернуть данные во всплывающую подсказку
 * @param ico string Класс указывающий на иконку отображения для подсказки
 * @param status string Класс указывающий на цвет подсказки
 * @param subject string Заголовок подсказки
 * @param data Array Массив из элементов или строк, которые будут вставлены в подсказку и разделены
 * элементами br
 * @returns {Element} Всплывающая подсказка
 */
function putInTooltip(ico, status, subject, data) {
  var tooltipWrapper = elementCreate('span', {class: "tooltip-wrapper", tabindex: "0"});
  var tooltip = elementCreate('span', {class: "tooltip " + status});
  tooltipWrapper.appendChild(elementCreate('div', {class: ico}));
  tooltip.appendChild(elementCreate('b', {}, subject));
  data.forEach(function (item) {
    tooltip.appendChild(elementCreate('br'));
    if (item instanceof Element) {
      tooltip.appendChild(item);
    } else {
      tooltip.appendChild(document.createTextNode(item));
    }
  });
  tooltipWrapper.appendChild(tooltip);
  return tooltipWrapper;
}

/**
 * Обернуть текст или элемент код в спойлер
 * @param label string Заголовок спойлера
 * @param content Element|string Содержимое спойлера
 * @returns {Element} Спойлер
 */
function putInSpoiler(label, content) {
  var spoiler = elementCreate('dl', {class: "details"});
  var dd = elementCreate('dd');
  var dt = elementCreate('dt');
  dt.appendChild(elementCreate('i', {}, label))
  spoiler.appendChild(dt);
  if (content instanceof Element) {
    // Если содержимое в виде элемента
    dd.appendChild(content);
    spoiler.appendChild(dd);
  } else {
    // Если содержимое в виде строки
    spoiler.appendChild(elementCreate('dd').appendChild(document.createTextNode(content)));
  }
  spoiler.getElementsByTagName('dt')[0].addEventListener("click", clickDetails, false);
  return spoiler;
}

/**
 * Класс для вывода закупок
 * @constructor
 */
function Purchase() {
  this._status = document.getElementById('load-status'); // Статус загрузки

  /**
   * Получить комментарии для заказа
   * @param commentPay string Комментарий к оплате
   * @param commentOrg string Комментарий организатора
   * @param comments array Комментарии участника к каждому товару
   * @returns {Element}
   */
  this._getComments = function (commentPay, commentOrg, comments) {
    var commentsElement = elementCreate('div', {class: 'comments'});
    var ul, element;
    // Комментарии к платежу
    if (commentPay !== '') {
      element = elementCreate('p', {class: 'bold'}, 'Комментарий к оплате:');
      commentsElement.appendChild(element);
      ul = elementCreate('ul', {class: 'list'});
      element = elementCreate('li', {}, commentPay);
      ul.appendChild(element);
      commentsElement.appendChild(ul);
    }
    // Комментарии организатора
    if (commentOrg !== '') {
      element = elementCreate('p', {class: 'bold'}, 'Комментарий организатора:');
      commentsElement.appendChild(element);
      ul = elementCreate('ul', {class: 'list'});
      element = elementCreate('li', {}, commentOrg);
      ul.appendChild(element);
      commentsElement.appendChild(ul);
    }
    // Комментарии участника
    if (comments.length > 0) {
      element = elementCreate('p', {class: 'bold'}, 'Комментарий участника закупки:');
      commentsElement.appendChild(element);
      ul = elementCreate('ul', {class: 'list'});
      for (var key in comments) {
        if (comments.hasOwnProperty(key)) {
          element = elementCreate('li', {}, comments[key]);
        }
      }
      ul.appendChild(element);
      commentsElement.appendChild(ul);
    }
    return commentsElement;
  };

  /**
   * Получить информацию об СМС
   * @param sms Данные об СМС
   * @param tr Элемент в который будет добавлена информация
   */
  this._getSmsInfo = function (sms, tr) {
    var td, element;
    // Ф.И.О.
    if (sms[SMS_FIO]) {
      td = elementCreate('td', {class: "center"});
      element = elementCreate('span', {title: "Ф.И.О. плательщика, полученные из SMS"}, sms[SMS_FIO]);
      td.appendChild(element);
      tr.appendChild(td);
    }
    // № карты
    if (sms[SMS_CARD_PAYER]) {
      td = elementCreate('td', {class: "center"});
      element = elementCreate('span', {title: "№ карты с которой поступила оплата, полученный из SMS"}, sms[SMS_CARD_PAYER]);
      td.appendChild(element);
      tr.appendChild(td);
    }
    // Сообщение
    if (sms[SMS_COMMENT]) {
      td = elementCreate('td', {class: "center"});
      element = elementCreate('span', {title: "Сообщение содержащееся в SMS"}, 'Сообщение: ' + sms[SMS_COMMENT]);
      td.appendChild(element);
      tr.appendChild(td);
    }
    // Если нет ФИО, номера карты и сообщения в СМС
    if (!sms[SMS_FIO] && !sms[SMS_CARD_PAYER] && !sms[SMS_COMMENT]) {
      td = elementCreate('td', {class: "center"}, '—');
      tr.appendChild(td);
    }
    return tr;
  };

  /**
   * Получить таблицу с информацией о СМС
   * @param sms Данные о СМС
   * @returns {Element} Таблица с информацией о СМС
   */
  this._getTableSmsInfo = function (sms) {
    var table = elementCreate('table', {class: "sms-info"});
    var tr = elementCreate('tr');
    table.appendChild(elementCreate('tbody'));
    tr = this._getSmsInfo(sms, tr);
    table.tBodies[0].appendChild(tr);
    return table;
  };

  /**
   * Получить платежи для заказа
   * @param keyLot Номер заказа
   * @param lot Информация о лоте
   * @param paysElement {Element} Область вывода платежей
   * @param callback function Функция отвечающая за отрисовку платежа
   * @returns {Element} Платежи для заказа
   */
  this._getPays = function (keyLot, lot, paysElement, callback) {
    var payElement, table;
    if (lot.active) {
      if (lot.specified) {
        for (var keyPay in lot.pays) {
          if (lot.pays.hasOwnProperty(keyPay)) {
            payElement = elementCreate('div', {class: 'pay'});
            table = elementCreate('table', {class: 'zebra ' + lot.pays[keyPay].status});
            table.appendChild(this._getPayHead(keyPay, lot.pays[keyPay]));
            table.appendChild(elementCreate('tbody'));
            callback = callback.bind(this);
            callback(lot.pays[keyPay], table, keyLot, keyPay);
            payElement.appendChild(table);
            paysElement.appendChild(payElement);
          }
        }
      } else {
        paysElement.appendChild(this._getExplanationPay('Участник не указал ни одной оплаты'));
      }
    } else {
      paysElement.appendChild(this._getExplanationPay('Неактивный заказ'));
    }
    return paysElement;
  };

  /**
   * Получить шапку таблицы для платежа
   * @param keyPay int Номер платежа
   * @param pay Инфомрация о платеже
   * @returns {Element} Шапка таблицы дял платежа
   */
  this._getPayHead = function (keyPay, pay) {
    var head = elementCreate('thead');
    var tr, th, element;
    tr = elementCreate('tr', {class: 'pay ' + pay.status});
    // Номер отчёта
    th = elementCreate('th', {class: 'number'});
    element = putInTooltip('glyph more', pay.status, 'Отчёт об оплате создан:', [pay[PAY_CREATED]]);
    th.appendChild(element);
    element = elementCreate('span', {title: "Номер отчёта об оплате"}, '#' + (parseInt(keyPay) + 1));
    th.appendChild(element);
    tr.appendChild(th);
    // Время оплаты
    th = elementCreate('th', {class: 'time'});
    element = elementCreate('span', {title: "Время оплаты, указанное участником закупки"}, pay[PAY_TIME]);
    th.appendChild(element);
    tr.appendChild(th);
    // № карты
    th = elementCreate('th', {class: 'center'});
    element = elementCreate('span', {title: "№ карты указанный участником закупки, с которой произведена оплата"}, pay[PAY_CARD_PAYER]);
    th.appendChild(element);
    tr.appendChild(th);
    // Сумма оплаты
    th = elementCreate('th', {class: 'sum right'});
    element = elementCreate('span', {title: "Сумма оплаты, указанная участником закупки"}, pay[PAY_SUM] + ' \u20BD');
    th.appendChild(element);
    tr.appendChild(th);
    head.appendChild(tr);
    return head;
  };

  /**
   * Получить неактивный платёж
   * @param text Текст который будет выведен в качестве пояснения
   * @returns {Element} Объект неактивного платежа
   */
  this._getExplanationPay = function (text) {
    var table = elementCreate('table', {class: 'zebra'});
    var tr, td;
    table.appendChild(elementCreate('tbody'));
    tr = elementCreate('tr', {class: 'explanation'});
    td = elementCreate('td', {}, text);
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    return table;
  };

  /**
   * Получить контейнер заказа
   * @param keyLot Номер заказа
   * @param lot Данные для вывода заказа
   * @returns {Element}
   */
  this._getLotDescription = function (keyLot, lot) {
    var lotElement = elementCreate('div', {class: 'lot ' + lot.status + ' ' + lot.filter_tag + ' ' + lot.display});
    var element;
    if (lot.lost_lot) {
      lotElement.id = 'lost-lot-' + keyLot;
    } else {
      lotElement.id = 'lot-' + keyLot;
    }
    // спиннер
    element = elementCreate('div', {class: "lot-overlay hide"});
    element.appendChild(elementCreate('div', {class: "spinner"}));
    lotElement.appendChild(element);
    // Номер заказа
    element = elementCreate('p', {title: "Номер заказа", class: 'user-number bold'}, '#' + (parseInt(keyLot) + 1));
    lotElement.appendChild(element);
    // Ник участника
    element = elementCreate('p', {class: 'bold'});
    element.appendChild(elementCreate('a', {
      href: lot.url,
      title: "Ник участника закупки",
      target: "_blank"
    }, lot[USER_PURCHASE_NICK]));
    lotElement.appendChild(element);
    // ФИО участника
    element = elementCreate('p', {title: 'Ф.И.О. участника закупки'}, lot[USER_PURCHASE_NAME]);
    lotElement.appendChild(element);
    // Область вывода оплат
    element = elementCreate('p', {class: 'bold'}, 'Данные об оплатах и найденных для них SMS:');
    lotElement.appendChild(element);
    element = elementCreate('div', {class: 'pays'});
    lotElement.appendChild(element);
    return lotElement;
  };

  /**
   * Получить заказ
   * @param keyLot Номер заказа
   * @param lot Данные для вывода заказа
   * @param callback function Функция отвечающая за отрисовку платежа
   * @returns {Element}
   */
  this.getLot = function (keyLot, lot, callback) {
    var lotElement = this._getLotDescription(keyLot, lot);
    var pays = lotElement.getElementsByClassName('pays');
    var element;
    // Оплаты
    pays[0] = this._getPays(keyLot, lot, pays[0], callback);
    lotElement.appendChild(pays[0]);
    // Комментарии к заказу
    if (lot.comment_pay !== '' || lot.comment_org !== '' || lot.comments.length > 0) {
      var comments = this._getComments(lot.comment_pay, lot.comment_org, lot.comments);
      element = window.putInSpoiler('Комментарии к заказу', comments);
      lotElement.appendChild(element);
    }
    // Ручное изменение суммы
    if (typeof lot.corrections !== 'undefined') {
      element = this._getManualEdit(keyLot, lot.corrections);
      element = window.putInSpoiler('Ручное изменение суммы', element);
      lotElement.appendChild(element);
    }
    // Итог по заказу
    element = this._getReport(keyLot, lot);
    lotElement.appendChild(element);
    return lotElement;
  };

  /**
   * Вывод ошибки
   * @param error int Код ошибки
   */
  this._viewError = function (error) {
    // Если ошибка
    window.viewError(error);
    this._status.innerText = 'Не удалось получить данные.';
  };
}

/**
 * Класс для вывода "Редактора закупок"
 * @constructor
 */
function Editor() {
  Purchase.call(this); // extends

  /**
   * Получить итоговую таблицу по заказу
   * @param keyLot Номер лота
   * @param lot Данные о заказе
   * @returns {Element} Итоговая таблица по заказу
   * @private
   */
  this._getReport = function (keyLot, lot) {
    var table = elementCreate('table', {class: 'zebra report'});
    var tr, td, element;
    table.appendChild(elementCreate('tbody'));
    // Сумма к внесению участником
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Сумма к внесению участником');
    tr.appendChild(td);
    td = elementCreate('td', {class: "sum right"}, lot.total + ' \u20BD');
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Найдено «Разносилкой»
    tr = elementCreate('tr');
    td = elementCreate('td', {class: "bold " + lot.class_diff_sum_total}, 'Найдено «Разносилкой»');
    tr.appendChild(td);
    td = elementCreate('td', {class: "sum right"});
    element = elementCreate('span', {class: "bold " + lot.class_diff_sum_total}, lot.total_found + ' \u20BD');
    td.appendChild(element);
    if (parseFloat(lot.diff_sum_total_plain) != 0) {
      element = elementCreate('span', {title: "Разница между суммой, которую должен внести участник закупки и суммой найденной «Разносилкой»"});
      element.appendChild(elementCreate('sup', {}, ' ' + lot.diff_sum_total + ' \u20BD'));
      td.children[0].appendChild(element);
    }
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Внесено на сайт СП
    tr = elementCreate('tr');
    td = elementCreate('td');
    element = elementCreate('span', {class: "bold " + lot.class_diff_sum_total_found}, 'Внесено на сайт СП');
    td.appendChild(element);
    tr.appendChild(td);
    td = elementCreate('td', {class: "sum right"});
    element = elementCreate('button', {
      class: "editor update-sum",
      title: "Проставить найденную «Разносилкой» сумму на сайт СП",
      name: keyLot + '-NaN'
    });
    element.appendChild(elementCreate('div', {class: "glyph update-sum"}));
    td.appendChild(element);
    element = elementCreate('span', {class: "bold " + lot.class_diff_sum_total_found}, lot.total_put + ' \u20BD');
    td.appendChild(element);
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    return table;
  };

  /**
   * Получить блок с "Ручным изменением суммы"
   * @param corrections array Массив с корректировками
   * @param keyLot Номер лота
   * @returns {Element}
   * @private
   */
  this._getManualEdit = function (keyLot, corrections) {
    var result = elementCreate('div', {class: 'correction'});
    var table = elementCreate('table', {class: 'zebra'});
    var tr, td, element;
    table.appendChild(elementCreate('tbody'));
    // вывод корректировок
    for (var keyCorrection in corrections) {
      if (corrections.hasOwnProperty(keyCorrection)) {
        tr = elementCreate('tr');
        // кнопка
        td = elementCreate('td', {class: 'number'});
        element = elementCreate('button', {
          class: "editor delete-correction",
          title: "Удалить сумму",
          name: keyLot + '-' + keyCorrection
        });
        element.appendChild(elementCreate('div', {class: "glyph delete-correction"}));
        element.appendChild(elementCreate('span', {}, " Удалить сумму"));
        td.appendChild(element);
        tr.appendChild(td);
        // комментарий
        td = elementCreate('td', {}, corrections[keyCorrection][CORRECTION_COMMENT]);
        tr.appendChild(td);
        // сумма
        td = elementCreate('td', {class: "sum right"}, corrections[keyCorrection][CORRECTION_SUM] + ' \u20BD');
        tr.appendChild(td);
        table.tBodies[0].appendChild(tr);
      }
    }
    // добавление корректировки
    tr = elementCreate('tr');
    td = elementCreate('td', {colspan: "3", class: "form"});
    td.appendChild(this._getCorrectionAdd(keyLot));
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    result.appendChild(table);
    return result;
  };

  /**
   * Получить элемент добавления корректировки
   * @param keyLot integer Номер заказа
   * @returns {Element} Элемент для добавления корректировки
   * @private
   */
  this._getCorrectionAdd = function (keyLot) {
    var form = elementCreate('form', {class: "correction-add", name: keyLot + '-NaN'});
    var table = elementCreate('table');
    var tr, td, element;
    table.appendChild(elementCreate('tbody'));
    tr = elementCreate('tr');
    // кнопка
    td = elementCreate('td', {class: 'number'});
    element = elementCreate('button', {
      class: "editor",
      title: "Внести сумму вручную",
      type: "submit"
    });
    element.appendChild(elementCreate('div', {class: "glyph correction-add"}));
    element.appendChild(elementCreate('span', {}, " Внести сумму"));
    td.appendChild(element);
    tr.appendChild(td);
    // комментарий
    td = elementCreate('td', {class: 'nested'});
    element = elementCreate('textarea', {
      placeholder: "Введите комментарий",
      maxlength: "256",
      rows: "1",
      name: "correction_comment"
    });
    td.appendChild(element);
    tr.appendChild(td);
    // сумма
    td = elementCreate('td', {class: 'sum nested'});
    element = elementCreate('input', {
      class: "right",
      type: "number",
      required: "required",
      placeholder: "Введите сумму",
      name: "correction_sum",
      step: "0.01"
    });
    td.appendChild(element);
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    form.appendChild(table);
    element = elementCreate('input', {type: "hidden", name: "lot", value: keyLot});
    form.appendChild(element);
    element = elementCreate('input', {type: "hidden", name: "cmd", value: "correction_add"});
    form.appendChild(element);
    return form;
  };

  /**
   * Получить платёж
   * @param pay Данные платежа
   * @param table Таблица, в которуб будут добавлен платёж
   * @param keyLot integer Номер заказа
   * @param keyPay integer Номер платежа
   * @private
   */
  this._getPay = function (pay, table, keyLot, keyPay) {
    var tr, td, element;
    if (pay.error) {
      tr = elementCreate('tr', {class: 'explanation ' + pay.status});
      // кнопка
      td = elementCreate('td');
      element = elementCreate('button', {
        class: "editor error-del",
        title: "Удалить у оплаты отметку, о его ошибочности",
        name: keyLot + '-' + keyPay
      });
      element.appendChild(elementCreate('div', {class: "glyph error-del"}));
      element.appendChild(elementCreate('span', {}, " Удалить отметку"));
      td.appendChild(element);
      tr.appendChild(td);
      // Сообщение
      td = elementCreate('td', {colspan: "3"}, 'Оплата отмечена как ошибочная');
      tr.appendChild(td);
      table.tBodies[0].appendChild(tr);
    } else {
      if (pay.filling) {
        tr = elementCreate('tr', {class: "sms filling " + pay.filling_sms.status});
        // кнопка
        td = elementCreate('td');
        element = elementCreate('button', {
          class: "editor",
          title: "Удалить проставленную оплату",
          name: keyLot + '-' + keyPay
        });
        if (pay.lost_pay) {
          classie.add(element, 'lost-pay-del');
        } else {
          classie.add(element, 'pay-del');
        }
        element.appendChild(elementCreate('div', {class: "glyph pay-del"}));
        element.appendChild(elementCreate('span', {}, " Удалить оплату"));
        td.appendChild(element);
        tr.appendChild(td);
        // Время оплаты
        td = elementCreate('td', {class: "time"});
        element = elementCreate('span', {title: "Время оплаты, полученное из SMS"}, pay.filling_sms.time);
        td.appendChild(element);
        tr.appendChild(td);
        // Информация о СМС
        td = elementCreate('td', {class: "nested"});
        td.appendChild(this._getTableSmsInfo(pay.filling_sms));
        tr.appendChild(td);
        // Сумма оплаты
        td = elementCreate('td', {class: "sum right"});
        element = elementCreate('span', {title: "Сумма оплаты, получанная из SMS"}, pay.filling_sms[SMS_SUM_PAY] + ' \u20BD');
        td.appendChild(element);
        if (parseFloat(pay.filling_sms.diff_sum_plain) != 0) {
          element = elementCreate('span', {title: "Разница между суммой указанной участником и полученной из SMS"});
          element.appendChild(elementCreate('sup', {}, ' ' + pay.filling_sms.diff_sum + ' \u20BD'));
          td.children[0].appendChild(element);
        }
        tr.appendChild(td);
        table.tBodies[0].appendChild(tr);
      } else {
        tr = elementCreate('tr', {class: 'explanation ' + pay.status});
        // кнопки
        td = elementCreate('td');
        // кнопка найти
        element = this._getSearchButton(keyLot, keyPay);
        td.appendChild(element);
        // Кнопка ошибочная оплата
        element = elementCreate('button', {
          class: "editor error-set",
          title: "Отметить оплату как ошибочную",
          name: keyLot + '-' + keyPay
        });
        element.appendChild(elementCreate('div', {class: "glyph error-set"}));
        element.appendChild(elementCreate('span', {}, " Отметить как ошибочный"));
        td.appendChild(element);
        tr.appendChild(td);
        td = elementCreate('td', {colspan: "3"}, 'SMS для оплаты не найдена');
        tr.appendChild(td);
        table.tBodies[0].appendChild(tr);
      }
    }
  };

  /**
   * Кнопка для поиска
   * @param {integer} keyLot Номер лота
   * @param keyPay integer Номер платежа
   * @private
   */
  this._getSearchButton = function (keyLot, keyPay) {
    var form = elementCreate('form', {class: "inline", action: "purchase/search", method: "post"});
    var element;
    form.appendChild(elementCreate('input', {type: "hidden", name: "lot", value: keyLot}));
    form.appendChild(elementCreate('input', {type: "hidden", name: "pay", value: keyPay}));
    element = elementCreate('button', {
      class: "editor search",
      title: "Найти SMS для оплаты вручную",
      type: 'submit'
    });
    element.appendChild(elementCreate('div', {class: "glyph search"}));
    element.appendChild(elementCreate('span', {}, " Найти SMS"));
    form.appendChild(element);
    return form;
  };

  /**
   * Получить потерянный заказ
   * @param keyLot Номер заказа
   * @param lot Данные для вывода заказа
   * returns {Element}
   */
  this.getLostLot = function (keyLot, lot) {
    var lotElement = this._getLotDescription(keyLot, lot);
    var pays = lotElement.getElementsByClassName('pays');
    // Оплаты
    pays[0] = this._getPays(keyLot, lot, pays[0], this._getPay);
    lotElement.appendChild(pays[0]);
    return lotElement;
  };

  /**
   * Вывести фильтр для редактора закупок
   * @param arg string Выбранный фильтр
   * @param filters array Список фильтров
   */
  this.editorFilter = function (arg, filters) {
    var select = document.getElementById('editor-filter');
    var element;
    // Сброс данных
    select.options.length = 0;
    // Отрисовка
    for (keyFilter in filters) {
      if (filters.hasOwnProperty(keyFilter)) {
        element = elementCreate('option', {value: keyFilter}, filters[keyFilter]);
        if (keyFilter == arg) {
          element.setAttribute('selected', 'selected');
        }
        select.appendChild(element);
      }
    }
  };

  /**
   * Вывод общей статистики по закупке
   * @param data Данные со общей статистикой закупки
   */
  this.editorStatistic = function (data) {
    var table = document.getElementById('editor-statistic');
    var tr, td;
    // Очистка данных
    table.tBodies[0].innerHTML = '';
    // Участников
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Участников');
    tr.appendChild(td);
    td = elementCreate('td', {class: "right"}, data.count_active_lots);
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Заказов
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Заказов');
    tr.appendChild(td);
    td = elementCreate('td', {class: "right"}, data.count_active_orders);
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Общая сумма
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Общая сумма');
    tr.appendChild(td);
    td = elementCreate('td', {class: "right"}, data.count_total_money + ' \u20BD');
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Денег сдано
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Денег сдано');
    tr.appendChild(td);
    td = elementCreate('td', {class: "right"}, data.count_total_put_money + ' \u20BD');
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Найдено «Разносилкой»
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Найдено «Разносилкой»');
    tr.appendChild(td);
    td = elementCreate('td', {class: "right"}, data.count_total_found_money + ' \u20BD');
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
  };

  /**
   * Вывод страницы с редактором закупок
   * @param data Данные для вывода страницы с редактором закупок
   * @private
   */
  this._editor = function (data) {
    var page = document.getElementById('editor-page'); // Область вывода редактора
    var lots = document.getElementById('lots-wrapper'); // Область для вывода заказов
    var lostLots = document.getElementById('lost-lots-wrapper'); // Область для вывода потерянных оплат
    var label = document.getElementById('no-pay'); // надпись о том, что все заказы скрыты
    var lot, keyLot;
    this.editorFilter(data.editor.view, data.editor.filters);
    // Вывод заказов
    if (data.editor.lots.length === 0) {
      // Если в закупке нет заказаов
      lots.innerText = 'В закупке нет ни одного заказа.';
      classie.add(lots, 'bold');
    } else {
      // Если закупка с заказами
      data.editor.display_label ? classie.remove(label, 'hide') : classie.add(label, 'hide');
      for (keyLot in data.editor.lots) {
        // Вывод закупок
        if (data.editor.lots.hasOwnProperty(keyLot)) {
          lot = this.getLot(keyLot, data.editor.lots[keyLot], this._getPay);
          lots.appendChild(lot);
        }
      }

    }
    // Вывод потерянны оплат
    if (data.editor.lost_lots.length !== 0) {
      lostLots.appendChild(elementCreate('h2', {}, 'Потерянные оплаты'));
      // Вывод закупок
      for (keyLot in data.editor.lost_lots) {
        if (data.editor.lost_lots.hasOwnProperty(keyLot)) {
          lot = this.getLostLot(keyLot, data.editor.lost_lots[keyLot]);
          lostLots.appendChild(lot);
        }
      }
    }
    this.editorStatistic(data.editor.statistic);
    initEditorButtons();
    classie.remove(page, 'hide');
    classie.add(this._status, 'hide');
  };

  /**
   * Вывести страницу редактора закупок
   * @param response Данные для вывода редактора закупок
   * @param error boolean Ошибка при получении данных
   */
  this.view = function (response, error) {
    // console.log(response);
    if (!error) {
      if (response.info.error === ERROR_NONE) {
        if (typeof pageData !== 'undefined') {
          // Если данные получены не из кэша
          if (!response.info.cache) {
            pageData = response;
          }
          // Выводим редактор закупок
          this._editor(pageData);
        }
        return;
      }
    }
    this._viewError(response.info.error);
  };
}

/**
 * Класс для вывода "Анализатора"
 * @constructor
 */
function Analysis() {
  Purchase.call(this); // extends

  /**
   * Получить итоговую таблицу по заказу
   * @param keyLot int Номер заказа
   * @param lot Данные о заказе
   * @returns {Element} Итоговая таблица по заказу
   */
  this._getReport = function (keyLot, lot) {
    var table = elementCreate('table', {class: 'zebra report'});
    var tr, td;
    table.appendChild(elementCreate('tbody'));
    // Сумма к внесению участником
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Сумма к внесению участником');
    tr.appendChild(td);
    td = elementCreate('td', {class: "sum right"}, lot.total + ' \u20BD');
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Найдено раньше
    tr = elementCreate('tr');
    td = elementCreate('td', {}, 'Найдено раньше');
    tr.appendChild(td);
    td = elementCreate('td', {class: "sum right"}, lot.total_found + ' \u20BD');
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    // Итого найдено
    tr = elementCreate('tr');
    td = elementCreate('td', {class: "bold"}, 'Итого найдено');
    tr.appendChild(td);
    td = elementCreate('td', {class: "sum right"});
    td.appendChild(elementCreate('span', {class: "pre-found " + lot.class_total_pre_found}, lot.total_pre_found + ' \u20BD'));
    tr.appendChild(td);
    table.tBodies[0].appendChild(tr);
    return table;
  };

  /**
   * Получить платёж
   * @param pay Данные платежа
   * @param table Таблица, в которуб будут добавлен платёж
   * @param keyLot integer Номер заказа
   * @param keyPay integer Номер платежа
   */
  this._getPay = function (pay, table, keyLot, keyPay) {
    var tr, td;
    if (pay.error) {
      tr = elementCreate('tr', {class: 'explanation ' + pay.status});
      td = elementCreate('td', {colspan: "4"}, 'Оплата отмечена как ошибочная');
      tr.appendChild(td);
      table.tBodies[0].appendChild(tr);
    } else {
      if (pay.filling) {
        tr = elementCreate('tr', {class: 'explanation ' + pay.status});
        td = elementCreate('td', {colspan: "4"});
        td.appendChild(this._getFillingSms(pay.filling_sms));
        tr.appendChild(td);
        table.tBodies[0].appendChild(tr);
      } else {
        if (pay.has_sms) {
          for (var keySms in pay.sms) {
            if (pay.sms.hasOwnProperty(keySms)) {
              // СМС
              tr = elementCreate('tr', {
                selectable: "true",
                class: 'sms ' + pay.sms[keySms].status
              });
              tr = this._getSms(keyLot, keyPay, keySms, pay.sms[keySms], tr);
              table.tBodies[0].appendChild(tr);
              // Нет подходящей
              tr = elementCreate('tr', {
                selectable: "true",
                class: 'no-sms explanation ' + pay.sms[keySms].status
              });
              tr = this._getNoSms(keyLot, keyPay, tr);
              table.tBodies[0].appendChild(tr);
            }
          }
        } else {
          tr = elementCreate('tr', {class: 'explanation ' + pay.status});
          td = elementCreate('td', {colspan: "4"}, 'SMS для оплаты не найдена');
          tr.appendChild(td);
          table.tBodies[0].appendChild(tr);
        }
      }
    }
  };

  /**
   * Получить СМС которой проставлен платёж
   * @param sms Информация о СМС
   * @returns {Element} Блок с СМС которой проставлен платёж
   */
  this._getFillingSms = function (sms) {
    var fillingSms = elementCreate('div', {class: 'filling-sms'});
    var content = elementCreate('div', {class: 'filling-sms-wrapper'});
    var table = elementCreate('table', {class: "zebra"});
    var tr, td, element;
    element = elementCreate('p', {}, 'Информация о SMS, которой проставлена оплата:');
    content.appendChild(element);
    // Таблица
    table.appendChild(elementCreate('tbody'));
    tr = elementCreate('tr', {class: "sms filling " + sms.status});
    // Время оплаты
    td = elementCreate('td', {class: "time"});
    element = elementCreate('span', {title: "Время оплаты, полученное из SMS"}, sms.time);
    td.appendChild(element);
    tr.appendChild(td);
    // Информация о СМС
    tr = this._getSmsInfo(sms, tr);
    // Сумма оплаты
    if (sms[SMS_SUM_PAY]) {
      td = elementCreate('td', {class: "sum right"});
      element = elementCreate('span', {title: "Сумма оплаты, получанная из SMS"}, sms[SMS_SUM_PAY] + ' \u20BD');
      td.appendChild(element);
      tr.appendChild(td);
    }
    table.tBodies[0].appendChild(tr);
    // Спойлер
    content.appendChild(table);
    fillingSms.appendChild(window.putInSpoiler('Оплата уже проставлена', content));
    return fillingSms;
  };

  /**
   * Получить tr с вариантом выбора если в списке нет подходящей SMS
   * @param keyLot int Номер заказа
   * @param keyPay int Номер платежа
   * @param tr Element tr элемент в который необходимо добавить вариант выбора если в списке нет подходящей SMS
   * @returns {Element}
   */
  this._getNoSms = function (keyLot, keyPay, tr) {
    var element, td;
    // Элемент выбора
    td = elementCreate('td');
    element = elementCreate('span', {class: "button"});
    td.appendChild(element);
    element = elementCreate('input', {
      update: "true",
      type: "radio",
      required: 'required',
      value: 'none',
      name: keyLot + '-' + keyPay
    });
    td.children[0].appendChild(element);
    element = elementCreate('span', {}, "Не выбирать ни одной SMS");
    td.children[0].appendChild(element);
    tr.appendChild(td);
    // Описание
    td = elementCreate('td', {colspan: "3"}, 'В списке нет подходящей SMS');
    tr.appendChild(td);
    return tr;
  };

  /**
   * Получить СМС
   * @param keyLot Номер заказа
   * @param keyPay Номер платежа
   * @param keySms Номер СМС
   * @param sms Данные о СМС
   * @param tr Element Элемент в который будет добавлена СМС
   * @returns {Element}
   */
  this._getSms = function (keyLot, keyPay, keySms, sms, tr) {
    var element, td;
    // Элемент выбора
    td = elementCreate('td');
    element = elementCreate('span', {class: "button"});
    td.appendChild(element);
    element = elementCreate('input', {
      update: "true",
      type: "radio",
      required: 'required',
      value: keySms,
      name: keyLot + '-' + keyPay
    });
    if (sms.checked) {
      element.setAttribute(sms.checked, '');
    }
    td.children[0].appendChild(element);
    element = elementCreate('span', {}, "Выбрать данную SMS");
    td.children[0].appendChild(element);
    tr.appendChild(td);
    // Время оплаты
    td = elementCreate('td', {class: "time"});
    element = elementCreate('span', {title: "Время оплаты, полученное из SMS"}, sms.time);
    td.appendChild(element);
    tr.appendChild(td);
    // Информация о СМС
    td = elementCreate('td', {class: "nested"});
    td.appendChild(this._getTableSmsInfo(sms));
    tr.appendChild(td);
    // Сумма оплаты
    td = elementCreate('td', {class: 'sum right'});
    element = elementCreate('span', {title: "Сумма оплаты, получанная из SMS"}, sms[SMS_SUM_PAY] + ' \u20BD');
    td.appendChild(element);
    tr.appendChild(td);
    return tr;
  };

  /**
   * Вывод страницы с автоматическим поиском СМС
   * @param data Данные для вывода страницы с автоматическим поиском СМС
   */
  this._analysis = function (data) {
    var page = document.getElementById('analysis-page'); // Область вывода анализатора
    var lots = document.getElementById('lots-wrapper'); // Область для вывода заказов
    var countFoundPays = document.getElementById('count-found-pays'); // Вывод количества найденных платежей
    var label = document.getElementById('no-pay'); // надпись о том, что все оплаты скрыты
    var lot;
    if (data.analysis.lots.length === 0) {
      // Если в закупке нет заказаов
      page.innerText = 'В закупке нет ни одного заказа.';
    } else {
      // Если закупка с заказами
      countFoundPays.innerText = data.analysis.count_found_pays + ' шт.';
      data.analysis.display_label ? classie.remove(label, 'hide') : classie.add(label, 'hide');
      // Вывод закупок
      for (var keyLot in data.analysis.lots) {
        if (data.analysis.lots.hasOwnProperty(keyLot)) {
          lot = this.getLot(keyLot, data.analysis.lots[keyLot], this._getPay);
          lots.appendChild(lot);
        }
      }
      // Инициализация скриптов
      initFilterAnalysis();
      initUpdatesSum(data.sum);
      initSelectable();
    }
    classie.remove(page, 'hide');
    classie.add(this._status, 'hide');
  };

  /**
   * Вывести страницу автоматического поиска СМС
   * @param response Данные для вывода страницы автоматического поиска СМС
   * @param error boolean Ошибка при получении данных
   */
  this.view = function (response, error) {
    if (!error) {
      if (response.info.error === ERROR_NONE) {
        // Выводим заказы, платежи и найденные к ним СМС
        this._analysis(response);
        return;
      }
    }
    this._viewError(response.info.error);
  };
}