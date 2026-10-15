<?php
// Đầu trang khách head + thanh menu
require __DIR__ . '/head.php';

// tổng số ly trong giỏ
$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));   
?>
<header class="site-header">
  <a class="brand" href="<?= url('index.php') ?>"><span class="brand-mark">Q</span>Quào Tea</a>
  <nav class="nav" aria-label="Chính">
    <a href="<?= url('menu.php') ?>">Menu</a>
    <a href="<?= url('tra-cuu.php') ?>">Tra cứu đơn</a>
    <a class="nav-cart" href="<?= url('cart.php') ?>">Giỏ hàng<?php if ($cartCount > 0): ?><span class="count"><?= (int)$cartCount ?></span><?php endif; ?></a>
  </nav>
</header>

<main class="container">
<?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div><?php endif; ?>
