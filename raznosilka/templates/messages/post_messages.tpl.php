<!-- start post_messages.tpl.php -->
<h1>Рассылка уведомлений</h1>
<form class="post_messages" action="<?= URL::to('messages/posting') ?>" method="post">
  <ul>
    <li>
      <label>Тип уведомления:
        <select name="messages_type" required="required">
          <option value="" disabled selected>- Выберите тип уведомления -</option>
          <option value="<?= SUCCESS_MESSAGE ?>">Общее уведомление</option>
          <option value="<?= INFO_MESSAGE ?>">Информационное уведомоление</option>
          <option value="<?= WARNING_MESSAGE ?>">Предупреждение</option>
          <option value="<?= MONEY_MESSAGE ?>">Финансовые операции</option>
        </select>
      </label>
    </li>
    <li>
      <label>Текст уведомления:
        <textarea id="messages_text" name="messages_text" autofocus required="required" placeholder="Введите текст уведомления"></textarea>
      </label>
    </li>
    <li>
      <input type="submit" value="Разослать уведомления">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('admin') ?>'">
    </li>
  </ul>
</form>
<!-- end post_messages.tpl.php -->