<!-- start purchase.tpl.php -->

<h1>Обзор и редактирование закупки</h1>

<? // var_dump($var['var']['purchase']) ?>

<? if ($var['var']['purchase']['select'] === false) : ?>

  <? #Закупка не выбрана?>

  <p id="load-status" class="hide">Загрузка данных...</p>

  <p>Для того чтобы редактировать закупку, сначала необходимо её выбрать.</p>

  <form action="<?= URL::to('service/purchase_org') ?>" method="post">
    <button type="submit">Выбрать закупку</button>
  </form>

<? else : ?>

  <? # Закупка выбрана ?>

  <form action="<?= URL::to('purchase/update') ?>" method="post">
    <b>Закупка:
      <a href="<?= $var['var']['purchase']['select']['url'] ?>" target='_blank'><?= $var['var']['purchase']['select'][PURCHASE_NAME] ?></a></b>
    <button type="submit" class="editor fix-width" title="Обновление данных о закупке">
      <div class="glyph refresh"></div>
    </button>
  </form>

  <p id="load-status">Загрузка данных...</p>

  <div id="editor-page" class="hide">
    <div class="article">
    <label>Выберите какие заказы будут показаны: <select id="editor-filter" name='view'></select> </label>
    </div>

    <div id="no-pay" class="hide">
      <p><b>В закупке нет ни одного заказа удовлетворяющего заданным условиям.</b></p>
    </div>

    <div id="lots-wrapper"></div>

    <div id="lost-lots-wrapper"></div>

    <dl class="details">
      <dt><i>Расшифровка цветовых обозначений</i></dt>
      <dd>
        <table class="zebra legend">
          <tr>
            <td colspan="2" class="bold">Оплаты</td>
          </tr>
          <tr class="pay normal">
            <th>&nbsp;</th>
            <td>Оплата не имеет никаких проблем.</td>
          </tr>
          <tr class="pay warning">
            <th>&nbsp;</th>
            <td>Сумма оплаты и SMS не совпадают.</td>
          </tr>
          <tr class="pay error">
            <th>&nbsp;</th>
            <td>Возможные варианты: оплата проставлена при помощи SMS, которая содержит сообщение; оплата не проставлена.</td>
          </tr>
          <tr class="pay inactive">
            <th>&nbsp;</th>
            <td>Оплата отмечена как ошибочная.</td>
          </tr>
          <tr>
            <td colspan="2" class="bold">Заказы</td>
          </tr>
          <tr class="lot normal">
            <th>&nbsp;</th>
            <td>Заказ не имеет никаких проблем.</td>
          </tr>
          <tr class="lot warning">
            <th>&nbsp;</th>
            <td>Сумма заказа, найденная «Разносилкой», больше, чем должен оплатить участник закупки.</td>
          </tr>
          <tr class="lot error">
            <th>&nbsp;</th>
            <td>Возможные варианты: сумма заказа, найденная «Разносилкой», меньше, чем должен оплатить участник закупки; сумма найденная «Разносилкой» и сумма внесённая на сайт СП не совпадает; заказ имеет непроставленную оплату; в данном заказе имеется оплата, проставленная при помощи SMS, которая содержит сообщение.</td>
          </tr>
          <tr class="lot inactive">
            <th>&nbsp;</th>
            <td>Неактивный заказ.</td>
          </tr>
        </table>
      </dd>
    </dl>

    <h2>Общая статистика по закупке</h2>
    <table id="editor-statistic" class="zebra purchase-statistic">
      <tbody>

      </tbody>
    </table>

  </div>

<? endif; ?>

<!-- end purchase.tpl.php -->