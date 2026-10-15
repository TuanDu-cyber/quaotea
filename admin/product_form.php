<?php
// Form thêm/sửa món + xử lý lưu, có ?id=5 là SỬA món 5, không có là thêm ms
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$product = null;
if ($id) {
    $product = db('SELECT * FROM products WHERE id = ?', [$id])->fetch();
    if (!$product) redirect('admin/products.php');
}
$editing = $product !== null;
$categories = db('SELECT * FROM categories ORDER BY id')->fetchAll();

//Xử lý form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // lỗi thì quay lại đúng form này
    $back = 'admin/product_form.php' . ($editing ? '?id=' . $id : '');  

    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (int)($_POST['price'] ?? 0);

    // checkbox chỉ có trong $_POST khi được tick
    $isActive    = isset($_POST['is_active']) ? 1 : 0;   
    
    // mặc định giữ ảnh cũ
    $image       = $product['image'] ?? null;              

    if ($name === '' || mb_strlen($name) > 150) { flash('error', 'Tên món không được để trống (tối đa 150 ký tự).'); redirect($back); }
    if ($price < 1000)                           { flash('error', 'Giá phải từ 1.000đ trở lên.'); redirect($back); }
    if (!$categoryId)                            { flash('error', 'Vui lòng chọn nhóm món.'); redirect($back); }

    // Upload ảnh $_FILES['image']['error'] === UPLOAD_ERR_OK nghĩa là có file và tải lên ổn
    $f = $_FILES['image'] ?? null;
    if ($f && $f['error'] === UPLOAD_ERR_OK) {
        // Đọc loại file thật từ nội dung ko tin đuôi file hay Content-Type do khách gửi
        $mime = mime_content_type($f['tmp_name']);
        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        if (!$ext)                        { flash('error', 'Ảnh phải là JPG, PNG hoặc WEBP.'); redirect($back); }
        if ($f['size'] > 2 * 1024 * 1024) { flash('error', 'Ảnh tối đa 2MB.'); redirect($back); }

        // tên ngẫu nhiên: không trùng, không độc hại
        $file = bin2hex(random_bytes(8)) . '.' . $ext;      
        if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $file)) {
            flash('error', 'Không lưu được ảnh, kiểm tra quyền ghi thư mục assets/uploads.'); redirect($back);
        }
        // có ảnh mới thì xoá ảnh cũ
        remove_image($image);   
        $image = $file;
    }

    if ($editing) {
        db('UPDATE products SET category_id=?, name=?, description=?, price=?, image=?, is_active=? WHERE id=?',
           [$categoryId, $name, $description, $price, $image, $isActive, $id]);
        flash('ok', 'Đã lưu thay đổi.');
    } else {
        db('INSERT INTO products (category_id,name,description,price,image,is_active) VALUES (?,?,?,?,?,?)',
           [$categoryId, $name, $description, $price, $image, $isActive]);
        flash('ok', 'Đã thêm món "' . $name . '".');
    }
    redirect('admin/products.php');
}

//Hiển thị form
$title = $editing ? 'Sửa món' : 'Thêm món';
require __DIR__ . '/includes/admin_header.php';
?>
<h1><?= e($title) ?></h1>
<!-- enctype="multipart/form-data" bắt buộc khi form có upload file, thiếu thì $_FILES rỗng -->
<form method="post" enctype="multipart/form-data" class="card form-card form narrow"
      action="<?= url('admin/product_form.php' . ($editing ? '?id=' . $id : '')) ?>">
  <?= csrf_field() ?>
  <label>Tên món <input name="name" required maxlength="150" value="<?= e($product['name'] ?? '') ?>"></label>
  <label>Nhóm
    <select name="category_id" required>
      <option value="">Chọn nhóm</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= ($product['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Giá (đồng) <input type="number" name="price" min="1000" step="500" required value="<?= e($product['price'] ?? '') ?>"></label>
  <label>Mô tả <textarea name="description" rows="3" maxlength="500"><?= e($product['description'] ?? '') ?></textarea></label>
  <label>Ảnh (JPG, PNG, WEBP, tối đa 2MB)
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  </label>
  <?php if ($editing && $product['image']): ?><img class="preview" src="<?= e(product_image($product)) ?>" alt="Ảnh hiện tại"><?php endif; ?>
  <label class="check"><input type="checkbox" name="is_active" value="1" <?= !$editing || $product['is_active'] ? 'checked' : '' ?>> Đang bán (hiện trên menu)</label>
  <div class="actions">
    <button class="btn btn-primary" type="submit">Lưu món</button>
    <a class="btn btn-ghost" href="<?= url('admin/products.php') ?>">Huỷ</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
