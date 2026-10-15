<?php
// Đầu trang quản trị: <head> + menu quản trị + nút đăng xuất.
require __DIR__ . '/../../includes/head.php';
?>
<header class="site-header">
  <a class="brand" href="<?= url('admin/orders.php') ?>"><span class="brand-mark">Q</span>Quản trị</a>
  <nav class="nav" aria-label="Quản trị">
    <a href="<?= url('admin/orders.php') ?>">Đơn hàng</a>
    <a href="<?= url('admin/products.php') ?>">Món</a>
    <a href="<?= url('admin/stats.php') ?>">Thống kê</a>
    <a href="<?= url('index.php') ?>" target="_blank" rel="noopener">Xem web</a>
  </nav>
  <!-- Đăng xuất bằng POST + CSRF để người lạ không đăng xuất bạn bằng 1 đường link -->
  <form method="post" action="<?= url('admin/logout.php') ?>" class="inline">
    <?= csrf_field() ?>
    <button class="btn btn-ghost btn-sm" type="submit">Đăng xuất (<?= e($_SESSION['admin_name'] ?? '') ?>)</button>
  </form>
</header>
<main class="container wide">
<?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div><?php endif; ?>
