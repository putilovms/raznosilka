<!-- start password_del.tpl.php -->

<h1>Удаление пароля от сайта СП</h1>

<form action="" method="post" class="inline">
  <p>Вы точно хотите удалить свой логин и пароль от сайта СП?</p>
  <button type="submit" name="del" value="1">Да</button>
  <button type="button" onclick="location.href='<?= URL::to('user/info') ?>'">Нет</button>
</form>

<!-- end password_del.tpl.php -->