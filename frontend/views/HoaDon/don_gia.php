<?php
$title = 'Cài đặt đơn giá';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Cài đặt đơn giá</h1>
    <p>Giá điện và nước dùng để tính hóa đơn hàng tháng</p>
  </div>
</div>

<?php if(!empty($msg)): ?>
<div class="msg-alert msg-success" style="margin-bottom:18px;">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;max-width:860px;">

  <!-- CARD GIÁ HIỆN TẠI -->
  <div style="display:flex;flex-direction:column;gap:14px;">

    <!-- Stat điện -->
    <div class="card" style="padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:40px;height:40px;border-radius:11px;background:rgba(247,169,79,.15);display:flex;align-items:center;justify-content:center;font-size:20px;">⚡</div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--text);">Giá điện</div>
            <div style="font-size:11px;color:var(--text3);">đ / kWh</div>
          </div>
        </div>
        <div style="font-size:26px;font-weight:800;color:var(--amber);"><?= number_format((int)($donGia['gia_dien']??3500)) ?>đ</div>
      </div>
      <div style="height:4px;border-radius:2px;background:rgba(255,255,255,.06);">
        <div style="height:100%;width:70%;border-radius:2px;background:linear-gradient(90deg,var(--amber),#f75c5c);"></div>
      </div>
      <div style="font-size:11px;color:var(--text3);margin-top:6px;">Thị trường: 3.500 – 4.000đ/kWh</div>
    </div>

    <!-- Stat phí dịch vụ -->
    <div class="card" style="padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:40px;height:40px;border-radius:11px;background:rgba(34,201,147,.15);display:flex;align-items:center;justify-content:center;font-size:20px;">🛎</div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--text);">Phí dịch vụ</div>
            <div style="font-size:11px;color:var(--text3);">đ / phòng / tháng</div>
          </div>
        </div>
        <div style="font-size:26px;font-weight:800;color:var(--green);"><?= number_format((int)($donGia['phi_dv']??150000)) ?>đ</div>
      </div>
      <div style="font-size:11px;color:var(--text3);">Bao gồm: Wifi · Rác · Vệ sinh · Bảo vệ</div>
    </div>

    <!-- Stat nước -->
    <div class="card" style="padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:40px;height:40px;border-radius:11px;background:rgba(79,142,247,.15);display:flex;align-items:center;justify-content:center;font-size:20px;">💧</div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--text);">Giá nước</div>
            <div style="font-size:11px;color:var(--text3);">đ / m³</div>
          </div>
        </div>
        <div style="font-size:26px;font-weight:800;color:var(--accent);"><?= number_format((int)($donGia['gia_nuoc']??15000)) ?>đ</div>
      </div>
      <div style="height:4px;border-radius:2px;background:rgba(255,255,255,.06);">
        <div style="height:100%;width:50%;border-radius:2px;background:linear-gradient(90deg,var(--accent),#2dd4bf);"></div>
      </div>
      <div style="font-size:11px;color:var(--text3);margin-top:6px;">Thị trường: 10.000 – 20.000đ/m³</div>
    </div>

    <!-- Ví dụ tính tiền -->
    <div class="card" style="padding:18px;">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">🧮 Ví dụ hóa đơn</div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--bg3);border-radius:8px;">
          <span style="font-size:12px;color:var(--text2);">⚡ 50 kWh điện</span>
          <strong style="font-size:13px;color:var(--amber);" id="ex_dien"><?= number_format(50 * (int)($donGia['gia_dien']??3500)) ?>đ</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--bg3);border-radius:8px;">
          <span style="font-size:12px;color:var(--text2);">💧 5 m³ nước</span>
          <strong style="font-size:13px;color:var(--accent);" id="ex_nuoc"><?= number_format(5 * (int)($donGia['gia_nuoc']??15000)) ?>đ</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:rgba(79,142,247,.06);border:1px solid rgba(79,142,247,.15);border-radius:8px;">
          <span style="font-size:12px;font-weight:700;color:var(--text);">Tổng điện + nước</span>
          <strong style="font-size:14px;color:var(--accent);" id="ex_tong"><?= number_format(50 * (int)($donGia['gia_dien']??3500) + 5 * (int)($donGia['gia_nuoc']??15000)) ?>đ</strong>
        </div>
      </div>
    </div>

  </div>

  <!-- FORM CHỈNH SỬA -->
  <div>
    <div class="card">
      <div class="card-header">
        <div class="card-title">✏️ Chỉnh sửa đơn giá</div>
      </div>
      <div class="card-body">
        <form method="POST" action="index.php?controller=dongia&action=index" id="donGiaForm">
            <?= Csrf::field() ?>

          <div class="form-group">
            <label class="form-label">⚡ Giá điện (đ / kWh)</label>
            <div style="position:relative;">
              <input class="form-control no-vnd" type="text" inputmode="numeric" name="gia_dien" id="inp_dien"
                     value="<?= number_format((int)($donGia['gia_dien']??3500)) ?>"
                     oninput="onDienInput(this)" required
                     style="padding-right:60px;"/>
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text3);font-weight:600;">đ/kWh</span>
            </div>
            <div style="font-size:11px;color:var(--text3);margin-top:5px;">Thông thường: 3.500 – 4.000đ/kWh</div>
          </div>

          <!-- Slider điện -->
          <div style="margin-bottom:18px;">
            <input type="range" id="sl_dien" min="1000" max="8000" step="100"
                   value="<?= (int)($donGia['gia_dien']??3500) ?>"
                   oninput="syncSlider('dien',this.value)"
                   style="width:100%;accent-color:var(--amber);cursor:pointer;"/>
            <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text3);">
              <span>1.000đ</span><span>8.000đ</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">💧 Giá nước (đ / m³)</label>
            <div style="position:relative;">
              <input class="form-control no-vnd" type="text" inputmode="numeric" name="gia_nuoc" id="inp_nuoc"
                     value="<?= number_format((int)($donGia['gia_nuoc']??15000)) ?>"
                     oninput="onNuocInput(this)" required
                     style="padding-right:50px;"/>
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text3);font-weight:600;">đ/m³</span>
            </div>
            <div style="font-size:11px;color:var(--text3);margin-top:5px;">Thông thường: 10.000 – 20.000đ/m³</div>
          </div>

          <!-- Slider nước -->
          <div style="margin-bottom:18px;">
            <input type="range" id="sl_nuoc" min="5000" max="40000" step="500"
                   value="<?= (int)($donGia['gia_nuoc']??15000) ?>"
                   oninput="syncSlider('nuoc',this.value)"
                   style="width:100%;accent-color:var(--accent);cursor:pointer;"/>
            <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text3);">
              <span>5.000đ</span><span>40.000đ</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">🛎 Phí dịch vụ (đ / phòng / tháng)</label>
            <div style="position:relative;">
              <input class="form-control no-vnd" type="text" inputmode="numeric" name="phi_dv" id="inp_dv"
                     value="<?= number_format((int)($donGia['phi_dv']??150000)) ?>"
                     oninput="onDvInput(this)" required
                     style="padding-right:50px;"/>
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text3);font-weight:600;">đ</span>
            </div>
            <div style="font-size:11px;color:var(--text3);margin-top:5px;">Wifi · Rác · Vệ sinh · Bảo vệ (cố định/phòng)</div>
          </div>

          <!-- Cảnh báo nếu giá quá cao -->
          <div id="warnBox" style="display:none;padding:10px 14px;background:rgba(247,92,92,.08);border:1px solid rgba(247,92,92,.2);border-radius:10px;margin-bottom:16px;">
            <div style="font-size:12px;color:var(--red);">⚠ Giá điều chỉnh cao hơn mức thông thường, vui lòng kiểm tra lại.</div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;">
            💾 Lưu đơn giá mới
          </button>
        </form>
      </div>
    </div>

    <!-- Ghi chú -->
    <div style="margin-top:14px;padding:14px 16px;background:rgba(79,142,247,.06);border:1px solid rgba(79,142,247,.15);border-radius:12px;">
      <div style="font-size:12px;color:var(--text2);line-height:1.8;">
        💡 <strong>Lưu ý:</strong> Thay đổi đơn giá sẽ áp dụng cho <strong>hóa đơn tạo mới</strong>. Hóa đơn cũ đã tạo không bị ảnh hưởng.
      </div>
    </div>
  </div>

</div>

<script src="frontend/assets/js/hoadon-don-gia.js"></script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>