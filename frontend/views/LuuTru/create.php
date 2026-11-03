<?php
$title = 'Thêm người lưu trú';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Thêm người lưu trú mới</h1>
    <p>Nhập thông tin người thuê/ở cùng phòng để làm thủ tục khai báo cư trú</p>
  </div>
  <a href="index.php?controller=luutru&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:720px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin khai báo cư trú</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?>
        <div class="msg-alert msg-error" style="margin-bottom:18px;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?controller=luutru&action=create">
        <?= Csrf::field() ?>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">🏠 Phòng trọ <span style="color:var(--red)">*</span></label>
            <select name="phong_id" class="form-control" required>
              <option value="">— Chọn phòng —</option>
              <?php foreach($phongs as $p): ?>
              <option value="<?= $p['id'] ?>" <?= ($_POST['phong_id']??'')==$p['id']?'selected':'' ?>>
                Phòng <?= htmlspecialchars($p['so_phong']) ?> (<?= htmlspecialchars($p['ten_khu']??'') ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Trạng thái hồ sơ</label>
            <select name="trang_thai" class="form-control">
              <option value="cho_khai_bao">⏳ Chờ khai báo</option>
              <option value="da_gui_dvc">🌐 Đã nộp Cổng Dịch vụ công</option>
              <option value="da_tiep_nhan">✅ Đã tiếp nhận</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
            <input type="text" name="ho_ten" class="form-control" placeholder="NGUYEN VAN A" value="<?= htmlspecialchars($_POST['ho_ten']??'') ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Số CCCD / Mã định danh <span style="color:var(--red)">*</span></label>
            <input type="text" name="cccd" class="form-control" placeholder="12 chữ số CCCD" value="<?= htmlspecialchars($_POST['cccd']??'') ?>" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày sinh</label>
            <input type="date" name="ngay_sinh" class="form-control" value="<?= htmlspecialchars($_POST['ngay_sinh']??'') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Giới tính</label>
            <select name="gioi_tinh" class="form-control">
              <option value="nam" <?= ($_POST['gioi_tinh']??'')==='nam'?'selected':'' ?>>Nam</option>
              <option value="nu" <?= ($_POST['gioi_tinh']??'')==='nu'?'selected':'' ?>>Nữ</option>
              <option value="khac" <?= ($_POST['gioi_tinh']??'')==='khac'?'selected':'' ?>>Khác</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày cấp CCCD</label>
            <input type="date" name="ngay_cap_cccd" class="form-control" value="<?= htmlspecialchars($_POST['ngay_cap_cccd']??'') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Nơi cấp CCCD</label>
            <input type="text" name="noi_cap_cccd" class="form-control" placeholder="Cục Cảnh sát QLHC về TTXH" value="<?= htmlspecialchars($_POST['noi_cap_cccd']??'Cục Cảnh sát QLHC về TTXH') ?>">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="sdt" class="form-control" placeholder="09xxxxxxx" value="<?= htmlspecialchars($_POST['sdt']??'') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Nghề nghiệp</label>
            <input type="text" name="nghe_nghiep" class="form-control" placeholder="Sinh viên / Nhân viên văn phòng..." value="<?= htmlspecialchars($_POST['nghe_nghiep']??'') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Hộ khẩu thường trú (Quê quán theo CCCD)</label>
          <input type="text" name="thuong_tru" class="form-control" placeholder="Số nhà, đường, xã/phường, quận/huyện, tỉnh/TP" value="<?= htmlspecialchars($_POST['thuong_tru']??'') ?>">
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày bắt đầu đến lưu trú <span style="color:var(--red)">*</span></label>
            <input type="date" name="ngay_den" class="form-control" value="<?= htmlspecialchars($_POST['ngay_den']??date('Y-m-d')) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Ngày dự kiến kết thúc</label>
            <input type="date" name="ngay_di" class="form-control" value="<?= htmlspecialchars($_POST['ngay_di']??'') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Lý do lưu trú</label>
          <input type="text" name="ly_do" class="form-control" value="<?= htmlspecialchars($_POST['ly_do']??'Thuê trọ để sinh hoạt, học tập và làm việc') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Mã hồ sơ Cổng DVC (nếu có)</label>
          <input type="text" name="ma_dvc" class="form-control" placeholder="VD: DVC-2026-xxxxx" value="<?= htmlspecialchars($_POST['ma_dvc']??'') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghi_chu" class="form-control" rows="2"><?= htmlspecialchars($_POST['ghi_chu']??'') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
          <button type="submit" class="btn btn-primary">💾 Lưu thông tin</button>
          <a href="index.php?controller=luutru&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

