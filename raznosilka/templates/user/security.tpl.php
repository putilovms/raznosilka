<!-- start security.tpl.php -->
<h1>Смена пароля к «Разносилке»</h1>
<form class="settings" action="" method="post">
  <ul>
    <li>
      <p>Для смены пароля к «Разносилке», заполните поля расположенные ниже.</p>
    </li>
    <? if (!empty($var['var']['change_pass']['message'])): ?>
      <li>
        <? foreach ($var['var']['change_pass']['message'] as $message) : ?>
          <p class="<?= $var['var']['change_pass']['class'] ?>"><?= $message ?></p>
        <? endforeach; ?>
      </li>
    <? endif; ?>
    <li>
      <label>Старый пароль:
        <input class="<?= $var['var']['change_pass']['class'] ?>" required="required" type="password" name="old_pass" placeholder="Введите ваш старый пароль" maxlength="100" value="<? if (isset($_POST['old_pass'])) print $_POST['old_pass'] ?>">
      </label>
    </li>
    <li>
      <label>Новый пароль:
        <input class="<?= $var['var']['change_pass']['class'] ?>" required="required" type="password" name="new_pass_1" placeholder="Введите новый пароль" maxlength="100" value="<? if (isset($_POST['new_pass_1'])) print $_POST['new_pass_1'] ?>">
      </label>
    </li>
    <li>
      <input class="<?= $var['var']['change_pass']['class'] ?>" required="required" type="password" name="new_pass_2" placeholder="Ещё раз введите новый пароль" maxlength="100" value="<? if (isset($_POST['new_pass_2'])) print $_POST['new_pass_2'] ?>">

      <p class="hint">Длина пароля должна быть не менее <?= Validator::minPassLen ?> и не более <?= Validator::maxPassLen ?> символов.</p>
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <input type="submit" value="Сохранить">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'">
    </li>
  </ul>
</form>
<!-- end security.tpl.php -->