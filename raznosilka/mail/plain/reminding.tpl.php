Напоминание.<?= "\r\n" ?>
У вас есть закупки, в которых сегодня нужно проставить оплаты:<?= "\r\n" ?>
<? foreach ($var['reminding'] as $reminder) : ?>
  <?= $reminder[PURCHASE_NAME] ?> (<?= URL::to('service/set_purchase', array('purchase' => $reminder[PURCHASE_ID])) ?>)<?= "\r\n" ?>
<? endforeach; ?>
Отключить напоминания можно в настройках уведомлений по e-mail (<?= URL::to('user/notify') ?>).