<?php
class UserController {
    private NguoiThueModel $nguoiThueModel;
    private HoaDonModel $hoaDonModel;
    private NguoiThuePhongModel $ntp;

    public function __construct() {
        $this->nguoiThueModel = new NguoiThueModel();
        $this->hoaDonModel    = new HoaDonModel();
        $this->ntp            = new NguoiThuePhongModel();
    }

    public function index() {
        $account_id = (int)$_SESSION['user_id'];
        $nguoi_thue = $this->nguoiThueModel->getByAccountId($account_id);

        $hoa_don_list = [];
        $phong_nguoi  = [];
        $thong_bao_list = (new ThongBaoModel())->getChuaDoc($account_id, 5);

        if ($nguoi_thue && !empty($nguoi_thue['hop_dong_id'])) {
            $hoa_don_list = $this->hoaDonModel->getByPhongId((int)$nguoi_thue['phong_id']);
            $phong_nguoi  = $this->ntp->getByHopDong((int)$nguoi_thue['hop_dong_id']);
        }

        $title = 'Phòng của tôi';
        require 'frontend/views/User/index.php';
    }

    /**
     * Xem hợp đồng phòng mình
     */
    public function hopDong() {
        $account_id = (int)$_SESSION['user_id'];
        $nguoi_thue = $this->nguoiThueModel->getByAccountId($account_id);

        $hopDong    = null;
        $phong_nguoi = [];
        $xeList     = [];
        $sucChuaPhong = 4;

        if ($nguoi_thue && !empty($nguoi_thue['hop_dong_id'])) {
            $hdModel  = new HopDongModel();
            $hopDong  = $hdModel->getById((int)$nguoi_thue['hop_dong_id']);
            $phong_nguoi = $this->ntp->getByHopDong((int)$nguoi_thue['hop_dong_id']);

            $xeModel = new XeModel();
            $xeList  = $xeModel->getByHopDong((int)$nguoi_thue['hop_dong_id']);

            if ($hopDong && !empty($hopDong['phong_id'])) {
                $db = Database::getInstance();
                $capStmt = $db->prepare("SELECT so_nguoi FROM phong WHERE id = ?");
                $capStmt->execute([(int)$hopDong['phong_id']]);
                $sucChuaPhong = max(1, (int)$capStmt->fetchColumn());
            }
        }

        $yeuCauOCungModel = new YeuCauNguoiOCungModel();
        $yeuCauOCungList = $yeuCauOCungModel->getByUser($account_id);
        $soYeuCauOCungChoDuyet = $yeuCauOCungModel->countPendingByUser($account_id);

        $title = 'Hợp đồng của tôi';
        require 'frontend/views/User/hop_dong.php';
    }

