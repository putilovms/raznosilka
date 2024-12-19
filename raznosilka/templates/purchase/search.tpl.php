<!-- start search.tpl.php -->

<div id="search-overlay" class="hide">
  <div class="spinner"></div>
</div>

<h1>Поиск SMS</h1>

<? // var_dump($var['var']['search']) ?>

<p>
  <b>Закупка:
    <a href="<?= $var['var']['search']['url'] ?>" target='_blank'><?= $var['var']['search'][PURCHASE_NAME] ?></a></b>
</p>

<? $lot = $var['var']['search']['lot'] ?>

<div id="lot" class="lot normal">

  <p class="user-number" title="Номер заказа"><b>#<?= $lot['number'] + 1 ?></b></p>

  <p>
    <b><a href="<?= $lot['url'] ?>" target='_blank' title="Ник участника закупки"><?= $lot[USER_PURCHASE_NICK] ?></a></b>
  </p>

  <p title="Ф.И.О. участника закупки"><?= $lot[USER_PURCHASE_NAME] ?></p>

  <p><b>Данные об оплате, для которой ищется SMS:</b></p>

  <? $pay = $var['var']['search']['lot']['pay'] ?>

  <div class="pays" id="sticky">

    <div class="pay">
      <table class="zebra normal">
        <thead>
        <tr class="pay normal">
          <th class="number">
            <span class="tooltip-wrapper" tabindex="0">
              <img class="time" src="<?= URL::to('files/images/more.png') ?>">
              <span class="tooltip normal">
                <b>Отчёт об оплате создан:</b><br>
                <?= $pay[PAY_CREATED] ?>
              </span>
            </span> <span title="Номер оплаты">#<?= $pay['number'] + 1 ?></span>
          </th>
          <th class="time">
            <span title="Время оплаты, указанное участником закупки"><?= $pay[PAY_TIME] ?></span></th>
          <th class="center" colspan="2">
            <span title="№ карты указанный участником закупки, с которой произведена оплата"><?= $pay[PAY_CARD_PAYER] ?></span>
          </th>
          <th class="sum right">
            <span title="Сумма оплаты, указанная участником закупки"><?= $pay[PAY_SUM] ?> &#8381;</span>
          </th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <? if (!empty($lot['comment_org']) or !empty($lot['comments'])) : ?>
    <dl class="details">
      <dt><i>Комментарии к заказу</i></dt>
      <dd>
        <div class="comments">

          <? if (!empty($lot['comment_org'])) : ?>
            <p><b>Комментарий организатора:</b></p>
            <ul class="list">
              <li>
                <?= $lot['comment_org'] ?>
              </li>
            </ul>
          <? endif; ?>

          <? if (!empty($lot['comments'])) : ?>
            <p><b>Комментарий участника закупки:</b></p>
            <ul class="list">
              <? foreach ($lot['comments'] as $comment) : ?>
                <li>
                  <?= $comment ?>
                </li>
              <? endforeach; ?>
            </ul>
          <? endif; ?>

        </div>
      </dd>
    </dl>
  <? endif; ?>

</div>

