<?php
$title = 'Xác nhận thanh toán';
require_once 'frontend/views/Layouts/header.php';
require_once 'backend/config/payment.php';

$qrAmount = (int)$hd['tong_tien'];
$qrInfo   = 'TT phong ' . $hd['so_phong'] . ' T' . $hd['thang'] . '/' . $hd['nam'];
$suggestedMethod = $hd['pending_method'] ?: 'tien_mat';
$pendingNote = trim($hd['pending_note'] ?? '');
$bankQrUrl = 'https://img.vietqr.io/image/' . BANK_ID . '-' . BANK_ACCOUNT . '-' . VIETQR_TEMPLATE . '.jpg'
           . '?amount=' . $qrAmount
           . '&addInfo=' . urlencode($qrInfo)
           . '&accountName=' . urlencode(BANK_NAME);

$paymentQrOptions = [
  'chuyen_khoan' => [
    'url'   => $bankQrUrl,
    'title' => 'Quét QR Banking để chuyển khoản',
    'hint'  => 'Mở app ngân hàng bất kỳ → Quét QR → Kiểm tra thông tin → Xác nhận',
  ],
  'momo' => [
    'url'   => MOMO_QR_IMAGE,
    'title' => 'Quét QR bằng ứng dụng MoMo',
    'hint'  => 'Mở MoMo → Quét mã → Nhập đúng số tiền và nội dung thanh toán',
  ],
  'vnpay' => [
    'url'   => VNPAY_QR_IMAGE,
    'title' => 'Quét mã VNPay-QR',
    'hint'  => 'Mở ứng dụng hỗ trợ VNPay-QR → Quét mã → Kiểm tra thông tin → Xác nhận',
  ],
];
?>

<link rel="stylesheet" href="frontend/assets/css/hoadon-thanh-toan.css">