    public function guiYeuCauOCung() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=hopDong');
            exit;
        }

        $model = new YeuCauNguoiOCungModel();
        try {
            $account_id = (int)$_SESSION['user_id'];
            $rental = $model->getCurrentRentalByAccount($account_id);
            if (!$rental) {
                throw new RuntimeException('Bạn chưa có hợp đồng hiệu lực để gửi yêu cầu.');
            }

            $hoTen = trim($_POST['ho_ten'] ?? '');
            if ($hoTen === '') {
                throw new RuntimeException('Vui lòng nhập họ tên người ở cùng.');
            }
            if (mb_strlen($hoTen, 'UTF-8') > 150) {
                throw new RuntimeException('Họ tên tối đa 150 ký tự.');
            }

            $gioiTinh = $_POST['gioi_tinh'] ?? 'nam';
            if (!in_array($gioiTinh, ['nam', 'nu', 'khac'], true)) {
                $gioiTinh = 'nam';
            }

            $model->createRequest([
                'user_id' => $account_id,
                'nguoi_thue_id' => (int)$rental['nguoi_thue_id'],
                'hop_dong_id' => (int)$rental['hop_dong_id'],
                'phong_id' => (int)$rental['phong_id'],
                'ho_ten' => $hoTen,
                'cccd' => trim($_POST['cccd'] ?? ''),
                'sdt' => trim($_POST['sdt'] ?? ''),
                'ngay_sinh' => trim($_POST['ngay_sinh'] ?? ''),
                'gioi_tinh' => $gioiTinh,
                'que_quan' => trim($_POST['que_quan'] ?? ''),
                'ly_do' => trim($_POST['ly_do'] ?? ''),
            ]);

            $noiDung = "Người thuê {$rental['ho_ten']} vừa gửi yêu cầu thêm người ở cùng.\n"
                     . "Phòng: {$rental['so_phong']}"
                     . (!empty($rental['ten_khu']) ? " - {$rental['ten_khu']}" : '')
                     . "\nNgười muốn thêm: {$hoTen}";
            $model->notifyAdmins('Có yêu cầu thêm người ở cùng mới', $noiDung, (string)$rental['ho_ten']);

            header('Location: index.php?controller=user&action=hopDong&msg=roommate_sent');
        } catch (Throwable $e) {
            header('Location: index.php?controller=user&action=hopDong&msg=roommate_error&err=' . urlencode($e->getMessage()));
        }
        exit;
    }

    public function huyYeuCauOCung() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=hopDong');
            exit;
        }

        $model = new YeuCauNguoiOCungModel();
        $ok = $model->cancelByUser((int)($_POST['id'] ?? 0), (int)($_SESSION['user_id'] ?? 0));
        header('Location: index.php?controller=user&action=hopDong&msg=' . ($ok ? 'roommate_cancelled' : 'roommate_error'));
        exit;
    }

    public function khuPhong() {
        $khuModel = new KhuTroModel();
        $list  = $khuModel->getAll();
        $title = 'Khu & Phòng trọ';
        require 'frontend/views/User/khu_phong.php';
    }

    public function phong() {
        $khu_id   = isset($_GET['khu_id']) ? (int)$_GET['khu_id'] : null;
        $filter   = $_GET['filter'] ?? 'all';
        $phongModel = new PhongModel();
        $khuModel   = new KhuTroModel();
        $phongs = $phongModel->getAll($khu_id, $filter === 'all' ? null : $filter);
        $khus   = $khuModel->getAll();
        $title  = 'Danh sách phòng';
        require 'frontend/views/User/phong.php';
    }

    public function profile() {
        $account_id = (int)$_SESSION['user_id'];
        $db = \Database::getInstance();
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check(); // ═══ Lỗi #3: CSRF ═══
            $action = $_POST['_action'] ?? '';

            if ($action === 'update_avatar') {
                if (!empty($_FILES['avatar']['tmp_name'])) {
                    $uploadDir = 'public/uploads/avatar/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $ext   = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

                    // ═══ Lỗi #1: Validate extension + MIME type ═══
                    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $mime = function_exists('mime_content_type')
                        ? mime_content_type($_FILES['avatar']['tmp_name'])
                        : ($_FILES['avatar']['type'] ?? '');

                    if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
                        $error = 'Ảnh đại diện chỉ chấp nhận JPG, PNG, WEBP hoặc GIF!';
                    } elseif (($_FILES['avatar']['size'] ?? 0) > 5 * 1024 * 1024) {
                        $error = 'Ảnh đại diện tối đa 5MB!';
                    } else {
                        $fname = 'avatar_' . $account_id . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $fname)) {
                            $db->prepare("UPDATE nguoi_thue SET avatar=? WHERE account_id=?")
                               ->execute([$uploadDir . $fname, $account_id]);
                            $success = 'Cập nhật ảnh đại diện thành công!';
                        } else {
                            $error = 'Upload thất bại, thử lại!';
                        }
                    }
                }
            } elseif ($action === 'update_info') {
                $ho_ten   = trim($_POST['ho_ten']   ?? '');
                $email    = trim($_POST['email']    ?? '');
                $sdt      = trim($_POST['sdt']      ?? '');
                $dia_chi  = trim($_POST['dia_chi']  ?? '');
                $cccd     = trim($_POST['cccd']     ?? '');
                $ngay_sinh = ($_POST['ngay_sinh'] ?? '') !== '' ? $_POST['ngay_sinh'] : null;

                if (!$ho_ten) {
                    $error = 'Họ tên không được để trống!';
                } elseif ($sdt === '') {
                    $error = 'Số điện thoại là bắt buộc!';
                } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $sdt)) {
                    $error = 'Số điện thoại không hợp lệ!';
                } elseif ($email === '') {
                    $error = 'Email là bắt buộc!';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Email không hợp lệ!';
                } elseif ($cccd === '') {
                    $error = 'Số CCCD/CMND là bắt buộc!';
                } else {
                    // Upload ảnh CCCD
                    $uploadDir = 'public/uploads/cccd/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $ntRow2 = $db->prepare("SELECT cccd_truoc,cccd_sau FROM nguoi_thue WHERE account_id=?");
                    $ntRow2->execute([$account_id]);
                    $ntCur     = $ntRow2->fetch();
                    $cccdTruoc = $ntCur['cccd_truoc'] ?? '';
                    $cccdSau   = $ntCur['cccd_sau']   ?? '';
                    foreach (['cccd_truoc','cccd_sau'] as $field) {
                        if (!empty($_FILES[$field]['tmp_name'])) {
                            $ext  = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                                $error = 'Ảnh CCCD chỉ chấp nhận JPG, PNG hoặc WEBP!';
                                break;
                            }
                            if (($_FILES[$field]['size'] ?? 0) > 5 * 1024 * 1024) {
                                $error = 'Mỗi ảnh CCCD tối đa 5MB!';
                                break;
                            }
                            $fname = $field . '_' . $account_id . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $fname)) {
                                if ($field === 'cccd_truoc') {
                                    $cccdTruoc = $uploadDir . $fname;
                                } else {
                                    $cccdSau = $uploadDir . $fname;
                                }
                            }
                        }
                    }

                    if ($error === '' && ($cccdTruoc === '' || $cccdSau === '')) {
                        $error = 'Vui lòng tải lên ảnh CCCD/CMND cả mặt trước và mặt sau!';
                    }

                    if ($error === '') {
                        $db->prepare("UPDATE account SET ho_ten=?,email=?,sdt=? WHERE id=?")
                           ->execute([$ho_ten, $email, $sdt, $account_id]);
                        $db->prepare("UPDATE nguoi_thue SET ho_ten=?,sdt=?,dia_chi=?,cccd=?,cccd_truoc=?,cccd_sau=?,ngay_sinh=? WHERE account_id=?")
                           ->execute([$ho_ten, $sdt, $dia_chi, $cccd, $cccdTruoc, $cccdSau, $ngay_sinh, $account_id]);
                        $_SESSION['ho_ten'] = $ho_ten;
                        $success = 'Cập nhật thông tin thành công!';
                    }
                }
            } elseif ($action === 'change_password') {
                $old = $_POST['old_password'] ?? '';
                $new = $_POST['new_password'] ?? '';
                $cfm = $_POST['confirm_password'] ?? '';
                $row = $db->prepare("SELECT password FROM account WHERE id=?");
                $row->execute([$account_id]);
                $hash = $row->fetchColumn();
                if (!password_verify($old, $hash)) {
                    $error = 'Mật khẩu hiện tại không đúng!';
                } elseif (strlen($new) < 8) {
                    $error = 'Mật khẩu mới tối thiểu 8 ký tự!';
                } elseif ($new !== $cfm) {
                    $error = 'Xác nhận mật khẩu không khớp!';
                } else {
                    $db->prepare("UPDATE account SET password=? WHERE id=?")
                       ->execute([password_hash($new, PASSWORD_DEFAULT), $account_id]);
                    $success = 'Đổi mật khẩu thành công!';
                }
            }
        }

        // Load thông tin
        $accRow = $db->prepare("SELECT * FROM account WHERE id=?");
        $accRow->execute([$account_id]);
        $account = $accRow->fetch();

        $ntRow = $db->prepare("SELECT * FROM nguoi_thue WHERE account_id=?");
        $ntRow->execute([$account_id]);
        $nguoi_thue = $ntRow->fetch();

        // Xe của người dùng
        $xeList = [];
        if ($nguoi_thue) {
            $xeStmt = $db->prepare("
                SELECT x.*, hd.trang_thai AS hd_trang_thai, p.so_phong, kt.ten_khu
                FROM xe x
                JOIN hop_dong hd ON hd.id = x.hop_dong_id
                LEFT JOIN phong p  ON p.id = hd.phong_id
                LEFT JOIN khu_tro kt ON kt.id = p.khu_id
                WHERE hd.nguoi_thue_id = ?
                   OR x.ghi_chu LIKE ?
                ORDER BY hd.trang_thai='hieu_luc' DESC, x.id DESC
            ");
            $xeStmt->execute([$nguoi_thue['id'], '%' . $nguoi_thue['ho_ten'] . '%']);
            $xeList = $xeStmt->fetchAll();
        }

        $title = 'Hồ sơ của tôi';
        require 'frontend/views/User/profile.php';
    }

    public function chiTietPhong() {
        $id = (int)($_GET['id'] ?? 0);
        $phongModel = new PhongModel();
        $phong = $phongModel->getById($id);
        if (!$phong) {
            header('Location: index.php?controller=user&action=phong');
            exit;
        }
        $title = 'Chi tiết phòng ' . $phong['so_phong'];
        require 'frontend/views/User/chi_tiet_phong.php';
    }
}
