/**
 * Настройки
 */

var COMMAND_URL = '../command'; // Raznosilka API

/**
 * Константы
 */

var REQUEST_EXTENSIONS = 2; // Запрос к сайту СП при помощи расширения браузера
// Ошибки
var ERROR_NONE = 0; // Нет ошибок
var ERROR_OTHER = 1; // Все остальные ошибки
var ERROR_ACCESS = 2; // Нет доступа к выбранной странице
var ERROR_PAGE = 3; // Не удалось получить страницу
var ERROR_DATA = 4; // Не удалось получить данные
var PURCHASE_NOT_SELECT = 5; // Закупка не выбрана
var ERROR_EXTENSION = 6; // Нет доступа к расширению (не установлено или отключено)
var ERROR_AUTHORIZATION = 7; // Нет авторизации на сайте СП
var ERROR_ORG_ID = 8; // Неверный ID организатора

/**
 * Расширение
 */

/**
 * Отрправить информацию о состоянии пользователя расширению Chrome
 */
(function () {
  window.addEventListener("load", function () {
    if (typeof chrome !== 'undefined') {
      chrome.runtime.sendMessage(EXTENSION_ID, {typeRequest: 'userInfo', userAuth: USER_AUTH});
    }
  }, false);
})();

/**
 * Проверить установлено ли расширение, и если не установлено, то вывести сообщение с предупреждением,
 * о том что расширение не установлено.
 */
(function () {
  window.addEventListener("load", function () {
    // Сообщение, если не установлено расширение
    if (USER_REQUEST === REQUEST_EXTENSIONS) {
      messageExtension();
    }
  }, false);

  /**
   * Сообщение, если расширение не установлено или выключено
   */
  function messageExtension() {
    // Доступность Crome API
    if (typeof chrome !== 'undefined') {
      existExtension(exist);
    } else {
      viewError(ERROR_EXTENSION);
      initInstallButton();
    }

    /**
     * Проверка наличия установленного и включенного расширения
     * @param response Ответ true если расширение доступно
     */
    function exist(response) {
      if (response !== true) {
        // Расширение не установлено или выключено
        viewError(ERROR_EXTENSION);
        initInstallButton();
      }
    }

    /**
     * Вывод сообщения, о том что расширение не установлено
     */
    function initInstallButton() {
      var installButton = document.getElementById('install-button');
      installButton.addEventListener("click", function () {
        chrome.webstore.install(getExtensionUrl(), function () {
          location.reload();
        });
      }, false);
    }

    /**
     * Получить url для установки расширения
     */
    function getExtensionUrl() {
      var link = document.getElementsByTagName('link');
      if (link.length > 0) {
        for (var i = 1; i < link.length; i++) {
          if (link[i].rel == 'chrome-webstore-item') {
            return link[i].href;
          }
        }
      }
    }

  }
})();

/**
 * Запрос к расширению, для определения его наличия
 * @param callback Функция обратного вызова
 */
function existExtension(callback) {
  chrome.runtime.sendMessage(EXTENSION_ID, {typeRequest: 'existExtension'}, callback);
}

/**
 * Сервис
 */

/**
 * Вывод ошибки
 * @param type int Тип ошибки
 */
function viewError(type) {
  switch (type) {
    // Все остальные ошибки
    case ERROR_OTHER :
      viewNotify('Ошибка: что-то пошло не так...', 'error');
      break;
    // Нет доступа к выбранной странице
    case ERROR_ACCESS :
      viewNotify('Ошибка: ваша учётная запись для сайта СП не имеет доступа к организаторской.', 'error');
      break;
    // Не удалось получить страницу
    case ERROR_PAGE :
      viewNotify('Ошибка: не удалось получить страницу с сайта СП.', 'error');
      break;
    // Не удалось получить данные
    case ERROR_DATA :
      viewNotify('Ошибка: не удалось получить получить необходимые данные.', 'error');
      break;
    // Закупка не выбрана
    case PURCHASE_NOT_SELECT :
      viewNotify('Ошибка: закупка не выбрана.', 'error');
      break;
    // Расширение не найдено
    case ERROR_EXTENSION :
      viewNotify('Ошибка: расширение не установлено или выключено.<br><button id="install-button">Установить расширение</button><br><span class="hint"><a href="/help/extension">Как установить или включить расширение?</a></span>', 'error');
      break;
    // Нет авторизации на сайте СП
    case ERROR_AUTHORIZATION :
      viewNotify('Ошибка: нет аутентификации на сайте СП. Пожалуйста, войдите на сайт СП под своим логином и паролем.', 'error');
      break;
    // Неверный ID организатора
    case ERROR_ORG_ID :
      viewNotify('Ошибка: неверный ID организатора. Пожалуйста зайдите на сайт СП под учётными данными того организатора, учётные данные которого были использованы для первого входа в «Разносилку».', 'error');
      break;
    default :
      return;
  }
}

