<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';

$order = db('SELECT * FROM orders WHERE id = ?', [(int)($_GET['id'] ?? 0)])->fetch();
if (!$order) redirect('admin/orders.php');
$items = db('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$order['id']])->fetchAll();

$title = 'Đơn ' . $order['code'];
require __DIR__ . '/includes/admin_header.php';
?>
<p><a href="<?= url('admin/orders.php') ?>">← Danh sách đơn</a></p>
<div class="row-between wrap"><h1>Đơn <?= e($order['code']) ?></h1><?= status_badge($order['status']) ?></div>
<div class="two-col">
  <section class="card form-card">
    <h2>Khách hàng</h2>
    <p><strong><?= e($order['customer_name']) ?></strong><br><?= e($order['phone']) ?></p>
    <p><?= e($order['address']) ?></p>
    <?php if ($order['note']): ?><p class="muted">Ghi chú: <?= e($order['note']) ?></p><?php endif; ?>
    <p class="muted small">Đặt lúc <?= dt($order['created_at']) ?></p>
    <form method="post" action="<?= url('admin/order_status.php') ?>" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
      <input type="hidden" name="back" value="admin/order_detail.php?id=<?= (int)$order['id'] ?>">
      <label>Trạng thái
        <select name="status"><?php foreach (ORDER_STATUS as $k => $v): ?><option value="<?= e($k) ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select>
      </label>
      <button class="btn btn-primary" type="submit">Cập nhật trạng thái</button>
    </form>
  </section>
  <section class="card form-card">
    <h2>Món đã đặt</h2>
    <ul class="plain">
      <?php foreach ($items as $it): ?>
        <li class="row-between"><span><?= (int)$it['quantity'] ?> × <strong><?= e($it['product_name']) ?></strong></span><span><?= money($it['unit_price'] * $it['quantity']) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <hr><p class="row-between"><strong>Tổng cộng</strong><strong><?= money($order['total']) ?></strong></p>
  </section>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
