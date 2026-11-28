<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? 'RoomManager') ?> — Quản lý phòng trọ</title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/layouts-header.css?v=<?= (int)@filemtime(__DIR__ . '/../../assets/css/layouts-header.css') ?>">
<?php require 'frontend/views/Layouts/pwa_head.php'; ?>
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
    $__soLuuTruChoKhaiBao = 0;
    $__soBaoChiSoChoDuyet = 0;
    if ($__role !== 'user') {
        try {
            $__dbH = Database::getInstance();
            $__soLuuTruChoKhaiBao = (int)$__dbH->query("SELECT COUNT(*) FROM khai_bao_luu_tru WHERE trang_thai='cho_khai_bao'")->fetchColumn();
            $__soBaoChiSoChoDuyet = (int)$__dbH->query("SELECT COUNT(*) FROM bao_chi_so_dien_nuoc WHERE trang_thai='cho_duyet'")->fetchColumn();
        } catch(\Throwable $e) {}
    }
    $__soCanhBao = in_array($__role, ['quan_ly', 'chu_tro'], true) ? (new CanhBaoModel())->countCanChuY() : 0;
    $__soTraPhongChoDuyet = $__role === 'user'
      ? (new BanGiaoModel())->countPendingTraPhongByUser((int)($_SESSION['user_id'] ?? 0))
      : (new BanGiaoModel())->countPendingTraPhong();

    // Menu: nhóm trang "Thông báo" (danh sách, xem, soạn, sửa) khác trang "Bảo trì" (yêu cầu sửa chữa) dù cùng controller
    $__ctl = $_GET['controller'] ?? '';
    $__act = $_GET['action'] ?? 'index';
    $__laTrangThongBao = $__ctl === 'thongbao' && in_array($__act, ['danhSach', 'xem', 'create', 'edit', 'docTatCa'], true);

    // CHUÔNG: dữ liệu chuẩn hóa cho cả hai vai trò. Mỗi mục: icon, tieu, trich, gio, href (href do server tự dựng).
    // Người thuê: thông báo chưa đọc mà họ được xem. Quản lý: các việc đang chờ xử lý.
    $__chuongMuc = [];
    $__chuongDem = 0;
    $__tbDem = 0;
    if ($__role === 'user') {
        $__tbModel = new ThongBaoModel();
        $__tbUid   = (int)($_SESSION['user_id'] ?? 0);
        $__tbDem   = $__chuongDem = $__tbModel->countChuaDoc($__tbUid);
        foreach ($__tbModel->getChuaDoc($__tbUid, 6) as $__tb) {
            $__chuongMuc[] = [
                'icon' => $__tb['loai'] === 'tien_phong' ? '💰' : '📢',
                'tieu' => (string)$__tb['tieu_de'],
                'trich' => trim(preg_replace('/\s+/u', ' ', (string)$__tb['noi_dung'])),
                'gio' => date('d/m/Y H:i', strtotime($__tb['created_at'])),
                'href' => 'index.php?controller=thongbao&action=xem&id=' . (int)$__tb['id'],
            ];
        }
    } else {
        $__dem = function (string $sql): int {
            try { return (int)Database::getInstance()->query($sql)->fetchColumn(); } catch (\Throwable $e) { return 0; }
        };
        $__viec = [
            ['🔧', 'yêu cầu sửa chữa chờ xử lý',  $__dem("SELECT COUNT(*) FROM yeu_cau_sua_chua WHERE trang_thai = 'cho_xu_ly'"), 'Người thuê báo hỏng hóc cần xử lý', 'index.php?controller=thongbao&action=index'],
            ['🔄', 'yêu cầu chuyển phòng chờ duyệt', (int)$__soChuyenPhongChoDuyet, 'Cần duyệt hoặc từ chối', 'index.php?controller=chuyenphong&action=index'],
            ['➕', 'yêu cầu thêm người ở cùng chờ duyệt', (int)$__soOCungChoDuyet, 'Cần duyệt hoặc từ chối', 'index.php?controller=nguoithue&action=yeuCauOCung'],
            ['📦', 'yêu cầu trả phòng chờ kiểm tra', (int)$__soTraPhongChoDuyet, 'Kiểm tra phòng và quyết toán cọc', 'index.php?controller=bangiao&action=index'],
            ['🧾', 'yêu cầu xác nhận thanh toán chờ duyệt', $__dem("SELECT COUNT(*) FROM lich_su_thanh_toan WHERE trang_thai = 'dang_xu_ly'"), 'Người thuê đã gửi ảnh giao dịch, kiểm tra tiền rồi xác nhận', 'index.php?controller=hoadon&action=index'],
            ['💰', 'hóa đơn chưa thu', $__dem("SELECT COUNT(*) FROM hoa_don WHERE trang_thai = 'chua_tt'"), 'Xem danh sách công nợ', 'index.php?controller=hoadon&action=congNo'],
            ['⚠️', 'khách có nguy cơ rời', (int)$__soCanhBao, 'Xem cảnh báo khách rời', 'index.php?controller=canhbao&action=index'],
            ['⚡', 'báo số điện nước chờ duyệt', (int)$__soBaoChiSoChoDuyet, 'Xem và duyệt chỉ số công tơ AI', 'index.php?controller=hoadon&action=baoChiSoList'],
            ['📋', 'hồ sơ lưu trú chờ khai báo', (int)$__soLuuTruChoKhaiBao, 'Lập tờ khai cư trú Mẫu CT01', 'index.php?controller=luutru&action=index'],
        ];
        foreach ($__viec as [$__icon, $__nhan, $__n, $__goiY, $__href]) {
            if ($__n > 0) {
                $__chuongDem += $__n;
                $__chuongMuc[] = ['icon' => $__icon, 'tieu' => $__n . ' ' . $__nhan, 'trich' => $__goiY, 'gio' => '', 'href' => $__href];
            }
        }
    }
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
    <a class="nav-item <?= (($_GET['controller']??'')==='user' && ($_GET['action']??'')==='baoChiSo')?'active':'' ?>"
       href="index.php?controller=user&action=baoChiSo">
      <div class="nav-icon">⚡</div>Báo điện nước (AI)
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='user' && ($_GET['action']??'')==='luuTru')?'active':'' ?>"
       href="index.php?controller=user&action=luuTru">
      <div class="nav-icon">📋</div>Khai báo lưu trú
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='chuyenphong')?'active':'' ?>"
       href="index.php?controller=chuyenphong&action=index">
      <div class="nav-icon">🔄</div>Chuyển phòng
      <?php if ($__soChuyenPhongChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soChuyenPhongChoDuyet ?></span><?php endif; ?>
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='bangiao')?'active':'' ?>"
       href="index.php?controller=bangiao&action=index">
      <div class="nav-icon">📦</div>Bàn giao & Trả phòng
      <?php if ($__soTraPhongChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soTraPhongChoDuyet ?></span><?php endif; ?>
    </a>
    <a class="nav-item <?= ($__ctl==='thongbao' && !$__laTrangThongBao)?'active':'' ?>"
       href="index.php?controller=thongbao&action=index">
      <div class="nav-icon">🔧</div>Bảo trì
    </a>
    <a class="nav-item <?= $__laTrangThongBao?'active':'' ?>"
       href="index.php?controller=thongbao&action=danhSach">
      <div class="nav-icon">📢</div>Thông báo
      <?php if ($__tbDem > 0): ?><span class="nav-badge nb-red"><?= $__tbDem > 99 ? '99+' : $__tbDem ?></span><?php endif; ?>
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

    <a class="nav-item <?= (($_GET['controller']??'')==='luutru')?'active':'' ?>"
       href="index.php?controller=luutru&action=index">
      <div class="nav-icon">📋</div>Khai báo lưu trú
      <?php if ($__soLuuTruChoKhaiBao > 0): ?><span class="nav-badge nb-amber"><?= $__soLuuTruChoKhaiBao ?></span><?php endif; ?>
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='chuyenphong')?'active':'' ?>"
       href="index.php?controller=chuyenphong&action=index">
      <div class="nav-icon">🔄</div>Chuyển phòng
      <?php if ($__soChuyenPhongChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soChuyenPhongChoDuyet ?></span><?php endif; ?>
    </a>
    <a class="nav-item <?= (($_GET['controller']??'')==='bangiao')?'active':'' ?>"
       href="index.php?controller=bangiao&action=index">
      <div class="nav-icon">📦</div>Bàn giao & Trả phòng
      <?php if ($__soTraPhongChoDuyet > 0): ?><span class="nav-badge nb-amber"><?= $__soTraPhongChoDuyet ?></span><?php endif; ?>
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='hoadon')?'active':'' ?>"
       href="index.php?controller=hoadon&action=index">
      <div class="nav-icon">💵</div>Hóa đơn
    </a>

    <a class="nav-item <?= (($_GET['controller']??'')==='hoadon' && ($_GET['action']??'')==='lichSu')?'active':'' ?>"
       href="index.php?controller=hoadon&action=lichSu">
      <div class="nav-icon">📋</div>Lịch sử thanh toán
    </a>

    <a class="nav-item <?= ($__ctl==='thongbao' && !$__laTrangThongBao)?'active':'' ?>"
       href="index.php?controller=thongbao&action=index">
      <div class="nav-icon">🔧</div>Bảo trì
    </a>

    <a class="nav-item <?= $__laTrangThongBao?'active':'' ?>"
       href="index.php?controller=thongbao&action=danhSach">
      <div class="nav-icon">📢</div>Thông báo
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

    <a class="nav-item <?= (($_GET['controller']??'')==='canhbao')?'active':'' ?>"
       href="index.php?controller=canhbao&action=index">
      <div class="nav-icon">⚠️</div>Cảnh báo khách rời
      <?php if ($__soCanhBao > 0): ?><span class="nav-badge nb-red"><?= $__soCanhBao ?></span><?php endif; ?>
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
    <div class="tb-search" id="globalSearchBox" data-mode="<?= $__role === 'user' ? 'phong' : 'nguoithue' ?>">
      <span style="color:var(--text3);font-size:13px">🔍</span>
      <input type="text" id="globalSearchInput" autocomplete="off" placeholder="<?= $__role === 'user' ? 'Tìm phòng...' : 'Tìm người thuê, phòng, SĐT...' ?>"/>
      <?php if ($__role !== 'user'): ?>
      <div class="global-search-results" id="globalSearchResults">
        <div class="gs-head">Tìm kiếm người thuê</div>
        <div class="gs-list" id="globalSearchList"></div>
        <a class="gs-all" id="globalSearchAll" href="index.php?controller=nguoithue&action=index">Xem tất cả trong danh sách người thuê ›</a>
      </div>
      <?php else: ?>
      <div class="global-search-results" id="globalSearchResults">
        <div class="gs-head">Tìm phòng</div>
        <div class="gs-list" id="globalSearchList"></div>
        <a class="gs-all" id="globalSearchAll" href="index.php?controller=user&action=phong">Xem tất cả trong danh sách phòng ›</a>
      </div>
      <?php endif; ?>
    </div>
    <div class="topbar-right">
      <?php
        // CHUÔNG dùng chung: người thuê thấy thông báo chưa đọc, quản lý thấy các việc đang chờ xử lý (dữ liệu tính ở đầu file)
        $__chuongTieuDe = $__role === 'user' ? 'Thông báo' : 'Cần xử lý';
      ?>
      <div class="tb-bell" id="tbBell">
        <button type="button" class="icon-btn" id="tbBellBtn" aria-haspopup="true" aria-expanded="false" aria-controls="tbBellPanel"
                title="<?= $__chuongTieuDe ?><?= $__chuongDem > 0 ? ' (' . $__chuongDem . ')' : '' ?>">🔔<?php if ($__chuongDem > 0): ?><span class="bell-count"><?= $__chuongDem > 99 ? '99+' : $__chuongDem ?></span><?php endif; ?></button>
        <div class="notif-panel" id="tbBellPanel" hidden>
          <div class="notif-head">
            <span><?= $__chuongTieuDe ?><?= $__chuongDem > 0 ? ' · ' . $__chuongDem . ($__role === 'user' ? ' chưa đọc' : '') : '' ?></span>
            <?php if ($__role === 'user' && $__chuongDem > 0): ?>
            <form method="POST" action="index.php?controller=thongbao&action=docTatCa" style="margin:0;">
              <?= Csrf::field() ?>
              <button type="submit" class="notif-readall">Đánh dấu đã đọc</button>
            </form>
            <?php endif; ?>
          </div>
          <?php if (empty($__chuongMuc)): ?>
          <div class="notif-empty"><?= $__role === 'user' ? 'Không có thông báo mới 🎉' : 'Không có việc nào cần xử lý 🎉' ?></div>
          <?php else: foreach ($__chuongMuc as $__m):
            $__mTieu  = htmlspecialchars(mb_strimwidth($__m['tieu'], 0, 70, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8');
            $__mTrich = htmlspecialchars(mb_strimwidth($__m['trich'], 0, 110, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8');
          ?>
          <a class="notif-item" href="<?= htmlspecialchars($__m['href'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="notif-ico"><?= $__m['icon'] ?></span>
            <span class="notif-body">
              <span class="notif-title"><?= $__mTieu ?></span>
              <span class="notif-text"><?= $__mTrich ?></span>
              <?php if ($__m['gio'] !== ''): ?><span class="notif-time"><?= htmlspecialchars($__m['gio'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </span>
          </a>
          <?php endforeach; endif; ?>
          <a class="notif-foot" href="index.php?controller=thongbao&action=danhSach"><?= $__role === 'user' ? 'Xem tất cả thông báo' : 'Quản lý thông báo gửi người thuê' ?> →</a>
        </div>
      </div>
      <script>
      (function () {
        var wrap = document.getElementById('tbBell'), btn = document.getElementById('tbBellBtn'), panel = document.getElementById('tbBellPanel');
        if (!wrap || !btn || !panel) return;
        function dat(mo) { panel.hidden = !mo; btn.setAttribute('aria-expanded', mo ? 'true' : 'false'); }
        btn.addEventListener('click', function (e) { e.stopPropagation(); dat(panel.hidden); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) dat(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') dat(false); });
      })();
      </script>
      <?php if ($__role !== 'user'): ?>
      <a class="icon-btn" href="index.php?controller=nguoithue&action=yeuCauOCung" title="Yêu cầu thêm người ở cùng">➕<?php if ($__soOCungChoDuyet > 0): ?><span class="notif-dot"></span><?php endif; ?></a>
      <a class="icon-btn" href="index.php?controller=chuyenphong&action=index" title="Yêu cầu chuyển phòng">🔄<?php if ($__soChuyenPhongChoDuyet > 0): ?><span class="notif-dot"></span><?php endif; ?></a>
      <a class="icon-btn" href="index.php?controller=baocao&action=index" title="Báo cáo">📊</a>
      <?php endif; ?>
      <a class="icon-btn" href="index.php?controller=auth&action=logout" title="Đăng xuất">⏻</a>
      <span class="tb-date"><?= date('d/m/Y') ?></span>
    </div>
  </header>

  <div class="page-content">
    <?php if(!empty($_GET['msg'])):
      $map=['created'=>['success','✓ Thêm mới thành công!'],
            'hoadon_created'=>['success','✓ Tạo hóa đơn thành công!'],
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
    <!-- Ô tìm kiếm trên cùng: quản trị tìm người thuê, người dùng tìm phòng (JS tự chọn theo data-mode) -->
    <script src="frontend/assets/js/layouts-header-2.js"></script>