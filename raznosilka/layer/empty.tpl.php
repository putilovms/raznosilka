<!DOCTYPE html>

<html>

<head>
  <title><?= $var['title'] ?></title>
  <meta name="theme-color" content="#009688">
  <link rel="stylesheet" type="text/css" href="<?= URL::to('files/style.css') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0"/>
  <meta charset="utf-8">
</head>

<body class="empty">

<div id="page-wrapper">
  <div id="page">
    <div class="main-content-wrapper">
      <div class="main-content">
        <?php require_once $var['content'] ?>
      </div>
    </div>
  </div>
</div>

</body>
</html>