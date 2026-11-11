<?php
$title = 'Chi tiết biên bản ' . htmlspecialchars($bb['ma_bien_ban']);
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>📦 Biên bản <?= $bb['loai'] === 'tra_phong' ? 'bàn giao trả phòng' : 'bàn giao nhận phòng' ?></h1>
    <p>Mã biên bản: <strong style="color:var(--accent);"><?= htmlspecialchars($bb['ma_bien_ban']) ?></strong> · Lập ngày <?= date('d/m/Y', strtotime($bb['ngay_lap'])) ?></p>
  </div>
  <div class="header-actions">
    <a href="index.php?controller=bangiao&action=inBienBan&id=<?=$bb['id']?>" target="_blank" class="btn btn-primary">
      🖨 In biên bản A4
    </a>
    <a href="index.php?controller=bangiao&action=index" class="btn btn-outline">
      ← Danh sách
    </a>
  </div>
</div>

<div class="card" style="max-width:850px;margin:0 auto 20px auto;">
  <div class="card-header">
    <div class="card-title">
      🏠 Phòng <?= htmlspecialchars($bb['so_phong']) ?> - <?= htmlspecialchars($bb['ten_khu'] ?? '') ?>
    </div>
    <span class="pill <?= $bb['loai'] === 'tra_phong' ? 'p-orange' : 'p-green' ?>">
      <?= $bb['loai'] === 'tra_phong' ? '📤 Bàn giao trả phòng' : '📥 Bàn giao nhận phòng' ?>
    </span>
  </div>
  <div class="card-body">

    <!-- THÔNG TIN BÊN GIAO & BÊN NHẬN -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;background:var(--bg3);padding:16px;border-radius:10px;">
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:6px;">Đại diện quản lý / Cho thuê</div>
        <div style="font-size:14px;font-weight:700;color:var(--text);"><?= htmlspecialchars($bb['nguoi_lap_ten'] ?? 'Quản lý phòng trọ') ?></div>
        <div style="font-size:12px;color:var(--text3);margin-top:2px;">Địa chỉ: <?= htmlspecialchars($bb['dia_chi'] ?? 'Khu trọ') ?></div>
      </div>
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:6px;">Bên thuê / Nhận bàn giao</div>
        <div style="font-size:14px;font-weight:700;color:var(--text);"><?= htmlspecialchars($bb['ten_nguoi_thue']) ?></div>
        <div style="font-size:12px;color:var(--text2);margin-top:2px;">Điện thoại: <?= htmlspecialchars($bb['sdt']) ?> · CCCD: <?= htmlspecialchars($bb['cccd']) ?></div>
      </div>
    </div>

    <!-- CHỈ SỐ ĐỒNG HỒ -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
      <div style="border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;align-items:center;gap:12px;">
        <div style="font-size:28px;">⚡</div>
        <div>
          <div style="font-size:11px;color:var(--text3);font-weight:600;">CHỈ SỐ ĐIỆN BÀN GIAO</div>
          <div style="font-size:20px;font-weight:800;color:var(--text);"><?= number_format($bb['chi_so_dien']) ?> <span style="font-size:13px;font-weight:normal;color:var(--text3);">kWh</span></div>
        </div>
      </div>
      <div style="border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;align-items:center;gap:12px;">
        <div style="font-size:28px;">💧</div>
        <div>
          <div style="font-size:11px;color:var(--text3);font-weight:600;">CHỈ SỐ NƯỚC BÀN GIAO</div>
          <div style="font-size:20px;font-weight:800;color:var(--text);"><?= number_format($bb['chi_so_nuoc']) ?> <span style="font-size:13px;font-weight:normal;color:var(--text3);">m³</span></div>
        </div>
      </div>
    </div>

    <!-- DANH SÁCH THIẾT BỊ BÀN GIAO -->
    <div style="margin-bottom:20px;">
      <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:10px;">
        🛋 Danh mục trang thiết bị bàn giao (<?= count($danhSachTaiSan) ?> mục)
      </div>
      <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:var(--bg3);border-bottom:1.5px solid var(--border);text-align:left;">
            <th style="padding:10px 12px;width:10%;">STT</th>
            <th style="padding:10px 12px;width:45%;">Tên thiết bị / Tài sản</th>
            <th style="padding:10px 12px;width:15%;">Số lượng</th>
            <th style="padding:10px 12px;width:30%;">Tình trạng hiện tại</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($danhSachTaiSan as $idx => $ts): ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:10px 12px;color:var(--text3);"><?= $idx + 1 ?></td>
            <td style="padding:10px 12px;font-weight:600;color:var(--text);"><?= htmlspecialchars($ts['ten']) ?></td>
            <td style="padding:10px 12px;"><?= $ts['so_luong'] ?></td>
            <td style="padding:10px 12px;color:var(--green);font-weight:500;">✓ <?= htmlspecialchars($ts['tinh_trang']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ẢNH BÀN GIAO -->
    <?php if (!empty($bb['anh_ban_giao']) && file_exists($bb['anh_ban_giao'])): ?>
    <div style="margin-bottom:20px;">
      <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:10px;">📷 Ảnh hiện trạng bàn giao phòng</div>
      <img src="<?= htmlspecialchars($bb['anh_ban_giao']) ?>"
           alt="Ảnh bàn giao"
           style="max-width:100%;max-height:360px;border-radius:10px;border:1px solid var(--border);object-fit:cover;"/>
    </div>
    <?php endif; ?>

    <!-- GHI CHÚ -->
    <?php if (!empty($bb['ghi_chu'])): ?>
    <div style="background:rgba(79,142,247,.08);border:1px solid rgba(79,142,247,.2);border-radius:10px;padding:12px 16px;margin-bottom:20px;">
      <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:4px;">Ghi chú bàn giao</div>
      <div style="font-size:13px;color:var(--text2);"><?= nl2br(htmlspecialchars($bb['ghi_chu'])) ?></div>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
