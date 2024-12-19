<!-- start password.tpl.php -->

<? // var_dump($var['var']) ?>

<? if (empty($var['var']['user'][USER_SP_LOGIN]) and empty($var['var']['user'][USER_SP_PASSWORD])) : ?>
  <h1>Ввод пароля к сайту СП</h1>
<? else: ?>
  <h1>Смена пароля к сайту СП</h1>
<? endif; ?>

<form class="settings" action="" method="post">
  <ul>
    <li>
      <? if (empty($var['var']['user'][USER_SP_LOGIN]) and empty($var['var']['user'][USER_SP_PASSWORD])) : ?>
        <p>Введите ваш логин и пароль с доступом к организаторской на сайте СП.</p>
      <? else: ?>
        <p>Для смены пароля к сайту СП, заполните поля расположенные ниже.</p>
      <? endif; ?>
    </li>
    <? if (!empty($var['var']['password']['message'])): ?>
      <li>
        <? foreach ($var['var']['password']['message'] as $message) : ?>
          <p class="<?= $var['var']['password']['class'] ?>"><?= $message ?></p>
        <? endforeach; ?>
      </li>
    <? endif; ?>
    <li>
      <label class="<?= $var['var']['password']['class'] ?>">Логин на сайте СП:
        <input class="<?= $var['var']['password']['class'] ?>" autocomplete="off" required="required" type="text" name="login" placeholder="Введите логин к сайту СП" value="<? if (isset($_POST['login'])) print $_POST['login'] ?>">
      </label>
    </li>
    <li>
      <label class="<?= $var['var']['password']['class'] ?>">Пароль на сайте СП:
        <input class="<?= $var['var']['password']['class'] ?>" autocomplete="off" required="required" type="password" name="password" placeholder="Введите пароль к сайту СП" value="<? if (isset($_POST['password'])) print $_POST['password'] ?>">
      </label>
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <input type="submit" value="Сохранить">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('user/sp') ?>'">
    </li>
  </ul>
</form><!-- end password.tpl.php -->