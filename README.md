# Quào Tea – website đặt trà sữa (PHP thuần, mỗi trang một file)

Website cho một tiệm trà sữa nhỏ, viết bằng **HTML + CSS + PHP thuần + MySQL**, không framework. Giao diện lấy cảm hứng từ https://quaotea.com/.

## Chức năng
**Khách hàng (không cần tài khoản):** xem menu, lọc theo nhóm, tìm món, xem ảnh và mô tả món, chọn số lượng, giỏ hàng, đặt đồ uống, tra cứu đơn bằng số điện thoại.

**Chủ tiệm (đăng nhập tại `admin/login.php`):** thêm/sửa/xoá món (upload ảnh, ẩn/hiện món), xem lịch sử đơn (lọc theo trạng thái / số điện thoại), cập nhật trạng thái đơn, thống kê đơn và doanh thu theo tuần/tháng.

## Cài đặt (XAMPP / Laragon, PHP 8.0+)
1. Chép thư mục `quaotea/` vào `htdocs` (hoặc `www`).
2. Import `quaotea_schema.sql` bằng phpMyAdmin (tự tạo database `quaotea` và dữ liệu mẫu).
3. Nếu MySQL của bạn khác mặc định (user `root`, mật khẩu rỗng), sửa mảng `$DB` ở đầu file `config.php`.
4. Mở `http://localhost/quaotea/`.
5. Quản trị: `http://localhost/quaotea/admin/login.php`, tài khoản mặc định **admin / admin123** (tự tạo ở lần đầu mở trang đăng nhập)
6. Thư mục `assets/uploads/` cần cho phép ghi để upload ảnh món.

Chạy nhanh không cần Apache: `php -S localhost:8000` ở thư mục dự án, rồi mở `http://localhost:8000/`.

## Cấu trúc
config.php            kết nối MySQL, session, hằng số (trang nào cũng require đầu tiên)
includes/             functions.php (hàm dùng chung), head/header/footer, auth.php (chặn chưa đăng nhập)
index.php             trang chủ
menu.php              menu, tìm món, lọc nhóm
mon.php               chi tiết 1 món + thêm vào giỏ
cart_add.php          thêm món vào giỏ
cart.php              giỏ hàng + form đặt hàng
checkout.php          ghi đơn vào DB (transaction)
order_success.php     đặt hàng thành công
tra-cuu.php           tra cứu đơn theo số điện thoại
admin/                login, logout, products, product_form, product_delete,
                      orders, order_detail, order_status, stats
assets/               css, uploads (ảnh món)
quaotea_schema.sql    schema + dữ liệu mẫu

## Bảo mật đã xử lý
Prepared statements (mọi dữ liệu người dùng đi qua dấu `?`), `password_hash`/`password_verify`, CSRF token cho mọi form POST, escape đầu ra bằng `e()`, giá đơn hàng tính lại ở server, kiểm tra loại và dung lượng ảnh upload, `session_regenerate_id` khi đăng nhập, chặn truy cập trực tiếp `includes/` bằng `.htaccess`.
