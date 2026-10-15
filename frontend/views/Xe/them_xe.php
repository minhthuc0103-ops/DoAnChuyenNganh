<?php
$title = 'Thêm xe';
require_once 'frontend/views/Layouts/header.php';
?>
<div class="page-header">
  <div class="page-title">
    <h1>Thêm xe</h1>
    <p>Phòng <?= htmlspecialchars($hd['so_phong']??'') ?> — HĐ #<?= str_pad($hd['id']??0,4,'0',STR_PAD_LEFT) ?> · Đã có <?= $soXe ?>/4 xe</p>
  </div>
  <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<?php if($soXe >= 4): ?>
<div class="card" style="text-align:center;padding:40px;">
  <div style="font-size:40px;margin-bottom:10px">🚗</div>
  <div style="font-size:15px;font-weight:700;color:var(--text2);margin-bottom:6px">Phòng đã đủ 4 xe!</div>
  <div style="font-size:13px;color:var(--text3);margin-bottom:16px">Mỗi phòng tối đa 4 xe. Xóa xe cũ trước khi thêm mới.</div>
  <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">← Quay lại</a>
</div>
<?php else: ?>
<div style="max-width:500px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🚗 Thông tin xe</div>
      <div class="card-sub">Còn <?= 4 - $soXe ?> chỗ xe trống trong phòng này</div>
    </div>
    <div class="card-body">
      <?php if(!empty($error)): ?>
      <div class="msg-alert msg-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="index.php?controller=xe&action=them&id=<?= $hd['id']??0 ?>
            <?= Csrf::field() ?>">

        <div class="form-group">
          <label class="form-label">Biển số xe <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="bien_so" required
                 placeholder="51F1-23456" style="text-transform:uppercase;"
                 value="<?= htmlspecialchars(strtoupper($_POST['bien_so']??'')) ?>"
                 oninput="this.value=this.value.toUpperCase()"/>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Loại xe</label>
            <select class="form-control" name="loai_xe">
              <option value="xe_may"  <?= ($_POST['loai_xe']??'xe_may')==='xe_may' ?'selected':'' ?>>🏍 Xe máy</option>
              <option value="xe_dien"<?= ($_POST['loai_xe']??'')==='xe_dien'?'selected':'' ?>>⚡ Xe điện</option>
              <option value="xe_dap" <?= ($_POST['loai_xe']??'')==='xe_dap' ?'selected':'' ?>>🚲 Xe đạp</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Màu sắc</label>
            <input class="form-control" type="text" name="mau_sac"
                   placeholder="Đen, Đỏ, Trắng..."
                   value="<?= htmlspecialchars($_POST['mau_sac']??'') ?>"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Ghi chú</label>
          <input class="form-control" type="text" name="ghi_chu"
                 placeholder="Ghi chú thêm (không bắt buộc)..."
                 value="<?= htmlspecialchars($_POST['ghi_chu']??'') ?>"/>
        </div>

        <div style="display:flex;gap:10px;padding-top:16px;border-top:1px solid var(--border);margin-top:4px;">
          <button type="submit" class="btn btn-primary">💾 Thêm xe</button>
          <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>
