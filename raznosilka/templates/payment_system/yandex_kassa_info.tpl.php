<!-- start yandex_kassa_info.tpl.php -->

<table class="zebra settings">
  <tr>
    <th>ID транзакции</th>
    <td><?= $var['payment'][INVOICE_ID] ?></td>
  </tr>
  <tr>
    <th>ID пользователя</th>
    <td><?= $var['payment'][CUSTOMER_NUMBER] ?></td>
  </tr>
  <tr>
    <th>ID товара</th>
    <td><?= $var['payment'][SHOP_ARTICLE_ID] ?></td>
  </tr>
  <tr>
    <th>Дата заказа</th>
    <td><?= $var['payment'][ORDER_CREATED_DATETIME] ?></td>
  </tr>
  <tr>
    <th>Дата оплаты</th>
    <td><?= $var['payment'][PAYMENT_DATETIME] ?></td>
  </tr>
  <tr>
    <th>Стоимость заказа</th>
    <td><?= $var['payment'][ORDER_SUM_AMOUNT] ?></td>
  </tr>
  <tr>
    <th>Сумма к выплате</th>
    <td><?= $var['payment'][SHOP_SUM_AMOUNT] ?></td>
  </tr>
  <tr>
    <th>Способ оплаты</th>
    <td><?= $var['payment'][PAYMENT_TYPE] ?></td>
  </tr>
</table>

<!-- end yandex_kassa_info.tpl.php -->