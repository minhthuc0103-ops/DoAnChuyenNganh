<?php
$title = 'Cảnh báo khách rời';
require_once 'frontend/views/Layouts/header.php';

$tong = array_sum($dem);
$nhan = ['tieu_thu' => ['⚡', 'Tiêu thụ', 25], 'thanh_toan' => ['💸', 'Thanh toán', 30], 'phan_anh' => ['🔧', 'Phản ánh', 25], 'het_han' => ['📅', 'Hết hạn', 20]];
$tab  = [
    'all'    => ['Tất cả', $tong],
    'da_bao' => [CanhBaoModel::MUC['da_bao'][0] . ' Đã báo rời', $dem['da_bao']],
    'cao'    => [CanhBaoModel::MUC['cao'][0] . ' Nguy cơ cao', $dem['cao']],
    'tb'     => [CanhBaoModel::MUC['tb'][0] . ' Trung bình', $dem['tb']],
    'thap'   => [CanhBaoModel::MUC['thap'][0] . ' Thấp', $dem['thap']],
];
?>
<link rel="stylesheet" href="frontend/assets/css/canhbao.css">

<div class="page-header">
  <div class="page-title">
    <h1>⚠️ Cảnh báo khách rời</h1>
    <p>
      <?php if ($dem['cao'] + $dem['da_bao'] > 0): ?>
        <strong style="color:var(--red);"><?= $dem['cao'] + $dem['da_bao'] ?> khách</strong> cần chú ý ngay
      <?php else: ?>
        Hiện không có khách nào ở mức nguy cơ cao ✓
      <?php endif; ?>
      · <?= $tong ?> hợp đồng đang theo dõi
    </p>
  </div>
</div>

<details class="cb-info" style="margin-bottom:16px;">
  <summary style="cursor:pointer;font-weight:700;color:var(--text);">ℹ️ Điểm nguy cơ được tính thế nào?</summary>
  <div style="margin-top:8px;">
    Mỗi hợp đồng đang hiệu lực được chấm tối đa 100 điểm từ 4 tín hiệu:
    <b>Tiêu thụ ít</b> (≤ 25đ: điện dưới <?= CanhBaoModel::DIEN_THAP_KWH ?> kWh hoặc nước dưới <?= CanhBaoModel::NUOC_THAP_M3 ?> m³ mỗi tháng, hoặc thấp hơn <?= round(CanhBaoModel::TI_LE_THAP * 100) ?>% mức trung bình các phòng),
    <b>Thanh toán trễ</b> (≤ 30đ: hóa đơn quá hạn (ngày <?= HanThanhToan::NGAY_HAN_THANG_SAU ?> tháng sau kỳ hóa đơn) mà chưa trả, trễ từ <?= CanhBaoModel::NO_NANG_NGAY ?> ngày tính nặng hơn),
    <b>Phản ánh nhiều</b> (≤ 25đ: từ 2 yêu cầu sửa chữa trong <?= CanhBaoModel::NGAY_PHAN_ANH ?> ngày, để chờ quá <?= CanhBaoModel::NGAY_CHO_LAU ?> ngày, hoặc chấm sao thấp),
    <b>Hợp đồng sắp hết</b> (≤ 20đ: còn dưới 60 ngày).
    Từ <b><?= CanhBaoModel::MUC_TB ?>đ</b> là trung bình, từ <b><?= CanhBaoModel::MUC_CAO ?>đ</b> là nguy cơ cao.
    Khách đã báo trả phòng luôn xếp riêng ở đầu danh sách. Điểm tính theo dữ liệu mới nhất mỗi lần mở trang.
  </div>
</details>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach ($tab as $v => [$lbl, $n]): ?>
  <a href="index.php?controller=canhbao&action=index&muc=<?= $v ?>"
     class="btn <?= $loc === $v ? 'btn-primary' : 'btn-outline' ?>" style="font-size:12px;padding:6px 14px;"><?= $lbl ?> (<?= $n ?>)</a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php if (empty($ds)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">✅</div>
    Không có hợp đồng nào trong mục này.
  </div>
  <?php else: foreach ($ds as $r): $m = CanhBaoModel::MUC[$r['muc']]; $tel = preg_replace('/[^0-9+]/', '', (string)$r['sdt']); ?>
  <div class="cb-item cb-risk-<?= htmlspecialchars($r['muc']) ?>">
    <div class="cb-head">
      <div class="cb-score"><?= $r['muc'] === 'da_bao' ? '🚪' : (int)$r['diem'] ?></div>
      <div style="flex:1;min-width:180px;">
        <div style="font-weight:700;">
          Phòng <?= htmlspecialchars($r['so_phong']) ?>
          <span style="color:var(--text3);font-weight:400;">· <?= htmlspecialchars($r['ten_khu'] ?? '') ?></span>
          — <?= htmlspecialchars($r['ho_ten']) ?>
          <span class="pill <?= ['da_bao' => 'p-purple', 'cao' => 'p-red', 'tb' => 'p-amber', 'thap' => 'p-green'][$r['muc']] ?>" style="margin-left:6px;"><?= $m[0] . ' ' . $m[1] ?></span>
        </div>
        <?php if ($r['muc'] !== 'da_bao'): ?>
        <div class="cb-meter" style="margin-top:6px;"><i style="width:<?= (int)$r['diem'] ?>%"></i></div>
        <?php endif; ?>
      </div>
      <?php if ($tel !== ''): ?>
      <a class="btn btn-outline" style="font-size:12px;padding:6px 12px;" href="tel:<?= htmlspecialchars($tel) ?>">📞 <?= htmlspecialchars($r['sdt']) ?></a>
      <?php endif; ?>
    </div>

    <?php if (!empty($r['ly_do'])): ?>
    <ul class="cb-reasons">
      <?php foreach ($r['ly_do'] as $ld): ?><li><?= htmlspecialchars($ld) ?></li><?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if ($r['muc'] !== 'da_bao'): ?>
    <div class="cb-chips">
      <?php foreach ($nhan as $k => [$ic, $ten, $max]): $d = (int)$r['thanh_phan'][$k]; ?>
      <span class="cb-chip <?= $d > 0 ? 'on' : '' ?>"><?= $ic . ' ' . $ten ?> <?= $d ?>/<?= $max ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="cb-advice">💡 <?= htmlspecialchars($r['goi_y']) ?></div>
  </div>
  <?php endforeach; endif; ?>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>