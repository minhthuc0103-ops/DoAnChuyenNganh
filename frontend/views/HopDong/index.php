<?php
$title = 'Quản lý hợp đồng';
require_once 'frontend/views/Layouts/header.php';
?>
<link rel="stylesheet" href="frontend/assets/css/hopdong-index.css">

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="page-title">
    <h1>Quản lý hợp đồng</h1>
    <p>Tổng <?= count($list??[]) ?> hợp đồng · Bấm vào thẻ để xem thông tin người thuê</p>
  </div>
  <a href="index.php?controller=hopdong&action=create" class="btn btn-primary">＋ Tạo hợp đồng</a>
</div>

<!-- FILTER -->
<div style="display:flex;gap:7px;margin-bottom:22px;flex-wrap:wrap;">
<?php
$filter=$_GET['filter']??'all';
$tabs=['all'=>'Tất cả','hieu_luc'=>'Đang hiệu lực','het_han'=>'Hết hạn','da_huy'=>'Đã hủy'];
foreach($tabs as $val=>$lbl): $a=($filter===$val); ?>
<a href="index.php?controller=hopdong&action=index&filter=<?=$val?>"
   style="padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
          <?=$a?'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;box-shadow:0 4px 14px rgba(79,142,247,.28);':'background:var(--card);color:var(--text2);border:1px solid var(--border);'?>">
  <?=$lbl?>
</a>
<?php endforeach; ?>
</div>

