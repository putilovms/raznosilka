<!-- start import_android.tpl.php -->

<h1>Выгрузка SMS на Android</h1>

<ul>
  <li>
    Установить приложение <a target="_blank" href="https://play.google.com/store/apps/details?id=com.riteshsahu.SMSBackupRestore">SMS Backup & Restore</a> для Andriod
  </li>
  <li>
    Запустить приложение и нажать на кнопку «Сделать бекап»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-6.png') ?>" alt="SMS Backup & Restore">
    </div>
  </li>
  <li>
    При первом запуске, приложение спросит, в какую папку сохранять файлы с SMS. Выбрать «По умолчанию» и нажать кнопку «ОK»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-24.png') ?>" alt="SMS Backup & Restore">
    </div>
  </li>

  <li>
    В появившемся окне снять галочку с «Call Logs» и выбрать «Только выбранные диалоги», остальное оставить как есть:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-7.png') ?>" alt="SMS Backup & Restore">
    </div>
  </li>
  <li>
    Далее программа предложит выбрать, с каких номеров делать выгрузку, нужно выбрать номер 900 и нажать кнопку «Назад» на телефоне:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-8.png') ?>" alt="SMS Backup & Restore">
    </div>
  </li>
  <li>
    В появившемся окне нажать кнопку «ОК»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-9.png') ?>" alt="SMS Backup & Restore">
    </div>
  </li>
  <li>
    Если всё прошло успешно, то вы увидите сообщение о том что «Бекап завершён».
  </li>
</ul>
<blockquote>Файл с выгрузкой SMS по умолчанию будет находиться на внутреннем накопителе, в папке <span class="path">SMSBackupRestore</span>.</blockquote>



<!-- end import_android.tpl.php -->