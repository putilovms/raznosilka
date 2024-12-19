<!-- start service.tpl.php -->

<h1>Инструменты</h1>

<? // todo сделать предварительную подготовку данных для вывода в моделе ?>

<div id="tools">
  <ul class="menu">

    <li class="<?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
      <? if ($var['user']['access_to_tools']) : ?>
      <a href="<?= URL::to('service/purchase_org') ?>">
        <? endif; ?>

        <div class="icon check <?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
          <h2>Выбрать закупку</h2>

          <? if ($var['var']['select'] === false): ?>
            <p class="description">Закупка не выбрана</p>
          <? else : ?>
            <p class="description">Сейчас выбрана: <b><?= $var['var']['select'][PURCHASE_NAME] ?></b></p>
          <? endif; ?>

        </div>

        <? if ($var['user']['access_to_tools']) : ?>
      </a>
    <? endif; ?>
    </li>

    <li class="<?= ($var['user']['access_to_tools'] and $var['system']['select']) ? '' : 'disabled' ?>">
      <? if ($var['user']['access_to_tools'] and $var['system']['select']) : ?>
      <a href="<?= URL::to('service/analysis') ?>">
        <? endif; ?>

        <div class="icon auto <?= ($var['user']['access_to_tools'] and $var['system']['select']) ? '' : 'disabled' ?>">
          <h2>Автопроставление</h2>

          <p class="description">Проставление оплат в автоматическом режиме</p>
        </div>

        <? if ($var['user']['access_to_tools'] and $var['system']['select']) : ?>
      </a>
    <? endif; ?>
    </li>

    <li class="<?= ($var['user']['access_to_tools'] and $var['system']['select']) ? '' : 'disabled' ?>">
      <? if ($var['user']['access_to_tools'] and $var['system']['select']) : ?>
      <a href="<?= URL::to('purchase', array('view' => 'not_filling')) ?>">
        <? endif; ?>

        <div class="icon edit <?= ($var['user']['access_to_tools'] and $var['system']['select']) ? '' : 'disabled' ?>">
          <h2>Обзор закупки</h2>

          <p class="description">Проставление оплат в ручном режиме, обзор и редактирование закупки</p>
        </div>

        <? if ($var['user']['access_to_tools'] and $var['system']['select']) : ?>
      </a>
    <? endif; ?>
    </li>

    <li class="<?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
      <? if ($var['user']['access_to_tools']) : ?>
      <a href="<?= URL::to('service/upload') ?>">
        <? endif; ?>

        <div class="icon upload <?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
          <h2>Загрузка SMS</h2>

          <p class="description">Служит для загрузки ваших SMS в «Разносилку»</p>
        </div>

        <? if ($var['user']['access_to_tools']) : ?>
      </a>
    <? endif; ?>
    </li>

    <li class="<?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
      <? if ($var['user']['access_to_tools']) : ?>
      <a href="<?= URL::to('service/search') ?>">
        <? endif; ?>

        <div class="icon return <?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
          <h2>Возврат SMS</h2>

          <p class="description">Отметьте неактуальные SMS после возврата оплаты участнику закупки</p>
        </div>

        <? if ($var['user']['access_to_tools']) : ?>
      </a>
    <? endif; ?>
    </li>

  </ul>
</div>

<!-- end service.tpl.php -->
