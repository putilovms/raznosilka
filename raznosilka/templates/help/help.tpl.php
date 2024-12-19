<!-- start help.tpl.php -->

<h1>Руководство пользователя</h1>

<div class="help">
  <ul>

    <li>
      <b>Общая информация</b>
      <ul>
        <li>
          <a href="<?= URL::to('help/system_req') ?>">Системные требования</a>
        </li>
        <li>
          <a href="<?= URL::to('info/sp') ?>">Список сайтов СП</a>
        </li>
        <li>
          <a href="<?= URL::to('help/first_steps') ?>">Первые шаги</a>
        </li>
        <li>
          <a href="<?= URL::to('help/support') ?>">Как написать службе поддержки?</a>
        </li>
        <li>
          <a href="<?= URL::to('help/home') ?>">Описание главной страницы сервиса</a>
        </li>
      </ul>
    </li>

    <li>
      <b>Создание аккаунта</b>
      <ul>
        <li>
          <a href="<?= URL::to('help/register') ?>">Регистрация аккаунта</a>
        </li>
        <li>
          <a href="<?= URL::to('help/activation') ?>">Активация аккаунта</a>
        </li>
      </ul>
    </li>

    <li>
      <b>Расширение</b>
      <ul>
        <li>
          <a href="<?= URL::to('help/extension') ?>">Установка или включение расширения</a>
        </li>
        <li>
          <a href="<?= URL::to('help/extension_use') ?>">Работа с расширением</a>
        </li>
      </ul>
    </li>

    <li>
      <b>Пробный период и оплата</b>
      <ul>
        <li>
          <a href="<?= URL::to('help/gift') ?>">Получение пробного периода</a>
        </li>
        <li>
          <a href="<?= URL::to('help/paying') ?>">Оплата услуги</a>
        </li>
      </ul>
    </li>

    <li>
      <b>Работа с SMS</b>
      <ul>
        <li>
          <a href="<?= URL::to('help/import_sms') ?>">Выгрузка SMS из телефона</a>
          <ul>
            <li>
              <a href="<?= URL::to('help/import_android') ?>">Android</a>
            </li>
            <li>
              <a href="<?= URL::to('help/import_iphone') ?>">iPhone</a>
            </li>
          </ul>
        </li>
        <li>
          <a href="<?= URL::to('help/upload_sms') ?>">Загрузка SMS в «Разносилку»</a>
          <ul>
            <li>
              <a href="<?= URL::to('help/upload_pc') ?>">Загрузка SMS с компьютера</a>
            </li>
            <li>
              <a href="<?= URL::to('help/upload_phone') ?>">Загрузка SMS с телефона</a>
            </li>
          </ul>
        </li>
        <li>
          <a href="<?= URL::to('help/return_sms') ?>">Возврат SMS</a>
        </li>
      </ul>
    </li>

    <li>
      <b>Работа с закупками</b>
      <ul>
        <li>
          <a href="<?= URL::to('help/select_purchase') ?>">Выбор закупки</a>
        </li>
        <li>
          <a href="<?= URL::to('help/filling_pay') ?>">Проставление оплат</a>
          <ul>
            <li>
              <a href="<?= URL::to('help/auto_filling') ?>">Автопроставление</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#manual') ?>">Ручное проставление</a>
            </li>
          </ul>
        </li>
        <li>
          <a href="<?= URL::to('help/purchase') ?>">Обзор закупки</a>
          <ul>
            <li>
              <a href="<?= URL::to('help/purchase#update') ?>">Обновление данных о закупке</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#filter') ?>">Фильтрация заказов</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#manual') ?>">Ручное проставление оплаты</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#pay_del') ?>">Удалить проставленную оплату</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#error') ?>">Отметить платёж как ошибочный</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#error_del') ?>">Удалить отметку об ошибочном платеже</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#edit') ?>">Ручное изменение суммы</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#edit_del') ?>">Удаление изменений суммы внесённых вручную</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#sum_update') ?>">Проставить найденную «Разносилкой» сумму на сайт СП</a>
            </li>
            <li>
              <a href="<?= URL::to('help/purchase#report') ?>">Просмотреть отчёт о закупке</a>
            </li>
          </ul>
        </li>
      </ul>
    </li>

  </ul>
</div>

<!-- end help.tpl.php -->