<p style='font-size: 1.4em; font-weight: bold;'>Напоминание</p>
<p>У вас есть закупки, в которых сегодня нужно проставить оплаты:</p>
<table cellspacing='0' cellpadding='0' border='0'>
<? foreach ($var['reminding'] as $reminder) : ?>
<tr>
  <td style='width: 30px;'></td>
  <td style='padding: 5px;'><a href='<?= URL::to('service/set_purchase', array('purchase' => $reminder[PURCHASE_ID])) ?>'><?= $reminder[PURCHASE_NAME] ?></a></td>
</tr>
<? endforeach; ?>
</table>
<p style='font-size:0.8em;'>Отключить напоминания можно в настройках <a href='<?= URL::to('user/notify') ?>'>уведомлений по e-mail</a>.</p>