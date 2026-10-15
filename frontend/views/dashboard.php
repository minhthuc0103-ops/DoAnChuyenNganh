<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/dashboard.css">
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">🏠</div>
    <span class="logo-text">RoomManager</span>
    <span class="logo-ver">v2.0</span>
  </div>
  <div class="sidebar-nav">
    <div class="sidebar-label">Quản lý</div>
    <a href="index.php?controller=dashboard&action=index" class="nav-item active"><span class="ni">📊</span> Tổng quan</a>
    <a href="index.php?controller=phong&action=index"     class="nav-item"><span class="ni">🏠</span> Phòng trọ</a>
    <a href="index.php?controller=nguoithue&action=index" class="nav-item"><span class="ni">👥</span> Người thuê</a>
    <a href="index.php?controller=hopdong&action=index"   class="nav-item"><span class="ni">📋</span> Hợp đồng</a>
    <a href="index.php?controller=hoadon&action=index"    class="nav-item">
      <span class="ni">🧾</span> Hóa đơn
      <?php if (!empty($soHoaDonChua)): ?><span class="nav-badge"><?= $soHoaDonChua ?></span><?php endif; ?>
    </a>
    <div class="sidebar-label">Hệ thống</div>
    <a href="index.php?controller=khutro&action=index"    class="nav-item"><span class="ni">🏘️</span> Khu trọ</a>
    <a href="index.php?controller=thongbao&action=index"  class="nav-item"><span class="ni">🔔</span> Thông báo</a>
    <a href="index.php?controller=baocao&action=index"    class="nav-item"><span class="ni">📈</span> Báo cáo</a>
  </div>
  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'] ?? 'A', 0, 1)) ?></div>
      <div><div class="user-name"><?= htmlspecialchars($_SESSION['user'] ?? 'Admin') ?></div><div class="user-role">Quản trị viên</div></div>
    </div>
    <a href="index.php?controller=auth&action=logout" class="logout-btn">🚪 Đăng xuất</a>
  </div>
</aside>

