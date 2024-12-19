<!-- start admin.tpl.php -->

<h1>Управление сервисом</h1>

<p class="hint">Версия <?= VERSION_RAZNOSILKA?></p>

<form class="settings" action="" method="post">
  <ul>
    <li>
      <a href="<?= URL::to('admin/info') ?>">Отчёт о состоянии сервиса</a>
    </li>
    <li>
      <a href="<?= URL::to('admin/settings') ?>">Настройки сервиса</a>
    </li>
    <li>
      <a href="<?= URL::to('admin/users') ?>">Управление пользователями</a>
    </li>
    <li>
      <a href="<?= URL::to('admin/templates') ?>">Управление шаблонами SMS</a>
    </li>
    <li>
      <a href="<?= URL::to('admin/detector') ?>">Управление нераспознанными SMS</a>
    </li>
    <li>
      <a href="<?= URL::to('admin/sp') ?>">Управление сайтами СП</a>
    </li>
    <li>
      <a href="<?= URL::to('reports') ?>">Просмотр журналов</a>
    </li>
    <li>
      <a href="<?= URL::to('messages/post') ?>">Рассылка уведомлений</a>
    </li>
    <li>
      <a href="<?= URL::to('admin/compensation') ?>">Компенсации пользователям</a>
    </li>
  </ul>
</form>

<!-- end admin.tpl.php -->