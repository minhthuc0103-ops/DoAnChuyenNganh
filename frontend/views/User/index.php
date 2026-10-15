<?php
$title = 'Phòng của tôi';
require_once 'frontend/views/Layouts/header.php';
?>
<link rel="stylesheet" href="frontend/assets/css/user-index.css">

<div class="page-header">
  <div class="page-title">
    <h1>Phòng của tôi</h1>
    <p>Thông tin hợp đồng và hóa đơn phòng trọ</p>
  </div>
  <?php if (!empty($nguoi_thue['hop_dong_id'])): ?>
  <div class="header-actions">
    <a href="index.php?controller=chuyenphong&action=index" class="btn btn-primary">🔄 Yêu cầu chuyển phòng</a>
  </div>
  <?php endif; ?>
</div>

<?php if (!empty($thong_bao_list)): ?>
<div class="card" style="margin-bottom:22px;border-color:rgba(34,201,147,.25);">
  <div class="card-header">
    <div>
      <div class="card-title">Thong bao moi</div>
      <div class="card-sub"><?= count($thong_bao_list) ?> thong bao chua doc</div>
    </div>
    <a href="index.php?controller=thongbao&action=docTatCa" class="btn btn-outline btn-sm">Danh dau da doc</a>
  </div>
  <div style="display:flex;flex-direction:column;">
    <?php foreach ($thong_bao_list as $tb): ?>
    <a href="index.php?controller=thongbao&action=xem&id=<?= $tb['id'] ?>"
       style="display:flex;gap:12px;align-items:flex-start;padding:14px 18px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;">
      <div style="width:38px;height:38px;border-radius:10px;background:rgba(34,201,147,.12);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
        <?= $tb['loai'] === 'tien_phong' ? '💰' : '📢' ?>
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:800;color:var(--text);margin-bottom:3px;"><?= htmlspecialchars($tb['tieu_de']) ?></div>
        <div style="font-size:12px;color:var(--text2);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= htmlspecialchars($tb['noi_dung']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:5px;"><?= date('d/m/Y H:i', strtotime($tb['created_at'])) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!$nguoi_thue): ?>
<div class="card" style="text-align:center;padding:64px 32px;">
  <div style="font-size:52px;margin-bottom:16px;">🔗</div>
  <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:10px;">Chưa liên kết hồ sơ người thuê</div>
  <div style="font-size:14px;color:var(--text2);max-width:400px;margin:0 auto;line-height:1.8;">
    Tài khoản của bạn chưa được liên kết với hồ sơ người thuê.<br>
    Vui lòng liên hệ quản lý để được cập nhật.
  </div>
</div>

<?php elseif (empty($nguoi_thue['hop_dong_id'])): ?>
<div style="display:flex;align-items:center;gap:16px;padding:20px 24px;background:rgba(247,169,79,.07);border:1px solid rgba(247,169,79,.25);border-radius:var(--radius);margin-bottom:22px;">
  <span style="font-size:28px;">📋</span>
  <div>
    <div style="font-size:15px;font-weight:700;color:var(--amber);">Không có hợp đồng hiệu lực</div>
    <div style="font-size:13px;color:var(--text2);margin-top:3px;">Vui lòng liên hệ quản lý để gia hạn hoặc tạo hợp đồng mới.</div>
  </div>
</div>

<?php else:
  $kt   = strtotime($nguoi_thue['ngay_ket_thuc']);
  $diff = (int)ceil(($kt - time()) / 86400);
  $avCols = ['#4f8ef7,#7c5cfc','#22c993,#2dd4bf','#f7a94f,#f75c5c','#7c5cfc,#f472b6'];
?>

