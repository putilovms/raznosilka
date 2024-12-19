<!-- start cron.tpl.php -->

<h1>Журнал работы хрона</h1>

<? // var_dump($var['var']['log']) ?>

<? if (!empty($var['var']['log'])) : ?>
  <div class="box">
    <label>Фильтр:<input id="filter" name="filter" type="text"/></label>

    <table class="zebra" id="log">
      <tr>
        <th>№</th>
        <th class="time">Время</th>
        <th>Действие</th>
      </tr>
      <? foreach ($var['var']['log'] as $keyLog => $log) : ?>
        <tr>
          <td><?= $keyLog + 1 ?></td>
          <td><?= $log['time'] ?></td>
          <td><?= $log['action'] ?></td>
        </tr>
      <? endforeach; ?>
    </table>
  </div>
<? else: ?>

  <p>Журнал пуст.</p>

<? endif; ?>

<button onclick="location.href='<?= URL::to('reports') ?>'">Назад</button>
<button onclick="location.href='<?= URL::to('reports/delete', array('log' => 'cron')) ?>'">Удалить</button>


<!-- end cron.tpl.php -->