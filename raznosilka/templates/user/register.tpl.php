<!-- start register.tpl.php -->

<h1>Регистрация</h1>

<p>Для регистрации заполните поля расположенные ниже:</p>
<div contenteditable="false">
  <form class="register" action="" method="post">
    <ul>
      <li>
        <? if (!empty($var['var']['reg_login']['message'])): ?>

          <? foreach ($var['var']['reg_login']['message'] as $message) : ?>
            <p class="error"><?= $message ?></p>
          <? endforeach; ?>

        <? endif; ?>
        <input class="<?= $var['var']['reg_login']['class'] ?>" autofocus maxlength="<?= Validator::maxLoginLen ?>" type="text" name="reg_login" placeholder="Логин" required="required" value="<? if (isset($_POST['reg_login'])) print $_POST['reg_login'] ?>">

        <p class="hint">Логин может состоять из букв и цифр, а так же содержать пробелы. Длина логина должна быть не менее <?= Validator::minLoginLen ?> и не более <?= Validator::maxLoginLen ?> символов.</p>
      </li>
      <li>
        <? if (!empty($var['var']['reg_email']['message'])): ?>

          <? foreach ($var['var']['reg_email']['message'] as $message) : ?>
            <p class="error"><?= $message ?></p>
          <? endforeach; ?>

        <? endif; ?>
        <input class="<?= $var['var']['reg_email']['class'] ?>" maxlength="<?= Validator::maxEmailLen ?>" title="name@mail.ru" type="email" name="reg_email" placeholder="Ваш e-mail" required="required" value="<? if (isset($_POST['reg_email'])) print $_POST['reg_email'] ?>">

        <p class="hint">Длина e-mail должна быть не более <?= Validator::maxEmailLen ?> символов.</p>
      </li>
      <li>
        <? if (!empty($var['var']['reg_pass']['message'])): ?>

          <? foreach ($var['var']['reg_pass']['message'] as $message) : ?>
            <p class="error"><?= $message ?></p>
          <? endforeach; ?>

        <? endif; ?>
        <input class="<?= $var['var']['reg_pass']['class'] ?>" autocomplete="off" maxlength="<?= Validator::maxPassLen ?>" type="password" name="reg_pass_1" placeholder="Пароль" required="required" value="<? if (isset($_POST['reg_pass_1'])) print $_POST["reg_pass_1"] ?>">

        <p class="hint">Длина пароля должна быть не менее <?= Validator::minPassLen ?> и не более <?= Validator::maxPassLen ?> символов.</p>
      </li>
      <li>
        <input class="<?= $var['var']['reg_pass']['class'] ?>" autocomplete="off" maxlength="<?= Validator::maxPassLen ?>" type="password" name="reg_pass_2" placeholder="Ещё раз пароль" required="required" value="<? if (isset($_POST['reg_pass_2'])) print $_POST['reg_pass_2'] ?>">

        <p class="hint">Пароли в обоих полях должны совпадать.</p>
      </li>
      <li>
        <? if (!empty($var['var']['reg_sp_id']['message'])): ?>

          <? foreach ($var['var']['reg_sp_id']['message'] as $message) : ?>
            <p class="error"><?= $message ?></p>
          <? endforeach; ?>

        <? endif; ?>

        <select class="<?= $var['var']['reg_sp_id']['class'] ?>" name="sp_id" required="required">
          <option <?= ((isset($_POST['sp_id'])) and ($_POST['sp_id'] == '')) ? "selected" : '' ?> value="">- Выберите ваш сайт СП -</option>
          <? if ($var['var']['sp'] !== false) : ?>

            <? foreach ($var['var']['sp'] as $sp) : ?>
              <option <?= ((isset($_POST['sp_id'])) and ($_POST['sp_id'] == $sp[SP_ID])) ? "selected" : '' ?> value="<?= $sp[SP_ID] ?>"><?= $sp[SP_SITE_NAME] ?></option>
            <? endforeach; ?>

          <? endif; ?>
        </select>
        <p class="hint">Выберите сайт СП на котором вы собираетесь использовать «Разносилку».</p>
        <p class="hint"><a href="<?= URL::to('info/add_sp') ?>">Что делать, если вашего сайта СП нет в списке?</a></p>
      </li>
      <li>
        <label> <input type="checkbox" required="required"> Я соглашаюсь с
          <a target="_blank" href="<?= URL::to('info/user_agreement') ?>">пользовательским соглашеним</a> и
          <a target="_blank" href="<?= URL::to('info/confidential') ?>">политикой обработки персональных данных</a>.
        </label>
      </li>
      <li>
        <span class="icon-required"></span> - обязательно для заполнения.
      </li>
      <li>
        <input type="submit" value="Зарегистрироваться">
      </li>
    </ul>
  </form>
</div>

<!-- end register.tpl.php -->