<?php
$title = 'Tổng quan';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Tổng quan hệ thống</h1>
    <p>Tình hình phòng trọ và doanh thu hôm nay</p>
  </div>
  <div class="header-actions">
    <a href="index.php?controller=hoadon&action=create" class="btn btn-outline">⚡ Tạo hóa đơn</a>
    <a href="index.php?controller=phong&action=create" class="btn btn-primary">＋ Thêm phòng</a>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stat-grid">
  <div class="stat-card" onclick="location.href='index.php?controller=phong&action=index'">
    <div class="stat-icon si-blue">🏠</div>
    <div class="stat-val"><?= $tongPhong ?? 0 ?></div>
    <div class="stat-lbl">Tổng số phòng</div>
    <div class="stat-chg chg-flat">— Ổn định</div>
  </div>
  <div class="stat-card" onclick="location.href='index.php?controller=phong&action=index'">
    <div class="stat-icon si-green">✅</div>
    <div class="stat-val"><?= $phongDangThue ?? 0 ?></div>
    <div class="stat-lbl">Đang cho thuê</div>
    <div class="stat-chg chg-up">↑ Tốt</div>
  </div>
  <div class="stat-card" onclick="location.href='index.php?controller=phong&action=index'">
    <div class="stat-icon si-amber">🔑</div>
    <div class="stat-val"><?= $phongTrong ?? 0 ?></div>
    <div class="stat-lbl">Phòng trống</div>
    <div class="stat-chg chg-dn">Còn trống</div>
  </div>
  <div class="stat-card" onclick="location.href='index.php?controller=baocao&action=index'">
    <div class="stat-icon si-purple">💰</div>
    <div class="stat-val"><?= number_format(($doanhThuThang ?? 0) / 1000000, 1) ?>M</div>
    <div class="stat-lbl">Doanh thu tháng <?= date('n') ?></div>
    <div class="stat-chg chg-up">↑ Thu</div>
  </div>
</div>

