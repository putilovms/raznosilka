<!-- start password_sp_del.tpl.php -->

<h1>Удаление логина и пароля от сайта СП</h1>

<blockquote>Если для соединения с сайтом СП используется расширение для браузера, то пропустите данный раздел, так как удалять логин и пароль от сайта СП не требуется.</blockquote>

<p>После того как вы ввели логин и пароль от сайта СП, может возникнуть ситуация, при которой необходимо удалить логин и пароль от сайта СП из «Разносилки».</p>

<p>Для того чтобы удалить логин и пароль от сайта СП, нужно:</p>

<ul>
  <li>
    Зайти в «<a href="<?= URL::to('user') ?>" target="_blank">Настройки аккаунта</a>» через боковое меню:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-60.png') ?>" alt="Удаление логина и пароля от сайта СП">
    </div>
  </li>
  <li>
    Далее нажать на ссылку «<a href="<?= URL::to('user/sp') ?>" target="_blank">Настройки сайта СП</a>»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-61.png') ?>" alt="Удаление логина и пароля от сайта СП">
    </div>
  </li>
  <li>
    На открывшейся странице нажать на ссылку «<a href="<?= URL::to('user/password_del') ?>" target="_blank">Удалить логин и пароль от сайта СП</a>»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-65.png') ?>" alt="Удаление логина и пароля от сайта СП">
    </div>
  </li>
  <li>
    Подтвердите удаление логина и пароля нажатием на кнопку «Да»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-66.png') ?>" alt="Удаление логина и пароля от сайта СП">
    </div>
  </li>
  <li>
    Далее вы увидите сообщение о том, что логин и пароль от сайта СП успешно удалён:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-67.png') ?>" alt="Удаление логина и пароля от сайта СП">
    </div>
  </li>
</ul>

<blockquote>
  После удаления логина и пароля от сайта СП, вы не сможете пользоваться «Разносилкой» пока <a href="<?= URL::to('help/password_sp_set') ?>">снова их не введёте</a>.
</blockquote>

<!-- end password_sp_del.tpl.php -->