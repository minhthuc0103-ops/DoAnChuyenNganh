<?php
$title = 'Khai báo lưu trú của tôi';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>📋 Thông tin khai báo lưu trú</h1>
    <p>Hồ sơ đăng ký tạm trú / lưu trú của bạn tại cơ sở phòng trọ</p>
  </div>
  <a href="index.php?controller=user&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<?php if(empty($list)): ?>
<div class="card" style="padding:40px;text-align:center;">
  <div style="font-size:48px;margin-bottom:12px;">📑</div>
  <h3 style="margin-bottom:8px;">Chưa có thông tin khai báo lưu trú</h3>
  <p style="color:var(--text3);max-width:480px;margin:0 auto 16px auto;">
    Chủ trọ sẽ cập nhật thông tin khai báo tạm trú cho bạn và các thành viên cùng phòng lên Cổng Dịch vụ công hoặc Công an địa phương.
  </p>
  <a href="index.php?controller=user&action=profile" class="btn btn-primary">Kiểm tra thông tin CCCD của bạn</a>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;">
  <?php foreach($list as $r): ?>
  <div class="card" style="padding:20px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
      <div>
        <div style="font-size:18px;font-weight:700;color:var(--text);"><?= htmlspecialchars($r['ho_ten']) ?></div>
        <div style="font-size:13px;color:var(--text3);">
          Phòng <?= htmlspecialchars($r['so_phong']) ?> (<?= htmlspecialchars($r['ten_khu']??'') ?>)
        </div>
      </div>
      <div>
        <?php if($r['trang_thai'] === 'cho_khai_bao'): ?>
          <span class="badge" style="background:rgba(247,169,79,.15);color:var(--amber);">⏳ Chờ khai báo</span>
        <?php elseif($r['trang_thai'] === 'da_gui_dvc'): ?>
          <span class="badge" style="background:rgba(124,92,252,.15);color:var(--accent2);">🌐 Đã nộp DVC</span>
        <?php elseif($r['trang_thai'] === 'da_tiep_nhan'): ?>
          <span class="badge" style="background:rgba(34,201,147,.15);color:var(--green);">✅ Đã tiếp nhận</span>
        <?php else: ?>
          <span class="badge" style="background:rgba(156,163,175,.15);color:#9ca3af;">🚪 Kết thúc</span>
        <?php endif; ?>
      </div>
    </div>

    <div style="background:var(--bg3);border-radius:10px;padding:12px;font-size:13px;margin-bottom:16px;line-height:1.7;">
      <div>🪪 <strong>Số CCCD:</strong> <?= htmlspecialchars($r['cccd']) ?></div>
      <div>🎂 <strong>Ngày sinh:</strong> <?= $r['ngay_sinh'] ? date('d/m/Y', strtotime($r['ngay_sinh'])) : 'Chưa có' ?> (<?= $r['gioi_tinh']==='nu'?'Nữ':'Nam' ?>)</div>
      <div>📞 <strong>Điện thoại:</strong> <?= htmlspecialchars($r['sdt'] ?: 'Chưa có') ?></div>
      <div>📍 <strong>Thường trú:</strong> <?= htmlspecialchars($r['thuong_tru'] ?: 'Theo CCCD') ?></div>
      <div>🗓 <strong>Thời gian lưu trú:</strong> Từ <?= date('d/m/Y', strtotime($r['ngay_den'])) ?> <?= $r['ngay_di'] ? 'đến ' . date('d/m/Y', strtotime($r['ngay_di'])) : '(Dài hạn)' ?></div>
      <?php if(!empty($r['ma_dvc'])): ?>
      <div>🌐 <strong>Mã hồ sơ DVC:</strong> <code style="color:var(--accent);"><?= htmlspecialchars($r['ma_dvc']) ?></code></div>
      <?php endif; ?>
    </div>

    <div style="display:flex;justify-content:flex-end;">
      <a href="index.php?controller=luutru&action=mauCT01&id=<?= $r['id'] ?>" target="_blank" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
        📄 Xem & Tải Mẫu CT01 (PDF/In)
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

