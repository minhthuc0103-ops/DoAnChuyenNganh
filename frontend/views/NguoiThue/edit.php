<?php
$title = 'Chỉnh sửa người thuê';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title"><h1>Chỉnh sửa người thuê</h1><p><?= htmlspecialchars($data['ho_ten']??'') ?></p></div>
  <a href="index.php?controller=nguoithue&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:600px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin cá nhân</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="index.php?controller=nguoithue&action=edit&id=<?= $data['id'] ?>" enctype="multipart/form-data">
            <?= Csrf::field() ?>

        <!-- AVATAR UPLOAD -->
        <div class="form-group" style="display:flex;align-items:center;gap:16px;padding:14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);margin-bottom:20px;">
          <?php
            $colors=['#4f8ef7,#7c5cfc','#22c993,#4f8ef7','#f7a94f,#f75c5c','#7c5cfc,#f472b6'];
            $col = $colors[$data['id'] % count($colors)];
            $init = mb_strtoupper(mb_substr($data['ho_ten'],0,1,'UTF-8'));
          ?>
          <?php if(!empty($data['avatar'])): ?>
          <img src="<?= htmlspecialchars($data['avatar']) ?>" id="avatarPreview"
               style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;"
               onerror="this.style.display='none';document.getElementById('avatarInit').style.display='flex'"/>
          <div id="avatarInit" style="display:none;width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,<?= $col ?>);align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;flex-shrink:0;"><?= $init ?></div>
          <?php else: ?>
          <div id="avatarInit" style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,<?= $col ?>);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;flex-shrink:0;"><?= $init ?></div>
          <img id="avatarPreview" style="display:none;width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;"/>
          <?php endif; ?>
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;">Ảnh đại diện</div>
            <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--card);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;color:var(--text2);transition:border-color .2s;"
                   onmouseover="this.style.borderColor='rgba(79,142,247,.4)'" onmouseout="this.style.borderColor='var(--border)'">
              📷 Chọn ảnh
              <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
            </label>
            <div style="font-size:11px;color:var(--text3);margin-top:5px;">JPG, PNG · Tối đa 2MB</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="ho_ten"
                 value="<?= htmlspecialchars($data['ho_ten']??'') ?>" required/>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số CCCD / CMND</label>
            <input class="form-control" type="text" name="cccd"
                   value="<?= htmlspecialchars($data['cccd']??'') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input class="form-control" type="tel" name="sdt"
                   value="<?= htmlspecialchars($data['sdt']??'') ?>"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Địa chỉ thường trú</label>
          <textarea class="form-control" name="dia_chi"><?= htmlspecialchars($data['dia_chi']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Liên kết tài khoản đăng nhập</label>
          <select class="form-control" name="account_id">
            <option value="">— Chưa liên kết —</option>
            <?php foreach ($accounts??[] as $acc): ?>
            <option value="<?= $acc['id'] ?>" <?= ($data['account_id']??0)==$acc['id']?'selected':'' ?>>
              <?= htmlspecialchars($acc['username']) ?> · <?= htmlspecialchars($acc['ho_ten']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div style="font-size:11px;color:var(--text3);margin-top:5px;">Người thuê sẽ xem được hợp đồng và hóa đơn của mình khi đăng nhập</div>
        </div>
        <div style="display:flex;gap:10px;padding-top:4px;">
          <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
          <a href="index.php?controller=nguoithue&action=index" class="btn btn-outline">Hủy</a>
          <button type="submit" form="formXoaNguoiThue" class="btn btn-danger" style="cursor:pointer;margin-left:auto">🗑 Xóa</button>
        </div>
      </form>
      <form id="formXoaNguoiThue" method="POST" action="index.php?controller=nguoithue&action=delete" onsubmit="return confirm('Xóa người thuê này?')">
        <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $data['id'] ?>">
      </form>
    </div>
  </div>

  <?php
    $loaiXe = ['xe_may'=>'🏍 Xe máy','xe_dien'=>'⚡ Xe điện','xe_dap'=>'🚲 Xe đạp','o_to'=>'🚗 Ô tô'];
    $xeDangKy  = $xeDangKy  ?? [];
    $xeHopDong = $xeHopDong ?? [];
    // Biển số đang gửi trong hợp đồng còn hiệu lực
    $bsDangGui = [];
    foreach ($xeHopDong as $xh) {
        if (($xh['hd_trang_thai'] ?? '') === 'hieu_luc') $bsDangGui[strtoupper(trim($xh['bien_so']))] = $xh['so_phong'] ?? '';
    }
  ?>
  <!-- XE ĐÃ ĐĂNG KÝ (người thuê tự đăng ký trong hồ sơ; tự điền vào hợp đồng khi tạo) -->
  <div class="card" style="margin-top:16px;">
    <div class="card-header">
      <div class="card-title">🏍 Xe đã đăng ký</div>
      <div class="card-sub"><?= count($xeDangKy) ?> xe người thuê tự đăng ký trong hồ sơ</div>
    </div>
    <div class="card-body">
      <?php if (empty($data['account_id'])): ?>
        <div style="font-size:13px;color:var(--text3);">Chưa liên kết tài khoản đăng nhập nên chưa có xe tự đăng ký.</div>
      <?php elseif (empty($xeDangKy)): ?>
        <div style="font-size:13px;color:var(--text3);">Người thuê chưa đăng ký xe nào trong hồ sơ.</div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php foreach ($xeDangKy as $x): $key = strtoupper(trim($x['bien_so'])); ?>
          <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
            <div style="font-size:15px;font-weight:800;letter-spacing:.5px;font-family:monospace;color:var(--text);"><?= htmlspecialchars($x['bien_so']) ?></div>
            <div style="flex:1;min-width:0;font-size:12px;color:var(--text3);">
              <?= $loaiXe[$x['loai_xe']] ?? htmlspecialchars($x['loai_xe']) ?><?= !empty($x['mau_sac']) ? ' · ' . htmlspecialchars($x['mau_sac']) : '' ?>
            </div>
            <?php if (isset($bsDangGui[$key])): ?>
            <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(34,201,147,.12);color:var(--green);">✓ Đang gửi tại phòng <?= htmlspecialchars($bsDangGui[$key]) ?></span>
            <?php else: ?>
            <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(247,169,79,.12);color:var(--amber);">Chưa vào hợp đồng</span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- XE TRONG HỢP ĐỒNG -->
  <?php if (!empty($xeHopDong)): ?>
  <div class="card" style="margin-top:16px;">
    <div class="card-header"><div class="card-title">📄 Xe trong hợp đồng</div><div class="card-sub"><?= count($xeHopDong) ?> xe</div></div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($xeHopDong as $x): $hl = ($x['hd_trang_thai'] ?? '') === 'hieu_luc'; ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);<?= $hl ? '' : 'opacity:.6;' ?>">
          <div style="font-size:15px;font-weight:800;letter-spacing:.5px;font-family:monospace;color:var(--text);"><?= htmlspecialchars($x['bien_so']) ?></div>
          <div style="flex:1;min-width:0;font-size:12px;color:var(--text3);">
            <?= $loaiXe[$x['loai_xe']] ?? htmlspecialchars((string)$x['loai_xe']) ?><?= !empty($x['mau_sac']) ? ' · ' . htmlspecialchars($x['mau_sac']) : '' ?>
            <?php if (!empty($x['so_phong'])): ?> · Phòng <?= htmlspecialchars($x['so_phong']) ?><?= !empty($x['ten_khu']) ? ' (' . htmlspecialchars($x['ten_khu']) . ')' : '' ?><?php endif; ?>
          </div>
          <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;<?= $hl ? 'background:rgba(34,201,147,.12);color:var(--green);' : 'background:rgba(156,163,175,.15);color:#9ca3af;' ?>"><?= $hl ? 'Hợp đồng hiệu lực' : 'Hợp đồng đã kết thúc' ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>
<script src="frontend/assets/js/nguoithue-edit.js"></script>
