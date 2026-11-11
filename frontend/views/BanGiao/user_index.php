<?php
$title = 'Bàn giao & Trả phòng';
require_once 'frontend/views/Layouts/header.php';

$statusMap = [
    'cho_kiem_tra'  => ['⏳', 'Chờ quản lý kiểm tra phòng', 'p-amber'],
    'dang_kiem_tra' => ['🔍', 'Đang kiểm tra & Quyết toán', 'p-blue'],
    'da_thanh_ly'   => ['✅', 'Đã thanh lý & Hoàn trả cọc', 'p-green'],
    'tu_choi'       => ['❌', 'Đã hủy', 'p-red'],
];
?>

<div class="page-header">
  <div class="page-title">
    <h1>📦 Bàn giao & Báo trả phòng</h1>
    <p>Biên bản nhận phòng, bàn giao tài sản và gửi yêu cầu trả phòng hoàn cọc</p>
  </div>
  <?php if ($activeRental): ?>
  <div class="header-actions">
    <button onclick="document.getElementById('formBaoTraPhong').scrollIntoView({behavior:'smooth'})"
            class="btn btn-primary">
      📤 Báo trước trả phòng
    </button>
  </div>
  <?php endif; ?>
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="msg-alert msg-success" style="margin-bottom:14px;">
  <?php
    if ($_GET['msg'] === 'requested') echo '✓ Đã gửi yêu cầu trả phòng thành công! Quản lý sẽ liên hệ kiểm tra phòng và quyết toán cọc.';
    elseif ($_GET['msg'] === 'cancelled') echo '✓ Đã hủy yêu cầu trả phòng.';
    else echo '✓ Thao tác thành công.';
  ?>
</div>
<?php endif; ?>

<?php if (!empty($_GET['err'])): ?>
<div class="msg-alert msg-error" style="margin-bottom:14px;">
  ⚠ <?= htmlspecialchars($_GET['err']) ?>
</div>
<?php endif; ?>

