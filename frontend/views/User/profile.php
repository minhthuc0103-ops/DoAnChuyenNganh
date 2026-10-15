<?php require_once 'frontend/views/Layouts/header.php'; ?>
<?php
$ho_ten  = htmlspecialchars($account['ho_ten']  ?? '');
$email   = htmlspecialchars($account['email']   ?? '');
$sdt     = htmlspecialchars($account['sdt']     ?? '');
$username= htmlspecialchars($account['username'] ?? '');
$dia_chi  = htmlspecialchars($nguoi_thue['dia_chi']   ?? '');
$cccd     = htmlspecialchars($nguoi_thue['cccd']      ?? '');
$ngay_sinh= $nguoi_thue['ngay_sinh'] ?? '';
$avatar  = htmlspecialchars($nguoi_thue['avatar']  ?? '');
// Avatar chữ cái
$parts = explode(' ', $account['ho_ten'] ?? 'U');
$init  = mb_strtoupper(mb_substr(end($parts), 0, 1, 'UTF-8'), 'UTF-8');
?>
<link rel="stylesheet" href="frontend/assets/css/user-profile.css">

<div class="page-header">
  <div class="page-title">
    <h1>Hồ sơ của tôi</h1>
    <p>Thông tin tài khoản & bảo mật</p>
  </div>
</div>

