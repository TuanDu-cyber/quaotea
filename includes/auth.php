<?php
// Chặn người chưa đăng nhập, mọi trang admin trừ login.php
if (empty($_SESSION['admin_id'])) {
    redirect('admin/login.php');
}
