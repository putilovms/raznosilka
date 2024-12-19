<!-- start notify.tpl.php -->

<h1>Уведомления по e-mail</h1>

<? // var_dump($var['var']['notify']) ?>

<form class="settings" action="" method="post">

  <ul>
    <li>
      <label> Через сколько дней, после окончания сроков оплаты, необходимо проставить полученные суммы от участников:
        <input type="number" min="0" max="<?= Validator::maxFillingDay ?>" name="filling_day" value="<?= $var['var']['notify']['user'][USER_FILLING_DAY] ?>">
      </label>

      <p class="hint">От этой настройки зависит, через сколько дней «Разносилка» будет напоминать по E-mail о необходимости проставить оплаты. А так же какие закупки будут помечены для проставления оплат на странице «Выбрать закупку».</p>
    </li>

    <li>
      <label>
        <input type="checkbox" name="reminding" <?= ($var['var']['notify']['user'][USER_REMINDING]) ? "checked" : '' ?> value="1"> Напоминать по E-mail о необходимости проставить оплаты в закупке
      </label>
    </li>

    <li>
      <input name="submit" type="submit" value="Сохранить">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'">
    </li>
  </ul>

</form>

<!-- end notify.tpl.php -->