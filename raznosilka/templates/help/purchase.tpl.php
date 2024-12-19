<!-- start purchase.tpl.php -->

<h1>Обзор закупки</h1>

<p><b>Обзор закупок</b> – это многофункциональный инструмент, который позволяет просматривать закупку, проставлять оплаты в ручную, удалять проставленные оплаты, вручную изменять найденную сумму, фильтровать проблемные заказы и находить проблемы в самих заказах.</p>
<p>Чтобы инструмент «Обзор закупок» был доступен, сначала нужно <a href="<?= URL::to('help/select_purchase') ?>">выбрать закупку</a>, с которой вы собираетесь работать.</p>
<p>Перейти в инструмент «Обзор закупок» можно несколькими способами:</p>
<ul>
  <li>
    На главной странице «Разносилки» нажать на кнопку «Обзор закупки»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-34.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    В боковом меню нажать на кнопку «Обзор закупки»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-35.png') ?>" alt="Обзор закупки">
    </div>
  </li>
</ul>

<h1>Список действий</h1>

<p>Действия, которые позволяет совершить инструмент «Обзор закупок»:</p>

<ul>
  <li>
    <a href="#update">Обновление данных о закупке</a>
  </li>
  <li>
    <a href="#filter">Фильтрация заказов</a>
  </li>
  <li>
    <a href="#manual">Ручное проставление оплаты</a>
  </li>
  <li>
    <a href="#pay_del">Удалить проставленную оплату</a>
  </li>
  <li>
    <a href="#error">Отметить платёж как ошибочный</a>
  </li>
  <li>
    <a href="#error_del">Удалить отметку об ошибочном платеже</a>
  </li>
  <li>
    <a href="#edit">Ручное изменение суммы</a>
  </li>
  <li>
    <a href="#edit_del">Удаление изменений суммы внесённых вручную</a>
  </li>
  <li>
    <a href="#sum_update">Проставить найденную «Разносилкой» сумму на сайт СП</a>
  </li>
  <li>
    <a href="#report">Просмотреть отчёт о закупке</a>
  </li>
</ul>

<h1>Описание действий</h1>

<a name="update"></a>
<h2>Обновление данных о закупке</h2>

<p>«Разносилка» автоматически обновляет сведения о закупке. Но при некотором стечение обстоятельств, вы можете оказаться в ситуации, когда данные о закупке в «Разносилке» не соответствуют данным о закупке на сайте СП.</p>
<p>Для того чтобы «Разносилка» обновила данные о закупке, необходимо нажать на кнопку «Обновление данных о закупке»:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-36.png') ?>" alt="Обзор закупки">
</div>

<a name="filter"></a>
<h2>Фильтрация заказов</h2>

<p>Фильтр заказов показывает одни заказы и скрывает другие, что позволяет быстро ориентироваться среди множества заказов.</p>
<p>Фильтр заказов находится вверху страницы «Обзор и редактирование закупки»:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-37.png') ?>" alt="Обзор закупки">
</div>

<p>На данный момент доступны следующие фильтры:</p>

<ul>
  <li>
    <b>Все</b> – фильтр отключен и будут показаны все заказы в выбранной закупке.
  </li>
  <li>
    <b>Проблемные</b> – будут показаны только те заказы, которые содержат какие-либо проблемы. Например, имеется переплата или недоплата, имеются ненайденные платежи, не сходятся какие-либо суммы и так далее.
  </li>
  <li>
    <b>Не проставленные</b> – будут показаны только те заказы, которые содержат не проставленные платежи.
  </li>
</ul>

<blockquote>По умолчанию включен фильтр «Проблемные».</blockquote>

<a name="manual"></a>
<h2>Ручное проставление оплаты</h2>
<p>Для того чтобы проставить оплату вручную, необходимо выполнить следующие шаги:</p>

<ul>
  <li>
    Нажать кнопку «Найти SMS для оплаты вручную»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-38.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    На открывшейся странице «Поиск SMS», вы увидите уже заполненную форму для поиска и список найденных SMS.
  </li>
  <li>
    Если среди найденных SMS отсутствует нужная, то вы можете скорректировать данные, по которым ищется SMS и нажать на кнопку «Найти SMS»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-39.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    Если так и не удалось найти нужную SMS, то нажмите кнопку «Назад» для того чтобы вернуться в «Обзор закупки»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-40.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    Если среди найденных SMS присутствует нужная, то нажмите на кнопку «Проставить оплату найденной SMS», напротив нужной SMS:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-41.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    Найденная сумма автоматически проставится на сайте СП.
  </li>
</ul>

<a name="pay_del"></a>
<h2>Удалить проставленную оплату</h2>

