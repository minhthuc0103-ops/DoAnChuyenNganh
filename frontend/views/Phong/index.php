<?php
$title  = 'Quản lý phòng trọ';
require_once 'frontend/views/Layouts/header.php';
$khu_id = isset($_GET['khu_id']) ? (int)$_GET['khu_id'] : null;
?>

<div class="page-header">
  <div class="page-title">
    <h1>Quản lý phòng trọ</h1>
    <p>Tổng <?= count($phongs??[]) ?> phòng trong hệ thống</p>
  </div>
  <div class="header-actions">
    <a href="index.php?controller=khutro&action=index" class="btn btn-outline">🏘 Khu trọ</a>
    <a href="index.php?controller=phong&action=create<?= $khu_id ? "&khu_id=$khu_id" : '' ?>" class="btn btn-primary">＋ Thêm phòng mới</a>
  </div>
</div>

<!-- FILTER KHU + TRẠNG THÁI -->
<div style="display:flex;gap:7px;margin-bottom:10px;flex-wrap:wrap;align-items:center;">
  <span style="font-size:12px;color:var(--text3);font-weight:600;">KHU:</span>
  <a href="index.php?controller=phong&action=index"
     style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
            <?= !$khu_id ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;' : 'background:var(--card);color:var(--text2);border:1px solid var(--border);' ?>">
    Tất cả
  </a>
  <?php foreach($khus??[] as $k): ?>
  <a href="index.php?controller=phong&action=index&khu_id=<?= $k['id'] ?>"
     style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
            <?= $khu_id===$k['id'] ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;' : 'background:var(--card);color:var(--text2);border:1px solid var(--border);' ?>">
    <?= htmlspecialchars($k['ten_khu']) ?>
    <span style="opacity:.65;margin-left:2px;font-size:11px;">(<?= $k['so_phong'] ?>)</span>
  </a>
  <?php endforeach; ?>
</div>

