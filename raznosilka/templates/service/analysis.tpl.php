<!-- start analysis.tpl.php -->

<? // var_dump($var['var']['purchase']['select']) ?>

<h1>Автопоиск оплат</h1>

<? if ($var['var']['purchase']['select'] === false) : ?>

  <? #Закупка не выбрана?>

  <p id="load-status" class="hide">Загрузка данных...</p>

  <p>Для того чтобы проставить оплаты в закупке, сначала необходимо её выбрать.</p>

  <form action="<?= URL::to('service/purchase_org') ?>" method="post">
    <button type="submit">Выбрать закупку</button>
  </form>

<? else : ?>

  <? #Закупка выбрана?>

  <p>
    <b>Закупка:
      <a href="<?= $var['var']['purchase']['select']['url'] ?>" target='_blank'><?= $var['var']['purchase']['select'][PURCHASE_NAME] ?></a></b>
  </p>

  <p id="load-status">Загрузка данных...</p>

  <div id="analysis-page" class="hide">

    <p>Количество оплат для которых удалось найти SMS:
      <span id="count-found-pays" class="bold">0 шт.</span>
    </p>

    <label>Выберите какие заказы будут показаны:

      <select id="analysis-filter" name='view'>
        <option value="">Показать все заказы</option>
        <option value="not-found">Только с найденными оплатами</option>
        <option value="normal" selected>Только с оплатами трубующих вмешательства</option>
      </select>

    </label>

    <p class="hint">Все заказы с оплатами, не трубующих принятия каких-либо решений, скрыты по умолчанию.</p>

    <form name='pay_choice' method='post' action='<?= URL::to('service/filling') ?>'>

      <div id="lots-wrapper"></div>

      <div id="no-pay" class="hide">
        <p><b>Заказов с оплатами требующих вашего вмешательства нет</b></p>
      </div>

      <p>Для того чтобы проставить оплаты с помощью найденных SMS, нажмите на кнопку «Проставить оплаты».</p>

      <input type="submit" value="Проставить оплаты">

    </form>

    <div class="box">
      <dl class="details">
        <dt><i>Расшифровка цветовых обозначений</i></dt>
        <dd>
          <table class="zebra legend">

            <tr>
              <td colspan="2" class="bold">SMS</td>
            </tr>
            <tr class="sms normal">
              <th>&nbsp;</th>
              <td>Не требуется принимать никаких действий</td>
            </tr>
            <tr class="sms warning">
              <th>&nbsp;</th>
              <td>Необходимо выбрать одну из SMS для проставления</td>
            </tr>
            <tr class="sms error">
              <th>&nbsp;</th>
              <td>SMS содержит сообщение от участника закупки</td>
            </tr>

          </table>
        </dd>
      </dl>
    </div>

  </div>

<? endif; ?>

<!-- end analysis.tpl.php -->