<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
  <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'>
</head>
<body bgcolor='#ffffff' text='#000000'>
<?php require $var['template'] ?>
<? if ($var['mode'] == 'debug') : ?>
  <p style='font-size: 0.8em;'><b>Это письмо перенаправлено на электронный адрес администратора «Разносилки», так как сервис находится в режиме отладки.</b></p>
<? endif; ?>
<p>«Разносилка» © 2014<?= (date('Y') > 2014) ? "-" . date('Y') : '' ?> Михаил Путилов</p>
</body>
</html>