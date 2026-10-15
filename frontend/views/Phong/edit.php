<?php
$title = 'Chỉnh sửa phòng';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Chỉnh sửa phòng</h1>
    <p>Cập nhật thông tin phòng <?= htmlspecialchars($phong['so_phong']??'') ?></p>
  </div>
  <a href="index.php?controller=phong&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:660px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin phòng</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?>
      <div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?controller=phong&action=edit&id=<?= $phong['id'] ?>
            <?= Csrf::field() ?>" enctype="multipart/form-data">

        <!-- ẢNH PHÒNG (nhiều ảnh) -->
        <div class="form-group">
          <label class="form-label">Ảnh phòng <span style="font-size:11px;font-weight:400;color:var(--text3);">(tối đa 10 ảnh · JPG, PNG, WEBP · mỗi ảnh ≤ 5MB)</span></label>
          <div style="padding:14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
            <!-- Ảnh hiện có từ DB -->
            <?php
              $anhList = [];
              if (!empty($phong['anh_phong'])) {
                $anhList = normalize_room_images($phong['anh_phong']);
              }
            ?>
            <?php if(!empty($anhList)): ?>
            <div style="margin-bottom:10px;">
              <div style="font-size:11px;color:var(--text3);margin-bottom:6px;">Ảnh hiện tại — tích vào ảnh muốn xóa:</div>
              <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <?php foreach($anhList as $idx => $src): ?>
                <div style="position:relative;width:100px;height:80px;flex-shrink:0;">
                  <img src="<?=htmlspecialchars($src)?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;border:1px solid var(--border);"
                       onerror="this.parentElement.style.display='none'"/>
                  <label style="position:absolute;top:-6px;right:-6px;cursor:pointer;">
                    <input type="checkbox" name="xoa_anh[]" value="<?=htmlspecialchars($src)?>"
                           style="display:none;" onchange="toggleXoa(this)"/>
                    <span class="xoa-badge" style="width:20px;height:20px;border-radius:50%;background:#e53e3e;color:#fff;font-size:13px;display:flex;align-items:center;justify-content:center;opacity:0;transition:.2s;">✕</span>
                  </label>
                  <?php if($idx===0): ?>
                  <span style="position:absolute;bottom:4px;left:4px;font-size:9px;background:rgba(79,142,247,.85);color:#fff;padding:1px 5px;border-radius:4px;">Chính</span>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>
            <!-- Grid preview ảnh mới chọn -->
            <div id="imgGrid" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;"></div>
            <div id="imgPlaceholder" style="display:<?= (empty($anhList)) ? 'flex' : 'none' ?>;flex-direction:column;align-items:center;justify-content:center;height:70px;border:2px dashed var(--border);border-radius:10px;color:var(--text3);font-size:13px;gap:6px;margin-bottom:12px;">
              <span style="font-size:24px;">🏠</span><span>Chưa có ảnh</span>
            </div>
            <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;background:var(--card);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;color:var(--text2);transition:border-color .2s;"
                   onmouseover="this.style.borderColor='rgba(79,142,247,.4)'" onmouseout="this.style.borderColor='var(--border)'">
              📷 Thêm ảnh mới
              <input type="file" id="fileInput" name="anh_phong[]" accept="image/*" multiple style="display:none;" onchange="handleImages(this)"/>
            </label>
            <span style="font-size:11px;color:var(--text3);margin-left:10px;">Giữ Ctrl/Cmd để chọn nhiều ảnh</span>
          </div>
        </div>

        <!-- Phân khu -->
        <div class="form-group">
          <label class="form-label">Thuộc khu trọ</label>
          <select class="form-control" name="khu_id" id="khuSel" onchange="applyPrefix(this)">
            <option value="">— Chưa phân khu —</option>
            <?php foreach($khus??[] as $k): ?>
            <option value="<?= $k['id'] ?>"
                    data-ma="<?= htmlspecialchars($k['ma_khu']) ?>"
                    <?= ($phong['khu_id']??null)==$k['id']?'selected':'' ?>>
              <?= htmlspecialchars($k['ten_khu']) ?> (<?= htmlspecialchars($k['ma_khu']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số phòng <span style="color:var(--red)">*</span>
              <span id="prefixHint" style="font-size:10px;color:var(--green);font-weight:400;display:none;"> ✓ Tiền tố tự động</span>
            </label>
            <input class="form-control" type="text" name="so_phong" id="soPhong"
                   value="<?= htmlspecialchars($phong['so_phong']??'') ?>" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Giá thuê / tháng (đ) <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="number" name="gia" id="giaInput"
                   value="<?= htmlspecialchars($phong['gia']??'') ?>" min="0" required
                   oninput="checkGiaChange()"/>
            <div style="font-size:11px;color:var(--text3);margin-top:4px;">
              Giá hiện tại: <strong style="color:var(--accent);"><?= number_format((float)($phong['gia']??0)) ?>đ</strong>
            </div>
          </div>
        </div>

        <!-- Lý do đổi giá (ẩn, hiện khi giá thay đổi) -->
        <div class="form-group" id="lyDoDoiGia" style="display:none;">
          <label class="form-label" style="color:var(--amber);">📝 Lý do thay đổi giá</label>
          <input class="form-control" type="text" name="ly_do_doi_gia"
                 placeholder="VD: Tăng giá theo thị trường, nâng cấp phòng..."
                 style="border-color:rgba(247,169,79,.3);background:rgba(247,169,79,.04);"/>
          <div style="font-size:11px;color:var(--amber);margin-top:4px;">⚠ Giá đã thay đổi — lý do sẽ được lưu vào lịch sử</div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Diện tích (m²)</label>
            <input class="form-control" type="number" name="dien_tich" step="0.1"
                   value="<?= htmlspecialchars($phong['dien_tich']??'') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Số lượng người tối đa</label>
            <input class="form-control" type="number" name="so_nguoi" min="1" max="20"
                   value="<?= htmlspecialchars($phong['so_nguoi']??'2') ?>"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Trạng thái</label>
          <select class="form-control" name="trang_thai">
            <option value="trong"     <?= ($phong['trang_thai']??'')==='trong'    ?'selected':'' ?>>Còn trống</option>
            <option value="dang_thue" <?= ($phong['trang_thai']??'')==='dang_thue'?'selected':'' ?>>Đang thuê</option>
            <option value="bao_tri"   <?= ($phong['trang_thai']??'')==='bao_tri'  ?'selected':'' ?>>Bảo trì</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Mô tả / Tiện ích</label>
          <textarea class="form-control" name="mo_ta"><?= htmlspecialchars($phong['mo_ta']??'') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;padding-top:4px;">
          <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
          <a href="index.php?controller=phong&action=index" class="btn btn-outline">Hủy</a>
          <form method="POST" action="index.php?controller=phong&action=delete" style="display:inline;" onsubmit="return confirm('Xóa phòng này?')">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $phong['id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs" style="cursor:pointer;">🗑 Xóa phòng</button>
          </form>
        </div>
      </form>
    </div>
  </div>

  <!-- LỊCH SỬ GIÁ PHÒNG -->
  <?php
    $lichSuGiaModel = new LichSuGiaModel();
    $lichSuGia = $lichSuGiaModel->getByPhong($phong['id']);
  ?>
  <?php if(!empty($lichSuGia)): ?>
  <div class="card" style="margin-top:18px;">
    <div class="card-header">
      <div>
        <div class="card-title">📊 Lịch sử thay đổi giá</div>
        <div class="card-sub"><?= count($lichSuGia) ?> lần thay đổi</div>
      </div>
      <a href="index.php?controller=phong&action=lichSuGia&id=<?= $phong['id'] ?>" class="card-link">Xem tất cả →</a>
    </div>
    <?php foreach(array_slice($lichSuGia, 0, 5) as $ls): ?>
    <div style="padding:12px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="font-size:13px;color:var(--text3);text-decoration:line-through;"><?= number_format((float)$ls['gia_cu']) ?>đ</span>
          <span style="color:var(--text3);">→</span>
          <?php $isUp = (float)$ls['gia_moi'] >= (float)$ls['gia_cu']; ?>
          <span style="font-size:14px;font-weight:700;color:<?= $isUp ? 'var(--red)' : 'var(--green)' ?>;"><?= number_format((float)$ls['gia_moi']) ?>đ</span>
          <?php
            $pct = (float)$ls['gia_cu'] > 0 ? round(((float)$ls['gia_moi'] - (float)$ls['gia_cu']) / (float)$ls['gia_cu'] * 100, 1) : 0;
          ?>
          <span style="font-size:11px;font-weight:700;padding:2px 7px;border-radius:5px;background:<?= $isUp ? 'rgba(247,92,92,.1)' : 'rgba(34,201,147,.1)' ?>;color:<?= $isUp ? 'var(--red)' : 'var(--green)' ?>;">
            <?= $isUp ? '↑' : '↓' ?> <?= abs($pct) ?>%
          </span>
        </div>
        <?php if(!empty($ls['ghi_chu'])): ?>
        <div style="font-size:11px;color:var(--text3);margin-top:3px;">💬 <?= htmlspecialchars($ls['ghi_chu']) ?></div>
        <?php endif; ?>
      </div>
      <div style="text-align:right;flex-shrink:0;">
        <div style="font-size:11px;color:var(--text3);"><?= date('d/m/Y', strtotime($ls['ngay_thay_doi'])) ?></div>
        <?php if(!empty($ls['nguoi_thay_doi'])): ?>
        <div style="font-size:10px;color:var(--text3);">👤 <?= htmlspecialchars($ls['nguoi_thay_doi']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<script>
/* ── Kiểm tra thay đổi giá ── */
const giaGoc = <?= (float)($phong['gia'] ?? 0) ?>;

function checkGiaChange() {
  const giaNew = parseFloat(document.getElementById('giaInput').value) || 0;
  const box = document.getElementById('lyDoDoiGia');
  if (giaNew !== giaGoc && giaNew > 0) {
    box.style.display = 'block';
  } else {
    box.style.display = 'none';
  }
}

/* ── Multi-image upload ── */
let selectedFiles = [];

function handleImages(input) {
  const newFiles = Array.from(input.files);
  const allowed  = ['image/jpeg','image/png','image/webp','image/gif'];
  const maxSize  = 5 * 1024 * 1024;
  newFiles.forEach(f => {
    if (!allowed.includes(f.type))  { alert(`"${f.name}" không phải ảnh hợp lệ.`); return; }
    if (f.size > maxSize)           { alert(`"${f.name}" vượt quá 5MB.`); return; }
    if (selectedFiles.length >= 10) { alert('Tối đa 10 ảnh.'); return; }
    selectedFiles.push(f);
  });
  input.value = '';
  renderPreviews();
  syncFileInput();
}

function removeImage(idx) {
  selectedFiles.splice(idx, 1);
  renderPreviews();
  syncFileInput();
}

function renderPreviews() {
  const grid = document.getElementById('imgGrid');
  const ph   = document.getElementById('imgPlaceholder');
  grid.innerHTML = '';
  if (selectedFiles.length === 0) {
    if(ph) ph.style.display = 'flex';
    return;
  }
  if(ph) ph.style.display = 'none';
  selectedFiles.forEach((f, i) => {
    const url = URL.createObjectURL(f);
    const wrap = document.createElement('div');
    wrap.style.cssText = 'position:relative;width:100px;height:80px;flex-shrink:0;';
    wrap.innerHTML = `
      <img src="${url}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;border:1px solid var(--border);" onload="URL.revokeObjectURL(this.src)"/>
      <button type="button" onclick="removeImage(${i})"
        style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:#e53e3e;border:none;color:#fff;font-size:13px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
      <span style="position:absolute;bottom:4px;left:4px;font-size:9px;background:rgba(34,201,147,.85);color:#fff;padding:1px 5px;border-radius:4px;">Mới</span>
    `;
    grid.appendChild(wrap);
  });
}

function syncFileInput() {
  const dt = new DataTransfer();
  selectedFiles.forEach(f => dt.items.add(f));
  document.getElementById('fileInput').files = dt.files;
}

/* Toggle xóa ảnh cũ */
function toggleXoa(cb) {
  const badge = cb.nextElementSibling;
  const img   = cb.closest('div[style]').querySelector('img');
  if (cb.checked) {
    badge.style.opacity = '1';
    img.style.opacity   = '0.35';
    img.style.filter    = 'grayscale(1)';
  } else {
    badge.style.opacity = '0';
    img.style.opacity   = '1';
    img.style.filter    = '';
  }
}

function applyPrefix(sel) {
  const ma      = sel.options[sel.selectedIndex]?.dataset?.ma || '';
  const inp     = document.getElementById('soPhong');
  const numeric = inp.value.replace(/^[A-Za-z]+/, '');
  inp.value = ma + numeric;
  const hint = document.getElementById('prefixHint');
  hint.style.display = ma ? 'inline' : 'none';
  inp.style.borderColor = 'rgba(34,201,147,.5)';
  inp.style.background  = 'rgba(34,201,147,.04)';
  setTimeout(() => { inp.style.borderColor = ''; inp.style.background = ''; }, 1800);
}
window.addEventListener('DOMContentLoaded', () => {
  const sel = document.getElementById('khuSel');
  if (sel && sel.value) document.getElementById('prefixHint').style.display = 'inline';
});
</script>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>
