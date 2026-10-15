<?php
$title = $title ?? 'Lịch sử giá phòng';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>📊 Lịch sử giá</h1>
    <p>Phòng <?= htmlspecialchars($phong['so_phong'] ?? '') ?> — Giá hiện tại: <strong style="color:var(--accent);"><?= number_format((float)($phong['gia'] ?? 0)) ?>đ</strong></p>
  </div>
  <a href="index.php?controller=phong&action=edit&id=<?= $phong['id'] ?>" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:600px;">
  <?php if(!empty($lichSuGia)): ?>
  <div class="card">
    <div class="card-header">
      <div class="card-title">Thay đổi giá phòng <?= htmlspecialchars($phong['so_phong']) ?></div>
      <div class="card-sub"><?= count($lichSuGia) ?> lần thay đổi</div>
    </div>
    <?php foreach($lichSuGia as $ls): ?>
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:13px;color:var(--text2);">📅 <?= date('d/m/Y', strtotime($ls['ngay_thay_doi'])) ?></span>
        <?php
          $diff = (float)$ls['gia_moi'] - (float)$ls['gia_cu'];
          $pct  = (float)$ls['gia_cu'] > 0 ? round($diff / (float)$ls['gia_cu'] * 100, 1) : 0;
          $isUp = $diff >= 0;
        ?>
        <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:6px;
                     background:<?= $isUp ? 'rgba(247,92,92,.1)' : 'rgba(34,201,147,.1)' ?>;
                     color:<?= $isUp ? 'var(--red)' : 'var(--green)' ?>;">
          <?= $isUp ? '↑' : '↓' ?> <?= abs($pct) ?>%
        </span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
        <span style="font-size:15px;color:var(--text3);text-decoration:line-through;"><?= number_format((float)$ls['gia_cu']) ?>đ</span>
        <span style="font-size:16px;color:var(--text3);">→</span>
        <span style="font-size:17px;font-weight:800;color:<?= $isUp ? 'var(--red)' : 'var(--green)' ?>;"><?= number_format((float)$ls['gia_moi']) ?>đ</span>
      </div>
      <?php if(!empty($ls['ghi_chu'])): ?>
      <div style="font-size:12px;color:var(--text3);font-style:italic;">💬 <?= htmlspecialchars($ls['ghi_chu']) ?></div>
      <?php endif; ?>
      <?php if(!empty($ls['nguoi_thay_doi'])): ?>
      <div style="font-size:11px;color:var(--text3);margin-top:3px;">👤 <?= htmlspecialchars($ls['nguoi_thay_doi']) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:48px;">
    <div style="font-size:36px;margin-bottom:10px;">📊</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);">Chưa có thay đổi giá</div>
    <div style="font-size:13px;color:var(--text3);margin-top:4px;">Lịch sử sẽ được ghi lại khi bạn thay đổi giá phòng</div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
