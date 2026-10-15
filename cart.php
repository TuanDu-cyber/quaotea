<?php
require __DIR__ . '/config.php';

//Xử lý thao tác sửa/xoá dòng (POST), xong chuyển hướng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $key = $_POST['key'] ?? '';
    if (isset($_SESSION['cart'][$key])) {
        if (($_POST['action'] ?? '') === 'remove') {
            unset($_SESSION['cart'][$key]);    // xoá dòng
        } else {
            $qty = (int)($_POST['qty'] ?? 1);  // đổi số lượng
            if ($qty <= 0) unset($_SESSION['cart'][$key]);  // về 0 thì xoá dòng
            else $_SESSION['cart'][$key]['qty'] = min(20, $qty);
        }
    }
    redirect('cart.php');
}

//Hiển thị 
$lines = $_SESSION['cart'] ?? [];
$total = cart_total($lines);

// chữ khách đã gõ ở form đặt hàng lần trước khi bị báo lỗi
$old   = $_SESSION['old'] ?? [];  
unset($_SESSION['old']);

$title = 'Giỏ hàng';
require __DIR__ . '/includes/header.php';
?>
<h1>Giỏ hàng</h1>
<?php if (!$lines): ?>
  <section class="empty">
    <div class="empty-art"><?= cup_svg(1) ?></div>
    <h2>Giỏ hàng đang trống</h2>
    <p>Chọn một ly trà để bắt đầu.</p>
    <a class="btn btn-primary" href="<?= url('menu.php') ?>">Xem menu</a>
  </section>
<?php else: ?>
<div class="two-col">
  <section>
    <ul class="lines">
      <?php foreach ($lines as $l): ?>
      <li class="line">
        <div class="line-main">
          <!-- Ảnh món có ảnh thật thì hiện ch có thì hiện ly trà SVG (product_image đọc $l['image']) -->
          <div class="line-thumb"><?php if (product_image($l)): ?><img src="<?= e(product_image($l)) ?>" alt="<?= e($l['name']) ?>"><?php else: echo cup_svg((int)$l['product_id']); endif; ?></div>
          <div>
            <strong><?= e($l['name']) ?></strong>
            <p class="price"><?= money($l['unit_price']) ?></p>
          </div>
        </div>
        <div class="line-actions">
          <!-- Đổi số lượng là POST ko phải link vì làm thay đổi dữ liệu. Số 0 = xoá dòng -->
          <form method="post" action="<?= url('cart.php') ?>" class="inline">
            <?= csrf_field() ?><input type="hidden" name="key" value="<?= e($l['key']) ?>">
            <input class="qty" type="number" name="qty" min="0" max="20" value="<?= (int)$l['qty'] ?>" aria-label="Số lượng">
            <button class="btn btn-sm" type="submit">Cập nhật</button>
          </form>
          <form method="post" action="<?= url('cart.php') ?>" class="inline">
            <?= csrf_field() ?><input type="hidden" name="key" value="<?= e($l['key']) ?>">
            <input type="hidden" name="action" value="remove">
            <button class="btn btn-link" type="submit">Xoá</button>
          </form>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <p class="total-row">Tổng cộng <strong><?= money($total) ?></strong></p>
  </section>

  <section class="card form-card">
    <h2>Thông tin nhận đồ uống</h2>
    <form method="post" action="<?= url('checkout.php') ?>" class="form">
      <?= csrf_field() ?>
      <label>Họ tên <input name="name" required maxlength="100" value="<?= e($old['name'] ?? '') ?>" autocomplete="name"></label>
      <label>Số điện thoại <input name="phone" required inputmode="tel" placeholder="0901234567" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel"></label>
      <label>Địa chỉ <input name="address" required maxlength="255" value="<?= e($old['address'] ?? '') ?>" autocomplete="street-address"></label>
      <label>Ghi chú <textarea name="note" rows="2" maxlength="255"><?= e($old['note'] ?? '') ?></textarea></label>
      <!-- Chỉ có 1 phương thức nên hiển thị cố định ko lưu vào database -->
      <div>
        <span class="pay-label">Phương thức thanh toán</span>
        <div class="pay-box"><span class="pay-radio" aria-hidden="true"></span>Thanh toán khi nhận hàng</div>
      </div>
      <p class="muted small">Dùng số điện thoại này để tra cứu đơn sau khi đặt.</p>
      <button class="btn btn-primary btn-block" type="submit">Đặt món · <?= money($total) ?></button>
    </form>
  </section>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
