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

        // Danh sách phòng (cho bảng "Danh sách phòng")
        $phongs = $phongModel->getAll();

        // Doanh thu theo tháng trong năm nay (cho biểu đồ), mảng khóa 1..12
        $chartData = $hoadonModel->getDoanhThuTheoThang($nam);

        // Hóa đơn chưa thu CỦA THÁNG NÀY (cho thẻ "Hóa đơn chưa thu")
        $hoaDonChuaThu = array_values(array_filter(
            $hoadonModel->getCongNo(),
            fn($hd) => (int)$hd['thang'] === $thang && (int)$hd['nam'] === $nam
        ));

        require 'frontend/views/dashboard.php';
    }
}
