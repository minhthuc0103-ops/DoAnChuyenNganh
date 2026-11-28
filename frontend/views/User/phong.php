<?php
$title = 'Danh sách phòng';
require_once 'frontend/views/Layouts/header.php';
$khu_id = isset($_GET['khu_id']) ? (int)$_GET['khu_id'] : null;
$filter = $_GET['filter'] ?? 'all';
$kw     = trim((string)($_GET['kw'] ?? ''));
$phongCuaToi = $phongCuaToi ?? [];
// Giữ từ khóa khi bấm các bộ lọc khu / trạng thái
$kwQs = $kw !== '' ? '&kw=' . urlencode($kw) : '';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Danh sách phòng</h1>
    <p>Tổng <?= count($phongs??[]) ?> phòng</p>
  </div>
  <a href="index.php?controller=user&action=khuPhong" class="btn btn-outline">🏘 Khu trọ</a>
</div>

<?php if (!empty($_GET['chan'])): ?>
<div class="msg-alert msg-error" style="margin-bottom:14px;">🔒 <?= htmlspecialchars((string)$_GET['chan']) ?></div>
<?php endif; ?>

<!-- TÌM PHÒNG -->
<form method="GET" action="index.php" style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
  <input type="hidden" name="controller" value="user">
  <input type="hidden" name="action" value="phong">
  <?php if ($khu_id): ?><input type="hidden" name="khu_id" value="<?= (int)$khu_id ?>"><?php endif; ?>
  <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
  <input type="text" name="kw" class="form-control" style="flex:1;min-width:200px;" placeholder="🔍 Tìm theo số phòng hoặc tên khu (VD: A101, Khu A)..." value="<?= htmlspecialchars($kw) ?>">
  <button type="submit" class="btn btn-primary">Tìm</button>
  <?php if ($kw !== ''): ?><a href="index.php?controller=user&action=phong<?= $khu_id ? '&khu_id=' . (int)$khu_id : '' ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline">Xóa tìm</a><?php endif; ?>
</form>
<div style="font-size:12px;color:var(--text3);margin-bottom:14px;">Bạn xem được chi tiết phòng <strong style="color:var(--green);">còn trống</strong> và phòng của mình. Phòng người khác đang thuê chỉ hiện thông tin chung.</div>

<!-- FILTER KHU -->
<div style="display:flex;gap:7px;margin-bottom:10px;flex-wrap:wrap;align-items:center;">
  <span style="font-size:12px;color:var(--text3);font-weight:600;">KHU:</span>
  <a href="index.php?controller=user&action=phong&filter=<?= $filter ?><?= $kwQs ?>"
     style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
            <?= !$khu_id ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;' : 'background:var(--card);color:var(--text2);border:1px solid var(--border);' ?>">
    Tất cả
  </a>
  <?php foreach($khus??[] as $k): ?>
  <a href="index.php?controller=user&action=phong&khu_id=<?= $k['id'] ?>&filter=<?= $filter ?><?= $kwQs ?>"
     style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
            <?= $khu_id===$k['id'] ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;' : 'background:var(--card);color:var(--text2);border:1px solid var(--border);' ?>">
    <?= htmlspecialchars($k['ten_khu']) ?>
    <span style="opacity:.65;margin-left:2px;font-size:11px;">(<?= $k['so_phong'] ?>)</span>
  </a>
  <?php endforeach; ?>
</div>

<!-- FILTER TRẠNG THÁI -->
<div style="display:flex;gap:7px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
  <span style="font-size:12px;color:var(--text3);font-weight:600;">TRẠNG THÁI:</span>
  <?php
  $tabs = ['all'=>'Tất cả','dang_thue'=>'Đang thuê','trong'=>'Còn trống','bao_tri'=>'Bảo trì'];
  foreach($tabs as $val=>$lbl): $a=($filter===$val); ?>
  <a href="index.php?controller=user&action=phong<?= $khu_id?"&khu_id=$khu_id":'' ?>&filter=<?= $val ?><?= $kwQs ?>"
     style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
            <?= $a ? 'background:var(--bg3);color:var(--text);border:1px solid var(--border2);' : 'background:var(--card);color:var(--text2);border:1px solid var(--border);' ?>">
    <?= $lbl ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php if(!empty($phongs)): ?>
  <table class="tbl">
    <thead>
      <tr>
        <th>#</th>
        <th>Khu</th>
        <th>Số phòng</th>
        <th>Giá thuê / tháng</th>
        <th>Diện tích</th>
        <th>Số người</th>
        <th>Trạng thái</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($phongs as $i=>$p):
      $cuaToi = in_array((int)$p['id'], $phongCuaToi, true);
      $xemDuoc = $p['trang_thai'] === 'trong' || $cuaToi;   // cùng quy tắc với UserController::chiTietPhong
    ?>
    <?php if ($xemDuoc): ?>
    <tr onclick="location.href='index.php?controller=user&action=chiTietPhong&id=<?= $p['id'] ?>'"
        style="cursor:pointer;" title="Xem chi tiết phòng" onmouseover="this.style.background='rgba(79,142,247,.06)'" onmouseout="this.style.background=''">
    <?php else: ?>
    <tr style="cursor:not-allowed;opacity:.75;" title="Phòng này không xem được chi tiết">
    <?php endif; ?>
      <td style="color:var(--text3)"><?= $i+1 ?></td>
      <td>
        <?php if(!empty($p['ten_khu'])): ?>
        <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                     padding:3px 9px;border-radius:6px;background:rgba(79,142,247,.1);
                     color:var(--accent);border:1px solid rgba(79,142,247,.2);">
          🏘 <?= htmlspecialchars($p['ten_khu']) ?>
        </span>
        <?php else: ?>
        <span style="color:var(--text3);font-size:12px;">—</span>
        <?php endif; ?>
      </td>
      <td><span class="td-name"><?= htmlspecialchars($p['so_phong']) ?></span></td>
      <td><strong style="color:var(--text)"><?= number_format($p['gia']) ?>đ</strong></td>
      <td><?= $p['dien_tich'] ? $p['dien_tich'].' m²' : '—' ?></td>
      <td>👥 <?= $p['so_nguoi'] ?? 1 ?> người</td>
      <td>
        <?php if($cuaToi): ?><span class="pill p-blue">🏠 Phòng của bạn</span>
        <?php elseif($p['trang_thai']==='dang_thue'): ?><span class="pill p-blue">🔒 Đang thuê</span>
        <?php elseif($p['trang_thai']==='trong'):  ?><span class="pill p-green">Còn trống · Xem chi tiết ›</span>
        <?php else: ?>                              <span class="pill p-amber">🔒 Bảo trì</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:48px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px">🏠</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);"><?= $kw !== '' ? 'Không tìm thấy phòng nào khớp "' . htmlspecialchars($kw) . '"' : 'Không có phòng nào' ?></div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>