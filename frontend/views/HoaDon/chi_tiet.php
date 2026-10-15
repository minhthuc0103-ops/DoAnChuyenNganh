<?php
$title = 'Chi tiết hóa đơn';
require_once 'frontend/views/Layouts/header.php';
require_once 'backend/config/payment.php';

$isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']);
$isPaid  = $hd['trang_thai'] === 'da_tt';

// Tính toán
$dienTieuThu = max(0, $hd['chi_so_dien_moi'] - $hd['chi_so_dien_cu']);
$nuocTieuThu = max(0, $hd['chi_so_nuoc_moi'] - $hd['chi_so_nuoc_cu']);

// QR URL
$qrInfo = 'TT phong ' . ($phong['so_phong'] ?? '') . ' T' . $hd['thang'] . '/' . $hd['nam'];
$qrUrl  = 'https://img.vietqr.io/image/' . BANK_ID . '-' . BANK_ACCOUNT . '-' . VIETQR_TEMPLATE . '.jpg'
         . '?amount=' . (int)$hd['tong_tien']
         . '&addInfo=' . urlencode($qrInfo)
         . '&accountName=' . urlencode(BANK_NAME);

$detailQrOptions = [
  'chuyen_khoan' => ['url' => $qrUrl, 'title' => 'QR Banking', 'hint' => 'QR ngân hàng đã điền sẵn số tiền và nội dung.'],
  'momo' => ['url' => MOMO_QR_IMAGE, 'title' => 'MoMo', 'hint' => 'QR tĩnh: nhập đúng số tiền và nội dung trước khi xác nhận.'],
  'vnpay' => ['url' => VNPAY_QR_IMAGE, 'title' => 'VNPay-QR', 'hint' => 'QR tĩnh: nhập đúng số tiền và nội dung trước khi xác nhận.'],
];
?>

<link rel="stylesheet" href="frontend/assets/css/hoadon-chi-tiet.css">

<div class="page-header">
  <div class="page-title">
    <h1>📄 Chi tiết hóa đơn</h1>
    <p>Phòng <?= htmlspecialchars($phong['so_phong'] ?? '') ?> — Tháng <?= $hd['thang'] ?>/<?= $hd['nam'] ?></p>
  </div>
  <div class="header-actions no-print">
    <button onclick="window.print()" class="btn btn-outline">🖨 In</button>
    <?php if($isAdmin): ?>
    <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Danh sách</a>
    <?php else: ?>
    <a href="index.php?controller=user&action=index" class="btn btn-outline">← Quay lại</a>
    <?php endif; ?>
  </div>
</div>

