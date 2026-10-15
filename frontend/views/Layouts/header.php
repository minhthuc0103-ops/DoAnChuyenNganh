<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? 'RoomManager') ?> — Quản lý phòng trọ</title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/layouts-header.css">
</head>
<body>

<!-- SIDEBAR OVERLAY -->
<div id="overlay" onclick="closeSidebar()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;"></div>

<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar" id="sidebar">
  <?php
    $__role = $_SESSION['vai_tro'] ?? 'user';
    $__soChuyenPhongChoDuyet = $__role === 'user'
      ? (new ChuyenPhongModel())->countPendingByUser((int)($_SESSION['user_id'] ?? 0))
      : (new ChuyenPhongModel())->countPending();
    $__soOCungChoDuyet = $__role === 'user'
      ? (new YeuCauNguoiOCungModel())->countPendingByUser((int)($_SESSION['user_id'] ?? 0))
      : (new YeuCauNguoiOCungModel())->countPending();
  ?>
  <a class="sidebar-logo" href="<?= $__role === 'user' ? 'index.php?controller=user&action=index' : 'index.php?controller=dashboard&action=index' ?>">
    <div class="logo-box">🏠</div>
    <span class="logo-name">RoomManager</span>
    <span class="logo-ver">v2.0</span>
  </a>

  <nav class="sidebar-nav">
    <?php if ($__role === 'user'): ?>
    <!-- NAV NGƯỜI DÙNG -->
    <div class="nav-lbl">Tổng quan</div>
    <a class="nav-item <?= (($_GET['controller']??'')==='user' && in_array($_GET['action']??'index', ['index','']))?'active':'' ?>"
       href="index.php?controller=user&action=index">
      <div class="nav-icon">🏠</div>Phòng của tôi
    </a>
    <div class="nav-divider"></div>
    <div class="nav-lbl">Xem</div>
    <a class="nav-item <?= (($_GET['controller']??'')==='user' && in_array($_GET['action']??'', ['khuPhong','phong']))?'active':'' ?>"
       href="index.php?controller=user&action=khuPhong">
      <div class="nav-icon">🏘</div>Khu & Phòng trọ
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='hoadon' && ($_GET['action']??'')==='lichSu')?'active':'' ?>"
       href="index.php?controller=hoadon&action=lichSu">
      <div class="nav-icon">📋</div>Lịch sử thanh toán
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='user' && ($_GET['action']??'')==='hopDong')?'active':'' ?>"
       href="index.php?controller=user&action=hopDong">
      <div class="nav-icon">📄</div>Hợp đồng
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='chuyenphong')?'active':'' ?>"
       href="index.php?controller=chuyenphong&action=index">
      <div class="nav-icon">🔄</div>Chuyển phòng
      <?php if ($__soChuyenPhongChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soChuyenPhongChoDuyet ?></span><?php endif; ?>
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='thongbao')?'active':'' ?>"
       href="index.php?controller=thongbao&action=index">
      <div class="nav-icon">📢</div>bảo trì
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='chat')?'active':'' ?>"
       href="index.php?controller=chat&action=index">
      <div class="nav-icon">💬</div>Chat nhóm
    </a>
    <?php else: ?>
    <!-- NAV ADMIN/CHỦ TRỌ -->
    <div class="nav-lbl">Tổng quan</div>
    <a class="nav-item <?= (($_GET['controller']??'dashboard')==='dashboard')?'active':'' ?>"
       href="index.php?controller=dashboard&action=index">
      <div class="nav-icon">🏠</div>Home
    </a>

    <div class="nav-divider"></div>
    <div class="nav-lbl">Quản lý</div>

    <a class="nav-item <?= (in_array($_GET['controller']??'', ['khutro','phong']))?'active':'' ?>"
       href="index.php?controller=khutro&action=index">
      <div class="nav-icon">🏘</div>Khu & Phòng trọ
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='nguoithue' && ($_GET['action']??'index')!=='yeuCauOCung')?'active':'' ?>"
       href="index.php?controller=nguoithue&action=index">
      <div class="nav-icon">👥</div>Người thuê
      <?php if ($__soOCungChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soOCungChoDuyet ?></span><?php endif; ?>
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='nguoithue' && ($_GET['action']??'')==='yeuCauOCung')?'active':'' ?>"
       href="index.php?controller=nguoithue&action=yeuCauOCung">
      <div class="nav-icon">➕</div>Duyệt ở cùng
      <?php if ($__soOCungChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soOCungChoDuyet ?></span><?php endif; ?>
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='hopdong')?'active':'' ?>"
       href="index.php?controller=hopdong&action=index">
      <div class="nav-icon">📄</div>Hợp đồng
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='chuyenphong')?'active':'' ?>"
       href="index.php?controller=chuyenphong&action=index">
      <div class="nav-icon">🔄</div>Chuyển phòng
      <?php if ($__soChuyenPhongChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soChuyenPhongChoDuyet ?></span><?php endif; ?>
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='hoadon')?'active':'' ?>"
       href="index.php?controller=hoadon&action=index">
      <div class="nav-icon">💵</div>Tổng chi phí
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='hoadon' && ($_GET['action']??'')==='lichSu')?'active':'' ?>"
       href="index.php?controller=hoadon&action=lichSu">
      <div class="nav-icon">📋</div>Lịch sử thanh toán
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='thongbao')?'active':'' ?>"
       href="index.php?controller=thongbao&action=index">
      <div class="nav-icon">🔧</div>Bảo trì
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='chat')?'active':'' ?>"
       href="index.php?controller=chat&action=index">
      <div class="nav-icon">💬</div>Chat nhóm
    </a>

    <div class="nav-divider"></div>
    <div class="nav-lbl">Thống kê</div>

    <a class="nav-item <?= (($_GET['controller']??'')==='baocao')?'active':'' ?>"
       href="index.php?controller=baocao&action=index">
      <div class="nav-icon">📈</div>Báo cáo doanh thu
    </a>

    <div class="nav-divider"></div>
    <div class="nav-lbl">Cài đặt</div>

    <a class="nav-item <?= (($_GET['controller']??'')==='dongia')?'active':'' ?>"
       href="index.php?controller=dongia&action=index">
      <div class="nav-icon">⚙️</div>Đơn giá điện nước
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-user">
    <?php if($__role === 'user'): ?>
    <a class="user-card" href="index.php?controller=user&action=profile"
       style="<?= (($_GET['action']??'')==='profile') ? 'border-color:rgba(79,142,247,.4);background:rgba(79,142,247,.08);' : '' ?>">
      <div class="user-av"><?= strtoupper(substr($_SESSION['user']??'A',0,1)) ?></div>
      <div style="flex:1;min-width:0;">
        <div class="user-name"><?= htmlspecialchars($_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Người dùng') ?></div>
        <?php $__roleLabel = ['quan_ly'=>'Quản trị viên','chu_tro'=>'Chủ trọ','user'=>'Người thuê']; ?>
        <div class="user-role"><?= $__roleLabel[$__role] ?? 'Người dùng' ?> · Hồ sơ</div>
      </div>
      <a href="index.php?controller=auth&action=logout"
         title="Đăng xuất"
         onclick="event.stopPropagation();"
         style="flex-shrink:0;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-size:14px;text-decoration:none;transition:.2s;"
         onmouseover="this.style.background='rgba(239,68,68,.25)'" onmouseout="this.style.background='rgba(239,68,68,.12)'">⏻</a>
    </a>
    <?php else: ?>
    <a class="user-card" href="index.php?controller=auth&action=logout">
      <div class="user-av"><?= strtoupper(substr($_SESSION['user']??'A',0,1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Người dùng') ?></div>
        <?php $__roleLabel = ['quan_ly'=>'Quản trị viên','chu_tro'=>'Chủ trọ','user'=>'Người thuê']; ?>
        <div class="user-role"><?= $__roleLabel[$__role] ?? 'Người dùng' ?> · Đăng xuất</div>
      </div>
    </a>
    <?php endif; ?>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-btn" onclick="toggleSidebar()">☰</button>
      <div class="breadcrumb">
        <span>RoomManager</span>
        <span style="opacity:.4;margin:0 2px;">/</span>
        <span class="breadcrumb-cur"><?= htmlspecialchars($title??'Dashboard') ?></span>
      </div>
    </div>
    <div class="tb-search" id="globalSearchBox">
      <span style="color:var(--text3);font-size:13px">🔍</span>
      <input type="text" id="globalSearchInput" autocomplete="off" placeholder="<?= $__role === 'user' ? 'Tìm phòng...' : 'Tìm người thuê, phòng, SĐT...' ?>"/>
      <?php if ($__role !== 'user'): ?>
      <div class="global-search-results" id="globalSearchResults">
        <div class="gs-head">Tìm kiếm người thuê</div>
        <div class="gs-list" id="globalSearchList"></div>
        <a class="gs-all" id="globalSearchAll" href="index.php?controller=nguoithue&action=index">Xem tất cả trong danh sách người thuê ›</a>
      </div>
      <?php endif; ?>
    </div>
    <div class="topbar-right">
      <?php if ($__role !== 'user'): ?>
      <a class="icon-btn" href="index.php?controller=nguoithue&action=yeuCauOCung" title="Yêu cầu thêm người ở cùng">➕<?php if ($__soOCungChoDuyet > 0): ?><span class="notif-dot"></span><?php endif; ?></a>
      <a class="icon-btn" href="index.php?controller=chuyenphong&action=index" title="Yêu cầu chuyển phòng">🔄<?php if ($__soChuyenPhongChoDuyet > 0): ?><span class="notif-dot"></span><?php endif; ?></a>
      <a class="icon-btn" href="index.php?controller=hoadon&action=congNo" title="Công nợ">🔔</a>
      <a class="icon-btn" href="index.php?controller=baocao&action=index" title="Báo cáo">📊</a>
      <?php endif; ?>
      <a class="icon-btn" href="index.php?controller=auth&action=logout" title="Đăng xuất">⏻</a>
      <span class="tb-date"><?= date('d/m/Y') ?></span>
    </div>
  </header>

  <div class="page-content">
    <?php if(!empty($_GET['msg'])):
      $map=['created'=>['success','✓ Thêm mới thành công!'],
            'updated'=>['success','✓ Cập nhật thành công!'],
            'deleted'       =>['error','✓ Đã xóa thành công!'],
          'cannot_delete' =>['error','⚠ Không thể xóa! Người thuê đang có hợp đồng hiệu lực.'],
            'paid'   =>['success','✓ Đã đánh dấu thanh toán!'],
            'ended'  =>['success','✓ Đã kết thúc hợp đồng!'],
            'transfer_sent'      =>['success','✓ Đã gửi yêu cầu chuyển phòng cho quản lý!'],
            'transfer_cancelled' =>['success','✓ Đã hủy yêu cầu chuyển phòng!'],
            'transfer_approved'  =>['success','✓ Đã duyệt và cập nhật phòng mới thành công!'],
            'transfer_rejected'  =>['info','Đã từ chối yêu cầu chuyển phòng.'],
            'transfer_error'     =>['error','⚠ Không thể xử lý yêu cầu chuyển phòng.'],
            'roommate_sent'      =>['success','✓ Đã gửi yêu cầu thêm người ở cùng cho quản lý!'],
            'roommate_cancelled' =>['success','✓ Đã hủy yêu cầu thêm người ở cùng!'],
            'roommate_approved'  =>['success','✓ Đã duyệt và thêm người ở cùng vào phòng!'],
            'roommate_rejected'  =>['info','Đã từ chối yêu cầu thêm người ở cùng.'],
            'roommate_error'     =>['error','⚠ Không thể xử lý yêu cầu thêm người ở cùng.']];
      $m=$map[$_GET['msg']]??['info','Thao tác thành công!'];
    ?>
    <div class="msg-alert msg-<?= $m[0] ?>" id="autoMsg">
      <?= $m[1] ?>
    </div>
    <script src="frontend/assets/js/layouts-header.js"></script>
    <?php endif; ?>
    <?php if($__role !== 'user'): ?>
    <script src="frontend/assets/js/layouts-header-2.js"></script>
    <?php endif; ?>
