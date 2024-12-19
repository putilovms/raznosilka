<!-- start new.tpl.php -->

<h1>Список сайтов СП</h1>

<p>Здесь вы найдёте список сайтов СП с которыми на данный момент работает «Разносилка».</p>

<? // var_dump($var['var']['list']) ?>

<? if (!empty($var['var']['list'])) : ?>

  <table class="zebra">
    <tr>
      <th>
        Название
      </th>
      <th>
        Описание
      </th>
    </tr>
    <? foreach ($var['var']['list'] as $sp) : ?>
      <tr>
        <td>
          <a href="<?= $sp[SP_SITE_URL] ?>" target="_blank"><?= $sp[SP_SITE_NAME] ?></a>
        </td>
        <td>
          <?= $sp[SP_DESCRIPTION] ?>
        </td>
      </tr>
    <? endforeach; ?>
  </table>

<? else : ?>

  <p>Список сайтов СП пуст.</p>

<? endif; ?>

<p><a href="<?= URL::to('info/add_sp') ?>">Что делать, если вашего сайта СП нет в списке?</a></p>

<!-- end new.tpl.php -->