<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';

// Chỉ nhận 'week' hoặc 'month', giá trị này quyết định đoạn SQL nên phải lọc chặt
$mode = ($_GET['mode'] ?? 'month') === 'week' ? 'week' : 'month';

if ($mode === 'week') {
    // WEEKDAY() = 0 cho thứ Hai trừ đi số ngày lệch để ra "ngày thứ Hai đầu tuần" làm khoá nhóm
    $key   = "DATE_FORMAT(DATE_SUB(DATE(created_at), INTERVAL WEEKDAY(created_at) DAY), '%Y-%m-%d')";
    $since = 'DATE_SUB(CURDATE(), INTERVAL 12 WEEK)';
} else {
    $key   = "DATE_FORMAT(created_at, '%Y-%m')";
    $since = 'DATE_SUB(CURDATE(), INTERVAL 12 MONTH)';
}

// GROUP BY gom các đơn cùng kỳ; SUM(status='done') đếm đơn hoàn thành, case chỉ cộng tiền đơn hoàn thành
$rows = db("SELECT $key AS period,
                   COUNT(*) AS orders,
                   SUM(status = 'done') AS done_orders,
                   SUM(status = 'cancelled') AS cancelled_orders,
                   COALESCE(SUM(CASE WHEN status = 'done' THEN total END), 0) AS revenue
            FROM orders WHERE created_at >= $since
            GROUP BY period ORDER BY period DESC")->fetchAll();

$totals = db("SELECT COUNT(*) AS orders,
                     COALESCE(SUM(status='pending'),0) AS pending,
                     COALESCE(SUM(CASE WHEN status='done' THEN total END),0) AS revenue
              FROM orders")->fetch();

$top = db("SELECT oi.product_name, SUM(oi.quantity) AS qty
           FROM order_items oi JOIN orders o ON o.id = oi.order_id
           WHERE o.status <> 'cancelled'
           GROUP BY oi.product_name ORDER BY qty DESC LIMIT 5")->fetchAll();

// Doanh thu lớn nhất để tính độ dài cột; max(1, ...) tránh chia cho 0 khi chưa có doanh thu
$maxRevenue = max(1, ...array_map('intval', array_column($rows, 'revenue') ?: [0]));

$title = 'Thống kê';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="row-between wrap">
  <h1>Thống kê đơn hàng</h1>
  <div class="chips">
    <a class="chip <?= $mode === 'week' ? 'on' : '' ?>" href="<?= url('admin/stats.php?mode=week') ?>">Theo tuần</a>
    <a class="chip <?= $mode === 'month' ? 'on' : '' ?>" href="<?= url('admin/stats.php?mode=month') ?>">Theo tháng</a>
  </div>
</div>

<div class="kpis">
  <div class="card kpi"><span class="muted small">Tổng số đơn</span><strong><?= (int)$totals['orders'] ?></strong></div>
  <div class="card kpi"><span class="muted small">Đơn chờ xác nhận</span><strong><?= (int)$totals['pending'] ?></strong></div>
  <div class="card kpi"><span class="muted small">Doanh thu (đơn hoàn thành)</span><strong><?= money($totals['revenue']) ?></strong></div>
</div>

<h2><?= $mode === 'week' ? '12 tuần gần nhất' : '12 tháng gần nhất' ?></h2>
<?php if (!$rows): ?>
  <section class="empty"><h2>Chưa có dữ liệu</h2><p>Số liệu sẽ xuất hiện khi có đơn đầu tiên.</p></section>
<?php else: ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th><?= $mode === 'week' ? 'Tuần bắt đầu' : 'Tháng' ?></th><th>Số đơn</th><th>Hoàn thành</th><th>Đã huỷ</th><th>Doanh thu</th><th class="bar-col"></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <!-- Tuần: '2026-10-12' => 12/10/2026. Tháng: nối '-01' để strtotime hiểu => 10/2026 -->
      <td><?= $mode === 'week' ? e(date('d/m/Y', strtotime($r['period']))) : e(date('m/Y', strtotime($r['period'] . '-01'))) ?></td>
      <td><?= (int)$r['orders'] ?></td><td><?= (int)$r['done_orders'] ?></td><td><?= (int)$r['cancelled_orders'] ?></td>
      <td><strong><?= money($r['revenue']) ?></strong></td>
      <!-- Biểu đồ cột bằng CSS thuần: độ rộng = doanh thu / doanh thu lớn nhất × 100% -->
      <td class="bar-col"><div class="bar"><span style="width: <?= round($r['revenue'] / $maxRevenue * 100) ?>%"></span></div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>

<?php if ($top): ?>
<h2>Món bán chạy</h2>
<ol class="top">
  <?php foreach ($top as $t): ?><li><span><?= e($t['product_name']) ?></span><strong><?= (int)$t['qty'] ?> ly</strong></li><?php endforeach; ?>
</ol>
<?php endif; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
