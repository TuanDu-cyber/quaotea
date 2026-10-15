<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';

$status = $_GET['status'] ?? '';

// chỉ nhận trạng thái có trong bảng, mã lạ coi như ko lọc
if (!isset(ORDER_STATUS[$status])) $status = '';    
$phone = trim($_GET['phone'] ?? '');

$sql  = 'SELECT * FROM orders WHERE 1=1';
$args = [];
if ($status !== '') { $sql .= ' AND status = ?';  $args[] = $status; }
if ($phone !== '')  { $sql .= ' AND phone LIKE ?'; $args[] = '%' . $phone . '%'; }

// limit ko tải hàng nghìn đơn cùng lúc
$orders = db($sql . ' ORDER BY created_at DESC LIMIT 300', $args)->fetchAll();   

$title = 'Đơn hàng';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Đơn hàng</h1>
<!-- Form lọc dùng GET -->
<form class="searchbar" method="get" action="<?= url('admin/orders.php') ?>">
  <input type="search" name="phone" value="<?= e($phone) ?>" placeholder="Tìm theo số điện thoại" aria-label="Số điện thoại">
  <select name="status" aria-label="Trạng thái">
    <option value="">Mọi trạng thái</option>
    <?php foreach (ORDER_STATUS as $k => $v): ?><option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-ghost" type="submit">Lọc</button>
</form>

<?php if (!$orders): ?>
  <section class="empty"><h2>Không có đơn nào</h2><p>Đơn mới sẽ hiện ở đây ngay khi khách đặt.</p></section>
<?php else: ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Mã đơn</th><th>Khách</th><th>Thời gian</th><th>Tổng</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><a href="<?= url('admin/order_detail.php?id=' . $o['id']) ?>"><strong><?= e($o['code']) ?></strong></a></td>
      <td><?= e($o['customer_name']) ?><br><span class="muted small"><?= e($o['phone']) ?></span></td>
      <td><?= dt($o['created_at']) ?></td>
      <td><?= money($o['total']) ?></td>
      <td><?= status_badge($o['status']) ?></td>
      <td>
        <form method="post" action="<?= url('admin/order_status.php') ?>" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
          <!-- 'back': URL hiện tại (kèm bộ lọc) để sau khi đổi trạng thái quay lại đúng chỗ -->
          <input type="hidden" name="back" value="<?= e('admin/orders.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
          <select name="status" aria-label="Đổi trạng thái đơn <?= e($o['code']) ?>">
            <?php foreach (ORDER_STATUS as $k => $v): ?><option value="<?= e($k) ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-sm" type="submit">Lưu</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
