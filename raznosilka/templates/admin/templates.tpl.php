<!-- start templates.tpl.php -->

<h1>Управление шаблонами SMS</h1>

<div class="wrapper">
  <button onclick="location.href='<?= URL::to('admin/import_tpl') ?>'">Импорт CSV</button>
  <button onclick="location.href='<?= URL::to('admin/export_tpl') ?>'">Экспорт CSV</button>
</div>

<form name="templates" action="<?= URL::to('admin/templates') ?>" method="get">

  <label>Тип шаблона:

    <select name="type" onchange="document.forms['templates'].submit()">
      <? foreach ($var['var']['info']['select'] as $type => $text) : ?>
        <option value="<?= $type ?>" <?= (($var['var']['info']['type'] == $type)) ? "selected='selected'" : '' ?>><?= $text ?></option>
      <? endforeach; ?>
    </select>

  </label>

</form>

<? // var_dump($var['var']['info']['tpl']) ?>

<? if (!empty($var['var']['info']['tpl'])) : ?>

  <? foreach ($var['var']['info']['tpl'] as $key => $tpl): ?>
    <div class="lot <?= $tpl['class'] ?>">

      <p class="user-number" title="Позиция шаблона"><b>#<?= $key + 1 ?></b></p>

      <p title="Подтип шаблона"><b><?= $tpl[TPL_SUBTYPE] ?></b></p>

      <form action="<?= URL::to('admin/edit_tpl') ?>" method="post">

        <input class="template" type="text" name="template" placeholder="Введите шаблон" required="required" value="<?= $tpl[TPL_TEMPLATE] ?>">

        <table class="zebra settings">
          <tbody>
          <tr title="Количество использований шаблона" class="explanation">
            <th>Использован раз</th>
            <td class="right"><?= $tpl[TPL_COUNT_USED] ?></td>
          </tr>
          <tr title="Последняя дата использования шаблона">
            <th>Дата использования</th>
            <td class="right"><?= $tpl[TPL_LAST_USED] ?></td>
          </tr>
          </tbody>
        </table>

        <dl class="details">
          <dt><i>Дополнительно</i></dt>
          <dd>
            <div class="wrapper">
              <ul>
                <li>
                  <label><input name="active" value="1" type="checkbox" <?= $tpl[TPL_ACTIVE] ? "checked" : "" ?>> Шаблон включен</label>
                </li>
                <li>
                  <textarea class="template" name="description" rows="3" placeholder="Введите описание шаблона"><?= $tpl[TPL_DESCRIPTION] ?></textarea>
                </li>
              </ul>
            </div>
          </dd>
        </dl>

        <div class="wrapper">
          <button type="submit">Изменить</button>
          <button type="button" onclick="if (confirm('Удалить шаблон?')) location.href='<?= $tpl['url'] ?>'">Удалить</button>
        </div>

        <input type="hidden" name="tid" value="<?= $tpl[TPL_ID] ?>">
      </form>

    </div>
  <? endforeach ?>

<? else: ?>
  <p>Шаблонов выбранного типа не найдено.</p>
<? endif ?>

<button onclick="location.href='<?= URL::to('admin/add_tpl') ?>'">Добавить шаблон</button>
<button onclick="if (confirm('Сбросить статистику?')) location.href='<?= URL::to('admin/stat_tpl_reset') ?>'">Сброс статистики</button>
<button onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>

<!-- end templates.tpl.php -->