<div class="page-header">
  <div class="page-title">
    <h1>💳 Xác nhận thanh toán</h1>
    <p>Phòng <?= htmlspecialchars($hd['so_phong']) ?> — Tháng <?= $hd['thang'] ?>/<?= $hd['nam'] ?></p>
  </div>
  <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:620px;">
  <div class="card">
    <div class="card-body">
      <div style="background:linear-gradient(135deg,rgba(79,142,247,.1),rgba(124,92,252,.07));border:1px solid rgba(79,142,247,.2);border-radius:14px;padding:20px;margin-bottom:22px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Tổng tiền phải thu</div>
        <div style="font-size:36px;font-weight:800;color:var(--accent);"><?= number_format($qrAmount) ?>đ</div>
        <div style="display:flex;justify-content:center;gap:16px;margin-top:10px;font-size:12px;color:var(--text3);">
          <span>🏠 <?= number_format($hd['tien_phong']) ?>đ</span>
          <span>⚡ <?= number_format($hd['tien_dien']) ?>đ</span>
          <span>💧 <?= number_format($hd['tien_nuoc']) ?>đ</span>
        </div>
      </div>

      <?php if(!empty($hd['pending_count'])): ?>
      <div style="padding:12px 14px;border:1px solid rgba(247,169,79,.25);background:rgba(247,169,79,.08);border-radius:10px;margin-bottom:18px;">
        <div style="font-size:13px;font-weight:800;color:var(--amber);margin-bottom:4px;">Nguoi thue da gui xac nhan thanh toan</div>
        <div style="font-size:12px;color:var(--text2);line-height:1.6;">
          Phuong thuc nguoi thue chon: <strong style="color:var(--text);"><?= htmlspecialchars($suggestedMethod) ?></strong>
          <?php if(!empty($hd['pending_by'])): ?><br>Nguoi gui: <?= htmlspecialchars($hd['pending_by']) ?><?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <form method="POST" action="index.php?controller=hoadon&action=thanhToan&id=<?= $hd['id'] ?>
            <?= Csrf::field() ?>">
        <div class="form-group">
          <label class="form-label">💳 Phương thức thanh toán</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <label class="pt-option active" data-val="tien_mat" onclick="selectPT('tien_mat')">
              <input type="radio" name="phuong_thuc" value="tien_mat" checked style="display:none;"/>
              <span style="font-size:24px;">💵</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">Tiền mặt</div><div style="font-size:11px;color:var(--text3);">Thu trực tiếp</div></div>
            </label>
            <label class="pt-option" data-val="chuyen_khoan" onclick="selectPT('chuyen_khoan')">
              <input type="radio" name="phuong_thuc" value="chuyen_khoan" style="display:none;"/>
              <span style="font-size:24px;">🏦</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">Chuyển khoản</div><div style="font-size:11px;color:var(--text3);">QR Banking</div></div>
            </label>
            <label class="pt-option" data-val="momo" onclick="selectPT('momo')">
              <input type="radio" name="phuong_thuc" value="momo" style="display:none;"/>
              <span style="font-size:24px;">📱</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">MoMo</div><div style="font-size:11px;color:var(--text3);">QR riêng</div></div>
            </label>
            <label class="pt-option" data-val="vnpay" onclick="selectPT('vnpay')">
              <input type="radio" name="phuong_thuc" value="vnpay" style="display:none;"/>
              <span style="font-size:24px;">🔵</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">VNPay</div><div style="font-size:11px;color:var(--text3);">VNPay-QR</div></div>
            </label>
          </div>
        </div>

        <div id="qrSection" style="display:none;margin-bottom:18px;">
          <div style="border:1px solid rgba(79,142,247,.25);border-radius:14px;overflow:hidden;">
            <div style="background:rgba(79,142,247,.06);padding:20px;text-align:center;border-bottom:1px solid rgba(79,142,247,.15);">
              <div id="qrTitle" style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">Quét QR để thanh toán</div>
              <div style="background:#fff;border-radius:14px;padding:14px;display:inline-block;margin-bottom:10px;">
                <img id="paymentQrImage" src="" style="width:200px;height:200px;object-fit:contain;display:block;" alt="QR thanh toán"/>
              </div>
              <div id="qrHint" style="font-size:11px;color:var(--text3);"></div>
            </div>

            <div style="padding:16px;">
              <div id="bankDetails">
                <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Thông tin tài khoản ngân hàng</div>
                <div class="bank-row"><span style="font-size:12px;color:var(--text2);">Ngân hàng</span><strong style="font-size:13px;color:var(--text);"><?= BANK_DISPLAY ?></strong></div>
                <div class="bank-row"><span style="font-size:12px;color:var(--text2);">Số tài khoản</span><div style="display:flex;align-items:center;gap:8px;"><strong style="font-size:14px;color:var(--accent);letter-spacing:.5px;"><?= BANK_ACCOUNT ?></strong><button type="button" class="copy-btn" onclick="copyText('<?= BANK_ACCOUNT ?>', this)">Sao chép</button></div></div>
                <div class="bank-row"><span style="font-size:12px;color:var(--text2);">Chủ tài khoản</span><strong style="font-size:13px;color:var(--text);"><?= BANK_NAME ?></strong></div>
              </div>
              <div id="walletDetails" style="display:none;">
                <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Thông tin cần nhập khi thanh toán</div>
                <div class="bank-row"><span style="font-size:12px;color:var(--text2);">Lưu ý</span><strong style="font-size:12px;color:var(--amber);text-align:right;">QR tĩnh có thể yêu cầu nhập số tiền thủ công</strong></div>
              </div>
              <div class="bank-row"><span style="font-size:12px;color:var(--text2);">Số tiền</span><div style="display:flex;align-items:center;gap:8px;"><strong style="font-size:14px;color:var(--accent);"><?= number_format($qrAmount) ?>đ</strong><button type="button" class="copy-btn" onclick="copyText('<?= $qrAmount ?>', this)">Sao chép</button></div></div>
              <div class="bank-row" style="margin-bottom:0;"><span style="font-size:12px;color:var(--text2);">Nội dung</span><div style="display:flex;align-items:center;gap:8px;"><strong style="font-size:12px;color:var(--text);"><?= htmlspecialchars($qrInfo) ?></strong><button type="button" class="copy-btn" onclick="copyText('<?= addslashes($qrInfo) ?>', this)">Sao chép</button></div></div>
            </div>
          </div>
          <div style="margin-top:10px;padding:10px 14px;background:rgba(247,169,79,.06);border:1px solid rgba(247,169,79,.2);border-radius:8px;"><div style="font-size:11px;color:var(--amber);">⚠ Chỉ bấm “Xác nhận” sau khi đã kiểm tra tiền thực sự vào tài khoản.</div></div>
        </div>

        <div class="form-group">
          <label class="form-label">📝 Ghi chú <span style="color:var(--text3);font-weight:400;">(không bắt buộc)</span></label>
          <textarea class="form-control" name="ghi_chu_tt" rows="2" placeholder="VD: Khach da chuyen khoan luc 10:30..."><?= htmlspecialchars($pendingNote) ?></textarea>
        </div>
        <div style="display:flex;gap:10px;margin-top:6px;">
          <button type="submit" id="btnSubmit" class="btn btn-success" style="flex:1;justify-content:center;padding:13px;font-size:14px;" onclick="return confirm('Xác nhận đã thu ' + '<?= number_format($qrAmount) ?>' + 'đ?')">✓ Xác nhận đã thu tiền mặt</button>
          <a href="index.php?controller=hoadon&action=index" class="btn btn-outline" style="padding:13px 18px;">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const paymentQrOptions = <?= json_encode($paymentQrOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const btnLabels = {tien_mat:'✓ Xác nhận đã thu tiền mặt',chuyen_khoan:'✓ Xác nhận đã nhận chuyển khoản',momo:'✓ Xác nhận đã nhận MoMo',vnpay:'✓ Xác nhận đã nhận VNPay'};
function selectPT(val) {
  document.querySelectorAll('input[name=phuong_thuc]').forEach(r => { if (r.value === val) r.checked = true; });
  document.querySelectorAll('.pt-option').forEach(el => el.classList.toggle('active', el.dataset.val === val));
  const qrSection = document.getElementById('qrSection');
  const option = paymentQrOptions[val];
  qrSection.style.display = option ? 'block' : 'none';
  if (option) {
    document.getElementById('paymentQrImage').src = option.url;
    document.getElementById('qrTitle').textContent = option.title;
    document.getElementById('qrHint').textContent = option.hint;
    document.getElementById('bankDetails').style.display = val === 'chuyen_khoan' ? 'block' : 'none';
    document.getElementById('walletDetails').style.display = val === 'chuyen_khoan' ? 'none' : 'block';
  }
  document.getElementById('btnSubmit').textContent = btnLabels[val] ?? '✓ Xác nhận đã thanh toán';
}
function copyText(text, btn) {
  const done = () => { const orig=btn.textContent; btn.textContent='✓ Đã copy'; btn.classList.add('copied'); setTimeout(()=>{btn.textContent=orig;btn.classList.remove('copied');},2000); };
  if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done).catch(()=>fallbackCopy(text,done)); else fallbackCopy(text,done);
}
function fallbackCopy(text, done) { const el=document.createElement('textarea');el.value=text;document.body.appendChild(el);el.select();document.execCommand('copy');document.body.removeChild(el);done(); }
document.addEventListener('DOMContentLoaded', () => selectPT('<?= htmlspecialchars($suggestedMethod, ENT_QUOTES) ?>'));
</script>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>

