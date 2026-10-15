<?php
//Phần head dùng chung

//thông báo (nếu có) từ lần trước để header in ra
$flash = take_flash();  
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? '') ?> · Quào Tea</title>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