/**
 * Вывод уведомлений
 * @param text string Текст уведомления (может быть HTML)
 * @param type string Тип уведомления, может быть: success, error, info
 */
function viewNotify(text, type) {
  var notifyWrapper = document.getElementById('notify-wrapper');
  var notify = document.createElement('div');
  var p = document.createElement('p');
  notify.className = 'notify ' + type;
  p.innerHTML = text;
  notify.appendChild(p);
  prefixedEvent(notify, "AnimationEnd", notificationEvent);
  notifyWrapper.appendChild(notify);
}

/**
 * Отображает текущее значение value элемента input[range] при его изменении (диапазон поиска).
 * Для работы необходим input типа range с id=fork и элемент в котором будет выводиться value с id=fork-out.
 */
(function () {
  window.addEventListener("load", function () {
    var
      fork = document.getElementById("fork"),
      forkOut = document.getElementById("fork-out");

    if (fork) {
      fork.addEventListener("input", inputChange, false);
      fork.addEventListener("change", inputChange, false);
      function inputChange() {
        if (forkOut) {
          forkOut.innerHTML = fork.value;
        }
      }
    }
  }, false);
})();

/**
 * При выборе какого-либо пункта меню из select с id=type, изменят содержание подменю в select с id=subtype.
 * Для работы необходим select с id=type, select с id=subtype, массив значений для подменю содержащийся в var = subType[]
 */
(function () {
  window.addEventListener("load", function () {
    var
      selectType = document.getElementById('type'),
      selectSubType = document.getElementById('subtype');

    if (selectType && selectSubType) {
      selectType.addEventListener("change", function () {
        if (typeof subType !== 'undefined') {
          var list = subType[selectType.value];

          selectSubType.options.length = 0;
          // Вставляем новые элементы
          for (var i in list) {
            selectSubType.options[selectSubType.options.length] = new Option(list[i], i);
          }
        }
      }, false);
    }
  }, false);
})();

/**
 * Фмльтрует содержание таблицы с id=log, в зависимости от значения input[text] с id=filter
 * Для работы необходима таблица с id=log и input типа text с id=filter
 */
(function () {
  window.addEventListener("load", function () {
    var
      inputFilter = document.getElementById('filter'),
      table = document.getElementById('log');

    if (inputFilter && table) {
      inputFilter.addEventListener("keyup", function () {
        var
          words = inputFilter.value.toLowerCase().split(" "),
          displayStyle = 'none',
          match;
        for (var row = 1; row < table.rows.length; row++) {
          match = table.rows[row].innerHTML.replace(/<[^>]+>/g, "");
          for (var i = 0; i < words.length; i++) {
            if (match.toLowerCase().indexOf(words[i]) >= 0)
              displayStyle = '';
            else {
              displayStyle = 'none';
              break;
            }
          }
          table.rows[row].style.display = displayStyle;
        }
      }, false);
    }
  }, false);
})();

/**
 * Выделяет все checkbox или снимает выделение со всех checkbox в форме с name=control при нажатии на checkbox с id=select-all,
 * а так же делает неактивным элемент выбора действий select с id=select-action.
 * Для работы необходим input[checkbox] с id=select-all, select с id=select-action, form с name=control содержащая элементы input[checkbox]
 */
