<!-- start detector.tpl.php -->

<h1>Детектор нераспознанных СМС</h1>

<? if (!empty($var['var']['detect'])): ?>

  <? # Отчёт о распознании СМС?>
  <h2>Отчёт</h2>

  <h3>Таблица 'sms_unknown'</h3>
  <ul>
    <li>
      <? # Все СМС которые были удалены, изначально были выбраны для обработки ?>
      Обработано нераспознанных SMS: <b><?= count($var['var']['detect']['delete_unknown']) ?> шт</b>.
    </li>
    <li>
      <? # Разница между удалёнными и вновь добавлыенными нераспознанными СМС, есть количество распознанных ?>
      Распознанно ранее нераспознанных SMS:
      <b><?= (count($var['var']['detect']['delete_unknown']) - count($var['var']['detect']['save_unknown'])) ?> шт</b>.
    </li>
    <li>
      Не удалось распознать SMS: <b><?= count($var['var']['detect']['save_unknown']) ?> шт</b>.
    </li>
  </ul>

  <h3>Таблица 'sms'</h3>
  <ul>
    <li>
      Сохранено в базу данных SMS: <b><?= count($var['var']['detect']['save']) ?> шт</b>.
    </li>
    <li>
      Найдено повторяющихся SMS: <b><?= count($var['var']['detect']['not_save']) ?> шт</b>.
    </li>
  </ul>

<? endif; ?>

<? if (!empty($var['var']['detector']['sms'])): ?>
  <h2>Управление нераспознанными SMS</h2>

  <p>Всего нераспознанных SMS: <b><?= $var['var']['detector']['sms_count'] ?> шт</b>, из них новых:
    <b><?= $var['var']['detector']['count_new'] ?> шт</b>.</p>

  <div class="control-wrapper table">
    <label> <input id="select-all" type="checkbox" name="select-all"> </label>

    <div class="action-wrapper">
      <label> <select id="select-action" name="action" disabled>
          <option value="" selected>- Выберите действие -</option>
          <option value="delete">Удалить выбранные SMS</option>
          <option value="detect">Распознать выбранные SMS</option>
        </select> </label>
    </div>
  </div>

  <form action="" name="control" method="post">

    <div class="article">
      <?= $var['var']['detector']['pager'] ?>
    </div>

    <table class="zebra detector">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th class="time center">Время</th>
          <th>Текст СМС</th>
        </tr>
      </thead>
      <tbody>
        <? foreach ($var['var']['detector']['sms'] as $key => $value): ?>
          <tr selectable="true" class="pointer <?= $value['new'] ?>">
            <td>
              <span class="button">
                <label> <input type="checkbox" name="<?= $value[SMS_UNKNOWN_ID] ?>">
                </label>
                <span>Выбрать текущую SMS</span>
              </span>
            </td>
            <td class="center"><?= $value[SMS_UNKNOWN_TIME] ?></td>
            <td><?= $value[SMS_UNKNOWN_TEXT] ?></td>
          </tr>
        <? endforeach; ?>
      </tbody>
    </table>

    <?= $var['var']['detector']['pager'] ?>

    <input type="hidden" name="action" id="hidden-action" value="">
    <button type="button" onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>
    <button type="submit" name="action" value="detect_all">Распознать все SMS</button>
  </form>


<? else: ?>
  <p>Нераспознанных SMS нет.</p>
  <button onclick="location.href='<?= URL::to('admin') ?>'">Назад</button>
<? endif; ?>

<!-- end detector.tpl.php -->