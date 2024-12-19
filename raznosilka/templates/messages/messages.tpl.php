<!-- start messages.tpl.php -->

<h1>Уведомления</h1>
<? if (!empty($var['var']['messages']['messages'])): ?>
  <h2>Управление уведомлениями</h2>
  <div class="control-wrapper">
    <label> <input id="select-all" type="checkbox" name="select-all"> </label>
    <div class="action-wrapper">
      <label>

        <select id="select-action" name="select-action" disabled>
          <option value="" selected>- Выберите действие -</option>
          <option value="delete">Удалить выбранные уведомления</option>
        </select>

      </label>
    </div>
  </div>

  <div class="article">
    <?= $var['var']['messages']['pager'] ?>
  </div>

  <form name="control" action="<?= URL::to('messages/delete') ?>" method="post">
    <h2>Ваши уведомления</h2>

    <div class="messages">
      <ul>
        <? foreach ($var['var']['messages']['messages'] as $key => $message): ?>
          <li class="<?= ($message['message_new']) ? 'new' : '' ?>">
            <label>
              <div class="message-wrapper">

                <input type="checkbox" name="<?= $message[MESSAGE_ID] ?>">

                <div class="icon <?= $message['class'] ?>"></div>
                <div class="message">
                  <div class="text"><?= $message[MESSAGE_TEXT] ?></div>
                  <div class="date"><?= $message[MESSAGE_DATE] ?></div>
                </div>

              </div>
            </label>
          </li>
        <? endforeach; ?>
      </ul>
    </div>

    <input type="hidden" name="action" id="hidden-action" value="">
  </form>

  <?= $var['var']['messages']['pager'] ?>

<? else: ?>
  <p>Уведомлений нет.</p>
<? endif; ?>

<!-- end messages.tpl.php -->