(function () {
  window.addEventListener("load", function () {
    var
      form = document.forms['control'],
      checkbox = document.getElementById('select-all'),
      selectAction = document.getElementById('select-action');

    if (form && checkbox && selectAction) {
      // Установить обработчик на чекбокс "выбрать всё"
      checkbox.addEventListener("click", function () {
        for (var i = 0; i < form.elements.length; i++) {
          if (form.elements[i].type == 'checkbox') {
            form.elements[i].checked = checkbox.checked;
          }
        }
        selectAction.disabled = !checkbox.checked;
      }, false);

      // Установить обработчик на все чекбоксы в форме
      for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].type == 'checkbox') {
          form.elements[i].addEventListener("click", clickCheckbox, false);
        }
      }

      /**
       * Проверяет отмеченные чекбоксы в форме, если отмечен хотя бы один, то делаете активным элемент выбора действий,
       * если выбраны все, то отмечает чекбокс отвечающего за выделение всех чекбоксов.
       */
      function clickCheckbox() {
        var
          oneEnabled = false,
          allEnabled = true;

        for (var i = 0; i < form.elements.length; i++) {
          if (form.elements[i].type == 'checkbox') {
            if (form.elements[i] != checkbox) {
              if (form.elements[i].checked) {
                oneEnabled = true;
              } else {
                allEnabled = false;
              }
            }
          }
          // Деактивация/активация элемента выбора действий
          selectAction.disabled = !oneEnabled;
          // Отмечает чекбокс отвечающего за выделение всех чекбоксов
          checkbox.checked = allEnabled;
        }
      }

    }
  }, false);
})();

/**
 * Инициализация selectable при загрузке страницы
 */
(function () {
  window.addEventListener("load", initSelectable, false);
})();

/**
 * Выбирает radio или checkbox при нажатии на tr траблицы.
 * Для работы необходимо установить аттрибут selectable="true" у tr элемента таблицы, а так же иметь
 * внутри tr элемента вложенный input[radio|checkbox]
 */
function initSelectable() {
  var
    trs = document.getElementsByTagName('tr');

  if (trs.length) {
    for (var x = 0; x < trs.length; x++) {
      if (trs[x].getAttribute('selectable')) {
        trs[x].addEventListener("click", inputChange, false);
      }
    }
  }

  function inputChange(event) {
    event = event || window.event;
    var
      target = event.target || event.srcElement,
      inputs = this.getElementsByTagName('input');
    for (var x = 0; x < inputs.length; x++) {
      if (inputs[x].type == 'radio' || inputs[x].type == 'checkbox') {
        if (target.tagName !== 'A' && inputs[x] !== target) {
          inputs[x].click();
        }
      }
    }
  }

}

/**
 * Выполняет submit в форме для выбранного действия в select (который не расположен в форме).
 * Для работы необходима форма с name=control, в форме должен находиться input с id=hidden-action и type=hidden,
 * элемент select с id=select-action расположенный в любом месте.
 */
(function () {
  window.addEventListener("load", function () {
    var
      form = document.forms['control'],
      selectAction = document.getElementById('select-action'),
      action = document.getElementById('hidden-action');

    if (form && selectAction && action) {
      selectAction.addEventListener("change", function () {
        action.value = selectAction.value;
        form.submit();
      }, false);
    }
  }, false);
})();

/**
 * Выполняет submit в форме для выбранного действия в select (расположен в форме), подходит для нескольких select.
 * Для работы необходима форма, в форме должен находиться select с class=select-auto
 */
(function () {
  window.addEventListener("load", function () {
    var
      selectAction = document.getElementsByClassName('select-auto');

    if (selectAction) {
      for (var i = 0; i < selectAction.length; i++) {
        var form = selectAction[i].parentElement;
        while (form) {
          if (form.tagName === 'FORM') {
            selectAction[i].addEventListener("change", function () {
              form.submit();
            }, false);
            break;
          }
          form = form.parentElement;
        }
      }
    }
  }, false);
})();