<form action="<?= URL::to('purchase/search') ?>" method="get">

  <? $form = $var['var']['search']['form'] ?>

  <p><b>Введите данные для поиска:</b></p>

  <table class="zebra search-filter">
    <tr>
      <td width="50%">
        <div class="control-wrapper">
          <input type="checkbox" name="dtc" value='1' <?= $form['dtc'] ? "checked" : "" ?> title="Если стоит галочка, значит данное поле будет учитываться при поиске">

          <div class="action-wrapper">
            <label>Время и дата<br>

              <input type="text" name="dt" placeholder='Введите время и дату оплаты' title="Поле для поиска SMS по времени и дате оплаты" value="<?= $form['dt'] ?>">

            </label>
          </div>
        </div>
      </td>
      <td width="50%">
        <div class="control-wrapper">
          <div class="action-wrapper">
            <label>Диапазон поиска &plusmn;<span id="fork-out"><?= $form['fk'] ?></span> дн. <br>
              <input id='fork' name='fk' type='range' max='7' min='1' step='1' list='fork-list' title="Переместите ползунок, для того чтобы установить диапазон поиска в днях от указанной даты оплаты" value="<?= $form['fk'] ?>">
              <datalist id='fork-list'>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
                <option>7</option>
              </datalist>
            </label>
          </div>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <div class="control-wrapper">
          <input type="checkbox" name="cc" value="1" <?= $form['cc'] ? "checked" : "" ?> title="Если стоит галочка, значит данное поле будет учитываться при поиске">

          <div class="action-wrapper">
            <label>Номер карты участника<br>

              <input name="c" placeholder='Введите номер карты' type='number' step='1' min='0' max='9999' title="Поле для поиска SMS по номеру карты участника" value="<?= $form['c'] ?>">

            </label>
          </div>
        </div>
      </td>
      <td>
        <div class="control-wrapper">
          <input type="checkbox" name="sc" value='1' <?= $form['sc'] ? "checked" : "" ?> title="Если стоит галочка, значит данное поле будет учитываться при поиске">

          <div class="action-wrapper">
            <label>Сумма оплаты<br>

              <input name="s" placeholder='Введите сумму оплаты' type='number' step='0.01' title="Поле для поиска SMS по сумме оплаты" value="<?= $form['s'] ?>">

            </label>
          </div>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div class="control-wrapper">
          <input type="checkbox" name="fc" value='1' <?= $form['fc'] ? "checked" : "" ?> title="Если стоит галочка, значит данное поле будет учитываться при поиске">

          <div class="action-wrapper">
            <label>Ф.И.О. плательщика<br>

              <input name="f" placeholder='Введите Ф.И.О. плательщика' type='text' title="Поле для поиска SMS по Ф.И.О. плательщика" value="<?= $form['f'] ?>">
            </label>

            <p class="hint">Для замены любого количества любых симоволов используйте знак <b>*</b>, например:
              <b>Иван*И.</b></p>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <div class="advanced-search">
    <dl class="details">
      <dt><i><b>Расширенный поиск</b></i></dt>
      <dd>

        <table class="zebra search-filter">
          <tr>
            <td width="50%">
              <div class="control-wrapper">
                <label title="Поиск только среди SMS, которые содержат сообщения от плательщика">
                  <input name="mc" type='checkbox' value='1' <?= $form['mc'] ? "checked" : "" ?>>

                  SMS содержит сообщение

                </label>
              </div>
            </td>
            <td width="50%">
              <div class="control-wrapper">
                <label title="Поиск только среди возвращённых SMS">
                  <input name="rc" type='checkbox' value='1' <?= $form['rc'] ? "checked" : "" ?>>

                  SMS возвращена

                </label>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="control-wrapper">
                <div class="action-wrapper">
                  <label>Тип SMS<br>

                    <select name="t" title="Поле для фильтрации SMS по типу. SMS бывают двух типов: либо содержащие Ф.И.О. плательщика, либо номер карты с которой поступила оплата">
                      <option value="0" <?= ($form['t'] == 0) ? "selected='selected'" : "" ?> >Любой</option>
                      <option value="1" <?= ($form['t'] == 1) ? "selected='selected'" : "" ?> >Содержит номер карты</option>
                      <option value="2" <?= ($form['t'] == 2) ? "selected='selected'" : "" ?> >Содержит Ф.И.О.</option>
                    </select>

                  </label>
                </div>
              </div>
            </td>
            <td>
              <div class="control-wrapper">
                <div class="action-wrapper">
                  <label>Статус SMS<br>

                    <select name="st" title="Поле для фильтрации SMS по статусу. SMS может быть доступной для проставления оплаты, либо уже использованной для проставления другой оплаты или возвращена плательщику">
                      <option value="0" <?= ($form['st'] == 0) ? "selected='selected'" : "" ?>>Любой</option>
                      <option value="1" <?= ($form['st'] == 1) ? "selected='selected'" : "" ?>>Использованная SMS</option>
                      <option value="2" <?= ($form['st'] == 2) ? "selected='selected'" : "" ?>>Не использованная SMS</option>
                    </select>

                  </label>
                </div>
              </div>
            </td>
          </tr>
        </table>

      </dd>
    </dl>
  </div>
  <input type="hidden" name="l" value="<?= $lot['number'] ?>">
  <input type="hidden" name="p" value="<?= $pay['number'] ?>"> <input type="hidden" name="q" value="1">
  <input type="submit" value="Найти SMS" title="Найти SMS по заданным параметрам">
  <input type="button" value="Назад" onclick="location.href='<?= $var['var']['search']['redirect_url'] ?>'"/>
