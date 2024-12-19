<? // var_dump($var['user']) ?>

<p style='font-size: 1.4em; font-weight: bold;'>Добро пожаловать</p><p>Ваш аккаунт успешно активирован.</p>
<p>Теперь вам нужно <a href='<?= URL::to('help/extension') ?>'>установить расширение для браузера</a> и <a href='<?= URL::to('help/gift') ?>'>получить пробный период</a>, после чего вы сможете использовать все возможности сервиса.</p>
<p>Для того чтобы войти в «<a href='<?= URL::base() ?>'>Разносилку</a>» используйте следующие реквизиты:</p>
<table cellspacing='0' cellpadding='0' border='0'>
<tr>
  <td style='width: 30px;'></td>
  <td style='font-weight: bold; text-align: right; padding: 5px;'>Логин:</td>
  <td style='padding: 5px;'><?= $var['user'][USER_LOGIN] ?></td>
</tr>
<tr>
  <td style='width: 30px;'></td>
  <td style='font-weight: bold; text-align: right; padding: 5px;'>Пароль:</td>
  <td style='padding: 5px;'><?= $var['user'][USER_PASSWORD] ?></td>
</tr>
</table><p>Спасибо за ваш выбор!</p>