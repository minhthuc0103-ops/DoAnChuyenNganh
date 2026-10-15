<?php
class KhuTroController {
    private KhuTroModel $model;
    public function __construct() { $this->model = new KhuTroModel(); }

    public function index() {
        $list  = $this->model->getAll();
        $title = 'Quản lý khu trọ';
        require 'frontend/views/KhuTro/index.php';
    }

    public function create() {
        $title = 'Thêm khu trọ';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $ten    = trim($_POST['ten_khu'] ?? '');
            $ma     = strtoupper(trim($_POST['ma_khu'] ?? ''));
            $dia    = trim($_POST['dia_chi'] ?? '');
            $mo_ta  = trim($_POST['mo_ta']   ?? '');
            if (!$ten || !$ma) {
                $error = 'Vui lòng nhập tên và mã khu!';
            } elseif (!preg_match('/^[A-Z0-9]{1,5}$/', $ma)) {
                $error = 'Mã khu chỉ gồm chữ in hoa và số, tối đa 5 ký tự!';
            } else {
                $this->model->create($ten, $ma, $dia, $mo_ta);
                header('Location: index.php?controller=khutro&action=index&msg=created');
                exit;
            }
        }
        require 'frontend/views/KhuTro/create.php';
    }

    public function edit() {
        $id  = (int)($_GET['id'] ?? 0);
        $khu = $this->model->getById($id);
        if (!$khu) { header('Location: index.php?controller=khutro&action=index'); exit; }
        $title = 'Chỉnh sửa khu trọ';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $ten   = trim($_POST['ten_khu'] ?? '');
            $ma    = strtoupper(trim($_POST['ma_khu'] ?? ''));
            $dia   = trim($_POST['dia_chi'] ?? '');
            $mo_ta = trim($_POST['mo_ta']   ?? '');
            if (!$ten || !$ma) {
                $error = 'Vui lòng nhập tên và mã khu!';
            } else {
                $this->model->update($id, $ten, $ma, $dia, $mo_ta);
                header('Location: index.php?controller=khutro&action=index&msg=updated');
                exit;
            }
        }
        require 'frontend/views/KhuTro/edit.php';
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?controller=khutro&action=index&msg=deleted');
        exit;
    }

    // AJAX: lấy số phòng gợi ý tiếp theo
    public function getNextRoom() {
        $khu_id = (int)($_GET['khu_id'] ?? 0);
        $next   = $this->model->getNextSoPhong($khu_id);
        echo json_encode(['so_phong' => $next]);
        exit;
    }
}