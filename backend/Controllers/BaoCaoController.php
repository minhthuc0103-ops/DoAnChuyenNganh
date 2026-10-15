<?php
class BaoCaoController {
    private HoaDonModel $hoadonModel;
    private PhongModel  $phongModel;

    public function __construct() {
        $this->hoadonModel = new HoaDonModel();
        $this->phongModel  = new PhongModel();
    }

    public function index() {
        $nam      = (int)($_GET['nam'] ?? date('Y'));
        $chartData = $this->hoadonModel->getDoanhThuTheoThang($nam);
        $stats     = $this->phongModel->countByTrangThai();
        $title     = 'Báo cáo thống kê doanh thu';

        // Tổng năm
        $tongNam = array_sum($chartData);

        // Tháng hiện tại
        $thangHT       = (int)date('m');
        $doanhThuThang = $this->hoadonModel->getTongDoanhThuThang($thangHT, $nam);

        require 'frontend/views/BaoCao/index.php';
    }
}