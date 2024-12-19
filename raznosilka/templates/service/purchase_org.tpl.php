<!-- start purchase_org.tpl.php -->

<? // var_dump($var['var']['list']) ?>

<h1>Выбор закупки</h1>

<? if ($var['var']['list']['select'] === false): ?>
  <p>На данный момент ни одна закупка не выбрана.</p>
<? else : ?>
  <p>Сейчас выбрана закупка:
    <a href="<?= $var['var']['list']['select']['url'] ?>" target='_blank'><?= $var['var']['list']['select'][PURCHASE_NAME] ?></a>
  </p>
<? endif; ?>

<section class="tabs">
  <ul class="menu">
    <li class="active"><a href="<?= URL::to('service/purchase_org') ?>">Организаторская</a></li>
    <li><a href="<?= URL::to('service/purchase_all') ?>">Все</a></li>
  </ul>
</section>

<form action="" method="get">
  <label>Поиск закупки:
    <input type="text" name="filter" placeholder="Введите имя закупки" value="<? if (isset($_GET['filter'])) print $_GET['filter'] ?>">
  </label>
</form>

<p>В этом списке содержатся все доступные закупки из вашей организаторской. Всего:
  <span id="total-purchase" class="bold">0</span> шт.</p>

<p>Выберите закупку с которой вы хотите работать:</p>

<div id="pager-top"></div>

<table id="list-purchase" class="zebra">
  <thead>
    <tr>
      <th class="purchase-name">Имя закупки</th>
      <th class="time center">Оплата до</th>
      <th class="sum center">Найдено, руб</th>
      <th class="link center">Ссылка</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td id="load-status" colspan="4">Загрузка данных...</td>
    </tr>
  </tbody>
</table>

<div id="pager-bottom"></div>

<!-- end purchase_org.tpl.php -->