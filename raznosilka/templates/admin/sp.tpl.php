<!-- start sp.tpl.php -->

<h1>Управление сайтами СП</h1>

<div class="wrapper">
  <button onclick="location.href='<?= URL::to('admin/import_sp') ?>'">Импорт CSV</button>
  <button onclick="location.href='<?= URL::to('admin/export_sp') ?>'">Экспорт CSV</button>
</div>

<? // var_dump($var['var']['info']['sp']) ?>

<? if (!empty($var['var']['info']['sp'])) : ?>

  <? foreach ($var['var']['info']['sp'] as $key => $sp): ?>
    <div class="lot <?= $sp['class'] ?>">

      <p class="user-number bold" title="ID сайта СП">#<?= $sp[SP_ID] ?></p>

      <p class="bold" title="Название сайта СП"><?= $sp[SP_SITE_NAME] ?></p>

      <form action="<?= URL::to('admin/edit_sp') ?>" method="post">

        <table class="zebra settings">
          <tbody>
          <tr title="Название сайта СП" class="explanation">
            <th>Название сайта СП</th>
            <td class="nested">
              <input class="template" type="text" name="<?= SP_SITE_NAME ?>" placeholder="Введите название сайта СП" required="required" value="<?= $sp[SP_SITE_NAME] ?>">
            </td>
          </tr>
          <tr title="URL сайта СП" class="explanation">
            <th>URL сайта СП</th>
            <td class="nested">
              <input class="template" type="url" name="<?= SP_SITE_URL ?>" placeholder="Введите URL сайта СП" required="required" value="<?= $sp[SP_SITE_URL] ?>">
            </td>
          </tr>
          <tr title="Описание сайта СП" class="explanation">
            <th>Описание сайта СП</th>
            <td class="nested">
              <input class="template" type="text" name="<?= SP_DESCRIPTION ?>" placeholder="Введите описание сайта СП" required="required" value="<?= $sp[SP_DESCRIPTION] ?>">
            </td>
          </tr>
          <tr title="Количество дней на проставление оплат по правилам данного сайта СП" class="explanation">
            <th>Дней на проставление</th>
            <td class="nested">
              <input class="template" type="number" step="1" min="0" max="30" name="<?= SP_FILLING_DAY ?>" placeholder="Введите количество дней" required="required" value="<?= $sp[SP_FILLING_DAY] ?>">
            </td>
          </tr>
          <tr title="Тип запроса к сайту СП по умолчанию" class="explanation">
            <th>Тип запроса</th>
            <td class="nested">
              <label>

                <select name="<?= SP_REQUEST ?>">
                  <? foreach ($var['var']['info']['request_list'] as $request => $name) : ?>
                    <option value="<?= $request ?>" <?= ($request == $sp[SP_REQUEST]) ? 'selected' : '' ?> ><?= $name ?></option>
                  <? endforeach; ?>
                </select>

              </label>
            </td>
          </tr>
          <tr title="Часовой пояс для сайта СП по умолчанию" class="explanation">
            <th>Часовой пояс</th>
            <td class="nested">
              <label>

                <select name="<?= SP_TIME_ZONE ?>">
                  <? foreach ($var['var']['info']['time_zones'] as $group => $zones) : ?>
                    <optgroup label="<?= $group ?>">
                      <? foreach ($zones as $zone) : ?>
                        <option value="<?= $zone['time_zone'] ?>" <?= ($zone['time_zone'] == $sp[SP_TIME_ZONE]) ? 'selected' : '' ?> ><?= $zone['city'] ?></option>
                      <? endforeach; ?>
                    </optgroup>
                  <? endforeach; ?>
                </select>

              </label>
            </td>
          </tr>
          </tbody>
        </table>

        <label><input name="<?= SP_ACTIVE ?>" value="1" type="checkbox" <?= $sp[SP_ACTIVE] ? "checked" : "" ?>> Сайт СП доступен</label>

        <div class="wrapper">
          <button type="submit">Изменить</button>
          <button type="button" onclick="if (confirm('Удалить сайт СП?')) location.href='<?= $sp['url'] ?>'">Удалить</button>
        </div>

        <input type="hidden" name="<?= SP_ID ?>" value="<?= $sp[SP_ID] ?>">

      </form>

    </div>
  <? endforeach ?>

<? else: ?>
  <p>Список сайтов СП пуст.</p>
<? endif ?>

<button onclick="location.href='<?= URL::to('admin/add_sp') ?>'">Добавить сайт СП</button>
<button onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>

<!-- end sp.tpl.php -->