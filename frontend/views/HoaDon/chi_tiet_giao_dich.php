<?php
// Biến từ HoaDonController::chiTietGiaoDich(): $gd (giao dịch + phòng + hóa đơn), $laAdmin
$title = 'Chi tiết giao dịch #' . (int)$gd['id'];
require_once 'frontend/views/Layouts/header.php';

$ptMap = ['tien_mat' => '💵 Tiền mặt', 'chuyen_khoan' => '🏦 Chuyển khoản', 'momo' => '📱 MoMo',
          'vnpay' => '🔵 VNPay', 'zalopay' => '📲 ZaloPay', 'khac' => '💳 Khác'];
$ttMap = ['thanh_cong' => ['p-green', '✓ Thành công'], 'that_bai' => ['p-red', '✗ Thất bại'], 'dang_xu_ly' => ['p-amber', '⏳ Đang xử lý']];
$tt = $ttMap[$gd['trang_thai']] ?? ['p-blue', 'Không rõ'];
$kyHd = !empty($gd['thang']) ? 'Tháng ' . (int)$gd['thang'] . '/' . (int)$gd['nam'] : '—';
$laAdmin = $laAdmin ?? false;

$dong = function (string $nhan, $giaTri, bool $html = false) {
  $v = trim((string)$giaTri);
  echo '<div style="display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);font-size:14px;">'
     . '<div style="width:160px;flex-shrink:0;color:var(--text3);">' . htmlspecialchars($nhan) . '</div>'
     . '<div style="color:var(--text);font-weight:600;word-break:break-word;">'
     . ($v !== '' ? ($html ? $giaTri : htmlspecialchars($v)) : '<span style="color:var(--text3);font-weight:400;">—</span>')
     . '</div></div>';
};
// Ghi chú lưu nhiều dòng (yêu cầu của người thuê, xác nhận / từ chối của quản lý): hiển thị từng dòng
$ghiChu = trim((string)($gd['ghi_chu'] ?? ''));
?>

<div class="page-header">
  <div class="page-title">
    <h1>🧾 Chi tiết giao dịch #<?= (int)$gd['id'] ?></h1>
    <p>Phòng <?= htmlspecialchars($gd['so_phong']) ?><?= !empty($gd['ten_khu']) ? ' · ' . htmlspecialchars($gd['ten_khu']) : '' ?> · <?= $kyHd ?></p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="index.php?controller=hoadon&action=lichSu<?= $laAdmin ? '&phong_id=' . (int)$gd['phong_id'] : '' ?>" class="btn btn-outline">← Lịch sử<?= $laAdmin ? ' phòng này' : '' ?></a>
    <a href="index.php?controller=hoadon&action=chiTiet&id=<?= (int)$gd['hoa_don_id'] ?>" class="btn btn-primary">📄 Xem hóa đơn</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:18px;align-items:start;">

  <div class="card" style="padding:22px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
      <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;">Giao dịch</div>
      <span class="pill <?= $tt[0] ?>"><?= $tt[1] ?></span>
    </div>
    <?php
      $dong('Số tiền', number_format((float)$gd['so_tien']) . 'đ');
      $dong('Phương thức', $ptMap[$gd['phuong_thuc']] ?? 'Khác');
      $dong('Người thanh toán', $gd['nguoi_tra'] ?? '');
      $dong('Gửi yêu cầu lúc', date('d/m/Y H:i', strtotime($gd['created_at'])));
      if ($gd['trang_thai'] === 'thanh_cong') {
          $dong('Người xác nhận thu', $gd['nguoi_thu'] ?? '');
          $dong('Xác nhận lúc', !empty($gd['ngay_thanh_toan']) ? date('d/m/Y H:i', strtotime($gd['ngay_thanh_toan'])) : '');
      } elseif ($gd['trang_thai'] === 'that_bai') {
          $dong('Người xử lý', $gd['nguoi_thu'] ?? '');
      } else {
          $dong('Trạng thái xử lý', 'Đang chờ quản lý xác nhận');
      }
    ?>
    <?php if ($ghiChu !== ''): ?>
    <div style="margin-top:12px;font-size:12px;color:var(--text3);line-height:1.8;white-space:pre-line;"><?= htmlspecialchars($ghiChu) ?></div>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:22px;">
    <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">Hóa đơn liên quan</div>
    <?php
      $dong('Kỳ hóa đơn', $kyHd);
      $dong('Tiền phòng', isset($gd['tien_phong']) ? number_format((float)$gd['tien_phong']) . 'đ' : '');
      $dong('Tiền điện', isset($gd['tien_dien']) ? number_format((float)$gd['tien_dien']) . 'đ' : '');
      $dong('Tiền nước', isset($gd['tien_nuoc']) ? number_format((float)$gd['tien_nuoc']) . 'đ' : '');
      $dong('Phí dịch vụ', isset($gd['phi_dich_vu']) ? number_format((float)$gd['phi_dich_vu']) . 'đ' : '');
      $dong('Phí xe', isset($gd['phi_xe']) ? number_format((float)$gd['phi_xe']) . 'đ' : '');
      $dong('Tổng hóa đơn', isset($gd['tong_tien_hd']) ? number_format((float)$gd['tong_tien_hd']) . 'đ' : '');
      $dong('Tình trạng hóa đơn', ($gd['trang_thai_hd'] ?? '') === 'da_tt' ? 'Đã thanh toán' : 'Chưa thanh toán');
    ?>
  </div>
</div>

<?php if (!empty($gd['anh_chung_tu'])): ?>
<div class="card" style="padding:22px;margin-top:18px;">
  <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">📸 Ảnh chụp giao dịch (bấm để xem rõ)</div>
  <a href="<?= htmlspecialchars($gd['anh_chung_tu']) ?>" target="_blank" rel="noopener">
    <img src="<?= htmlspecialchars($gd['anh_chung_tu']) ?>" alt="Ảnh giao dịch" style="max-width:100%;max-height:480px;border-radius:12px;border:1px solid var(--border);background:var(--bg3);" onerror="this.replaceWith(document.createTextNode('Không tải được ảnh'))">
  </a>
</div>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
