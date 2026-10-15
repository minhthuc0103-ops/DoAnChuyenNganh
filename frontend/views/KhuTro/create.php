<?php
$title = 'Thêm khu trọ';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title"><h1>Thêm khu trọ mới</h1><p>Mã khu sẽ là prefix cho tên phòng (A → A101, A102...)</p></div>
  <a href="index.php?controller=khutro&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:560px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin khu trọ</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?=htmlspecialchars($error)?></div><?php endif; ?>

      <!-- Preview -->
      <div id="preview" style="display:none;margin-bottom:20px;padding:12px 16px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.2);border-radius:10px;">
        <div style="font-size:11px;color:var(--text3);font-weight:600;text-transform:uppercase;margin-bottom:5px;">Ví dụ tên phòng</div>
        <div id="previewText" style="font-size:15px;font-weight:700;color:var(--accent);"></div>
      </div>

      <form method="POST" action="index.php?controller=khutro&action=create">
            <?= Csrf::field() ?>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Tên khu trọ <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="text" name="ten_khu" id="tenKhu"
                   placeholder="VD: Khu A, Khu Nguyễn Trãi..."
                   value="<?=htmlspecialchars($_POST['ten_khu']??'')?>" required
                   oninput="updatePreview()"/>
          </div>
          <div class="form-group">
            <label class="form-label">Mã khu <span style="color:var(--red)">*</span>
              <span style="font-weight:400;color:var(--text3);font-size:10px;text-transform:none;"> (1-5 ký tự in hoa)</span>
            </label>
            <input class="form-control" type="text" name="ma_khu" id="maKhu"
                   placeholder="VD: A, B, KA..."
                   value="<?=htmlspecialchars($_POST['ma_khu']??'')?>"
                   maxlength="5" required
                   oninput="this.value=this.value.toUpperCase();updatePreview()"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Địa chỉ khu trọ</label>
          <input class="form-control" type="text" name="dia_chi"
                 placeholder="Số nhà, đường, phường/xã, quận/huyện..."
                 value="<?=htmlspecialchars($_POST['dia_chi']??'')?>"/>
        </div>
        <div class="form-group">
          <label class="form-label">Mô tả / Tiện ích khu</label>
          <textarea class="form-control" name="mo_ta"
                    placeholder="Có bãi xe, camera an ninh, gần chợ..."><?=htmlspecialchars($_POST['mo_ta']??'')?></textarea>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary">💾 Tạo khu trọ</button>
          <a href="index.php?controller=khutro&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="frontend/assets/js/khutro-create.js"></script>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>