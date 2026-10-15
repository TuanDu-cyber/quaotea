<?php
//Các hàm dùng chung cho mọi trang

//Hiển thị/ đường dẫn

// Escape ký tự đặc biệt khi in ra HTML để chống XSS, (string) để nhận cả số/null
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Tạo link: url('menu.php') => '/quaotea/menu.php'. ltrim bỏ dấu / đầu để gõ kiểu nào cũng đúng
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }

// Link tới file tĩnh trong thư mục assets 
function asset(string $path): string { return BASE_URL . '/assets/' . ltrim($path, '/'); }

// 38000 => '38.000đ'. number_format(số, số lẻ, dấu thập phân, dấu ngăn nghìn)
function money($n): string { return number_format((float)$n, 0, ',', '.') . 'đ'; }

// Đổi format '2026-10-14 09:30:00' => '09:30 14/10/2026'
function dt($s): string { return date('H:i d/m/Y', strtotime($s)); }

// Chuyển sang trang khác rồi DỪNG script/exit, nếu không code bên dưới vẫn chạy tiếp
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

// Database

// Hàm db() dùng để thực thi câu SQL với các giá trị truyền vào an toàn
function db(string $sql, array $params = []): PDOStatement
{
    global $pdo;
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st;
}

//Thông báo

// Lưu 1 thông báo để hiện ở lần tải trang kế tiếp
function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

// Lấy thông báo ra rồi xoá luôn để chỉ hiện đúng một lần
function take_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

//Chống giả mạo form-CSRF

// In ô ẩn chứa mã bí mật vào mọi form POST, gọi hàm này ngay trong thẻ form
function csrf_field(): string
{
    // Chưa có mã thì sinh 1 lần cho cả phiên. random_bytes: ngẫu nhiên an toàn; bin2hex: đổi thành chữ-số
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="_csrf" value="' . $_SESSION['csrf'] . '">';
}

// Gọi ở đầu mọi trang xử lý POST: ko phải POST hoặc sai mã thì dừng
function csrf_check(): void
{
    $ok = $_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['_csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['_csrf']);   // hash_equals: so sánh chuỗi an toàn
    if (!$ok) {
        http_response_code(419);
        exit('Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ. Hãy quay lại, tải lại trang và thử lại.');
    }
}

//Đơn hàng

// Bảng tra mã trạng thái lưu trong DB sang chữ hiển thị
const ORDER_STATUS = [
    'pending'    => 'Chờ xác nhận',
    'preparing'  => 'Đang pha chế',
    'delivering' => 'Đang giao',
    'done'       => 'Hoàn thành',
    'cancelled'  => 'Đã huỷ',
];

// ?? $s: mã lạ thì giữ nguyên
function status_label(string $s): string { return ORDER_STATUS[$s] ?? $s; } 

// Nhãn có màu: class badge-<mã> để CSS tô màu theo trạng thái
function status_badge(string $s): string
{
    return '<span class="badge badge-' . e($s) . '">' . e(status_label($s)) . '</span>';
}

// Tổng tiền giỏ = tổng (đơn giá × số lượng)
function cart_total(array $lines): int
{
    $t = 0;
    foreach ($lines as $l) $t += $l['unit_price'] * $l['qty'];
    return $t;
}

// Chuẩn hoá số điện thoại: bỏ khoảng trắng/dấu
// \s là khoảng trắng, . là dấu chấm, \- là dấu gạch ngang - , () ngoặc đơn
function normalize_phone(string $raw): string
{
    $p = preg_replace('/[\s.\-()]/', '', $raw);
    return $p;
}

// Số điện thoại hợp lệ: đúng 10 số, bắt đầu bằng 0
function valid_phone(string $p): bool { return (bool)preg_match('/^0\d{9}$/', $p); }

//Ảnh món

// Đường dẫn ảnh của món, hoặc '' nếu chưa có ảnh khi đó trang hiện ly trà SVG
function product_image(array $p): string
{
    return !empty($p['image']) ? asset('uploads/' . $p['image']) : '';
}

// Xoá file ảnh trong uploads. basename(): chỉ lấy tên file, chặn kiểu '../../'. @: bỏ cảnh báo nếu file không có
function remove_image(?string $file): void
{
    if ($file) @unlink(UPLOAD_DIR . '/' . basename($file));
}

// Ly trà vẽ bằng SVG, dùng khi món chưa có ảnh. $seed % số màu => mỗi món một màu, không vượt mảng.
function cup_svg(int $seed = 0): string
{
    $colors = ['#7BC8A4', '#F6B99A', '#C9A7EB', '#F2D16B', '#8EC5F0', '#F29BB0'];
    $c = $colors[$seed % count($colors)];
   return '<svg viewBox="0 0 1024 1024" class="cup" aria-hidden="true" style="color:' . $c . '">'
        . '<path fill="currentColor" d="M416 128V96a96 96 0 0196-96h128a32 32 0 110 64H512a32 32 0 00-32 32v32h320a96 96 0 0111.712 191.296l-39.68 581.056A64 64 0 01708.224 960H315.776a64 64 0 01-63.872-59.648l-39.616-581.056A96 96 0 01224 128h192zM276.48 320l39.296 576h392.448l4.8-70.784a224.064 224.064 0 0130.016-439.808L747.52 320H276.48zM224 256h576a32 32 0 100-64H224a32 32 0 000 64zm493.44 503.872l21.12-309.12a160 160 0 00-21.12 309.12z"/>'
        . '</svg>';
}
