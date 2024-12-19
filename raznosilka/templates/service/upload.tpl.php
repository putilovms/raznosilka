<!-- start upload.tpl.php -->

<?
/**
 * Рисует нумерованную таблицу с двумя колонками
 * @param array $arr Массив с SMS
 */
function printTableTwoCol ($arr) {
  $result = "
    <table class='zebra'>
      <thead>
        <tr>
          <th>№</th>
          <th class='time'>Время получения</th>
          <th>Текст SMS</th>
        </tr>
      </thead>
    ";
  foreach ($arr as $key => $sms) {
    $result .= "
      <tr>
        <td>" . ++$key . "</td>
        <td>" . strftime('%d.%m.%Y %H:%M', strtotime($sms[SMS_TIME_SMS])) . "</td>
        <td>" . $sms[SMS_UNKNOWN_TEXT] . "</td>
      </tr>
      ";
  }
  $result .= "</table>";
  print $result;
}

/**
 * Рисует нумерованную таблицу с шестью колонками
 * @param array $arr Массив с SMS
 */
function printTableSixCol ($arr) {
  $result = "
    <table class='zebra'>
      <thead>
        <tr>
          <th>№</th>
          <th class='time'>Время SMS</th>
          <th class='time'>Время оплаты</th>
          <th>Сумма</th>
          <th>Карта</th>
          <th>Ф.И.О.</th>
          <th>Сообщ.</th>
        </tr>
      </thead>
    ";
  foreach ($arr as $key => $sms) {
    $result .= "
      <tr>
        <td>" . ++$key . "</td>
        <td>" . (($sms[SMS_TIME_SMS] == '0000-00-00 00:00:00') ? '' : strftime('%d.%m.%y %H:%M', strtotime($sms[SMS_TIME_SMS]))) . "</td>
        <td>" . (($sms[SMS_TIME_PAY] == '0000-00-00 00:00:00') ? '' : strftime('%d.%m.%y %H:%M', strtotime($sms[SMS_TIME_PAY]))) . "</td>
        <td>" . $sms[SMS_SUM_PAY] . "</td>
        <td>" . (($sms[SMS_CARD_PAYER] >= 0) ? sprintf("%04d", $sms[SMS_CARD_PAYER]) : '') . "</td>
        <td>" . $sms[SMS_FIO] . "</td>
        <td>" . $sms[SMS_COMMENT] . "</td>
      </tr>
      ";
  }
  $result .= "</table>";
  print $result;
}

?>

