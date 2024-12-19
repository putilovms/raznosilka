<!-- start purchase_all.tpl.php -->

<? // var_dump($var['var']['list']) ?>

<h1>Выбор закупки</h1>

<? if (is_array($var['var']['list'])): ?>

  <? if ($var['var']['list']['select'] === false): ?>
    <p>На данный момент ни одна закупка не выбрана.</p>
  <? else : ?>
    <p>Сейчас выбрана закупка:
      <a href="<?= $var['var']['list']['select']['url'] ?>" target='_blank'><?= $var['var']['list']['select'][PURCHASE_NAME] ?></a>
    </p>
  <? endif; ?>

<? else: ?>
  <p>На данный момент ни одна закупка не выбрана.</p>
<? endif; ?>

<section class="tabs">
  <ul class="menu">
    <li><a href="<?= URL::to('service/purchase_org') ?>">Организаторская</a></li>
    <li class="active"><a href="<?= URL::to('service/purchase_all') ?>">Все</a></li>
  </ul>
</section>

<form action="" method="get">
  <label>Поиск закупки:
    <input type="text" name="filter" placeholder="Введите имя закупки" value="<? if (isset($_GET['filter'])) print $_GET['filter'] ?>">
  </label>
</form>

<p>В этом списке содержатся все ваши закупки известные «Разносилке».
  <? if (is_array($var['var']['list'])): ?>
    Всего: <b><?= $var['var']['list']['item_count'] ?></b> шт.
  <? endif; ?>
</p>

<p>Выберите закупку с которой вы хотите работать:</p>

<? if (is_array($var['var']['list'])): ?>

  <? if (!empty($var['var']['list']['purchase'])): ?>

    <?= $var['var']['list']['pager'] ?>

    <table class="zebra">
      <tr>
        <th class="purchase-name">Имя закупки</th>
        <th class="sum center">Найдено, руб</th>
        <th class="link center">Ссылка</th>
      </tr>
      <? foreach ($var['var']['list']['purchase'] as $key => $purchase): ?>
        <tr class="<?= $purchase['class'] ?>">
          <td class="purchase-name">
            <a href="<?= $purchase['url_set'] ?>"><?= $purchase['name'] ?></a>
          </td>
          <td class="sum center"><?= $purchase['sum'] ?> &#8381;</td>
          <td class="link center">
          <span class="button">
            <a href="<?= $purchase['url'] ?>" class="external-link" target="_blank">
              <div class="glyph external-link"></div>
            </a>
            <span>Перейти в закупку на сайте СП</span>
          </span>
          </td>
        </tr>
      <? endforeach; ?>
    </table>

    <?= $var['var']['list']['pager'] ?>

  <? endif; ?>

<? else: ?>
  <table class="zebra">
    <tr>
      <th class="purchase-name">Имя закупки</th>
      <th class="sum center">Найдено, руб</th>
      <th class="link center">Ссылка</th>
    </tr>
    <tr>
      <td colspan="3">Ваш список закупок пуст.</td>
    </tr>
  </table>
<? endif; ?>

<!-- end purchase_all.tpl.php -->