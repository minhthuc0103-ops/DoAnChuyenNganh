<?php
$title = 'Cập nhật thông tin lưu trú';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Cập nhật thông tin lưu trú</h1>
    <p>Người lưu trú: <strong><?= htmlspecialchars($item['ho_ten']) ?></strong> — Phòng <?= htmlspecialchars($item['so_phong']) ?></p>
  </div>
  <a href="index.php?controller=luutru&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:720px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Chỉnh sửa hồ sơ lưu trú</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?>
        <div class="msg-alert msg-error" style="margin-bottom:18px;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?controller=luutru&action=edit&id=<?= $item['id'] ?>">
        <?= Csrf::field() ?>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">🏠 Phòng trọ <span style="color:var(--red)">*</span></label>
            <select name="phong_id" class="form-control" required>
              <?php foreach($phongs as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $item['phong_id']==$p['id']?'selected':'' ?>>
                Phòng <?= htmlspecialchars($p['so_phong']) ?> (<?= htmlspecialchars($p['ten_khu']??'') ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Trạng thái hồ sơ</label>
            <select name="trang_thai" class="form-control">
              <option value="cho_khai_bao" <?= $item['trang_thai']==='cho_khai_bao'?'selected':'' ?>>⏳ Chờ khai báo</option>
              <option value="da_gui_dvc" <?= $item['trang_thai']==='da_gui_dvc'?'selected':'' ?>>🌐 Đã nộp Cổng Dịch vụ công</option>
              <option value="da_tiep_nhan" <?= $item['trang_thai']==='da_tiep_nhan'?'selected':'' ?>>✅ Đã tiếp nhận</option>
              <option value="ket_thuc" <?= $item['trang_thai']==='ket_thuc'?'selected':'' ?>>🚪 Đã kết thúc</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
            <input type="text" name="ho_ten" class="form-control" value="<?= htmlspecialchars($item['ho_ten']) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Số CCCD / Mã định danh <span style="color:var(--red)">*</span></label>
            <input type="text" name="cccd" class="form-control" value="<?= htmlspecialchars($item['cccd']) ?>" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày sinh</label>
            <input type="date" name="ngay_sinh" class="form-control" value="<?= htmlspecialchars($item['ngay_sinh']??'') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Giới tính</label>
            <select name="gioi_tinh" class="form-control">
              <option value="nam" <?= $item['gioi_tinh']==='nam'?'selected':'' ?>>Nam</option>
              <option value="nu" <?= $item['gioi_tinh']==='nu'?'selected':'' ?>>Nữ</option>
              <option value="khac" <?= $item['gioi_tinh']==='khac'?'selected':'' ?>>Khác</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày cấp CCCD</label>
            <input type="date" name="ngay_cap_cccd" class="form-control" value="<?= htmlspecialchars($item['ngay_cap_cccd']??'') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Nơi cấp CCCD</label>
            <input type="text" name="noi_cap_cccd" class="form-control" value="<?= htmlspecialchars($item['noi_cap_cccd']??'') ?>">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($item['sdt']??'') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Nghề nghiệp</label>
            <input type="text" name="nghe_nghiep" class="form-control" value="<?= htmlspecialchars($item['nghe_nghiep']??'') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Hộ khẩu thường trú (Quê quán theo CCCD)</label>
          <input type="text" name="thuong_tru" class="form-control" value="<?= htmlspecialchars($item['thuong_tru']??'') ?>">
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày bắt đầu đến lưu trú <span style="color:var(--red)">*</span></label>
            <input type="date" name="ngay_den" class="form-control" value="<?= htmlspecialchars($item['ngay_den']??'') ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Ngày dự kiến kết thúc</label>
            <input type="date" name="ngay_di" class="form-control" value="<?= htmlspecialchars($item['ngay_di']??'') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Lý do lưu trú</label>
          <input type="text" name="ly_do" class="form-control" value="<?= htmlspecialchars($item['ly_do']??'') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Mã hồ sơ Cổng DVC (nếu có)</label>
          <input type="text" name="ma_dvc" class="form-control" value="<?= htmlspecialchars($item['ma_dvc']??'') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghi_chu" class="form-control" rows="2"><?= htmlspecialchars($item['ghi_chu']??'') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
          <button type="submit" class="btn btn-primary">💾 Lưu cập nhật</button>
          <a href="index.php?controller=luutru&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