</form>

<h2>Результат:</h2>
<? if (($var['var']['search']['count_sms']) > 0) : ?>
  <p>Количество найденных SMS: <b><?= $var['var']['search']['count_sms'] ?></b> шт.</p>

  <?= $var['var']['search']['pager'] ?>

  <div class="lot search">
    <table class="zebra">

      <? foreach ($var['var']['search']['sms'] as $keySms => $sms) : ?>
        <tr class="sms <?= $sms['status'] ?>">
          <td class="number">
            <? if (!$sms['used'] and !$sms['return']) : ?>
              <button value="<?= $sms[SMS_ID] ?>" class="editor sms-apply" title="Проставить оплату найденной SMS">
                <div class="glyph apply"></div>
              </button>
            <? elseif ($sms['used']) : ?>
              <span class="tooltip-wrapper" tabindex="0">
                <span class="button">
                  <img src="<?= URL::to('files/images/help.png') ?>">
                  <span>Информация об оплате</span>
                </span>
                <span class="tooltip <?= $sms['status'] ?>">
                  <b>Информация об оплате:</b><br>
                  Закупка: <a href="<?= $sms['purchase_url'] ?>" target='_blank'><?= $sms[PURCHASE_NAME] ?></a> <br>
                  Ник участника: <a href="<?= $sms['user_purchase_url'] ?>" target='_blank'><?= $sms[USER_PURCHASE_NICK] ?></a>
                </span>
              </span>
            <? elseif ($sms['return']) : ?>
              &nbsp;
            <? endif; ?>
          </td>
          <td class="time">
            <span title="Время оплаты, полученное из SMS"><?= $sms['time'] ?></span>
          </td>

          <td class="nested">
            <table class="sms-info">
              <tr>
                <? if (!empty($sms[SMS_FIO])) : ?>
                  <td class="center">
                    <span title="Ф.И.О. плательщика, полученные из SMS"><?= $sms[SMS_FIO] ?></span>
                  </td>
                <? endif; ?>
                <? if (!empty($sms[SMS_CARD_PAYER])) : ?>
                  <td class="center">
                    <span title="№ карты с которой поступила оплата, полученный из SMS"><?= $sms[SMS_CARD_PAYER] ?></span>
                  </td>
                <? endif; ?>
                <? if (!empty($sms[SMS_COMMENT])) : ?>
                  <td class="center">
                    <span title="Сообщение содержащееся в SMS">Сообщение: "<?= $sms[SMS_COMMENT] ?>"</span>
                  </td>
                <? endif; ?>
                <? if (empty($sms[SMS_CARD_PAYER]) and empty($sms[SMS_FIO]) and empty($sms[SMS_COMMENT])) : ?>
                  <td class="center">
                    —
                  </td>
                <? endif; ?>
              </tr>
            </table>
          </td>

          <td class="sum right">
            <span title="Сумма оплаты, получанная из SMS"><?= $sms[SMS_SUM_PAY] ?> &#8381;</span>
          </td>
        </tr>

      <? endforeach; ?>
    </table>
  </div>

  <?= $var['var']['search']['pager'] ?>

<? else : ?>
  <p>Не найдено ни одной SMS удовлетворяющей условиям поиска</p>
<? endif; ?>

<dl class="details">
  <dt><i>Расшифровка цветовых обозначений</i></dt>
  <dd>
    <table class="zebra legend">
      <tr>
        <td colspan="2" class="bold">SMS</td>
      </tr>
      <tr class="sms normal">
        <th>&nbsp;</th>
        <td>SMS доступна для проставления оплаты.</td>
      </tr>
      <tr class="sms warning">
        <th>&nbsp;</th>
        <td>Данная SMS уже использована при проставлении другой оплаты.</td>
      </tr>
      <tr class="sms error">
        <th>&nbsp;</th>
        <td>SMS доступна для проставления оплаты, но содержит сообщение.</td>
      </tr>
      <tr class="sms inactive">
        <th>&nbsp;</th>
        <td>Сделан возврат участнику закупки.</td>
      </tr>
    </table>
  </dd>
</dl>

<!-- end search.tpl.php -->