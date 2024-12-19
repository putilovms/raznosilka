<!-- start debug.tpl.php --><h1>Отладка</h1>

<h3>Переменные шаблона</h3>

<dl class="details">
  <dt><i><b>Переданные скриптом (var)</b></i></dt>
  <dd>
    <pre>
      <? print_r($var['var']) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>Текущий пользователь (user)</b></i></dt>
  <dd>
    <pre>
      <? print_r($var['user']) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>Системные переменные (system)</b></i></dt>
  <dd>
    <pre>
      <? print_r($var['system']) ?>
    </pre>
  </dd>
</dl>

<p><b>Шаблон:</b> <?= $var['content'] ?></p><p><b>Слой:</b> <?= $layer ?></p>

<h3>Глобальные переменные</h3>

<dl class="details">
  <dt><i><b>$_POST</b></i></dt>
  <dd>
    <pre>
      <? print_r($_POST) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>$_SERVER</b></i></dt>
  <dd>
    <pre>
      <? print_r($_SERVER) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>$_REQUEST</b></i></dt>
  <dd>
    <pre>
      <? print_r($_REQUEST) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>$_FILES</b></i></dt>
  <dd>
    <pre>
      <? print_r($_FILES) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>$_SESSION (Registry Session)</b></i></dt>
  <dd>
    <pre>
      <? print_r($_SESSION) ?>
    </pre>
  </dd>
</dl>

<dl class="details">
  <dt><i><b>Registry Request</b></i></dt>
  <dd>
    <pre>
      <? print_r(Registry_Request::instance()->getAll()) ?>
    </pre>
  </dd>
</dl>

<h3>Системные настройки</h3>
<ul>
  <li>Тип запроса к сайту СП:<b>
      <? if ((int)$var['user'][USER_REQUEST] === REQUEST_CURL) :?>
        прямой запрос
      <? endif; ?>
      <? if ((int)$var['user'][USER_REQUEST] === REQUEST_EXTENSIONS) :?>
        через расширение
      <? endif; ?>
    </b>
  </li>
  <li>Данные запроса из кэша: <b><?= (Registry_Request::instance()->get('load_from_cache')) ? 'да' : 'нет' ?></b></li>
</ul>


<h3>Системная информация</h3>
<ul>
  <li>Скрипт выполнялся (всего) <b><?= $info->getTimeWork(); ?></b> сек.</li>
  <li>Отрисовка заняла <b><?= $info->getTimePiece(); ?></b> сек.</li>
  <li>Память необходимая скрипту <b><?= $info->getMemoryUsage(); ?></b> Mb.</li>
  <li>Версия PHP <b><?= phpversion(); ?></b></li>
</ul>

<p class="hint">Вы видите эту информацию так как сайт находится в режиме отладки.</p><!-- end debug.tpl.php -->