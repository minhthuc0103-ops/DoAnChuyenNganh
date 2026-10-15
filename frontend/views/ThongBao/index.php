<?php
$title   = 'Bảo trì';
require_once 'frontend/views/Layouts/header.php';
$isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']);

$mucDoMap = [
  'nhe'        => ['🟢', 'Nhẹ',        'p-green'],
  'trung_binh' => ['🟡', 'Trung bình', 'p-amber'],
  'khan_cap'   => ['🔴', 'Khẩn cấp',  'p-red'],
];

$ttMap = [
  'cho_xu_ly'  => ['⏳', 'Chờ xử lý',  'p-amber'],
  'dang_xu_ly' => ['🔧', 'Đang xử lý', 'p-blue'],
  'da_xong'    => ['✅', 'Đã xong',    'p-green'],
];
?>

<div class="page-header">
  <div class="page-title">
    <h1>🔧 Bảo trì</h1>
    <p>
      <?php if($isAdmin): ?>
        <?= count($suaChuaList ?? []) ?> yêu cầu
        <?php if(($soChoXuLy ?? 0) > 0): ?>
          · <strong style="color:var(--amber);"><?= $soChoXuLy ?> chờ xử lý</strong>
        <?php endif; ?>
      <?php else: ?>
        Yêu cầu sửa chữa phòng của bạn
      <?php endif; ?>
    </p>
  </div>
  <?php if(!$isAdmin): ?>
  <div class="header-actions">
    <button onclick="document.getElementById('formSuaChua').scrollIntoView({behavior:'smooth'})"
            class="btn btn-primary">＋ Gửi yêu cầu mới</button>
  </div>
  <?php endif; ?>
</div>

<?php if(!empty($_GET['msg'])): ?>
<div class="msg-alert msg-success" style="margin-bottom:14px;">
  <?= $_GET['msg']==='sent' ? '✓ Đã gửi yêu cầu! Quản lý sẽ xử lý sớm.' : '✓ Cập nhật thành công.' ?>
</div>
<?php endif; ?>

<?php if($isAdmin): ?>
<!-- ═══ ADMIN: Danh sách tất cả yêu cầu ═══ -->

<!-- Stat nhanh -->
<?php
  $sc_all   = count($suaChuaList ?? []);
  $sc_cho   = count(array_filter($suaChuaList??[], fn($x)=>$x['trang_thai']==='cho_xu_ly'));
  $sc_dang  = count(array_filter($suaChuaList??[], fn($x)=>$x['trang_thai']==='dang_xu_ly'));
  $sc_xong  = count(array_filter($suaChuaList??[], fn($x)=>$x['trang_thai']==='da_xong'));
?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
  <?php foreach([
    ['🔧','Tổng yêu cầu',$sc_all,'rgba(79,142,247,.15)'],
    ['⏳','Chờ xử lý',$sc_cho,'rgba(247,169,79,.15)'],
    ['⚙','Đang xử lý',$sc_dang,'rgba(79,142,247,.15)'],
    ['✅','Đã xong',$sc_xong,'rgba(34,201,147,.12)'],
  ] as [$ico,$lbl,$val,$bg]): ?>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:<?=$bg?>;display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;"><?=$ico?></div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$val?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;"><?=$lbl?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Bộ lọc -->
