<?php
$title = 'Chuyển phòng';
require_once 'frontend/views/Layouts/header.php';
$isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true);
$ttMap = [
    'cho_duyet' => ['⏳', 'Chờ duyệt', 'p-amber'],
    'da_duyet'  => ['✅', 'Đã duyệt', 'p-green'],
    'tu_choi'   => ['❌', 'Từ chối',  'p-red'],
    'da_huy'    => ['↩',  'Đã hủy',   'p-purple'],
];
?>

<link rel="stylesheet" href="frontend/assets/css/chuyenphong-index.css">

<div class="transfer-page">
<div class="page-header">
  <div class="page-title">
    <h1>🔄 Chuyển phòng</h1>
    <p><?= $isAdmin ? 'Duyệt yêu cầu chuyển phòng của người thuê' : 'Gửi yêu cầu và theo dõi phản hồi từ quản lý' ?></p>
  </div>
</div>

<?php if (!empty($_GET['err'])): ?>
<div class="msg-alert msg-error">⚠ <?= htmlspecialchars($_GET['err']) ?></div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<!-- ADMIN -->
<div class="msg-alert msg-info">
  ⚠ Tính năng chuyển phòng hiện chỉ cập nhật phòng trên hợp đồng, người ở và xe. Hệ thống chưa tự tính bù trừ tiền phòng giữa tháng, chênh lệch tiền cọc hoặc ngày bắt đầu áp dụng giá phòng mới. Vui lòng xử lý các khoản này thủ công trước khi duyệt.
</div>

<div class="card transfer-card" style="margin-bottom:18px;">
  <div class="card-body" style="display:flex;align-items:center;gap:16px;">
    <div style="width:52px;height:52px;border-radius:14px;background:rgba(247,169,79,.14);display:flex;align-items:center;justify-content:center;font-size:24px;">⏳</div>
    <div>
      <div style="font-size:24px;font-weight:800;color:var(--text);"><?= (int)$soChoDuyet ?></div>
      <div style="font-size:12px;color:var(--text3);">Yêu cầu đang chờ duyệt</div>
    </div>
  </div>
</div>

