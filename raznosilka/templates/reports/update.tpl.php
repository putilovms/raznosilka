<!-- start update.tpl.php -->

<h1>Журнал ошибок скрипта update.php</h1>

<? // var_dump($var['var']['log']) ?>

<? if (!empty($var['var']['log'])) : ?>
  <div class="box">
    <label>Фильтр:<input id="filter" name="filter" type="text"/></label>

    <table class="zebra" id="log">
      <tr>
        <th>№</th>
        <th>Ошибка</th>
      </tr>
      <? foreach ($var['var']['log'] as $keyLog => $log) : ?>
        <tr>
          <td><?= $keyLog + 1 ?></td>
          <td><?= $log['error'] ?></td>
        </tr>
      <? endforeach; ?>
    </table>
  </div>
<? else: ?>

  <p>Журнал пуст.</p>

<? endif; ?>

<button onclick="location.href='<?= URL::to('reports') ?>'">Назад</button>
<button onclick="location.href='<?= URL::to('reports/delete', array('log' => 'update')) ?>'">Удалить</button>


<!-- end update.tpl.php -->