<? if (isset($var['var']['upload'])): ?>

  <h1>Отчёт</h1>

  <div class="article-wrapper">

    <? if ($var['system']['mode'] != 'debug'): ?>

      <? # Отчёт в обычном режиме?>

      <p>Загрузка SMS в «Разносилку» прошла успешно:</p>

      <table class="zebra">
        <tr>
          <td>Всего обработано SMS</td>
          <td class="right number"><?= count($var['var']['upload']['total']['separated']) ?></td>
        </tr>
        <tr class="bold">
          <td class="bold">Всего добавлено SMS в базу данных</td>
          <td class="right number bold"><?= count($var['var']['upload']['total']['save']) ?></td>
        </tr>
        <tr>
          <td>Не добавлено SMS, так как они уже содержатся в базе данных</td>
          <td class="right number"><?= count($var['var']['upload']['total']['not_save']) ?></td>
        </tr>
        <tr>
          <td>Не добавлено SMS, так как они не содержат данных об оплатах</td>
          <td class="right number"><?= count($var['var']['upload']['total']['all_unknown']) ?></td>
        </tr>
      </table>

      <dl class="details">
        <dt><i>Список SMS, которые не содержат данных об оплатах</i></dt>
        <dd>
          <? if (!empty($var['var']['upload']['total']['all_unknown'])): ?>

            <? printTableTwoCol($var['var']['upload']['total']['all_unknown']) ?>

          <? endif; ?>
        </dd>
      </dl>

      <p class="hint">SMS из данного списка не сохраняются в «Разносилке» и отображаются только для вашего удобства.</p>

    <? else: ?>

      <? # Отчёт в режиме отладки?>
      <h2>Информация о файлах</h2>
      <div class="article">

        <dl class="details">
          <dt><i>Загружаемые файлы: <b><?= count($var['var']['upload']['files']['load']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['files']['load'])): ?>
              <ul>
                <? foreach ($var['var']['upload']['files']['load'] as $file): ?>
                  <li>
                    <?= $file['name'] ?>
                  </li>
                <? endforeach; ?>
              </ul>
            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Сохранённые файлы: <b><?= count($var['var']['upload']['files']['save']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['files']['save'])): ?>
              <ul>
                <? foreach ($var['var']['upload']['files']['save'] as $file): ?>
                  <li>
                    <?= $file['name'] ?>
                  </li>
                <? endforeach; ?>
              </ul>
            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Обработанные файлы: <b><?= count($var['var']['upload']['files']['processed']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['files']['processed'])): ?>
              <ul>
                <? foreach ($var['var']['upload']['files']['processed'] as $file): ?>
                  <li>
                    <?= $file['name'] ?>
                  </li>
                <? endforeach; ?>
              </ul>
            <? endif; ?>
          </dd>
        </dl>
      </div>

      <? if (!empty($var['var']['upload']['sms'])): ?>

        <h2>Обработка файлов</h2>

        <div class="article">

          <? foreach ($var['var']['upload']['sms'] as $fileSMS): ?>
            <dl class="details">
              <dt><i><b><?= $fileSMS['file']['name'] ?></b></i></dt>
              <dd>

                <div class="article">

                  <dl class="details">
                    <dt><i>Исходный файл: <b><?= count($fileSMS['sms_file']) ?> стр</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['sms_file'])): ?>

                        <? printTableTwoCol($fileSMS['sms_file']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Склеенные SMS: <b><?= count($fileSMS['glued']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['glued'])): ?>

                        <? printTableTwoCol($fileSMS['glued']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Рассоединённые SMS: <b><?= count($fileSMS['unglued']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['unglued'])): ?>

                        <? printTableTwoCol($fileSMS['unglued']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Бесполезные SMS: <b><?= count($fileSMS['detected_unknown']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['detected_unknown'])): ?>

                        <? printTableTwoCol($fileSMS['detected_unknown']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Неопределённые SMS: <b><?= count($fileSMS['unknown']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['unknown'])): ?>

                        <? printTableTwoCol($fileSMS['unknown']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Новые неопределённые SMS: <b><?= count($fileSMS['save_unknown']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['save_unknown'])): ?>

                        <? printTableTwoCol($fileSMS['save_unknown']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Распознанные SMS: <b><?= count($fileSMS['processed']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['processed'])): ?>

                        <? printTableSixCol($fileSMS['processed']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Повторяющиеся SMS: <b><?= count($fileSMS['not_save']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['not_save'])): ?>

                        <? printTableSixCol($fileSMS['not_save']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                  <dl class="details">
                    <dt><i>Сохранённые SMS: <b><?= count($fileSMS['save']) ?> шт</b>.</i></dt>
                    <dd>
                      <? if (!empty($fileSMS['save'])): ?>

                        <? printTableSixCol($fileSMS['save']) ?>

                      <? endif; ?>
                    </dd>
                  </dl>

                </div>
              </dd>
            </dl>
          <? endforeach; ?>
        </div>

      <? endif; ?>

      <h2>Общая информация</h2>
      <div class="article">

        <dl class="details">
          <dt><i>Всего в исходных файлах: <b><?= count($var['var']['upload']['total']['sms_file']) ?> стр</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['sms_file'])): ?>

              <? printTableTwoCol($var['var']['upload']['total']['sms_file']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего склеенных SMS: <b><?= count($var['var']['upload']['total']['glued']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['glued'])): ?>

              <? printTableTwoCol($var['var']['upload']['total']['glued']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего рассоединённых SMS: <b><?= count($var['var']['upload']['total']['unglued']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['unglued'])): ?>

              <? printTableTwoCol($var['var']['upload']['total']['unglued']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего бесполезных SMS: <b><?= count($var['var']['upload']['total']['detected_unknown']) ?> шт</b>.</i>
          </dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['detected_unknown'])): ?>

              <? printTableTwoCol($var['var']['upload']['total']['detected_unknown']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего неопределённых SMS: <b><?= count($var['var']['upload']['total']['unknown']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['unknown'])): ?>

              <? printTableTwoCol($var['var']['upload']['total']['unknown']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего новых неопределённых SMS: <b><?= count($var['var']['upload']['total']['save_unknown']) ?> шт</b>.</i>
          </dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['save_unknown'])): ?>

              <? printTableTwoCol($var['var']['upload']['total']['save_unknown']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего распознанных SMS: <b><?= count($var['var']['upload']['total']['processed']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['processed'])): ?>

              <? printTableSixCol($var['var']['upload']['total']['processed']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего повторяющихся SMS: <b><?= count($var['var']['upload']['total']['not_save']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['not_save'])): ?>

              <? printTableSixCol($var['var']['upload']['total']['not_save']) ?>

            <? endif; ?>
          </dd>
        </dl>

        <dl class="details">
          <dt><i>Всего сохранено SMS: <b><?= count($var['var']['upload']['total']['save']) ?> шт</b>.</i></dt>
          <dd>
            <? if (!empty($var['var']['upload']['total']['save'])): ?>

              <? printTableSixCol($var['var']['upload']['total']['save']) ?>

            <? endif; ?>
          </dd>
        </dl>
      </div>

      <div class="article">
        <h2>Информация о работе скрипта</h2>
        <ul>
          <li>Времени для обработки SMS потребовалось: <b><?= $var['var']['info']['_time'] ?> сек</b>.</li>
          <li>Памяти для обработки SMS потребовалось: <b><?= $var['var']['info']['_memory'] ?> Mb</b>.</li>
        </ul>
      </div>

    <? endif; ?>

    <div class="article">
      <input type="button" value="Загрузить ещё" onclick="location.href='<?= URL::to('service/upload') ?>'"/>
      <input type="button" value="Выход" onclick="location.href='<?= URL::to('service') ?>'"/>
    </div>
  </div>

<? else: ?>

  <h1>Загрузка SMS</h1>

  <p>Пожалуйста, выберете файлы с SMS и нажмите кнопку «Загрузить».</p>
  <form class="load" action="" method="post" enctype="multipart/form-data">
    <ul>
      <li>
        <input name="files[]" type="file" size="30" required="required" multiple="multiple"><br>

        <p class="hint">Максимальное количество файлов которое можно загрузить - <?= ini_get('max_file_uploads') ?> шт.</p>
      </li>
      <li>
        <span class="icon-required"></span> - обязательно для заполнения.
      </li>
      <li>
        <input type="submit" value="Загрузить">
        <input type="button" value="Выход" onclick="location.href='<?= URL::to('service') ?>'"/>
      </li>
    </ul>
  </form>

<? endif; ?>

<!-- end upload.tpl.php -->