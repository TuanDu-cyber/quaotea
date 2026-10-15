<?php
require __DIR__ . '/config.php';

$title = 'Trang chủ';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-text">
    <p class="hero-kicker">Pha mới mỗi ly</p>
    <h1>Chạm để chọn món, trà ngon có liền.</h1>
    <p class="lead">Chọn hương vị yêu thích, đặt trong vài cú chạm và theo dõi ly trà của bạn bằng số điện thoại.</p>
    <div class="actions">
      <a class="btn btn-primary" href="<?= url('menu.php') ?>">Đặt món</a>
      <a class="btn btn-ghost" href="<?= url('tra-cuu.php') ?>">Tra cứu đơn</a>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="peek"><?= cup_svg(0) ?></div>
    <div class="hero-blob"></div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
