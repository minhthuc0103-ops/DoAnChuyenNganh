<?php
$title = 'Thêm người thuê chung';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title">
    <h1>Thêm người thuê chung</h1>
    <p>Phòng <?= htmlspecialchars($hd['so_phong']??'') ?> — HĐ #<?= str_pad($hd['id']??0,4,'0',STR_PAD_LEFT) ?> · Đang có <?= $soNguoi ?>/4 người</p>
  </div>
  <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:600px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin người thuê chung</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="index.php?controller=hopdong&action=themNguoi&id=<?= $hd['id']??0 ?>
            <?= Csrf::field() ?>" enctype="multipart/form-data">

        <!-- Avatar -->
        <div class="form-group" style="display:flex;align-items:center;gap:16px;padding:14px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);margin-bottom:20px;">
          <div id="avInit" style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#4f8ef7,#7c5cfc);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">?</div>
          <img id="avPreview" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;"/>
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;">Ảnh đại diện</div>
            <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--card);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;color:var(--text2);">
              📷 Chọn ảnh
              <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="prevAv(this)"/>
            </label>
            <div style="font-size:11px;color:var(--text3);margin-top:5px;">JPG, PNG · Tối đa 2MB</div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="ho_ten" placeholder="Nguyễn Văn A" required
                 value="<?= htmlspecialchars($_POST['ho_ten']??'') ?>" oninput="updateInit(this.value)"/>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số CCCD / CMND</label>
            <input class="form-control" type="text" name="cccd" placeholder="079......"
                   value="<?= htmlspecialchars($_POST['cccd']??'') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input class="form-control" type="tel" name="sdt" placeholder="0901234567"
                   value="<?= htmlspecialchars($_POST['sdt']??'') ?>"/>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày sinh</label>
            <input class="form-control" type="date" name="ngay_sinh"
                   value="<?= htmlspecialchars($_POST['ngay_sinh']??'') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Giới tính</label>
            <select class="form-control" name="gioi_tinh">
              <option value="nam"  <?= ($_POST['gioi_tinh']??'nam')==='nam'?'selected':'' ?>>Nam</option>
              <option value="nu"   <?= ($_POST['gioi_tinh']??'')==='nu'?'selected':'' ?>>Nữ</option>
              <option value="khac" <?= ($_POST['gioi_tinh']??'')==='khac'?'selected':'' ?>>Khác</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Quê quán / Địa chỉ thường trú</label>
          <textarea class="form-control" name="que_quan" placeholder="Tỉnh/thành phố..."><?= htmlspecialchars($_POST['que_quan']??'') ?></textarea>
        </div>
        <div style="display:flex;gap:10px;padding-top:4px;">
          <button type="submit" class="btn btn-primary">💾 Thêm người thuê</button>
          <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="frontend/assets/js/hopdong-them-nguoi.js"></script>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>