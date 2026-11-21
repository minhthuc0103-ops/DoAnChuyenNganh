<?php
$title = 'Lập biên bản bàn giao phòng';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>📦 Lập biên bản bàn giao phòng</h1>
    <p>Kiểm kê hiện trạng thiết bị, chốt chỉ số điện nước khi giao nhận phòng</p>
  </div>
  <a href="index.php?controller=bangiao&action=index" class="btn btn-outline">
    ← Quay lại danh sách
  </a>
</div>

<?php if (!empty($error)): ?>
<div class="msg-alert msg-error" style="margin-bottom:14px;">
  ⚠ <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:880px;margin:0 auto;">
  <div class="card-header">
    <div class="card-title">📝 Thông tin biên bản bàn giao</div>
  </div>
  <div class="card-body">
    <form method="POST" action="index.php?controller=bangiao&action=taoBienBan" enctype="multipart/form-data">
      <?= Csrf::field() ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
        <div class="form-group">
          <label class="form-label">Chọn Hợp đồng / Phòng trọ <span style="color:var(--red)">*</span></label>
          <select name="hop_dong_id" class="form-control" required onchange="updateHopDongInfo(this)">
            <option value="">-- Chọn hợp đồng / phòng --</option>
            <?php foreach ($hopDongList as $hd): ?>
            <option value="<?= $hd['hop_dong_id'] ?>"
                    data-phong="<?= htmlspecialchars($hd['so_phong']) ?>"
                    data-nguoi="<?= htmlspecialchars($hd['ten_nguoi_thue']) ?>"
                    data-sdt="<?= htmlspecialchars($hd['sdt']) ?>"
                    data-coc="<?= number_format($hd['tien_coc']) ?>">
              Phòng <?= htmlspecialchars($hd['so_phong']) ?> - <?= htmlspecialchars($hd['ten_nguoi_thue']) ?> (HĐ #<?= $hd['hop_dong_id'] ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Loại bàn giao <span style="color:var(--red)">*</span></label>
          <select name="loai" class="form-control" required>
            <option value="nhan_phong">📥 Bàn giao khi nhận phòng (Check-in)</option>
            <option value="tra_phong">📤 Bàn giao khi trả phòng (Check-out)</option>
          </select>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:18px;">
        <div class="form-group">
          <label class="form-label">Ngày lập biên bản</label>
          <input type="date" name="ngay_lap" class="form-control" value="<?= date('Y-m-d') ?>" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Chỉ số điện (kWh) <span style="color:var(--red)">*</span></label>
          <input type="number" name="chi_so_dien" class="form-control" placeholder="vd: 120" min="0" value="0" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Chỉ số nước (m³) <span style="color:var(--red)">*</span></label>
          <input type="number" name="chi_so_nuoc" class="form-control" placeholder="vd: 15" min="0" value="0" required/>
        </div>
      </div>

      <!-- BẢNG KIỂM KÊ TÀI SẢN & THIẾT BỊ -->
      <div style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
          <label class="form-label" style="font-size:14px;font-weight:700;margin-bottom:0;">
            🛋 Danh mục tài sản, thiết bị bàn giao trong phòng
          </label>
          <button type="button" class="btn btn-outline" onclick="themDongTaiSan()" style="font-size:12px;padding:4px 10px;">
            ＋ Thêm thiết bị
          </button>
        </div>

        <div style="overflow-x:auto;">
          <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;" id="tableTaiSan">
            <thead>
              <tr style="background:var(--bg3);border-bottom:1.5px solid var(--border);text-align:left;">
                <th style="padding:10px 12px;width:40%;">Tên thiết bị / Tài sản</th>
                <th style="padding:10px 12px;width:15%;">Số lượng</th>
                <th style="padding:10px 12px;width:35%;">Tình trạng khi bàn giao</th>
                <th style="padding:10px 12px;width:10%;text-align:center;">Xóa</th>
              </tr>
            </thead>
            <tbody id="tbodyTaiSan">
              <?php
                $mauTaiSan = [
                  ['Chìa khóa phòng / Thẻ từ', 2, 'Đầy đủ, mở tốt'],
                  ['Máy lạnh (Điều hòa) & Remote', 1, 'Mới, làm lạnh tốt, remote đủ pin'],
                  ['Tủ lạnh', 1, 'Hoạt động tốt, sạch sẽ'],
                  ['Bình nóng lạnh', 1, 'Nóng nhanh, an toàn'],
                  ['Giường ngủ & Nệm', 1, 'Chắc chắn, nệm sạch không rách'],
                  ['Tủ quần áo', 1, 'Cửa mở êm, không trầy xước'],
                  ['Quạt trần / Quạt treo tường', 1, 'Quay êm, đủ cấp tốc độ'],
                  ['Thiết bị vệ sinh (Lavabo, bồn cầu, vòi sen)', 1, 'Sạch sẽ, thoát nước tốt'],
                  ['Đèn chiếu sáng', 3, 'Sáng tốt tất cả các bóng'],
                ];
                foreach ($mauTaiSan as $ts):
              ?>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px 12px;">
                  <input type="text" name="tai_san_ten[]" class="form-control" value="<?= htmlspecialchars($ts[0]) ?>" required/>
                </td>
                <td style="padding:8px 12px;">
                  <input type="number" name="tai_san_sl[]" class="form-control" value="<?= $ts[1] ?>" min="1" required/>
                </td>
                <td style="padding:8px 12px;">
                  <input type="text" name="tai_san_tinh_trang[]" class="form-control" value="<?= htmlspecialchars($ts[2]) ?>" required/>
                </td>
                <td style="padding:8px 12px;text-align:center;">
                  <button type="button" class="btn btn-outline" onclick="xoaDong(this)" style="padding:4px 8px;font-size:11px;color:var(--red);">✕</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">📷 Chụp / Tải ảnh hiện trạng phòng khi bàn giao (ảnh phòng, đồng hồ...)</label>
        <input type="file" name="anh_ban_giao" class="form-control" accept="image/*"/>
      </div>

      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label">Ghi chú bổ sung</label>
        <textarea class="form-control" name="ghi_chu" rows="2" placeholder="Cam kết khác hoặc thỏa thuận thêm giữa hai bên..."></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:12px;">
        <a href="index.php?controller=bangiao&action=index" class="btn btn-outline">Hủy bỏ</a>
        <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-weight:700;">
          💾 Lưu biên bản bàn giao
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function themDongTaiSan() {
  const tbody = document.getElementById('tbodyTaiSan');
  const tr = document.createElement('tr');
  tr.style.borderBottom = '1px solid var(--border)';
  tr.innerHTML = `
    <td style="padding:8px 12px;">
      <input type="text" name="tai_san_ten[]" class="form-control" placeholder="Tên thiết bị..." required/>
    </td>
    <td style="padding:8px 12px;">
      <input type="number" name="tai_san_sl[]" class="form-control" value="1" min="1" required/>
    </td>
    <td style="padding:8px 12px;">
      <input type="text" name="tai_san_tinh_trang[]" class="form-control" value="Hoạt động tốt" required/>
    </td>
    <td style="padding:8px 12px;text-align:center;">
      <button type="button" class="btn btn-outline" onclick="xoaDong(this)" style="padding:4px 8px;font-size:11px;color:var(--red);">✕</button>
    </td>
  `;
  tbody.appendChild(tr);
}

function xoaDong(btn) {
  const tr = btn.closest('tr');
  if (document.querySelectorAll('#tbodyTaiSan tr').length > 1) {
    tr.remove();
  } else {
    alert('Biên bản cần ít nhất 1 hạng mục tài sản.');
  }
}
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