/**
 * Фильтрует заказы "Автопоиска", содержащиеся в контейнере lot, в зависимости от наличия класса, выбранного в меню select,
 * а так же выводит или скрывает пояснение в случае если оплат соответствующих фильтру не найдено.
 * Для работы необходим select с id=analysis-filter, контейнеры lot, label с id=no-pay.
 */
(function () {
  window.addEventListener("load", initFilterAnalysis, false);
})();

/**
 * Инициализация фильтра для "Автопоиска"
 */
function initFilterAnalysis() {
  var selectFilter = document.getElementById('analysis-filter');
  var label = document.getElementById('no-pay');
  var lots = document.getElementsByClassName('lot');
  if (selectFilter && label && lots.length) {
    selectFilter.addEventListener("change", function () {
      var displayLabel = true;
      // Запустить фильтр
      for (var i = 0; i < lots.length; i++) {
        if (this.value != '') {
          // Если фильтр задан
          if (classie.has(lots[i], this.value)) {
            // Если класс заказа совпал с фильтром - скрыть
            classie.add(lots[i], 'hide');
          } else {
            // Если класс заказа не совпал с фильтром - значит есть заказы к выводу
            classie.remove(lots[i], 'hide');
            displayLabel = false;
          }
        } else {
          // Если фильтр не задан - показать всё
          classie.remove(lots[i], 'hide');
          displayLabel = false;
        }
      }
      // Показать или скрыть надпись о том, что все оплаты скрыты
      displayLabel ? classie.remove(label, 'hide') : classie.add(label, 'hide');
      // Что выводить в надписи
      switch (this.value) {
        case 'not-found':
          label.innerHTML = '<p><b>Заказов с найденными оплатами нет</b></p>';
          break;
        case 'normal':
          label.innerHTML = '<p><b>Заказов с оплатами требующих вашего вмешательства нет</b></p>';
          break
      }
    }, false);
  }
}

/**
 * Фильтрует заказы "Редактора закупок", содержащиеся в контейнере lot, в зависимости от наличия класса, выбранного в меню select,
 * а так же выводит или скрывает пояснение в случае если оплат соответствующих фильтру не найдено.
 * Для работы необходим select с id=editor-filter, контейнеры lot, label с id=no-pay.
 */
(function () {
  window.addEventListener("load", initFilterEditor, false);
})();

/**
 * Инициализация фильтра для "Редактора закупок"
 */
function initFilterEditor() {
  var selectFilter = document.getElementById('editor-filter');
  var label = document.getElementById('no-pay');
  var lotsWrapper = document.getElementById('lots-wrapper');
  if (selectFilter && label && lotsWrapper) {
    selectFilter.addEventListener("change", function () {
      var lots = lotsWrapper.getElementsByClassName('lot');
      var displayLabel = true;
      // Запустить фильтр
      for (var i = 0; i < lots.length; i++) {
        if (classie.has(lots[i], this.value)) {
          // Если класс заказа совпал с фильтром - значит есть заказы к выводу
          classie.remove(lots[i], 'hide');
          displayLabel = false;
        } else {
          // Если класс заказа не совпал с фильтром - скрыть
          classie.add(lots[i], 'hide');
        }
      }
      // Показать или скрыть надпись о том, что все оплаты скрыты
      displayLabel ? classie.remove(label, 'hide') : classie.add(label, 'hide');
      // замена URL
      updateUrl('view', this.value);
      // Обновление данных по закупке
      pageData.editor.view = this.value;
    }, false);
  }
}

/**
 * Обновить URL адрес, после выбора фильтра
 * @param name string Имя параметра
 * @param value string Значение параметра
 */
function updateUrl(name, value) {
  var href = document.location.href;
  var regexp = new RegExp(name + '=[^&]+', 'i');
  history.replaceState(null, null, href.replace(regexp, name + '=' + value));
}

/**
 * Инициализация обновления итоговой суммы для лота в "Автопоиске" при загрузке страницы.
 */
(function () {
  window.addEventListener("load", function () {
    if ((typeof pageData !== "undefined") && (typeof pageData.sum !== "undefined")) {
      initUpdatesSum(pageData.sum);
    }
  }, false);
})();

