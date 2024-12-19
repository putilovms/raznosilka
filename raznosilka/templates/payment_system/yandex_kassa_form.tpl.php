<!-- start yandex-kassa.tpl.php -->

<form method="post" action="<?= $var['action_url'] ?>">
  <input type="hidden" name="shopId" value="<?= $var['shop_id'] ?>">
  <input type="hidden" name="scid" value="<?= $var['scid'] ?>">
  <input type="hidden" name="customerNumber" value="<?= $var['uid'] ?>">
  <input type="hidden" name="sum" value="<?= $var['sum'] ?>">
  <input type="hidden" name="cps_email" value="<?= $var['email'] ?>">
  <ul>
    <li>
      <b>Способ оплаты:</b>
    </li>
    <li>
      <div class="radio-buttons">
        <input id="card" type="radio" name="paymentType" value="AC" checked required> <label for="card" title="Банковская карта">
          <img src="<?= URL::to('files/images/card.png') ?>" alt="Банковская карта"><span>Банковская карта</span></label>
        <input id="yandex-money" type="radio" name="paymentType" value="PC" required> <label for="yandex-money" title="Яндекс.Деньги">
          <img src="<?= URL::to('files/images/yandex-money.png') ?>" alt="Яндекс.Деньги"><span>Яндекс.Деньги</span></label>
      </div>
    </li>
    <li>
      <input type=submit value="Оплатить">
    </li>
  </ul>
</form>

<!-- end yandex-kassa.tpl.php -->