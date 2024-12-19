<!-- start new.tpl.php -->

<h1>Настройки сервиса</h1>

<form class="settings" action="" method="post">
  <ul>
    <li>
      <label> Режим работы сайта: <select name="mode">
          <option value="debug" <? if ($var['var']['settings']['mode'] == 'debug') print "selected" ?>>Отладка</option>
          <option value="normal" <? if ($var['var']['settings']['mode'] == 'normal') print "selected" ?>>Нормальный режим</option>
          <option value="service" <? if ($var['var']['settings']['mode'] == 'service') print "selected" ?>>Техническое обслуживание</option>
        </select> </label>
    </li>
    <li>
      <label>
        <input type="checkbox" name="load_from_cache" <? if ($var['var']['settings']['load_from_cache']) print "checked" ?> value="1"> Не обращаться к сайту СП в режиме отладки, если это возможно
        <p class="hint">Если выбрано, то запросы к сайту СП будут сохраняться в КЭШ при любом режиме работы сайта</p>
      </label>
    </li>
    <li>
      <label>Системный e-mail:
        <input type="email" name="system_email" placeholder="Введите системный e-mail" value="<?= $var['var']['settings']['system_email'] ?>" required="required">
      </label>
    </li>
    <li>
      <label>
        <input type="checkbox" name="register_account" <? if ($var['var']['settings']['register_account']) print "checked" ?> value="1"> Разрешить регистрацию новых пользователей
      </label>
    </li>
    <li>
      <label>
        <input type="checkbox" name="activate_account" <? if ($var['var']['settings']['activate_account']) print "checked" ?> value="1"> Включить подтверждение e-mail для пользователей
      </label>
    </li>
    <li>
      <input name="submit" type="submit" value="Сохранить">
      <input type="button" value="Очистить кэш" onclick="location.href='<?= URL::to('admin/cache_del') ?>'">
      <input type="button" value="Назад" onclick="location.href='<?= URL::to('admin') ?>'">
    </li>
  </ul>
</form>

<!-- end new.tpl.php -->