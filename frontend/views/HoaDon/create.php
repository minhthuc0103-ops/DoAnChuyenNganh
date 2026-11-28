<?php
$title = 'Tạo hóa đơn';
require_once 'frontend/views/Layouts/header.php';

$phong_id_init = (int)($_POST['phong_id'] ?? $_GET['phong_id'] ?? 0);
$thang_init    = (int)($_POST['thang']    ?? $_GET['thang']    ?? date('m'));
$nam_init      = (int)($_POST['nam']      ?? $_GET['nam']      ?? date('Y'));
$dien_moi_init = $_POST['chi_so_dien_moi'] ?? $_GET['dien_moi'] ?? '';
$nuoc_moi_init = $_POST['chi_so_nuoc_moi'] ?? $_GET['nuoc_moi'] ?? '';
$anh_dien_init = $_POST['anh_cong_to_dien'] ?? $_GET['anh_dien'] ?? '';
$anh_nuoc_init = $_POST['anh_cong_to_nuoc'] ?? $_GET['anh_nuoc'] ?? '';
$bcs_id_init   = (int)($_POST['bao_chi_so_id'] ?? $_GET['bcs_id'] ?? 0);
?>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<div class="page-header">
  <div class="page-title">
    <h1>Tạo hóa đơn tháng</h1>
    <p>Nhập chỉ số hoặc sử dụng AI chụp ảnh công tơ điện nước tự động</p>
  </div>
  <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:680px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin hóa đơn</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

      <!-- Đơn giá -->
      <div style="display:flex;gap:10px;margin-bottom:20px;padding:12px 14px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.18);border-radius:10px;flex-wrap:wrap;align-items:center;">
        <span style="font-size:13px;color:var(--text2)">Đơn giá:</span>
        <strong style="color:var(--accent)">⚡ <?= number_format($donGia['gia_dien']??3500) ?>đ/kWh</strong>
        <strong style="color:var(--accent)">💧 <?= number_format($donGia['gia_nuoc']??15000) ?>đ/m³</strong>
        <strong style="color:var(--green)">🛎 <?= number_format($donGia['phi_dv']??150000) ?>đ/DV</strong>
        <a href="index.php?controller=dongia&action=index" style="color:var(--text3);font-size:12px;margin-left:auto">Cài đặt →</a>
      </div>

      <form method="POST" action="index.php?controller=hoadon&action=create" id="hdForm">
        <?= Csrf::field() ?>
        <input type="hidden" name="bao_chi_so_id" id="bao_chi_so_id" value="<?= $bcs_id_init ?>">
        <input type="hidden" name="anh_cong_to_dien" id="anh_cong_to_dien" value="<?= htmlspecialchars($anh_dien_init) ?>">
        <input type="hidden" name="anh_cong_to_nuoc" id="anh_cong_to_nuoc" value="<?= htmlspecialchars($anh_nuoc_init) ?>">

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
              <option value=""><?= empty($phongs) ? '— Chưa có phòng nào có hợp đồng —' : '— Chọn phòng —' ?></option>
              <?php foreach($phongs??[] as $p): ?>
              <option value="<?= $p['id'] ?>" data-gia="<?= $p['gia'] ?>"
                      data-khu="<?= $p['khu_id'] ?? '' ?>"
                      <?= $phong_id_init == $p['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['so_phong']) ?>
                <?php if(!empty($p['ten_khu'])): ?>(<?= htmlspecialchars($p['ten_khu']) ?>)<?php endif; ?>
                — <?= number_format($p['gia']) ?>đ
              </option>
              <?php endforeach; ?>
            </select>
            <div style="font-size:11px;color:var(--text3);margin-top:5px;">Chỉ hiện phòng đang có hợp đồng hiệu lực.</div>
          </div>
          <div class="form-grid" style="gap:10px">
            <div class="form-group">
              <label class="form-label">Tháng</label>
              <select class="form-control" name="thang" id="sel_thang" onchange="autoFill()">
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m===$thang_init?'selected':'' ?>>Tháng <?= $m ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Năm</label>
              <select class="form-control" name="nam" id="sel_nam" onchange="autoFill()">
                <?php for($y=date('Y');$y>=date('Y')-2;$y--): ?>
                <option value="<?= $y ?>" <?= $y===$nam_init?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- CHẾ ĐỘ AI TỰ ĐỘNG TẠO HÓA ĐƠN KHI QUÉT / TẢI ẢNH -->
        <div style="background:linear-gradient(135deg,rgba(34,201,147,.12),rgba(79,142,247,.1));border:1px solid rgba(34,201,147,.35);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" id="auto_create_invoice" checked style="width:18px;height:18px;accent-color:var(--green);cursor:pointer;">
            <label for="auto_create_invoice" style="cursor:pointer;font-size:13px;font-weight:700;color:var(--text);">
              🤖 Tự động tạo / cập nhật hóa đơn ngay khi quét hoặc tải ảnh công tơ lên
            </label>
          </div>
          <span class="badge" style="background:rgba(34,201,147,.2);color:var(--green);font-size:11px;">AI Vision Auto-Invoice</span>
        </div>

        <!-- BANNER KẾT QUẢ TỰ ĐỘNG TẠO HÓA ĐƠN -->
        <div id="auto_invoice_banner" style="display:none;background:rgba(34,201,147,.14);border:1px solid var(--green);border-radius:10px;padding:14px;margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
              <div id="auto_invoice_title" style="font-weight:800;color:var(--green);font-size:14px;">✅ Đã tự động tạo hóa đơn thành công!</div>
              <div id="auto_invoice_desc" style="font-size:13px;color:var(--text2);margin-top:4px;"></div>
            </div>
            <div style="display:flex;gap:8px;">
              <a id="auto_invoice_link" href="#" class="btn btn-primary" style="padding:6px 12px;font-size:12px;">👁 Xem hóa đơn</a>
              <a href="index.php?controller=hoadon&action=index" class="btn btn-outline" style="padding:6px 12px;font-size:12px;">📋 Danh sách HĐ</a>
            </div>
          </div>
        </div>

        <!-- BANNER GỢI Ý KHI NGƯỜI THUÊ ĐÃ GỬI BÁO SỐ -->
        <div id="bcs_banner" style="display:none;background:rgba(34,201,147,.1);border:1px solid rgba(34,201,147,.3);border-radius:10px;padding:12px 14px;margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div>
              <strong style="color:var(--green)">✨ Người thuê đã báo số tháng này:</strong>
              <div id="bcs_info" style="font-size:13px;color:var(--text2);margin-top:2px;"></div>
            </div>
            <button type="button" class="btn btn-primary" onclick="applyTenantReport()" style="padding:6px 12px;font-size:12px;">
              ⚡ Áp dụng số này
            </button>
          </div>
        </div>

        <!-- ĐIỆN -->
        <div style="background:var(--bg3);border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div style="font-size:12px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.5px;">⚡ Chỉ số điện</div>
            <input type="file" id="owner_file_dien" accept="image/*" capture="environment" style="display:none;" onchange="handleOwnerScan(this, 'dien')">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('owner_file_dien').click()" style="padding:4px 10px;font-size:12px;background:rgba(247,169,79,.1);color:var(--amber);border-color:rgba(247,169,79,.3);">
              📷 AI đọc công tơ điện
            </button>
          </div>

          <!-- Preview & AI status -->
          <div id="scan_box_dien" style="<?= !empty($anh_dien_init) ? '' : 'display:none;' ?>margin-bottom:10px;">
            <img id="scan_img_dien" src="<?= htmlspecialchars($anh_dien_init) ?>" style="max-height:140px;border-radius:6px;border:1px solid var(--border);display:block;margin-bottom:4px;">
            <div id="scan_status_dien" style="font-size:12px;color:var(--green);">
              <?= !empty($anh_dien_init) ? '✅ Đã nạp ảnh công tơ điện' : '' ?>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số cũ đã ghi nhớ (kWh) <span id="nguon_dien_cu" style="font-size:10px;color:var(--text3);font-weight:400;"></span></label>
              <input class="form-control" type="number" name="chi_so_dien_cu" id="dien_cu"
                     min="0" step="0.1" placeholder="0"
                     value="<?= $_POST['chi_so_dien_cu']??'0' ?>" oninput="calcTotal()"/>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số trên công tơ mới (kWh) <span id="dien_ai_badge" class="badge" style="display:none;background:rgba(34,201,147,.15);color:var(--green);font-size:10px;">AI đã đọc</span></label>
              <input class="form-control" type="number" name="chi_so_dien_moi" id="dien_moi"
                     min="0" step="0.1" placeholder="0"
                     value="<?= htmlspecialchars($dien_moi_init) ?>" oninput="calcTotal()"/>
            </div>
          </div>
          <div style="margin-top:8px;font-size:12px;color:var(--text3)" id="dien_formula_line">
            Tiêu thụ (Số mới − Số cũ): <strong id="kwh" style="color:var(--amber)">0</strong> kWh
            → Thành tiền: <strong id="tien_dien_show" style="color:var(--amber)">0đ</strong>
          </div>
        </div>

        <!-- NƯỚC -->
        <div style="background:var(--bg3);border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div style="font-size:12px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.5px;">💧 Chỉ số nước</div>
            <input type="file" id="owner_file_nuoc" accept="image/*" capture="environment" style="display:none;" onchange="handleOwnerScan(this, 'nuoc')">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('owner_file_nuoc').click()" style="padding:4px 10px;font-size:12px;background:rgba(79,142,247,.1);color:var(--accent);border-color:rgba(79,142,247,.3);">
              📷 AI đọc công tơ nước
            </button>
          </div>

          <!-- Preview & AI status -->
          <div id="scan_box_nuoc" style="<?= !empty($anh_nuoc_init) ? '' : 'display:none;' ?>margin-bottom:10px;">
            <img id="scan_img_nuoc" src="<?= htmlspecialchars($anh_nuoc_init) ?>" style="max-height:140px;border-radius:6px;border:1px solid var(--border);display:block;margin-bottom:4px;">
            <div id="scan_status_nuoc" style="font-size:12px;color:var(--green);">
              <?= !empty($anh_nuoc_init) ? '✅ Đã nạp ảnh công tơ nước' : '' ?>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số cũ đã ghi nhớ (m³) <span id="nguon_nuoc_cu" style="font-size:10px;color:var(--text3);font-weight:400;"></span></label>
              <input class="form-control" type="number" name="chi_so_nuoc_cu" id="nuoc_cu"
                     min="0" step="0.1" placeholder="0"
                     value="<?= $_POST['chi_so_nuoc_cu']??'0' ?>" oninput="calcTotal()"/>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Chỉ số trên công tơ mới (m³) <span id="nuoc_ai_badge" class="badge" style="display:none;background:rgba(34,201,147,.15);color:var(--green);font-size:10px;">AI đã đọc</span></label>
              <input class="form-control" type="number" name="chi_so_nuoc_moi" id="nuoc_moi"
                     min="0" step="0.1" placeholder="0"
                     value="<?= htmlspecialchars($nuoc_moi_init) ?>" oninput="calcTotal()"/>
            </div>
          </div>
          <div style="margin-top:8px;font-size:12px;color:var(--text3)" id="nuoc_formula_line">
            Tiêu thụ (Số mới − Số cũ): <strong id="m3" style="color:var(--accent)">0</strong> m³
            → Thành tiền: <strong id="tien_nuoc_show" style="color:var(--accent)">0đ</strong>
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
let tenantReportData = null;

function loadPhong(khu_id) {
  const sel = document.getElementById('phongSel');
  const url = khu_id
    ? `index.php?controller=hoadon&action=getPhongByKhu&khu_id=${khu_id}`
    : `index.php?controller=hoadon&action=getPhongByKhu`;

  sel.innerHTML = '<option value="">Đang tải...</option>';

  fetch(url)
    .then(r => r.json())
    .then(phongs => {
      sel.innerHTML = '<option value="">' + (phongs.length ? '— Chọn phòng —' : '— Khu này chưa có phòng nào có hợp đồng —') + '</option>';
      phongs.forEach(p => {
        const khuTxt = p.ten_khu ? ` (${p.ten_khu})` : '';
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.dataset.gia = p.gia;
        opt.textContent = `${p.so_phong}${khuTxt} — ${parseInt(p.gia).toLocaleString('vi-VN')}đ`;
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
  if (!opt || !opt.value) {
    tienPhong = 0;
    soXe = 0;
    document.getElementById('dien_cu').value = 0;
    document.getElementById('nuoc_cu').value = 0;
    calcTotal();
    document.getElementById('bcs_banner').style.display = 'none';
    return;
  }
  tienPhong = parseFloat(opt.dataset.gia || 0);
  const thang = document.getElementById('sel_thang').value;
  const nam = document.getElementById('sel_nam').value;

  // Luôn lấy lại chỉ số cũ đã ghi nhớ của phòng đang chọn (loại trừ tháng đang lập)
  fetch(`index.php?controller=hoadon&action=getChiSoCu&phong_id=${opt.value}&thang=${thang}&nam=${nam}`)
    .then(r => r.json())
    .then(d => {
      document.getElementById('dien_cu').value = (d.dien !== undefined ? d.dien : (d.chi_so_dien_moi || 0));
      document.getElementById('nuoc_cu').value = (d.nuoc !== undefined ? d.nuoc : (d.chi_so_nuoc_moi || 0));
      const nguonTxt = d.nguon ? `(${d.nguon})` : '';
      const ndEl = document.getElementById('nguon_dien_cu');
      const nnEl = document.getElementById('nguon_nuoc_cu');
      if (ndEl) ndEl.textContent = nguonTxt;
      if (nnEl) nnEl.textContent = nguonTxt;
      soXe = d.so_xe || 0;
      calcTotal();
    });

  // Kiểm tra báo số từ người thuê
  fetch(`index.php?controller=hoadon&action=getBaoChiSo&phong_id=${opt.value}&thang=${thang}&nam=${nam}`)
    .then(r => r.json())
    .then(res => {
      if (res.found && res.data) {
        tenantReportData = res.data;
        document.getElementById('bcs_info').innerHTML = `Người thuê <strong>${res.data.ten_nguoi_thue}</strong> báo: Điện = <strong>${res.data.chi_so_dien}</strong> kWh, Nước = <strong>${res.data.chi_so_nuoc}</strong> m³`;
        document.getElementById('bcs_banner').style.display = 'block';
      } else {
        tenantReportData = null;
        document.getElementById('bcs_banner').style.display = 'none';
      }
    });
}

function applyTenantReport() {
  if (!tenantReportData) return;
  document.getElementById('dien_moi').value = tenantReportData.chi_so_dien;
  document.getElementById('nuoc_moi').value = tenantReportData.chi_so_nuoc;
  document.getElementById('bao_chi_so_id').value = tenantReportData.id;

  if (tenantReportData.anh_cong_to_dien) {
    document.getElementById('anh_cong_to_dien').value = tenantReportData.anh_cong_to_dien;
    document.getElementById('scan_img_dien').src = tenantReportData.anh_cong_to_dien;
    document.getElementById('scan_box_dien').style.display = 'block';
    document.getElementById('scan_status_dien').innerHTML = '✅ Ảnh công tơ điện từ người thuê';
  }
  if (tenantReportData.anh_cong_to_nuoc) {
    document.getElementById('anh_cong_to_nuoc').value = tenantReportData.anh_cong_to_nuoc;
    document.getElementById('scan_img_nuoc').src = tenantReportData.anh_cong_to_nuoc;
    document.getElementById('scan_box_nuoc').style.display = 'block';
    document.getElementById('scan_status_nuoc').innerHTML = '✅ Ảnh công tơ nước từ người thuê';
  }

  calcTotal();
  alert('Đã áp dụng chỉ số điện nước từ người thuê thành công!');
}

// Hàm hỗ trợ cắt vùng cửa sổ mặt số công tơ để Tesseract đọc chính xác dãy số mà không bị nhiễu bởi chữ 220V, 450 vòng/kWh, No. 08124933
function cropMeterDialToBlob(file) {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      try {
        const canvas = document.createElement('canvas');
        // Vùng cửa sổ mặt số công tơ thường nằm ở trung tâm phía trên (x: 28%..72%, y: 18%..42%)
        const sx = Math.round(img.width * 0.28);
        const sy = Math.round(img.height * 0.18);
        const sw = Math.round(img.width * 0.44);
        const sh = Math.round(img.height * 0.22);
        canvas.width = sw;
        canvas.height = sh;
        const ctx = canvas.getContext('2d');
        ctx.filter = 'contrast(1.4) grayscale(1)';
        ctx.drawImage(img, sx, sy, sw, sh, 0, 0, sw, sh);
        canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.95);
      } catch (e) {
        resolve(null);
      }
    };
    img.onerror = () => resolve(null);
    img.src = URL.createObjectURL(file);
  });
}

async function handleOwnerScan(input, type) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];

  const phongSel = document.getElementById('phongSel');
  const autoCreateChk = document.getElementById('auto_create_invoice');
  if (autoCreateChk && autoCreateChk.checked && !phongSel.value) {
    alert('Vui lòng chọn Phòng trước khi tải ảnh lên để AI tự động tạo hóa đơn!');
    input.value = '';
    phongSel.focus();
    return;
  }

  const scanBox = document.getElementById(`scan_box_${type}`);
  const scanImg = document.getElementById(`scan_img_${type}`);
  const scanStatus = document.getElementById(`scan_status_${type}`);
  const valInput = document.getElementById(`${type}_moi`);
  const cuInput = document.getElementById(`${type}_cu`);
  const pathHidden = document.getElementById(`anh_cong_to_${type}`);
  const badge = document.getElementById(`${type}_ai_badge`);

  // Nếu trước đó đã quét 1 lần (ô Chỉ số mới đã có số > 0), tự động chuyển số đó sang ô Chỉ số cũ đã ghi nhớ!
  const prevMoiVal = parseFloat(valInput.value) || 0;
  if (prevMoiVal > 0) {
    cuInput.value = prevMoiVal;
  }

  scanBox.style.display = 'block';
  scanImg.src = URL.createObjectURL(file);
  scanStatus.innerHTML = `<span style="color:var(--accent);">🤖 AI đang phân tích mặt số công tơ và tính toán (Số mới − Số cũ)...</span>`;

  // 1. Client OCR qua Tesseract (ưu tiên quét vùng cửa sổ mặt số đã cắt + toàn ảnh)
  let ocrClientText = '';
  let ocrDialText = '';
  try {
    if (window.Tesseract) {
      const croppedBlob = await cropMeterDialToBlob(file);
      if (croppedBlob) {
        const resCrop = await Tesseract.recognize(croppedBlob, 'eng');
        ocrDialText = (resCrop && resCrop.data && resCrop.data.text) ? resCrop.data.text : '';
      }
      const resFull = await Tesseract.recognize(file, 'eng');
      ocrClientText = (resFull && resFull.data && resFull.data.text) ? resFull.data.text : '';
    }
  } catch(e) {}

  // 2. Server upload, Vision AI & Auto Create Invoice
  const formData = new FormData();
  formData.append('meter_image', file);
  formData.append(type === 'dien' ? 'anh_dien' : 'anh_nuoc', file);
  formData.append('type', type);
  formData.append('ocr_text', ocrClientText);
  formData.append('ocr_dial_text', ocrDialText);
  formData.append('phong_id', phongSel.value || '0');
  formData.append('thang', document.getElementById('sel_thang').value);
  formData.append('nam', document.getElementById('sel_nam').value);
  formData.append('chi_so_dien_cu', document.getElementById('dien_cu').value || '0');
  formData.append('chi_so_nuoc_cu', document.getElementById('nuoc_cu').value || '0');
  formData.append('chi_so_dien_moi', document.getElementById('dien_moi').value || '');
  formData.append('chi_so_nuoc_moi', document.getElementById('nuoc_moi').value || '');
  formData.append('anh_cong_to_dien', document.getElementById('anh_cong_to_dien').value || '');
  formData.append('anh_cong_to_nuoc', document.getElementById('anh_cong_to_nuoc').value || '');
  formData.append('auto_create_invoice', (autoCreateChk && autoCreateChk.checked) ? '1' : '0');

  fetch('index.php?controller=ai&action=quetChiSoDongHo', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      pathHidden.value = data.file_path;
      const readingVal = (data.chi_so_moi !== undefined && data.chi_so_moi !== null) ? parseFloat(data.chi_so_moi) : parseFloat(data.reading);

      // Cập nhật từ kết quả hóa đơn server (nếu đã tự động sắp xếp Số mới - Số cũ)
      if (data.invoice && data.invoice.ok && data.invoice.data) {
        const inv = data.invoice.data;
        document.getElementById('dien_cu').value = inv.chi_so_dien_cu;
        document.getElementById('dien_moi').value = inv.chi_so_dien_moi;
        document.getElementById('nuoc_cu').value = inv.chi_so_nuoc_cu;
        document.getElementById('nuoc_moi').value = inv.chi_so_nuoc_moi;
      } else if (!isNaN(readingVal) && readingVal > 0) {
        const curCuBefore = parseFloat(cuInput.value) || 0;
        if (curCuBefore > 0 && readingVal < curCuBefore) {
          // Nếu quét ảnh có số nhỏ hơn số vừa quét trước đó -> đặt số nhỏ làm Số cũ, số lớn làm Số mới
          cuInput.value = readingVal;
          valInput.value = curCuBefore;
        } else {
          valInput.value = readingVal;
        }
      }

      const finalCu  = parseFloat(cuInput.value) || 0;
      const finalMoi = parseFloat(valInput.value) || 0;
      const usedVal  = Math.max(0, finalMoi - finalCu).toFixed(1);
      const unit     = type === 'dien' ? 'kWh' : 'm³';
      const donGia   = type === 'dien' ? GIA_DIEN : GIA_NUOC;
      const thanhTien = Math.round(parseFloat(usedVal) * donGia).toLocaleString('vi-VN');

      if (finalMoi > 0) {
        badge.style.display = 'inline-block';
        scanStatus.innerHTML = `<span style="color:var(--green);font-weight:700;">✅ AI đã đọc công tơ: <strong>${finalMoi} ${unit}</strong> − Số cũ đã nhớ: <strong>${finalCu} ${unit}</strong> = Tiêu thụ: <strong>${usedVal} ${unit}</strong> (Thành tiền: <strong>${thanhTien}đ</strong>)</span>`;
      }

      // Hiển thị banner hóa đơn tự động tạo/cập nhật
      if (data.invoice && data.invoice.ok) {
        const banner = document.getElementById('auto_invoice_banner');
        const titleEl = document.getElementById('auto_invoice_title');
        const descEl = document.getElementById('auto_invoice_desc');
        const linkEl = document.getElementById('auto_invoice_link');

        banner.style.display = 'block';
        titleEl.innerHTML = data.invoice.action === 'created'
          ? '🎉 AI đã tự động TẠO HÓA ĐƠN điện nước thành công!'
          : '🔄 AI đã tự động CẬP NHẬT HÓA ĐƠN điện nước thành công!';
        descEl.innerHTML = data.invoice.message;
        linkEl.href = `index.php?controller=hoadon&action=chiTiet&id=${data.invoice.hoa_don_id}`;
      } else if (data.invoice && !data.invoice.ok) {
        scanStatus.innerHTML += `<div style="color:var(--amber);margin-top:4px;">ℹ️ ${data.invoice.message}</div>`;
      }

      calcTotal();
      // Reset input file để có thể chọn tiếp ảnh thứ 2 ngay lập tức
      input.value = '';
    } else {
      scanStatus.innerHTML = `<span style="color:var(--red);">❌ ${data.message || 'Lỗi quét'}</span>`;
      input.value = '';
    }
  })
  .catch(() => {
    scanStatus.innerHTML = `<span style="color:var(--amber);">⚠️ Đã lưu ảnh. Vui lòng nhập số tương ứng.</span>`;
    input.value = '';
  });
}

