<?php
// Nhận form "Chọn món" (POST), thêm vào giỏ ($_SESSION['cart']) rồi chuyển hướng
require __DIR__ . '/config.php';
csrf_check();

// Tìm món trong DB theo id khách gửi, ko có hoặc đã ẩn thì từ chối
$product = db('SELECT * FROM products WHERE id = ?', [(int)($_POST['product_id'] ?? 0)])->fetch();
if (!$product || !$product['is_active']) {
    flash('error', 'Món này hiện không có trong menu.');
    redirect('menu.php');
}

// kẹp số lượng trong 1..20
$qty = max(1, min(20, (int)($_POST['qty'] ?? 1)));   

// dùng id món làm khoá của dòng trong giỏ
$id  = (int)$product['id'];                          

if (isset($_SESSION['cart'][$id])) {
    // Món này đã có trong giỏ chỉ cộng thêm số lượng tối đa 20
    $_SESSION['cart'][$id]['qty'] = min(20, $_SESSION['cart'][$id]['qty'] + $qty);
} else {
    // Giá lấy từ DB ko tin giá từ trình duyệt. Lưu cả tên và giá lúc thêm
    $_SESSION['cart'][$id] = [
        'key' => $id, 'product_id' => $id, 'name' => $product['name'],

        //trang giỏ hàng hiện ảnh món
        'image' => $product['image'],            
        'unit_price' => $product['price'], 'qty' => $qty,
    ];
}

redirect('menu.php');  
