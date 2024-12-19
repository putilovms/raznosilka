<!-- start users.tpl.php -->

<h1>Управление пользователями</h1>

<? // var_dump($var['var']['data']['users']) ?>

<? if (!empty($var['var']['data']['users'])): ?>

  <p>Всего пользователей: <b><?= $var['var']['data']['items_count'] ?></b>.</p>
  <?= $var['var']['data']['pager'] ?>

  <table class="zebra">
    <thead>
    <tr>
      <th>ID</th>
      <th>Логин</th>
      <th class="time">Последний вход</th>
      <th class="time">Оплачено до</th>
      <th>СП</th>
      <th title="ID организатора, если он получен">Орг. ID</th>
      <th title="Активировал ли пользователь свой аккаунт">Акт.</th>
      <th title="Разблокирован или заблокирован пользователь в сервисе">Р/З</th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($var['var']['data']['users'] as $key => $user): ?>
      <tr>
        <td>
          <?= $user[USER_ID] ?>
        </td>
        <td>
          <a href="<?= $user['url'] ?>"><?= $user[USER_LOGIN] ?></a>
        </td>
        <td>
          <?= $user[USER_LAST_TIME] ?>
        </td>
        <td>
          <? if ($user['status']): ?>
            <?= $user['date_done'] ?><? else: ?>
            —
          <? endif; ?>
        </td>
        <td>
          <? if (!empty($user[SP_SITE_URL])): ?>
            <a href="<?= $user[SP_SITE_URL] ?>" target="_blank"><?= $user[SP_SITE_NAME] ?></a>
          <? else: ?>
            —
          <? endif; ?>
        </td>
        <td>
          <? if ($user[USER_ORG_ID] != -1) : ?>
            <?= $user[USER_ORG_ID] ?>
          <? else: ?>
            <img src="<?= URL::to('files/images/error.png') ?>" class="error">
          <? endif; ?>
        </td>
        <td>
          <? if ($user['activate']) : ?>
            <img src="<?= URL::to('files/images/success.png') ?>" class="success">
          <? else: ?>
            <img src="<?= URL::to('files/images/error.png') ?>" class="error">
          <? endif; ?>
        </td>
        <td>
          <? if (!$user[USER_BLOCKED]) : ?>
            <img src="<?= URL::to('files/images/success.png') ?>" class="success">
          <? else: ?>
            <img src="<?= URL::to('files/images/error.png') ?>" class="error">
          <? endif; ?>
        </td>
      </tr>
    <? endforeach; ?>
    </tbody>
  </table>

  <?= $var['var']['data']['pager'] ?>

<? else: ?>

  <p>Список пользователей «Разносилки» пуст.</p>

<? endif; ?>

<button onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>

<!-- end users.tpl.php -->