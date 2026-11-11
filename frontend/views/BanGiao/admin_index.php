<?php
$title = 'Bàn giao & Trả phòng';
require_once 'frontend/views/Layouts/header.php';

$activeTab = $_GET['tab'] ?? 'tra_phong';
$statusMap = [
    'cho_kiem_tra'  => ['⏳', 'Chờ kiểm tra', 'p-amber'],
    'dang_kiem_tra' => ['🔍', 'Đang kiểm tra', 'p-blue'],
    'da_thanh_ly'   => ['✅', 'Đã thanh lý', 'p-green'],
    'tu_choi'       => ['❌', 'Đã hủy', 'p-red'],
];
?>

<div class="page-header">
  <div class="page-title">
    <h1>📦 Quản lý Bàn giao & Trả phòng</h1>
    <p>Kiểm tra thiết bị bàn giao nhận phòng, quyết toán tiền cọc và thanh lý hợp đồng trả phòng</p>
  </div>
  <div class="header-actions">
    <a href="index.php?controller=bangiao&action=taoBienBan" class="btn btn-primary">
      ＋ Lập biên bản bàn giao mới
    </a>
  </div>
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="msg-alert msg-success" style="margin-bottom:14px;">
  <?php
    if ($_GET['msg'] === 'created') echo '✓ Đã tạo biên bản bàn giao thành công.';
    elseif ($_GET['msg'] === 'deleted') echo '✓ Đã xóa biên bản thành công.';
    elseif ($_GET['msg'] === 'settled') echo '✓ Đã hoàn tất thanh lý hợp đồng và trả phòng thành công!';
    else echo '✓ Thao tác thành công.';
  ?>
</div>
<?php endif; ?>

<!-- TAB NAVIGATION -->
<div style="display:flex;gap:10px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:10px;">
  <a href="index.php?controller=bangiao&action=index&tab=tra_phong"
     class="btn <?= $activeTab === 'tra_phong' ? 'btn-primary' : 'btn-outline' ?>"
     style="font-size:13px;padding:8px 18px;">
    📤 Yêu cầu trả phòng & Quyết toán cọc
    <?php if (($soChoKiemTra ?? 0) > 0): ?>
      <span class="pill p-red" style="margin-left:6px;font-size:10px;"><?=$soChoKiemTra?></span>
    <?php endif; ?>
  </a>
  <a href="index.php?controller=bangiao&action=index&tab=ban_giao"
     class="btn <?= $activeTab === 'ban_giao' ? 'btn-primary' : 'btn-outline' ?>"
     style="font-size:13px;padding:8px 18px;">
    📋 Danh sách biên bản bàn giao phòng
  </a>
</div>

<?php if ($activeTab === 'tra_phong'): ?>
<!-- ════════════ TAB 1: YÊU CẦU TRẢ PHÒNG & QUYẾT TOÁN ════════════ -->

<!-- STATS -->
<?php
  $totalYc = count($traPhongList ?? []);
  $ycCho = count(array_filter($traPhongList ?? [], fn($x) => $x['trang_thai'] === 'cho_kiem_tra'));
  $ycDang = count(array_filter($traPhongList ?? [], fn($x) => $x['trang_thai'] === 'dang_kiem_tra'));
  $ycXong = count(array_filter($traPhongList ?? [], fn($x) => $x['trang_thai'] === 'da_thanh_ly'));
