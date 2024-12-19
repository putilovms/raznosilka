<!-- start details.tpl.php -->

<h1>Подробности о платеже</h1>

<? // var_dump($var['var']['info']) ?>

<? if (!empty($var['var']['info'])) : ?>
  <?= $var['var']['info']['payment'] ?>

  <input type="button" value="Назад" onclick="location.href='<?= $var['var']['info']['url'] ?>'">
<? else : ?>
  <p>Платежа с указанным ID не найдено.</p>
<? endif; ?>

<!-- end details.tpl.php -->