<?php if($error): ?>
<div class="msg-alert msg-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="msg-alert msg-success" style="margin-bottom:16px;">✓ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if(!empty($_GET['required'])): ?>
<div class="msg-alert msg-info" style="margin-bottom:16px;">Vui lòng hoàn thành hồ sơ bắt buộc: số điện thoại, email, số CCCD/CMND và ảnh CCCD/CMND 2 mặt trước khi sử dụng hệ thống.</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:start;">

  <!-- ── Cột trái: Avatar + thông tin tóm tắt ── -->
  <div style="display:flex;flex-direction:column;gap:14px;">
    <div class="card" style="padding:24px;text-align:center;">

      <!-- Avatar có thể click để đổi -->
      <form method="POST" enctype="multipart/form-data" id="avatarForm">
            <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="update_avatar"/>
        <label class="av-wrap" title="Click để đổi ảnh đại diện">
          <?php if($avatar): ?>
          <img id="avatarImg" src="<?= e($avatar) ?>" style="width:88px;height:88px;border-radius:50%;object-fit:cover;border:3px solid rgba(79,142,247,.35);"
               onerror="this.style.display='none';document.getElementById('avatarInitial').style.display='flex'"/>
          <div id="avatarInitial" class="profile-avatar" style="display:none;position:absolute;inset:0;margin:0;"><?= $init ?></div>
          <?php else: ?>
          <div id="avatarInitial" class="profile-avatar" style="margin:0;"><?= $init ?></div>
          <img id="avatarImg" style="display:none;width:88px;height:88px;border-radius:50%;object-fit:cover;border:3px solid rgba(79,142,247,.35);position:absolute;inset:0;"/>
          <?php endif; ?>
          <div class="av-overlay">📷</div>
          <input type="file" name="avatar" accept="image/*" style="display:none;"
                 onchange="previewAvatar(this)"/>
        </label>
        <div id="avatarActions" style="display:none;margin-top:8px;gap:6px;justify-content:center;">
          <button type="submit" class="btn btn-primary" style="padding:4px 14px;font-size:12px;">✓ Lưu</button>
          <button type="button" onclick="cancelAvatar()" style="padding:4px 12px;font-size:12px;border-radius:8px;border:1px solid var(--border);background:var(--card);color:var(--text2);cursor:pointer;">✕</button>
        </div>
      </form>

      <div style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:4px;margin-top:8px;"><?= e($ho_ten) ?></div>
      <div style="font-size:12px;color:var(--text3);margin-bottom:12px;">@<?= e($username) ?></div>
      <span style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(34,201,147,.12);color:var(--green);border:1px solid rgba(34,201,147,.25);">
        👤 Người thuê
      </span>
    </div>

    <!-- Thông tin nhanh -->
    <div class="card" style="padding:16px;">
      <div style="font-size:12px;font-weight:700;color:var(--text3);margin-bottom:12px;text-transform:uppercase;letter-spacing:.6px;">Thông tin liên hệ</div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
          <span style="font-size:16px;">📱</span>
          <span style="color:<?= $sdt ? 'var(--text)' : 'var(--text3)' ?>;"><?= $sdt ?: 'Chưa cập nhật' ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
          <span style="font-size:16px;">✉️</span>
          <span style="color:<?= $email ? 'var(--text)' : 'var(--text3)' ?>;word-break:break-all;"><?= $email ?: 'Chưa cập nhật' ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
          <span style="font-size:16px;">📍</span>
          <span style="color:<?= $dia_chi ? 'var(--text)' : 'var(--text3)' ?>;"><?= $dia_chi ?: 'Chưa cập nhật' ?></span>
        </div>
        <?php if($cccd): ?>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
          <span style="font-size:16px;">🪪</span>
          <span style="color:var(--text);"><?= $cccd ?></span>
        </div>
        <?php endif; ?>
        <?php if($ngay_sinh): ?>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
          <span style="font-size:16px;">🎂</span>
          <span style="color:var(--text);"><?= date('d/m/Y', strtotime($ngay_sinh)) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── Cột phải: Tab form ── -->
  <div>
    <!-- Tab switcher -->
    <div style="display:flex;gap:8px;margin-bottom:16px;">
      <button class="profile-tab active" id="tabInfo" onclick="switchTab('info')">✏️ Thông tin</button>
      <button class="profile-tab" id="tabXe"   onclick="switchTab('xe')">🏍 Xe của tôi</button>
      <button class="profile-tab" id="tabPass"  onclick="switchTab('pass')">🔒 Đổi mật khẩu</button>
    </div>

    <!-- Tab: Thông tin cá nhân -->
    <div id="panelInfo">
      <div class="card">
        <div class="card-header"><div class="card-title">Chỉnh sửa thông tin</div></div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <?= Csrf::field() ?>
            <input type="hidden" name="_action" value="update_info"/>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
                <input class="form-control" type="text" name="ho_ten" value="<?= e($ho_ten) ?>" required/>
              </div>
              <div class="form-group">
                <label class="form-label">Tên đăng nhập</label>
                <input class="form-control" type="text" value="<?= e($username) ?>" disabled
                       style="opacity:.5;cursor:not-allowed;"/>
                <div style="font-size:11px;color:var(--text3);margin-top:4px;">Không thể thay đổi</div>
              </div>
              <div class="form-group">
                <label class="form-label">Số điện thoại</label>
                <input class="form-control" type="tel" name="sdt" value="<?= e($sdt) ?>" placeholder="0912345678" required/>
              </div>
              <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="<?= e($email) ?>" placeholder="example@gmail.com" required/>
              </div>
              <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Địa chỉ thường trú</label>
                <input class="form-control" type="text" name="dia_chi" value="<?= e($dia_chi) ?>" placeholder="Số nhà, đường, phường/xã, quận/huyện..."/>
              </div>
              <div class="form-group">
                <label class="form-label">Số CCCD / CMND</label>
                <input class="form-control" type="text" name="cccd" value="<?= e($cccd) ?>" placeholder="012345678901" required/>
              </div>
              <div class="form-group">
                <label class="form-label">Ngày sinh</label>
                <input class="form-control" type="date" name="ngay_sinh"
                       value="<?= htmlspecialchars($ngay_sinh) ?>"/>
              </div>
            </div>

            <!-- ── Ảnh CCCD ── -->
            <?php
              $cccdTruoc = htmlspecialchars($nguoi_thue['cccd_truoc'] ?? '');
              $cccdSau   = htmlspecialchars($nguoi_thue['cccd_sau']   ?? '');
            ?>
            <div style="margin-top:16px;padding:14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
              <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">🪪 Ảnh CCCD / CMND</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                <!-- Mặt trước -->
                <div>
                  <div style="font-size:11px;color:var(--text3);margin-bottom:6px;font-weight:600;">MẶT TRƯỚC</div>
                  <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--border);background:var(--bg);height:130px;display:flex;align-items:center;justify-content:center;">
                    <?php if($cccdTruoc): ?>
                    <img id="prevTruoc" src="<?= e($cccdTruoc) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'"/>
                    <?php else: ?>
                    <img id="prevTruoc" style="display:none;width:100%;height:100%;object-fit:cover;"/>
                    <div id="phTruoc" style="text-align:center;color:var(--text3);">
                      <div style="font-size:28px;">🪪</div>
                      <div style="font-size:11px;margin-top:4px;">Chưa có ảnh</div>
                    </div>
                    <?php endif; ?>
                  </div>
                  <label style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:8px;padding:6px;background:var(--card);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;color:var(--text2);">
                    📷 Chọn ảnh mặt trước
                    <input type="file" name="cccd_truoc" accept="image/*" style="display:none;"
                           onchange="previewCCCD(this,'prevTruoc','phTruoc')"/>
                  </label>
                </div>

                <!-- Mặt sau -->
                <div>
                  <div style="font-size:11px;color:var(--text3);margin-bottom:6px;font-weight:600;">MẶT SAU</div>
                  <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--border);background:var(--bg);height:130px;display:flex;align-items:center;justify-content:center;">
                    <?php if($cccdSau): ?>
                    <img id="prevSau" src="<?= e($cccdSau) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'"/>
                    <?php else: ?>
                    <img id="prevSau" style="display:none;width:100%;height:100%;object-fit:cover;"/>
                    <div id="phSau" style="text-align:center;color:var(--text3);">
                      <div style="font-size:28px;">🪪</div>
                      <div style="font-size:11px;margin-top:4px;">Chưa có ảnh</div>
                    </div>
                    <?php endif; ?>
                  </div>
                  <label style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:8px;padding:6px;background:var(--card);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;color:var(--text2);">
                    📷 Chọn ảnh mặt sau
                    <input type="file" name="cccd_sau" accept="image/*" style="display:none;"
                           onchange="previewCCCD(this,'prevSau','phSau')"/>
                  </label>
                </div>

              </div>
              <div style="font-size:11px;color:var(--text3);margin-top:8px;">JPG, PNG, WEBP · Tối đa 5MB mỗi ảnh</div>
            </div>

            <div style="margin-top:14px;">
              <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Tab: Xe của tôi -->
    <div id="panelXe" style="display:none;">
      <div class="card">
        <div class="card-header"><div class="card-title">🏍 Xe đăng ký của tôi</div></div>
        <div class="card-body">
          <?php if(empty($xeList)): ?>
          <div style="text-align:center;padding:40px 20px;color:var(--text3);">
            <div style="font-size:48px;margin-bottom:12px;">🏍</div>
            <div style="font-size:14px;font-weight:600;color:var(--text2);margin-bottom:6px;">Chưa có xe đăng ký</div>
            <div style="font-size:12px;">Xe sẽ được đăng ký khi tạo hợp đồng thuê phòng</div>
          </div>
          <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php
            $loaiLabel = ['xe_may'=>'🏍 Xe máy','xe_dien'=>'⚡ Xe điện','xe_dap'=>'🚲 Xe đạp','o_to'=>'🚗 Ô tô'];
            foreach($xeList as $xe):
              $isOwner = empty($xe['ghi_chu']) || strpos($xe['ghi_chu'], $nguoi_thue['ho_ten']) !== false;
            ?>
            <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
              <!-- Icon loại xe -->
              <div style="width:48px;height:48px;border-radius:12px;background:rgba(79,142,247,.1);border:1px solid rgba(79,142,247,.2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                <?= $xe['loai_xe']==='xe_dien'?'⚡':($xe['loai_xe']==='xe_dap'?'🚲':($xe['loai_xe']==='o_to'?'🚗':'🏍')) ?>
              </div>
              <!-- Info -->
              <div style="flex:1;min-width:0;">
                <div style="font-size:15px;font-weight:800;color:var(--text);letter-spacing:.5px;font-family:monospace;">
                  <?= htmlspecialchars($xe['bien_so']) ?>
                </div>
                <div style="font-size:12px;color:var(--text3);margin-top:3px;">
                  <?= $loaiLabel[$xe['loai_xe']] ?? $xe['loai_xe'] ?>
                  <?= $xe['mau_sac'] ? ' · ' . htmlspecialchars($xe['mau_sac']) : '' ?>
                </div>
                <?php if(!empty($xe['so_phong'])): ?>
                <div style="font-size:11px;color:var(--text3);margin-top:2px;">
                  📍 Phòng <?= htmlspecialchars($xe['so_phong']) ?>
                  <?= !empty($xe['ten_khu']) ? ' — ' . htmlspecialchars($xe['ten_khu']) : '' ?>
                </div>
                <?php endif; ?>
              </div>
              <!-- Badge chủ xe / người ở cùng -->
              <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;
                           background:<?= $isOwner?'rgba(79,142,247,.12)':'rgba(247,169,79,.12)' ?>;
                           color:<?= $isOwner?'var(--accent)':'var(--amber,#f59e0b)' ?>;
                           border:1px solid <?= $isOwner?'rgba(79,142,247,.2)':'rgba(247,169,79,.2)' ?>;">
                <?= $isOwner ? '✍ Chủ HĐ' : '👤 Người ở cùng' ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="font-size:11px;color:var(--text3);margin-top:12px;text-align:center;">
            Để thêm/sửa xe, liên hệ quản lý hoặc cập nhật khi ký hợp đồng mới
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Tab: Đổi mật khẩu -->
    <div id="panelPass" style="display:none;">
      <div class="card">
        <div class="card-header"><div class="card-title">Đổi mật khẩu</div></div>
        <div class="card-body">
          <form method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="_action" value="change_password"/>
            <div style="display:flex;flex-direction:column;gap:16px;max-width:420px;">
              <div class="form-group">
                <label class="form-label">Mật khẩu hiện tại <span style="color:var(--red)">*</span></label>
                <div style="position:relative;">
                  <input class="form-control" type="password" name="old_password" id="oldPw" required style="padding-right:44px;"/>
                  <button type="button" onclick="togglePw('oldPw',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text3);">👁</button>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Mật khẩu mới <span style="color:var(--red)">*</span></label>
                <div style="position:relative;">
                  <input class="form-control" type="password" name="new_password" id="newPw" required minlength="8" style="padding-right:44px;" oninput="checkStrength(this.value)"/>
                  <button type="button" onclick="togglePw('newPw',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text3);">👁</button>
                </div>
                <!-- Strength bar -->
                <div style="margin-top:6px;height:4px;border-radius:4px;background:var(--bg3);overflow:hidden;">
                  <div id="strengthBar" style="height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s;"></div>
                </div>
                <div id="strengthLabel" style="font-size:11px;color:var(--text3);margin-top:3px;"></div>
              </div>
              <div class="form-group">
                <label class="form-label">Xác nhận mật khẩu mới <span style="color:var(--red)">*</span></label>
                <div style="position:relative;">
                  <input class="form-control" type="password" name="confirm_password" id="cfmPw" required style="padding-right:44px;"/>
                  <button type="button" onclick="togglePw('cfmPw',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text3);">👁</button>
                </div>
              </div>
              <div>
                <button type="submit" class="btn btn-primary">🔒 Đổi mật khẩu</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function switchTab(tab) {
  document.getElementById('panelInfo').style.display = tab === 'info' ? '' : 'none';
  document.getElementById('panelXe').style.display   = tab === 'xe'   ? '' : 'none';
  document.getElementById('panelPass').style.display = tab === 'pass' ? '' : 'none';
  document.getElementById('tabInfo').classList.toggle('active', tab === 'info');
  document.getElementById('tabXe').classList.toggle('active',   tab === 'xe');
  document.getElementById('tabPass').classList.toggle('active', tab === 'pass');
}

