<?php require_once 'frontend/views/Layouts/header.php'; ?>
<?php
$anhList = [];
if (!empty($phong['anh_phong'])) {
    $anhList = normalize_room_images($phong['anh_phong']);
}
$trangThaiLabel = ['trong'=>'Còn trống','dang_thue'=>'Đang thuê','bao_tri'=>'Bảo trì'];
$trangThaiColor = ['trong'=>'var(--green)','dang_thue'=>'var(--accent)','bao_tri'=>'var(--amber,#f59e0b)'];
$tt = $phong['trang_thai'] ?? 'trong';
?>

<!-- Breadcrumb -->
<div class="page-header">
  <div class="page-title">
    <h1>Chi tiết phòng <?= htmlspecialchars($phong['so_phong']) ?></h1>
    <?php if(!empty($phong['ten_khu'])): ?>
    <p style="color:var(--accent);">🏘 <?= htmlspecialchars($phong['ten_khu']) ?></p>
    <?php endif; ?>
  </div>
  <a href="index.php?controller=user&action=phong" class="btn btn-outline">← Danh sách phòng</a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

  <!-- Cột trái: ảnh -->
  <div>
    <?php if(!empty($anhList)): ?>
    <!-- Ảnh chính -->
    <div style="border-radius:14px;overflow:hidden;border:1px solid var(--border);background:var(--bg3);margin-bottom:12px;cursor:zoom-in;"
         onclick="openModal(0)">
      <img id="mainImg" src="<?= htmlspecialchars($anhList[0]) ?>"
           style="width:100%;height:340px;object-fit:cover;display:block;"
           onerror="this.src='';this.parentElement.style.display='none'"/>
    </div>
    <!-- Thumbnail strip -->
    <?php if(count($anhList) > 1): ?>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php foreach($anhList as $idx => $src): ?>
      <img src="<?= htmlspecialchars($src) ?>"
           id="thumb-<?= $idx ?>"
           onclick="switchImg(<?= $idx ?>, '<?= htmlspecialchars($src, ENT_QUOTES) ?>')"
           style="width:80px;height:60px;object-fit:cover;border-radius:8px;border:2px solid <?= $idx===0?'var(--accent)':'var(--border)' ?>;cursor:pointer;transition:border-color .2s;"
           onerror="this.style.display='none'"/>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Placeholder không ảnh -->
    <div style="height:340px;border-radius:14px;border:2px dashed var(--border);background:var(--bg3);display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text3);gap:12px;">
      <span style="font-size:72px;">🏠</span>
      <span style="font-size:15px;font-weight:600;color:var(--text2);">Phòng <?= htmlspecialchars($phong['so_phong']) ?></span>
      <span style="font-size:12px;">Chưa có ảnh phòng</span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Cột phải: thông tin -->
  <div style="display:flex;flex-direction:column;gap:14px;">

    <!-- Trạng thái -->
    <div class="card" style="padding:16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:13px;font-weight:700;color:var(--text2);">TRẠNG THÁI</span>
        <span style="font-size:13px;font-weight:700;color:<?= $trangThaiColor[$tt] ?? 'var(--text)' ?>;">
          ● <?= $trangThaiLabel[$tt] ?? $tt ?>
        </span>
      </div>

      <!-- Giá nổi bật -->
      <div style="text-align:center;padding:14px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);">
        <div style="font-size:11px;color:var(--text3);margin-bottom:4px;">GIÁ THUÊ / THÁNG</div>
        <div style="font-size:26px;font-weight:800;color:var(--accent);">
          <?= number_format($phong['gia']) ?>đ
        </div>
      </div>
    </div>

    <!-- Thông tin chi tiết -->
    <div class="card" style="padding:16px;">
      <div style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:12px;">THÔNG TIN PHÒNG</div>
      <div style="display:flex;flex-direction:column;gap:10px;">

        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border);">
          <span style="font-size:12px;color:var(--text3);">Số phòng</span>
          <span style="font-size:14px;font-weight:700;color:var(--text);"><?= htmlspecialchars($phong['so_phong']) ?></span>
        </div>

        <?php if(!empty($phong['ten_khu'])): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border);">
          <span style="font-size:12px;color:var(--text3);">Khu trọ</span>
          <span style="font-size:13px;font-weight:600;color:var(--accent);">🏘 <?= htmlspecialchars($phong['ten_khu']) ?></span>
        </div>
        <?php endif; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border);">
          <span style="font-size:12px;color:var(--text3);">Diện tích</span>
          <span style="font-size:13px;font-weight:600;color:var(--text);"><?= $phong['dien_tich'] ? number_format($phong['dien_tich'],1).' m²' : '—' ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border);">
          <span style="font-size:12px;color:var(--text3);">Sức chứa tối đa</span>
          <span style="font-size:13px;font-weight:600;color:var(--text);">👥 <?= $phong['so_nguoi'] ?? 1 ?> người</span>
        </div>

      </div>
    </div>

    <!-- Mô tả / tiện ích -->
    <?php if(!empty($phong['mo_ta'])): ?>
    <div class="card" style="padding:16px;">
      <div style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:8px;">MÔ TẢ / TIỆN ÍCH</div>
      <div style="font-size:13px;color:var(--text2);line-height:1.7;white-space:pre-wrap;"><?= htmlspecialchars($phong['mo_ta']) ?></div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- ── LIGHTBOX MODAL ── -->