/**
 * Обновляет итоговую сумму для лота в "Автопоиске", после того как пользователь выберет input с СМС или input с отказом
 * от предложенных СМС.
 * Для работы необходим массив sum содержащий иформацию об оплатах и найденных СМС, div содержащий информацию о лоте
 * с id=lot-№Лота включающий в себя следующие элементы:
 * - div с аттрибутом name=pre-found, для отображения итоговой суммы;
 * - элементы div с аттрибутом name=pay содержащие информацию о платеже и найденные СМС для данного платежа,
 * включающие в себя следующие элементы:
 *  - input[radio] с аттрибутом update="true" и аттрибутом name=№Лота-№Платежа;
 */
function initUpdatesSum(sum) {
  var inputs = document.getElementsByTagName('input');

  if (inputs.length) {
    for (var x = 0; x < inputs.length; x++) {
      if (inputs[x].getAttribute('update')) {
        inputs[x].addEventListener("click", inputChange, false);
      }
    }
  }

  function inputChange() {
    var lot = this.name.toString().split('-')[0];
    var div = document.getElementById('lot-' + lot);
    var label = div.getElementsByClassName('pre-found')[0];
    var pays = div.getElementsByClassName('pay');
    var preSelectSmsSum = 0.00;
    var totalFoundSum, total, keyPay, keySms, inputs;
    // Перебираем оплаты
    for (var i = 0; i < pays.length; i++) {
      if (pays[i].nodeName == 'DIV') {
        // Получаем инпуты выбора СМС
        inputs = pays[i].getElementsByTagName('input');
        for (var x = 0; x < inputs.length; x++) {
          if (inputs[x].type == 'radio') {
            if (inputs[x].checked) {
              // Получаем ID лота, оплаты и СМС
              keyPay = inputs[x].name.split('-')[1];
              keySms = inputs[x].value;
              // Получаем суммы выбранных СМС
              if (keySms != 'none') {
                preSelectSmsSum += parseFloat(sum['lots'][lot]['pays'][keyPay]['sms'][keySms]['sms_sum']);
              }
            }
          }
        }
      }
    }
    total = parseFloat(sum['lots'][lot]['total']);
    totalFoundSum = parseFloat(sum['lots'][lot]['total_found']);
    label.innerText = numberFormat((totalFoundSum + preSelectSmsSum), 2, ',') + ' \u20BD';
    if (Math.round(total) != Math.round(totalFoundSum + preSelectSmsSum)) {
      classie.remove(label, 'normal');
      classie.add(label, 'error');
    } else {
      classie.remove(label, 'error');
      classie.add(label, 'normal');
    }
  }

}

/**
 * Инициализация бокового меню.
 * Для работы необходим элемент содержащий меню с id=sidebar-menu, кнопка меню с id=trigger
 */
(function () {
  window.addEventListener("load", loadMenu, false);

  function loadMenu() {
    var
      menu = document.getElementById('sidebar-menu'),
      trigger = document.getElementById('trigger'),
      overlay = document.createElement('div'),
      content = menu.parentNode,
      eventType = mobileCheck() ? 'touchstart' : 'click'; // Тип события, если мобильный, то использовать touchstart вместо click

    overlay.className = 'overlay';
    content.insertBefore(overlay, menu);

    trigger.addEventListener(eventType, function (event) {
      event.stopPropagation();
      event.preventDefault();
      if (classie.has(menu, 'menu-open')) {
        // Если меню открыто - закрыть
        resetMenu();
      } else {
        // Если меню закрыто - открыть
        openMenu();
      }
    }, false);

    /**
     * Открыть меню
     */
    function openMenu() {
      classie.remove(menu, 'menu-close');
      classie.add(menu, 'menu-open');
      classie.remove(overlay, 'menu-close');
      classie.add(overlay, 'menu-open');
      classie.add(document.body, 'disable-scroll');
      overlay.addEventListener(eventType, function () {
        // Закрыть меню по клику на overlay
        resetMenu();
        overlay.removeEventListener(eventType, closeClickFn);
      }, false);
    }

    /**
     * Закрыть меню
     */
    function resetMenu() {
      classie.remove(menu, 'menu-open');
      classie.add(menu, 'menu-close');
      classie.remove(overlay, 'menu-open');
      classie.add(overlay, 'menu-close');
      classie.remove(document.body, 'disable-scroll');
    }
  }
})();

