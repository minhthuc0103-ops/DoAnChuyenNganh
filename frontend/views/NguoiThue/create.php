<?php
$title = 'Thêm người thuê';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title"><h1>Thêm người thuê</h1><p>Điền thông tin người thuê mới</p></div>
  <a href="index.php?controller=nguoithue&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:600px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin cá nhân</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="index.php?controller=nguoithue&action=create">
            <?= Csrf::field() ?>
        <div class="form-group">
          <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="ho_ten" placeholder="Nguyễn Văn A"
                 value="<?= htmlspecialchars($_POST['ho_ten']??'') ?>" required/>
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
        <div class="form-group">
          <label class="form-label">Địa chỉ thường trú</label>
          <textarea class="form-control" name="dia_chi" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/TP..."><?= htmlspecialchars($_POST['dia_chi']??'') ?></textarea>
        </div>
        <div style="display:flex;gap:10px;padding-top:4px;">
          <button type="submit" class="btn btn-primary">💾 Lưu</button>
          <a href="index.php?controller=nguoithue&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>