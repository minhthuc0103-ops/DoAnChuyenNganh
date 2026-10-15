<?php
class DonGiaController {
    private DonGiaModel $model;

    public function __construct() { $this->model = new DonGiaModel(); }

    public function index() {
        $donGia = $this->model->getCurrent();
        $title  = 'Cài đặt đơn giá';
        $msg    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $gia_dien_cu  = (int)($donGia['gia_dien'] ?? 0);
            $gia_nuoc_cu  = (int)($donGia['gia_nuoc'] ?? 0);

            $gia_dien_moi = (int)($_POST['gia_dien'] ?? 0);
            $gia_nuoc_moi = (int)($_POST['gia_nuoc'] ?? 0);
            $phi_dv_moi   = (int)($_POST['phi_dv']   ?? 150000);

            $this->model->update($gia_dien_moi, $gia_nuoc_moi, $phi_dv_moi);
            $donGia = $this->model->getCurrent();
            $msg    = 'Cập nhật đơn giá thành công!';

            // Gửi thông báo vào nhóm chat nếu có thay đổi
            $lines = [];
            if ($gia_dien_moi !== $gia_dien_cu) {
                $lines[] = "⚡ Giá điện: " . number_format($gia_dien_cu) . "đ → " . number_format($gia_dien_moi) . "đ/kWh";
            }
            if ($gia_nuoc_moi !== $gia_nuoc_cu) {
                $lines[] = "💧 Giá nước: " . number_format($gia_nuoc_cu) . "đ → " . number_format($gia_nuoc_moi) . "đ/m³";
            }

            if (!empty($lines)) {
                $chatModel = new ChatModel();
                $noi_dung  = "📢 Thông báo cập nhật đơn giá:\n" . implode("\n", $lines)
                           . "\n\nÁp dụng từ: " . date('d/m/Y H:i');
                $admin_id  = (int)($_SESSION['user_id'] ?? 0);
                $ho_ten    = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Quản lý';
                $chatModel->send($admin_id, $ho_ten, 'quan_ly', $noi_dung, 'text');
            }
        }

        require 'frontend/views/HoaDon/don_gia.php';
    }
}