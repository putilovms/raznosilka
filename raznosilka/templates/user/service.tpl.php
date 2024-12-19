<!-- start notify.tpl.php -->

<h1>Настройки «Разносилки»</h1>

<? // var_dump($var['var']['info']) ?>

<form class="settings" action="" method="post">

  <ul>
    <li>
      <label>Настройка часового пояса:
        <select name="time_zone">
          <? foreach ($var['var']['info']['time_zones'] as $group => $zones) : ?>
            <optgroup label="<?= $group ?>">
            <? foreach ($zones as $zone) : ?>
              <option value="<?= $zone['time_zone'] ?>" <?= ($zone['time_zone'] == $var['var']['info']['user'][USER_TIME_ZONE])? 'selected'  : '' ?> ><?= $zone['city'] ?></option>
            <? endforeach; ?>
          </optgroup>
          <? endforeach; ?>
        </select>
      </label>
    </li>

    <li>
      <input name="submit" type="submit" value="Сохранить">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('user') ?>'">
    </li>
  </ul>

</form>

<!-- end notify.tpl.php -->