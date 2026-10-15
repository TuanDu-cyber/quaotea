<?php
require __DIR__ . '/../config.php';

// đã đăng nhập rồi thì khỏi vào form
if (!empty($_SESSION['admin_id'])) redirect('admin/orders.php');   

// Lần đầu chưa có tài khoản nào tự tạo admin mặc định
//ko bao giờ lưu mật khẩu chữ thường
if ((int)db('SELECT COUNT(*) FROM admins')->fetchColumn() === 0) {
    db('INSERT INTO admins (username, password_hash) VALUES (?, ?)',
       [DEFAULT_ADMIN_USER, password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT)]);
}

//Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $admin = db('SELECT * FROM admins WHERE username = ?', [trim($_POST['username'] ?? '')])->fetch();
    // password_verify: so mật khẩu gõ vào với chuỗi băm đã lưu
    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {

        // đổi mã phiên sau khi đăng nhập (chống chiếm phiên)
        session_regenerate_id(true);               
        $_SESSION['admin_id']   = $admin['id'];    
        $_SESSION['admin_name'] = $admin['username'];
        redirect('admin/orders.php');
    }
    // Sai tên hay sai mật khẩu thông báo, không để lộ tài khoản nào tồn tại
    flash('error', 'Sai tên đăng nhập hoặc mật khẩu.');
    redirect('admin/login.php');
}

$title = 'Đăng nhập chủ tiệm';
$bodyClass = 'auth';
require __DIR__ . '/../includes/head.php';
?>
<?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div><?php endif; ?>
<main class="login">
  <form method="post" action="<?= url('admin/login.php') ?>" class="card form-card form">
    <div class="brand center-brand"><span class="brand-mark">Q</span>Quào Tea</div>
    <h1>Đăng nhập chủ tiệm</h1>
    <?= csrf_field() ?>
    <label>Tên đăng nhập <input name="username" required autofocus autocomplete="username"></label>
    <label>Mật khẩu <input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn btn-primary btn-block" type="submit">Đăng nhập</button>
    <a class="muted small center" href="<?= url('index.php') ?>">Về trang khách hàng</a>
  </form>
</main>
</body>
</html>
