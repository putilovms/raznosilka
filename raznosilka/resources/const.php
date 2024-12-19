<?
/**
 * Проект Raznosilka
 * @author Михаил Сергеевич Путилов
 * <@package Raznosilka\const.php>
 * @copyright © М. С. Путилов, 2015
 */

define('VERSION_RAZNOSILKA', '2.15'); // Версия "Разносилки"

// Константы для именования полей БД.
// * - все ID общие

// Таблица - users
define('USER_ID', 'user_id'); // * ID пользователя "Разносилки"
define('USER_LOGIN', 'user_login'); // Логин пользователя "Разносилки"
define('USER_EMAIL', 'user_email'); // E-mail пользователя "Разносилки"
define('USER_PASSWORD', 'user_password'); // Пароль пользователя "Разносилки"
define('USER_SP_LOGIN', 'user_sp_login'); // Логин для доступа к сайту СП
define('USER_SP_PASSWORD', 'user_sp_password'); // Пароль для доступа к сайту СП
define('USER_ORG_ID', 'user_org_id'); // ID организатора на выбранном сайте СП
define('USER_REMINDING', 'user_reminding'); // Согласие пользователя на рассылку напоминаний
define('USER_FILLING_DAY', 'user_filling_day'); // Через сколько дней напоминать
define('USER_REG_DATE', 'user_reg_date'); // Дата регистрации пользователя в "Разносилке"
define('USER_ACTIVATE', 'user_activate'); // Активация аккаунта
define('USER_TMP_EMAIL', 'user_tmp_email'); // Временное хранилище для нового е-майла
define('USER_SESSION_ID', 'user_session_id'); // Уникальный ID сессии
define('USER_LAST_TIME', 'user_last_time'); // Время последнего входа пользователя
define('USER_GIFT', 'user_gift'); // Бесплатный месяц для нового пользователя
define('USER_BLOCKED', 'user_blocked'); // Заблокирован ли пользователь
define('USER_TIME_ZONE', 'user_tz'); // Временная зона пользователя
define('USER_REQUEST', 'user_request'); // Тип запроса к сайту СП

// Таблица - sp
define('SP_ID', 'sp_id'); // * ID сайта СП
define('SP_SITE_NAME', 'sp_site_name'); // Короткое название сайта СП
define('SP_SITE_URL', 'sp_site_url'); // URL сайта СП
define('SP_FILLING_DAY', 'sp_filling_day'); // Количество дней на проставление оплат по правилам сайта СП
define('SP_DESCRIPTION', 'sp_full_name'); // Описание сайта СП
define('SP_TIME_ZONE', 'sp_tz'); // Временная зона сайта СП
define('SP_REQUEST', 'sp_request'); // Тип запроса к сайту СП
define('SP_ACTIVE', 'sp_active'); // Доступен ли для выбора данный сайт СП

// Таблица - sms
define('SMS_ID', 'id_sms'); // * ID СМС
define('SMS_TIME_SMS', 'sms_time'); // Время получения СМС
define('SMS_TIME_PAY', 'sms_time_pay'); // Время поступления платежа
define('SMS_SUM_PAY', 'sms_sum'); // Сумма платежа
define('SMS_CARD_PAYER', 'sms_card_payer'); // Номер карты участника
define('SMS_FIO', 'sms_fio'); // ФИО плательщика
define('SMS_COMMENT', 'sms_comment'); // Комментарий в СМС
define('SMS_RETURN', 'sms_return'); // Возвращена ли СМС

// Таблица - pay
define('PAY_ID', 'id_pay'); // * ID платежа
define('PAY_TIME', 'pay_time'); // Время платежа указанное участником
define('PAY_SUM', 'pay_sum'); // Сумма указанная участником
define('PAY_CARD_PAYER', 'pay_card_payer'); // карта указанная участником в платеже
define('PAY_CREATED', 'pay_created'); // Время создания отчёта о платеже на сайте СП

// Таблица - purchase
define('PURCHASE_ID', 'purchase_id'); // * ID закупки
define('PURCHASE_NAME', 'purchase_name'); // Название закупки
define('PURCHASE_PAY_TO', 'purchase_pay_to'); // Дата до которой пользователь должен оплатить заказ

// Таблица - users_purchase
define('USER_PURCHASE_ID', 'user_purchase_id'); // * ID участника закупки
define('USER_PURCHASE_NAME', 'user_purchase_name'); // ФИО участника закупки
define('USER_PURCHASE_NICK', 'user_purchase_nick'); // Ник участника закупки

