/**
 * Настройки
 */

var REQUEST_INTERVAL = 250; // Пауза между запросами к сайту СП, мс

/**
 * Модуль автопроставления оплат
 */
(function () {
  var fillingProcess = false;
  var fillingTotal = 0;

  /**
   * Инициализация проставления оплат по таймеру при загрузке страницы.
   * Для работы необходимы элементы div с name=lot и id=lot-№Лота содержащий другие элементы необходимые для работы
   * функции fillingPay(), элемент progress с id=progress-bar, элемент содержащий текст прогресса с id=progress-text,
   * а так же объекты содержащие информацию о процессе автопроставления с id=start-filling и id=stop-filling.
   */
  window.addEventListener("load", function () {
    var lotsDiv = document.getElementsByClassName('lot');
    var progressBar = document.getElementById('progress-bar');
    var progressText = document.getElementById('progress-text');
    var start = document.getElementById('start-filling');
    var stop = document.getElementById('stop-filling');
    var lots = [], pos = 0;
    // Получение массива с заказами которые нужно проставить
    if (lotsDiv.length > 0) {
      for (var x = 0; x < lotsDiv.length; x++) {
        if (!classie.has(lotsDiv[x], 'normal')) {
          lots.push(parseInt(lotsDiv[x].id.split('-')[1]));
        }
      }
    }
    // Проверка готовности расширения если запрос к сайту СП через расширение
    if (USER_REQUEST === REQUEST_EXTENSIONS) {
      // Если расширение не готово к работе, вывести причину
      readyExtensionCheck(function () {
      }, function () {
      }, true);
    }
    // Инициализация прогресс-бара
    if (progressBar) {
      progressBar.value = 0;
      progressBar.max = lots.length;
      progressText.innerHTML = '0 / ' + lots.length;
    }
    // Инициализация таймера
    if (lots.length > 0) {
      fillingProcess = true;
      stop.style.display = 'none';
      start.style.display = '';
      // timerId = setInterval(timerIntervalFilling, REQUEST_INTERVAL);
      setTimeout(timerFilling, REQUEST_INTERVAL);
    }

    /**
     * Проставления оплат по таймеру
     */
    function timerFilling() {
      // Если оплаты для проставления ещё есть
      if (pos < lots.length) {
        // Проставление оплаты
        fillingPay(lots[pos], false, function () {
          progressBar.value = progressBar.value + 1;
          progressText.innerHTML = (pos + 1) + ' / ' + lots.length;
          pos++;
          setTimeout(timerFilling, REQUEST_INTERVAL);
        });
      } else {
        // Если это последняя оплата для проставления
        fillingProcess = false;
        stop.style.display = '';
        start.style.display = 'none';
        // Запуск проверки общей найденной суммы
        setTimeout(checkFillingSum, REQUEST_INTERVAL);
      }
    }

  }, false);

  /**
   * Блокирует выход со страницы пока идёт автопроставление оплат
   */
  window.addEventListener("beforeunload", function (event) {
    // Если процесс проставления активен
    if (fillingProcess) {
      var
        message = "Процесс автопроставления оплат ещё не окончен.";
      if (typeof event == "undefined") {
        event = window.event;
      }
      if (event) {
        event.returnValue = message;
      }
      return message;
    }
  }, false);

  /**
   * Обработчики для кнопок "Попробовать снова" и "Проставить вручную".
   * Для работы необходимо у соотвествующих кнопок поставить аттрибуты filling-again="true" или filling-manual="true"
   */
  window.addEventListener("load", function () {
    var lots = document.getElementsByClassName('lot');
    var lot, buttons;
    if (lots.length) {
      // Перебрать все лоты
      for (var x = 0; x < lots.length; x++) {
        // Получить номер лота
        lot = lots[x].id.split('-')[1];
        // Получить все кнопки
        buttons = lots[x].getElementsByTagName('button');
        if (buttons.length) {
          for (var y = 0; y < buttons.length; y++) {
            // Если это кнопка "Попробовать снова"
            if (buttons[y].getAttribute('filling-again')) {
              buttons[y].addEventListener("click", fillingPay.bind(null, lot, false), false);
            }
            // Если это кнопка "Проставить вручную"
            if (buttons[y].getAttribute('filling-manual')) {
              buttons[y].addEventListener("click", fillingPay.bind(null, lot, true), false);
            }
          }
        }
      }
    }
  }, false);

  /**
   * Установить обработчик для кнопки сверки сумм.
   * Для работы необходим элемент с id=check-filling-sum.
   */
  window.addEventListener("load", function () {
    var buttonSpan = document.getElementById('check-filling-sum');
    if (buttonSpan) {
      buttonSpan.addEventListener("click", checkFillingSum, false);
    }
  }, false);

  /**
   * Проставление оплаты для режима "Автоматического проставления оплат"
   * @param lot int Номер заказа.
   * @param manual bool Ручное (true) или автоматическое (null|false) проставление оплат, не обзяательный параметр.
   * @param callback Function Функция обратного вызова, не обязательный параметр.
   */
  function fillingPay(lot, manual, callback) {
    var request = getXmlHttpRequest();
    var lotDiv = document.getElementById('lot-' + lot);
    var spinner = lotDiv.getElementsByClassName('lot-overlay')[0];
    var noFilling = lotDiv.getElementsByClassName('no-filling')[0];
    var total = document.getElementById('total-found-money');
    var noLot = document.getElementById('no-lot');
    // Проверка наличия команд для запросов
    if (typeof requestData === "undefined") {
      responseError();
      return;
    }
    // Контроллер запросов
    switch (USER_REQUEST) {
      // Запрос к сайту СП через расширение
      case REQUEST_EXTENSIONS:
        if (manual) {
          serverRequest();
        } else {
          responseWaiting();
          readyExtensionCheck(extensionRequest, extensionError);
        }
        break;
      // Запрос к сайту СП через сервер по умолчанию
      default :
        serverRequest();
        break;
    }

    /**
     * Запрос через расширение
     */
    function extensionRequest() {
      chrome.runtime.sendMessage(EXTENSION_ID, requestData.filling[lot].auto, function (response) {
        response !== 'false' ? responseSuccess(response) : responseError();
        sendCallback();
      });
    }

    /**
     * Расширение не готово к работе
     */
    function extensionError() {
      responseError();
      sendCallback();
    }

    /**
     * Запрос на сервер
     */
    function serverRequest() {
      request.onreadystatechange = function () {
        // Ожидание ответа
        if (request.readyState == 1) {
          responseWaiting();
        }
        // Ответ получен
        if (request.readyState == 4) {
          if (request.status == 200) {
            // Успех или ошибка
            request.responseText != 'false' ? responseSuccess(request.responseText) : responseError();
          } else {
            // Ошибка
            responseError();
          }
          sendCallback();
        }
      };

      request.open("POST", COMMAND_URL, true);
      request.setRequestHeader("Content-Charset", "UTF-8");
      request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      // Отсылка данных, ручное или автоматическое проставление оплат
      manual ? request.send(requestData.filling[lot].manual) : request.send(requestData.filling[lot].auto);
    }

    /**
     * Запустить функцию обратного вызова
     */
    function sendCallback() {
      // Если функция обратного вызова задана
      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Действия во время ожидания ответа
     */
    function responseWaiting() {
      spinner.style.display = '';
    }

    /**
     * Действия при положительном ответе
     */
    function responseSuccess(response) {
      classie.remove(lotDiv, 'error');
      classie.remove(lotDiv, 'warning');
      classie.add(lotDiv, 'normal');
      noFilling.style.display = 'none';
      spinner.style.display = 'none';
      if (!manual) {
        lotDiv.style.display = 'none';
        // Проверка, остались ли видимые блоки с заказами
        if (!checkVisibleLotDiv()) {
          noLot.style.display = '';
        }
      }
      // Получение общей найденной суммы
      fillingTotal = parseFloat(response);
      total.innerHTML = numberFormat(fillingTotal, 2, ',') + ' \u20BD';
    }

    /**
     * Действия при ошибке
     */
    function responseError() {
      classie.remove(lotDiv, 'warning');
      classie.add(lotDiv, 'error');
      noFilling.style.display = '';
      spinner.style.display = 'none';
    }
  }

  /**
   * Проверка, есть ли видимые блоки с заказами
   * @returns {boolean} true если есть видимые блоки с заказами, false если все блоки скрыты
   */
  function checkVisibleLotDiv() {
    var lotsDiv = document.getElementsByClassName('lot');
    // Получение массива с заказами которые нужно проставить
    if (lotsDiv.length > 0) {
      for (var x = 0; x < lotsDiv.length; x++) {
        if (lotsDiv[x].style.display == '') {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Сравнивает найденную сервисом сумму с той, которая фактически внесена на сайт СП.
   * Для работы необходим элемент с id=total-found-money
   */
  function checkFillingSum() {
    var request = getXmlHttpRequest();
    var totalSpan = document.getElementById('total-found-money');
    // Проверка наличия команд для запросов
    if (typeof requestData === "undefined") {
      setUnknown();
      return;
    }
    // Контроллер запросов
    switch (USER_REQUEST) {
      // Запрос к сайту СП через расширение
      case REQUEST_EXTENSIONS:
        setUnknown();
        // Дополнить команду
        requestData.checkTotal.cmdService += (fillingTotal + '&body=');
        readyExtensionCheck(extensionRequest, setUnknown);
        break;
      // Запрос к сайту СП через сервер по умолчанию
      default :
        serverRequest();
        break;
    }

    /**
     * Запрос через расширение
     */
    function extensionRequest() {
      chrome.runtime.sendMessage(EXTENSION_ID, requestData.checkTotal, function (response) {
        (response == 'true') ? setSuccess() : setError();
      });
    }

    /**
     * Запрос через сервер
     */
    function serverRequest() {
      request.onreadystatechange = function () {
        if (request.readyState == 1) {
          // Проверка
          setUnknown();
        }
        if (request.readyState == 4) {
          if (request.status == 200) {
            if (request.responseText == 'true') {
              // Сумма совпадает
              setSuccess();
            } else {
              // Сумма не совпадает
              setError();
            }
          } else {
            // Не удалось сравнить суммы
            setUnknown();
          }
        }
      };

      request.open("POST", COMMAND_URL, true);
      request.setRequestHeader("Content-Charset", "UTF-8");
      request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      request.send(requestData.checkTotal + fillingTotal);
    }

    /**
     * Установить класс error'
     */
    function setError() {
      classie.remove(totalSpan, 'success');
      classie.remove(totalSpan, 'unknown');
      classie.add(totalSpan, 'error');
    }

    /**
     * Установить класс success'
     */
    function setSuccess() {
      classie.remove(totalSpan, 'error');
      classie.remove(totalSpan, 'unknown');
      classie.add(totalSpan, 'success');
    }

    /**
     * Установить класс unknown'
     */
    function setUnknown() {
      classie.remove(totalSpan, 'success');
      classie.remove(totalSpan, 'error');
      classie.add(totalSpan, 'unknown');
    }
  }

})();