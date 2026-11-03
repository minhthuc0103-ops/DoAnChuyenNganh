<?php
$title = 'Báo chỉ số điện nước (AI)';
require_once 'frontend/views/Layouts/header.php';
?>

<!-- Nạp Tesseract.js qua CDN để hỗ trợ AI OCR client mượt mà không cần API key -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<div class="page-header">
  <div class="page-title">
    <h1>⚡ Báo chỉ số điện nước định kỳ</h1>
    <p>Người ở tự chụp ảnh công tơ đồng hồ để AI nhận diện chỉ số và gửi cho chủ trọ</p>
  </div>
  <a href="index.php?controller=user&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<?php if(!empty($error)): ?>
  <div class="msg-alert msg-error" style="margin-bottom:18px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if(!empty($success)): ?>
  <div class="msg-alert msg-success" style="margin-bottom:18px;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if(!$phong): ?>
  <div class="card" style="padding:40px;text-align:center;">
    <div style="font-size:48px;margin-bottom:12px;">🏠</div>
    <h3>Bạn chưa được gán vào phòng trọ nào</h3>
    <p style="color:var(--text3);">Vui lòng liên hệ chủ trọ để được kích hoạt hợp đồng phòng.</p>
  </div>
<?php else: ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;" class="form-grid-resp">
  
  <!-- FORM BÁO SỐ -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Gửi số điện nước tháng này</div>
      <span class="badge" style="background:rgba(79,142,247,.15);color:var(--accent);">Phòng <?= htmlspecialchars($phong['so_phong']) ?></span>
    </div>
    <div class="card-body">
      <form method="POST" action="index.php?controller=user&action=baoChiSo" id="meterForm">
        <?= Csrf::field() ?>

        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label class="form-label">Tháng</label>
            <select name="thang" class="form-control" id="sel_thang">
              <?php for($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= $m==(int)date('m')?'selected':'' ?>>Tháng <?= $m ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Năm</label>
            <input type="number" name="nam" class="form-control" value="<?= date('Y') ?>" readonly>
          </div>
        </div>

        <!-- KHỐI ĐIỆN -->
        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:18px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-weight:700;color:var(--amber);font-size:14px;text-transform:uppercase;">
              ⚡ Công tơ Điện (kWh)
            </div>
            <?php if($lastHd): ?>
            <span style="font-size:12px;color:var(--text3);">Số cũ: <strong><?= (float)$lastHd['chi_so_dien_moi'] ?></strong> kWh</span>
            <?php endif; ?>
          </div>

          <!-- UPLOAD / CHỤP ẢNH ĐIỆN -->
          <div style="margin-bottom:12px;">
            <input type="file" id="input_file_dien" accept="image/*" capture="environment" style="display:none;" onchange="handleImageUpload(this, 'dien')">
            <input type="hidden" name="anh_cong_to_dien" id="path_dien" value="">

            <div style="display:flex;gap:10px;align-items:center;">
              <button type="button" class="btn btn-outline" onclick="document.getElementById('input_file_dien').click()" style="flex:1;background:rgba(247,169,79,.08);border-color:rgba(247,169,79,.3);color:var(--amber);">
                📷 Chụp / Tải ảnh công tơ điện
              </button>
            </div>
          </div>

          <!-- PREVIEW & SCAN STATUS -->
          <div id="preview_box_dien" style="display:none;margin-bottom:12px;position:relative;">
            <img id="img_preview_dien" src="" alt="Công tơ điện" style="width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:1px solid var(--border);background:#000;">
            <div id="scan_status_dien" style="margin-top:6px;font-size:12px;padding:6px 10px;border-radius:6px;background:var(--bg);color:var(--text2);display:flex;align-items:center;gap:6px;">
              <span>Đã tải ảnh lên</span>
            </div>
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Chỉ số điện nhận diện (kWh) <span style="color:var(--red)">*</span></label>
            <input type="number" step="0.1" name="chi_so_dien" id="val_dien" class="form-control" placeholder="Nhập hoặc chụp ảnh cho AI đọc" required style="font-size:18px;font-weight:700;color:var(--amber);">
          </div>
        </div>

        <!-- KHỐI NƯỚC -->
        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:18px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-weight:700;color:var(--accent);font-size:14px;text-transform:uppercase;">
              💧 Công tơ Nước (m³)
            </div>
            <?php if($lastHd): ?>
            <span style="font-size:12px;color:var(--text3);">Số cũ: <strong><?= (float)$lastHd['chi_so_nuoc_moi'] ?></strong> m³</span>
            <?php endif; ?>
          </div>

          <!-- UPLOAD / CHỤP ẢNH NƯỚC -->
          <div style="margin-bottom:12px;">
            <input type="file" id="input_file_nuoc" accept="image/*" capture="environment" style="display:none;" onchange="handleImageUpload(this, 'nuoc')">
            <input type="hidden" name="anh_cong_to_nuoc" id="path_nuoc" value="">

            <div style="display:flex;gap:10px;align-items:center;">
              <button type="button" class="btn btn-outline" onclick="document.getElementById('input_file_nuoc').click()" style="flex:1;background:rgba(79,142,247,.08);border-color:rgba(79,142,247,.3);color:var(--accent);">
                📷 Chụp / Tải ảnh công tơ nước
              </button>
            </div>
          </div>

          <!-- PREVIEW & SCAN STATUS -->
          <div id="preview_box_nuoc" style="display:none;margin-bottom:12px;position:relative;">
            <img id="img_preview_nuoc" src="" alt="Công tơ nước" style="width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:1px solid var(--border);background:#000;">
            <div id="scan_status_nuoc" style="margin-top:6px;font-size:12px;padding:6px 10px;border-radius:6px;background:var(--bg);color:var(--text2);display:flex;align-items:center;gap:6px;">
              <span>Đã tải ảnh lên</span>
            </div>
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Chỉ số nước nhận diện (m³) <span style="color:var(--red)">*</span></label>
            <input type="number" step="0.1" name="chi_so_nuoc" id="val_nuoc" class="form-control" placeholder="Nhập hoặc chụp ảnh cho AI đọc" required style="font-size:18px;font-weight:700;color:var(--accent);">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Ghi chú gửi chủ trọ (nếu có)</label>
          <textarea name="ghi_chu" class="form-control" rows="2" placeholder="Ví dụ: Công tơ nước phòng em mới thay ngày 15..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:15px;font-weight:700;">
          🚀 Gửi chỉ số cho chủ trọ
        </button>
      </form>
    </div>
  </div>

  <!-- LỊCH SỬ GỬI SỐ ĐIỆN NƯỚC -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Lịch sử đã gửi</div>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if(empty($history)): ?>
        <div style="padding:40px;text-align:center;color:var(--text3);">
          <div style="font-size:32px;margin-bottom:8px;">📊</div>
          Chưa có lượt gửi số điện nước nào trước đây.
        </div>
      <?php else: ?>
        <table class="table" style="width:100%;margin-bottom:0;">
          <thead>
            <tr>
              <th>Tháng</th>
              <th>Điện (kWh)</th>
              <th>Nước (m³)</th>
              <th>Ảnh chụp</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($history as $h): ?>
            <tr>
              <td>
                <strong><?= $h['thang'] ?>/<?= $h['nam'] ?></strong>
                <div style="font-size:11px;color:var(--text3);"><?= date('d/m/Y', strtotime($h['created_at'])) ?></div>
              </td>
              <td><strong style="color:var(--amber)"><?= (float)$h['chi_so_dien'] ?></strong></td>
              <td><strong style="color:var(--accent)"><?= (float)$h['chi_so_nuoc'] ?></strong></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <?php if($h['anh_cong_to_dien']): ?>
                  <a href="<?= htmlspecialchars($h['anh_cong_to_dien']) ?>" target="_blank" title="Xem ảnh công tơ điện" style="font-size:16px;">⚡</a>
                  <?php endif; ?>
                  <?php if($h['anh_cong_to_nuoc']): ?>
                  <a href="<?= htmlspecialchars($h['anh_cong_to_nuoc']) ?>" target="_blank" title="Xem ảnh công tơ nước" style="font-size:16px;">💧</a>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <?php if($h['trang_thai'] === 'cho_duyet'): ?>
                  <span class="badge" style="background:rgba(247,169,79,.15);color:var(--amber);">⏳ Chờ duyệt</span>
                <?php elseif($h['trang_thai'] === 'da_duyet'): ?>
                  <span class="badge" style="background:rgba(34,201,147,.15);color:var(--green);">✅ Đã lập HĐ</span>
                <?php else: ?>
                  <span class="badge" style="background:rgba(239,68,68,.15);color:var(--red);">❌ Từ chối</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php endif; ?>

<script>
// Xử lý Upload & AI OCR Nhận diện số công tơ
async function handleImageUpload(input, type) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  
  // Hiển thị preview
  const previewBox = document.getElementById(`preview_box_${type}`);
  const imgElem = document.getElementById(`img_preview_${type}`);
  const statusElem = document.getElementById(`scan_status_${type}`);
  const valInput = document.getElementById(`val_${type}`);
  const pathInput = document.getElementById(`path_${type}`);

  previewBox.style.display = 'block';
  imgElem.src = URL.createObjectURL(file);
  statusElem.innerHTML = `<span style="color:var(--accent);">🤖 AI đang phân tích mặt số công tơ...</span>`;

  // 1. Chạy OCR Client bằng Tesseract.js (nhanh, offline)
  let ocrClientText = '';
  try {
    if (window.Tesseract) {
      statusElem.innerHTML = `<span style="color:var(--accent);">🔍 Đang quét số qua AI Vision...</span>`;
      const res = await Tesseract.recognize(file, 'eng', {
        logger: m => {
          if (m.status === 'recognizing text') {
            statusElem.innerHTML = `<span>⏳ AI đang đọc số: ${Math.round(m.progress * 100)}%</span>`;
          }
        }
      });
      ocrClientText = res.data.text || '';
    }
  } catch(err) {
    console.warn('Tesseract fallback:', err);
  }

  // 2. Gửi ảnh lên server lưu trữ và kết hợp Gemini Vision AI / regex server
  const formData = new FormData();
  formData.append('meter_image', file);
  formData.append('type', type);
  formData.append('ocr_text', ocrClientText);

  fetch('index.php?controller=ai&action=readMeter', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      pathInput.value = data.file_path;
      if (data.reading !== null && data.reading > 0) {
        valInput.value = data.reading;
        statusElem.innerHTML = `<span style="color:var(--green);font-weight:700;">✅ AI đã đọc thành công: ${data.reading} ${type==='dien'?'kWh':'m³'}</span>`;
      } else {
        // Nếu AI chưa tự tin 100%, thử bóc tách số từ text client
        const nums = ocrClientText.match(/\b\d{3,6}\b/);
        if (nums && nums[0]) {
          valInput.value = parseFloat(nums[0]);
          statusElem.innerHTML = `<span style="color:var(--green);font-weight:700;">✅ Nhận diện được số: ${nums[0]} (vui lòng kiểm tra lại)</span>`;
        } else {
          statusElem.innerHTML = `<span style="color:var(--amber);">⚠️ Đã lưu ảnh. Vui lòng nhìn ảnh phía trên và tự nhập chỉ số nếu AI chưa đọc rõ.</span>`;
        }
      }
    } else {
      statusElem.innerHTML = `<span style="color:var(--red);">❌ ${data.message || 'Lỗi xử lý ảnh'}</span>`;
    }
  })
  .catch(err => {
    statusElem.innerHTML = `<span style="color:var(--amber);">⚠️ Đã lưu ảnh xem trước. Vui lòng nhập số tương ứng.</span>`;
  });
}
</script>

<style>
@media (max-width: 768px) {
  .form-grid-resp {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

