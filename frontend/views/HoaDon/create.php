<?php
$title = 'Tạo hóa đơn';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title"><h1>Tạo hóa đơn tháng</h1><p>Nhập chỉ số điện nước để tính tiền</p></div>
  <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:640px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin hóa đơn</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

      <!-- Đơn giá -->
      <div style="display:flex;gap:10px;margin-bottom:20px;padding:12px 14px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.18);border-radius:10px;flex-wrap:wrap;">
        <span style="font-size:13px;color:var(--text2)">Đơn giá:</span>
        <strong style="color:var(--accent)">⚡ <?= number_format($donGia['gia_dien']??3500) ?>đ/kWh</strong>
        <strong style="color:var(--accent)">💧 <?= number_format($donGia['gia_nuoc']??15000) ?>đ/m³</strong>
        <strong style="color:var(--green)">🛎 <?= number_format($donGia['phi_dv']??150000) ?>đ/DV</strong>
        <a href="index.php?controller=dongia&action=index" style="color:var(--text3);font-size:12px;margin-left:auto">Cài đặt →</a>
      </div>

      <form method="POST" action="index.php?controller=hoadon&action=create" id="hdForm">
            <?= Csrf::field() ?>

        <!-- BƯỚC 1: Chọn khu -->
        <div class="form-group">
          <label class="form-label">🏘 Khu trọ</label>
          <select class="form-control" id="khuSel" onchange="loadPhong(this.value)">
            <option value="">— Tất cả khu —</option>
            <?php foreach($khus??[] as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['ten_khu']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- BƯỚC 2: Chọn phòng (lọc theo khu) -->
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">🏠 Phòng <span style="color:var(--red)">*</span></label>
            <select class="form-control" name="phong_id" id="phongSel" required onchange="autoFill()">
              <option value="">— Chọn phòng —</option>
              <?php foreach($phongs??[] as $p): ?>
              <option value="<?= $p['id'] ?>" data-gia="<?= $p['gia'] ?>"
                      data-khu="<?= $p['khu_id'] ?? '' ?>"
                      <?= ($_POST['phong_id']??'')==$p['id']?'selected':'' ?>>
                <?= htmlspecialchars($p['so_phong']) ?>
                <?php if(!empty($p['ten_khu'])): ?>(<?= htmlspecialchars($p['ten_khu']) ?>)<?php endif; ?>
                — <?= number_format($p['gia']) ?>đ
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grid" style="gap:10px">
            <div class="form-group">
              <label class="form-label">Tháng</label>
              <select class="form-control" name="thang">
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m===$thang?'selected':'' ?>>Tháng <?= $m ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Năm</label>
              <select class="form-control" name="nam">
                <?php for($y=date('Y');$y>=date('Y')-2;$y--): ?>
                <option value="<?= $y ?>" <?= $y===$nam?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- ĐIỆN -->
        <div style="background:var(--bg3);border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid var(--border);">
          <div style="font-size:12px;font-weight:700;color:var(--amber);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">⚡ Chỉ số điện</div>
          <div class="form-grid">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số cũ (kWh)</label>
              <input class="form-control" type="number" name="chi_so_dien_cu" id="dien_cu"
                     min="0" step="0.1" placeholder="0"
                     value="<?= $_POST['chi_so_dien_cu']??'0' ?>" oninput="calcTotal()"/>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số mới (kWh)</label>
              <input class="form-control" type="number" name="chi_so_dien_moi" id="dien_moi"
                     min="0" step="0.1" placeholder="0"
                     value="<?= $_POST['chi_so_dien_moi']??'' ?>" oninput="calcTotal()"/>
            </div>
          </div>
          <div style="margin-top:8px;font-size:12px;color:var(--text3)">
            Tiêu thụ: <strong id="kwh" style="color:var(--amber)">0</strong> kWh
            → <strong id="tien_dien_show" style="color:var(--amber)">0đ</strong>
          </div>
        </div>

        <!-- NƯỚC -->
        <div style="background:var(--bg3);border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid var(--border);">
          <div style="font-size:12px;font-weight:700;color:var(--accent);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">💧 Chỉ số nước</div>
          <div class="form-grid">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số cũ (m³)</label>
              <input class="form-control" type="number" name="chi_so_nuoc_cu" id="nuoc_cu"
                     min="0" step="0.1" placeholder="0"
                     value="<?= $_POST['chi_so_nuoc_cu']??'0' ?>" oninput="calcTotal()"/>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số mới (m³)</label>
              <input class="form-control" type="number" name="chi_so_nuoc_moi" id="nuoc_moi"
                     min="0" step="0.1" placeholder="0"
                     value="<?= $_POST['chi_so_nuoc_moi']??'' ?>" oninput="calcTotal()"/>
            </div>
          </div>
          <div style="margin-top:8px;font-size:12px;color:var(--text3)">
            Tiêu thụ: <strong id="m3" style="color:var(--accent)">0</strong> m³
            → <strong id="tien_nuoc_show" style="color:var(--accent)">0đ</strong>
          </div>
        </div>

        <!-- TỔNG -->
        <div style="background:linear-gradient(135deg,rgba(79,142,247,.1),rgba(124,92,252,.07));border:1px solid rgba(79,142,247,.2);border-radius:10px;padding:14px;margin-bottom:18px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:14px;font-weight:600;color:var(--text2)">Tổng tiền phải thu:</span>
            <strong id="tongShow" style="font-size:22px;font-weight:800;color:var(--accent)">0đ</strong>
          </div>
          <div style="margin-top:6px;font-size:12px;color:var(--text3)" id="tongDetail">Chọn phòng và nhập chỉ số để tính</div>
        </div>

        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary" onclick="return validateForm()">💾 Tạo hóa đơn</button>
          <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const GIA_DIEN = <?= $donGia['gia_dien']??3500 ?>;
const GIA_NUOC = <?= $donGia['gia_nuoc']??15000 ?>;
const PHI_DV   = <?= $donGia['phi_dv']??150000 ?>;
const PHI_XE_1 = 100000;
let tienPhong = 0;
let soXe = 0;

// Lọc phòng theo khu bằng AJAX
function loadPhong(khu_id) {
  const sel   = document.getElementById('phongSel');
  const url   = khu_id
    ? `index.php?controller=hoadon&action=getPhongByKhu&khu_id=${khu_id}`
    : `index.php?controller=hoadon&action=getPhongByKhu`;

  sel.innerHTML = '<option value="">Đang tải...</option>';

  fetch(url)
    .then(r => r.json())
    .then(phongs => {
      sel.innerHTML = '<option value="">— Chọn phòng —</option>';
      phongs.forEach(p => {
        const khuTxt = p.ten_khu ? ` (${p.ten_khu})` : '';
        const opt    = document.createElement('option');
        opt.value         = p.id;
        opt.dataset.gia   = p.gia;
        opt.textContent   = `${p.so_phong}${khuTxt} — ${parseInt(p.gia).toLocaleString('vi-VN')}đ`;
        sel.appendChild(opt);
      });
      tienPhong = 0;
      soXe = 0;
      calcTotal();
    })
    .catch(() => {
      sel.innerHTML = '<option value="">Lỗi tải phòng</option>';
    });
}

function autoFill() {
  const sel = document.getElementById('phongSel');
  const opt = sel.options[sel.selectedIndex];
  tienPhong = parseFloat(opt.dataset.gia || 0);

  // Auto-fill chỉ số cũ từ hóa đơn trước
  const phongId = sel.value;
  if (phongId) {
    fetch('index.php?controller=hoadon&action=getChiSoCu&phong_id=' + phongId)
      .then(r => r.json())
      .then(data => {
        const dienCu = document.getElementById('dien_cu');
        const nuocCu = document.getElementById('nuoc_cu');

        if (data.chi_so_dien_moi > 0) {
          dienCu.value = data.chi_so_dien_moi;
          dienCu.style.borderColor = 'rgba(34,201,147,.5)';
          dienCu.style.background = 'rgba(34,201,147,.04)';
          setTimeout(() => { dienCu.style.borderColor = ''; dienCu.style.background = ''; }, 2000);
        } else {
          dienCu.value = 0;
        }

        if (data.chi_so_nuoc_moi > 0) {
          nuocCu.value = data.chi_so_nuoc_moi;
          nuocCu.style.borderColor = 'rgba(34,201,147,.5)';
          nuocCu.style.background = 'rgba(34,201,147,.04)';
          setTimeout(() => { nuocCu.style.borderColor = ''; nuocCu.style.background = ''; }, 2000);
        } else {
          nuocCu.value = 0;
        }

        soXe = parseInt(data.so_xe) || 0;
        calcTotal();
      })
      .catch(() => {});
  }

  calcTotal();
}

function calcTotal() {
  const dc  = parseFloat(document.getElementById('dien_cu').value)  || 0;
  const dm  = parseFloat(document.getElementById('dien_moi').value) || 0;
  const nc  = parseFloat(document.getElementById('nuoc_cu').value)  || 0;
  const nm  = parseFloat(document.getElementById('nuoc_moi').value) || 0;
  const kwh = Math.max(0, dm - dc);
  const m3  = Math.max(0, nm - nc);
  const tD    = kwh * GIA_DIEN;
  const tN    = m3  * GIA_NUOC;
  const phiXe = soXe * PHI_XE_1;
  const tong  = tienPhong + tD + tN + PHI_DV + phiXe;

  // Validate: chỉ số mới phải >= chỉ số cũ
  const dienMoiEl = document.getElementById('dien_moi');
  const nuocMoiEl = document.getElementById('nuoc_moi');

  if (dm > 0 && dm < dc) {
    dienMoiEl.style.borderColor = 'var(--red)';
    dienMoiEl.style.background = 'rgba(247,92,92,.06)';
  } else {
    dienMoiEl.style.borderColor = '';
    dienMoiEl.style.background = '';
  }

  if (nm > 0 && nm < nc) {
    nuocMoiEl.style.borderColor = 'var(--red)';
    nuocMoiEl.style.background = 'rgba(247,92,92,.06)';
  } else {
    nuocMoiEl.style.borderColor = '';
    nuocMoiEl.style.background = '';
  }

  document.getElementById('kwh').textContent           = kwh.toFixed(1);
  document.getElementById('m3').textContent            = m3.toFixed(1);
  document.getElementById('tien_dien_show').textContent = tD.toLocaleString('vi-VN') + 'đ';
  document.getElementById('tien_nuoc_show').textContent = tN.toLocaleString('vi-VN') + 'đ';
  document.getElementById('tongShow').textContent       = tong.toLocaleString('vi-VN') + 'đ';
  let detail = `Phòng: ${tienPhong.toLocaleString('vi-VN')}đ + Điện: ${tD.toLocaleString('vi-VN')}đ + Nước: ${tN.toLocaleString('vi-VN')}đ + DV: ${PHI_DV.toLocaleString('vi-VN')}đ`;
  if (soXe > 0) detail += ` + Xe(${soXe}): ${phiXe.toLocaleString('vi-VN')}đ`;
  document.getElementById('tongDetail').textContent = detail;
}

function validateForm() {
  const dc = parseFloat(document.getElementById('dien_cu').value) || 0;
  const dm = parseFloat(document.getElementById('dien_moi').value) || 0;
  const nc = parseFloat(document.getElementById('nuoc_cu').value) || 0;
  const nm = parseFloat(document.getElementById('nuoc_moi').value) || 0;

  if (!document.getElementById('phongSel').value) {
    alert('Vui lòng chọn phòng!');
    return false;
  }
  if (dm > 0 && dm < dc) {
    alert('⚠ Chỉ số điện mới (' + dm + ') không được nhỏ hơn chỉ số cũ (' + dc + ')!\n\nChỉ số cũ được lấy từ hóa đơn tháng trước.');
    document.getElementById('dien_moi').focus();
    return false;
  }
  if (nm > 0 && nm < nc) {
    alert('⚠ Chỉ số nước mới (' + nm + ') không được nhỏ hơn chỉ số cũ (' + nc + ')!\n\nChỉ số cũ được lấy từ hóa đơn tháng trước.');
    document.getElementById('nuoc_moi').focus();
    return false;
  }
  return true;
}
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>