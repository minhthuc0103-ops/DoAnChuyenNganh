<?php
class PhongController {
    private PhongModel  $model;
    private KhuTroModel $khuModel;

    public function __construct() {
        $this->model    = new PhongModel();
        $this->khuModel = new KhuTroModel();
    }

    public function index() {
        $khu_id = isset($_GET['khu_id']) ? (int)$_GET['khu_id'] : null;
        $filter = $_GET['filter'] ?? 'all';
        $phongs = $this->model->getAll($khu_id, $filter !== 'all' ? $filter : null);
        $khus   = $this->khuModel->getAll();
        $title  = 'Quản lý phòng trọ';
        require 'frontend/views/Phong/index.php';
    }

    public function create() {
        $title  = 'Thêm phòng mới';
        $error  = '';
        $khus   = $this->khuModel->getAll();
        $defKhu = (int)($_GET['khu_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $so_phong  = trim($_POST['so_phong']   ?? '');
            $gia       = (float)($_POST['gia']      ?? 0);
            $dien_tich = (float)($_POST['dien_tich']?? 0);
            $so_nguoi  = (int)($_POST['so_nguoi']   ?? 1);
            $mo_ta     = trim($_POST['mo_ta']       ?? '');
            $khu_id    = ($_POST['khu_id'] ?? '') !== '' ? (int)$_POST['khu_id'] : null;
            if (!$so_phong || $gia <= 0) {
                $error = 'Vui lòng nhập đầy đủ thông tin!';
            } elseif ($this->model->existsSoPhong($so_phong)) {
                $error = "Số phòng \"$so_phong\" đã tồn tại, vui lòng chọn tên khác!";
            } else {
                $paths = $this->uploadAnhPhong('new');
                $anh_phong = !empty($paths) ? json_encode($paths) : '';
                $this->model->create($so_phong, $gia, $dien_tich, $so_nguoi, $mo_ta, $khu_id, $anh_phong);
                header('Location: index.php?controller=phong&action=index&msg=created');
                exit;
            }
        }
        require 'frontend/views/Phong/create.php';
    }

    public function edit() {
        $id    = (int)($_GET['id'] ?? 0);
        $phong = $this->model->getById($id);
        if (!$phong) { header('Location: index.php?controller=phong&action=index'); exit; }
        $title = 'Chỉnh sửa phòng';
        $error = '';
        $khus  = $this->khuModel->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $so_phong   = trim($_POST['so_phong']   ?? '');
            $gia        = (float)($_POST['gia']      ?? 0);
            $dien_tich  = (float)($_POST['dien_tich']?? 0);
            $so_nguoi   = (int)($_POST['so_nguoi']   ?? 1);
            $mo_ta      = trim($_POST['mo_ta']       ?? '');
            $trang_thai = $_POST['trang_thai']       ?? 'trong';
            $khu_id     = ($_POST['khu_id'] ?? '') !== '' ? (int)$_POST['khu_id'] : null;
            if (!$so_phong || $gia <= 0) {
                $error = 'Vui lòng nhập đầy đủ thông tin!';
            } elseif ($this->model->existsSoPhong($so_phong, $id)) {
                $error = "Số phòng \"$so_phong\" đã tồn tại, vui lòng chọn tên khác!";
            } else {
                $existing = $this->normalizeAnhPhong($phong['anh_phong'] ?? '');
                $anh_phong = !empty($existing) ? json_encode($existing) : '';
                // Xử lý xóa ảnh cũ
                $xoaList = $_POST['xoa_anh'] ?? [];
                if (!empty($xoaList)) {
                    foreach ($xoaList as $path) {
                        $path = trim((string)$path);
                        if (in_array($path, $existing, true) && file_exists($path)) @unlink($path);
                        $existing = array_values(array_filter($existing, fn($p) => $p !== $path));
                    }
                    $anh_phong = !empty($existing) ? json_encode($existing) : '';
                }
                // Thêm ảnh mới
                $existing = array_merge($existing, $this->uploadAnhPhong((string)$id, 10 - count($existing)));
                $anh_phong = !empty($existing) ? json_encode(array_values(array_unique($existing))) : '';
                $this->model->update($id, $so_phong, $gia, $dien_tich, $so_nguoi, $mo_ta, $trang_thai, $khu_id, $anh_phong);

                // Lưu lịch sử nếu giá thay đổi
                if ((float)$phong['gia'] !== $gia) {
                    $lichSuGiaModel = new LichSuGiaModel();
                    $lichSuGiaModel->create(
                        $id,
                        (float)$phong['gia'],
                        $gia,
                        date('Y-m-d'),
                        trim($_POST['ly_do_doi_gia'] ?? ''),
                        $_SESSION['user'] ?? 'Admin'
                    );
                }

                header('Location: index.php?controller=phong&action=index&msg=updated');
                exit;
            }
        }
        require 'frontend/views/Phong/edit.php';
    }

