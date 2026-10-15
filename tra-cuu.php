<?php
require __DIR__ . '/config.php';

// $orders = null nghĩa là "chưa tra cứu"; mảng rỗng = "tra rồi mà không có đơn"
$phoneRaw = trim($_GET['phone'] ?? '');
$orders = null; $items = []; $error = '';

if ($phoneRaw !== '') {
    $phone = normalize_phone($phoneRaw);    // cùng cách chuẩn hoá lúc đặt hàng
    if (!valid_phone($phone)) {
        $error = 'Số điện thoại gồm 10 số và bắt đầu bằng 0.';
    } else {
        $orders = db('SELECT * FROM orders WHERE phone = ? ORDER BY created_at DESC', [$phone])->fetchAll();
        if ($orders) {
            // Lấy món của TẤT CẢ đơn bằng 1 câu SQL (IN), rồi nhóm theo order_id. Tránh chạy SQL trong vòng lặp.
            $ids = array_column($orders, 'id');
            $in  = implode(',', array_fill(0, count($ids), '?'));
            foreach (db("SELECT * FROM order_items WHERE order_id IN ($in) ORDER BY id", $ids)->fetchAll() as $r) {
                $items[$r['order_id']][] = $r;
            }
        }
    }
}

$title = 'Tra cứu đơn';
require __DIR__ . '/includes/header.php';
?>
<h1>Tra cứu đơn</h1>
<form class="searchbar" method="get" action="<?= url('tra-cuu.php') ?>">
  <input type="tel" name="phone" value="<?= e($phoneRaw) ?>" placeholder="Nhập số điện thoại đã đặt" required aria-label="Số điện thoại">
  <button class="btn btn-primary" type="submit">Tra cứu</button>
</form>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>

<?php if ($orders !== null): ?>
  <?php if (!$orders): ?>
    <section class="empty"><div class="empty-art"><?= cup_svg(4) ?></div><h2>Chưa có đơn nào với số này</h2><p>Kiểm tra lại số điện thoại hoặc đặt món mới.</p><a class="btn btn-ghost" href="<?= url('menu.php') ?>">Xem menu</a></section>
  <?php else: foreach ($orders as $o): ?>
    <article class="card order-card">
      <div class="row-between"><strong><?= e($o['code']) ?></strong><?= status_badge($o['status']) ?></div>
      <p class="muted small"><?= dt($o['created_at']) ?> · <?= e($o['address']) ?></p>
      <ul class="plain">
        <?php foreach ($items[$o['id']] ?? [] as $it): ?>
          <li><?= (int)$it['quantity'] ?> × <?= e($it['product_name']) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="row-between"><span class="muted">Tổng</span><strong><?= money($o['total']) ?></strong></p>
    </article>
  <?php endforeach; endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
