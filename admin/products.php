<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';   // chưa đăng nhập thì bị đá về login

$q = trim($_GET['q'] ?? '');
$sql  = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
$args = [];
if ($q !== '') { $sql .= ' AND p.name LIKE ?'; $args[] = '%' . $q . '%'; }  // admin xem cả món đã ẩn
$products = db($sql . ' ORDER BY p.category_id, p.id', $args)->fetchAll();

$title = 'Quản lý món';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="row-between wrap">
  <h1>Quản lý món</h1>
  <a class="btn btn-primary" href="<?= url('admin/product_form.php') ?>">Thêm món</a>
</div>
<form class="searchbar" method="get" action="<?= url('admin/products.php') ?>">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên món" aria-label="Tìm món">
  <button class="btn btn-ghost" type="submit">Tìm</button>
</form>

<?php if (!$products): ?>
  <section class="empty"><h2>Chưa có món nào</h2><p>Thêm món đầu tiên để hiện trên menu.</p></section>
<?php else: ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Ảnh</th><th>Tên món</th><th>Nhóm</th><th>Giá</th><th>Trạng thái</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($products as $i => $p): ?>
    <tr>
      <td class="thumb-sm"><?php if (product_image($p)): ?><img src="<?= e(product_image($p)) ?>" alt=""><?php else: echo cup_svg($i); endif; ?></td>
      <td><strong><?= e($p['name']) ?></strong><br><span class="muted small"><?= e($p['description']) ?></span></td>
      <td><?= e($p['category_name']) ?></td>
      <td><?= money($p['price']) ?></td>
      <td><?= $p['is_active'] ? '<span class="badge badge-done">Đang bán</span>' : '<span class="badge badge-cancelled">Tạm ẩn</span>' ?></td>
      <td class="actions-cell">
        <a class="btn btn-ghost btn-sm" href="<?= url('admin/product_form.php?id=' . $p['id']) ?>">Sửa</a>
        <!-- Bấm Xoá chỉ mở trang hỏi xác nhận, chưa xoá gì -->
        <a class="btn btn-danger btn-sm" href="<?= url('admin/product_delete.php?id=' . $p['id']) ?>">Xoá</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
