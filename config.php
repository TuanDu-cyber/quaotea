<?php
// Cấu hình + kết nối database

// bật $_SESSION (giỏ hàng, đăng nhập admin, mã CSRF)
session_start();       

// giờ hiển thị đúng giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');   

//Thông tin Sql
$DB = [
    'host'    => '127.0.0.1',
    'name'    => 'quaotea',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];

// nơi lưu ảnh món
define('UPLOAD_DIR', __DIR__ . '/assets/uploads');  

// tài khoản admin tạo tự động lần đầu
define('DEFAULT_ADMIN_USER', 'admin');              
define('DEFAULT_ADMIN_PASS', 'admin123');

// Đường dẫn gốc của website trên trình duyệt
define('BASE_URL', '/quaotea');

// Kết nối MySQL bằng PDO
try {
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'], $DB['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // SQL lỗi thì báo lỗi, không im lặng
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // mỗi dòng là mảng ['name' => ...]
            PDO::ATTR_EMULATE_PREPARES   => false,                   // prepared statement thật (chống SQL injection)
        ]
    );
} catch (PDOException $e) {
    exit('Không kết nối được database: ' . htmlspecialchars($e->getMessage()));
}

require __DIR__ . '/includes/functions.php';
