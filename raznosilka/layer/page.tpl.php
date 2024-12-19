<!DOCTYPE html>

<html>

<head>
  <meta charset="utf-8">
  <title><?= $var['title'] ?></title>
  <meta name="theme-color" content="#009688">
  <link rel="stylesheet" type="text/css" href="<?= URL::to('files/style.css') ?>">
  <link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/<?= $var['system']['chrome_extension_id'] ?>">
  <script type="text/javascript" src="<?= URL::to('files/script.js') ?>"></script>
  <?= $var['filesJs'] ?>
  <? # Общие данные для работы скрипта ?>
  <script>
    <?= $var['codeJs'] ?>
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0"/>
  <?= $var['meta'] ?>
</head>

<body>

<div id="page-wrapper">

  <? # Боковае меню ?>
  <div id="sidebar-menu">

    <div class="menu-header">
      <p class="login">
        <span class="icon user ellipsis">
          <? if ($var['user']['auth']) {
            print $var['user'][USER_LOGIN];
          } else {
            print "Гость";
          } ?>
        </span>
      </p>
    </div>

    <ul class="menu">
      <? if (!$var['user']['auth']): ?>

        <? # Для гостей?>

        <li>
          <a href="<?= URL::base() ?>"><span class="icon home ellipsis">На главную</span></a>
        </li>
        <li>
          <a href="<?= URL::to('user/register') ?>"><span class="icon register ellipsis">Регистрация</span></a>
        </li>
        <li>
          <a href="<?= URL::to('user/login') ?>"><span class="icon login ellipsis">Вход</span></a>
        </li>

      <? else : ?>

        <? # Для зарегистрированного пользователя ?>

        <li>
          <a href="<?= URL::to('service') ?>"><span class="icon tools ellipsis">Инструменты</span></a>
        </li>

        <li class="separator"></li>

        <? # Список инструментов ?>

        <li class="<?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
          <? if ($var['user']['access_to_tools']) : ?>
            <a href="<?= URL::to('service/purchase_org') ?>">
              <span class="icon check ellipsis">Выбрать закупку</span></a>
          <? else : ?>
            <span class="icon check ellipsis disabled">Выбрать закупку</span>
          <? endif; ?>
        </li>

        <li class="<?= ($var['user']['access_to_tools'] and $var['system']['select']) ? '' : 'disabled' ?>">
          <? if ($var['user']['access_to_tools'] and $var['system']['select']) : ?>
            <a href="<?= URL::to('service/analysis') ?>"><span class="icon auto ellipsis">Автопроставление</span></a>
          <? else : ?>
            <span class="icon auto ellipsis disabled">Автопроставление</span>
          <? endif; ?>
        </li>

        <li class="<?= ($var['user']['access_to_tools'] and $var['system']['select']) ? '' : 'disabled' ?>">
          <? if ($var['user']['access_to_tools'] and $var['system']['select']) : ?>
            <a href="<?= URL::to('purchase', array('view' => 'not_filling')) ?>"><span class="icon edit ellipsis">Обзор закупки</span></a>
          <? else : ?>
            <span class="icon edit ellipsis disabled">Обзор закупки</span>
          <? endif; ?>
        </li>

        <li class="<?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
          <? if ($var['user']['access_to_tools']) : ?>
            <a href="<?= URL::to('service/upload') ?>"><span class="icon upload ellipsis">Загрузка SMS</span></a>
          <? else : ?>
            <span class="icon upload ellipsis disabled">Загрузка SMS</span>
          <? endif; ?>
        </li>

        <li class="<?= ($var['user']['access_to_tools']) ? '' : 'disabled' ?>">
          <? if ($var['user']['access_to_tools']) : ?>
            <a href="<?= URL::to('service/search') ?>"><span class="icon return ellipsis">Возврат SMS</span></a>
          <? else : ?>
            <span class="icon return ellipsis disabled">Возврат SMS</span>
          <? endif; ?>
        </li>

        <li class="separator"></li>

        <? # Работа с аккаунтом ?>

        <li>
          <a href="<?= URL::to('user') ?>"><span class="icon settings ellipsis">Настройки аккаунта</span></a>
        </li>
        <? if ($var['user']['admin']): ?>
          <li>
            <a href="<?= URL::to('admin') ?>"><span class="icon admin ellipsis">Управление сервисом</span></a>
          </li>
        <? endif; ?>
        <li>
          <a href="<?= URL::to('pay') ?>"><span class="icon pay ellipsis">Оплата сервиса</span></a>
        </li>
        <li>
          <a href="<?= URL::to('user/logout') ?>"><span class="icon exit ellipsis">Выход</span></a>
        </li>
      <? endif; ?>

    </ul>
  </div>

  <div id="container" class="active">

    <? # Шапка?>
    <div id="header-wrapper">
      <div id="header" class="active">
        <div class="content">

          <? # Кнопка бокового меню ?>
          <div id="menu-trigger">
            <ul class="menu">
              <li><a id="trigger" href=""><span class="icon menu">Меню</span></a></li>
            </ul>
          </div>

          <? # Логотип ?>
          <div class="logo <?= ($var['user']['auth']) ? 'active' : '' ?>">
            <a href="<?= URL::base() ?>"> <img src="<?= URL::to('files/images/logo.png') ?>"> </a>
          </div>

          <? # Меню?>
          <div id="main-menu">
            <ul class="menu">
              <li class="messages">
                <a href="<?= URL::to('help') ?>" title="Руководство по использованию сервиса «Разносилка»"><span class="icon help only"></span></a>
              </li>
              <? if ($var['user']['auth']): ?><? # Сообщения для зарегистрированного пользователя?>
                <li class="messages">
                  <a href="<?= URL::to('messages') ?>" title="Уведомления от сервиса">

                    <span class="icon messages only <?= ($var['user']['new_msg'] > 0) ? 'new' : '' ?>">
                      <? if ($var['user']['new_msg'] > 0) : ?>
                        <span class="count" title="Количество новых уведомлений от сервиса"><?= $var['user']['new_msg'] ?></span>
                      <? endif; ?>
                    </span>

                  </a>
                </li>
              <? endif; ?>
            </ul>
          </div>

        </div>
      </div>

      <? # Оповещения?>

      <div id="notify-wrapper" class="notify-wrapper">
        <? if (!empty($var['system']['notify'])): ?>

          <? foreach ($var['system']['notify'] as $notify): ?>
            <div class="notify <?= $notify['type'] ?>">
              <p><?= $notify['text'] ?></p>
            </div>
          <? endforeach; ?>

        <? endif; ?>
      </div>

    </div>

    <? # Основное содержание страницы ?>
    <div id="page">

      <? # Кнопка вверх ?>
      <div class="up-down-wrapper">
        <div id="up-down">
          <div class="up-down-img"></div>
        </div>
      </div>

      <div class="main-content-wrapper">
        <div class="main-content">

          <noscript>
            <div class="notify-wrapper">
              <div class="notify error">
                <p>У вашего браузера запрещено выполнение JavaScript. Для полноценной работы «Разносилки» необходимо разрешить выполнение JavaScript.</p>
              </div>
            </div>
          </noscript>

          <? # Контент?>
          <div class="content">
            <?php require_once $var['content'] ?>
          </div>

        </div>
      </div>
    </div>

    <? # Отладка?>
    <? if ($var['system']['mode'] == 'debug'): ?>
      <div id="debug">
        <?php require_once $var['debug'] ?>
      </div>
    <? endif; ?>

    <? # Подвал ?>
    <div id="footer">
      <div class="footer-menu">
        <ul>
          <li><a href="<?= URL::to('pay') ?>">Цены</a></li>
          <li><a href="<?= URL::to('info/user_agreement') ?>">Соглашение</a></li>
          <li><a href="<?= URL::to('info/confidential') ?>">Политика</a></li>
          <li><a href="<?= URL::to('help') ?>">Помощь</a></li>
          <li><a href="mailto:support@raznosilka.ru" target="_blank">Служба&nbsp;поддержки</a></li>
          <li><a href="http://forum.raznosilka.ru/" target="_blank">Форум</a></li>
          <li><a href="<?= URL::to('info/project') ?>">О&nbsp;проекте</a></li>
        </ul>
      </div>
      <div class="footer-logos"></div>
      <div class="footer-copyright">
        <p>
          <b>&laquo;Разносилка&raquo;</b>
          <span class="copyright">&copy;&nbsp;2014<? if (date('Y') > 2014) print "-" . date('Y') ?> Михаил&nbsp;Путилов</span>
        </p>
      </div>
    </div>

  </div>

