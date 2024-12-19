<!-- start info.tpl.php -->

<h1>Отчёт о состоянии сервиса</h1>

<? // var_dump($var['var']) ?>

<h2>Система</h2>

<table class="zebra">
  <tr>
    <td><b>Веб-сервер</b></td>
    <td class="right"><?= $var['var']['info']['server']['server_version'] ?></td>
  </tr>
  <tr>
    <td><b>Версия MySQL</b></td>
    <td class="right"><?= $var['var']['info']['mysql']['mysql_version'] ?></td>
  </tr>
  <tr>
    <td><b>Версия PHP</b></td>
    <td class="right"><?= $var['var']['info']['php']['php_version'] ?></td>
  </tr>
  <tr>
    <td><b>Доступно оперативной памяти</b></td>
    <td class="right"><?= $var['var']['info']['php']['memory_limit'] ?></td>
  </tr>
  <tr>
    <td><b>Максимальное количество файлов для загрузки</b></td>
    <td class="right"><?= $var['var']['info']['php']['file_uploads'] ?></td>
  </tr>
  <tr>
    <td><b>Максимальный размер загружаемого файла</b></td>
    <td class="right"><?= $var['var']['info']['php']['max_filesize'] ?></td>
  </tr>
  <tr>
    <td><b>Модуль Curl</b></td>
    <td class="right">
      <? if ($var['var']['info']['php']['curl']): ?>
        <span class="">Включен</span>
      <? else: ?>
        <span class="error">Не найден</span>
      <? endif; ?>
    </td>
  </tr>
  <tr>
    <td><b>Модуль mcrypt</b></td>
    <td class="right">
      <? if ($var['var']['info']['php']['mcrypt']): ?>
        <span class="">Включен</span>
      <? else: ?>
        <span class="error">Не найден</span>
      <? endif; ?>
    </td>
  </tr>

  <tr>
    <td><b>Модуль zip</b></td>
    <td class="right">
      <? if ($var['var']['info']['php']['zip']): ?>
        <span class="">Включен</span>
      <? else: ?>
        <span class="error">Не найден</span>
      <? endif; ?>
    </td>
  </tr>

</table>

<h2>Сервис</h2>
<table class="zebra settings">
    <tr>
      <th>Запуск хрона</th>
      <td class="right"><?= $var['var']['info']['cron']['cron_last_run'] ?></td>
    </tr>
  <tr>
    <th>Запуск рассылки</th>
    <td class="right"><?= $var['var']['info']['cron']['delivery_last_run'] ?></td>
  </tr>
</table>

<h2>Платёжная система</h2>

<? if (!empty($var['var']['info']['payment_system'])) : ?>

  <table class="zebra settings">
    <? foreach ($var['var']['info']['payment_system'] as $param) : ?>
      <tr>
        <th><?= $param['name'] ?></th>
        <td class="right"><?= $param['value'] ?></td>
      </tr>
    <? endforeach; ?>
  </table>

<? else: ?>
  <p>Платёжная система не подключена.</p>
<? endif; ?>

<h2>Заказы</h2>

