<!-- start first_steps.tpl.php -->

<h1>Первые шаги</h1>

<p>Самое первое, что необходимо сделать:</p>

<ul>
  <li>
    <a href="<?= URL::to('help/register') ?>">Зарегистрировать аккаунт</a>
  </li>
  <li>
    <a href="<?= URL::to('help/activation') ?>">Активировать аккаунт</a>
  </li>
  <li>
    <a href="<?= URL::to('help/extension') ?>">Установить расширение</a>
  </li>
  <li>
    <a href="<?= URL::to('help/gift') ?>">Получить пробный период</a>
  </li>
</ul>

<p>Чтобы проставить свои первые оплаты при помощи «Разносилки», вам нужно: </p>

<ul>
  <li>
    <a href="<?= URL::to('help/import_sms') ?>">Импортировать SMS из телефона</a>
  </li>
  <li>
    <a href="<?= URL::to('help/upload_sms') ?>">Загрузить SMS в «Разносилку»</a>
  </li>
  <li>
    <a href="<?= URL::to('help/select_purchase') ?>">Выбрать закупку</a>
  </li>
  <li>
    Выбрать <a href="<?= URL::to('help/auto_filling') ?>">автоматическое проставление оплат</a>
  </li>
  <li>
    Если необходимо, то <a href="<?= URL::to('help/purchase#manual') ?>">доразнести оплаты вручную</a>
  </li>
</ul>

<!-- end first_steps.tpl.php -->