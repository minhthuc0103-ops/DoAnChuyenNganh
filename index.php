<?php
session_start();
require_once 'backend/bootstrap.php';

// ═══ Lỗi #10: Security Headers ═══
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

$controller = $_GET['controller'] ?? 'auth';
$action     = $_GET['action']     ?? 'login';

$public = ['auth'];
if (!in_array($controller, $public) && empty($_SESSION['user'])) {
    header('Location: index.php?controller=auth&action=login');
    exit;
}

// Người dùng role=user chỉ truy cập được controller 'user' và 'thongbao'
if (!empty($_SESSION['user']) && ($_SESSION['vai_tro'] ?? '') === 'user') {
    $profileActions = $controller === 'user' && in_array($action, ['profile'], true);
    $authActions = $controller === 'auth' && in_array($action, ['logout'], true);

    if (!$profileActions && !$authActions) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "SELECT a.email, a.sdt, nt.cccd, nt.cccd_truoc, nt.cccd_sau
                 FROM account a
                 LEFT JOIN nguoi_thue nt ON nt.account_id = a.id
                 WHERE a.id = ?
                 LIMIT 1"
            );
            $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
            $profile = $stmt->fetch();
            $missingProfile = !$profile
                || trim((string)($profile['email'] ?? '')) === ''
                || trim((string)($profile['sdt'] ?? '')) === ''
                || trim((string)($profile['cccd'] ?? '')) === ''
                || trim((string)($profile['cccd_truoc'] ?? '')) === ''
                || trim((string)($profile['cccd_sau'] ?? '')) === '';

            if ($missingProfile) {
                header('Location: index.php?controller=user&action=profile&required=1');
                exit;
            }
        } catch (Throwable $e) {
            header('Location: index.php?controller=user&action=profile&required=1');
            exit;
        }
    }
}

$adminControllers = ['khutro','phong','nguoithue','hopdong','hoadon','baocao','dashboard','dongia','xe','aiknowledge'];
if (!empty($_SESSION['user']) && ($_SESSION['vai_tro'] ?? '') === 'user' && in_array($controller, $adminControllers)) {
    // Cho phép user xem lịch sử thanh toán và chi tiết hóa đơn
    if ($controller === 'hoadon' && in_array($action, ['lichSu', 'chiTiet', 'xacNhanThanhToanNguoiThue'])) {
        // OK
    } elseif ($controller === 'thongbao' && $action === 'guiSuaChua') {
        // OK, cho phép
    } else {
        header('Location: index.php?controller=user&action=index');
        exit;
    }
}

// ═══ Lỗi #13: Tăng throttle autoProcess lên 1 giờ ═══
if (!empty($_SESSION['user'])) {
    $lastRun = $_SESSION['_auto_process_ts'] ?? 0;
    if (time() - $lastRun > 3600) { // mỗi 1 giờ
        (new HopDongModel())->autoProcess();
        $_SESSION['_auto_process_ts'] = time();
    }
}

$map = [
    'auth'        => 'AuthController',
    'user'        => 'UserController',
    'khutro'      => 'KhuTroController',
    'phong'       => 'PhongController',
    'nguoithue'   => 'NguoiThueController',
    'hopdong'     => 'HopDongController',
    'hoadon'      => 'HoaDonController',
    'baocao'      => 'BaoCaoController',
    'dashboard'   => 'DashboardController',
    'dongia'      => 'DonGiaController',
    'xe'          => 'XeController',
    'thongbao'    => 'ThongBaoController',
    'chuyenphong' => 'ChuyenPhongController',
    'chat'        => 'ChatController',
    'ai'          => 'AiController',
    'aiknowledge' => 'AiKnowledgeController',
];

if (!isset($map[$controller])) {
    die('Controller không tồn tại.');
}

$class = $map[$controller];
$obj   = new $class();

if (!method_exists($obj, $action)) {
    die('Action không tồn tại.');
}

$obj->$action();