function calcTotal() {
  const dc = parseFloat(document.getElementById('dien_cu').value) || 0;
  const dm = parseFloat(document.getElementById('dien_moi').value) || 0;
  const nc = parseFloat(document.getElementById('nuoc_cu').value) || 0;
  const nm = parseFloat(document.getElementById('nuoc_moi').value) || 0;

  const kwh = Math.max(0, dm - dc);
  const m3  = Math.max(0, nm - nc);

  const tDien = kwh * GIA_DIEN;
  const tNuoc = m3  * GIA_NUOC;
  const phiXe = soXe * PHI_XE_1;
  const tong  = tienPhong + tDien + tNuoc + PHI_DV + phiXe;

  document.getElementById('dien_formula_line').innerHTML =
    `Công thức: <strong>${dm}</strong> (số mới) − <strong>${dc}</strong> (số cũ) = <strong id="kwh" style="color:var(--amber)">${kwh.toFixed(1)}</strong> kWh × ${GIA_DIEN.toLocaleString('vi-VN')}đ = <strong id="tien_dien_show" style="color:var(--amber)">${Math.round(tDien).toLocaleString('vi-VN')}đ</strong>`;

  document.getElementById('nuoc_formula_line').innerHTML =
    `Công thức: <strong>${nm}</strong> (số mới) − <strong>${nc}</strong> (số cũ) = <strong id="m3" style="color:var(--accent)">${m3.toFixed(1)}</strong> m³ × ${GIA_NUOC.toLocaleString('vi-VN')}đ = <strong id="tien_nuoc_show" style="color:var(--accent)">${Math.round(tNuoc).toLocaleString('vi-VN')}đ</strong>`;

  document.getElementById('tongShow').textContent = Math.round(tong).toLocaleString('vi-VN') + 'đ';
  document.getElementById('tongDetail').innerHTML =
    `Phòng: <strong>${tienPhong.toLocaleString('vi-VN')}đ</strong> + ` +
    `Điện (${kwh.toFixed(1)} kWh): <strong>${Math.round(tDien).toLocaleString('vi-VN')}đ</strong> + ` +
    `Nước (${m3.toFixed(1)} m³): <strong>${Math.round(tNuoc).toLocaleString('vi-VN')}đ</strong> + ` +
    `DV: <strong>${PHI_DV.toLocaleString('vi-VN')}đ</strong>` +
    (phiXe > 0 ? ` + Xe (${soXe} chiếc): <strong>${phiXe.toLocaleString('vi-VN')}đ</strong>` : '');
}

function validateForm() {
  const sel = document.getElementById('phongSel');
  if (!sel.value) {
    alert('Vui lòng chọn phòng!');
    return false;
  }
  return true;
}

// Khởi chạy khi tải trang nếu đã có phòng được chọn
window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('phongSel').value) {
    autoFill();
  }
});
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
