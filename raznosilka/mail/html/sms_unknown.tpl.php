<p style='font-size: 1.4em; font-weight: bold;'>Найдены неопределённые SMS</p>
<p>Количество неопределённых SMS - <b><?= count($var['sms']) ?> шт</b>.</p>
<table cellspacing='0' cellpadding='0' border='0'>
<? foreach ($var['sms'] as $key => $sms) : ?>
<tr>
  <td style='width: 30px;'></td>
  <td style='padding: 5px;' valign='top'><b><?= $key + 1 ?>.</b></td>
  <td style='padding: 5px;'><?= $sms[SMS_UNKNOWN_TEXT] ?></td>
</tr>
<? // Ограничение на количество СМС ?>
<? if ($key == 20) : ?>
<tr>
  <td style='width: 30px;'></td>
  <td style='padding: 5px;' valign='top'></td>
  <td style='padding: 5px;'>...</td>
</tr>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
</table>
<p><a href='<?= URL::to('admin/detector') ?>'>Управление нераспознанными SMS</a></p>