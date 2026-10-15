<?php
// Trang chi tiết 1 món: mon.php?id=5. Form gửi tới cart_add.php để thêm vào giỏ
require __DIR__ . '/config.php';

$p = db('SELECT p.*, c.name AS category_name FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.id = ? AND p.is_active = 1', [(int)($_GET['id'] ?? 0)])->fetch();

// Không có món hoặc món đã ẩn thì quay về menu
if (!$p) {
    flash('error', 'Món này hiện không có trong menu.');
    redirect('menu.php');
}

$title = $p['name'];
require __DIR__ . '/includes/header.php';
?>
<p><a href="<?= url('menu.php') ?>">← Quay lại menu</a></p>

<div class="two-col">
  <div class="card thumb">
    <?php if (product_image($p)): ?>
      <img src="<?= e(product_image($p)) ?>" alt="<?= e($p['name']) ?>">
    <?php else: echo cup_svg((int)$p['id']); endif; ?>
  </div>

  <section>
    <p class="muted small"><?= e($p['category_name']) ?></p>
    <h1><?= e($p['name']) ?></h1>
    <p class="muted"><?= e($p['description']) ?></p>
    <p class="price"><?= money($p['price']) ?></p>

    <form method="post" action="<?= url('cart_add.php') ?>" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
      <label>Số lượng
        <input class="qty" type="number" name="qty" min="1" max="20" value="1">
      </label>
      <button class="btn btn-primary" type="submit">Thêm vào giỏ</button>
    </form>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
