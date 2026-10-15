<?php
$title = 'Quản lý khu trọ';
require_once 'frontend/views/Layouts/header.php';
?>
<link rel="stylesheet" href="frontend/assets/css/khutro-index.css">

<div class="page-header">
  <div class="page-title">
    <h1>Quản lý khu trọ</h1>
    <p>Tổng <?=count($list??[])?> khu · Số phòng tự động theo mã khu (A101, B201...)</p>
  </div>
  <a href="index.php?controller=khutro&action=create" class="btn btn-primary">＋ Thêm khu trọ</a>
</div>

<?php if(!empty($list)): ?>
<div class="khu-grid">
<?php
$grads=[
  ['#4f8ef7','#7c5cfc'],['#22c993','#2dd4bf'],['#f7a94f','#f75c5c'],
  ['#7c5cfc','#f472b6'],['#f472b6','#f75c5c'],['#2dd4bf','#22c993'],
];
foreach($list as $i=>$khu):
  $g  = $grads[$i % count($grads)];
  $gc = "linear-gradient(135deg,{$g[0]},{$g[1]})";
  $pct= $khu['so_phong']>0 ? round($khu['dang_thue']/$khu['so_phong']*100) : 0;
?>
<div class="khu-card">
  <!-- Header -->
  <div class="khu-head" style="background:<?=$gc?>;">
    <div class="khu-head-bg"></div>
    <div class="khu-badge-ma"><?=htmlspecialchars($khu['ma_khu'])?></div>
    <div class="khu-name">
      <div class="khu-name-txt"><?=htmlspecialchars($khu['ten_khu'])?></div>
      <div class="khu-name-sub">Phòng: <?=htmlspecialchars($khu['ma_khu'])?>101, <?=htmlspecialchars($khu['ma_khu'])?>102...</div>
    </div>
    <!-- Nút actions top-right -->
    <div style="position:absolute;top:10px;right:12px;display:flex;gap:6px;">
      <a href="index.php?controller=khutro&action=edit&id=<?=$khu['id']?>"
         style="background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);color:#fff;font-size:11px;font-weight:600;padding:4px 10px;border-radius:7px;text-decoration:none;">✏ Sửa</a>
    </div>
  </div>

  <div style="padding:16px;">
    <!-- Địa chỉ -->
    <?php if(!empty($khu['dia_chi'])): ?>
    <div class="addr"><span style="flex-shrink:0;">📍</span><span><?=htmlspecialchars($khu['dia_chi'])?></span></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stat3">
      <div class="sbox">
        <div class="sval" style="color:var(--text);"><?=$khu['so_phong']?></div>
        <div class="slbl" style="color:var(--text3);">Tổng phòng</div>
      </div>
      <div class="sbox" style="background:rgba(34,201,147,.07);border-color:rgba(34,201,147,.15);">
        <div class="sval" style="color:var(--green);"><?=$khu['dang_thue']?></div>
        <div class="slbl" style="color:var(--green);">Đang thuê</div>
      </div>
      <div class="sbox" style="background:rgba(247,169,79,.07);border-color:rgba(247,169,79,.15);">
        <div class="sval" style="color:var(--amber);"><?=$khu['phong_trong']?></div>
        <div class="slbl" style="color:var(--amber);">Còn trống</div>
      </div>
    </div>

    <!-- Tỉ lệ lấp đầy -->
    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text3);margin-bottom:5px;">
      <span>Tỉ lệ lấp đầy</span>
      <strong style="color:var(--text);"><?=$pct?>%</strong>
    </div>
    <div class="fill-bar">
      <div class="fill-inner" style="width:<?=$pct?>%;background:<?=$gc?>;"></div>
    </div>

    <!-- Actions -->
    <div style="display:flex;gap:8px;">
      <a href="index.php?controller=phong&action=index&khu_id=<?=$khu['id']?>"
         class="btn btn-primary btn-sm" style="flex:1;justify-content:center;">🏠 Xem phòng</a>
      <a href="index.php?controller=phong&action=create&khu_id=<?=$khu['id']?>"
         class="btn btn-success btn-sm">＋ Thêm phòng</a>
      <form method="POST" action="index.php?controller=khutro&action=delete" style="display:inline" onsubmit="return confirm('Xóa khu <?=htmlspecialchars($khu['ten_khu'])?>? Các phòng sẽ không bị xóa.')">
        <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $khu['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm" style="cursor:pointer">🗑</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php else: ?>
<div class="card" style="text-align:center;padding:56px;">
  <div style="font-size:48px;margin-bottom:12px">🏘</div>
  <div style="font-size:16px;font-weight:800;color:var(--text2);margin-bottom:6px">Chưa có khu trọ nào</div>
  <div style="font-size:13px;color:var(--text3);margin-bottom:18px">
    Tạo khu trọ để phòng tự động được đặt tên theo khu<br>
    <strong style="color:var(--accent);">Khu A → A101, A102... &nbsp;|&nbsp; Khu B → B101, B102...</strong>
  </div>
  <a href="index.php?controller=khutro&action=create" class="btn btn-primary">＋ Thêm khu trọ đầu tiên</a>
</div>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>