<?php $filterTT = $_GET['filter'] ?? 'all'; ?>
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach(['all'=>'Tất cả','cho_xu_ly'=>'⏳ Chờ xử lý','dang_xu_ly'=>'🔧 Đang xử lý','da_xong'=>'✅ Đã xong'] as $val=>$lbl): ?>
  <a href="?controller=thongbao&action=index&filter=<?=$val?>"
     class="btn <?= $filterTT===$val ? 'btn-primary' : 'btn-outline' ?>"
     style="font-size:12px;padding:6px 14px;"><?=$lbl?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php
    $filtered = array_filter($suaChuaList??[], fn($x) => $filterTT==='all' || $x['trang_thai']===$filterTT);
  ?>
  <?php if(empty($filtered)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">✅</div>
    Không có yêu cầu nào.
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;">
    <?php foreach($filtered as $sc):
      $md = $mucDoMap[$sc['muc_do']] ?? $mucDoMap['trung_binh'];
      $tt = $ttMap[$sc['trang_thai']] ?? $ttMap['cho_xu_ly'];
    ?>
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);
                <?= $sc['trang_thai']==='cho_xu_ly' ? 'border-left:3px solid var(--amber);' : ($sc['trang_thai']==='da_xong' ? 'opacity:.7;' : '') ?>">
      <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
            <span class="pill <?=$md[2]?>"><?=$md[0].' '.$md[1]?></span>
            <span class="pill <?=$tt[2]?>"><?=$tt[0].' '.$tt[1]?></span>
            <?php if(!empty($sc['phong'])): ?>
            <span class="pill p-blue">Phòng <?= htmlspecialchars($sc['phong']) ?></span>
            <?php endif; ?>
          </div>
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;">
            📍 <?= htmlspecialchars($sc['vi_tri']) ?>
          </div>
          <div style="font-size:13px;color:var(--text2);line-height:1.6;margin-bottom:6px;">
            <?= htmlspecialchars($sc['mo_ta']) ?>
          </div>
          <?php if(!empty($sc['ghi_chu_ql'])): ?>
          <div style="font-size:12px;padding:8px 12px;background:rgba(79,142,247,.06);border:1px solid rgba(79,142,247,.15);border-radius:8px;color:var(--text2);">
            💬 Phản hồi: <?= htmlspecialchars($sc['ghi_chu_ql']) ?>
          </div>
          <?php endif; ?>
          <div style="font-size:11px;color:var(--text3);margin-top:6px;">
            👤 <?= htmlspecialchars($sc['ho_ten']) ?>
            · 🕐 <?= date('d/m/Y H:i', strtotime($sc['created_at'])) ?>
          </div>
        </div>
        <!-- Cập nhật trạng thái -->
        <form method="POST" action="index.php?controller=thongbao&action=capNhatSuaChua"
              style="display:flex;flex-direction:column;gap:8px;min-width:200px;flex-shrink:0;">
            <?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= $sc['id'] ?>"/>
          <select name="trang_thai" class="form-control" style="font-size:12px;padding:6px 10px;">
            <?php foreach($ttMap as $val=>$info): ?>
            <option value="<?=$val?>" <?= $sc['trang_thai']===$val?'selected':'' ?>>
              <?= $info[0].' '.$info[1] ?>
            </option>
            <?php endforeach; ?>
          </select>
          <input class="form-control" type="text" name="ghi_chu_ql"
                 placeholder="Ghi chú phản hồi..."
                 value="<?= htmlspecialchars($sc['ghi_chu_ql']??'') ?>"
                 style="font-size:12px;padding:6px 10px;"/>
          <button type="submit" class="btn btn-primary" style="font-size:12px;padding:7px;justify-content:center;">
            💾 Cập nhật
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- ═══ USER: Form gửi + lịch sử của mình ═══ -->