<?php $filter = $_GET['filter'] ?? 'all'; ?>
<div class="transfer-filter">
  <?php foreach (['all'=>'Tất cả','cho_duyet'=>'⏳ Chờ duyệt','da_duyet'=>'✅ Đã duyệt','tu_choi'=>'❌ Từ chối','da_huy'=>'↩ Đã hủy'] as $value=>$label): ?>
  <a href="index.php?controller=chuyenphong&action=index&filter=<?= $value ?>"
     class="btn <?= $filter === $value ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="card transfer-card">
  <?php $filtered = array_filter($requests, fn($x) => $filter === 'all' || $x['trang_thai'] === $filter); ?>
  <?php if (!$filtered): ?>
  <div class="transfer-empty">
    <div style="font-size:38px;margin-bottom:10px;">📭</div>
    Không có yêu cầu chuyển phòng phù hợp.
  </div>
  <?php else: ?>
  <?php foreach ($filtered as $yc): $tt = $ttMap[$yc['trang_thai']] ?? $ttMap['cho_duyet']; ?>
  <div class="transfer-item" style="<?= $yc['trang_thai'] === 'cho_duyet' ? 'border-left:3px solid var(--amber);' : '' ?>">
    <div class="transfer-row">
      <div class="transfer-main">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:9px;">
          <span class="pill <?= $tt[2] ?>"><?= $tt[0] . ' ' . $tt[1] ?></span>
          <span style="font-size:11px;color:var(--text3);">🕐 <?= date('d/m/Y H:i', strtotime($yc['created_at'])) ?></span>
        </div>
        <div style="font-size:15px;font-weight:800;color:var(--text);margin-bottom:6px;">
          👤 <?= htmlspecialchars($yc['ho_ten']) ?>
          <?php if (!empty($yc['sdt'])): ?><span style="font-size:12px;color:var(--text3);font-weight:500;"> · <?= htmlspecialchars($yc['sdt']) ?></span><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:9px;">
          <span class="pill p-blue">Phòng <?= htmlspecialchars($yc['phong_cu']) ?><?= !empty($yc['khu_cu']) ? ' · '.htmlspecialchars($yc['khu_cu']) : '' ?></span>
          <span style="color:var(--accent);font-weight:800;">→</span>
          <span class="pill p-green">Phòng <?= htmlspecialchars($yc['phong_moi']) ?><?= !empty($yc['khu_moi']) ? ' · '.htmlspecialchars($yc['khu_moi']) : '' ?></span>
        </div>
        <div style="font-size:13px;color:var(--text2);line-height:1.6;">
          <strong style="color:var(--text);">Lý do:</strong> <?= nl2br(htmlspecialchars($yc['ly_do'])) ?>
        </div>
        <?php if (!empty($yc['phan_hoi_ql'])): ?>
        <div style="font-size:12px;margin-top:9px;padding:9px 11px;border-radius:8px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.16);color:var(--text2);">
          💬 Phản hồi quản lý: <?= nl2br(htmlspecialchars($yc['phan_hoi_ql'])) ?>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($yc['trang_thai'] === 'cho_duyet'): ?>
      <form method="POST" action="index.php?controller=chuyenphong&action=xuLy" class="transfer-admin-form">
            <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)$yc['id'] ?>"/>
        <textarea name="phan_hoi_ql" class="form-control" rows="3" placeholder="Ghi chú phản hồi cho người thuê..."></textarea>
        <div class="transfer-admin-actions">
          <button type="submit" name="quyet_dinh" value="duyet" class="btn btn-success" style="flex:1;justify-content:center;" onclick="return confirm('Duyệt chuyển phòng? Hệ thống chỉ chuyển hợp đồng, người ở và xe sang phòng mới; chưa tự tính bù trừ tiền phòng, tiền cọc hoặc ngày áp dụng giá mới.')">✅ Duyệt</button>
          <button type="submit" name="quyet_dinh" value="tu_choi" class="btn btn-danger" style="flex:1;justify-content:center;" onclick="return confirm('Từ chối yêu cầu này?')">❌ Từ chối</button>
        </div>
      </form>
      <?php else: ?>
      <div style="min-width:180px;font-size:12px;color:var(--text3);line-height:1.7;">
        <?php if (!empty($yc['nguoi_duyet'])): ?>Xử lý bởi: <strong style="color:var(--text2);"><?= htmlspecialchars($yc['nguoi_duyet']) ?></strong><br><?php endif; ?>
        <?php if (!empty($yc['duyet_luc'])): ?>Lúc: <?= date('d/m/Y H:i', strtotime($yc['duyet_luc'])) ?><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- USER -->
<div class="msg-alert msg-info">
  ℹ Yêu cầu chuyển phòng chỉ gửi để quản lý xét duyệt phòng mới. Tiền phòng giữa tháng, chênh lệch tiền cọc và ngày bắt đầu áp dụng giá mới sẽ được quản lý xác nhận riêng.
</div>

<?php if (!$currentRental): ?>
<div class="card transfer-card" style="text-align:center;padding:54px 24px;">
  <div style="font-size:44px;margin-bottom:12px;">📄</div>
  <div style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:6px;">Chưa có hợp đồng hiệu lực</div>
  <div style="font-size:13px;color:var(--text2);">Bạn cần có phòng đang thuê để gửi yêu cầu chuyển phòng.</div>
