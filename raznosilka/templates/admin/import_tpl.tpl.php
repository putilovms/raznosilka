<!-- start import.tpl.php -->

<h1>Импорт шаблонов SMS</h1>

<p>Пожалуйста, выберете файл экспорта CSV с шаблонами SMS и нажмите кнопку «Загрузить».</p>
<form class="load" action="" method="post" enctype="multipart/form-data">
  <ul>
    <li>
      <input name="file" type="file" size="30" required="required"><br>
    </li>
    <li>
      <span class="icon-required"></span> - обязательно для заполнения.
    </li>
    <li>
      <input type="submit" value="Загрузить">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('admin/templates') ?>'"/>
    </li>
  </ul>
</form>

<!-- end import.tpl.php -->