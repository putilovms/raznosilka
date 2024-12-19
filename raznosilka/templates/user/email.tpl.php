<!-- start email.tpl.php -->

<h1>Смена e-mail</h1>

<? // var_dump($var['var']['info']) ?>

<table class="zebra settings">

  <tr>
    <th>
      Текущий e-mail:
    </th>
    <td>
      <?= $var['var']['info'][USER_EMAIL] ?>
    </td>
  </tr>

  <? if (!empty($var['var']['info'][USER_TMP_EMAIL])) : ?>
    <tr>
      <th>
        Новый e-mail:
      </th>
      <td>
        <?= $var['var']['info'][USER_TMP_EMAIL] ?>
      </td>
    </tr>
  <? endif; ?>

</table>

<? if (empty($var['var']['info'][USER_TMP_EMAIL])) : ?>
  <p>Для смены e-mail заполните поле расположенное ниже:</p>

  <form class="register" action="" method="post">
    <ul>
      <li>
        <? if (!empty($var['var']['email']['message'])): ?>

          <? foreach ($var['var']['email']['message'] as $message) : ?>
            <p class="error"><?= $message ?></p>
          <? endforeach; ?>

        <? endif; ?>
        <input class="<?= $var['var']['email']['class'] ?>" maxlength="<?= Validator::maxEmailLen ?>" title="name@mail.ru" type="email" name="new_email" placeholder="Новый e-mail" required="required" value="<? if (isset($_POST['new_email'])) print $_POST['new_email'] ?>">

        <p class="hint">Длина e-mail должна быть не более <?= Validator::maxEmailLen ?> символов.</p>
      </li>
      <li>
        <input type="submit" value="Сменить e-mail">
        <input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'"/>
      </li>
    </ul>
  </form>
<? else : ?>
  <p>Для того чтобы подтвердить смену e-mail, нажмите на ссылку в письме, которое было выслано вам на новый e-mail.</p>
  <input type="button" value="Выслать повторное письмо" onclick="location.href='<?= URL::to('user/repeat') ?>'"/>
  <input type="button" value="Отменить смену e-mail" onclick="location.href='<?= URL::to('user/cancel') ?>'"/>
  <input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'"/>
<? endif; ?>

<!-- end email.tpl.php -->