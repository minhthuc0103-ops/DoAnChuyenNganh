<?php
class HoaDonController {
    private HoaDonModel  $model;
    private PhongModel   $phongModel;
    private DonGiaModel  $donGiaModel;
    private KhuTroModel  $khuModel;

    public function __construct() {
        $this->model       = new HoaDonModel();
        $this->phongModel  = new PhongModel();
        $this->donGiaModel = new DonGiaModel();
        $this->khuModel    = new KhuTroModel();
    }

    public function index() {
        $thang = (int)($_GET['thang'] ?? date('m'));
        $nam   = (int)($_GET['nam']   ?? date('Y'));
        $list  = $this->model->getByThangNam($thang, $nam);
        $title = 'Quản lý hóa đơn';
        require 'frontend/views/HoaDon/index.php';
    }

    public function create() {
        $title  = 'Tạo hóa đơn';
        $error  = '';
        $khus   = $this->khuModel->getAll();
        $phongs = $this->phongModel->getAll();
        $donGia = $this->donGiaModel->getCurrent();
        $thang  = (int)date('m');
        $nam    = (int)date('Y');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $phong_id = (int)($_POST['phong_id'] ?? 0);
            $thang    = (int)($_POST['thang']    ?? date('m'));
            $nam      = (int)($_POST['nam']      ?? date('Y'));
            $dc = (float)($_POST['chi_so_dien_cu']  ?? 0);
            $dm = (float)($_POST['chi_so_dien_moi'] ?? 0);
            $nc = (float)($_POST['chi_so_nuoc_cu']  ?? 0);
            $nm = (float)($_POST['chi_so_nuoc_moi'] ?? 0);
            $p  = $phong_id ? $this->phongModel->getById($phong_id) : false;

            if (!$phong_id) {
                $error = 'Vui lòng chọn phòng!';
            } elseif (!$p) {
                $error = 'Phong khong ton tai!';
            } elseif ($this->model->existsForPhongThang($phong_id, $thang, $nam)) {
                $error = "Phòng này đã có hóa đơn tháng $thang/$nam!";
            } elseif (!$this->phongCoHopDongHieuLuc($phong_id)) {
                $error = 'Chi duoc lap hoa don cho phong dang co hop dong hieu luc!';
            } elseif ($dm < $dc) {
                $error = "Chỉ số điện mới ($dm) không được nhỏ hơn chỉ số cũ ($dc)!";
            } elseif ($nm < $nc) {
                $error = "Chỉ số nước mới ($nm) không được nhỏ hơn chỉ số cũ ($nc)!";
            } else {
                $td     = ($dm - $dc) * $donGia['gia_dien'];
                $tn     = ($nm - $nc) * $donGia['gia_nuoc'];
                $phi_dv = (float)($donGia['phi_dv'] ?? 150000);
                // Đếm xe của hợp đồng đang hiệu lực
                $db = Database::getInstance();
                $sXe = $db->prepare("SELECT COUNT(*) FROM xe x JOIN hop_dong hd ON x.hop_dong_id=hd.id WHERE hd.phong_id=? AND hd.trang_thai='hieu_luc'");
                $sXe->execute([$phong_id]);
                $so_xe  = (int)$sXe->fetchColumn();
                $phi_xe = $so_xe * 100000;
                $tong_tien = (float)$p['gia'] + $td + $tn + $phi_dv + $phi_xe;
                $created = $this->model->create([
                    'phong_id'        => $phong_id, 'thang' => $thang, 'nam' => $nam,
                    'chi_so_dien_cu'  => $dc, 'chi_so_dien_moi' => $dm,
                    'chi_so_nuoc_cu'  => $nc, 'chi_so_nuoc_moi' => $nm,
                    'tien_phong'      => (float)$p['gia'],
                    'tien_dien'       => $td, 'tien_nuoc' => $tn,
                    'phi_dich_vu'     => $phi_dv,
                    'phi_xe'          => $phi_xe,
                    'so_xe'           => $so_xe,
                    'tong_tien'       => $tong_tien,
                ]);
                if ($created) {
                    $this->taoThongBaoDongTien($phong_id, $p['so_phong'] ?? '', $thang, $nam, $tong_tien, $this->model->getLastInsertId());
                }
                header('Location: index.php?controller=hoadon&action=index&msg=created');
                exit;
            }
        }
        require 'frontend/views/HoaDon/create.php';
    }

    private function phongCoHopDongHieuLuc(int $phong_id): bool {
        $db = Database::getInstance();
        $s = $db->prepare("SELECT COUNT(*) FROM hop_dong WHERE phong_id=? AND trang_thai='hieu_luc'");
        $s->execute([$phong_id]);
        return (int)$s->fetchColumn() > 0;
    }

    private function taoThongBaoDongTien(int $phong_id, string $so_phong, int $thang, int $nam, float $tong_tien, int $hoa_don_id): void {
        $db = Database::getInstance();
        $s = $db->prepare(
            "SELECT DISTINCT nt.account_id
             FROM nguoi_thue nt
             JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id
             WHERE hd.phong_id = ?
               AND hd.trang_thai = 'hieu_luc'
               AND nt.account_id IS NOT NULL"
        );
        $s->execute([$phong_id]);
        $nguoiNhan = array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
        if (empty($nguoiNhan)) return;

        $tieuDe = "Thong bao dong tien phong $so_phong thang $thang/$nam";
        $noiDung = "Hoa don phong $so_phong thang $thang/$nam da duoc tao.\n"
                 . "Tong tien can thanh toan: " . number_format($tong_tien, 0, ',', '.') . "d\n"
                 . "Vui long thanh toan trong han 01-05 hang thang.\n\n"
                 . "Xem chi tiet/tai QR thanh toan tai: index.php?controller=hoadon&action=chiTiet&id=$hoa_don_id";

        $tbModel = new ThongBaoModel();
        $tbId = $tbModel->createAndGetId($tieuDe, $noiDung, 'tien_phong', $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Admin');
        if ($tbId) {
            $tbModel->anVoiTatCaTruNguoiNhan($tbId, $nguoiNhan);
        }
    }

    // AJAX: lấy danh sách phòng theo khu
    public function getPhongByKhu() {
        $khu_id = (int)($_GET['khu_id'] ?? 0);
        $phongs = $this->phongModel->getAll($khu_id ?: null);
        header('Content-Type: application/json');
        echo json_encode($phongs);
        exit;
    }

    // AJAX: lấy chỉ số điện nước cuối cùng của phòng
    public function getChiSoCu() {
        $phong_id = (int)($_GET['phong_id'] ?? 0);
        $data = $this->model->getChiSoCuoiCung($phong_id);
        // Đếm xe đang có trong hợp đồng hiệu lực
        $db = Database::getInstance();
        $sXe = $db->prepare("SELECT COUNT(*) FROM xe x JOIN hop_dong hd ON x.hop_dong_id=hd.id WHERE hd.phong_id=? AND hd.trang_thai='hieu_luc'");
        $sXe->execute([$phong_id]);
        $data['so_xe'] = (int)$sXe->fetchColumn();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function thanhToan() {
        $id = (int)($_GET['id'] ?? 0);
        $hd = $this->model->getById($id);

        if (!$hd || $hd['trang_thai'] !== 'chua_tt') {
            header('Location: index.php?controller=hoadon&action=index');
            exit;
        }

        // POST: xử lý thanh toán
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $phuong_thuc = $_POST['phuong_thuc'] ?? 'tien_mat';
            $ghi_chu     = trim($_POST['ghi_chu_tt'] ?? '');
            $nguoi_thu   = $_SESSION['user'] ?? 'Admin';

            $this->model->updateThanhToan($id, $phuong_thuc, $nguoi_thu, $ghi_chu ?: ('Thu tiền tháng ' . $hd['thang'] . '/' . $hd['nam']));

            // Lưu lịch sử thanh toán
            $lsttModel = new LichSuThanhToanModel();
            $ghiChuLichSu = $ghi_chu ?: ('Thu tien thang ' . $hd['thang'] . '/' . $hd['nam']);
            $resolvedPending = $lsttModel->markPendingResolved($id, $nguoi_thu, $ghiChuLichSu);
            if (!$resolvedPending) {
                $lsttModel->create([
                    'hoa_don_id'  => $id,
                    'phong_id'    => $hd['phong_id'],
                    'so_tien'     => $hd['tong_tien'],
                    'phuong_thuc' => $phuong_thuc,
                    'trang_thai'  => 'thanh_cong',
                    'nguoi_thu'   => $nguoi_thu,
                    'nguoi_tra'   => '',
                    'ghi_chu'     => $ghiChuLichSu,
                ]);
            }

            header('Location: index.php?controller=hoadon&action=index&msg=paid');
            exit;
        }

        // GET: hiển thị form chọn phương thức
        $title = 'Xác nhận thanh toán';
        require 'frontend/views/HoaDon/thanh_toan.php';
    }

    public function xacNhanThanhToanNguoiThue() {
        if (($_SESSION['vai_tro'] ?? '') !== 'user' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $hd = $this->model->getById($id);
        if (!$hd || $hd['trang_thai'] !== 'chua_tt') {
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        $ntModel = new NguoiThueModel();
        $nguoiThue = $ntModel->getByAccountId((int)$_SESSION['user_id']);
        if (!$nguoiThue || (int)$nguoiThue['phong_id'] !== (int)$hd['phong_id']) {
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        $allowed = ['tien_mat', 'chuyen_khoan', 'momo', 'vnpay'];
        $phuong_thuc = $_POST['phuong_thuc'] ?? 'chuyen_khoan';
        if (!in_array($phuong_thuc, $allowed, true)) {
            $phuong_thuc = 'chuyen_khoan';
        }

        $lsttModel = new LichSuThanhToanModel();
        if ($lsttModel->hasPendingForInvoice($id)) {
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        $nguoiTra = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? '';
        $ghiChu = 'Nguoi thue gui xac nhan da thanh toan thang ' . $hd['thang'] . '/' . $hd['nam'];
        $lsttModel->create([
            'hoa_don_id'  => $id,
            'phong_id'    => $hd['phong_id'],
            'so_tien'     => $hd['tong_tien'],
            'phuong_thuc' => $phuong_thuc,
            'trang_thai'  => 'dang_xu_ly',
            'nguoi_thu'   => 'Cho quan ly xac nhan',
            'nguoi_tra'   => $nguoiTra,
            'ghi_chu'     => $ghiChu,
        ]);

        header('Location: index.php?controller=user&action=index');
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hoadon&action=index');
            exit;
        }
        Csrf::check();
        $id = (int)($_POST['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?controller=hoadon&action=index&msg=deleted');
        exit;
    }

    /**
     * Xem chi tiết hóa đơn
     */
    public function chiTiet() {
        $id = (int)($_GET['id'] ?? 0);
        $hd = $this->model->getById($id);

        if (!$hd) {
            header('Location: index.php?controller=hoadon&action=index');
            exit;
        }

        // User chỉ xem hóa đơn phòng mình
        if (($_SESSION['vai_tro'] ?? '') === 'user') {
            $ntModel = new NguoiThueModel();
            $nguoiThue = $ntModel->getByAccountId((int)$_SESSION['user_id']);
            if (!$nguoiThue || (int)$nguoiThue['phong_id'] !== (int)$hd['phong_id']) {
                header('Location: index.php?controller=user&action=index');
                exit;
            }
        }

        // Lấy thông tin phòng
        $phong = $this->phongModel->getById((int)$hd['phong_id']);
        $donGia = $this->donGiaModel->getCurrent();

        $title = 'Chi tiết hóa đơn';
        require 'frontend/views/HoaDon/chi_tiet.php';
    }

    public function congNo() {
        $list  = $this->model->getCongNo();
        $title = 'Danh sách công nợ';
        require 'frontend/views/HoaDon/cong_no.php';
    }

    /**
     * Lịch sử thanh toán
     */
    public function lichSu() {
        $lsttModel = new LichSuThanhToanModel();

        // User chỉ xem lịch sử phòng của mình
        if (($_SESSION['vai_tro'] ?? '') === 'user') {
            $ntModel = new NguoiThueModel();
            $nguoiThue = $ntModel->getByAccountId((int)$_SESSION['user_id']);
            if ($nguoiThue && !empty($nguoiThue['phong_id'])) {
                $list = $lsttModel->getByPhong((int)$nguoiThue['phong_id']);
            } else {
                $list = [];
            }
        } else {
            $list = $lsttModel->getAll(200);
        }

        $title = 'Lịch sử thanh toán';
        require 'frontend/views/HoaDon/lich_su.php';
    }
}

