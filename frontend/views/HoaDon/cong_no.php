<?php
$title = 'Danh sách công nợ';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title">
    <h1>Danh sách công nợ</h1>
    <p><?= count($list??[]) ?> hóa đơn chưa thanh toán</p>
  </div>
  <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<?php if(!empty($list)): ?>
<div class="card">
  <table class="tbl">
    <thead>
      <tr><th>#</th><th>Phòng</th><th>Tháng/Năm</th><th>Tổng tiền</th><th>Ngày tạo</th><th style="text-align:center">Thao tác</th></tr>
    </thead>
    <tbody>
      <?php foreach($list as $i=>$hd): ?>
      <tr>
        <td style="color:var(--text3)"><?= $i+1 ?></td>
        <td><span class="td-name"><?= htmlspecialchars($hd['so_phong']) ?></span></td>
        <td>Tháng <?= $hd['thang'] ?>/<?= $hd['nam'] ?></td>
        <td><strong style="color:var(--red)"><?= number_format($hd['tong_tien']) ?>đ</strong></td>
        <td style="color:var(--text3)"><?= date('d/m/Y',strtotime($hd['created_at'])) ?></td>
        <td style="text-align:center">
          <a href="index.php?controller=hoadon&action=thanhToan&id=<?= $hd['id'] ?>"
             class="btn btn-success btn-xs"
             onclick="return confirm('Xác nhận đã thu tiền phòng <?= htmlspecialchars($hd['so_phong']) ?>?')">✓ Thu tiền</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:48px;">
  <div style="font-size:36px;margin-bottom:10px">✅</div>
  <div style="font-size:15px;font-weight:600;color:var(--green)">Không có công nợ nào!</div>
  <div style="font-size:13px;color:var(--text3);margin-top:4px">Tất cả hóa đơn đã được thanh toán</div>
</div>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>