<!-- HERO BANNER -->
<div class="user-hero">
  <div style="position:relative;z-index:1;">
    <div style="font-size:13px;color:rgba(255,255,255,.75);margin-bottom:6px;">
      Xin chào, <strong><?= htmlspecialchars($_SESSION['ho_ten'] ?? $_SESSION['user']) ?></strong>!
    </div>
    <div class="hero-room">Phòng <?= htmlspecialchars($nguoi_thue['so_phong']) ?></div>
    <div style="font-size:13px;color:rgba(255,255,255,.8);margin-top:4px;">
      🏘 <?= htmlspecialchars($nguoi_thue['ten_khu'] ?? '') ?>
      &nbsp;·&nbsp;
      <?php if ($diff > 30): ?>
        <span>Còn <?= $diff ?> ngày đến hạn HĐ</span>
      <?php elseif ($diff > 0): ?>
        <span style="color:#fde68a;font-weight:700;">⚠ Còn <?= $diff ?> ngày đến hạn HĐ</span>
      <?php else: ?>
        <span style="color:#fca5a5;font-weight:700;">Hợp đồng đã quá hạn</span>
      <?php endif; ?>
    </div>
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-val"><?= number_format($nguoi_thue['gia'] / 1000000, 1) ?>M/tháng</div>
        <div class="hero-stat-lbl">Giá phòng</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-val"><?= date('d/m/Y', strtotime($nguoi_thue['ngay_bat_dau'])) ?></div>
        <div class="hero-stat-lbl">Ngày vào ở</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-val"><?= date('d/m/Y', $kt) ?></div>
        <div class="hero-stat-lbl">Hạn hợp đồng</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-val"><?= number_format($nguoi_thue['tien_coc'] / 1000000, 1) ?>M</div>
        <div class="hero-stat-lbl">Tiền cọc</div>
      </div>
    </div>
  </div>
</div>

<!-- ẢNH PHÒNG -->
<?php
  $phongModel = new PhongModel();
  $phongData  = $phongModel->getById((int)$nguoi_thue['phong_id']);
  $anhPhong   = [];
  if (!empty($phongData['anh_phong'])) {
      $anhPhong = normalize_room_images($phongData['anh_phong']);
  }
?>
<?php if (!empty($anhPhong)): ?>
<div class="card" style="margin-bottom:22px;">
  <div class="card-header">
    <div class="card-title">📷 Hình ảnh phòng</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($anhPhong) ?> ảnh</span>
  </div>
  <div class="card-body" style="padding:12px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
      <?php foreach ($anhPhong as $idx => $anh): ?>
      <div style="position:relative;border-radius:12px;overflow:hidden;aspect-ratio:4/3;cursor:pointer;border:1px solid var(--border);"
           onclick="openImgModal('<?= htmlspecialchars($anh) ?>')">
        <img src="<?= htmlspecialchars($anh) ?>"
             style="width:100%;height:100%;object-fit:cover;transition:transform .2s;"
             onmouseover="this.style.transform='scale(1.05)'"
             onmouseout="this.style.transform='scale(1)'"
             onerror="this.parentElement.style.display='none'"
             alt="Ảnh phòng <?= $idx+1 ?>"/>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<div id="imgModal" onclick="closeImgModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:1300;align-items:center;justify-content:center;padding:20px;cursor:zoom-out;backdrop-filter:blur(8px);">
  <img id="imgModalSrc" src="" style="max-width:90%;max-height:90%;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.5);object-fit:contain;"/>
  <button onclick="closeImgModal()" style="position:absolute;top:20px;right:20px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:18px;">✕</button>
</div>
<script src="frontend/assets/js/user-index.js"></script>
<?php endif; ?>

<!-- HỢP ĐỒNG CHI TIẾT -->
<div class="card" style="margin-bottom:22px;">
  <div class="card-header">
    <div class="card-title">📄 Chi tiết hợp đồng #<?= str_pad($nguoi_thue['hop_dong_id'], 4, '0', STR_PAD_LEFT) ?></div>
    <span class="pill p-green">Đang hiệu lực</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Số phòng</div>
        <div style="font-size:16px;font-weight:800;color:var(--text);">Phòng <?= htmlspecialchars($nguoi_thue['so_phong']) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Khu trọ</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= htmlspecialchars($nguoi_thue['ten_khu'] ?? '—') ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Ngày bắt đầu</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= date('d/m/Y', strtotime($nguoi_thue['ngay_bat_dau'])) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Ngày kết thúc</div>
        <div style="font-size:15px;font-weight:700;color:<?= $diff <= 30 ? 'var(--amber)' : 'var(--text)' ?>;"><?= date('d/m/Y', $kt) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Tiền phòng/tháng</div>
        <div style="font-size:15px;font-weight:700;color:var(--accent);"><?= number_format($nguoi_thue['gia']) ?>đ</div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Tiền cọc</div>
        <div style="font-size:15px;font-weight:700;color:var(--amber);"><?= number_format($nguoi_thue['tien_coc']) ?>đ</div>
      </div>
    </div>
    <?php if (!empty($nguoi_thue['ghi_chu'])): ?>
    <div style="margin-top:14px;padding:12px 16px;background:rgba(79,142,247,.06);border:1px solid rgba(79,142,247,.15);border-radius:10px;">
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text3);margin-bottom:5px;">📝 Ghi chú</div>
      <div style="font-size:13px;color:var(--text2);line-height:1.6;"><?= htmlspecialchars($nguoi_thue['ghi_chu']) ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- NGƯỜI Ở TRONG PHÒNG -->
