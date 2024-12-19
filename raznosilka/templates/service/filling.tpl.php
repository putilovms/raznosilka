<!-- start filling.tpl.php -->

<h1>Автопроставление оплат</h1>

<? // var_dump($var['var']['purchase']) ?>

<? if (!empty($var['var']['purchase'])) : ?>

  <p><b>Закупка: <a href="<?= $var['var']['purchase']['url'] ?>" target='_blank'><?= $var['var']['purchase'][PURCHASE_NAME] ?></a></b></p>

  <? if (!empty($var['var']['purchase']['lots'])) : ?>

    <p id="start-filling">
      Запущен процесс автопроставления оплат.
    </p>
    <p id="stop-filling" style="display: none">
      Автопроставление оплат завершено.
    </p>

    <div class="progress-wrapper">
      <progress id="progress-bar" value="0" max="0"></progress>
      <div id="progress-text" title="Количество оплат обработанных / найденных"></div>
    </div>

    <table class="zebra">
      <tr>
        <td>
          <span id="check-filling-sum" class="update pointer" title="Сверить сумму найденную «Разносилкой» с суммой «Денег сдано» на сайте СП.">
            <img src="<?= URL::to('files/images/update.png') ?>">
          </span>
          Сумма найденная «Разносилкой»
        </td>
        <td class="sum right">
          <b><span id="total-found-money" class="unknown" title="Жёлтый - суммы ещё не сверялись. <?="\r\n"?>Зелёный - сумма совпадает с сайтом СП. <?="\r\n"?>Красный - сумма не совпадает с сайтом СП."><?= $var['var']['purchase']['count_total_found_money'] ?> &#8381;</span></b>
        </td>
      </tr>
    </table>
  <? endif; ?>

  <h2>Информация об оплатах</h2>

  <? if (!empty($var['var']['purchase']['lots'])) : ?>

    <? foreach ($var['var']['purchase']['lots'] as $keyLot => $lot) : ?>
      <div id="lot-<?= $keyLot ?>" class="lot warning">

        <div class="lot-overlay" style="display: none">
          <div class="spinner"></div>
        </div>

        <p class="user-number" title="Номер заказа"><b>#<?= $keyLot + 1 ?></b></p>

        <p><b><a href="<?= $lot['url'] ?>" target='_blank' title="Ник участника закупки"><?= $lot[USER_PURCHASE_NICK] ?></a></b></p>

        <p title="Ф.И.О. участника закупки"><?= $lot[USER_PURCHASE_NAME] ?></p>

        <table class="zebra">
          <tr>
            <td>Сумма к внесению участником</td>
            <td class="sum right"><?= $lot['total'] ?> &#8381;</td>
          </tr>
          <tr>
            <td>Найдено раньше</td>
            <td class="sum right"><?= $lot['total_found'] ?> &#8381;</td>
          </tr>
          <tr>
            <td><b>Сумма к внесению на сайт СП</b></td>
            <td class="sum right"><b><?= $lot['total_filling'] ?> &#8381;</b></td>
          </tr>
        </table>

        <div class="no-filling" style="display: none">
          <button filling-again="true">Попробовать снова</button>
          <button filling-manual="true">Проставить вручную</button>
        </div>

      </div>
    <? endforeach; ?>

    <p id="no-lot" style="display: none">Для выбранной закупки не осталось ни одной оплаты требующей проставления.</p>

  <? else : ?>

    <p>Для выбранной закупки нет ни одной оплаты требующей проставления.</p>

  <? endif; ?>

  <input type="button" value="Обзор закупки" onclick="location.href='<?= URL::to('purchase', array('view' => 'not_filling')) ?>'"/>

  <input type="button" value="Выход" onclick="location.href='<?= URL::to('service') ?>'"/>

<? else : ?>

  <p>Для того чтобы проставить оплаты, сначала необходимо найти SMS для выбранной закупки.</p>

  <form action="<?= URL::to('service/analysis') ?>" method="post">
    <button type="submit">Выполнить поиск SMS</button>
  </form>

<? endif; ?>

<!-- end filling.tpl.php -->