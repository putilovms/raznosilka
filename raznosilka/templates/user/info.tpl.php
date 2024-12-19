<!-- start new.tpl.php -->

<h1>Информация об аккаунте</h1>

<? // var_dump($var['var']['user']) ?>

<table class="settings zebra">
  <tr>
    <th>Ваш ID:</th>
    <td>#<?= $var['var']['user'][USER_ID] ?></td>
  </tr>
  <tr>
    <th>Ваш логин:</th>
    <td><?= $var['var']['user'][USER_LOGIN] ?></td>
  </tr>
  <tr>
    <th>Ваш e-mail:</th>
    <td><?= $var['var']['user'][USER_EMAIL] ?></td>
  </tr>

  <tr>
    <th>Состояние:</th>
    <td>
      <? if ($var['var']['user']['status_account'] == 0) : ?>
        Аккаунт не активирован
      <? endif; ?>
      <? if ($var['var']['user']['status_account'] == 1) : ?>
        Не введён логин и пароль к сайту СП
      <? endif; ?>
      <? if ($var['var']['user']['status_account'] == 2) : ?>
        Не введён логин и пароль к сайту СП
      <? endif; ?>
      <? if ($var['var']['user']['status_account'] == 3) : ?>
        Аккаунт готов к работе
      <? endif; ?>
    </td>
  </tr>

  <tr>
    <th>Статус:</th>
    <td>
      <? if ($var['var']['user']['status']) : ?>
        Услуга предоставляется до <?= $var['var']['user']['date_done'] ?>
      <? else: ?>
        Услуга не предоставляется
      <? endif; ?>
    </td>
  </tr>

</table>

<input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'"/>

<!-- end new.tpl.php -->