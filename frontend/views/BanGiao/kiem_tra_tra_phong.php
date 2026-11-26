<?php
$title = 'Kiểm tra & Quyết toán trả phòng #' . $yc['id'];
require_once 'frontend/views/Layouts/header.php';

$tienCocGoc = (float)($yc['tien_coc_goc'] > 0 ? $yc['tien_coc_goc'] : $yc['tien_coc']);
$giaDien = (float)($yc['don_gia_dien'] > 0 ? $yc['don_gia_dien'] : $donGia['gia_dien']);
$giaNuoc = (float)($yc['don_gia_nuoc'] > 0 ? $yc['don_gia_nuoc'] : $donGia['gia_nuoc']);
?>

<div class="page-header">
  <div class="page-title">
    <h1>🔍 Kiểm tra & Quyết toán trả phòng</h1>
    <p>Phòng <strong><?= htmlspecialchars($yc['so_phong']) ?></strong> · Khách thuê: <strong><?= htmlspecialchars($yc['ten_nguoi_thue']) ?></strong> (HĐ #<?= $yc['hop_dong_id'] ?>)</p>
  </div>
  <a href="index.php?controller=bangiao&action=index" class="btn btn-outline">
    ← Quay lại danh sách
  </a>
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="msg-alert msg-success" style="margin-bottom:14px;">
  ✓ Đã lưu kết quả kiểm tra thành công.
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

  <!-- FORM TÍNH TOÁN QUYẾT TOÁN -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📝 Bảng kiểm tra chỉ số & Khấu trừ chi phí</div>
      <span class="pill <?= $yc['trang_thai'] === 'da_thanh_ly' ? 'p-green' : 'p-amber' ?>">
        <?= $yc['trang_thai'] === 'da_thanh_ly' ? 'Đã thanh lý' : 'Đang xử lý quyết toán' ?>
      </span>
    </div>
    <div class="card-body">
      <form method="POST" action="index.php?controller=bangiao&action=kiemTraTraPhong&id=<?=$yc['id']?>" enctype="multipart/form-data" id="formQuyetToan">
        <?= Csrf::field() ?>
        <input type="hidden" name="action_type" id="inputActionType" value="luu_nhap"/>

        <!-- 1. TIỀN ĐIỆN PHÁT SINH -->
        <div style="background:var(--bg3);border-radius:10px;padding:16px;margin-bottom:16px;border:1px solid var(--border);">
          <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:10px;display:flex;align-items:center;gap:6px;">
            <span>⚡ Quyết toán tiền điện</span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chỉ số đầu (kWh)</label>
              <input type="number" name="chi_so_dien_dau" id="dienDau" class="form-control"
                     value="<?= (int)$yc['chi_so_dien_dau'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chỉ số cuối (kWh)</label>
              <input type="number" name="chi_so_dien_cuoi" id="dienCuoi" class="form-control"
                     value="<?= (int)$yc['chi_so_dien_cuoi'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Đơn giá điện (đ/kWh)</label>
              <input type="number" name="don_gia_dien" id="giaDien" class="form-control"
                     value="<?= $giaDien ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;font-size:13px;font-weight:600;color:var(--text2);">
            <span>Tiêu thụ: <strong id="lblSoDien">0</strong> kWh</span>
            <span>Thành tiền điện: <strong id="lblTienDien" style="color:var(--red);">0đ</strong></span>
          </div>
        </div>

        <!-- 2. TIỀN NƯỚC PHÁT SINH -->
        <div style="background:var(--bg3);border-radius:10px;padding:16px;margin-bottom:16px;border:1px solid var(--border);">
          <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:10px;display:flex;align-items:center;gap:6px;">
            <span>💧 Quyết toán tiền nước</span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chỉ số đầu (m³)</label>
              <input type="number" name="chi_so_nuoc_dau" id="nuocDau" class="form-control"
                     value="<?= (int)$yc['chi_so_nuoc_dau'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chỉ số cuối (m³)</label>
              <input type="number" name="chi_so_nuoc_cuoi" id="nuocCuoi" class="form-control"
                     value="<?= (int)$yc['chi_so_nuoc_cuoi'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Đơn giá nước (đ/m³)</label>
              <input type="number" name="don_gia_nuoc" id="giaNuoc" class="form-control"
                     value="<?= $giaNuoc ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;font-size:13px;font-weight:600;color:var(--text2);">
            <span>Tiêu thụ: <strong id="lblSoNuoc">0</strong> m³</span>
            <span>Thành tiền nước: <strong id="lblTienNuoc" style="color:var(--red);">0đ</strong></span>
          </div>
        </div>

        <!-- 3. HƯ HỎNG & KHẤU TRỪ VẬT CHẤT -->
        <div style="background:var(--bg3);border-radius:10px;padding:16px;margin-bottom:16px;border:1px solid var(--border);">
          <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:10px;">
            🛠 Khấu trừ hư hỏng thiết bị & Phí vệ sinh
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:10px;">
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chi phí hư hỏng / mất đồ (đ)</label>
              <input type="number" name="chi_phi_hu_hong" id="chiPhiHuHong" class="form-control"
                     value="<?= (float)$yc['chi_phi_hu_hong'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chi tiết đồ hư hỏng (nếu có)</label>
              <input type="text" name="chi_tiet_hu_hong" class="form-control"
                     value="<?= htmlspecialchars($yc['chi_tiet_hu_hong'] ?? '') ?>"
                     placeholder="vd: Vỡ kính cửa sổ, mất remote điều hòa..."/>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Phí vệ sinh phòng (đ)</label>
              <input type="number" name="phi_ve_sinh" id="phiVeSinh" class="form-control"
                     value="<?= (float)$yc['phi_ve_sinh'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:11px;">Chi phí khác (nợ cũ / phạt...) (đ)</label>
              <input type="number" name="chi_phi_khac" id="chiPhiKhac" class="form-control"
                     value="<?= (float)$yc['chi_phi_khac'] ?>" min="0" oninput="tinhQuyetToan()"/>
            </div>
          </div>
        </div>

        <!-- 4. ẢNH TRẢ PHÒNG & GHI CHÚ -->
        <div class="form-group">
          <label class="form-label">📷 Chụp / Tải ảnh hiện trạng khi trả phòng</label>
          <input type="file" name="anh_tra_phong" class="form-control" accept="image/*"/>
          <?php if (!empty($yc['anh_tra_phong']) && file_exists($yc['anh_tra_phong'])): ?>
          <div style="margin-top:8px;">
            <img src="<?= htmlspecialchars($yc['anh_tra_phong']) ?>" alt="Ảnh trả phòng" style="max-height:120px;border-radius:8px;"/>
          </div>
          <?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label">Ghi chú phản hồi của quản lý</label>
          <textarea class="form-control" name="ghi_chu_ql" rows="2"
                    placeholder="Ghi chú xác nhận về tình trạng phòng và hoàn cọc..."><?= htmlspecialchars($yc['ghi_chu_ql'] ?? '') ?></textarea>
        </div>

        <!-- NÚT HÀNH ĐỘNG -->
        <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid var(--border);padding-top:16px;">
          <button type="button" class="btn btn-outline" onclick="submitQuyetToan('luu_nhap')">
            💾 Lưu bản nháp
          </button>
          <button type="button" class="btn btn-primary" onclick="submitQuyetToan('hoan_tat')" style="font-weight:700;">
            ✅ Xác nhận hoàn tất thanh lý & Trả phòng
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- CỘT TỔNG KẾT QUYẾT TOÁN CỌC -->
  <div style="display:flex;flex-direction:column;gap:16px;">
    <div class="card" style="border:1.5px solid var(--accent);background:var(--bg2);">
      <div class="card-header" style="background:rgba(79,142,247,.08);">
        <div class="card-title" style="color:var(--accent);">💰 BẢNG TỔNG KẾT QUYẾT TOÁN</div>
      </div>
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px;">
          <span>Tiền cọc giữ phòng:</span>
          <strong id="lblTienCoc"><?= number_format($tienCocGoc) ?>đ</strong>
          <input type="hidden" name="tien_coc_goc" id="inputTienCoc" value="<?= $tienCocGoc ?>"/>
        </div>

        <div style="border-top:1px dashed var(--border);padding-top:10px;margin-bottom:10px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--red);">
            <span>- Tiền điện:</span>
            <span id="summaryTienDien">0đ</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--red);">
            <span>- Tiền nước:</span>
            <span id="summaryTienNuoc">0đ</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--red);">
            <span>- Hư hỏng thiết bị:</span>
            <span id="summaryHuHong">0đ</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--red);">
            <span>- Phí dọn vệ sinh:</span>
            <span id="summaryVeSinh">0đ</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--red);">
            <span>- Chi phí khác:</span>
            <span id="summaryChiPhiKhac">0đ</span>
          </div>
        </div>

        <div style="border-top:2px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:4px;">
          <div style="font-size:12px;color:var(--text3);font-weight:700;text-transform:uppercase;">
            Số tiền thực tế hoàn trả khách:
          </div>
          <div id="lblTienHoanLai" style="font-size:24px;font-weight:800;color:var(--green);">
            0đ
          </div>
        </div>
      </div>
    </div>

    <!-- THÔNG TIN NGÂN HÀNG HOÀN TIỀN -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">🏦 Thông tin nhận hoàn cọc của khách</div>
      </div>
      <div class="card-body" style="font-size:13px;line-height:1.6;">
        <div>Ngân hàng: <strong><?= htmlspecialchars($yc['ngan_hang'] ?: 'Chưa nhập') ?></strong></div>
        <div>Số tài khoản: <strong style="color:var(--accent);"><?= htmlspecialchars($yc['so_tai_khoan'] ?: 'Chưa nhập') ?></strong></div>
        <div>Chủ tài khoản: <strong><?= htmlspecialchars($yc['ten_chu_tk'] ?: $yc['ten_nguoi_thue']) ?></strong></div>
        <div style="margin-top:6px;font-size:11px;color:var(--text3);">
          Lý do xin trả phòng: <em>"<?= htmlspecialchars($yc['ly_do']) ?>"</em>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function tinhQuyetToan() {
  const cocGoc = parseFloat(document.getElementById('inputTienCoc').value) || 0;

  const dienDau = parseFloat(document.getElementById('dienDau').value) || 0;
  const dienCuoi = parseFloat(document.getElementById('dienCuoi').value) || 0;
  const giaDien = parseFloat(document.getElementById('giaDien').value) || 0;
  const soDien = Math.max(0, dienCuoi - dienDau);
  const tienDien = soDien * giaDien;

  const nuocDau = parseFloat(document.getElementById('nuocDau').value) || 0;
  const nuocCuoi = parseFloat(document.getElementById('nuocCuoi').value) || 0;
  const giaNuoc = parseFloat(document.getElementById('giaNuoc').value) || 0;
  const soNuoc = Math.max(0, nuocCuoi - nuocDau);
  const tienNuoc = soNuoc * giaNuoc;

  const huHong = parseFloat(document.getElementById('chiPhiHuHong').value) || 0;
  const veSinh = parseFloat(document.getElementById('phiVeSinh').value) || 0;
  const chiPhiKhac = parseFloat(document.getElementById('chiPhiKhac').value) || 0;

  const tongKhauTru = tienDien + tienNuoc + huHong + veSinh + chiPhiKhac;
  const tienHoanLai = Math.max(0, cocGoc - tongKhauTru);

  // Hiển thị
  document.getElementById('lblSoDien').textContent = soDien.toLocaleString();
  document.getElementById('lblTienDien').textContent = tienDien.toLocaleString() + 'đ';
  document.getElementById('lblSoNuoc').textContent = soNuoc.toLocaleString();
  document.getElementById('lblTienNuoc').textContent = tienNuoc.toLocaleString() + 'đ';

  document.getElementById('summaryTienDien').textContent = '-' + tienDien.toLocaleString() + 'đ';
  document.getElementById('summaryTienNuoc').textContent = '-' + tienNuoc.toLocaleString() + 'đ';
  document.getElementById('summaryHuHong').textContent = '-' + huHong.toLocaleString() + 'đ';
  document.getElementById('summaryVeSinh').textContent = '-' + veSinh.toLocaleString() + 'đ';
  document.getElementById('summaryChiPhiKhac').textContent = '-' + chiPhiKhac.toLocaleString() + 'đ';

  document.getElementById('lblTienHoanLai').textContent = tienHoanLai.toLocaleString() + 'đ';
}

function submitQuyetToan(type) {
  document.getElementById('inputActionType').value = type;
  if (type === 'hoan_tat') {
    if (!confirm('XÁC NHẬN THANH LÝ:\nKhi xác nhận hoàn tất, Hợp đồng sẽ kết thúc, phòng sẽ chuyển về trạng thái TRỐNG và tạo biên bản trả phòng tự động.\nBạn có chắc chắn muốn thực hiện?')) {
      return;
    }
  }
  document.getElementById('formQuyetToan').submit();
}

document.addEventListener('DOMContentLoaded', () => {
  tinhQuyetToan();
});
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
