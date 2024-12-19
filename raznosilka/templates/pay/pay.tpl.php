<!-- start pay.tpl.php -->

<h1>Цены и оплата</h1>

<? // var_dump($var['var']['page']) ?>

<div class="list-items">
  <ul>
    <li>
      <span class="cost">Стоимость 30 дней оказания услуги поиска SMS для отчётов об оплатах:
      <span class="cost-old">1000&nbsp;</span>&nbsp;<span class="cost-new"><?= MONTH_COST ?>&nbsp;₽&nbsp;*</span></span>

      <p class="hint">* Цена снижена на время пока сервис находится в β-версии</p>
    </li>

    <? $user = $var['var']['page']['user'] ?>

    <? if (($user == 0) or ($user == 1) or ($user == 2)) : ?>
      <li>
        <span class="cost">Первые <?= DAY_GIFT ?> дней вы получаете услугу бесплатно.</span>
      </li>
    <? endif; ?>
    <? if ($user == 0) : ?>
      <li>
        <blockquote>
          Зарегистрируйтесь в «Разносилке» и активируйте свой аккаунт, чтобы получить услугу: <br><br>
          <input type="button" value="Зарегистрироваться" onclick="location.href='<?= URL::to('user/register') ?>'"/>
          <br><br>
          <span class="hint"><a target="_blank" href="<?= URL::to('help/register') ?>">Как зарегистрироваться в «Разносилке»?</a></span>
          <br>
          <span class="hint"><a target="_blank" href="<?= URL::to('help/activation') ?>">Как активировать аккаунт?</a></span>
        </blockquote>
      </li>
    <? endif; ?>
    <? if ($user == 1) : ?>
      <li>
        <blockquote>
          Вы зарегистрированы в «Разносилке». Теперь активируйте свой аккаунт, чтобы получить услугу.
          <br>
          <span class="hint"><a target="_blank" href="<?= URL::to('help/activation') ?>">Как активировать аккаунт?</a></span>
        </blockquote>
      </li>
    <? endif; ?>
    <? if ($user == 2) : ?>
      <li>
        <input type="button" value="Получить услугу бесплатно" onclick="location.href='<?= URL::to('pay/gift') ?>'"/>
      </li>
    <? endif; ?>
    <? if ($user == 3) : ?>
      <li>
        <? if ($var['var']['page']['status']) : ?>
        <blockquote>
          Услуга оплачена и предоставляется до <b><?= $var['var']['page']['date_done'] ?></b>.
        </blockquote>
        <? else: ?>
        <blockquote>
          На данный момент услуга не оплачена и не предоставляется.
        </blockquote>
        <? endif; ?>
      </li>
      <li>
        <? // var_dump($var['var']['page']['payment_form']) ?>
        <?= $var['var']['page']['payment_form']?>
      </li>
    <? endif; ?>
  </ul>

</div>

<!-- end pay.tpl.php -->