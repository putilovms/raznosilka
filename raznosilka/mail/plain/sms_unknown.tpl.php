Найдены неопределённые SMS.<?= "\r\n" ?>
Количество неопределённых SMS - <?= count($var['sms']) ?> шт.<?= "\r\n" ?>
<? foreach ($var['sms'] as $key => $sms) : ?>
  <?= $key + 1 ?>. <?= $sms[SMS_UNKNOWN_TEXT] ?><?= "\r\n" ?>
<? // Ограничение на количество СМС ?>
<? if ($key == 20) : ?>
  ...<?= "\r\n" ?>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
Управление нераспознанными SMS (<?= URL::to('admin/detector') ?>)