<?php
class LuuTruController {
    private LuuTruModel $model;
    private KhuTroModel $khuModel;
    private PhongModel  $phongModel;

    public function __construct() {
        $this->model      = new LuuTruModel();
        $this->khuModel   = new KhuTroModel();
        $this->phongModel = new PhongModel();
    }

    public function index() {
        $filter = [
            'khu_id'     => $_GET['khu_id'] ?? '',
            'phong_id'   => $_GET['phong_id'] ?? '',
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'keyword'    => $_GET['keyword'] ?? '',
            'chi_hieu_luc' => !LocHopDong::xemLichSu(),   // mặc định: chỉ hợp đồng đang hiệu lực
        ];

        $list   = $this->model->getAll($filter);
        $stats  = $this->model->getStats();
        $khus   = $this->khuModel->getAll();
        $phongs = $this->phongModel->getAll();
        $title  = 'Khai báo lưu trú (Tạm trú)';

        require 'frontend/views/LuuTru/index.php';
    }

    public function sync() {
        Csrf::check();
        $count = $this->model->syncFromContracts();
        header('Location: index.php?controller=luutru&action=index&msg=synced&count=' . $count);
        exit;
    }

    public function create() {
        $title  = 'Thêm thông tin lưu trú';
        $error  = '';
        $khus   = $this->khuModel->getAll();
        $phongs = $this->phongModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $phong_id = (int)($_POST['phong_id'] ?? 0);
            $ho_ten   = trim($_POST['ho_ten'] ?? '');
            $cccd     = trim($_POST['cccd'] ?? '');

            if (!$phong_id || !$ho_ten || !$cccd) {
                $error = 'Vui lòng điền các trường bắt buộc (Phòng, Họ tên, CCCD)!';
            } else {
                // Tìm người thuê theo CCCD nếu có
                $db = Database::getInstance();
                $sNt = $db->prepare("SELECT id FROM nguoi_thue WHERE cccd = ? LIMIT 1");
                $sNt->execute([$cccd]);
                $ntId = (int)$sNt->fetchColumn();

                if (!$ntId) {
                    // Lấy chủ phòng đại diện
                    $sHd = $db->prepare("SELECT nguoi_thue_id FROM hop_dong WHERE phong_id = ? AND trang_thai = 'hieu_luc' LIMIT 1");
                    $sHd->execute([$phong_id]);
                    $ntId = (int)$sHd->fetchColumn() ?: 1;
                }

                $this->model->create([
                    'phong_id'        => $phong_id,
                    'nguoi_thue_id'   => $ntId,
                    'ho_ten'          => $ho_ten,
                    'ngay_sinh'       => $_POST['ngay_sinh'] ?: null,
                    'gioi_tinh'       => $_POST['gioi_tinh'] ?? 'nam',
                    'cccd'            => $cccd,
                    'ngay_cap_cccd'   => $_POST['ngay_cap_cccd'] ?: null,
                    'noi_cap_cccd'    => trim($_POST['noi_cap_cccd'] ?? ''),
                    'sdt'             => trim($_POST['sdt'] ?? ''),
                    'thuong_tru'      => trim($_POST['thuong_tru'] ?? ''),
                    'nghe_nghiep'     => trim($_POST['nghe_nghiep'] ?? ''),
                    'ngay_den'        => $_POST['ngay_den'] ?: date('Y-m-d'),
                    'ngay_di'         => $_POST['ngay_di'] ?: null,
                    'ly_do'           => trim($_POST['ly_do'] ?? 'Thuê trọ để sinh hoạt, làm việc'),
                    'ma_dvc'          => trim($_POST['ma_dvc'] ?? ''),
                    'trang_thai'      => $_POST['trang_thai'] ?? 'cho_khai_bao',
                    'ghi_chu'         => trim($_POST['ghi_chu'] ?? '')
                ]);

                header('Location: index.php?controller=luutru&action=index&msg=created');
                exit;
            }
        }

