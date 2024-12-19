<p style='font-size: 1.4em; font-weight: bold;'>Восстановление пароля</p>
<p>Ваши реквизиты для доступа в «<a href='<?= URL::base() ?>'>Разносилку</a>»:</p>
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
</table>