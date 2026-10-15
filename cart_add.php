<?php
// Nhận form "Chọn món" (POST), thêm vào giỏ ($_SESSION['cart']) rồi chuyển hướng
require __DIR__ . '/config.php';
csrf_check();

// Tìm món trong DB theo id khách gửi. Không có hoặc đã ẩn thì từ chối
$product = db('SELECT * FROM products WHERE id = ?', [(int)($_POST['product_id'] ?? 0)])->fetch();
if (!$product || !$product['is_active']) {
    flash('error', 'Món này hiện không có trong menu.');
    redirect('menu.php');
}

$qty = max(1, min(20, (int)($_POST['qty'] ?? 1)));   // kẹp số lượng trong 1..20
$id  = (int)$product['id'];                          // dùng id món làm khoá của dòng trong giỏ

if (isset($_SESSION['cart'][$id])) {
    // Món này đã có trong giỏ: chỉ cộng thêm số lượng (tối đa 20)
    $_SESSION['cart'][$id]['qty'] = min(20, $_SESSION['cart'][$id]['qty'] + $qty);
} else {
    // Giá lấy từ DB (không tin giá từ trình duyệt). Lưu cả tên và giá lúc thêm.
    $_SESSION['cart'][$id] = [
        'key' => $id, 'product_id' => $id, 'name' => $product['name'],
        'image' => $product['image'],            // để trang giỏ hàng hiện ảnh món
        'unit_price' => $product['price'], 'qty' => $qty,
    ];
}

// Không hiện thông báo: khách thấy số trên chữ "Giỏ hàng" ở header tăng lên là biết món đã vào giỏ
redirect('menu.php');  
