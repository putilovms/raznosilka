<!-- start new.tpl.php -->

<? // var_dump($var['var']['data']) ?>

<? if (!empty($var['var']['data'])) : ?>

  <h1><?= $var['var']['data']['user'][USER_LOGIN] ?></h1>

  <table class="zebra settings">
    <tr>
      <th>
        Логин
      </th>
      <td>
        <?= $var['var']['data']['user'][USER_LOGIN] ?>
      </td>
    </tr>
    <tr>
      <th>
        ID
      </th>
      <td>
        #<?= $var['var']['data']['user'][USER_ID] ?>
      </td>
    </tr>
    <tr>
      <th>
        E-mail
      </th>
      <td>
        <?= $var['var']['data']['user'][USER_EMAIL] ?>
      </td>
    </tr>
    <tr>
      <th>
        Дата регистрации
      </th>
      <td>
        <?= $var['var']['data']['user'][USER_REG_DATE] ?>
      </td>
    </tr>
    <tr>
      <th>
        Последний вход
      </th>
      <td>
        <?= $var['var']['data']['user'][USER_LAST_TIME] ?>
      </td>
    </tr>
    <tr>
      <th>
        Сайт СП
      </th>
      <td>
        <? if (isset($var['var']['data']['sp'])): ?>
          <a href="<?= $var['var']['data']['sp'][SP_SITE_URL] ?>" target="_blank"><?= $var['var']['data']['sp'][SP_SITE_NAME] ?></a>
        <? else: ?>
          —
        <? endif; ?>
      </td>
    </tr>
    <tr>
      <th>Активирован</th>
      <td>
        <? if ($var['var']['data']['user']['activate']) : ?>
          <img src="<?= URL::to('files/images/success.png') ?>" class="success">
          <div class="manage">
            <a class="img" href="<?= $var['var']['data']['user']['force_forgot_url'] ?>" title="Отослать пользователю ссылку для восстановаление пароля">
              <img src="<?= URL::to('files/images/mail.png') ?>" class="mail"></a>
          </div>
        <? else: ?>
          <img src="<?= URL::to('files/images/error.png') ?>" class="error">
          <div class="manage">
            <a class="img" href="<?= $var['var']['data']['user']['activate_url'] ?>" title="Отослать пользователю повторную ссылку для активации аккаунта">
              <img src="<?= URL::to('files/images/mail.png') ?>" class="mail"></a>&nbsp;
            <a class="img" href="<?= $var['var']['data']['user']['force_activate_url'] ?>" title="Принудительная активация аккаунта пользователя">
              <img src="<?= URL::to('files/images/force.png') ?>" class="force"></a>
          </div>
        <? endif; ?>
      </td>
    </tr>
    <tr>
      <th>ID организатора</th>
      <td>
        <? if ($var['var']['data']['user'][USER_ORG_ID] != -1) : ?>
          <?= $var['var']['data']['user'][USER_ORG_ID] ?>
        <? else: ?>
          <img src="<?= URL::to('files/images/error.png') ?>" class="error">
        <? endif; ?>
      </td>
    </tr>
    <tr>
      <th>Пароль к СП</th>
      <td>
        <? if ($var['var']['data']['user']['have_login']) : ?>
          <img src="<?= URL::to('files/images/success.png') ?>" class="success">
        <? else: ?>
          <img src="<?= URL::to('files/images/error.png') ?>" class="error">
        <? endif; ?>
      </td>
    </tr>

    <tr>
      <th>Оплачено до</th>
      <td>
        <? if ($var['var']['data']['order']['status']): ?>
          <?= $var['var']['data']['order']['date_done'] ?><? else: ?>
          <img src="<?= URL::to('files/images/error.png') ?>" class="error">
        <? endif; ?>
      </td>
    </tr>

    <tr>
      <th>Доступ к сервису</th>
      <td>
        <? if (!$var['var']['data']['user']['blocked']) : ?>
          <img src="<?= URL::to('files/images/success.png') ?>" class="success">
        <? else: ?>
          <img src="<?= URL::to('files/images/error.png') ?>" class="error">
        <? endif; ?>
        <div class="manage">
          <? if (!$var['var']['data']['user']['blocked']) : ?>
            <a href="<?= $var['var']['data']['user']['blocked_url'] ?>">Заблокировать</a>
          <? else: ?>
            <a href="<?= $var['var']['data']['user']['blocked_url'] ?>">Разблокировать</a>
          <? endif; ?>
        </div>
      </td>
    </tr>

    <tr>
      <th>
        Количество SMS
      </th>
      <td>
        <?= $var['var']['data']['user']['count_sms'] ?>
      </td>
    </tr>
    <tr>
      <th>
        Количество закупок
      </th>
      <td>
        <?= $var['var']['data']['user']['count_purchase'] ?>
      </td>
    </tr>
    <tr>
      <th>
        Количество оплат
      </th>
      <td>
        <?= $var['var']['data']['user']['count_pay'] ?>
      </td>
    </tr>

    <tr>
      <th>
        Денег заплачено
      </th>
      <td>
        <?= $var['var']['data']['sum_paid'] ?> ₽
      </td>
    </tr>
    <tr>
      <th>
        Денег получено
      </th>
      <td>
        <?= $var['var']['data']['sum'] ?> ₽
      </td>
    </tr>

    <tr>
      <th>
        Тип запроса
      </th>
      <td>
        <form class="inline" action="<?= URL::to('admin/request') ?>" method="post">
          <label>

            <select class="select-auto" name="request">
              <? foreach ($var['var']['data']['request_list'] as $request => $name) : ?>
                <option value="<?= $request ?>" <?= ($request == $var['var']['data']['user'][USER_REQUEST]) ? 'selected' : '' ?> ><?= $name ?></option>
              <? endforeach; ?>
            </select>

          </label>

          <input type="hidden" name="uid" value="<?= $var['var']['data']['user'][USER_ID] ?>">
        </form>
      </td>
    </tr>

  </table>

  <h2>Список заказов пользователя</h2>

  <? // var_dump($var['var']['data']['order']['orders']) ?>

  <? if (!empty($var['var']['data']['order']['orders'])) : ?>

    <div class="action-wrapper">
      <label> <select id="select-action" name="action" disabled>
          <option value="" selected>- Выберите действие -</option>
          <option value="return">Возврат заказа</option>
          <option value="cancel">Отменить возврат заказа</option>
          <option value="delete">Удалить заказ</option>
        </select> </label>
    </div>

    <form action="<?= URL::to('admin/order') ?>" name="control" method="post">
      <table class="zebra color">
        <thead>
        <tr>
          <th>&nbsp;</th>
          <th>ID</th>
          <th>Тип</th>
          <th>Дней</th>
          <th class="time">Добавлен</th>
          <th class="time">Запущен</th>
          <th class="time">Исполнен</th>
          <th class="time">Возвращён</th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($var['var']['data']['order']['orders'] as $key => $order): ?>
          <tr selectable="true" class="<?= $order['class'] ?> pointer">
            <td>
              <span class="button">
              <label>
                <input onclick="document.getElementsByName('action')[0].disabled=false" type="radio" name="oid" value="<?= $order[ORDER_ID] ?>" required>
              </label>
              <span>Выбрать данный заказ</span>
            </span>
            </td>
            <td>
              <?= $order[ORDER_ID] ?>
            </td>
            <td>
              <? if (empty($order['details_url'])) : ?>
                <?= $order[ORDER_TYPE] ?>

              <? else: ?>
                <a href="<?= $order['details_url'] ?>"><?= $order[ORDER_TYPE] ?></a>
              <? endif; ?>
            </td>
            <td>
              <?= $order[ORDER_DAY] ?>
            </td>
            <td>
              <?= $order[ORDER_ADD] ?>
            </td>
            <td>
              <?= $order[ORDER_RUN] ?>
            </td>
            <td>
              <?= $order[ORDER_DONE] ?>
            </td>
            <td>
              <?= $order[ORDER_RETURN] ?>
            </td>
          </tr>
        <? endforeach; ?>
        </tbody>
      </table>
      <input type="hidden" name="uid" value="<?= $var['var']['data']['user'][USER_ID] ?>">
      <input type="hidden" name="action" id="hidden-action" value="">
    </form>

  <? else : ?>
    <p>Список заказов пуст.</p>
  <? endif; ?>

  <h2>Ручное добавление заказа</h2>

  <form action="<?= URL::to('admin/order_add') ?>" method="get">
    <ul>
      <li>
        <label>Добавить выбранному пользователю заказ вручную:<input name="day" type="number" min="1" max="30" required placeholder="Укажите количество дней"></label>
      </li>
      <li>
        <input type="hidden" name="id" value="<?= $var['var']['data']['user'][USER_ID] ?>">
        <input type="submit" value="Добавить">
        <input type="button" value="Назад" onclick="location.href='<?= URL::to('admin/users') ?>'">
      </li>

    </ul>
  </form>

<? else : ?>
  <h1>Управление пользователем</h1>

  <p>Пользователя с указанным ID не найдено.</p>
<? endif; ?>

<!-- end new.tpl.php -->