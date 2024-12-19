<!-- start path.tpl.php -->

<h1>Журнал URL</h1>

<? // var_dump($var['var']['log']) ?>

<? if (!empty($var['var']['log'])) : ?>
  <div class="box">
    <label>Фильтр:<input id="filter" name="filter" type="text"/></label>

    <table class="zebra" id="log">
      <tr>
        <th>№</th>
        <th class="time">Время</th>
        <th>UID</th>
        <th>IP</th>
        <th>Логин</th>
        <th>Путь</th>
        <th>Ошибка</th>
      </tr>
      <? foreach ($var['var']['log'] as $keyLog => $log) : ?>
        <tr>
          <td><?= $keyLog + 1 ?></td>
          <td><?= $log['time'] ?></td>
          <td><?= $log['uid'] ?></td>
          <td><?= $log['ip'] ?></td>
          <td><?= $log['login'] ?></td>
          <td><?= $log['path'] ?></td>
          <td><?= $log['error'] ?></td>
        </tr>
      <? endforeach; ?>
    </table>
  </div>
<? else: ?>

  <p>Журнал пуст.</p>

<? endif; ?>

<button onclick="location.href='<?= URL::to('reports') ?>'">Назад</button>
<button onclick="location.href='<?= URL::to('reports/delete', array('log' => 'path')) ?>'">Удалить</button>

<!-- end path.tpl.php -->