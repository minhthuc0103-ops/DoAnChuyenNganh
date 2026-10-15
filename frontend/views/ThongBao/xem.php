<?php
$title = $tb['tieu_de'] ?? 'Thông báo';
require_once 'frontend/views/Layouts/header.php';

$loaiMap = [
  'chung'     => ['📢', 'Chung', 'rgba(79,142,247,.12)', 'var(--accent)'],
  'khan_cap'  => ['🚨', 'Khẩn cấp', 'rgba(247,92,92,.12)', 'var(--red)'],
  'bao_tri'   => ['🔧', 'Bảo trì', 'rgba(247,169,79,.12)', 'var(--amber)'],
  'tien_phong'=> ['💰', 'Tiền phòng', 'rgba(34,201,147,.12)', 'var(--green)'],
  'khac'      => ['📋', 'Khác', 'rgba(79,142,247,.12)', 'var(--accent)'],
];
$loai = $loaiMap[$tb['loai']] ?? $loaiMap['khac'];
$isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']);
?>

<div class="page-header">
  <div class="page-title">
    <h1>Chi tiết thông báo</h1>
  </div>
  <div class="header-actions">
    <?php if($isAdmin): ?>
    <a href="index.php?controller=thongbao&action=edit&id=<?= $tb['id'] ?>" class="btn btn-outline">✏ Sửa</a>
    <form method="POST" action="index.php?controller=thongbao&action=delete" style="display:inline;" onsubmit="return confirm('Xóa?')">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $tb['id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs" style="cursor:pointer;">🗑 Xóa</button>
          </form>
    <?php endif; ?>
    <a href="index.php?controller=thongbao&action=index" class="btn btn-outline">← Quay lại</a>
  </div>
</div>

<div style="max-width:700px;">
  <div class="card">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:16px;">
      <div style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;background:<?= $loai[2] ?>;">
        <?= $loai[0] ?>
      </div>
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
          <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;background:<?= $loai[2] ?>;color:<?= $loai[3] ?>;"><?= $loai[1] ?></span>
          <?php if($tb['ghim']): ?>
          <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;background:rgba(247,169,79,.15);color:var(--amber);">📌 Ghim</span>
          <?php endif; ?>
        </div>
        <h2 style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:8px;"><?= htmlspecialchars($tb['tieu_de']) ?></h2>
        <div style="font-size:12px;color:var(--text3);">
          👤 <?= htmlspecialchars($tb['nguoi_gui'] ?: 'Admin') ?> · 🕐 <?= date('d/m/Y \l\ú\c H:i', strtotime($tb['created_at'])) ?>
        </div>
      </div>
    </div>
    <div style="padding:24px;line-height:1.8;font-size:14px;color:var(--text);white-space:pre-wrap;"><?= htmlspecialchars($tb['noi_dung']) ?></div>
  </div>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
