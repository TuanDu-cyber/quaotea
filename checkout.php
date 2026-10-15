<?php
// Nhận form đặt hàng (POST): kiểm tra, ghi vào DB (orders + order_items), xoá giỏ. Không in HTML
require __DIR__ . '/config.php';
csrf_check();

$lines = $_SESSION['cart'] ?? [];
if (!$lines) {   // giỏ trống mà vẫn bấm đặt (vd mở 2 tab) thì chặn
    flash('error', 'Giỏ hàng đang trống.');
    redirect('menu.php');
}

$name    = trim($_POST['name'] ?? '');
$phone   = normalize_phone($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$note    = trim($_POST['note'] ?? '');

// Kiểm tra dữ liệu: gom lỗi vào mảng $err(required trong HTML chỉ để tiện, kiểm tra thật phải ở server)
$err = [];
if ($name === '' || mb_strlen($name) > 100) $err[] = 'Vui lòng nhập tên (tối đa 100 ký tự).';   // mb_strlen đếm đúng tiếng Việt
if (!valid_phone($phone))                   $err[] = 'Số điện thoại gồm 10 số và bắt đầu bằng 0.';
if ($address === '')                        $err[] = 'Vui lòng nhập địa chỉ nhận đồ uống.';

if ($err) {
    flash('error', implode(' ', $err));
    $_SESSION['old'] = ['name' => $name, 'phone' => $phone, 'address' => $address, 'note' => $note];  // để điền lại form
    redirect('cart.php');
}

// Transaction: các lệnh bên trong hoặc thành công cả, hoặc huỷ cả (không để đơn dở dang)
$pdo->beginTransaction();
try {
    // Mã đơn: QT + ngày + 4 số ngẫu nhiên, lặp lại nếu trùng
    do {
        $code = 'QT' . date('ymd') . random_int(1000, 9999);
        $exists = db('SELECT 1 FROM orders WHERE code = ?', [$code])->fetch();
    } while ($exists);

    db('INSERT INTO orders (code, customer_name, phone, address, note, total) VALUES (?,?,?,?,?,?)',
       [$code, $name, $phone, $address, $note, cart_total($lines)]);   // tổng tính lại từ giỏ ở server
    $orderId = (int)$pdo->lastInsertId();   // id của dòng vừa INSERT, để gắn các món vào đơn

    foreach ($lines as $l) {
        // Lưu cả TÊN và GIÁ món lúc đặt (bản chụp), nên sau này đổi giá hay xoá món thì đơn cũ vẫn đúng
        db('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (?,?,?,?,?)',
           [$orderId, $l['product_id'], $l['name'], $l['qty'], $l['unit_price']]);
    }
    $pdo->commit();      // mọi INSERT đều ổn thì ghi thật
} catch (Throwable $e) {
    $pdo->rollBack();    // có lỗi giữa chừng thì huỷ hết
    throw $e;
}

unset($_SESSION['cart']);
redirect('order_success.php?code=' . urlencode($code));