?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;">
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(79,142,247,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">📤</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$totalYc?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Tổng yêu cầu trả phòng</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;border:1px solid rgba(247,169,79,.3);">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(247,169,79,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">⏳</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--amber);"><?=$ycCho?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Chờ kiểm tra phòng</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(79,142,247,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">🔍</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$ycDang?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Đang kiểm tra / Tính tiền</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(34,201,147,.12);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">✅</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--green);"><?=$ycXong?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Đã quyết toán & Trả phòng</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Danh sách yêu cầu trả phòng</div>
  </div>
  <?php if (empty($traPhongList)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">📭</div>
    Hiện chưa có yêu cầu trả phòng nào.
  </div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="border-bottom:1px solid var(--border);text-align:left;color:var(--text3);font-size:11px;text-transform:uppercase;">
          <th style="padding:12px 14px;">Mã / Phòng</th>
          <th style="padding:12px 14px;">Khách thuê</th>
          <th style="padding:12px 14px;">Ngày dự kiến trả</th>
          <th style="padding:12px 14px;">Lý do & Nhận cọc</th>
          <th style="padding:12px 14px;">Tiền cọc</th>
          <th style="padding:12px 14px;">Trạng thái</th>
          <th style="padding:12px 14px;text-align:right;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($traPhongList as $yc):
          $st = $statusMap[$yc['trang_thai']] ?? ['❓', $yc['trang_thai'], 'p-gray'];
        ?>
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:14px;">
            <div style="font-weight:700;color:var(--text);font-size:14px;">
              Phòng <?= htmlspecialchars($yc['so_phong']) ?>
            </div>
            <div style="font-size:11px;color:var(--text3);">
              <?= htmlspecialchars($yc['ten_khu'] ?? '') ?> · YC #<?= $yc['id'] ?>
            </div>
          </td>
          <td style="padding:14px;">
            <div style="font-weight:600;color:var(--text);"><?= htmlspecialchars($yc['ten_nguoi_thue']) ?></div>
            <div style="font-size:11px;color:var(--text3);">📞 <?= htmlspecialchars($yc['sdt'] ?? '') ?></div>
          </td>
          <td style="padding:14px;">
            <div style="font-weight:700;color:var(--text);">
              📅 <?= date('d/m/Y', strtotime($yc['ngay_du_kien_tra'])) ?>
            </div>
            <div style="font-size:11px;color:var(--text3);">
              Báo ngày: <?= date('d/m/Y', strtotime($yc['ngay_yeu_cau'])) ?>
            </div>
          </td>
          <td style="padding:14px;max-width:240px;">
            <div style="color:var(--text2);margin-bottom:4px;"><?= htmlspecialchars($yc['ly_do']) ?></div>
            <?php if (!empty($yc['so_tai_khoan'])): ?>
            <div style="font-size:11px;background:var(--bg3);padding:4px 8px;border-radius:6px;color:var(--text3);">
              🏦 <?= htmlspecialchars($yc['ngan_hang']) ?> - <?= htmlspecialchars($yc['so_tai_khoan']) ?>
              (<?= htmlspecialchars($yc['ten_chu_tk']) ?>)
            </div>
            <?php endif; ?>
          </td>
          <td style="padding:14px;">
            <div style="font-weight:700;color:var(--amber);">
              <?= number_format($yc['tien_coc_goc'] ?: $yc['tien_coc']) ?>đ
            </div>
            <?php if ($yc['trang_thai'] === 'da_thanh_ly'): ?>
            <div style="font-size:11px;color:var(--green);font-weight:700;">
              Hoàn trả: <?= number_format($yc['tien_hoan_lai']) ?>đ
            </div>
            <?php endif; ?>
          </td>
          <td style="padding:14px;">
            <span class="pill <?=$st[2]?>"><?=$st[0].' '.$st[1]?></span>
          </td>
          <td style="padding:14px;text-align:right;">
            <?php if ($yc['trang_thai'] === 'da_thanh_ly'): ?>
              <a href="index.php?controller=bangiao&action=inBienBanTraPhong&id=<?=$yc['id']?>" target="_blank"
                 class="btn btn-outline" style="font-size:12px;padding:6px 12px;">
                🖨 In phiếu quyết toán
              </a>
            <?php else: ?>
              <a href="index.php?controller=bangiao&action=kiemTraTraPhong&id=<?=$yc['id']?>"
                 class="btn btn-primary" style="font-size:12px;padding:6px 12px;">
                🔍 Kiểm tra & Quyết toán cọc
              </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- ════════════ TAB 2: BIÊN BẢN BÀN GIAO PHÒNG ════════════ -->

<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Biên bản bàn giao thiết bị & Hiện trạng phòng</div>
    <span style="font-size:12px;color:var(--text3);"><?= count($bienBanList ?? []) ?> biên bản</span>
  </div>
  <?php if (empty($bienBanList)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">📦</div>
    Chưa có biên bản bàn giao nào được lập.
  </div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="border-bottom:1px solid var(--border);text-align:left;color:var(--text3);font-size:11px;text-transform:uppercase;">
          <th style="padding:12px 14px;">Mã biên bản</th>
          <th style="padding:12px 14px;">Loại</th>
          <th style="padding:12px 14px;">Phòng & Người thuê</th>
          <th style="padding:12px 14px;">Ngày lập</th>
          <th style="padding:12px 14px;">Chỉ số điện / nước</th>
          <th style="padding:12px 14px;">Tài sản bàn giao</th>
          <th style="padding:12px 14px;text-align:right;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bienBanList as $bb):
          $tsList = json_decode($bb['danh_sach_tai_san'] ?? '[]', true) ?: [];
        ?>
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:14px;">
            <div style="font-weight:700;color:var(--accent);"><?= htmlspecialchars($bb['ma_bien_ban']) ?></div>
            <div style="font-size:11px;color:var(--text3);">HĐ #<?= $bb['hop_dong_id'] ?></div>
          </td>
          <td style="padding:14px;">
            <?php if ($bb['loai'] === 'tra_phong'): ?>
              <span class="pill p-orange">📤 Trả phòng</span>
            <?php else: ?>
              <span class="pill p-green">📥 Nhận phòng</span>
            <?php endif; ?>
          </td>
          <td style="padding:14px;">
            <div style="font-weight:700;color:var(--text);">Phòng <?= htmlspecialchars($bb['so_phong']) ?></div>
            <div style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($bb['ten_nguoi_thue']) ?> (<?= htmlspecialchars($bb['sdt']) ?>)</div>
          </td>
          <td style="padding:14px;">
            📅 <?= date('d/m/Y', strtotime($bb['ngay_lap'])) ?>
          </td>
          <td style="padding:14px;">
            <div>⚡ Điện: <strong><?= number_format($bb['chi_so_dien']) ?></strong> kWh</div>
            <div>💧 Nước: <strong><?= number_format($bb['chi_so_nuoc']) ?></strong> m³</div>
          </td>
          <td style="padding:14px;">
            <div style="font-weight:600;color:var(--text);"><?= count($tsList) ?> hạng mục tài sản</div>
            <div style="font-size:11px;color:var(--text3);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?php
                $names = array_column($tsList, 'ten');
                echo htmlspecialchars(implode(', ', array_slice($names, 0, 3)));
                if (count($names) > 3) echo '...';
              ?>
            </div>
          </td>
          <td style="padding:14px;text-align:right;white-space:nowrap;">
            <a href="index.php?controller=bangiao&action=xemBienBan&id=<?=$bb['id']?>"
               class="btn btn-outline" style="font-size:12px;padding:5px 10px;">
              👁 Xem
            </a>
            <a href="index.php?controller=bangiao&action=inBienBan&id=<?=$bb['id']?>" target="_blank"
               class="btn btn-outline" style="font-size:12px;padding:5px 10px;">
              🖨 In
            </a>
            <a href="index.php?controller=bangiao&action=xoaBienBan&id=<?=$bb['id']?>"
               onclick="return confirm('Bạn có chắc chắn muốn xóa biên bản này?')"
               class="btn btn-outline" style="font-size:12px;padding:5px 10px;color:var(--red);">
              🗑 Xóa
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