// Таблица - correction
define('CORRECTION_ID', 'correction_id'); // ID корректировки
define('CORRECTION_SUM', 'correction_sum'); // Сумма корректировки
define('CORRECTION_COMMENT', 'correction_comment'); // Комментарий к корректировке

// Таблица - sms_unknown
define('SMS_UNKNOWN_ID', 'sms_unknown_id'); // ID неопознанной смс
define('SMS_UNKNOWN_TIME', 'sms_unknown_time'); // Время получения неопознанной смс
define('SMS_UNKNOWN_TEXT', 'sms_unknown_text'); // Тело неопознанной смс
define('SMS_UNKNOWN_ADD', 'sms_unknown_add'); // Время добавления смс в базу данных
define('SMS_UNKNOWN_NEW', 'sms_unknown_new'); // Новая ли это СМС

// Таблица - messages
define('MESSAGE_ID', 'message_id'); // ID сообщения
define('MESSAGE_DATE', 'message_date'); // Дата сообщения
define('MESSAGE_NEW', 'message_new'); // Новое сообщение или нет
define('MESSAGE_TEXT', 'message_text'); // Текст сообщения
define('MESSAGE_TYPE', 'message_type'); // Тип сообщения

// Таблица - delivery
define('DELIVERY_ID', 'delivery_id'); // ID сообщения для рассылки
define('DELIVERY_EMAIL', 'delivery_email'); // Е-мейл для рассылки
define('DELIVERY_SUBJECT', 'delivery_subject'); // Новое сообщение или нет
define('DELIVERY_BODY', 'delivery_body'); // Текст сообщения

// Таблица - settings
define('SETTINGS_NAME', 'settings_name'); // имя настройки
define('SETTINGS_VALUE', 'settings_value'); // значение настройки

// Таблица - orders
define('ORDER_ID', 'order_id'); // ID заказа
define('ORDER_TYPE', 'order_type'); // тип заказа
define('ORDER_DAY', 'order_day'); // Количество дней заказа
define('ORDER_ADD', 'order_add'); // Дата добавления заказа
define('ORDER_RUN', 'order_run'); // Дата запуска (активации) услуги
define('ORDER_DONE', 'order_done'); // Дата завершения (исполнения) услуги
define('ORDER_RETURN', 'order_return'); // Дата возврата денег за услугу
define('PAYMENT_ID', 'payment_id'); // ID платежа

// Таблица - yandex_kassa
define('INVOICE_ID', 'invoiceId'); // Уникальный номер транзакции в сервисе Яндекс.Денег
define('CUSTOMER_NUMBER', 'customerNumber'); // Идентификатор плательщика (присланный в платежной форме) на стороне магазина
define('PAYMENT_DATETIME', 'paymentDatetime'); // Момент регистрации оплаты заказа в Яндекс.Деньгах
define('ORDER_CREATED_DATETIME', 'orderCreatedDatetime'); // Момент регистрации заказа в сервисе Яндекс.Денег
define('SHOP_ARTICLE_ID', 'shopArticleId'); // Идентификатор товара, выдается Яндекс.Деньгами
define('ORDER_SUM_AMOUNT', 'orderSumAmount'); // Стоимость заказа
define('SHOP_SUM_AMOUNT', 'shopSumAmount'); // Сумма к выплате на счет магазина (стоимость заказа минус комиссия Яндекс.Денег)
define('PAYMENT_TYPE', 'paymentType'); // Способ оплаты заказа

//Таблица - template
define('TPL_ID', 'tpl_id'); // ID шаблона
define('TPL_TYPE', 'tpl_type'); // тип шаблона
define('TPL_SUBTYPE', 'tpl_subtype'); // Подтип шаблона
define('TPL_TEMPLATE', 'tpl_template'); // шаблон
define('TPL_DESCRIPTION', 'tpl_description'); // описание шаблона
define('TPL_ACTIVE', 'tpl_active'); // активен ли шаблон
define('TPL_COUNT_USED', 'tpl_count_used'); // количество использования шаблона
define('TPL_LAST_USED', 'tpl_last_used'); // дата последнего использования шаблона

# Системные константы

// Расширения
define('URL_EXTENSION', 'https://chrome.google.com/webstore/detail/raznosilka/hfbimiebhidpdcgghcafhphflmcciamc?hl=ru'); // URL расширения для "Разносилки"

// Соль
define('SALT', '5b128a4d03e056958a5e59271515913e');

// Данные для оплат
define('MONTH_COST', 500); // Стоимость одного месяца
define('DAY_GIFT', 30); // Количество дней в подарок
define('DAY_NOTIFY_PAY', 5); // За сколько дней до истечения услуги, можно напомнить о продлении услуги

