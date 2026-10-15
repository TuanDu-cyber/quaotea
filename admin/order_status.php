<?php
// Đổi trạng thái đơn dùng chung cho trang danh sách và trang chi tiết
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';
csrf_check();

$status = $_POST['status'] ?? '';
$order  = db('SELECT * FROM orders WHERE id = ?', [(int)($_POST['id'] ?? 0)])->fetch();

// Phải vừa có đơn vừa có trạng thái hợp lệ (nằm trong ORDER_STATUS) mới ghi
if ($order && isset(ORDER_STATUS[$status])) {
    db('UPDATE orders SET status = ? WHERE id = ?', [$status, $order['id']]);
    flash('ok', 'Đơn ' . $order['code'] . ' chuyển sang "' . status_label($status) . '".');
}
// 'back' = trang để quay về giữ bộ lọc, url() luôn ghép BASE_URL nên ko chuyển sang web khác được
redirect($_POST['back'] ?? 'admin/orders.php');
