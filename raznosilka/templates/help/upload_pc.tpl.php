<!-- start upload_pc.tpl.php -->

<h1>Загрузка SMS с компьютера</h1>

<p>На страницу «Загрузки SMS» можно попасть несколькими способами:</p>

<ul>
  <li>
    На <a href="<?= URL::base() ?>" target="_blank">главной странице</a> «Разносилки» нажать на кнопку «Загрузка SMS»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-10.png') ?>" alt="Загрузка SMS">
    </div>
  </li>
  <li>
    В боковом меню нажать на кнопку «Загрузка SMS»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-11.png') ?>" alt="Загрузка SMS">
    </div>
  </li>
</ul>

<blockquote>В «Разносилку» можно загружать сразу несколько файлов.</blockquote>

<p>Для того чтобы загрузить файл с SMS в «Разносилку» нужно:</p>
<ul>
  <li>
    Нажать на кнопку «Выбрать файлы»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-12.png') ?>" alt="Загрузка SMS">
    </div>
  </li>
  <li>
    В открывшемся окне найти нужный файл или несколько файлов и нажать кнопку «Открыть»
  </li>
  <li>
    Нажать кнопку «Загрузить»
  </li>
</ul>

<p>После того как все SMS будут загружены и обработаны вы увидите небольшой отчёт:</p>
<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-13.png') ?>" alt="Загрузка SMS">
</div>

<blockquote>В «Разносилке» не хранится текст самой SMS, а только необходимые для проставления оплат данные. Эти данные извлекаются только из определённого типа SMS полученных на номер 900. Поэтому вы можете не переживать за конфиденциальность своих данных.</blockquote>

<!-- end upload_pc.tpl.php -->