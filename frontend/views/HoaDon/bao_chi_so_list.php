<?php
$title = 'Duyệt chỉ số điện nước từ người thuê';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>⚡ Duyệt chỉ số điện nước từ người thuê</h1>
    <p>Người ở tự chụp ảnh công tơ để AI đọc số và gửi lên — Chủ trọ kiểm tra và lập hóa đơn</p>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="index.php?controller=hoadon&action=create" class="btn btn-primary">＋ Lập hóa đơn mới</a>
    <a href="index.php?controller=hoadon&action=index" class="btn btn-outline">← Danh sách hóa đơn</a>
  </div>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] === 'rejected'): ?>
  <div class="msg-alert msg-success" style="margin-bottom:18px;">✅ Đã từ chối báo số điện nước!</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <div class="card-title">Danh sách báo số đang chờ duyệt</div>
    <span class="badge" style="background:rgba(247,169,79,.15);color:var(--amber);font-weight:700;"><?= count($list) ?> yêu cầu</span>
  </div>
  <div class="card-body" style="padding:0;">
    <?php if(empty($list)): ?>
      <div style="text-align:center;padding:50px 20px;color:var(--text3);">
        <div style="font-size:40px;margin-bottom:10px;">✨</div>
        <h3 style="margin-bottom:6px;">Hiện không có báo số nào cần duyệt</h3>
        <p style="margin:0;">Khi người thuê chụp công tơ và gửi chỉ số hàng tháng, danh sách sẽ hiển thị tại đây.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="table" style="width:100%;margin-bottom:0;">
          <thead>
            <tr>
              <th>Phòng / Khu</th>
              <th>Người gửi</th>
              <th>Tháng</th>
              <th>Công tơ Điện (kWh)</th>
              <th>Công tơ Nước (m³)</th>
              <th>Ghi chú</th>
              <th>Gửi lúc</th>
              <th style="text-align:right;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($list as $r): ?>
            <tr>
              <td>
                <span class="badge" style="background:rgba(79,142,247,.12);color:var(--accent);font-weight:700;font-size:13px;">
                  Phòng <?= htmlspecialchars($r['so_phong']) ?>
                </span>
                <div style="font-size:12px;color:var(--text3);margin-top:2px;">
                  <?= htmlspecialchars($r['ten_khu'] ?? '') ?>
                </div>
              </td>

              <td>
                <strong style="color:var(--text);"><?= htmlspecialchars($r['ten_nguoi_thue']) ?></strong>
                <div style="font-size:12px;color:var(--text3);">📞 <?= htmlspecialchars($r['sdt'] ?? '') ?></div>
              </td>

              <td>
                <strong style="font-size:14px;color:var(--accent);">Tháng <?= $r['thang'] ?>/<?= $r['nam'] ?></strong>
              </td>

              <td>
                <div style="font-size:15px;font-weight:700;color:var(--amber);">
                  <?= (float)$r['chi_so_dien'] ?> kWh
                </div>
                <?php if($r['anh_cong_to_dien']): ?>
                <a href="<?= htmlspecialchars($r['anh_cong_to_dien']) ?>" target="_blank" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;color:var(--accent);text-decoration:none;margin-top:2px;">
                  📷 Xem ảnh công tơ điện
                </a>
                <?php endif; ?>
              </td>

              <td>
                <div style="font-size:15px;font-weight:700;color:var(--accent);">
                  <?= (float)$r['chi_so_nuoc'] ?> m³
                </div>
                <?php if($r['anh_cong_to_nuoc']): ?>
                <a href="<?= htmlspecialchars($r['anh_cong_to_nuoc']) ?>" target="_blank" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;color:var(--accent);text-decoration:none;margin-top:2px;">
                  📷 Xem ảnh công tơ nước
                </a>
                <?php endif; ?>
              </td>

              <td style="max-width:200px;">
                <span style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($r['ghi_chu'] ?: '—') ?></span>
              </td>

              <td>
                <span style="font-size:12px;color:var(--text3);"><?= date('H:i d/m/Y', strtotime($r['created_at'])) ?></span>
              </td>

              <td style="text-align:right;">
                <div style="display:inline-flex;gap:6px;align-items:center;">
                  <!-- Lập hóa đơn từ báo số này -->
                  <a href="index.php?controller=hoadon&action=create&phong_id=<?= $r['phong_id'] ?>&thang=<?= $r['thang'] ?>&nam=<?= $r['nam'] ?>&dien_moi=<?= $r['chi_so_dien'] ?>&nuoc_moi=<?= $r['chi_so_nuoc'] ?>&anh_dien=<?= urlencode($r['anh_cong_to_dien']??'') ?>&anh_nuoc=<?= urlencode($r['anh_cong_to_nuoc']??'') ?>&bcs_id=<?= $r['id'] ?>" class="btn btn-primary" style="padding:6px 12px;font-size:13px;font-weight:600;">
                    ⚡ Lập HĐ với số này
                  </a>

                  <!-- Từ chối -->
                  <form method="POST" action="index.php?controller=hoadon&action=tuChoiBaoChiSo" style="display:inline;" onsubmit="return confirm('Từ chối báo số này và yêu cầu người thuê chụp lại?')">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:13px;color:var(--red);" title="Từ chối">
                      ✕
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

