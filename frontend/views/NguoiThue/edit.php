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
      <form method="POST" action="index.php?controller=nguoithue&action=edit&id=<?= $data['id'] ?>
            <?= Csrf::field() ?>" enctype="multipart/form-data">

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
          <form method="POST" action="index.php?controller=nguoithue&action=delete" style="display:inline;margin-left:auto" onsubmit="return confirm('Xóa người thuê này?')">
            <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $data['id'] ?>">
            <button type="submit" class="btn btn-danger" style="cursor:pointer">🗑 Xóa</button>
          </form>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>
<script src="frontend/assets/js/nguoithue-edit.js"></script>