/**
 * Спойлер.
 * Для работы необходим жлемент dl с аттрибутом class=details, со вложенным элементом dt.
 */
(function () {
  window.addEventListener("load", function () {
    var
      details = document.getElementsByTagName("dl"),
      i = details.length;

    // Установить обработчики на click для спойлера
    while (i--) {
      if (details[i].className == "details") {
        details[i].getElementsByTagName('dt')[0].addEventListener("click", clickDetails, false);
      }
    }
  }, false);
})();

/**
 * Открывает и закрывает спойлер
 */
function clickDetails() {
  var dl;
  dl = this.parentElement;
  if (classie.has(dl, 'open')) {
    classie.remove(dl, 'open');
  } else {
    classie.add(dl, 'open');
  }
}

/**
 * Прилипающий блок с информацией об оплате
 * Для работы небходим объект с id=sticky
 */
(function () {
  window.addEventListener("load", function () {
    var
      objToSticky = document.getElementById('sticky'),
      topOfObjToSticky = null;

    if (objToSticky) {
      topOfObjToSticky = getOffset(objToSticky).top;
      window.addEventListener("scroll", stickyScroll, false);
    }

    function stickyScroll() {
      var
        scrolled = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || window.scrollY;

      if (topOfObjToSticky != null) {
        if (scrolled > topOfObjToSticky) { // Если прокрутили больше, чем расстояние до блока, то приклеиваем его
          classie.add(objToSticky, 'sticky');
        } else {
          classie.remove(objToSticky, 'sticky');
        }
      }
    }

  }, false);
})();

/**
 * Первичный scroll
 */
(function () {
  window.addEventListener("load", function () {
    window.scrollBy(0, 1);
  }, false);
})();

/**
 * Скрывает верхнее меню при прокручивании вниз и показывает при прокручивании вверх.
 * Для работы необходим элемент с id=header.
 */
(function () {
  window.addEventListener("load", function () {
    var
      menu = document.getElementById('header'),
      menuHeight = menu.clientHeight,
      preScrolled = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || window.scrollY;

    window.addEventListener("scroll", function () {
      var
        scrolled = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || window.scrollY;

      if (scrolled > menuHeight) {
        // Перемотка вниз
        if (scrolled > (preScrolled + menuHeight)) {
          if (!classie.has(menu, 'hide')) {
            classie.add(menu, 'hide');
          }
          preScrolled = scrolled;
        }
        // Перемотка вверх
        if (scrolled < (preScrolled - menuHeight)) {
          if (classie.has(menu, 'hide')) {
            classie.remove(menu, 'hide');
          }
          preScrolled = scrolled;
        }
      } else {
        if (classie.has(menu, 'hide')) {
          classie.remove(menu, 'hide');
        }
        preScrolled = scrolled;
      }
    }, false);

  }, false);
})();

/**
 * Кнопка "Вверх"
 * Для работы необходим элемент с id=up-down
 */
(function () {
  window.addEventListener("load", function () {
    var upDownElement = document.getElementById('up-down');
    var yTmp = 0; // Положение экрана до нажатия кнопки "Вверх"

    upDownElement.addEventListener("click", upDownClick, false);
    window.addEventListener("scroll", upDownScroll, false);

    /**
     * Повяление и смена иконки кнопки "Вверх" при скролле
     */
    function upDownScroll() {
      var scrolled = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || window.scrollY;
      var innerHeight = document.documentElement.clientHeight;

      switch (upDownElement.className) {
        case '':
          if (scrolled > innerHeight) {
            upDownElement.className = 'up';
          }
          break;
        case 'up':
          if (scrolled < innerHeight) {
            upDownElement.className = '';
          }
          break;
        case 'down':
          if (scrolled > innerHeight) {
            upDownElement.className = 'up';
          }
          break;
      }
    }

    /**
     * Нажатие на кнопку "Вверх"
     */
    function upDownClick() {
      var y = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || window.scrollY;
      switch (this.className) {
        case 'up':
          yTmp = y;
          window.scrollTo(0, 0);
          this.className = 'down';
          break;
        case 'down':
          window.scrollTo(0, yTmp);
          this.className = 'up';
          break;
      }
    }

  }, false);
})();

