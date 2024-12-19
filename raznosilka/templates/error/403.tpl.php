<!-- start 403.tpl.php -->
<div id="error-wrapper">
  <h1>Доступ запрещен</h1>

  <p><?= $var['var']['messages'] ?></p>

  <form action="<?= URL::base() ?>" method="post">
    <button type="submit">Перейти на главную</button>
  </form>

</div><!-- end 403.tpl.php -->