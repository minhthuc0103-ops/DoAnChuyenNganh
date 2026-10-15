<?php
class DashboardController {
    public function index() {
        // Tự động dọn tài khoản không hợp lệ
        (new AutoCleanController())->run();
        $phongModel   = new PhongModel();
        $hoadonModel  = new HoaDonModel();
        $hopdongModel = new HopDongModel();
        $nguoiThueModel = new NguoiThueModel();

        $stats = $phongModel->countByTrangThai();
        $tongPhong    = array_sum($stats);
        $phongTrong   = $stats['trong']     ?? 0;
        $phongDangThue= $stats['dang_thue'] ?? 0;
        $phongBaoTri  = $stats['bao_tri']   ?? 0;

        $thang = (int)date('m');
        $nam   = (int)date('Y');
        $doanhThuThang = $hoadonModel->getTongDoanhThuThang($thang, $nam);
        $congNo        = $hoadonModel->countChuaTT();
        $sapHetHan     = $hopdongModel->getSapHetHan(30);
        $tongNguoiThue = $nguoiThueModel->count();

        require 'frontend/views/dashboard.php';
    }
}