<!-- THÔNG TIN PHÒNG ĐANG THUÊ -->
<?php if ($activeRental): ?>
<div class="card" style="margin-bottom:20px;border-left:4px solid var(--accent);">
  <div class="card-header">
    <div class="card-title">🏠 Phòng bạn đang thuê: Phòng <?= htmlspecialchars($activeRental['so_phong']) ?> (<?= htmlspecialchars($activeRental['ten_khu'] ?? '') ?>)</div>
    <span class="pill p-green">Hợp đồng hiệu lực</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
      <div style="background:var(--bg3);padding:10px 14px;border-radius:8px;">
        <div style="font-size:11px;color:var(--text3);">Giá thuê hàng tháng</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= number_format($activeRental['gia_thue']) ?>đ</div>
      </div>
      <div style="background:var(--bg3);padding:10px 14px;border-radius:8px;">
        <div style="font-size:11px;color:var(--text3);">Tiền cọc giữ phòng</div>
        <div style="font-size:15px;font-weight:700;color:var(--amber);"><?= number_format($activeRental['tien_coc']) ?>đ</div>
      </div>
      <div style="background:var(--bg3);padding:10px 14px;border-radius:8px;">
        <div style="font-size:11px;color:var(--text3);">Ngày bắt đầu thuê</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= date('d/m/Y', strtotime($activeRental['ngay_bat_dau'])) ?></div>
      </div>
      <div style="background:var(--bg3);padding:10px 14px;border-radius:8px;">
        <div style="font-size:11px;color:var(--text3);">Hết hạn hợp đồng</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= date('d/m/Y', strtotime($activeRental['ngay_ket_thuc'])) ?></div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:minmax(320px,460px) 1fr;gap:20px;align-items:start;">

  <!-- FORM GỬI YÊU CẦU TRẢ PHÒNG -->
  <?php if ($activeRental): ?>
  <div class="card" id="formBaoTraPhong">
    <div class="card-header">
      <div class="card-title">📤 Báo trước trả phòng & Nhận hoàn cọc</div>
    </div>
    <div class="card-body">
      <form method="POST" action="index.php?controller=bangiao&action=guiYeuCauTraPhong">
        <?= Csrf::field() ?>

        <div class="form-group">
          <label class="form-label">Ngày dự kiến dọn ra / trả phòng <span style="color:var(--red)">*</span></label>
          <input type="date" name="ngay_du_kien_tra" class="form-control"
                 min="<?= date('Y-m-d') ?>"
                 value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required/>
          <div style="font-size:11px;color:var(--text3);margin-top:4px;">
            💡 Theo quy định, vui lòng báo trước tối thiểu 30 ngày để đảm bảo điều kiện hoàn trả 100% tiền cọc.
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Lý do trả phòng <span style="color:var(--red)">*</span></label>
          <textarea class="form-control" name="ly_do" rows="3"
                    placeholder="vd: Hết hợp đồng, chuyển công tác, về quê, tốt nghiệp..." required></textarea>
        </div>

        <div style="font-size:12px;font-weight:700;color:var(--text);margin:14px 0 8px 0;text-transform:uppercase;">
          🏦 Thông tin tài khoản nhận hoàn tiền cọc
        </div>

        <div class="form-group">
          <label class="form-label">Tên ngân hàng</label>
          <input type="text" name="ngan_hang" class="form-control"
                 placeholder="vd: MB Bank, Vietcombank, Techcombank..."/>
        </div>

        <div class="form-group">
          <label class="form-label">Số tài khoản</label>
          <input type="text" name="so_tai_khoan" class="form-control"
                 placeholder="vd: 0123456789"/>
        </div>

        <div class="form-group">
          <label class="form-label">Chủ tài khoản (viết hoa không dấu)</label>
          <input type="text" name="ten_chu_tk" class="form-control"
                 placeholder="vd: NGUYEN VAN A"/>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;font-size:14px;font-weight:700;">
          📤 Gửi yêu cầu trả phòng
        </button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- LỊCH SỬ YÊU CẦU TRẢ PHÒNG & BIÊN BẢN CỦA TÔI -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Lịch sử yêu cầu trả phòng -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">📋 Lịch sử yêu cầu trả phòng của bạn</div>
        <span style="font-size:12px;color:var(--text3);"><?= count($traPhongList ?? []) ?> yêu cầu</span>
      </div>
      <?php if (empty($traPhongList)): ?>
      <div style="text-align:center;padding:36px;color:var(--text3);">
        <div style="font-size:32px;margin-bottom:8px;">📭</div>
        Bạn chưa gửi yêu cầu trả phòng nào.
      </div>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;">
        <?php foreach ($traPhongList as $yc):
          $st = $statusMap[$yc['trang_thai']] ?? ['❓', $yc['trang_thai'], 'p-gray'];
        ?>
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
            <div>
              <span class="pill <?=$st[2]?>"><?=$st[0].' '.$st[1]?></span>
              <span class="pill p-blue" style="margin-left:4px;">Phòng <?= htmlspecialchars($yc['so_phong']) ?></span>
            </div>
            <div style="font-size:12px;color:var(--text3);">
              Báo lúc: <?= date('d/m/Y', strtotime($yc['ngay_yeu_cau'])) ?>
            </div>
          </div>

          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;">
            📅 Ngày dự kiến dọn ra: <?= date('d/m/Y', strtotime($yc['ngay_du_kien_tra'])) ?>
          </div>
          <div style="font-size:13px;color:var(--text2);margin-bottom:8px;">
            Lý do: <?= htmlspecialchars($yc['ly_do']) ?>
          </div>

          <!-- Kết quả quyết toán nếu đã xong -->
          <?php if ($yc['trang_thai'] === 'da_thanh_ly'): ?>
          <div style="background:rgba(34,201,147,.08);border:1px solid rgba(34,201,147,.25);border-radius:8px;padding:12px 14px;margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
              <span>Tiền cọc ban đầu:</span>
              <strong><?= number_format($yc['tien_coc_goc']) ?>đ</strong>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;color:var(--red);">
              <span>Tiền điện tiêu thụ (<?= max(0, $yc['chi_so_dien_cuoi'] - $yc['chi_so_dien_dau']) ?> kWh):</span>
              <span>-<?= number_format($yc['tien_dien']) ?>đ</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;color:var(--red);">
              <span>Tiền nước tiêu thụ (<?= max(0, $yc['chi_so_nuoc_cuoi'] - $yc['chi_so_nuoc_dau']) ?> m³):</span>
              <span>-<?= number_format($yc['tien_nuoc']) ?>đ</span>
            </div>
            <?php if ($yc['chi_phi_hu_hong'] > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;color:var(--red);">
              <span>Khấu trừ hư hỏng thiết bị:</span>
              <span>-<?= number_format($yc['chi_phi_hu_hong']) ?>đ</span>
            </div>
            <?php endif; ?>
            <?php if ($yc['phi_ve_sinh'] > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;color:var(--red);">
              <span>Phí dọn vệ sinh:</span>
              <span>-<?= number_format($yc['phi_ve_sinh']) ?>đ</span>
            </div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;color:var(--green);border-top:1px dashed rgba(34,201,147,.3);padding-top:6px;margin-top:6px;">
              <span>Số tiền thực tế hoàn trả bạn:</span>
              <span><?= number_format($yc['tien_hoan_lai']) ?>đ</span>
            </div>
          </div>
          <div style="display:flex;gap:10px;">
            <a href="index.php?controller=bangiao&action=inBienBanTraPhong&id=<?=$yc['id']?>" target="_blank"
               class="btn btn-primary" style="font-size:12px;padding:6px 14px;">
              🖨 Xem phiếu quyết toán chi tiết
            </a>
          </div>
          <?php elseif ($yc['trang_thai'] === 'cho_kiem_tra'): ?>
          <a href="index.php?controller=bangiao&action=huyYeuCauTraPhong&id=<?=$yc['id']?>"
             onclick="return confirm('Bạn có chắc chắn muốn hủy yêu cầu trả phòng này?')"
             class="btn btn-outline" style="font-size:11px;padding:4px 10px;color:var(--red);">
            ❌ Hủy yêu cầu
          </a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Biên bản bàn giao nhận phòng của tôi -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">📦 Biên bản bàn giao nhận phòng của bạn</div>
      </div>
      <?php if (empty($bienBanList)): ?>
      <div style="text-align:center;padding:36px;color:var(--text3);">
        <div style="font-size:32px;margin-bottom:8px;">📦</div>
        Chưa có biên bản bàn giao phòng nào.
      </div>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;">
        <?php foreach ($bienBanList as $bb): ?>
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
          <div>
            <div style="font-weight:700;color:var(--text);font-size:14px;">
              <?= $bb['loai'] === 'tra_phong' ? 'Biên bản trả phòng' : 'Biên bản nhận phòng' ?>
              #<?= htmlspecialchars($bb['ma_bien_ban']) ?>
            </div>
            <div style="font-size:12px;color:var(--text3);margin-top:2px;">
              Ngày lập: <?= date('d/m/Y', strtotime($bb['ngay_lap'])) ?> ·
              Điện: <?= number_format($bb['chi_so_dien']) ?> kWh · Nước: <?= number_format($bb['chi_so_nuoc']) ?> m³
            </div>
          </div>
          <div style="display:flex;gap:8px;">
            <a href="index.php?controller=bangiao&action=xemBienBan&id=<?=$bb['id']?>"
               class="btn btn-outline" style="font-size:12px;padding:6px 12px;">
              👁 Chi tiết
            </a>
            <a href="index.php?controller=bangiao&action=inBienBan&id=<?=$bb['id']?>" target="_blank"
               class="btn btn-outline" style="font-size:12px;padding:6px 12px;">
              🖨 In A4
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>

</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
