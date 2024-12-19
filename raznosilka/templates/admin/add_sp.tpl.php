<!-- start add_sp.tpl.php -->

<h1>Добавлние сайта СП</h1>

<? // var_dump($var['var']['info']) ?>

<form name="sp" action="<?= URL::to('admin/add_sp') ?>" method="post">

  <ul>
    <li>
      <label>Название сайта СП:

        <input type="text" name="<?= SP_SITE_NAME ?>" placeholder="Введите название сайта СП" required="required" value="<?= (isset($_POST[SP_SITE_NAME])) ? $_POST[SP_SITE_NAME] : '' ?>">

      </label>
    </li>
    <li>
      <label>URL сайта СП:

        <input type="url" name="<?= SP_SITE_URL ?>" placeholder="Введите URL сайта СП" required="required" value="<?= (isset($_POST[SP_SITE_URL])) ? $_POST[SP_SITE_URL] : '' ?>">

      </label>
    </li>
    <li>
      <label>Описание сайта СП:

        <input type="text" name="<?= SP_DESCRIPTION ?>" placeholder="Введите описание сайта СП" required="required" value="<?= (isset($_POST[SP_DESCRIPTION])) ? $_POST[SP_DESCRIPTION] : '' ?>">

      </label>
    </li>
    <li>
      <label>Количество дней на проставление оплат по правилам данного сайта СП:

        <input type="number" step="1" min="0" max="30" name="<?= SP_FILLING_DAY ?>" placeholder="Введите количество дней" required="required" value="<?= (isset($_POST[SP_FILLING_DAY])) ? $_POST[SP_FILLING_DAY] : '' ?>">

      </label>
    </li>
    <li>
      <label>Тип запроса к сайту СП по умолчанию:

        <select name="<?= SP_REQUEST ?>" required="required">
          <option value="" disabled selected>- Выберите тип запроса -</option>
          <? foreach ($var['var']['info']['request_list'] as $request => $name) : ?>
            <option value="<?= $request ?>" <?= ((isset($_POST[SP_REQUEST])) and ($_POST[SP_REQUEST] == $request)) ? 'selected' : '' ?> ><?= $name ?></option>
          <? endforeach; ?>
        </select>

      </label>
    </li>
    <li>
      <label>Часовой пояс для сайта СП по умолчанию:

        <select name="<?= SP_TIME_ZONE ?>" required>
          <option value="" disabled selected>- Выберите часовой пояс -</option>
          <? foreach ($var['var']['info']['time_zones'] as $group => $zones) : ?>
            <optgroup label="<?= $group ?>">
              <? foreach ($zones as $zone) : ?>
                <option value="<?= $zone['time_zone'] ?>" <?= ((isset($_POST[SP_TIME_ZONE])) and ($_POST[SP_TIME_ZONE] == $zone['time_zone'])) ? 'selected' : '' ?> ><?= $zone['city'] ?></option>
              <? endforeach; ?>
            </optgroup>
          <? endforeach; ?>
        </select>

      </label>
    </li>
    <li>
      <label><input name="<?= SP_ACTIVE ?>" value="1" type="checkbox" <?= (isset($_POST[SP_ACTIVE])) ? "checked" : "" ?>> Сайт СП доступен</label>
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <button type="submit">Добавить</button>
      <button type="button" onclick="location.href='<?= URL::to('admin/sp') ?>'">Назад</button>
    </li>
  </ul>

</form>

<script>
  <?= $var['var']['info']['json'] ?>
</script>

<!-- end add_sp.tpl.php -->