// Роли пользователей Разносилки
define('ADMIN', 1); // Администратор
define('GUEST', 2); // Не авторизированный пользователь
define('USER_AUTH', 3); // Авторизированный пользователь
define('USER_PAY', 4); // Оплаченный пользователь
define('USER_BIND', 5); // Пользователь имеет OrgID
define('USER_NOT_BIND', 6); // Пользователь не имеет OrgID
define('USER_HAVE_LOGIN_SP', 7); // Пользователь имеет логин и проль от сайта СП
define('NO_ACTIVATE', 8); // Неактивированный пользователь
define('ACTIVATE', 9); // Активированный пользователь
define('NOT_BLOCKED', 10); // Пользователь не заблокирован
define('USER_CURL', 11); // Запрос к сайту СП при помощи CURL через сервер сервиса
define('USER_EXTENSIONS', 12); // Запрос к сайту СП при расширения браузера, через компьютер пользователя

// Типы уведомлений
define('SUCCESS_NOTIFY', 'success'); // уведомлений об удачном завершении операции
define('ERROR_NOTIFY', 'error'); // уведомлений об ошибке
define('INFO_NOTIFY', 'info'); // уведомлений с оповещением или информацией

// Типы сообщений
define('SUCCESS_MESSAGE', 0); // сообщений об удачном завершении операции (переделать под что-то полезное)
define('INFO_MESSAGE', 1); // информационное сообщение
define('WARNING_MESSAGE', 2); // предупреждение
define('MONEY_MESSAGE', 3); // сообщений о финансовых операциях

// Статусы заказов, платежей и СМС
define('INACTIVE', 0); // Не активный или отмеченный как ошибочный платёж.
define('NORMAL', 1); // Не требуется вмешательства пользователя.
define('WARNING', 2); // Требуется вмешательство пользователя.
define('ERROR', 3); // Имеется СМС с сообщением.

// Коды ошибок
define('ERROR_NONE', 0); // Нет ошибок
define('ERROR_OTHER', 1); // Все остальные ошибки
define('ERROR_ACCESS', 2); // Нет доступа к выбранной странице
define('ERROR_PAGE', 3); // Не удалось получить страницу
define('ERROR_DATA', 4); // Не удалось получить данные
define('PURCHASE_NOT_SELECT', 5); // Закупка не выбрана
define('ERROR_EXTENSION', 6); // Нет доступа к расширению (не установлено или отключено) [Только для расширения]
define('ERROR_AUTHORIZATION', 7); // Нет авторизации на сайте СП [Только для расширения]
define('ERROR_ORG_ID', 8); // Неверный ID организатора

// Типы заказов
define('ORDER_GIFT', 1); // Услуга в подарок (первый месяц)
define('ORDER_ADMIN', 2); // Услуга добавлена администратором
define('ORDER_YANDEX_KASSA', 3); // Услуга куплена через Яндекс.Кассу

// Типы шаблонов
define('TPL_USEFUL', 1); // шаблоны содержащие данные
define('TPL_USELESS', 2); // шаблоны не содержащие данных
define('TPL_MARK_START', 3); // шаблоны для определения начала склеенных SMS
define('TPL_MARK_END', 4); // шаблоны для определения концов склеенных SMS

// Типы запросов к сайту СП
define('REQUEST_CURL', 1); // Запрос к сайту СП при помощи CURL через сервер сервиса
define('REQUEST_EXTENSIONS', 2); // Запрос к сайту СП при расширения браузера, через компьютер пользователя

// Опции для настройки пейджера
define('PAGER_PAGE', 1); // Номер текущей страницы
define('PAGER_BUTTON_COUNT', 2); // Количество выводимых кнопок (по умолчанию 2, т.е. n*2+1=5 кнопок будет выведено)
define('PAGER_URL', 3); // URL страницы для которой генерируется пейджер

// Вывод через JS
define('PAGE_DATA_JS', 'pageData'); // Имя массива для запроса к сайту СП для получения данных для вывода страницы или готовые данные для вывода страницы через JS
define('REQUEST_DATA_JS', 'requestData'); // Имя массива с запросами
define('USER_REQUEST_JS', 'USER_REQUEST'); // Имя константы содержащий выбранный пользователем тип запросов к сайту СП
define('USER_AUTH_JS', 'USER_AUTH'); // Имя константы содержащий статус авторизарованности ползьвателя в сервисе
define('EXTENSION_ID_JS', 'EXTENSION_ID'); // Имя константы содержащий ID расширения Google Chrome