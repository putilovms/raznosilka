<!-- start request.tpl.php -->

<h1>Журнал запросов к сайтам СП</h1>

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
        <th>Хост</th>
        <th>Путь</th>
        <th>Запрос</th>
        <th>Тип запроса</th>
      </tr>
      <? foreach ($var['var']['log'] as $keyLog => $log) : ?>
        <tr>
          <td><?= $keyLog + 1 ?></td>
          <td><?= $log['time'] ?></td>
          <td><?= $log['uid'] ?></td>
          <td><?= $log['ip'] ?></td>
          <td><?= $log['login'] ?></td>
          <td><?= $log['host'] ?></td>
          <td><?= $log['path'] ?></td>
          <td><?= $log['query'] ?></td>
          <td><?= $log['type'] ?></td>
        </tr>
      <? endforeach; ?>
    </table>
  </div>
<? else: ?>

  <p>Журнал пуст.</p>

<? endif; ?>

<button onclick="location.href='<?= URL::to('reports') ?>'">Назад</button>
<button onclick="location.href='<?= URL::to('reports/delete', array('log' => 'request')) ?>'">Удалить</button>


<!-- end request.tpl.php -->