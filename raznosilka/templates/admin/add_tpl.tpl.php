<!-- start add.tpl.php -->

<h1>Добавлние шаблона SMS</h1>

<? // var_dump($var['var']['info']) ?>

<form name="templates" action="<?= URL::to('admin/add_tpl') ?>" method="post">

  <ul>
    <li>
      <label>Тип шаблона:

        <select id="type" name="type">
          <? foreach ($var['var']['info']['type'] as $type => $text) : ?>
            <option value="<?= $type ?>" <?= ((isset($_POST['type'])) and ($_POST['type'] == $type)) ? "selected='selected'" : '' ?>><?= $text ?></option>
          <? endforeach; ?>
        </select>

      </label>
    </li>
    <li>
      <label>Подтип шаблона:

        <select id="subtype" name="subtype">
          <? $sel = $var['var']['info']['select'] ?>
          <? foreach ($var['var']['info']['subtype'][$sel] as $subtype => $text) : ?>
            <option value="<?= $subtype ?>" <?= ((isset($_POST['subtype'])) and ($_POST['subtype'] == $subtype)) ? "selected='selected'" : '' ?>><?= $text ?></option>
          <? endforeach; ?>
        </select>

      </label>
    </li>
    <li>
      <label>Шаблон:

        <input type="text" name="template" placeholder="Введите шаблон" required="required" value="<?= (isset($_POST['template'])) ? $_POST['template'] : '' ?>">

      </label>
    </li>
    <li>
      <label><input name="active" value="1" type="checkbox" <?= (isset($_POST['active'])) ? "checked" : "" ?>> Шаблон включен</label>
    </li>
    <li>
      <textarea name="description" rows="3" placeholder="Введите описание шаблона"><?= (isset($_POST['description'])) ? $_POST['description'] : '' ?></textarea>
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <button type="submit">Добавить</button>
      <button type="button" onclick="location.href='<?= URL::to('admin/templates') ?>'">Назад</button>
    </li>
  </ul>

</form>

<script>
  <?= $var['var']['info']['json'] ?>
</script>

<!-- end add.tpl.php -->