<?php
$title = 'Hợp đồng của tôi';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>📄 Hợp đồng của tôi</h1>
    <p>Thông tin hợp đồng thuê phòng</p>
  </div>
  <a href="index.php?controller=user&action=index" class="btn btn-outline">← Phòng của tôi</a>
</div>

<?php if (!empty($_GET['err'])): ?>
<div class="msg-alert msg-error">⚠ <?= htmlspecialchars($_GET['err']) ?></div>
<?php endif; ?>

<?php if (!$hopDong): ?>
<div class="card" style="text-align:center;padding:56px;">
  <div style="font-size:48px;margin-bottom:12px;">📄</div>
  <div style="font-size:16px;font-weight:700;color:var(--text2);margin-bottom:6px;">Không có hợp đồng</div>
  <div style="font-size:13px;color:var(--text3);">Bạn chưa có hợp đồng hiệu lực. Liên hệ quản lý để biết thêm.</div>
</div>

<?php else:
  $kt   = strtotime($hopDong['ngay_ket_thuc']);
  $diff = (int)ceil(($kt - time()) / 86400);
  $avCols = ['#4f8ef7,#7c5cfc','#22c993,#2dd4bf','#f7a94f,#f75c5c','#7c5cfc,#f472b6'];
?>

<!-- TRẠNG THÁI -->
<div style="display:flex;align-items:center;gap:12px;padding:16px 20px;margin-bottom:18px;border-radius:12px;
            background:<?= $hopDong['trang_thai']==='hieu_luc' ? 'rgba(34,201,147,.06)' : 'rgba(247,169,79,.06)' ?>;
            border:1px solid <?= $hopDong['trang_thai']==='hieu_luc' ? 'rgba(34,201,147,.2)' : 'rgba(247,169,79,.2)' ?>;">
  <span style="font-size:28px;"><?= $hopDong['trang_thai']==='hieu_luc' ? '✅' : '⏳' ?></span>
  <div>
    <div style="font-size:15px;font-weight:700;color:<?= $hopDong['trang_thai']==='hieu_luc' ? 'var(--green)' : 'var(--amber)' ?>;">
      <?= $hopDong['trang_thai']==='hieu_luc' ? 'Hợp đồng đang hiệu lực' : 'Hợp đồng hết hạn' ?>
    </div>
    <div style="font-size:12px;color:var(--text3);">
      Hợp đồng #<?= str_pad($hopDong['id'], 4, '0', STR_PAD_LEFT) ?>
      <?php if($diff > 0 && $diff <= 30): ?>
        · <span style="color:var(--amber);font-weight:700;">⚠ Còn <?= $diff ?> ngày</span>
      <?php elseif($diff > 0): ?>
        · Còn <?= $diff ?> ngày
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- THÔNG TIN HỢP ĐỒNG -->
<div class="card" style="margin-bottom:18px;">
  <div class="card-header">
    <div class="card-title">📋 Thông tin hợp đồng</div>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Phòng</div>
        <div style="font-size:16px;font-weight:800;color:var(--text);"><?= htmlspecialchars($hopDong['so_phong']) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Ngày bắt đầu</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= date('d/m/Y', strtotime($hopDong['ngay_bat_dau'])) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Ngày kết thúc</div>
        <div style="font-size:15px;font-weight:700;color:<?= $diff <= 30 ? 'var(--amber)' : 'var(--text)' ?>;"><?= date('d/m/Y', $kt) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Tiền cọc</div>
        <div style="font-size:15px;font-weight:700;color:var(--amber);"><?= number_format($hopDong['tien_coc']) ?>đ</div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Ngày ký</div>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= date('d/m/Y', strtotime($hopDong['created_at'])) ?></div>
      </div>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text3);margin-bottom:6px;">Thời hạn</div>
        <?php
          $months = (int)round((strtotime($hopDong['ngay_ket_thuc']) - strtotime($hopDong['ngay_bat_dau'])) / (30*86400));
        ?>
        <div style="font-size:15px;font-weight:700;color:var(--text);"><?= $months ?> tháng</div>
      </div>
    </div>

    <?php if (!empty($hopDong['ghi_chu'])): ?>
    <div style="margin-top:14px;padding:12px 16px;background:rgba(79,142,247,.06);border:1px solid rgba(79,142,247,.15);border-radius:10px;">
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text3);margin-bottom:5px;">📝 Ghi chú</div>
      <div style="font-size:13px;color:var(--text2);line-height:1.6;"><?= nl2br(htmlspecialchars($hopDong['ghi_chu'])) ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- NGƯỜI Ở TRONG PHÒNG -->
