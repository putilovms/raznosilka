<!-- start login.tpl.php -->
<h1>Вход на сайт</h1>
<p>Пожалуйста, введите свой логин и пароль.</p>
<form class="login" action="" method="post">
  <ul>
    <li>
      <? if (!empty($var['var']['login_result']['message'])): ?>
        <? foreach ($var['var']['login_result']['message'] as $message) : ?>
          <p class="error"><?= $message ?></p>
        <? endforeach; ?>
      <? endif; ?>
      <input class="<?= $var['var']['login_result']['class'] ?>" type="text" maxlength="<?= Validator::maxLoginLen ?>" name="auth_login" autofocus placeholder="Логин" required="required" value="<?= isset($_POST['auth_login']) ? $_POST['auth_login'] : '' ?>">
    </li>
    <li>
      <input class="<?= $var['var']['login_result']['class'] ?>" type="password" name="auth_pass" placeholder="Пароль" required="required" value="<?= isset($_POST['auth_pass']) ? $_POST['auth_pass'] : '' ?>">
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <input type="submit" value="Войти">
      <a href="<?= URL::to('user/forgot') ?>">Забыли пароль?</a>
    </li>
  </ul>
</form>
<!-- end login.tpl.php -->