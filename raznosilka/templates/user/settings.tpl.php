<!-- start settings.tpl.php -->

<h1>Настройки аккаунта</h1>

<form class="settings" action="" method="post">
  <ul>
    <li><a href="<?= URL::to('user/info') ?>">Информация об аккаунте</a></li>
    <li><a href="<?= URL::to('user/service') ?>">Настройки «Разносилки»</a></li>
    <li><a href="<?= URL::to('user/security') ?>">Смена пароля к «Разносилке»</a></li>
    <li><a href="<?= URL::to('user/email') ?>">Смена e-mail к «Разносилке»</a></li>
    <li><a href="<?= URL::to('user/sp') ?>">Настройки сайта СП</a></li>
    <li><a href="<?= URL::to('user/notify') ?>">Уведомления по e-mail</a></li>
  </ul>
</form>

<!-- end settings.tpl.php -->