</div>
<?php else: ?>
<div class="card transfer-card" style="margin-bottom:20px;max-width:760px;">
  <div class="card-header">
    <div>
      <div class="card-title">📤 Gửi yêu cầu chuyển phòng</div>
      <div class="card-sub">Phòng hiện tại: <?= htmlspecialchars($currentRental['so_phong']) ?><?= !empty($currentRental['ten_khu']) ? ' · '.htmlspecialchars($currentRental['ten_khu']) : '' ?></div>
    </div>
    <?php if ($soChoDuyet > 0): ?><span class="pill p-amber">Đang chờ duyệt</span><?php endif; ?>
  </div>
  <div class="card-body">
    <?php if ($soChoDuyet > 0): ?>
    <div class="msg-alert msg-info" style="margin-bottom:0;">⏳ Bạn đã gửi yêu cầu chuyển phòng. Vui lòng chờ quản lý xử lý hoặc hủy yêu cầu cũ trước khi gửi yêu cầu mới.</div>
    <?php elseif (!$phongTrong): ?>
    <div class="msg-alert msg-info" style="margin-bottom:0;">🏠 Hiện chưa có phòng trống để chuyển đến.</div>
    <?php else: ?>
    <form method="POST" action="index.php?controller=chuyenphong&action=guiYeuCau">
            <?= Csrf::field() ?>
      <div class="form-group">
        <label class="form-label">Phòng muốn chuyển đến <span style="color:var(--red);">*</span></label>
        <select name="phong_moi_id" class="form-control" required>
          <option value="">-- Chọn phòng còn trống --</option>
          <?php foreach ($phongTrong as $p): ?>
          <option value="<?= (int)$p['id'] ?>">
            Phòng <?= htmlspecialchars($p['so_phong']) ?><?= !empty($p['ten_khu']) ? ' · '.htmlspecialchars($p['ten_khu']) : '' ?> · <?= number_format((float)$p['gia']) ?>đ/tháng
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Lý do chuyển phòng <span style="color:var(--red);">*</span></label>
        <textarea name="ly_do" class="form-control" rows="4" maxlength="1000" required placeholder="Ví dụ: muốn chuyển sang phòng rộng hơn, gần tầng trệt hơn..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="justify-content:center;">📤 Gửi cho quản lý duyệt</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="card transfer-card">
  <div class="card-header">
    <div class="card-title">📋 Lịch sử yêu cầu của bạn</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($requests) ?> yêu cầu</span>
  </div>
  <?php if (!$requests): ?>
  <div style="text-align:center;padding:42px;color:var(--text3);">
    <div style="font-size:34px;margin-bottom:8px;">📭</div>
    Bạn chưa gửi yêu cầu chuyển phòng nào.
  </div>
  <?php else: ?>
  <?php foreach ($requests as $yc): $tt = $ttMap[$yc['trang_thai']] ?? $ttMap['cho_duyet']; ?>
  <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
    <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
      <div style="flex:1;min-width:220px;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">
          <span class="pill <?= $tt[2] ?>"><?= $tt[0] . ' ' . $tt[1] ?></span>
          <span style="font-size:11px;color:var(--text3);">🕐 <?= date('d/m/Y H:i', strtotime($yc['created_at'])) ?></span>
        </div>
        <div style="font-size:14px;font-weight:800;color:var(--text);margin-bottom:6px;">
          Phòng <?= htmlspecialchars($yc['phong_cu']) ?> → Phòng <?= htmlspecialchars($yc['phong_moi']) ?>
        </div>
        <div style="font-size:13px;color:var(--text2);line-height:1.6;"><?= nl2br(htmlspecialchars($yc['ly_do'])) ?></div>
        <?php if (!empty($yc['phan_hoi_ql'])): ?>
        <div style="font-size:12px;margin-top:8px;padding:8px 10px;border-radius:8px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.16);color:var(--text2);">💬 Phản hồi quản lý: <?= nl2br(htmlspecialchars($yc['phan_hoi_ql'])) ?></div>
        <?php endif; ?>
      </div>
      <?php if ($yc['trang_thai'] === 'cho_duyet'): ?>
      <form method="POST" action="index.php?controller=chuyenphong&action=huyYeuCau">
            <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)$yc['id'] ?>"/>
        <button class="btn btn-outline btn-sm" onclick="return confirm('Hủy yêu cầu chuyển phòng này?')">↩ Hủy yêu cầu</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php endif; ?>

</div>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>
