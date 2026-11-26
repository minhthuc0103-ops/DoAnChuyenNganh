<?php
// Biến từ HoaDonController::lichSu(): $list, $laAdmin, $loc, $khus, $phongs, $tongThu
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
$laAdmin = $laAdmin ?? false;
?>

<div class="page-header">
  <div class="page-title">
    <h1>📋 Lịch sử thanh toán</h1>
    <p>
      <?= count($list ?? []) ?> giao dịch
      <?php if ($laAdmin): ?>· Đã thu thành công: <strong style="color:var(--green);"><?= number_format($tongThu ?? 0) ?>đ</strong>
      <?php else: ?>của phòng bạn<?php endif; ?>
    </p>
  </div>
  <a href="index.php?controller=<?= $laAdmin ? 'hoadon&action=index' : 'user&action=index' ?>" class="btn btn-outline">← <?= $laAdmin ? 'Hóa đơn' : 'Phòng của tôi' ?></a>
</div>

<?php if ($laAdmin): ?>
<!-- Bộ lọc: xem lịch sử của từng phòng -->
<div class="card" style="padding:16px;margin-bottom:18px;">
  <form method="GET" action="index.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <input type="hidden" name="controller" value="hoadon">
    <input type="hidden" name="action" value="lichSu">

    <select name="khu_id" class="form-control" style="min-width:150px;width:auto;" onchange="this.form.phong_id.value='';this.form.submit()">
      <option value="">— Tất cả khu —</option>
      <?php foreach ($khus as $k): ?>
      <option value="<?= (int)$k['id'] ?>" <?= (int)$loc['khu_id'] === (int)$k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['ten_khu']) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="phong_id" class="form-control" style="min-width:150px;width:auto;" onchange="this.form.submit()">
      <option value="">— Tất cả phòng —</option>
      <?php foreach ($phongs as $p): ?>
      <option value="<?= (int)$p['id'] ?>" <?= (int)$loc['phong_id'] === (int)$p['id'] ? 'selected' : '' ?>>Phòng <?= htmlspecialchars($p['so_phong']) ?> (<?= htmlspecialchars($p['ten_khu'] ?? '') ?>)</option>
      <?php endforeach; ?>
    </select>

    <select name="trang_thai" class="form-control" style="min-width:150px;width:auto;" onchange="this.form.submit()">
      <option value="">— Mọi trạng thái —</option>
      <?php foreach ($ttMap as $val => $t): ?>
      <option value="<?= $val ?>" <?= $loc['trang_thai'] === $val ? 'selected' : '' ?>><?= $t[1] ?></option>
      <?php endforeach; ?>
    </select>

    <select name="thang" class="form-control" style="width:auto;">
      <option value="">Tháng</option>
      <?php for ($m = 1; $m <= 12; $m++): ?><option value="<?= $m ?>" <?= (int)$loc['thang'] === $m ? 'selected' : '' ?>><?= $m ?></option><?php endfor; ?>
    </select>
    <select name="nam" class="form-control" style="width:auto;">
      <option value="">Năm</option>
      <?php for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--): ?><option value="<?= $y ?>" <?= (int)$loc['nam'] === $y ? 'selected' : '' ?>><?= $y ?></option><?php endfor; ?>
    </select>

    <button type="submit" class="btn btn-primary" style="padding:8px 16px;">🔍 Lọc</button>
    <a href="index.php?controller=hoadon&action=lichSu" class="btn btn-outline" style="padding:8px 14px;">Xóa lọc</a>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <?php if(!empty($list)): ?>
  <table class="tbl tbl-r">
    <thead>
      <tr>
        <th>#</th>
        <th>Phòng</th>
        <th>Kỳ hóa đơn</th>
        <th>Số tiền</th>
        <th>Phương thức</th>
        <th>Trạng thái</th>
        <th>Người thu</th>
        <th>Thời gian</th>
        <th>Ảnh giao dịch</th>
        <th></th>
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
        <td data-label="Kỳ hóa đơn" style="font-size:12px;color:var(--text2);"><?= !empty($ls['thang']) ? 'T' . (int)$ls['thang'] . '/' . (int)$ls['nam'] : '—' ?></td>
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
        <td data-label="Ảnh giao dịch" style="font-size:12px;">
          <?php if(!empty($ls['anh_chung_tu'])): ?>
          <a href="<?= htmlspecialchars($ls['anh_chung_tu']) ?>" target="_blank" rel="noopener" style="color:var(--accent);">📸 Xem ảnh</a>
          <?php else: ?><span style="color:var(--text3);">—</span><?php endif; ?>
        </td>
        <td data-label="">
          <a href="index.php?controller=hoadon&action=chiTietGiaoDich&id=<?= (int)$ls['id'] ?>" class="btn btn-outline btn-xs">👁 Chi tiết</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:48px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px">📋</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);margin-bottom:4px">Chưa có lịch sử thanh toán</div>
    <div style="font-size:13px;">Giao dịch sẽ được ghi lại khi quản lý xác nhận đã thu tiền hóa đơn</div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