<?php if (!empty($phong_nguoi)): ?>
<div class="card" style="margin-bottom:18px;">
  <div class="card-header">
    <div class="card-title">👥 Người ở trong phòng</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($phong_nguoi) ?>/<?= (int)($sucChuaPhong ?? 4) ?> người</span>
  </div>
  <div class="card-body" style="padding-top:12px;">
    <?php foreach ($phong_nguoi as $i => $n):
      $parts = explode(' ', $n['ho_ten']);
      $init  = implode('', array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8')), array_slice($parts, -2)));
      $col   = $avCols[$i % count($avCols)];
    ?>
    <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:12px;margin-bottom:8px;">
      <?php if (!empty($n['avatar'])): ?>
      <img src="<?= htmlspecialchars($n['avatar']) ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'"/>
      <?php else: ?>
      <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,<?= $col ?>);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0;"><?= $init ?></div>
      <?php endif; ?>
      <div style="flex:1;">
        <div style="font-size:14px;font-weight:700;color:var(--text);">
          <?= htmlspecialchars($n['ho_ten']) ?>
          <?php if ($n['la_chu_hop_dong']): ?>
          <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:rgba(79,142,247,.15);color:var(--accent);margin-left:6px;">👑 Chủ HĐ</span>
          <?php else: ?>
          <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:rgba(34,201,147,.12);color:var(--green);margin-left:6px;">Ở cùng</span>
          <?php endif; ?>
        </div>
        <div style="font-size:12px;color:var(--text3);margin-top:3px;">
          <?= !empty($n['sdt']) ? '📱 '.htmlspecialchars($n['sdt']) : '' ?>
          <?= !empty($n['cccd']) ? ' · 🪪 '.htmlspecialchars($n['cccd']) : '' ?>
          <?= !empty($n['gioi_tinh']) ? ' · '.($n['gioi_tinh']==='nam'?'👨 Nam':'👩 Nữ') : '' ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- YEU CAU THEM NGUOI O CUNG -->
<?php
  $daDayPhong = count($phong_nguoi) >= (int)($sucChuaPhong ?? 4);
  $ttOCungMap = [
      'cho_duyet' => ['Chờ duyệt', 'p-amber'],
      'da_duyet'  => ['Đã duyệt', 'p-green'],
      'tu_choi'   => ['Từ chối', 'p-red'],
      'da_huy'    => ['Đã hủy', 'p-purple'],
  ];
?>
<div class="card" style="margin-bottom:18px;">
  <div class="card-header">
    <div>
      <div class="card-title">➕ Thêm người ở cùng</div>
      <div class="card-sub">Gửi thông tin cho quản lý xét duyệt trước khi thêm vào phòng</div>
    </div>
    <?php if (($soYeuCauOCungChoDuyet ?? 0) > 0): ?><span class="pill p-amber">Đang chờ duyệt</span><?php endif; ?>
  </div>
  <div class="card-body">
    <?php if ($daDayPhong): ?>
      <div class="msg-alert msg-info" style="margin-bottom:0;">Phòng đã đủ số người đăng ký, không thể gửi thêm yêu cầu mới.</div>
    <?php elseif (($soYeuCauOCungChoDuyet ?? 0) > 0): ?>
      <div class="msg-alert msg-info" style="margin-bottom:0;">Bạn đang có yêu cầu thêm người ở cùng chờ duyệt. Vui lòng chờ quản lý xử lý hoặc hủy yêu cầu cũ.</div>
    <?php else: ?>
    <form method="POST" action="index.php?controller=user&action=guiYeuCauOCung">
            <?= Csrf::field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Họ tên <span style="color:var(--red);">*</span></label>
          <input class="form-control" type="text" name="ho_ten" maxlength="150" required placeholder="Nguyễn Văn A">
        </div>
        <div class="form-group">
          <label class="form-label">Số điện thoại</label>
          <input class="form-control" type="text" name="sdt" maxlength="20" placeholder="0901234567">
        </div>
        <div class="form-group">
          <label class="form-label">CCCD/CMND</label>
          <input class="form-control" type="text" name="cccd" maxlength="30" placeholder="079...">
        </div>
        <div class="form-group">
          <label class="form-label">Ngày sinh</label>
          <input class="form-control" type="date" name="ngay_sinh">
        </div>
        <div class="form-group">
          <label class="form-label">Giới tính</label>
          <select class="form-control" name="gioi_tinh">
            <option value="nam">Nam</option>
            <option value="nu">Nữ</option>
            <option value="khac">Khác</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Quê quán</label>
          <input class="form-control" type="text" name="que_quan" maxlength="255" placeholder="Tỉnh/Thành phố">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Lý do</label>
        <textarea class="form-control" name="ly_do" rows="3" maxlength="1000" placeholder="Ví dụ: người thân chuyển đến ở cùng..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Gửi cho quản lý duyệt</button>
    </form>
    <?php endif; ?>
  </div>

  <?php if (!empty($yeuCauOCungList)): ?>
  <div style="border-top:1px solid var(--border);padding:14px 20px;">
    <div style="font-size:12px;font-weight:800;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Lịch sử yêu cầu</div>
    <?php foreach ($yeuCauOCungList as $yc): $tt = $ttOCungMap[$yc['trang_thai']] ?? $ttOCungMap['cho_duyet']; ?>
    <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap;padding:12px 0;border-top:1px solid rgba(255,255,255,.04);">
      <div style="flex:1;min-width:220px;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:5px;">
          <span class="pill <?= $tt[1] ?>"><?= $tt[0] ?></span>
          <span style="font-size:11px;color:var(--text3);"><?= date('d/m/Y H:i', strtotime($yc['created_at'])) ?></span>
        </div>
        <div style="font-size:14px;font-weight:800;color:var(--text);"><?= htmlspecialchars($yc['ho_ten']) ?></div>
        <div style="font-size:12px;color:var(--text3);margin-top:3px;">
          <?= !empty($yc['sdt']) ? htmlspecialchars($yc['sdt']) : 'Chưa có SĐT' ?>
          <?= !empty($yc['cccd']) ? ' · CCCD: '.htmlspecialchars($yc['cccd']) : '' ?>
        </div>
        <?php if (!empty($yc['phan_hoi_ql'])): ?>
        <div style="font-size:12px;margin-top:7px;color:var(--text2);">Phản hồi quản lý: <?= nl2br(htmlspecialchars($yc['phan_hoi_ql'])) ?></div>
        <?php endif; ?>
      </div>
      <?php if ($yc['trang_thai'] === 'cho_duyet'): ?>
      <form method="POST" action="index.php?controller=user&action=huyYeuCauOCung">
            <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)$yc['id'] ?>">
        <button class="btn btn-outline btn-sm" onclick="return confirm('Hủy yêu cầu thêm người ở cùng này?')">Hủy yêu cầu</button>
      </form>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- XE ĐĂNG KÝ -->