<div class="content">
  <div class="topbar">
    <div class="topbar-title">📊 Tổng quan hệ thống</div>
    <div class="topbar-right">
      <span class="topbar-date">📅 <?= date('d/m/Y') ?></span>
      <a href="index.php?controller=hoadon&action=create" class="btn-outline">⚡ Tạo hóa đơn</a>
      <a href="index.php?controller=phong&action=create"  class="btn-primary">＋ Thêm phòng</a>
    </div>
  </div>

  <div class="page">
    <div class="stats-grid">
      <div class="stat-card" onclick="location.href='index.php?controller=phong&action=index'">
        <div class="stat-row-top"><div class="stat-ico si-blue">🏠</div><div class="stat-change chg-flat">— Ổn định</div></div>
        <div class="stat-val"><?= $tongPhong ?? 0 ?></div><div class="stat-lbl">Tổng số phòng</div>
      </div>
      <div class="stat-card" onclick="location.href='index.php?controller=phong&action=index'">
        <div class="stat-row-top"><div class="stat-ico si-green">✅</div><div class="stat-change chg-up">↑ Tốt</div></div>
        <div class="stat-val"><?= $phongDangThue ?? 0 ?></div><div class="stat-lbl">Đang cho thuê</div>
      </div>
      <div class="stat-card" onclick="location.href='index.php?controller=phong&action=index'">
        <div class="stat-row-top"><div class="stat-ico si-amber">🔑</div><div class="stat-change chg-dn">Còn trống</div></div>
        <div class="stat-val"><?= $phongTrong ?? 0 ?></div><div class="stat-lbl">Phòng trống</div>
      </div>
      <div class="stat-card" onclick="location.href='index.php?controller=baocao&action=index'">
        <div class="stat-row-top"><div class="stat-ico si-purple">💰</div><div class="stat-change chg-up">↑ Thu</div></div>
        <div class="stat-val"><?= number_format(($doanhThuThang??0)/1000000, 1) ?>M</div><div class="stat-lbl">Doanh thu tháng <?= date('n') ?></div>
      </div>
    </div>

    <div class="main-grid">
      <div class="card">
        <div class="card-header">
          <div><div class="card-title">Danh sách phòng</div><div class="card-sub"><?= $tongPhong??0 ?> phòng · <?= $phongDangThue??0 ?> đang thuê</div></div>
          <a href="index.php?controller=phong&action=index" class="card-link">Xem tất cả →</a>
        </div>
        <?php if (!empty($phongs)): ?>
        <table class="tbl">
          <thead><tr><th>Phòng</th><th>Giá thuê</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody>
          <?php foreach (array_slice($phongs,0,7) as $p): ?>
          <tr onclick="location.href='index.php?controller=phong&action=chiTiet&id=<?= $p['id'] ?>'">
            <td><span class="td-name"><?= htmlspecialchars($p['so_phong']) ?></span></td>
            <td><strong><?= number_format($p['gia']) ?>đ</strong></td>
            <td>
              <?php if($p['trang_thai']==='dang_thue'): ?><span class="pill p-blue">Đang thuê</span>
              <?php elseif($p['trang_thai']==='trong'): ?><span class="pill p-green">Còn trống</span>
              <?php else: ?><span class="pill p-amber">Bảo trì</span><?php endif; ?>
            </td>
            <td><a href="index.php?controller=phong&action=chiTiet&id=<?= $p['id'] ?>" class="btn-outline" style="padding:4px 10px;font-size:11px;" onclick="event.stopPropagation()">Chi tiết</a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="empty-txt">Chưa có phòng nào. <a href="index.php?controller=phong&action=create" style="color:var(--accent);font-weight:700;">Thêm ngay</a></div>
        <?php endif; ?>
        <?php
          $months=['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
          $revData=$doanhThuTheoThang??array_fill(0,12,0);
          $maxRev=max($revData)?:1;
        ?>
        <div style="padding:14px 18px 0;border-top:1px solid var(--border);"><div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:8px;">Doanh thu theo tháng</div></div>
        <div class="revenue-row">
          <?php foreach($months as $i=>$m): ?>
          <?php $h=max(3,round(($revData[$i]/$maxRev)*48)); ?>
          <div class="rev-bar-wrap">
            <div class="rev-bar" style="height:<?=$h?>px" title="Tháng <?=$i+1?>: <?=number_format($revData[$i])?>đ"></div>
            <div class="rev-month"><?=$m?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="card">
          <div class="card-header">
            <div><div class="card-title">Hợp đồng sắp hết hạn</div><div class="card-sub">Trong 30 ngày tới</div></div>
            <a href="index.php?controller=hopdong&action=index" class="card-link">Xem →</a>
          </div>
          <?php if (!empty($sapHetHan)): ?>
            <?php foreach(array_slice($sapHetHan,0,5) as $hd): ?>
            <div class="expiry-item">
              <div class="expiry-date">
                <div class="expiry-day"><?=date('d',strtotime($hd['ngay_ket_thuc']))?></div>
                <div class="expiry-mon"><?=date('M',strtotime($hd['ngay_ket_thuc']))?></div>
              </div>
              <div style="flex:1">
                <div class="expiry-room">P.<?=htmlspecialchars($hd['so_phong'])?></div>
                <div class="expiry-name"><?=htmlspecialchars($hd['ho_ten'])?></div>
              </div>
              <span class="pill p-amber">Sắp HH</span>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-txt">Không có hợp đồng sắp hết hạn</div>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="card-header">
            <div><div class="card-title">Hóa đơn chưa thu</div><div class="card-sub">Tháng <?=date('n/Y')?></div></div>
            <a href="index.php?controller=hoadon&action=index" class="card-link">Xem →</a>
          </div>
          <?php if (!empty($hoaDonChuaThu)): ?>
            <?php foreach(array_slice($hoaDonChuaThu,0,5) as $hd): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 18px;border-bottom:1px solid rgba(255,255,255,0.04);">
              <div>
                <div style="font-size:13px;font-weight:700;">P.<?=htmlspecialchars($hd['so_phong'])?></div>
                <div style="font-size:11px;color:var(--text2);"><?=htmlspecialchars($hd['ho_ten'])?></div>
              </div>
              <div style="font-size:13px;font-weight:800;color:#f87171;"><?=number_format($hd['tong_tien'])?>đ</div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-txt">Tất cả đã thanh toán 🎉</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>