<!-- Form gửi yêu cầu -->
<div class="card" id="formSuaChua" style="margin-bottom:20px;max-width:620px;">
  <div class="card-header">
    <div class="card-title">📤 Gửi yêu cầu sửa chữa</div>
    <?php if(!empty($soPhongUser)): ?>
    <span class="pill p-blue">Phòng <?= htmlspecialchars($soPhongUser) ?></span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" action="index.php?controller=thongbao&action=guiSuaChua">
            <?= Csrf::field() ?>
      <div class="form-group">
        <label class="form-label">Vị trí hư hỏng <span style="color:var(--red)">*</span></label>
        <input class="form-control" type="text" name="vi_tri"
               placeholder="vd: Vòi nước phòng tắm, Bóng đèn hành lang, Cửa phòng..."
               required/>
      </div>
      <div class="form-group">
        <label class="form-label">Mô tả chi tiết <span style="color:var(--red)">*</span></label>
        <textarea class="form-control" name="mo_ta" rows="3"
                  placeholder="Mô tả tình trạng hư hỏng..." required></textarea>
      </div>
      <div class="form-group" style="margin-bottom:18px;">
        <label class="form-label">Mức độ</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
          <?php foreach([
            'nhe'        => ['🟢','Nhẹ',       'Có thể đợi'],
            'trung_binh' => ['🟡','Trung bình','Cần sửa sớm'],
            'khan_cap'   => ['🔴','Khẩn cấp', 'Ảnh hưởng sinh hoạt'],
          ] as $val=>$info): ?>
          <label class="muc-do-label <?= $val==='trung_binh'?'active':'' ?>"
                 style="border:1.5px solid var(--border);border-radius:10px;padding:10px;cursor:pointer;transition:all .15s;text-align:center;"
                 onclick="selectMucDo(this)">
            <input type="radio" name="muc_do" value="<?=$val?>"
                   <?= $val==='trung_binh'?'checked':'' ?> style="display:none;"/>
            <div style="font-size:22px;margin-bottom:4px;"><?=$info[0]?></div>
            <div style="font-size:13px;font-weight:700;color:var(--text);"><?=$info[1]?></div>
            <div style="font-size:11px;color:var(--text3);"><?=$info[2]?></div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;">
        📤 Gửi yêu cầu
      </button>
    </form>
  </div>
</div>

<!-- Lịch sử yêu cầu của user -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Lịch sử yêu cầu của bạn</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($suaChuaList??[]) ?> yêu cầu</span>
  </div>
  <?php if(empty($suaChuaList)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">📭</div>
    Bạn chưa có yêu cầu sửa chữa nào.
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;">
    <?php foreach($suaChuaList as $sc):
      $md = $mucDoMap[$sc['muc_do']] ?? $mucDoMap['trung_binh'];
      $tt = $ttMap[$sc['trang_thai']] ?? $ttMap['cho_xu_ly'];
    ?>
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);
                <?= $sc['trang_thai']==='da_xong' ? 'opacity:.65;' : '' ?>">
      <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div style="width:42px;height:42px;border-radius:11px;
                    background:<?= $sc['muc_do']==='khan_cap'?'rgba(247,92,92,.12)':($sc['muc_do']==='trung_binh'?'rgba(247,169,79,.12)':'rgba(34,201,147,.12)') ?>;
                    display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
          <?= $md[0] ?>
        </div>
        <div style="flex:1;min-width:150px;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
            <span class="pill <?=$md[2]?>"><?=$md[1]?></span>
            <span class="pill <?=$tt[2]?>"><?=$tt[0].' '.$tt[1]?></span>
          </div>
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:3px;">
            📍 <?= htmlspecialchars($sc['vi_tri']) ?>
          </div>
          <div style="font-size:13px;color:var(--text2);margin-bottom:5px;">
            <?= htmlspecialchars($sc['mo_ta']) ?>
          </div>
          <?php if(!empty($sc['ghi_chu_ql'])): ?>
          <div style="font-size:12px;padding:8px 12px;background:rgba(34,201,147,.07);border:1px solid rgba(34,201,147,.2);border-radius:8px;color:var(--text2);">
            💬 Phản hồi quản lý: <?= htmlspecialchars($sc['ghi_chu_ql']) ?>
          </div>
          <?php endif; ?>
          <div style="font-size:11px;color:var(--text3);margin-top:5px;">
            🕐 <?= date('d/m/Y H:i', strtotime($sc['created_at'])) ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<link rel="stylesheet" href="frontend/assets/css/thongbao-index.css">
<script src="frontend/assets/js/thongbao-index.js"></script>

<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
