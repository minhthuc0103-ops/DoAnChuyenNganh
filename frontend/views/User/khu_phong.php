<?php
$title = 'Khu & Phòng trọ';
require_once 'frontend/views/Layouts/header.php';
?>
<link rel="stylesheet" href="frontend/assets/css/khutro-index.css">

<div class="page-header">
  <div class="page-title">
    <h1>Khu & Phòng trọ</h1>
    <p>Tổng <?= count($list??[]) ?> khu trọ trong hệ thống</p>
  </div>
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
  <div class="khu-head" style="background:<?=$gc?>;">
    <div class="khu-head-bg"></div>
    <div class="khu-badge-ma"><?=htmlspecialchars($khu['ma_khu'])?></div>
    <div class="khu-name">
      <div class="khu-name-txt"><?=htmlspecialchars($khu['ten_khu'])?></div>
      <div class="khu-name-sub">Phòng: <?=htmlspecialchars($khu['ma_khu'])?>101, <?=htmlspecialchars($khu['ma_khu'])?>102...</div>
    </div>
  </div>

  <div style="padding:16px;">
    <?php if(!empty($khu['dia_chi'])): ?>
    <div class="addr"><span style="flex-shrink:0;">📍</span><span><?=htmlspecialchars($khu['dia_chi'])?></span></div>
    <?php endif; ?>

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

    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text3);margin-bottom:5px;">
      <span>Tỉ lệ lấp đầy</span>
      <strong style="color:var(--text);"><?=$pct?>%</strong>
    </div>
    <div class="fill-bar">
      <div class="fill-inner" style="width:<?=$pct?>%;background:<?=$gc?>;"></div>
    </div>

    <a href="index.php?controller=user&action=phong&khu_id=<?=$khu['id']?>"
       class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">🏠 Xem danh sách phòng</a>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php else: ?>
<div class="card" style="text-align:center;padding:56px;">
  <div style="font-size:48px;margin-bottom:12px">🏘</div>
  <div style="font-size:16px;font-weight:800;color:var(--text2);margin-bottom:6px">Chưa có khu trọ nào</div>
  <div style="font-size:13px;color:var(--text3);">Liên hệ quản lý để biết thêm thông tin.</div>
</div>
<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