<div id="lightbox" onclick="closeModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;">
  <button onclick="prevImg(event)" style="position:fixed;left:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.12);border:none;color:#fff;font-size:28px;width:46px;height:46px;border-radius:50%;cursor:pointer;">‹</button>
  <img id="lbImg" style="max-width:90vw;max-height:88vh;border-radius:12px;object-fit:contain;box-shadow:0 8px 48px rgba(0,0,0,.6);" onclick="event.stopPropagation()"/>
  <button onclick="nextImg(event)" style="position:fixed;right:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.12);border:none;color:#fff;font-size:28px;width:46px;height:46px;border-radius:50%;cursor:pointer;">›</button>
  <button onclick="closeModal()" style="position:fixed;top:18px;right:20px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:20px;width:38px;height:38px;border-radius:50%;cursor:pointer;">✕</button>
  <div id="lbCounter" style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.6);font-size:13px;"></div>
</div>

<script>
const imgs = <?= json_encode(array_values($anhList)) ?>;
let curIdx = 0;

function openModal(idx) {
  if (!imgs.length) return;
  curIdx = idx;
  document.getElementById('lbImg').src = imgs[curIdx];
  document.getElementById('lbCounter').textContent = (curIdx+1) + ' / ' + imgs.length;
  document.getElementById('lightbox').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('lightbox').style.display = 'none';
  document.body.style.overflow = '';
}
function prevImg(e) {
  e.stopPropagation();
  curIdx = (curIdx - 1 + imgs.length) % imgs.length;
  document.getElementById('lbImg').src = imgs[curIdx];
  document.getElementById('lbCounter').textContent = (curIdx+1) + ' / ' + imgs.length;
}
function nextImg(e) {
  e.stopPropagation();
  curIdx = (curIdx + 1) % imgs.length;
  document.getElementById('lbImg').src = imgs[curIdx];
  document.getElementById('lbCounter').textContent = (curIdx+1) + ' / ' + imgs.length;
}
function switchImg(idx, src) {
  curIdx = idx;
  document.getElementById('mainImg').src = src;
  document.querySelectorAll('[id^="thumb-"]').forEach((t,i) => {
    t.style.borderColor = i === idx ? 'var(--accent)' : 'var(--border)';
  });
}
document.addEventListener('keydown', e => {
  if (document.getElementById('lightbox').style.display === 'none') return;
  if (e.key === 'ArrowLeft')  prevImg(e);
  if (e.key === 'ArrowRight') nextImg(e);
  if (e.key === 'Escape')     closeModal();
});
</script>

<!-- Responsive mobile -->
<link rel="stylesheet" href="frontend/assets/css/phong-chi-tiet.css">

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
