<?php
$title = 'Thêm phòng mới';
require_once 'frontend/views/Layouts/header.php';
$defKhu = (int)($_GET['khu_id'] ?? 0);
?>
<div class="page-header">
  <div class="page-title"><h1>Thêm phòng mới</h1></div>
  <a href="index.php?controller=phong&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:600px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin phòng</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?=htmlspecialchars($error)?></div><?php endif; ?>
      <form method="POST" action="index.php?controller=phong&action=create" enctype="multipart/form-data">
            <?= Csrf::field() ?>

        <!-- Chọn khu → tự điền số phòng -->
        <div class="form-group">
          <label class="form-label">Thuộc khu trọ</label>
          <select class="form-control" name="khu_id" id="khuSel" onchange="loadNextRoom(this.value)">
            <option value="">— Chưa phân khu —</option>
            <?php foreach($khus??[] as $k): ?>
            <option value="<?=$k['id']?>" <?=($defKhu==$k['id']||($_POST['khu_id']??'')==$k['id'])?'selected':''?>>
              <?=htmlspecialchars($k['ten_khu'])?> (<?=htmlspecialchars($k['ma_khu'])?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- ẢNH PHÒNG (nhiều ảnh) -->
        <div class="form-group">
          <label class="form-label">Ảnh phòng <span style="font-size:11px;font-weight:400;color:var(--text3);">(tối đa 10 ảnh · JPG, PNG, WEBP · mỗi ảnh ≤ 5MB)</span></label>
          <div style="padding:14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
            <!-- Grid preview -->
            <div id="imgGrid" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;"></div>
            <!-- Placeholder khi chưa có ảnh -->
            <div id="imgPlaceholder" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:90px;border:2px dashed var(--border);border-radius:10px;color:var(--text3);font-size:13px;gap:6px;margin-bottom:12px;">
              <span style="font-size:28px;">🏠</span>
              <span>Chưa chọn ảnh nào</span>
            </div>
            <!-- Nút chọn -->
            <label id="btnChonAnh" style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;background:var(--card);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;color:var(--text2);transition:border-color .2s;"
                   onmouseover="this.style.borderColor='rgba(79,142,247,.4)'" onmouseout="this.style.borderColor='var(--border)'">
              📷 Chọn ảnh
              <input type="file" id="fileInput" name="anh_phong[]" accept="image/*" multiple style="display:none;" onchange="handleImages(this)"/>
            </label>
            <span style="font-size:11px;color:var(--text3);margin-left:10px;">Giữ Ctrl/Cmd để chọn nhiều ảnh cùng lúc</span>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số phòng <span style="color:var(--red)">*</span>
              <span id="autoHint" style="font-size:10px;color:var(--green);font-weight:400;display:none;"> ✓ Tự động điền</span>
            </label>
            <input class="form-control" type="text" name="so_phong" id="soPhong"
                   placeholder="Chọn khu để tự điền hoặc nhập thủ công"
                   value="<?=htmlspecialchars($_POST['so_phong']??'')?>" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Giá thuê / tháng (đ) <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="number" name="gia" placeholder="2500000"
                   value="<?=htmlspecialchars($_POST['gia']??'')?>" min="0" required/>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Diện tích (m²)</label>
            <input class="form-control" type="number" name="dien_tich" placeholder="20" step="0.1"
                   value="<?=htmlspecialchars($_POST['dien_tich']??'')?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Số người tối đa</label>
            <input class="form-control" type="number" name="so_nguoi" placeholder="2" min="1" max="20"
                   value="<?=htmlspecialchars($_POST['so_nguoi']??'2')?>"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Mô tả / Tiện ích</label>
          <textarea class="form-control" name="mo_ta" placeholder="Điều hòa, nóng lạnh, ban công..."><?=htmlspecialchars($_POST['mo_ta']??'')?></textarea>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary">💾 Lưu phòng</button>
          <a href="index.php?controller=phong&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
/* ── Multi-image upload ── */
let selectedFiles = [];  // DataTransfer-based file list

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

  /* Reset input để có thể chọn thêm ảnh khác lần sau */
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
    ph.style.display = 'flex';
    return;
  }
  ph.style.display = 'none';

  selectedFiles.forEach((f, i) => {
    const url = URL.createObjectURL(f);
    const wrap = document.createElement('div');
    wrap.style.cssText = 'position:relative;width:100px;height:80px;flex-shrink:0;';
    wrap.innerHTML = `
      <img src="${url}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;border:1px solid var(--border);" onload="URL.revokeObjectURL(this.src)"/>
      <button type="button" onclick="removeImage(${i})"
        style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:#e53e3e;border:none;color:#fff;font-size:13px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
      ${i===0 ? '<span style="position:absolute;bottom:4px;left:4px;font-size:9px;background:rgba(79,142,247,.85);color:#fff;padding:1px 5px;border-radius:4px;">Chính</span>' : ''}
    `;
    grid.appendChild(wrap);
  });
}

function syncFileInput() {
  /* Gán lại file list vào input để form submit đúng */
  const dt = new DataTransfer();
  selectedFiles.forEach(f => dt.items.add(f));
  document.getElementById('fileInput').files = dt.files;
}

<?php if($defKhu): ?>
window.onload = () => loadNextRoom(<?=$defKhu?>);
<?php endif; ?>

function loadNextRoom(khu_id) {
  if (!khu_id) {
    document.getElementById('soPhong').placeholder = 'Nhập số phòng thủ công';
    document.getElementById('autoHint').style.display = 'none';
    return;
  }
  fetch(`index.php?controller=khutro&action=getNextRoom&khu_id=${khu_id}`)
    .then(r => r.json())
    .then(d => {
      const inp = document.getElementById('soPhong');
      inp.value = d.so_phong;
      inp.style.borderColor = 'rgba(34,201,147,.5)';
      inp.style.background  = 'rgba(34,201,147,.04)';
      document.getElementById('autoHint').style.display = 'inline';
      setTimeout(() => {
        inp.style.borderColor = '';
        inp.style.background  = '';
      }, 2000);
    })
    .catch(() => {});
}
</script>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>