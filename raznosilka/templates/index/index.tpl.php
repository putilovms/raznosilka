<!-- start index.tpl.php -->

<div id="main-wrapper">
  <div id="legend">
    <p>Проставляйте оплаты с удовольствием</p>
  </div>

  <h1>Разносилка</h1>

  <div id="button">
    <p><span class="free">Первый месяц <b>бесплатно</b></span></p>

    <form action="<?= URL::to('user/register') ?>" method="post">
      <input type="submit" value="Зарегистрироваться"> или
      <input type="button" value="Войти" onclick="location.href='<?= URL::to('user/login') ?>'"/>
    </form>
  </div>

  <h2>Как это работает?</h2>

  <table class="step">
    <tr>
      <td class="image"><img src="<?= URL::to('files/images/landing/step-1.png') ?>" alt=""></td>
      <td>
        <b>Всё очень просто</b>

        <br>Зарегистрируйтесь в «Разносилке», установите расширение для браузера и работайте.

        <br>

        <p class="hint"><a href="<?= URL::to('info/sp') ?>">Список совместимых сайтов СП</a></p>
      </td>
    </tr>
    <tr>
      <td class="image"><img src="<?= URL::to('files/images/landing/step-2.png') ?>" alt=""></td>
      <td><b>Импорт SMS</b><br>Загрузите в «Разносилку» SMS с оплатами.</td>
    </tr>
    <tr>
      <td class="image"><img src="<?= URL::to('files/images/landing/step-3.png') ?>" alt=""></td>
      <td>
        <b>Автопоиск SMS</b><br>Выберите закупку для которой необходимо проставить оплаты и «Разносилка» сама найдёт для неё подходящие SMS.
      </td>
    </tr>
    <tr>
      <td class="image"><img src="<?= URL::to('files/images/landing/step-4.png') ?>" alt=""></td>
      <td><b>Всё под вашим контролем</b><br>Если это необходимо, проверьте работу «Разносилки» и внесите изменения.</td>
    </tr>
    <tr>
      <td class="image"><img src="<?= URL::to('files/images/landing/step-5.png') ?>" alt=""></td>
      <td><b>Автопроставление оплат</b><br>«Разносилка» сама внесёт найденные оплаты на сайт СП.</td>
    </tr>
    <tr>
      <td class="image"><img src="<?= URL::to('files/images/landing/step-6.png') ?>" alt=""></td>
      <td>
        <b>Зарабатывайте больше</b><br>Проставляйте оплаты за считанные минуты и освободите время для ведения ещё большего числа закупок.
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <div class="share article">
          <b>Поделитесь с другими</b>
          <script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script>
          <div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="small" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir" data-yashareTheme="counter"></div>
        </div>
      </td>
    </tr>
  </table>

  <? // var_dump($var['var']['info']['reviews']) ?>

  <? if (!empty($var['var']['info']['reviews'])): ?>
    <div class="reviews">
      <h2>Отзывы</h2>

      <div class="three-columns">
        <? foreach ($var['var']['info']['reviews'] as $review) : ?>
          <div class="columns">
            <div class="review">
              <div class="photo">
                <img src="<?= $review['photo'] ?>" alt="<?= $review['name'] ?>" height="100px" width="100px">
              </div>
              <h3>
                <? if (empty($review['author_url'])) : ?>

                  <?= $review['name'] ?>

                <? else : ?>

                  <a href="<?= $review['author_url'] ?>"><?= $review['name'] ?></a>

                <? endif; ?>
              </h3>

              <p class="hint"><?= $review['post'] ?> <a href="<?= $review['sp_url'] ?>"><?= $review['sp'] ?></a></p>

              <p><?= $review['review'] ?></p>
            </div>
          </div>
        <? endforeach; ?>
      </div>
    </div>

  <? endif; ?>

  <h2>Сколько стоит?</h2>

  <div class="description">
    <p>
      Стоимость 30 дней услуги –
      <span class="cost-old">1000&nbsp;</span>&nbsp;<span class="cost-new"><?= MONTH_COST ?>&nbsp;₽&nbsp;*</span> <br>
      <b>Первые <?= DAY_GIFT ?> дней</b> вы получаете услугу абсолютно <b>бесплатно</b>.
      <br><span class="hint">* Цена снижена на время пока сервис находится в β-версии</span></p>
  </div>

  <form action="<?= URL::to('user/register') ?>" method="post">
    <button type="submit">Зарегистрироваться</button>
  </form>

</div>

<!-- end index.tpl.php -->