        require 'frontend/views/LuuTru/create.php';
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header('Location: index.php?controller=luutru&action=index');
            exit;
        }

        $title  = 'Cập nhật thông tin lưu trú';
        $error  = '';
        $phongs = $this->phongModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $phong_id = (int)($_POST['phong_id'] ?? 0);
            $ho_ten   = trim($_POST['ho_ten'] ?? '');
            $cccd     = trim($_POST['cccd'] ?? '');

            if (!$phong_id || !$ho_ten || !$cccd) {
                $error = 'Vui lòng điền đủ thông tin bắt buộc!';
            } else {
                $this->model->update($id, [
                    'phong_id'        => $phong_id,
                    'ho_ten'          => $ho_ten,
                    'ngay_sinh'       => $_POST['ngay_sinh'] ?: null,
                    'gioi_tinh'       => $_POST['gioi_tinh'] ?? 'nam',
                    'cccd'            => $cccd,
                    'ngay_cap_cccd'   => $_POST['ngay_cap_cccd'] ?: null,
                    'noi_cap_cccd'    => trim($_POST['noi_cap_cccd'] ?? ''),
                    'sdt'             => trim($_POST['sdt'] ?? ''),
                    'thuong_tru'      => trim($_POST['thuong_tru'] ?? ''),
                    'nghe_nghiep'     => trim($_POST['nghe_nghiep'] ?? ''),
                    'ngay_den'        => $_POST['ngay_den'] ?: date('Y-m-d'),
                    'ngay_di'         => $_POST['ngay_di'] ?: null,
                    'ly_do'           => trim($_POST['ly_do'] ?? 'Thuê trọ để sinh hoạt, làm việc'),
                    'ma_dvc'          => trim($_POST['ma_dvc'] ?? ''),
                    'trang_thai'      => $_POST['trang_thai'] ?? 'cho_khai_bao',
                    'ghi_chu'         => trim($_POST['ghi_chu'] ?? '')
                ]);

                header('Location: index.php?controller=luutru&action=index&msg=updated');
                exit;
            }
        }

        require 'frontend/views/LuuTru/edit.php';
    }

    public function updateStatus() {
        Csrf::check();
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['trang_thai'] ?? '';
        $maDvc  = trim($_POST['ma_dvc'] ?? '');

        if ($id && in_array($status, ['cho_khai_bao', 'da_gui_dvc', 'da_tiep_nhan', 'ket_thuc'])) {
            $this->model->updateStatus($id, $status, $maDvc ?: null);
        }

        header('Location: index.php?controller=luutru&action=index&msg=status_updated');
        exit;
    }

    public function delete() {
        Csrf::check();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->model->delete($id);
        }
        header('Location: index.php?controller=luutru&action=index&msg=deleted');
        exit;
    }

    private function laQuanTri(): bool {
        return in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true);
    }

    /** Gợi ý khi quản trị gõ tên / SĐT / CCCD ở ô tìm kiếm (JSON). */
    public function suggest() {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->laQuanTri()) {
            echo json_encode(['ok' => false, 'results' => []]);
            exit;
        }
        $q = trim($_GET['q'] ?? '');
        if (mb_strlen($q, 'UTF-8') < 2) {
            echo json_encode(['ok' => true, 'results' => []]);
            exit;
        }
        $results = array_map(fn($r) => [
            'id'    => (int)$r['id'],
            'name'  => $r['ho_ten'],
            'cccd'  => $r['cccd'],
            'phone' => $r['sdt'],
            'room'  => $r['so_phong'],
            'area'  => $r['ten_khu'] ?? '',
        ], $this->model->search($q, 8));
        echo json_encode(['ok' => true, 'results' => $results], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Trang chi tiết thông tin cá nhân của một người đã đăng ký lưu trú (chỉ quản trị). */
    public function detail() {
        if (!$this->laQuanTri()) {
            header('Location: index.php?controller=user&action=luuTru');
            exit;
        }
        $id   = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header('Location: index.php?controller=luutru&action=index');
            exit;
        }
        $history = $this->model->getHistoryByCccd((string)$item['cccd']);
        $title   = 'Chi tiết lưu trú - ' . $item['ho_ten'];
        require 'frontend/views/LuuTru/chi_tiet.php';
    }

    public function mauCT01() {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            die('Không tìm thấy bản ghi lưu trú!');
        }
        // Người dùng chỉ được in CT01 của người ở trong phòng mình
        if (!$this->laQuanTri() && !$this->model->userCanView((int)($_SESSION['user_id'] ?? 0), $id)) {
            http_response_code(403);
            die('Bạn không có quyền xem bản ghi này.');
        }

        // Lấy thông tin chủ hộ / chủ trọ
        $db = Database::getInstance();
        $sOwner = $db->query("SELECT * FROM account WHERE vai_tro IN ('quan_ly', 'chu_tro') LIMIT 1");
        $owner = $sOwner->fetch();

        require 'frontend/views/LuuTru/mau_ct01.php';
    }

    public function export() {
        $list = $this->model->getAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=danh_sach_khai_bao_luu_tru_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        // UTF-8 BOM cho Excel mở tiếng Việt không lỗi
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, ['STT', 'Họ và tên', 'Ngày sinh', 'Giới tính', 'Số CCCD', 'Ngày cấp', 'Nơi cấp', 'SĐT', 'Thường trú', 'Phòng', 'Khu trọ', 'Ngày đến', 'Ngày đi', 'Lý do', 'Mã hồ sơ DVC', 'Trạng thái']);

        $i = 1;
        $statusLabels = [
            'cho_khai_bao' => 'Chờ khai báo',
            'da_gui_dvc'   => 'Đã nộp Dịch vụ công',
            'da_tiep_nhan' => 'Đã tiếp nhận',
            'ket_thuc'     => 'Đã trả phòng/kết thúc'
        ];

        foreach ($list as $r) {
            fputcsv($output, [
                $i++,
                $r['ho_ten'],
                $r['ngay_sinh'] ? date('d/m/Y', strtotime($r['ngay_sinh'])) : '',
                $r['gioi_tinh'] === 'nam' ? 'Nam' : 'Nữ',
                "'" . $r['cccd'],
                $r['ngay_cap_cccd'] ? date('d/m/Y', strtotime($r['ngay_cap_cccd'])) : '',
                $r['noi_cap_cccd'],
                "'" . $r['sdt'],
                $r['thuong_tru'],
                $r['so_phong'],
                $r['ten_khu'],
                $r['ngay_den'] ? date('d/m/Y', strtotime($r['ngay_den'])) : '',
                $r['ngay_di'] ? date('d/m/Y', strtotime($r['ngay_di'])) : '',
                $r['ly_do'],
                $r['ma_dvc'],
                $statusLabels[$r['trang_thai']] ?? $r['trang_thai']
            ]);
        }
        fclose($output);
        exit;
    }
}

