<?php
// Biến từ LuuTruController::detail(): $item (bản ghi lưu trú), $history (lịch sử theo CCCD)
$title = 'Chi tiết lưu trú - ' . $item['ho_ten'];
require_once 'frontend/views/Layouts/header.php';

$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$trangThai = [
  'cho_khai_bao' => ['⏳ Chờ khai báo',        'rgba(247,169,79,.15)',  'var(--amber)'],
  'da_gui_dvc'   => ['🌐 Đã gửi Dịch vụ công', 'rgba(124,92,252,.15)',  'var(--accent2)'],
  'da_tiep_nhan' => ['✅ Đã tiếp nhận',         'rgba(34,201,147,.15)',  'var(--green)'],
  'ket_thuc'     => ['🚪 Đã kết thúc',          'rgba(156,163,175,.15)', '#9ca3af'],
];
$tt = $trangThai[$item['trang_thai']] ?? $trangThai['cho_khai_bao'];
$dong = function (string $nhan, $giaTri) {
  $v = trim((string)$giaTri);
  echo '<div style="display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);font-size:14px;">'
     . '<div style="width:150px;flex-shrink:0;color:var(--text3);">' . htmlspecialchars($nhan) . '</div>'
     . '<div style="color:var(--text);font-weight:600;word-break:break-word;">' . ($v !== '' ? htmlspecialchars($v) : '<span style="color:var(--text3);font-weight:400;">—</span>') . '</div>'
     . '</div>';
};
?>

<div class="page-header">
  <div class="page-title">
    <h1>👤 Thông tin cá nhân lưu trú</h1>
    <p>Hồ sơ đăng ký lưu trú của <?= htmlspecialchars($item['ho_ten']) ?></p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="index.php?controller=luutru&action=index" class="btn btn-outline">← Quay lại danh sách</a>
    <a href="index.php?controller=luutru&action=edit&id=<?= (int)$item['id'] ?>" class="btn btn-outline">✏️ Sửa</a>
    <a href="index.php?controller=luutru&action=mauCT01&id=<?= (int)$item['id'] ?>" target="_blank" class="btn btn-primary">📄 Mẫu CT01</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:18px;align-items:start;">

  <!-- THÔNG TIN CÁ NHÂN -->
  <div class="card" style="padding:22px;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
      <?php if (!empty($item['avatar'])): ?>
        <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="" style="width:64px;height:64px;border-radius:16px;object-fit:cover;" onerror="this.style.display='none'">
      <?php else: ?>
        <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:26px;">
          <?= htmlspecialchars(mb_strtoupper(mb_substr($item['ho_ten'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
        </div>
      <?php endif; ?>
      <div>
        <div style="font-size:20px;font-weight:800;color:var(--text);"><?= htmlspecialchars($item['ho_ten']) ?></div>
        <span class="badge" style="background:<?= $tt[1] ?>;color:<?= $tt[2] ?>;"><?= $tt[0] ?></span>
      </div>
    </div>

    <?php
      $dong('Giới tính', $item['gioi_tinh'] === 'nu' ? 'Nữ' : ($item['gioi_tinh'] === 'khac' ? 'Khác' : 'Nam'));
      $dong('Ngày sinh', $fmt($item['ngay_sinh']));
      $dong('Số CCCD', $item['cccd']);
      $dong('Ngày cấp', $fmt($item['ngay_cap_cccd']));
      $dong('Nơi cấp', $item['noi_cap_cccd']);
      $dong('Số điện thoại', $item['sdt']);
      $dong('Email', $item['email'] ?? '');
      $dong('Nghề nghiệp', $item['nghe_nghiep']);
      $dong('Thường trú', $item['thuong_tru']);
    ?>
  </div>

  <!-- THÔNG TIN LƯU TRÚ -->
  <div class="card" style="padding:22px;">
    <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">🏠 Nơi lưu trú</div>
    <?php
      $dong('Phòng', 'Phòng ' . $item['so_phong']);
      $dong('Khu trọ', $item['ten_khu'] ?? '');
      $dong('Địa chỉ khu trọ', $item['dia_chi_khu'] ?? '');
      $dong('Ngày đến', $fmt($item['ngay_den']));
      $dong('Ngày đi', $item['ngay_di'] ? $fmt($item['ngay_di']) : 'Không xác định');
      $dong('Lý do lưu trú', $item['ly_do']);
      $dong('Mã hồ sơ DVC', $item['ma_dvc'] ?? '');
      $dong('Ghi chú', $item['ghi_chu'] ?? '');
    ?>
  </div>
</div>

<?php if (!empty($item['cccd_truoc']) || !empty($item['cccd_sau'])): ?>
<div class="card" style="padding:22px;margin-top:18px;">
  <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">🪪 Ảnh CCCD / CMND</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
    <?php foreach (['cccd_truoc' => 'Mặt trước', 'cccd_sau' => 'Mặt sau'] as $f => $nhan): ?>
      <div>
        <div style="font-size:11px;color:var(--text3);margin-bottom:6px;font-weight:600;"><?= $nhan ?></div>
        <?php if (!empty($item[$f])): ?>
          <img src="<?= htmlspecialchars($item[$f]) ?>" alt="<?= $nhan ?>" style="width:100%;max-height:220px;object-fit:contain;border-radius:10px;border:1px solid var(--border);background:var(--bg3);" onerror="this.replaceWith(document.createTextNode('Không tải được ảnh'))">
        <?php else: ?>
          <div style="padding:30px;text-align:center;color:var(--text3);border:1px dashed var(--border);border-radius:10px;font-size:13px;">Chưa có ảnh</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- LỊCH SỬ LƯU TRÚ THEO CCCD -->
<div class="card" style="margin-top:18px;overflow:hidden;">
  <div style="padding:16px 22px;font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;">🕘 Lịch sử lưu trú của người này (<?= count($history) ?>)</div>
  <div style="overflow-x:auto;">
    <table class="table" style="width:100%;margin-bottom:0;">
      <thead><tr><th>Phòng / Khu</th><th>Từ ngày</th><th>Đến ngày</th><th>Trạng thái</th></tr></thead>
      <tbody>
      <?php foreach ($history as $h): $t = $trangThai[$h['trang_thai']] ?? $trangThai['cho_khai_bao']; ?>
        <tr<?= (int)$h['id'] === (int)$item['id'] ? ' style="background:rgba(79,142,247,.07);"' : '' ?>>
          <td>
            <a href="index.php?controller=luutru&action=detail&id=<?= (int)$h['id'] ?>" style="color:var(--accent);font-weight:700;text-decoration:none;">Phòng <?= htmlspecialchars($h['so_phong']) ?></a>
            <span style="color:var(--text3);font-size:12px;"><?= htmlspecialchars($h['ten_khu'] ?? '') ?></span>
          </td>
          <td><?= $fmt($h['ngay_den']) ?></td>
          <td><?= $h['ngay_di'] ? $fmt($h['ngay_di']) : 'Không xác định' ?></td>
          <td><span class="badge" style="background:<?= $t[1] ?>;color:<?= $t[2] ?>;"><?= $t[0] ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