<p>Чтобы удалить проставленную оплату, нужно нажать на кнопку «Удалить проставленную оплату»:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-42.png') ?>" alt="Обзор закупки">
</div>

<p>Изменённая сумма, после удаления проставленной оплаты, обновится на сайте СП автоматически.</p>

<a name="error"></a>
<h2>Отметить платёж как ошибочный</h2>

<p>Иногда участники закупок заполняют неверные отчёты об оплатах. Для того чтобы указать «Разносилке» что тот или иной отчёт является ошибочным, нужно нажать кнопку «Отметить оплату как ошибочную»:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-43.png') ?>" alt="Обзор закупки">
</div>

<p>После того как платёж будет отмечен как ошибочный, он изменит свой статус и «Разносилка» больше не будет его учитывать:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-44.png') ?>" alt="Обзор закупки">
</div>

<a name="error_del"></a>
<h2>Удалить отметку об ошибочном платеже</h2>

<p>Для того, чтобы удалить отметку об ошибочном платеже, нажмите на кнопку «Удалить у оплаты отметку, о его ошибочности»:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-45.png') ?>" alt="Обзор закупки">
</div>

<a name="edit"></a>
<h2>Ручное изменение суммы</h2>

<p>Ручное изменение суммы необходимо в нескольких случаях:</p>

<ul>
  <li>
    Оплата поступила, но SMS не пришла на телефон.
  </li>
  <li>
    Оплата поступила не на карту (например, наличными).
  </li>
  <li>
    Участник закупки оплатил по ошибке и нужно вернуть часть суммы или всю сумму.
  </li>
</ul>

<p>Чтобы изменить сумму вручную нужно выполнить следующие шаги:</p>

<ul>
  <li>
    У заказа, в котором необходимо изменить сумму, нажать на ссылку «Ручное изменение суммы»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-46.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    После нажатия на ссылку будет открыта форма для добавления суммы, а так же список уже добавленных сумм, если они есть:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-47.png') ?>" alt="Обзор закупки">
    </div>
  </li>
  <li>
    Далее необходимо ввести сумму и нажать на кнопку «Внести сумму вручную»:
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-48.png') ?>" alt="Обзор закупки">
    </div>
    <blockquote>Поле «комментарий» заполнять не обязательно, оно служит для сохранения информации о вносимой сумме.</blockquote>
    <blockquote>Сумма может быть как положительной, так и отрицательной.</blockquote>
  </li>
  <li>
    После добавления суммы, автоматически внесётся на сайт СП и отобразится в списке сумм
    <div class="img-wrapper">
      <img src="<?= URL::to('/files/images/help/image-49.png') ?>" alt="Обзор закупки">
    </div>
  </li>
</ul>

<a name="edit_del"></a>
<h2>Удаление изменений суммы внесённых вручную</h2>

<p>Для удаления из заказа добавленной вручную суммы, нужно нажать на ссылку «Ручное изменение суммы» и в появившемся списке нажать на кнопку «Удалить сумму» напротив той суммы, которую необходимо удалить:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-50.png') ?>" alt="Обзор закупки">
</div>

<p>После удаления суммы, изменения будут внесены на сайт СП автоматически.</p>


<a name="sum_update"></a>
<h2>Проставить найденную «Разносилкой» сумму на сайт СП</h2>

<p>Хотя все изменения, сделанные в «Разносилке» автоматически вносятся на сайт СП, иногда возникает необходимость принудительно внести изменения на сайт СП. Для того чтобы это сделать нужно нажать на кнопку «Проставить найденную «Разносилкой» сумму на сайт СП»:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-51.png') ?>" alt="Обзор закупки">
</div>

<p>После чего, найденная «Разносилкой» сумма будет внесена на сайт СП:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-52.png') ?>" alt="Обзор закупки">
</div>

<a name="report"></a>
<h2>Просмотреть отчёт о закупке</h2>

<p>В самом низу страницы «Обзор закупки» расположен небольшой отчёт содержащий информацию о закупке:</p>

<div class="img-wrapper">
  <img src="<?= URL::to('/files/images/help/image-53.png') ?>" alt="Обзор закупки">
</div>

<p>Он содержит следующие данные:</p>

<ul>
  <li>
    <b>Участников</b> – количество активных участников закупки.
  </li>
  <li>
    <b>Заказов</b> – количество активных лотов.
  </li>
  <li>
    <b>Общая сумма</b> – общая сумма, которую должны сдать все участники данной закупки.
  </li>
  <li>
    <b>Денег сдано</b> – общая сумма, которая внесена на сайт СП.
  </li>
  <li>
    <b>Найдено «Разносилкой»</b> – общая сумма найденная «Разносилкой».
  </li>
</ul>

<!-- end purchase.tpl.php -->