<div class="main-grid" style="display:grid;grid-template-columns:1.4fr 1fr;gap:18px;align-items:start;">

  <div>
    <!-- DANH SÁCH PHÒNG -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Danh sách phòng</div>
          <div class="card-sub"><?= $tongPhong ?? 0 ?> phòng · <?= $phongDangThue ?? 0 ?> đang thuê</div>
        </div>
        <a href="index.php?controller=phong&action=index" class="card-link">Xem tất cả →</a>
      </div>
      <?php if (!empty($phongs)): ?>
      <table class="tbl tbl-r">
        <thead><tr><th>Phòng</th><th>Giá thuê</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        <?php foreach (array_slice($phongs, 0, 7) as $p): ?>
        <tr onclick="location.href='index.php?controller=phong&action=chiTiet&id=<?= $p['id'] ?>'">
          <td data-label="Phòng"><span class="td-name"><?= htmlspecialchars($p['so_phong']) ?></span></td>
          <td data-label="Giá thuê"><strong><?= number_format($p['gia']) ?>đ</strong></td>
          <td data-label="Trạng thái">
            <?php if ($p['trang_thai'] === 'dang_thue'): ?><span class="pill p-blue">Đang thuê</span>
            <?php elseif ($p['trang_thai'] === 'trong'): ?><span class="pill p-green">Còn trống</span>
            <?php else: ?><span class="pill p-amber">Bảo trì</span><?php endif; ?>
          </td>
          <td class="mob-actions" data-label="">
            <a href="index.php?controller=phong&action=chiTiet&id=<?= $p['id'] ?>" class="btn btn-outline btn-xs" onclick="event.stopPropagation()">Chi tiết</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="card-body" style="text-align:center;color:var(--text2);">
        Chưa có phòng nào. <a href="index.php?controller=phong&action=create" style="color:var(--accent);font-weight:700;">Thêm ngay</a>
      </div>
      <?php endif; ?>

      <!-- BIỂU ĐỒ DOANH THU -->
      <div style="padding:16px 20px 2px;border-top:1px solid var(--border);">
        <div style="font-size:12px;font-weight:700;color:var(--text2);">Doanh thu theo tháng — Năm <?= date('Y') ?></div>
      </div>
      <div style="padding:14px 20px 20px;">
        <?php $chartData = $chartData ?? array_fill(1, 12, 0); $maxVal = max(array_values($chartData)) ?: 1; ?>
        <div style="display:flex;align-items:flex-end;gap:6px;height:110px;">
          <?php for ($m = 1; $m <= 12; $m++):
            $val   = $chartData[$m] ?? 0;
            $pct   = round($val / $maxVal * 100);
            $isCur = ($m === (int)date('m'));
          ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;justify-content:flex-end;">
            <div style="width:100%;height:<?= max($pct, 3) ?>%;border-radius:4px 4px 0 0;
                        background:<?= $isCur ? 'linear-gradient(180deg,var(--green),rgba(34,201,147,.3))' : 'linear-gradient(180deg,var(--accent),rgba(79,142,247,.3))' ?>;
                        cursor:pointer;transition:opacity .15s;min-height:3px;"
                 onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'"
                 title="Tháng <?= $m ?>: <?= number_format($val) ?>đ"></div>
            <div style="font-size:9px;color:<?= $isCur ? 'var(--green)' : 'var(--text2)' ?>;font-weight:<?= $isCur ? '700' : '500' ?>">T<?= $m ?></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>

  <div>
    <!-- HỢP ĐỒNG SẮP HẾT HẠN -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Hợp đồng sắp hết hạn</div>
          <div class="card-sub">Trong 30 ngày tới</div>
        </div>
        <a href="index.php?controller=hopdong&action=index" class="card-link">Xem →</a>
      </div>
      <?php if (!empty($sapHetHan)): ?>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px;padding:14px 20px;">
        <?php foreach (array_slice($sapHetHan, 0, 5) as $hd): ?>
        <a href="index.php?controller=hopdong&action=index" style="display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;">
          <div style="flex-shrink:0;width:42px;height:42px;border-radius:9px;background:var(--bg3);border:1px solid var(--border);display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1.1;">
            <div style="font-size:14px;font-weight:800;color:var(--text);"><?= date('d', strtotime($hd['ngay_ket_thuc'])) ?></div>
            <div style="font-size:8px;color:var(--text2);text-transform:uppercase;"><?= date('M', strtotime($hd['ngay_ket_thuc'])) ?></div>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:700;color:var(--text);">P.<?= htmlspecialchars($hd['so_phong']) ?></div>
            <div style="font-size:11px;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($hd['ho_ten']) ?></div>
          </div>
          <span class="pill p-amber">Sắp HH</span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="card-body" style="text-align:center;color:var(--text2);">Không có hợp đồng sắp hết hạn</div>
      <?php endif; ?>
    </div>

    <!-- HÓA ĐƠN CHƯA THU -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Hóa đơn chưa thu</div>
          <div class="card-sub">Tháng <?= date('n/Y') ?></div>
        </div>
        <a href="index.php?controller=hoadon&action=index" class="card-link">Xem →</a>
      </div>
      <?php if (!empty($hoaDonChuaThu)): ?>
      <div class="card-body" style="display:flex;flex-direction:column;gap:2px;padding:6px 0;">
        <?php foreach (array_slice($hoaDonChuaThu, 0, 5) as $hd): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 20px;border-bottom:1px solid rgba(255,255,255,0.04);">
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--text);">P.<?= htmlspecialchars($hd['so_phong']) ?></div>
            <div style="font-size:11px;color:var(--text2);">Tháng <?= (int)$hd['thang'] ?>/<?= (int)$hd['nam'] ?></div>
          </div>
          <div style="font-size:13px;font-weight:800;color:var(--red);"><?= number_format($hd['tong_tien']) ?>đ</div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="card-body" style="text-align:center;color:var(--text2);">Tất cả đã thanh toán 🎉</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
