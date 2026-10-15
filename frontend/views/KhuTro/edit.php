<?php
$title = 'Chỉnh sửa khu trọ';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title"><h1>Chỉnh sửa khu trọ</h1><p><?=htmlspecialchars($khu['ten_khu']??'')?></p></div>
  <a href="index.php?controller=khutro&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<div style="max-width:560px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Thông tin khu trọ</div></div>
    <div class="card-body">
      <?php if(!empty($error)): ?><div class="msg-alert msg-error"><?=htmlspecialchars($error)?></div><?php endif; ?>

      <div style="margin-bottom:18px;padding:12px 16px;background:rgba(247,169,79,.07);border:1px solid rgba(247,169,79,.2);border-radius:10px;font-size:12px;color:var(--amber);">
        ⚠ <strong>Lưu ý:</strong> Thay đổi mã khu sẽ không tự đổi tên các phòng đã tạo. Nên cập nhật tên phòng thủ công sau khi đổi mã.
      </div>

      <form method="POST" action="index.php?controller=khutro&action=edit&id=<?=$khu['id']?>
            <?= Csrf::field() ?>">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Tên khu trọ <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="text" name="ten_khu"
                   value="<?=htmlspecialchars($khu['ten_khu']??'')?>" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Mã khu <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="text" name="ma_khu"
                   value="<?=htmlspecialchars($khu['ma_khu']??'')?>"
                   maxlength="5" required
                   oninput="this.value=this.value.toUpperCase()"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Địa chỉ khu trọ</label>
          <input class="form-control" type="text" name="dia_chi"
                 value="<?=htmlspecialchars($khu['dia_chi']??'')?>"/>
        </div>
        <div class="form-group">
          <label class="form-label">Mô tả</label>
          <textarea class="form-control" name="mo_ta"><?=htmlspecialchars($khu['mo_ta']??'')?></textarea>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
          <a href="index.php?controller=khutro&action=index" class="btn btn-outline">Hủy</a>
          <form method="POST" action="index.php?controller=khutro&action=delete" style="display:inline;" onsubmit="return confirm('Xóa khu này?')">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $khu['id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs" style="cursor:pointer;">🗑 Xóa</button>
          </form>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>