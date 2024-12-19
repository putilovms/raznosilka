<!-- start compensation.tpl.php -->

<h1>Компенсации пользователям</h1>

<p>Данный инструмент служит для компенсирования времени, в которое сервис не работал по каким-либо причинам.</p>

<form action="" method="post">

  <ul>
    <li>
      <label> Укажите причину, по которой начисляется компенсация: <select name="type" required>
          <option value="" selected disabled>- Выберите причину -</option>
          <option value="unplanned">Незапланированные технические работы</option>
        </select> </label>
    </li>
    <li>
      <? $datetime = date('Y-m-d\\TH:i'); ?>
      <label> Выберите дату и время момента, за который начисляется компенсация:
        <input placeholder='Введите дату и время' type='datetime-local' name='datetime' value='<?= $datetime ?>' required>
      </label>
    </li>
    <li>
      <label>Введите количество дней компенсации:<input name="day" type="number" min="1" max="30" required placeholder="Укажите количество дней"></label>
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <input type="submit" value="Добавить">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('admin') ?>'">
    </li>
  </ul>

</form>

<!-- end compensation.tpl.php -->