function togglePw(id, btn) {
  const inp = document.getElementById(id);
  inp.type  = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}

function checkStrength(val) {
  const bar   = document.getElementById('strengthBar');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 8)          score++;
  if (/[A-Z]/.test(val))        score++;
  if (/[0-9]/.test(val))        score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    {w:'20%', bg:'#ef4444', t:'Rất yếu'},
    {w:'40%', bg:'#f97316', t:'Yếu'},
    {w:'65%', bg:'#eab308', t:'Trung bình'},
    {w:'85%', bg:'#22c55e', t:'Mạnh'},
    {w:'100%',bg:'#10b981', t:'Rất mạnh'},
  ];
  const lv = levels[score] || levels[0];
  bar.style.width      = lv.w;
  bar.style.background = lv.bg;
  label.textContent    = lv.t;
  label.style.color    = lv.bg;
}

function previewAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById('avatarImg');
    const ini = document.getElementById('avatarInitial');
    img.src = e.target.result;
    img.style.display = 'block';
    if (ini) ini.style.display = 'none';
    document.getElementById('avatarActions').style.display = 'flex';
    // lưu src cũ để cancel
    img.dataset.oldSrc = img.dataset.oldSrc || (img.src !== e.target.result ? img.src : '');
  };
  reader.readAsDataURL(input.files[0]);
}
function cancelAvatar() {
  const form  = document.getElementById('avatarForm');
  const input = form.querySelector('input[type=file]');
  input.value = '';
  document.getElementById('avatarActions').style.display = 'none';
  // Không cần rollback — chỉ reset input là đủ
}

function previewCCCD(input, imgId, phId) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById(imgId);
    const ph  = document.getElementById(phId);
    img.src = e.target.result;
    img.style.display = 'block';
    if (ph) ph.style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}

<?php if($error && str_contains($error,'mật khẩu')): ?>
switchTab('pass');
<?php endif; ?>
</script>

<!-- Responsive -->
<link rel="stylesheet" href="frontend/assets/css/user-profile-2.css">

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
