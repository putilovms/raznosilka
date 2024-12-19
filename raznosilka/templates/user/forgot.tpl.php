<!-- start forgot.tpl.php -->
<h1>Восстановление пароля</h1>
<p>Пожалуйста, введите e-mail адрес указанный вами при регистрации.</p>
<form class="forgot" action="" method="post">
  <ul>
    <li>
      <? if (!empty($var['var']['forgot_result']['message'])): ?>
        <? foreach ($var['var']['forgot_result']['message'] as $message) : ?>
          <p class="error"><?= $message ?></p>
        <? endforeach; ?>
      <? endif; ?>
      <input class="<?= $var['var']['forgot_result']['class'] ?>" type="email" name="forgot_email" autofocus placeholder="E-mail" required="required" value="<?= isset($_POST['forgot_email']) ? $_POST['forgot_email'] : '' ?>">
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <input type="submit" value="Восстановить">
    </li>
  </ul>
</form>
<!-- end forgot.tpl.php -->