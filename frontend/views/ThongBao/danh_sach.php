<?php
// Danh sách thông báo — DÙNG CHUNG cho quản lý và người thuê.
// Controller đã lọc dữ liệu theo vai trò (người thuê chỉ nhận phần được phép xem); view chỉ rẽ nhánh để hiện nút.
$title = $title ?? 'Thông báo';
require_once 'frontend/views/Layouts/header.php';

$laAdmin = !empty($laAdmin);
$thongBaoList = $thongBaoList ?? [];
$soChuaDoc = (int)($soChuaDoc ?? 0);
$loaiLoc = $loaiLoc ?? null;

$loaiMap = [
  'chung'      => ['📢', 'Chung',      'rgba(79,142,247,.12)',  'var(--accent)'],
  'khan_cap'   => ['🚨', 'Khẩn cấp',   'rgba(247,92,92,.12)',   'var(--red)'],
  'bao_tri'    => ['🔧', 'Bảo trì',    'rgba(247,169,79,.12)',  'var(--amber)'],
  'tien_phong' => ['💰', 'Tiền phòng', 'rgba(34,201,147,.12)',  'var(--green)'],
  'khac'       => ['📋', 'Khác',       'rgba(124,92,252,.12)',  'var(--accent2)'],
];
$gioiHan = 100;
?>

<div class="page-header">
  <div class="page-title">
    <h1>🔔 Thông báo</h1>
    <p>
      <?php if ($laAdmin): ?>
        <?= count($thongBaoList) ?> thông báo<?= $loaiLoc ? ' thuộc loại ' . htmlspecialchars($loaiMap[$loaiLoc][1]) : '' ?> · gửi tới người thuê
      <?php else: ?>
        <?= count($thongBaoList) ?> thông báo
        <?php if ($soChuaDoc > 0): ?>· <strong style="color:var(--amber);"><?= $soChuaDoc ?> chưa đọc</strong><?php endif; ?>
      <?php endif; ?>
    </p>
  </div>
  <div class="header-actions">
    <?php if ($laAdmin): ?>
    <a href="index.php?controller=thongbao&action=create" class="btn btn-primary">＋ Gửi thông báo</a>
    <?php elseif ($soChuaDoc > 0): ?>
    <form method="POST" action="index.php?controller=thongbao&action=docTatCa" style="margin:0;">
      <?= Csrf::field() ?>
      <button type="submit" class="btn btn-outline">✓ Đánh dấu đã đọc</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- Lọc theo loại -->
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
  <a href="index.php?controller=thongbao&action=danhSach" class="btn btn-sm <?= $loaiLoc === null ? 'btn-primary' : 'btn-outline' ?>">Tất cả</a>
  <?php foreach ($loaiMap as $khoa => $l): ?>
  <a href="index.php?controller=thongbao&action=danhSach&loai=<?= $khoa ?>" class="btn btn-sm <?= $loaiLoc === $khoa ? 'btn-primary' : 'btn-outline' ?>"><?= $l[0] ?> <?= $l[1] ?></a>
  <?php endforeach; ?>
</div>

<?php if (empty($thongBaoList)): ?>
<div class="card" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:10px;">📭</div>
  <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px;">Chưa có thông báo nào</div>
  <div style="font-size:13px;color:var(--text2);">
    <?= $laAdmin ? 'Bấm "Gửi thông báo" để gửi thông báo đầu tiên cho người thuê.' : 'Khi có thông báo mới, bạn sẽ thấy ở đây và ở biểu tượng chuông.' ?>
  </div>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:10px;">
  <?php foreach ($thongBaoList as $tb):
    $l = $loaiMap[$tb['loai']] ?? $loaiMap['khac'];
    $chuaDoc = !$laAdmin && empty($tb['da_doc']);
    $trich = htmlspecialchars(mb_strimwidth(trim(preg_replace('/\s+/u', ' ', (string)$tb['noi_dung'])), 0, 170, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8');
  ?>
  <div class="card" style="margin-bottom:0;<?= $chuaDoc ? 'border-color:rgba(79,142,247,.4);background:rgba(79,142,247,.04);' : '' ?>">
    <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 18px;">
      <a href="index.php?controller=thongbao&action=xem&id=<?= (int)$tb['id'] ?>" style="display:flex;gap:14px;flex:1;min-width:0;text-decoration:none;color:inherit;">
        <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;background:<?= $l[2] ?>;"><?= $l[0] ?></div>
        <div style="min-width:0;flex:1;">
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:7px;margin-bottom:4px;">
            <span style="font-size:14px;font-weight:800;color:var(--text);"><?= htmlspecialchars((string)$tb['tieu_de']) ?></span>
            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:<?= $l[2] ?>;color:<?= $l[3] ?>;"><?= $l[1] ?></span>
            <?php if (!empty($tb['ghim'])): ?><span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;background:rgba(247,169,79,.15);color:var(--amber);">📌 Ghim</span><?php endif; ?>
            <?php if ($chuaDoc): ?><span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;background:rgba(247,92,92,.15);color:var(--red);">Mới</span><?php endif; ?>
          </div>
          <div style="font-size:13px;color:var(--text2);line-height:1.6;"><?= $trich ?></div>
          <div style="font-size:11px;color:var(--text3);margin-top:6px;">
            👤 <?= htmlspecialchars((string)($tb['nguoi_gui'] ?: 'Quản lý')) ?> · 🕐 <?= date('d/m/Y H:i', strtotime($tb['created_at'])) ?>
          </div>
        </div>
      </a>
      <?php if ($laAdmin): ?>
      <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end;">
        <form method="POST" action="index.php?controller=thongbao&action=ghim" style="margin:0;">
          <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int)$tb['id'] ?>">
          <button type="submit" class="btn btn-outline btn-xs" title="<?= !empty($tb['ghim']) ? 'Bỏ ghim' : 'Ghim lên đầu' ?>">📌</button>
        </form>
        <a href="index.php?controller=thongbao&action=edit&id=<?= (int)$tb['id'] ?>" class="btn btn-outline btn-xs">✏ Sửa</a>
        <form method="POST" action="index.php?controller=thongbao&action=delete" style="margin:0;" onsubmit="return confirm('Xóa thông báo này?')">
          <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int)$tb['id'] ?>">
          <button type="submit" class="btn btn-danger btn-xs">🗑 Xóa</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php if (count($thongBaoList) >= $gioiHan): ?>
<div style="text-align:center;font-size:12px;color:var(--text2);margin-top:14px;">Hiển thị <?= $gioiHan ?> thông báo mới nhất.</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
