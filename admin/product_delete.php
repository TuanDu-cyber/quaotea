<?php
// Xoá món, GET = trang hỏi xác nhận, POST + CSRF = xoá thật
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $p = db('SELECT * FROM products WHERE id = ?', [(int)($_POST['id'] ?? 0)])->fetch();
    if ($p) {
        // Xoá được vì order_items.product_id là ON DELETE SET NULL: đơn cũ vẫn giữ tên món đã chụp
        db('DELETE FROM products WHERE id = ?', [$p['id']]);
        remove_image($p['image']);
        flash('ok', 'Đã xoá "' . $p['name'] . '". Đơn cũ vẫn giữ nguyên tên món.');
    }
    redirect('admin/products.php');
}

// GET hỏi lại cho chắc 
$p = db('SELECT * FROM products WHERE id = ?', [(int)($_GET['id'] ?? 0)])->fetch();
if (!$p) redirect('admin/products.php');

$title = 'Xoá món';
require __DIR__ . '/includes/admin_header.php';
?>
<section class="card form-card narrow">
  <h1>Xoá món?</h1>
  <p>Bạn chắc chắn muốn xoá <strong><?= e($p['name']) ?></strong>? Đơn cũ vẫn giữ nguyên tên món.</p>
  <form method="post" action="<?= url('admin/product_delete.php') ?>" class="actions">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
    <button class="btn btn-danger" type="submit">Xoá</button>
    <a class="btn" href="<?= url('admin/products.php') ?>">Huỷ</a>
  </form>
</section>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
