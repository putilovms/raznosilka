<p style='font-size: 1.4em; font-weight: bold;'>Создан новый аккаунт</p><p>Данные о новом пользователе:</p>
<table cellspacing='0' cellpadding='0' border='0'>
<tr>
  <td style='width: 30px;'></td>
  <td style='font-weight: bold; text-align: right; padding: 5px;'>ID:</td>
  <td style='padding: 5px;'><?= $var['user'][USER_ID] ?></td>
</tr>
<tr>
  <td style='width: 30px;'></td>
  <td style='font-weight: bold; text-align: right; padding: 5px;'>Логин:</td>
  <td style='padding: 5px;'><?= $var['user'][USER_LOGIN] ?></td>
</tr>
<tr>
  <td style='width: 30px;'></td>
  <td style='font-weight: bold; text-align: right; padding: 5px;'>E-mail:</td>
  <td style='padding: 5px;'><?= $var['user'][USER_EMAIL] ?></td>
</tr>
</table>