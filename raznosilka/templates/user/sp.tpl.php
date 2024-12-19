<!-- start info.tpl.php -->

<h1>Настройки сайта СП</h1>


<form class="settings" action="" method="post">

  <ul>
    <? #Прямой запрос?>
    <? if ((int)$var['user'][USER_REQUEST] === REQUEST_CURL) : ?>

      <? if (!empty($var['var']['user'][USER_SP_LOGIN]) and !empty($var['var']['user'][USER_SP_PASSWORD])) : ?>
        <li>
          <p class="success">Ваш аккаунт готов к работе с сайтом СП.</p>
        </li>
      <? else: ?>
        <li>
          <p class="error">Не ввёден логин и пароль от сайта СП.</p>
        </li>
      <? endif; ?>

    <? endif; ?>
    <? #Запрос через расширение?>
    <? if ((int)$var['user'][USER_REQUEST] === REQUEST_EXTENSIONS) : ?>
      <li>
        <p class="success">Ваш аккаунт готов к работе с сайтом СП.</p>
      </li>
    <? endif; ?>
    <li>
      <table class="settings zebra">
        <tr>
          <th>Сайт СП:</th>
          <td><?= $var['var']['user'][SP_SITE_NAME] ?></td>
        </tr>
        <tr>
          <th>ID организатора:</th>
          <td>
            <?= ($var['var']['user'][USER_ORG_ID] != -1) ? $var['var']['user'][USER_ORG_ID] : '—' ?>
          </td>
        </tr>
        <? if (!empty($var['var']['user'][USER_SP_LOGIN]) and !empty($var['var']['user'][USER_SP_PASSWORD])) : ?>
          <tr>
            <th>Логин организатора:</th>
            <td><?= $var['var']['user'][USER_SP_LOGIN] ?></td>
          </tr>
        <? endif; ?>
      </table>
    </li>

    <? #Прямой запрос?>
    <? if ((int)$var['user'][USER_REQUEST] === REQUEST_CURL) : ?>

      <? if (!empty($var['var']['user'][USER_SP_LOGIN]) and !empty($var['var']['user'][USER_SP_PASSWORD])) : ?>
        <li>
          <a href="<?= URL::to('user/password') ?>">Смена логина и пароля к сайту СП</a>
        </li>
        <li>
          <a href="<?= URL::to('user/password_del') ?>">Удалить логин и пароль от сайта СП</a>

          <p class="hint">Воспользуйтесь данной функцией если вы хотите полностью удалить из «Разносилки» логин и пароль от сайта СП. ID организатора при этом будет сохранено.</p>
        </li>
      <? else: ?>
        <li>
          <a href="<?= URL::to('user/password') ?>">Ввести логин и пароль от сайта СП</a>
        </li>
      <? endif; ?>

    <? endif; ?>
    <li>
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'">
    </li>
  </ul>

</form>

<!-- end info.tpl.php -->