<?php if(!empty($list)): ?>
<div class="hd-grid">
<?php
$gradients=[
  ['#4f8ef7','#7c5cfc'],['#22c993','#2dd4bf'],['#f7a94f','#f75c5c'],
  ['#7c5cfc','#f472b6'],['#2dd4bf','#4f8ef7'],['#f472b6','#7c5cfc'],
];
foreach($list as $idx=>$hd):
  $kt   = strtotime($hd['ngay_ket_thuc']);
  $diff = ($kt - time()) / 86400;
  $exp  = ($hd['trang_thai']==='hieu_luc' && $diff>0 && $diff<=30);
  $g    = $gradients[$idx % count($gradients)];
  $gc   = "linear-gradient(135deg,{$g[0]},{$g[1]})";

  // Người thuê: ưu tiên nguoi_o_cung, fallback sang thông tin HĐ
  $nguoiList = $hd['nguoi_o_cung'] ?? [];
  $soNguoi   = count($nguoiList);
  $soXe      = count($hd['xe_list'] ?? []);

  // Tìm chủ HĐ để hiển thị trên card
  $chuHD = null;
  foreach($nguoiList as $n) { if($n['la_chu_hop_dong']) { $chuHD=$n; break; } }
  // Nếu chưa có trong nguoi_thue_phong, dùng thông tin từ JOIN
  if(!$chuHD) {
    $chuHD = [
      'ho_ten'         => $hd['ho_ten'],
      'sdt'            => $hd['sdt']   ?? '',
      'cccd'           => $hd['cccd']  ?? '',
      'dia_chi'        => $hd['dia_chi']?? '',
      'avatar'         => $hd['avatar']?? '',
      'la_chu_hop_dong'=> 1,
      'ngay_sinh'      => '',
      'gioi_tinh'      => '',
      'que_quan'       => $hd['dia_chi'] ?? '',
    ];
    $soNguoi = max($soNguoi, 1);
  }

  // Initials
  $w = explode(' ', $chuHD['ho_ten']);
  $init = implode('', array_map(fn($x)=>mb_strtoupper(mb_substr($x,0,1,'UTF-8')), array_slice($w,-2)));

  // JSON cho modal
  // Nếu nguoiList rỗng, tạo array từ chủ HĐ
  $nguoiModal = count($nguoiList) > 0 ? $nguoiList : [array_merge($chuHD,['id'=>0])];
  $md = json_encode([
    'id'        => $hd['id'],
    'so_phong'  => $hd['so_phong'],
    'trang_thai'=> $hd['trang_thai'],
    'ngay_bd'   => date('d/m/Y', strtotime($hd['ngay_bat_dau'])),
    'ngay_kt'   => date('d/m/Y', $kt),
    'tien_coc'  => number_format($hd['tien_coc']),
    'ghi_chu'   => $hd['ghi_chu'] ?? '',
    'diff'      => (int)ceil($diff),
    'gc'        => "{$g[0]},{$g[1]}",
    'nguoi'     => $nguoiModal,
    'xe'        => array_values(array_map(fn($x) => [
      'id'      => (int)$x['id'],
      'bien_so' => $x['bien_so'],
      'loai_xe' => $x['loai_xe'],
      'mau_sac' => $x['mau_sac'] ?? '',
      'ghi_chu' => $x['ghi_chu'] ?? '',
    ], $hd['xe_list'] ?? [])),
  ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
?>
<div class="hd-card" data-hd-id="<?= (int)$hd['id'] ?>" onclick='openModal(<?=htmlspecialchars($md,ENT_QUOTES)?>)'>

  <!-- HEADER GRADIENT -->
  <div class="hd-head" style="background:<?=$gc?>;">
    <div class="hd-head-bg"></div>
    <div class="hd-room">
      <div class="hd-room-name">Phòng <?=htmlspecialchars($hd['so_phong'])?></div>
    </div>
    <!-- STATUS BADGE -->
    <?php if($hd['trang_thai']==='hieu_luc'): ?>
      <?php if($exp): ?>
      <div class="hd-badge" style="background:rgba(247,169,79,.9);color:#fff;">⚠ Còn <?=(int)ceil($diff)?> ngày</div>
      <?php else: ?>
      <div class="hd-badge" style="background:rgba(34,201,147,.9);color:#fff;">● Hiệu lực</div>
      <?php endif;?>
    <?php elseif($hd['trang_thai']==='het_han'): ?>
      <div class="hd-badge" style="background:rgba(100,100,110,.85);color:#fff;">Hết hạn</div>
    <?php else: ?>
      <div class="hd-badge" style="background:rgba(247,92,92,.9);color:#fff;">Đã hủy</div>
    <?php endif;?>
  </div>

  <!-- NGƯỜI THUÊ CHỦ HĐ -->
  <div class="hd-person">
    <!-- Avatar -->
    <?php if(!empty($chuHD['avatar'])): ?>
    <div class="hd-av" style="background:<?=$gc?>;">
      <img src="<?=htmlspecialchars($chuHD['avatar'])?>"
           onerror="this.parentElement.textContent='<?=$init?>'"/>
    </div>
    <?php else: ?>
    <div class="hd-av" style="background:<?=$gc?>;"><?=$init?></div>
    <?php endif;?>
    <!-- Tên -->
    <div style="flex:1;min-width:0;">
      <div class="hd-pname"><?=htmlspecialchars($chuHD['ho_ten'])?></div>
      <div class="hd-prole">
        👑 Chủ hợp đồng
        <?php if(!empty($chuHD['sdt'])): ?>
        · 📱 <?=htmlspecialchars($chuHD['sdt'])?>
        <?php endif;?>
      </div>
    </div>
    <!-- Số người + xe -->
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
      <div class="hd-count">
        <span style="font-size:13px;">👥</span>
        <span style="font-size:12px;font-weight:700;color:var(--accent);"><?=$soNguoi?>/4</span>
      </div>
      <div class="hd-count" style="background:rgba(247,169,79,.1);border-color:rgba(247,169,79,.2);">
        <span style="font-size:13px;">🚗</span>
        <span style="font-size:12px;font-weight:700;color:var(--amber);"><?=$soXe?>/4</span>
      </div>
    </div>
  </div>

  <!-- NGÀY + TIỀN CỌC -->
  <div class="hd-dates">
    <div class="hd-date-box">
      <div class="hd-date-lbl">Bắt đầu</div>
      <div class="hd-date-val"><?=date('d/m/Y',strtotime($hd['ngay_bat_dau']))?></div>
    </div>
    <div class="hd-date-box">
      <div class="hd-date-lbl">Kết thúc</div>
      <div class="hd-date-val" style="color:<?=$exp?'var(--amber)':'var(--text)'?>;"><?=date('d/m/Y',$kt)?></div>
    </div>
    <div class="hd-date-box">
      <div class="hd-date-lbl">Tiền cọc</div>
      <div class="hd-date-val" style="color:var(--amber);"><?=number_format($hd['tien_coc']/1000000,1)?>M</div>
    </div>
  </div>

</div>
<?php endforeach; ?>
</div>

<?php else: ?>
<div class="card" style="text-align:center;padding:56px;">
  <div style="font-size:40px;margin-bottom:12px">📄</div>
  <div style="font-size:15px;font-weight:700;color:var(--text2);margin-bottom:4px">Chưa có hợp đồng nào</div>
  <a href="index.php?controller=hopdong&action=create" class="btn btn-primary" style="margin-top:10px;">＋ Tạo hợp đồng đầu tiên</a>
</div>
<?php endif; ?>

<!-- ══════ MODAL ══════ -->
<div class="mbg" id="mbg" onclick="if(event.target===this)closeM()">
<div class="mbox">

  <!-- Modal header -->
  <div class="mhead" id="mHead">
    <div class="mhead-bg"></div>
    <button onclick="closeM()" style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;z-index:2;line-height:1;">✕</button>
    <div style="position:absolute;bottom:0;left:0;right:0;padding:14px 20px;background:linear-gradient(transparent,rgba(0,0,0,.35));">
      <div style="font-size:21px;font-weight:800;color:#fff;" id="mRoom"></div>
      <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
        <span style="font-size:12px;color:rgba(255,255,255,.7);" id="mHdId"></span>
        <span id="mTT"></span>
      </div>
    </div>
  </div>

  <div class="mbody">

    <!-- Thông tin HĐ -->
    <div class="minfo-grid">
      <div class="minfo-box">
        <div class="minfo-lbl">Bắt đầu</div>
        <div class="minfo-val" id="mBD"></div>
      </div>
      <div class="minfo-box">
        <div class="minfo-lbl">Kết thúc</div>
        <div class="minfo-val" id="mKT"></div>
      </div>
      <div class="minfo-box">
        <div class="minfo-lbl">Tiền cọc</div>
        <div class="minfo-val" style="color:var(--amber);" id="mCoc"></div>
      </div>
    </div>

    <!-- Ghi chú -->
    <div id="mGCBox" style="display:none;margin-bottom:16px;padding:10px 14px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.15);border-radius:10px;">
      <div style="font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;margin-bottom:4px;">📝 Ghi chú</div>
      <div id="mGC" style="font-size:13px;color:var(--text2);line-height:1.6;"></div>
    </div>

    <!-- Danh sách người thuê -->
    <div class="msec-title">
      <span>👥 Danh sách người thuê</span>
      <div id="mAddBtn"></div>
    </div>
    <div id="mList"></div>

    <!-- Danh sách xe -->
    <div class="msec-title" style="margin-top:16px;">
      <span id="mXeTitle">🚗 Danh sách xe</span>
      <div id="mXeAddBtn"></div>
    </div>
    <div id="mXeList"></div>

    <!-- Footer actions -->
    <div class="mfooter">
      <div id="mEndBtn" style="flex:1;"></div>
      <div id="mDelBtn"></div>
      <button onclick="closeM()" class="btn btn-outline btn-sm">Đóng</button>
    </div>

  </div>
</div>
</div>

<script>
const GRADS=['#4f8ef7,#7c5cfc','#22c993,#2dd4bf','#f7a94f,#f75c5c','#7c5cfc,#f472b6','#2dd4bf,#4f8ef7','#f472b6,#7c5cfc'];
const gi=n=>{const w=n.trim().split(' ');return w.slice(-2).map(x=>(x.charAt(0)||'').toUpperCase()).join('');};
const esc=s=>String(s).replace(/</g,'&lt;').replace(/>/g,'&gt;');

function openModal(d) {
  const gc = d.gc || GRADS[d.id % GRADS.length];

  // Header
  document.getElementById('mHead').style.background = `linear-gradient(135deg,${gc})`;
  document.getElementById('mRoom').textContent = 'Phòng ' + d.so_phong;
  document.getElementById('mHdId').textContent = 'Hợp đồng #' + String(d.id).padStart(4,'0');

  // Trạng thái
  let tt = '';
  if (d.trang_thai === 'hieu_luc') {
    tt = d.diff > 0 && d.diff <= 30
      ? `<span style="background:rgba(247,169,79,.9);color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;">⚠ Còn ${d.diff} ngày</span>`
      : `<span style="background:rgba(34,201,147,.9);color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;">● Đang hiệu lực</span>`;
  } else if (d.trang_thai === 'het_han') {
    tt = `<span style="background:rgba(100,100,110,.8);color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;">Hết hạn</span>`;
  } else {
    tt = `<span style="background:rgba(247,92,92,.9);color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;">Đã hủy</span>`;
  }
  document.getElementById('mTT').innerHTML = tt;

  // Info
  document.getElementById('mBD').textContent  = d.ngay_bd;
  document.getElementById('mKT').textContent  = d.ngay_kt;
  document.getElementById('mKT').style.color  = (d.diff > 0 && d.diff <= 30) ? 'var(--amber)' : 'var(--text)';
  document.getElementById('mCoc').textContent = d.tien_coc + 'đ';

  // Ghi chú
  if (d.ghi_chu) {
    document.getElementById('mGCBox').style.display = 'block';
    document.getElementById('mGC').textContent = d.ghi_chu;
  } else {
    document.getElementById('mGCBox').style.display = 'none';
  }

  // Nút thêm người
  document.getElementById('mAddBtn').innerHTML = d.trang_thai === 'hieu_luc'
    ? `<a href="index.php?controller=hopdong&action=themNguoi&id=${d.id}" class="btn btn-success btn-sm">＋ Thêm người thuê</a>`
    : '';

  // Danh sách người
  const list = document.getElementById('mList');
  list.innerHTML = '';

  if (!d.nguoi || d.nguoi.length === 0) {
    list.innerHTML = `<div style="text-align:center;padding:28px;color:var(--text3);background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
      <div style="font-size:28px;margin-bottom:8px;">👤</div>
      <div style="font-size:13px;">Chưa có thông tin người thuê</div>
    </div>`;
  } else {
    d.nguoi.forEach((n, i) => {
      const cp      = GRADS[i % GRADS.length];
      const init    = gi(n.ho_ten);
      const isOwner = n.la_chu_hop_dong == 1;
      const badge   = isOwner
        ? `<span class="owner-tag">👑 Chủ HĐ</span>`
        : `<span class="other-tag">Ở cùng</span>`;

      const avHtml = n.avatar
        ? `<div class="mpc-av" style="background:linear-gradient(135deg,${cp});padding:0;overflow:hidden;">
             <img src="${esc(n.avatar)}" style="width:100%;height:100%;object-fit:cover;"
                  onerror="this.parentElement.innerHTML='${init}';this.parentElement.style.fontSize='19px'"/>
           </div>`
        : `<div class="mpc-av" style="background:linear-gradient(135deg,${cp});">${init}</div>`;

      const delBtn = (!isOwner && n.id > 0)
        ? `<form method="POST" action="index.php?controller=hopdong&action=xoaNguoi" style="position:absolute;top:10px;right:10px;display:inline" onsubmit="return confirm('Xóa người này khỏi phòng?')"><input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>"><input type="hidden" name="id" value="${n.id}"><button type="submit" style="background:rgba(247,92,92,.12);border:1px solid rgba(247,92,92,.2);border-radius:7px;color:var(--red);font-size:11px;font-weight:600;padding:4px 9px;cursor:pointer;">🗑 Xóa</button></form>`
        : '';

      const infos = [
        n.sdt       ? `<span>📱 ${esc(n.sdt)}</span>` : '',
        n.cccd      ? `<span>🪪 ${esc(n.cccd)}</span>` : '',
        n.ngay_sinh ? `<span>🎂 ${esc(n.ngay_sinh)}</span>` : '',
        n.gioi_tinh ? `<span>${n.gioi_tinh==='nam'?'👨 Nam':'👩 Nữ'}</span>` : '',
      ].filter(Boolean).join('');

      const diaChi = (n.que_quan||n.dia_chi)
        ? `<div style="font-size:12px;color:var(--text3);margin-top:6px;">📍 ${esc(n.que_quan||n.dia_chi)}</div>`
        : '';

      list.innerHTML += `
        <div class="mpc">
          ${delBtn}
          ${avHtml}
          <div style="flex:1;min-width:0;padding-right:${!isOwner&&n.id>0?'60px':'0'};">
            <div style="display:flex;align-items:center;flex-wrap:wrap;margin-bottom:6px;">
              <span class="mpc-name">${esc(n.ho_ten)}</span>${badge}
            </div>
            <div class="mpc-info">${infos||'<span style="color:var(--text3)">Chưa có thông tin thêm</span>'}</div>
            ${diaChi}
          </div>
        </div>`;
    });
  }

  // Danh sách xe
  const xeMap = {'xe_may':'🏍 Xe máy','xe_dien':'⚡ Xe điện','xe_dap':'🚲 Xe đạp'};
  const xeArr = d.xe || [];
  document.getElementById('mXeTitle').textContent = `🚗 Danh sách xe (${xeArr.length}/4)`;
  document.getElementById('mXeAddBtn').innerHTML = (d.trang_thai === 'hieu_luc' && xeArr.length < 4)
    ? `<a href="index.php?controller=xe&action=them&id=${d.id}" class="btn btn-success btn-sm">＋ Thêm xe</a>`
    : '';
  const xeListEl = document.getElementById('mXeList');
  xeListEl.innerHTML = '';
  if (xeArr.length === 0) {
    xeListEl.innerHTML = `<div style="text-align:center;padding:20px;color:var(--text3);background:var(--bg3);border-radius:12px;border:1px solid var(--border);">
      <div style="font-size:24px;margin-bottom:6px;">🚗</div>
      <div style="font-size:12px;">Chưa có xe đăng ký</div>
    </div>`;
  } else {
    xeArr.forEach(x => {
      const icon   = x.loai_xe==='xe_may'?'🏍':x.loai_xe==='xe_dien'?'⚡':x.loai_xe==='xe_dap'?'🚲':'🏍';
      const xeDel  = x.id > 0
        ? `<form method="POST" action="index.php?controller=xe&action=xoa" style="display:inline;flex-shrink:0" onsubmit="return confirm('Xóa xe ${esc(x.bien_so)}?')"><input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>"><input type="hidden" name="id" value="${x.id}"><button type="submit" style="background:rgba(247,92,92,.12);border:1px solid rgba(247,92,92,.2);border-radius:7px;color:var(--red);font-size:11px;font-weight:600;padding:4px 9px;cursor:pointer;">🗑 Xóa</button></form>`
        : '';
      xeListEl.innerHTML += `
        <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:12px;margin-bottom:8px;">
          <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--amber),#f75c5c);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">${icon}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:15px;font-weight:800;color:var(--text);letter-spacing:1px;">${esc(x.bien_so)}</div>
            <div style="font-size:12px;color:var(--text2);margin-top:2px;">${xeMap[x.loai_xe]||x.loai_xe}${x.mau_sac?' · '+esc(x.mau_sac):''}</div>
          </div>
          ${xeDel}
        </div>`;
    });
  }

  // Actions footer
  document.getElementById('mEndBtn').innerHTML = d.trang_thai === 'hieu_luc'
    ? `<a href="index.php?controller=hopdong&action=ketThuc&id=${d.id}"
          class="btn btn-outline btn-sm" style="width:100%;justify-content:center;"
          onclick="return confirm('Kết thúc hợp đồng phòng ${esc(d.so_phong)}?')">⏹ Kết thúc HĐ</a>`
    : '';
  document.getElementById('mDelBtn').innerHTML =
    `<form method="POST" action="index.php?controller=hopdong&action=delete" style="display:inline" onsubmit="return confirm('Xóa hợp đồng này?')"><input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>"><input type="hidden" name="id" value="${d.id}"><button type="submit" class="btn btn-danger btn-sm" style="cursor:pointer">🗑 Xóa</button></form>`;

  document.getElementById('mbg').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeM() {
  document.getElementById('mbg').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeM(); });

document.addEventListener('DOMContentLoaded', () => {
  const focusId = new URLSearchParams(window.location.search).get('focus_hd');
  if (!focusId) return;
  const card = document.querySelector(`.hd-card[data-hd-id="${focusId}"]`);
  if (card) {
    card.scrollIntoView({behavior:'smooth', block:'center'});
    setTimeout(() => card.click(), 250);
  }
});
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