<div style="max-width:700px;">

  <!-- TRẠNG THÁI -->
  <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;margin-bottom:18px;border-radius:12px;
              background:<?= $isPaid ? 'rgba(34,201,147,.06)' : 'rgba(247,92,92,.06)' ?>;
              border:1px solid <?= $isPaid ? 'rgba(34,201,147,.2)' : 'rgba(247,92,92,.2)' ?>;">
    <span style="font-size:28px;"><?= $isPaid ? '✅' : '⏳' ?></span>
    <div>
      <div style="font-size:15px;font-weight:700;color:<?= $isPaid ? 'var(--green)' : 'var(--red)' ?>;">
        <?= $isPaid ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
      </div>
      <div style="font-size:12px;color:var(--text3);">
        <?php if($isPaid && !empty($hd['ngay_thanh_toan'])): ?>
          Thanh toán lúc <?= date('d/m/Y H:i', strtotime($hd['ngay_thanh_toan'])) ?>
          <?php if(!empty($hd['phuong_thuc_tt'])): ?>
            · <?= ucfirst(str_replace('_', ' ', $hd['phuong_thuc_tt'])) ?>
          <?php endif; ?>
        <?php else: ?>
          Tạo ngày <?= date('d/m/Y H:i', strtotime($hd['created_at'])) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- THÔNG TIN PHÒNG -->
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header"><div class="card-title">🏠 Thông tin phòng</div></div>
    <div class="card-body">
      <div class="detail-grid">
        <div class="detail-box">
          <div class="detail-lbl">Số phòng</div>
          <div class="detail-val"><?= htmlspecialchars($phong['so_phong'] ?? '') ?></div>
        </div>
        <div class="detail-box">
          <div class="detail-lbl">Khu trọ</div>
          <div class="detail-val"><?= htmlspecialchars($phong['ten_khu'] ?? '—') ?></div>
        </div>
        <div class="detail-box">
          <div class="detail-lbl">Tháng/Năm</div>
          <div class="detail-val">Tháng <?= $hd['thang'] ?>/<?= $hd['nam'] ?></div>
        </div>
        <div class="detail-box">
          <div class="detail-lbl">Ngày tạo</div>
          <div class="detail-val"><?= date('d/m/Y', strtotime($hd['created_at'])) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- CHI TIẾT TIỀN -->
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header"><div class="card-title">💰 Chi tiết tiền</div></div>
    <div class="card-body">

      <!-- Tiền phòng -->
      <div class="meter-row">
        <span style="font-size:20px;">🏠</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;color:var(--text);">Tiền phòng</div>
          <div style="font-size:11px;color:var(--text3);">Giá thuê hàng tháng</div>
        </div>
        <strong style="font-size:15px;color:var(--text);"><?= number_format($hd['tien_phong']) ?>đ</strong>
      </div>

      <!-- Tiền điện -->
      <div class="meter-row" style="border-color:rgba(247,169,79,.2);">
        <span style="font-size:20px;">⚡</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;color:var(--text);">Tiền điện</div>
          <div style="font-size:11px;color:var(--text3);">
            Chỉ số: <?= $hd['chi_so_dien_cu'] ?> → <?= $hd['chi_so_dien_moi'] ?> kWh
            · Tiêu thụ: <strong style="color:var(--amber);"><?= $dienTieuThu ?> kWh</strong>
            · Đơn giá: <?= number_format($donGia['gia_dien']) ?>đ/kWh
          </div>
        </div>
        <strong style="font-size:15px;color:var(--amber);"><?= number_format($hd['tien_dien']) ?>đ</strong>
      </div>

      <!-- Tiền nước -->
      <div class="meter-row" style="border-color:rgba(79,142,247,.2);">
        <span style="font-size:20px;">💧</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;color:var(--text);">Tiền nước</div>
          <div style="font-size:11px;color:var(--text3);">
            Chỉ số: <?= $hd['chi_so_nuoc_cu'] ?> → <?= $hd['chi_so_nuoc_moi'] ?> m³
            · Tiêu thụ: <strong style="color:var(--accent);"><?= $nuocTieuThu ?> m³</strong>
            · Đơn giá: <?= number_format($donGia['gia_nuoc']) ?>đ/m³
          </div>
        </div>
        <strong style="font-size:15px;color:var(--accent);"><?= number_format($hd['tien_nuoc']) ?>đ</strong>
      </div>

      <!-- Phí dịch vụ -->
      <?php $phiDV = (float)($hd['phi_dich_vu'] ?? 0);
            if ($phiDV <= 0) $phiDV = (float)($donGia['phi_dv'] ?? 150000); ?>
      <div class="meter-row" style="border-color:rgba(34,201,147,.2);">
        <span style="font-size:20px;">🛎</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;color:var(--text);">Phí dịch vụ</div>
          <div style="font-size:11px;color:var(--text3);">Wifi · Rác · Vệ sinh · Bảo vệ</div>
        </div>
        <strong style="font-size:15px;color:var(--green);"><?= number_format($phiDV) ?>đ</strong>
      </div>

      <!-- Phí xe -->
      <?php
        $soXe  = (int)($hd['so_xe'] ?? 0);
        $phiXe = (float)($hd['phi_xe'] ?? 0);
        // Fallback cho hóa đơn cũ: query xe từ hợp đồng đang hiệu lực
        if ($soXe === 0 && $phiXe == 0) {
            $dbX = Database::getInstance();
            $stX = $dbX->prepare("SELECT COUNT(*) FROM xe x JOIN hop_dong hd2 ON x.hop_dong_id=hd2.id WHERE hd2.phong_id=? AND hd2.trang_thai='hieu_luc'");
            $stX->execute([(int)$hd['phong_id']]);
            $soXe  = (int)$stX->fetchColumn();
            $phiXe = $soXe * 100000;
        }
      ?>
      <?php if ($soXe > 0): ?>
      <div class="meter-row" style="border-color:rgba(124,92,252,.2);">
        <span style="font-size:20px;">🚗</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;color:var(--text);">Phí xe</div>
          <div style="font-size:11px;color:var(--text3);"><?= $soXe ?> xe × 100.000đ/xe</div>
        </div>
        <strong style="font-size:15px;color:#a78bfa;"><?= number_format($phiXe) ?>đ</strong>
      </div>
      <?php endif; ?>

      <!-- Tổng cộng (tính lại từ các dòng để luôn khớp) -->
      <?php $tongHienThi = (float)$hd['tien_phong'] + (float)$hd['tien_dien'] + (float)$hd['tien_nuoc'] + $phiDV + $phiXe; ?>
      <div style="margin-top:14px;padding:16px 20px;background:linear-gradient(135deg,rgba(79,142,247,.08),rgba(124,92,252,.05));border:1px solid rgba(79,142,247,.2);border-radius:12px;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:15px;font-weight:700;color:var(--text);">Tổng cộng</span>
        <span style="font-size:24px;font-weight:800;color:var(--accent);"><?= number_format($tongHienThi) ?>đ</span>
      </div>
    </div>
  </div>

  <!-- QR THANH TOÁN (chỉ hiện khi chưa TT) -->
  <?php if(!$isPaid): ?>
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header"><div class="card-title">📱 Thanh toán QR</div><span class="pill p-red">Chưa thanh toán</span></div>
    <div class="card-body" style="text-align:center;">
      <div class="no-print" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;">
        <button type="button" class="btn btn-outline" onclick="showDetailQr('chuyen_khoan')">🏦 Banking</button>
        <button type="button" class="btn btn-outline" onclick="showDetailQr('momo')">📱 MoMo</button>
        <button type="button" class="btn btn-outline" onclick="showDetailQr('vnpay')">🔵 VNPay</button>
      </div>
      <div id="detailQrTitle" style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:10px;">QR Banking</div>
      <div style="background:#fff;border-radius:12px;padding:14px;display:inline-block;margin-bottom:14px;"><img id="detailQrImage" src="<?= $qrUrl ?>" style="width:180px;height:180px;object-fit:contain;" alt="QR"/></div>
      <div id="detailBankInfo" style="text-align:left;font-size:12px;color:var(--text2);line-height:2;max-width:320px;margin:0 auto;">
        <div style="display:flex;justify-content:space-between;"><span>Ngân hàng:</span><strong style="color:var(--text);"><?= BANK_DISPLAY ?></strong></div>
        <div style="display:flex;justify-content:space-between;"><span>Số TK:</span><strong style="color:var(--text);"><?= BANK_ACCOUNT ?></strong></div>
        <div style="display:flex;justify-content:space-between;"><span>Chủ TK:</span><strong style="color:var(--text);"><?= BANK_NAME ?></strong></div>
      </div>
      <div style="text-align:left;font-size:12px;color:var(--text2);line-height:2;max-width:320px;margin:0 auto;">
        <div style="display:flex;justify-content:space-between;"><span>Số tiền:</span><strong style="color:var(--accent);"><?= number_format($hd['tong_tien']) ?>đ</strong></div>
        <div style="display:flex;justify-content:space-between;"><span>Nội dung:</span><strong style="color:var(--accent);"><?= htmlspecialchars($qrInfo) ?></strong></div>
      </div>
      <div style="margin-top:14px;padding:10px;background:rgba(34,201,147,.06);border:1px solid rgba(34,201,147,.2);border-radius:8px;"><div id="detailQrHint" style="font-size:11px;color:var(--green);">💡 QR ngân hàng đã điền sẵn số tiền và nội dung.</div></div>
      <?php if($isAdmin): ?><div style="margin-top:14px;"><a href="index.php?controller=hoadon&action=thanhToan&id=<?= $hd['id'] ?>" class="btn btn-success" style="width:100%;justify-content:center;">✓ Xác nhận đã thu tiền</a></div><?php endif; ?>
    </div>
  </div>
  <script>
  const detailQrOptions=<?= json_encode($detailQrOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  function showDetailQr(type){const q=detailQrOptions[type];if(!q)return;document.getElementById('detailQrImage').src=q.url;document.getElementById('detailQrTitle').textContent=q.title;document.getElementById('detailQrHint').textContent='💡 '+q.hint;document.getElementById('detailBankInfo').style.display=type==='chuyen_khoan'?'block':'none';}
  </script>
  <?php endif; ?>

  <!-- THÔNG TIN THANH TOÁN (nếu đã TT) -->
  <?php if($isPaid): ?>
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header">
      <div class="card-title">✅ Thông tin thanh toán</div>
    </div>
    <div class="card-body">
      <div class="detail-grid">
        <?php if(!empty($hd['phuong_thuc_tt'])): ?>
        <div class="detail-box">
          <div class="detail-lbl">Phương thức</div>
          <div class="detail-val">
            <?php
            $ptIcons = ['tien_mat'=>'💵 Tiền mặt','chuyen_khoan'=>'🏦 Chuyển khoản','momo'=>'📱 MoMo','vnpay'=>'🔵 VNPay','zalopay'=>'📲 ZaloPay'];
            echo $ptIcons[$hd['phuong_thuc_tt']] ?? ucfirst($hd['phuong_thuc_tt']);
            ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if(!empty($hd['ngay_thanh_toan'])): ?>
        <div class="detail-box">
          <div class="detail-lbl">Ngày thanh toán</div>
          <div class="detail-val"><?= date('d/m/Y H:i', strtotime($hd['ngay_thanh_toan'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if(!empty($hd['nguoi_thu'])): ?>
        <div class="detail-box">
          <div class="detail-lbl">Người thu</div>
          <div class="detail-val"><?= htmlspecialchars($hd['nguoi_thu']) ?></div>
        </div>
        <?php endif; ?>
        <?php if(!empty($hd['ghi_chu_tt'])): ?>
        <div class="detail-box" style="grid-column:span 2;">
          <div class="detail-lbl">Ghi chú</div>
          <div class="detail-val" style="font-size:13px;font-weight:500;"><?= htmlspecialchars($hd['ghi_chu_tt']) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
