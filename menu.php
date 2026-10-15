<?php
require __DIR__ . '/config.php';

// Lấy từ khóa tìm kiếm từ URL mặc định rỗng và xóa khoảng trắng ở đầu/cuối
$q = trim($_GET['q'] ?? '');

// Lấy mã danh mục từ URL mặc định là 0 và chuyển thành số nguyên
$cat = (int)($_GET['cat'] ?? 0);

//Lấy dữ liệu ra mảng
$categories = db('SELECT * FROM categories ORDER BY id')->fetchAll();

// SQL nối dần theo điều kiện khách chọn (nối thêm ' AND ...')
// Chỉ CÂU SQL được nối chuỗi; GIÁ TRỊ người dùng luôn nằm trong $args
$sql  = 'SELECT p.*, c.name AS category_name FROM products p
         JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1';
$args = [];
if ($q !== '') {
  $sql .= ' AND p.name LIKE ?';
  $args[] = '%' . $q . '%';
}
if ($cat) {
  $sql .= ' AND p.category_id = ?';
  $args[] = $cat;
}
$products = db($sql . ' ORDER BY p.category_id, p.id', $args)->fetchAll();

// Hiển thị
$title = 'Menu';
require __DIR__ . '/includes/header.php';
?>
<h1>Menu</h1>

<!-- Form GET: URL thành menu.php?q=matcha nên F5 hay gửi link vẫn giữ kết quả -->
<form class="searchbar" method="get" action="<?= url('menu.php') ?>" role="search">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm món, ví dụ: matcha" aria-label="Tìm món">
  <?php if ($cat): ?><input type="hidden" name="cat" value="<?= (int)$cat ?>"><?php endif; ?>
  <button class="btn btn-primary" type="submit">Tìm</button>
</form>

<nav class="chips" aria-label="Nhóm món">
  <a class="chip <?= !$cat ? 'on' : '' ?>" href="<?= url('menu.php' . ($q !== '' ? '?q=' . urlencode($q) : '')) ?>">Tất cả</a>
  <?php foreach ($categories as $c): ?>
    <a class="chip <?= $cat == $c['id'] ? 'on' : '' ?>"
      href="<?= url('menu.php?cat=' . $c['id'] . ($q !== '' ? '&q=' . urlencode($q) : '')) ?>"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
</nav>

<?php if (!$products): ?>
  <section class="empty">
    <div class="empty-art"><?= cup_svg(2) ?></div>
    <h2>Không có món nào khớp</h2>
    <p>Thử từ khoá khác hoặc xem tất cả món.</p>
    <a class="btn btn-ghost" href="<?= url('menu.php') ?>">Xem tất cả</a>
  </section>
<?php else: ?>
  <div class="grid">
    <?php foreach ($products as $i => $p): ?>
      <a class="card product" href="<?= url('mon.php?id=' . (int)$p['id']) ?>">
        <div class="thumb"><?php if (product_image($p)): ?><img src="<?= e(product_image($p)) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: echo cup_svg($i);
                                                                                                                                          endif; ?></div>
        <div class="card-body">
          <p class="muted small"><?= e($p['category_name']) ?></p>
          <h3><?= e($p['name']) ?></h3>
          <p class="desc"><?= e($p['description']) ?></p>
          <div class="row-between">
            <span class="price"><?= money($p['price']) ?></span>
            <span class="btn btn-primary btn-sm">Chọn món</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>