<table class="zebra settings">
  <tr>
    <th title="Всего клиентов которым оказывается услуга в данный момент">Клиентов сейчас</th>
    <td class="right"><?= $var['var']['info']['orders']['run'] ?> шт.</td>
  </tr>
  <tr>
    <th title="Всего денег заплачено пользователями во всех платёжных системах">Денег заплачено</th>
    <td class="right"><?= $var['var']['info']['orders']['sum_paid'] ?> ₽</td>
  </tr>
  <tr>
    <th title="Всего денег получено из всех платёжных систем">Денег получено</th>
    <td class="right"><?= $var['var']['info']['orders']['sum'] ?> ₽</td>
  </tr>
  <tr>
    <th title="Всего заказов куплено через платёжные системы">Куплено заказов</th>
    <td class="right"><?= $var['var']['info']['orders']['payment_order'] ?> шт.</td>
  </tr>
  <tr>
    <th title="Суммарное количество дней, на протяжении которых оказывалась или будет оказыватеся услуга всем клиентам, оплатившим её через платёжные системы">Куплено дней</th>
    <td class="right"><?= $var['var']['info']['orders']['payment_day'] ?> дн.</td>
  </tr>
  <tr>
    <th title="Всего заказов добавлено вручную (без учёта пробного периода)">Добавлено заказов</th>
    <td class="right"><?= $var['var']['info']['orders']['manual_order'] ?> шт.</td>
  </tr>
  <tr>
    <th title="Суммарное количество дней, на протяжении которых оказывалась или будет оказыватеся услуга всем клиентам, на основании ручного добавления заказа (без учёта пробного периода)">Добавлено дней</th>
    <td class="right"><?= $var['var']['info']['orders']['manual_day'] ?> дн.</td>
  </tr>
</table>

<? // var_dump($var['var']['info']['users_info'])?>

<h2>Пользователи</h2>

<table class="zebra settings">
  <tr>
    <th title="Всего зарегистрировано пользователей в сервисе">Зарегистрированных</th>
    <td class="right"><?= $var['var']['info']['users_info']['users_count'] ?></td>
  </tr>
  <tr>
    <th title="Всего активировано аккаунтов в сервисе">Активированных</th>
    <td class="right"><?= $var['var']['info']['users_info']['activate_count'] ?></td>
  </tr>
  <tr>
    <th title="Всего аккаунтов получили ID организатора">Имеют OrgID</th>
    <td class="right"><?= $var['var']['info']['users_info']['bind_count'] ?></td>
  </tr>
  <tr>
    <th title="Всего аккаунтов с введённым логином и паролем от сайта СП">С доступом к СП</th>
    <td class="right"><?= $var['var']['info']['users_info']['have_login_count'] ?></td>
  </tr>
  <tr>
    <th title="Всего заблокированных аккаунтов">Заблокированных</th>
    <td class="right"><?= $var['var']['info']['users_info']['blocked_count'] ?></td>
  </tr>
</table>

<h2>База данных</h2>

<table class="zebra">
  <tr>
    <td><b>Размер базы данных</b></td>
    <td class="right"><?= $var['var']['info']['mysql']['size_db'] ?> Мб</td>
  </tr>
</table>

<table class="zebra settings">
  <tr>
    <th>users</th>
    <td class="right"><?= $var['var']['info']['db']['users'] ?></td>
  </tr>
  <tr>
    <th>sms</th>
    <td class="right"><?= $var['var']['info']['db']['sms'] ?></td>
  </tr>
  <tr>
    <th>sms_unknown</th>
    <td class="right"><?= $var['var']['info']['db']['sms_unknown'] ?></td>
  </tr>
  <tr>
    <th>pay</th>
    <td class="right"><?= $var['var']['info']['db']['pay'] ?></td>
  </tr>
  <tr>
    <th>correction</th>
    <td class="right"><?= $var['var']['info']['db']['correction'] ?></td>
  </tr>
  <tr>
    <th>purchase</th>
    <td class="right"><?= $var['var']['info']['db']['purchase'] ?></td>
  </tr>
  <tr>
    <th>users_purchase</th>
    <td class="right"><?= $var['var']['info']['db']['users_purchase'] ?></td>
  </tr>
  <tr>
    <th>sp</th>
    <td class="right"><?= $var['var']['info']['db']['sp'] ?></td>
  </tr>
  <tr>
    <th>orders</th>
    <td class="right"><?= $var['var']['info']['db']['orders'] ?></td>
  </tr>
  <tr>
    <th><?= $var['var']['info']['db']['payment_system']['name'] ?></th>
    <td class="right"><?= $var['var']['info']['db']['payment_system']['count'] ?></td>
  </tr>
</table>

<button onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>

<!-- end info.tpl.php -->