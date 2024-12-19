<!-- start reports.tpl.php -->

<h1>Просмотр журналов</h1>

<? // var_dump($var['var']) ?>

<table class="zebra">
  <tr>
    <th>Журнал</th>
    <th>Описание</th>
    <th>Строк</th>
    <th>Изменён</th>
    <th>Размер</th>
  </tr>
  <? foreach ($var['var']['logs'] as $log) : ?>
    <tr>
      <td><a href="<?= $log['page_url'] ?>"><?= $log['file_name'] ?></a></td>
      <td><?= $log['description'] ?></td>
      <td><?= $log['lines'] ?></td>
      <td><?= $log['modify'] ?></td>
      <td><?= $log['size'] ?></td>
    </tr>
  <? endforeach; ?>
</table>

<button onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>
<button onclick="location.href='<?= URL::to('reports/delete_all') ?>'">Удалить все журналы</button>

<!-- end reports.tpl.php -->