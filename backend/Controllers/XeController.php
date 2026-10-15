<?php
class XeController {
    private XeModel      $model;
    private HopDongModel $hdModel;

    public function __construct() {
        $this->model   = new XeModel();
        $this->hdModel = new HopDongModel();
    }

    public function them() {
        $hd_id = (int)($_GET['id'] ?? 0);
        $hd    = $this->hdModel->getById($hd_id);
        if (!$hd || $hd['trang_thai'] !== 'hieu_luc') {
            header('Location: index.php?controller=hopdong&action=index');
            exit;
        }

        $soXe  = $this->model->countByHopDong($hd_id);
        $title = 'Thêm xe';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $bien_so = trim($_POST['bien_so'] ?? '');
            $loai_xe = $_POST['loai_xe']      ?? 'xe_may';
            $mau_sac = trim($_POST['mau_sac'] ?? '');
            $ghi_chu = trim($_POST['ghi_chu'] ?? '');

            $soXe = $this->model->countByHopDong($hd_id);
            if (!$bien_so) {
                $error = 'Vui lòng nhập biển số xe!';
            } elseif ($soXe >= 4) {
                $error = 'Phòng này đã đủ 4 xe, không thể thêm!';
            } else {
                $this->model->create([
                    'hop_dong_id' => $hd_id,
                    'phong_id'    => $hd['phong_id'],
                    'bien_so'     => strtoupper($bien_so),
                    'loai_xe'     => $loai_xe,
                    'mau_sac'     => $mau_sac,
                    'ghi_chu'     => $ghi_chu,
                ]);
                header('Location: index.php?controller=hopdong&action=index&msg=xe_added');
                exit;
            }
        }
        require 'frontend/views/Xe/them_xe.php';
    }

    public function xoa() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hopdong&action=index');
            exit;
        }
        Csrf::check();
        $id = (int)($_POST['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?controller=hopdong&action=index&msg=xe_deleted');
        exit;
    }
}