/**
 * Устанавливает обработчик для уже выведенных уведомлений
 */
(function () {
  window.addEventListener("load", function () {
    var notifyWrapper = document.getElementById('notify-wrapper');
    if (notifyWrapper) {
      var notify = notifyWrapper.getElementsByClassName('notify');
      for (var i = 0; i < notify.length; i++) {
        prefixedEvent(notify[i], "AnimationEnd", notificationEvent);
      }
    }
  }, false);
})();

/**
 * Обработчик для самоуничтожения уведомлений
 */
function notificationEvent() {
  this.parentNode.removeChild(this);
}

/**
 * Вспомогательные функции
 */

/**
 * Преобразует данные из формы в данные для запроса через URL
 * @param form Element Элемент формы из которой необходимо преобразовать данные
 * @returns {string} Строка с URL query
 */
function serializeForm(form) {
  var fields = form.elements;
  var field, name, value, type;
  var res = '';
  for (var z = 0; z < fields.length; z++) {
    field = fields[z];
    name = field.name;
    value = field.value;
    type = field.type;
    if (typeof name == "undefined" || name == "") {
      continue;
    }
    if (type == 'checkbox' || type == 'radio') {
      if (field.checked) {
        res += name + "=" + encodeURIComponent(value) + "&";
      }
      continue;
    }
    if (type == "select-multiple") {
      for (var so = 0; so < field.length; so++) {
        if (field[so].selected) {
          res += name + "=" + encodeURIComponent(field[so].value) + "&";
        }
      }
      continue;
    }
    res += name + "=" + encodeURIComponent(value) + "&";
  }
  return res.replace(/&*$/g, "");
}

/**
 * Кроссбраузерное создание объекта XMLHttpRequest
 * @returns {*} Возвращает объект XMLHttpRequest
 */
function getXmlHttpRequest() {
  if (window.XMLHttpRequest) {
    try {
      return new XMLHttpRequest();
    }
    catch (e) {
    }
  } else {
    if (window.ActiveXObject) {
      try {
        return new ActiveXObject('Msxml2.XMLHTTP');
      }
      catch (e) {
      }
      try {
        return new ActiveXObject('Microsoft.XMLHTTP');
      }
      catch (e) {
      }
    }
  }
  return null;
}

/**
 * classie - class helper functions
 * from bonzo https://github.com/ded/bonzo
 */
(function (window) {
  'use strict';

  function classReg(className) {
    return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
  }

  // classList support for class management
  // altho to_query be fair, the api sucks because it won't accept multiple classes at once
  var hasClass, addClass, removeClass;

  if ('classList' in document.documentElement) {
    hasClass = function (elem, c) {
      return elem.classList.contains(c);
    };
    addClass = function (elem, c) {
      elem.classList.add(c);
    };
    removeClass = function (elem, c) {
      elem.classList.remove(c);
    };
  }
  else {
    hasClass = function (elem, c) {
      return classReg(c).test(elem.className);
    };
    addClass = function (elem, c) {
      if (!hasClass(elem, c)) {
        elem.className = elem.className + ' ' + c;
      }
    };
    removeClass = function (elem, c) {
      elem.className = elem.className.replace(classReg(c), ' ');
    };
  }

  function toggleClass(elem, c) {
    var fn = hasClass(elem, c) ? removeClass : addClass;
    fn(elem, c);
  }

  var classie = {
    // full names
    hasClass: hasClass,
    addClass: addClass,
    removeClass: removeClass,
    toggleClass: toggleClass,
    // short names
    has: hasClass,
    add: addClass,
    remove: removeClass,
    toggle: toggleClass
  };

  // transport
  if (typeof define === 'function' && define.amd) {
    // AMD
    define(classie);
  } else {
    // browser global
    window.classie = classie;
  }

})(window);

