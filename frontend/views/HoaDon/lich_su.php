<?php
$title = 'Lịch sử thanh toán';
require_once 'frontend/views/Layouts/header.php';

$ptMap = [
  'tien_mat'     => ['💵', 'Tiền mặt'],
  'chuyen_khoan' => ['🏦', 'Chuyển khoản'],
  'momo'         => ['📱', 'MoMo'],
  'vnpay'        => ['🔵', 'VNPay'],
  'zalopay'      => ['📲', 'ZaloPay'],
  'khac'         => ['💳', 'Khác'],
];
$ttMap = [
  'thanh_cong' => ['p-green', '✓ Thành công'],
  'that_bai'   => ['p-red', '✗ Thất bại'],
  'dang_xu_ly' => ['p-amber', '⏳ Đang xử lý'],
];
?>

<div class="page-header">
  <div class="page-title">
    <h1>📋 Lịch sử thanh toán</h1>
    <p><?= count($list ?? []) ?> giao dịch</p>
  </div>
  <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Hóa đơn</a>
</div>

<div class="card">
  <?php if(!empty($list)): ?>
  <table class="tbl tbl-r">
    <thead>
      <tr>
        <th>#</th>
        <th>Phòng</th>
        <th>Số tiền</th>
        <th>Phương thức</th>
        <th>Trạng thái</th>
        <th>Người thu</th>
        <th>Thời gian</th>
        <th>Ghi chú</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($list as $i => $ls): ?>
      <tr>
        <td class="mob-hide" style="color:var(--text3)"><?= $i+1 ?></td>
        <td data-label="Phòng">
          <div style="text-align:right;">
            <span class="td-name"><?= htmlspecialchars($ls['so_phong'] ?? '') ?></span>
            <?php if(!empty($ls['ten_khu'])): ?>
            <div style="font-size:10px;color:var(--text3);"><?= htmlspecialchars($ls['ten_khu']) ?></div>
            <?php endif; ?>
          </div>
        </td>
        <td data-label="Số tiền"><strong style="color:var(--green);font-size:14px;"><?= number_format($ls['so_tien']) ?>đ</strong></td>
        <td data-label="Phương thức">
          <?php $pt = $ptMap[$ls['phuong_thuc']] ?? ['💳','Khác']; ?>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;">
            <span><?= $pt[0] ?></span> <?= $pt[1] ?>
          </span>
        </td>
        <td data-label="Trạng thái">
          <?php $tt = $ttMap[$ls['trang_thai']] ?? ['p-blue','Không rõ']; ?>
          <span class="pill <?= $tt[0] ?>"><?= $tt[1] ?></span>
        </td>
        <td data-label="Người thu" style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($ls['nguoi_thu'] ?? '—') ?></td>
        <td data-label="Thời gian" style="font-size:12px;color:var(--text3);"><?= date('d/m/Y H:i', strtotime($ls['created_at'])) ?></td>
        <td data-label="Ghi chú" style="font-size:12px;color:var(--text3);">
          <?= htmlspecialchars($ls['ghi_chu'] ?? '—') ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:48px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px">📋</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);margin-bottom:4px">Chưa có lịch sử thanh toán</div>
    <div style="font-size:13px;">Lịch sử sẽ được ghi lại khi thu tiền hóa đơn</div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