<div style="display:flex;gap:7px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
  <span style="font-size:12px;color:var(--text3);font-weight:600;">TRẠNG THÁI:</span>
  <?php
  $filter = $_GET['filter'] ?? 'all';
  $tabs   = ['all'=>'Tất cả','dang_thue'=>'Đang thuê','trong'=>'Còn trống','bao_tri'=>'Bảo trì'];
  foreach($tabs as $val=>$lbl): $a=($filter===$val); ?>
  <a href="index.php?controller=phong&action=index<?= $khu_id?"&khu_id=$khu_id":'' ?>&filter=<?= $val ?>"
     style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
            <?= $a ? 'background:var(--bg3);color:var(--text);border:1px solid var(--border2);' : 'background:var(--card);color:var(--text2);border:1px solid var(--border);' ?>">
    <?= $lbl ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php if(!empty($phongs)): ?>
  <table class="tbl tbl-r">
    <thead>
      <tr>
        <th>#</th>
        <th>Khu</th>
        <th>Số phòng</th>
        <th>Giá thuê / tháng</th>
        <th>Diện tích</th>
        <th>Số người</th>
        <th>Trạng thái</th>
        <th style="text-align:center">Thao tác</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($phongs as $i=>$p): ?>
    <tr>
      <td class="mob-hide" style="color:var(--text3)"><?= $i+1 ?></td>
      <td data-label="Khu">
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
      <td data-label="Số phòng"><span class="td-name"><?= htmlspecialchars($p['so_phong']) ?></span></td>
      <td data-label="Giá thuê"><strong style="color:var(--text)"><?= number_format($p['gia']) ?>đ</strong></td>
      <td data-label="Diện tích" class="mob-hide"><?= $p['dien_tich'] ? $p['dien_tich'].' m²' : '—' ?></td>
      <td data-label="Số người" class="mob-hide">👥 <?= $p['so_nguoi'] ?? 1 ?> người</td>
      <td data-label="Trạng thái">
        <?php if($p['trang_thai']==='dang_thue'): ?><span class="pill p-blue">Đang thuê</span>
        <?php elseif($p['trang_thai']==='trong'):  ?><span class="pill p-green">Còn trống</span>
        <?php else: ?>                              <span class="pill p-amber">Bảo trì</span>
        <?php endif; ?>
      </td>
      <td class="mob-actions" style="text-align:center;">
        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
          <?php
            $anhList = [];
            if (!empty($p['anh_phong'])) {
              $anhList = normalize_room_images($p['anh_phong']);
            }
          ?>
          <?php if(!empty($anhList)): ?>
          <button type="button" class="btn btn-outline btn-xs" onclick='openPhotoModal(<?= json_encode($anhList) ?>, "<?= htmlspecialchars($p['so_phong']) ?>")'>
            📷 <?= count($anhList) ?>
          </button>
          <?php endif; ?>
          <a href="index.php?controller=phong&action=chiTiet&id=<?= $p['id'] ?>"
             class="btn btn-primary btn-xs">👁 Chi tiết</a>
          <a href="index.php?controller=phong&action=edit&id=<?= $p['id'] ?>"
             class="btn btn-outline btn-xs">✏ Sửa</a>
          <?php if($p['trang_thai'] !== 'dang_thue'): ?>
          <a href="index.php?controller=phong&action=baoTri&id=<?= $p['id'] ?>"
             class="btn btn-xs <?= $p['trang_thai']==='bao_tri' ? 'btn-success' : 'btn-amber' ?>"
             style="<?= $p['trang_thai']!=='bao_tri' ? 'background:rgba(247,169,79,.15);color:var(--amber);border:1px solid rgba(247,169,79,.3);' : '' ?>"
             onclick="return confirm('<?= $p['trang_thai']==='bao_tri' ? 'Kết thúc bảo trì phòng' : 'Chuyển phòng' ?> <?= htmlspecialchars($p['so_phong']) ?> <?= $p['trang_thai']==='bao_tri' ? '?' : 'sang bảo trì?' ?>')">
            <?= $p['trang_thai']==='bao_tri' ? '✓ Hết bảo trì' : '🔧 Bảo trì' ?>
          </a>
          <?php endif; ?>
          <form method="POST" action="index.php?controller=phong&action=delete" style="display:inline" onsubmit="return confirm('Xóa phòng <?= htmlspecialchars($p['so_phong']) ?>?')">
            <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs" style="cursor:pointer">🗑</button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:48px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px">🏠</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);margin-bottom:4px">Chưa có phòng nào</div>
    <a href="index.php?controller=phong&action=create" style="color:var(--accent);font-size:13px">Thêm phòng đầu tiên →</a>
  </div>
  <?php endif; ?>
</div>

<!-- MODAL XEM ẢNH PHÒNG -->
<div id="photoModal" onclick="if(event.target===this)closePhotoModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:1300;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(6px);">
  <div style="background:var(--card);border:1px solid rgba(79,142,247,.2);border-radius:18px;width:100%;max-width:600px;max-height:85vh;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.6);display:flex;flex-direction:column;">
    <!-- Header -->
    <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
      <div style="font-size:14px;font-weight:700;color:var(--text);" id="photoModalTitle">📷 Ảnh phòng</div>
      <button onclick="closePhotoModal()" style="background:var(--bg3);border:1px solid var(--border);color:var(--text2);width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:14px;">✕</button>
    </div>
    <!-- Ảnh lớn -->
    <div style="flex:1;display:flex;align-items:center;justify-content:center;padding:16px;overflow:hidden;background:var(--bg);min-height:250px;">
      <img id="photoModalImg" src="" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:10px;" alt=""/>
    </div>
    <!-- Thumbnails -->
    <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:8px;overflow-x:auto;flex-shrink:0;" id="photoModalThumbs"></div>
    <!-- Nav -->
    <div style="padding:10px 16px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
      <button onclick="photoNav(-1)" class="btn btn-outline btn-sm">← Trước</button>
      <span style="font-size:12px;color:var(--text3);" id="photoModalCount"></span>
      <button onclick="photoNav(1)" class="btn btn-outline btn-sm">Sau →</button>
    </div>
  </div>
</div>

<script src="frontend/assets/js/phong-index.js"></script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