</div>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
  (function (d, w, c) {
    (w[c] = w[c] || []).push(function () {
      try {
        w.yaCounter36558290 = new Ya.Metrika({
          id: 36558290,
          clickmap: true,
          trackLinks: true,
          accurateTrackBounce: true
        });
      } catch (e) {
      }
    });

    var n = d.getElementsByTagName("script")[0],
      s = d.createElement("script"),
      f = function () {
        n.parentNode.insertBefore(s, n);
      };
    s.type = "text/javascript";
    s.async = true;
    s.src = "https://mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
      d.addEventListener("DOMContentLoaded", f, false);
    } else {
      f();
    }
  })(document, window, "yandex_metrika_callbacks");
</script>
<noscript>
  <div><img src="https://mc.yandex.ru/watch/36558290" style="position:absolute; left:-9999px;" alt=""/></div>
</noscript>
<!-- /Yandex.Metrika counter -->

<!-- Google Analytics counter -->
<script>
  (function (i, s, o, g, r, a, m) {
    i['GoogleAnalyticsObject'] = r;
    i[r] = i[r] || function () {
        (i[r].q = i[r].q || []).push(arguments)
      }, i[r].l = 1 * new Date();
    a = s.createElement(o),
      m = s.getElementsByTagName(o)[0];
    a.async = 1;
    a.src = g;
    m.parentNode.insertBefore(a, m)
  })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

  ga('create', 'UA-75944430-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- /Google Analytics counter -->

</body>
</html>