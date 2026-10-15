<?php
$title = 'Duyệt người ở cùng';
require_once 'frontend/views/Layouts/header.php';
$filter = $_GET['filter'] ?? 'all';
$ttMap = [
    'cho_duyet' => ['Chờ duyệt', 'p-amber'],
    'da_duyet'  => ['Đã duyệt', 'p-green'],
    'tu_choi'   => ['Từ chối', 'p-red'],
    'da_huy'    => ['Đã hủy', 'p-purple'],
];
?>

<div class="page-header">
  <div class="page-title">
    <h1>Duyệt người ở cùng</h1>
    <p>Xét duyệt yêu cầu thêm người ở cùng do người thuê gửi</p>
  </div>
  <a href="index.php?controller=nguoithue&action=index" class="btn btn-outline">← Danh sách người thuê</a>
</div>

<?php if (!empty($_GET['err'])): ?>
<div class="msg-alert msg-error">⚠ <?= htmlspecialchars($_GET['err']) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:18px;">
  <div class="card-body" style="display:flex;align-items:center;gap:16px;">
    <div style="width:52px;height:52px;border-radius:14px;background:rgba(247,169,79,.14);display:flex;align-items:center;justify-content:center;font-size:24px;">👥</div>
    <div>
      <div style="font-size:24px;font-weight:800;color:var(--text);"><?= (int)$soChoDuyet ?></div>
      <div style="font-size:12px;color:var(--text3);">Yêu cầu đang chờ duyệt</div>
    </div>
  </div>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach (['all'=>'Tất cả','cho_duyet'=>'Chờ duyệt','da_duyet'=>'Đã duyệt','tu_choi'=>'Từ chối','da_huy'=>'Đã hủy'] as $value=>$label): ?>
  <a href="index.php?controller=nguoithue&action=yeuCauOCung&filter=<?= $value ?>"
     class="btn <?= $filter === $value ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php $filtered = array_filter($requests, fn($x) => $filter === 'all' || $x['trang_thai'] === $filter); ?>
  <?php if (!$filtered): ?>
  <div style="text-align:center;padding:48px 16px;color:var(--text3);">
    <div style="font-size:38px;margin-bottom:10px;">📭</div>
    Không có yêu cầu thêm người ở cùng phù hợp.
  </div>
  <?php else: ?>
  <?php foreach ($filtered as $yc):
    $tt = $ttMap[$yc['trang_thai']] ?? $ttMap['cho_duyet'];
  ?>
  <div style="padding:18px 20px;border-bottom:1px solid var(--border);<?= $yc['trang_thai'] === 'cho_duyet' ? 'border-left:3px solid var(--amber);' : '' ?>">
    <div style="display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;">
      <div style="flex:1;min-width:280px;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:9px;">
          <span class="pill <?= $tt[1] ?>"><?= $tt[0] ?></span>
          <span style="font-size:11px;color:var(--text3);"><?= date('d/m/Y H:i', strtotime($yc['created_at'])) ?></span>
        </div>
        <div style="font-size:15px;font-weight:800;color:var(--text);margin-bottom:6px;">
          <?= htmlspecialchars($yc['ho_ten']) ?>
          <?php if (!empty($yc['sdt'])): ?><span style="font-size:12px;color:var(--text3);font-weight:500;"> · <?= htmlspecialchars($yc['sdt']) ?></span><?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:9px;">
          <span class="pill p-blue">Phòng <?= htmlspecialchars($yc['so_phong']) ?><?= !empty($yc['ten_khu']) ? ' · '.htmlspecialchars($yc['ten_khu']) : '' ?></span>
          <span class="pill p-purple">Người gửi: <?= htmlspecialchars($yc['nguoi_gui']) ?></span>
        </div>
        <div style="font-size:13px;color:var(--text2);line-height:1.7;">
          <?php if (!empty($yc['cccd'])): ?><strong>CCCD:</strong> <?= htmlspecialchars($yc['cccd']) ?> · <?php endif; ?>
          <?php if (!empty($yc['ngay_sinh'])): ?><strong>Ngày sinh:</strong> <?= date('d/m/Y', strtotime($yc['ngay_sinh'])) ?> · <?php endif; ?>
          <strong>Giới tính:</strong> <?= $yc['gioi_tinh'] === 'nu' ? 'Nữ' : ($yc['gioi_tinh'] === 'khac' ? 'Khác' : 'Nam') ?>
          <?php if (!empty($yc['que_quan'])): ?><br><strong>Quê quán:</strong> <?= htmlspecialchars($yc['que_quan']) ?><?php endif; ?>
          <?php if (!empty($yc['ly_do'])): ?><br><strong>Lý do:</strong> <?= nl2br(htmlspecialchars($yc['ly_do'])) ?><?php endif; ?>
        </div>
        <?php if (!empty($yc['phan_hoi_ql'])): ?>
        <div style="font-size:12px;margin-top:9px;padding:9px 11px;border-radius:8px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.16);color:var(--text2);">
          Phản hồi quản lý: <?= nl2br(htmlspecialchars($yc['phan_hoi_ql'])) ?>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($yc['trang_thai'] === 'cho_duyet'): ?>
      <form method="POST" action="index.php?controller=nguoithue&action=xuLyOCung" style="flex:0 1 320px;min-width:260px;display:flex;flex-direction:column;gap:8px;">
            <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)$yc['id'] ?>">
        <textarea name="phan_hoi_ql" class="form-control" rows="3" placeholder="Phản hồi cho người thuê..."></textarea>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button type="submit" name="quyet_dinh" value="duyet" class="btn btn-success" style="flex:1;justify-content:center;" onclick="return confirm('Duyệt thêm người này vào phòng?')">Duyệt</button>
          <button type="submit" name="quyet_dinh" value="tu_choi" class="btn btn-danger" style="flex:1;justify-content:center;" onclick="return confirm('Từ chối yêu cầu này?')">Từ chối</button>
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

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
