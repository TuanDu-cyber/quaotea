<?php
require __DIR__ . '/config.php';

$order = db('SELECT * FROM orders WHERE code = ?', [$_GET['code'] ?? ''])->fetch();
if (!$order) {
    http_response_code(404);
    $title = 'Không tìm thấy đơn';
    require __DIR__ . '/includes/header.php';
    echo '<section class="empty"><h1>Không tìm thấy đơn này</h1><a class="btn btn-primary" href="' . url('menu.php') . '">Về menu</a></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$items = db('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$order['id']])->fetchAll();

$title = 'Đặt món thành công';
require __DIR__ . '/includes/header.php';
?>
<section class="success">
  <div class="empty-art"><?= cup_svg(0) ?></div>
  <h1>Đặt món thành công</h1>
  <p>Mã đơn của bạn là <strong class="code"><?= e($order['code']) ?></strong>. Quán sẽ xác nhận sớm nhất.</p>
  <div class="card receipt">
    <?php foreach ($items as $it): ?>
      <div class="row-between"><span><?= (int)$it['quantity'] ?> × <?= e($it['product_name']) ?></span><span><?= money($it['unit_price'] * $it['quantity']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="row-between"><strong>Tổng cộng</strong><strong><?= money($order['total']) ?></strong></div>
    <p class="muted small">Giao tới: <?= e($order['address']) ?> · <?= status_badge($order['status']) ?></p>
    <p class="muted small">Thanh toán: Thanh toán khi nhận hàng</p>
  </div>
  <div class="actions center">
    <a class="btn btn-primary" href="<?= url('tra-cuu.php?phone=' . urlencode($order['phone'])) ?>">Theo dõi đơn</a>
    <a class="btn btn-ghost" href="<?= url('menu.php') ?>">Đặt thêm</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