/**
 * Определяет мобильное устройство
 * @returns {boolean} Возвращает true, если скрипт запущен на мобильном устройстве
 */
function mobileCheck() {
  var check = false;
  (function (a) {
    if (/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4)))
      check = true
  })(navigator.userAgent || navigator.vendor || window.opera);
  return check;
}

/**
 * Форматирует число согласно условиям
 * @param number float Число
 * @param decimals int Количество знаков после запятой
 * @param dec_point string Разделитель дробной части
 * @returns {string} Форматированное число
 */
function numberFormat(number, decimals, dec_point) {
  var
    numberFormat = Math.round(number * Math.pow(10, decimals)).toString(),
    length;
  while ((length = numberFormat.length) < (decimals + 1)) {
    numberFormat = '0' + numberFormat;
  }
  numberFormat = numberFormat.substring(0, length - decimals) + dec_point + numberFormat.substring(length - decimals);
  return numberFormat
}

/**
 * Получить координаты объекта относительно страницы одним из способов
 * @param elem элемент координаты которого необходимо получить
 * @returns {{top: number, left: number}} Координаты
 */
function getOffset(elem) {
  if (elem.getBoundingClientRect) {
    return getOffsetRect(elem)
  } else {
    return getOffsetSum(elem)
  }

  /**
   * Получить координаты объекта относительно страницы при помощи суммирования offset элементов
   * @param elem Элемент, координаты которого необходимо узнать
   * @returns {{top: number, left: number}}
   */
  function getOffsetSum(elem) {
    var top = 0, left = 0;
    while (elem) {
      top = top + parseInt(elem.offsetTop);
      left = left + parseInt(elem.offsetLeft);
      elem = elem.offsetParent;
    }
    return {top: top, left: left};
  }

  /**
   * Получить координаты объекта относительно страницы при помощи getBoundingClientRect
   * @param elem Элемент, координаты которого необходимо узнать
   * @returns {{top: number, left: number}}
   */
  function getOffsetRect(elem) {
    var box = elem.getBoundingClientRect();

    var body = document.body;
    var docElem = document.documentElement;

    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;

    var clientTop = docElem.clientTop || body.clientTop || 0;
    var clientLeft = docElem.clientLeft || body.clientLeft || 0;

    var top = box.top + scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;

    return {top: Math.round(top), left: Math.round(left)};
  }
}

/**
 * Создание объекта с заданными свойствами
 * @param name string Название тэга нового объекта
 * @param attrs object Аттрибуты объекта
 * @param text string Текст в объекте
 * @param style object Стили объекта
 * @returns {Element}
 */
function elementCreate(name, attrs, text, style) {
  var key, e = document.createElement(name);
  if (attrs) {
    for (key in attrs) {
      if (attrs.hasOwnProperty(key)) {
        if (key == 'class') {
          e.className = attrs[key];
        } else if (key == 'id') {
          e.id = attrs[key];
        } else {
          e.setAttribute(key, attrs[key]);
        }
      }
    }
  }
  if (style) {
    for (key in style) {
      if (style.hasOwnProperty(key)) {
        e.style[key] = style[key];
      }
    }
  }
  if (text) {
    e.appendChild(document.createTextNode(text));
  }
  return e;
}

/**
 * Проверить пуст ли массив (для разряженного массива)
 * @param arr array Разряженный массив для проверки
 * @returns {boolean} Результат проверки
 */
function emptyArray(arr) {
  for (var i = 0; i < arr.length; i++) {
    if (arr[i] !== undefined) {
      return false;
    }
  }
  return true;
}

/**
 * Кроссбраузерное назнечение обработчика события
 * @param element Елемент для которого назначается обработчик
 * @param type Тип события
 * @param callback Функция обратного вызова при наступлении события
 */
function prefixedEvent(element, type, callback) {
  var pfx = ["webkit", "moz", "MS", "o", ""];
  for (var p = 0; p < pfx.length; p++) {
    if (!pfx[p]) type = type.toLowerCase();
    element.addEventListener(pfx[p] + type, callback, false);
  }
}