<?php
$title = $title ?? 'Tạo thông báo mới';
$isEdit = isset($tb);
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1><?= $isEdit ? '✏ Sửa thông báo' : '📢 Tạo thông báo mới' ?></h1>
    <p><?= $isEdit ? 'Chỉnh sửa nội dung' : 'Gửi thông báo đến tất cả người thuê' ?></p>
  </div>
  <a href="index.php?controller=thongbao&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:640px;">
  <div class="card">
    <div class="card-header"><div class="card-title"><?= $isEdit ? 'Chỉnh sửa' : 'Nội dung thông báo' ?></div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?>
      <div class="msg-alert msg-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?controller=thongbao&action=<?= $isEdit ? "edit&id={$tb['id']}" : 'create' ?>
            <?= Csrf::field() ?>">

        <div class="form-group">
          <label class="form-label">Loại thông báo</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;" id="loaiGrid">
            <?php
            $loaiOptions = ['chung'=>['📢','Chung'],'khan_cap'=>['🚨','Khẩn cấp'],'bao_tri'=>['🔧','Bảo trì'],'tien_phong'=>['💰','Tiền phòng'],'khac'=>['📋','Khác']];
            $currentLoai = $isEdit ? $tb['loai'] : ($_POST['loai'] ?? 'chung');
            foreach($loaiOptions as $val => [$icon, $label]):
            ?>
            <label class="loai-btn <?= $currentLoai===$val ? 'active' : '' ?>" style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--bg3);border:2px solid var(--border);border-radius:10px;cursor:pointer;transition:all .15s;">
              <input type="radio" name="loai" value="<?= $val ?>" <?= $currentLoai===$val?'checked':'' ?> style="display:none;" onchange="selectLoai(this)"/>
              <span style="font-size:16px;"><?= $icon ?></span>
              <span style="font-size:12px;font-weight:600;color:var(--text);"><?= $label ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Tiêu đề <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="tieu_de"
                 value="<?= htmlspecialchars($isEdit ? $tb['tieu_de'] : ($_POST['tieu_de'] ?? '')) ?>"
                 placeholder="VD: Thông báo thu tiền tháng 6..." required maxlength="200"/>
        </div>

        <div class="form-group">
          <label class="form-label">Nội dung <span style="color:var(--red)">*</span></label>
          <textarea class="form-control" name="noi_dung" rows="8" required
                    style="min-height:200px;line-height:1.7;"
                    placeholder="Nhập nội dung thông báo..."><?= htmlspecialchars($isEdit ? $tb['noi_dung'] : ($_POST['noi_dung'] ?? '')) ?></textarea>
        </div>

        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;">
            <input type="checkbox" name="ghim" value="1"
                   <?= ($isEdit && $tb['ghim']) || (!empty($_POST['ghim'])) ? 'checked' : '' ?>
                   style="width:18px;height:18px;accent-color:var(--amber);"/>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text);">📌 Ghim thông báo</div>
              <div style="font-size:11px;color:var(--text3);">Luôn hiển thị ở đầu danh sách</div>
            </div>
          </label>
        </div>

        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
            <?= $isEdit ? '💾 Cập nhật' : '📢 Gửi thông báo' ?>
          </button>
          <a href="index.php?controller=thongbao&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>

<link rel="stylesheet" href="frontend/assets/css/thongbao-create.css">

<script src="frontend/assets/js/thongbao-create.js"></script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