    /**
     * Admin xem đầy đủ thông tin của một phòng.
     */
    public function chiTiet() {
        $id    = (int)($_GET['id'] ?? 0);
        $phong = $this->model->getById($id);
        if (!$phong) {
            header('Location: index.php?controller=phong&action=index');
            exit;
        }

        $db = Database::getInstance();
        $s = $db->prepare(
            "SELECT hd.*,
                    nt.ho_ten AS nguoi_thue, nt.sdt, nt.cccd, nt.dia_chi, nt.avatar
             FROM hop_dong hd
             LEFT JOIN nguoi_thue nt ON nt.id = hd.nguoi_thue_id
             WHERE hd.phong_id = ?
             ORDER BY CASE WHEN hd.trang_thai = 'hieu_luc' THEN 0 ELSE 1 END,
                      hd.created_at DESC, hd.id DESC"
        );
        $s->execute([$id]);
        $hopDongs = $s->fetchAll();

        $hopDongHieuLuc = null;
        foreach ($hopDongs as $hopDong) {
            if (($hopDong['trang_thai'] ?? '') === 'hieu_luc') {
                $hopDongHieuLuc = $hopDong;
                break;
            }
        }

        $nguoiO = [];
        $xeList = [];
        if ($hopDongHieuLuc) {
            $hopDongId = (int)$hopDongHieuLuc['id'];
            $nguoiO = (new NguoiThuePhongModel())->getByHopDong($hopDongId);
            $xeList = (new XeModel())->getByHopDong($hopDongId);
        }

        $hoaDons = (new HoaDonModel())->getByPhongId($id);
        $lichSuGia = (new LichSuGiaModel())->getByPhong($id);
        $tongNo = 0;
        foreach ($hoaDons as $hoaDon) {
            if (($hoaDon['trang_thai'] ?? '') === 'chua_tt') {
                $tongNo += (float)($hoaDon['tong_tien'] ?? 0);
            }
        }

        $title = 'Chi tiết phòng ' . $phong['so_phong'];
        require 'frontend/views/Phong/chi_tiet.php';
    }

    public function baoTri() {
        $id    = (int)($_GET['id'] ?? 0);
        $phong = $this->model->getById($id);
        if ($phong) {
            $tt = $phong['trang_thai'] === 'bao_tri' ? 'trong' : 'bao_tri';
            $this->model->updateTrangThai($id, $tt);
        }
        header('Location: index.php?controller=phong&action=index&msg=updated');
        exit;
    }

    /**
     * Xem lịch sử giá phòng
     */
    private function normalizeAnhPhong(string $value): array {
        $items = [];
        $walk = function ($data, int $depth = 0) use (&$walk, &$items) {
            if ($depth > 4 || $data === null || $data === '') return;
            if (is_array($data)) {
                foreach ($data as $item) $walk($item, $depth + 1);
                return;
            }
            $str = trim((string)$data, " \t\n\r\0\x0B\"'");
            $decoded = json_decode($str, true);
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== $str) {
                $walk($decoded, $depth + 1);
                return;
            }
            if (preg_match('#^public/uploads/phong/[^<>:"|?*]+\.(jpe?g|png|webp|gif)$#i', $str) && file_exists($str)) {
                $items[] = str_replace('\\', '/', $str);
            }
        };
        $walk($value);
        return array_values(array_unique($items));
    }

    private function uploadAnhPhong(string $prefix, int $limit = 10): array {
        if ($limit <= 0 || empty($_FILES['anh_phong']['tmp_name'][0])) return [];
        $uploadDir = 'public/uploads/phong/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $paths = [];
        foreach ($_FILES['anh_phong']['tmp_name'] as $i => $tmp) {
            if (empty($tmp) || count($paths) >= $limit) continue;
            if (!empty($_FILES['anh_phong']['error'][$i])) continue;
            if (($_FILES['anh_phong']['size'][$i] ?? 0) > 5 * 1024 * 1024) continue;

            $ext = strtolower(pathinfo($_FILES['anh_phong']['name'][$i], PATHINFO_EXTENSION));
            $mime = function_exists('mime_content_type') ? mime_content_type($tmp) : ($_FILES['anh_phong']['type'][$i] ?? '');
            if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) continue;

            $safePrefix = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix) ?: 'phong';
            $name = 'phong_' . $safePrefix . '_' . time() . '_' . bin2hex(random_bytes(3)) . '_' . $i . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $name)) {
                $paths[] = $uploadDir . $name;
            }
        }
        return $paths;
    }

    public function lichSuGia() {
        $id    = (int)($_GET['id'] ?? 0);
        $phong = $this->model->getById($id);
        if (!$phong) { header('Location: index.php?controller=phong&action=index'); exit; }

        $lichSuGiaModel = new LichSuGiaModel();
        $lichSuGia = $lichSuGiaModel->getByPhong($id);
        $title = 'Lịch sử giá phòng ' . $phong['so_phong'];
        require 'frontend/views/Phong/lich_su_gia.php';
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=phong&action=index');
            exit;
        }
        Csrf::check();
        $id = (int)($_POST['id'] ?? 0);
        // Lỗi #14: Kiểm tra có hợp đồng hiệu lực
        $db = Database::getInstance();
        $check = $db->prepare("SELECT COUNT(*) FROM hop_dong WHERE phong_id = ? AND trang_thai = 'hieu_luc'");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            header('Location: index.php?controller=phong&action=index&msg=has_active_contract');
            exit;
        }
        $this->model->delete($id);
        header('Location: index.php?controller=phong&action=index&msg=deleted');
        exit;
    }
}