<?php if (!empty($xeList)): ?>
<div class="card" style="margin-bottom:18px;">
  <div class="card-header">
    <div class="card-title">🚗 Xe đăng ký</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($xeList) ?>/4 xe</span>
  </div>
  <div class="card-body" style="padding-top:12px;">
    <?php
    $xeIcons = ['xe_may'=>'🏍','xe_dien'=>'⚡','xe_dap'=>'🚲'];
    $xeNames = ['xe_may'=>'Xe máy','xe_dien'=>'Xe điện','xe_dap'=>'Xe đạp'];
    foreach ($xeList as $xe):
    ?>
    <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:12px;margin-bottom:8px;">
      <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--amber),#f75c5c);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
        <?= $xeIcons[$xe['loai_xe']] ?? '🏍' ?>
      </div>
      <div style="flex:1;">
        <div style="font-size:15px;font-weight:800;color:var(--text);letter-spacing:1px;"><?= htmlspecialchars($xe['bien_so']) ?></div>
        <div style="font-size:12px;color:var(--text3);margin-top:2px;">
          <?= $xeNames[$xe['loai_xe']] ?? $xe['loai_xe'] ?>
          <?= !empty($xe['mau_sac']) ? ' · ' . htmlspecialchars($xe['mau_sac']) : '' ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- NỘI DUNG HỢP ĐỒNG -->
<div class="card" style="margin-bottom:18px;">
  <div class="card-header" style="cursor:pointer;" onclick="toggleHopDong()">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#4f8ef7,#7c5cfc);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">📜</div>
      <div>
        <div class="card-title">Nội dung hợp đồng</div>
        <div style="font-size:11px;color:var(--text3);margin-top:2px;">Nhấn để xem chi tiết điều khoản</div>
      </div>
    </div>
    <span id="hdToggleIcon" style="font-size:18px;color:var(--text3);transition:transform .25s;">▼</span>
  </div>

  <div id="ndHopDong" style="display:none;">
    <?php if (!empty($hopDong['noi_dung'])): ?>
    <div style="display:flex;justify-content:flex-end;gap:8px;padding:14px 20px 0;">
      <button onclick="printHopDong()" class="btn btn-outline btn-sm" style="font-size:12px;">🖨 In hợp đồng</button>
    </div>
    <div id="hopDongContent"
         style="padding:24px 28px;font-family:'Times New Roman',serif;font-size:14px;line-height:2;
                color:var(--text);white-space:pre-wrap;border-top:1px solid var(--border);margin-top:12px;">
<?= htmlspecialchars($hopDong['noi_dung']) ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:40px;color:var(--text3);">
      <div style="font-size:40px;margin-bottom:12px;">📄</div>
      <div style="font-size:14px;font-weight:600;color:var(--text2);">Chưa có nội dung hợp đồng</div>
      <div style="font-size:12px;margin-top:6px;line-height:1.7;">
        Liên hệ quản lý để được cung cấp bản hợp đồng đầy đủ.
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleHopDong() {
    const box  = document.getElementById('ndHopDong');
    const icon = document.getElementById('hdToggleIcon');
    const open = box.style.display === 'none';
    box.style.display    = open ? 'block' : 'none';
    icon.style.transform = open ? 'rotate(180deg)' : 'rotate(0)';
}

function printHopDong() {
    const el  = document.getElementById('hopDongContent');
    if (!el) return;
    const w = window.open('', '_blank', 'width=800,height=900');
    w.document.write(`<!DOCTYPE html><html><head>
        <title>Hợp đồng #<?= str_pad($hopDong['id'], 4, '0', STR_PAD_LEFT) ?></title>
        <link rel="stylesheet" href="frontend/assets/css/user-hop-dong.css"></head><body>
        <pre>${el.innerText}</pre>
        </body></html>`);
    w.document.close();
    setTimeout(() => w.print(), 400);
}
</script>

<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