<?php if (!empty($phong_nguoi)): ?>
<div class="card" style="margin-bottom:22px;">
  <div class="card-header">
    <div class="card-title">👥 Người ở trong phòng</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($phong_nguoi) ?>/4 người</span>
  </div>
  <div class="card-body" style="padding-top:12px;">
    <?php foreach ($phong_nguoi as $i => $n):
      $parts = explode(' ', $n['ho_ten']);
      $init  = implode('', array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8')), array_slice($parts, -2)));
      $col   = $avCols[$i % count($avCols)];
    ?>
    <div class="resident-row">
      <?php if (!empty($n['avatar'])): ?>
      <img src="<?= htmlspecialchars($n['avatar']) ?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'"/>
      <?php else: ?>
      <div class="resident-av" style="background:linear-gradient(135deg,<?= $col ?>);"><?= $init ?></div>
      <?php endif; ?>
      <div style="flex:1;">
        <div class="resident-name">
          <?= htmlspecialchars($n['ho_ten']) ?>
          <?php if ($n['la_chu_hop_dong']): ?>
          <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:rgba(79,142,247,.15);color:var(--accent);border:1px solid rgba(79,142,247,.25);margin-left:6px;">👑 Chủ HĐ</span>
          <?php else: ?>
          <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:rgba(34,201,147,.12);color:var(--green);border:1px solid rgba(34,201,147,.2);margin-left:6px;">Ở cùng</span>
          <?php endif; ?>
        </div>
        <div class="resident-sub">
          <?= !empty($n['sdt'])  ? '📱 '.htmlspecialchars($n['sdt'])  : '' ?>
          <?= !empty($n['cccd']) ? ' &nbsp;· 🪪 '.htmlspecialchars($n['cccd']) : '' ?>
          <?= !empty($n['ngay_sinh']) ? ' &nbsp;· 🎂 '.htmlspecialchars($n['ngay_sinh']) : '' ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- HÓA ĐƠN HÀNG THÁNG -->
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">⚡ Hóa đơn hàng tháng</div>
      <?php $chuaTT = count(array_filter($hoa_don_list, fn($x) => $x['trang_thai'] === 'chua_tt')); ?>
      <div class="card-sub"><?= count($hoa_don_list) ?> hóa đơn<?= $chuaTT ? " · <span style='color:var(--red);font-weight:700;'>$chuaTT chưa thanh toán</span>" : '' ?></div>
    </div>
  </div>
  <?php if (!empty($hoa_don_list)): ?>
  <div style="overflow-x:auto;">
    <table class="tbl">
      <thead>
        <tr>
          <th>Tháng</th><th>Tiền phòng</th><th>Tiền điện</th>
          <th>Tiền nước</th><th>Tổng cộng</th><th>Trạng thái</th>
          <th style="text-align:center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hoa_don_list as $hd): ?>
        <tr>
          <td><span class="td-name">Tháng <?= $hd['thang'] ?>/<?= $hd['nam'] ?></span></td>
          <td><?= number_format($hd['tien_phong']) ?>đ</td>
          <td><?= number_format($hd['tien_dien']) ?>đ<div style="font-size:10px;color:var(--text3);"><?= $hd['chi_so_dien_cu'] ?>→<?= $hd['chi_so_dien_moi'] ?> kWh</div></td>
          <td><?= number_format($hd['tien_nuoc']) ?>đ<div style="font-size:10px;color:var(--text3);"><?= $hd['chi_so_nuoc_cu'] ?>→<?= $hd['chi_so_nuoc_moi'] ?> m³</div></td>
          <td><strong style="color:var(--text);font-size:14px;"><?= number_format($hd['tong_tien']) ?>đ</strong></td>
          <td>
            <?php if ($hd['trang_thai'] === 'da_tt'): ?><span class="pill p-green">Đã TT</span>
            <?php elseif (!empty($hd['pending_count'])): ?><span class="pill p-amber">Chờ xác nhận</span>
            <?php else: ?><span class="pill p-red">Chưa TT</span>
            <?php endif; ?>
          </td>
          <td style="text-align:center;">
            <?php if ($hd['trang_thai'] === 'chua_tt' && empty($hd['pending_count'])): ?>
            <button type="button" class="btn btn-primary btn-xs" onclick="openPayModal(<?= $hd['id'] ?>, '<?= htmlspecialchars($hd['so_phong'] ?? $nguoi_thue['so_phong']) ?>', <?= (int)$hd['tong_tien'] ?>, <?= $hd['thang'] ?>, <?= $hd['nam'] ?>)">💳 Thanh toán</button>
            <?php elseif ($hd['trang_thai'] === 'chua_tt'): ?>
            <span style="font-size:11px;color:var(--amber);font-weight:700;">Chờ quản lý xác nhận</span>
            <?php else: ?><span style="font-size:11px;color:var(--green);">✓</span>
            <?php endif; ?>
            <a href="index.php?controller=hoadon&action=chiTiet&id=<?= $hd['id'] ?>" class="btn btn-outline btn-xs" style="margin-top:4px;">👁 Chi tiết</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div style="text-align:center;padding:52px;color:var(--text3);">
    <div style="font-size:40px;margin-bottom:12px;">📄</div>
    <div style="font-size:14px;color:var(--text2);">Chưa có hóa đơn nào</div>
  </div>
  <?php endif; ?>
</div>

<!-- MODAL THANH TOÁN QR -->
<?php require_once 'backend/config/payment.php'; ?>
<div id="payModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:1300;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(6px);" onclick="if(event.target===this)closePayModal()">
  <div style="background:var(--card);border:1px solid rgba(79,142,247,.2);border-radius:20px;width:100%;max-width:460px;box-shadow:0 24px 64px rgba(0,0,0,.6);animation:payIn .2s ease both;overflow:hidden;max-height:90vh;overflow-y:auto;">
    <div style="background:linear-gradient(135deg,#4f8ef7,#7c5cfc);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;">
      <div><div style="font-size:16px;font-weight:800;color:#fff;">💳 Thanh toán hóa đơn</div><div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:2px;" id="payModalSub"></div></div>
      <button onclick="closePayModal()" style="background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:16px;">✕</button>
    </div>
    <div style="padding:24px;">
      <div style="text-align:center;margin-bottom:22px;padding:20px;border:1px solid rgba(79,142,247,.2);border-radius:14px;background:rgba(79,142,247,.04);">
        <div style="font-size:12px;color:var(--text3);margin-bottom:4px;">Tổng tiền phải thu</div><div style="font-size:32px;font-weight:800;color:var(--accent);" id="payModalAmount"></div><div style="font-size:12px;color:var(--text3);margin-top:4px;" id="payModalThang"></div>
      </div>
      <div style="margin-bottom:20px;">
        <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">💳 Phương thức thanh toán</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <label class="pay-method active" data-val="tien_mat" onclick="selectPayMethod(this)"><span style="font-size:24px;">💵</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">Tiền mặt</div><div style="font-size:11px;color:var(--text3);">Thu trực tiếp</div></div></label>
          <label class="pay-method" data-val="chuyen_khoan" onclick="selectPayMethod(this)"><span style="font-size:24px;">🏦</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">Chuyển khoản</div><div style="font-size:11px;color:var(--text3);">QR Banking</div></div></label>
          <label class="pay-method" data-val="momo" onclick="selectPayMethod(this)"><span style="font-size:24px;">📱</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">MoMo</div><div style="font-size:11px;color:var(--text3);">QR riêng</div></div></label>
          <label class="pay-method" data-val="vnpay" onclick="selectPayMethod(this)"><span style="font-size:24px;">🔵</span><div><div style="font-size:13px;font-weight:700;color:var(--text);">VNPay</div><div style="font-size:11px;color:var(--text3);">VNPay-QR</div></div></label>
        </div>
      </div>
      <div id="payQRSection" style="display:none;margin-bottom:20px;">
        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;">
          <div id="payQrTitle" style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;">📱 Quét mã QR để thanh toán</div>
          <div style="background:#fff;border-radius:12px;padding:14px;display:inline-block;margin-bottom:14px;"><img id="payModalQR" src="" style="width:180px;height:180px;object-fit:contain;" alt="QR"/></div>
          <div id="payBankInfo" style="text-align:left;font-size:12px;color:var(--text2);line-height:2;">
            <div style="display:flex;justify-content:space-between;"><span>Ngân hàng:</span><strong style="color:var(--text);"><?= BANK_DISPLAY ?></strong></div>
            <div style="display:flex;justify-content:space-between;"><span>Số TK:</span><strong style="color:var(--text);"><?= BANK_ACCOUNT ?></strong></div>
          </div>
          <div style="text-align:left;font-size:12px;color:var(--text2);line-height:2;">
            <div style="display:flex;justify-content:space-between;"><span>Nội dung:</span><strong style="color:var(--accent);" id="payModalND"></strong></div>
          </div>
          <div id="payQrHint" style="font-size:11px;color:var(--amber);margin-top:10px;text-align:left;"></div>
        </div>
      </div>
      <form id="userPayForm" method="POST" action="" style="display:flex;gap:10px;">
            <?= Csrf::field() ?>
        <input type="hidden" name="phuong_thuc" id="userPayMethod" value="tien_mat"/>
        <button type="submit" class="btn btn-success" style="flex:1;justify-content:center;padding:12px;" onclick="return confirm('Xác nhận bạn đã gửi thanh toán hóa đơn này?')">✓ Tôi đã thanh toán</button>
        <button type="button" onclick="closePayModal()" class="btn btn-outline" style="padding:12px 18px;">Đóng</button>
      </form>
    </div>
  </div>
</div>
<link rel="stylesheet" href="frontend/assets/css/user-index-2.css">
<script>
let currentBankQrUrl='';
const walletQrUrls={momo:'<?= MOMO_QR_IMAGE ?>',vnpay:'<?= VNPAY_QR_IMAGE ?>'};
function openPayModal(hdId,soPhong,tongTien,thang,nam){
  const nd='TT phong '+soPhong+' T'+thang+'/'+nam;
  document.getElementById('userPayForm').action='index.php?controller=hoadon&action=xacNhanThanhToanNguoiThue&id='+hdId;
  document.getElementById('userPayMethod').value='tien_mat';
  document.getElementById('payModalSub').textContent='Phòng '+soPhong;
  document.getElementById('payModalAmount').textContent=tongTien.toLocaleString('vi-VN')+'đ';
  document.getElementById('payModalThang').textContent='Tháng '+thang+'/'+nam;
  document.getElementById('payModalND').textContent=nd;
  currentBankQrUrl='https://img.vietqr.io/image/<?= BANK_ID ?>-<?= BANK_ACCOUNT ?>-<?= VIETQR_TEMPLATE ?>.jpg?amount='+tongTien+'&addInfo='+encodeURIComponent(nd)+'&accountName='+encodeURIComponent('<?= BANK_NAME ?>');
  document.getElementById('payModalQR').src=currentBankQrUrl;
  document.querySelectorAll('.pay-method').forEach(el=>el.classList.remove('active'));
  document.querySelector('.pay-method[data-val="tien_mat"]').classList.add('active');
  document.getElementById('payQRSection').style.display='none';
  document.getElementById('payModal').style.display='flex';document.body.style.overflow='hidden';
}
function closePayModal(){document.getElementById('payModal').style.display='none';document.body.style.overflow='';}
function selectPayMethod(el){
  const val=el.dataset.val;
  document.querySelectorAll('.pay-method').forEach(e=>e.classList.remove('active'));el.classList.add('active');
  document.getElementById('userPayMethod').value=val;
  const section=document.getElementById('payQRSection');section.style.display=val==='tien_mat'?'none':'block';
  if(val==='chuyen_khoan'){
    document.getElementById('payModalQR').src=currentBankQrUrl;document.getElementById('payBankInfo').style.display='block';
    document.getElementById('payQrTitle').textContent='🏦 Quét QR Banking để chuyển khoản';document.getElementById('payQrHint').textContent='QR ngân hàng đã điền sẵn số tiền và nội dung.';
  }else if(walletQrUrls[val]){
    document.getElementById('payModalQR').src=walletQrUrls[val];document.getElementById('payBankInfo').style.display='none';
    document.getElementById('payQrTitle').textContent=val==='momo'?'📱 Quét QR bằng MoMo':'🔵 Quét mã VNPay-QR';document.getElementById('payQrHint').textContent='QR tĩnh: hãy nhập đúng số tiền và nội dung hiển thị trước khi xác nhận.';
  }
}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closePayModal();});
</script>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

