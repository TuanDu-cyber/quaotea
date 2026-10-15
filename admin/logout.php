<?php
// Đăng xuất (POST + CSRF
require __DIR__ . '/../config.php';
csrf_check();

// xoá toàn bộ dữ liệu phiên
$_SESSION = [];       

// huỷ phiên
session_destroy();    
header('Location: ' . url('admin/login.php'));
exit;
