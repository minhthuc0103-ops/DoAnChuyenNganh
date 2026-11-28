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
        $tbModel        = new ThongBaoModel();
        $thong_bao_list = $tbModel->getChuaDoc($account_id, 5);
        $so_chua_doc    = $tbModel->countChuaDoc($account_id);   // tổng thật, không phải số dòng của danh sách 5 tin

        if ($nguoi_thue && !empty($nguoi_thue['hop_dong_id'])) {
            // Chỉ hóa đơn trong thời gian hợp đồng của chính người này (không lẫn hóa đơn của người thuê trước cùng phòng)
            $hoa_don_list = $this->hoaDonModel->getByTaiKhoan($account_id);
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

    /** Id các phòng tài khoản này đang thuê (hợp đồng hiệu lực). */
    private function phongCuaToi(int $account_id): array {
        $s = \Database::getInstance()->prepare(
            "SELECT DISTINCT hd.phong_id FROM hop_dong hd
             JOIN nguoi_thue nt ON nt.id = hd.nguoi_thue_id
             WHERE nt.account_id = ? AND hd.trang_thai = 'hieu_luc'"
        );
        $s->execute([$account_id]);
        return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Người dùng chỉ xem chi tiết phòng CÒN TRỐNG hoặc phòng CỦA CHÍNH MÌNH; phòng người khác đang thuê thì không. */
    private static function xemDuocPhong(array $p, array $phongCuaToi): bool {
        return ($p['trang_thai'] ?? '') === 'trong' || in_array((int)$p['id'], $phongCuaToi, true);
    }

    /** Lọc danh sách phòng theo từ khóa (số phòng hoặc tên khu), không phân biệt hoa thường. */
    private static function locTheoTuKhoa(array $phongs, string $kw): array {
        $kw = mb_strtolower(trim($kw), 'UTF-8');
        if ($kw === '') return $phongs;
        return array_values(array_filter($phongs, fn($p) =>
            mb_strpos(mb_strtolower((string)$p['so_phong'], 'UTF-8'), $kw) !== false
            || mb_strpos(mb_strtolower((string)($p['ten_khu'] ?? ''), 'UTF-8'), $kw) !== false
        ));
    }

    public function phong() {
        $khu_id   = isset($_GET['khu_id']) ? (int)$_GET['khu_id'] : null;
        $filter   = $_GET['filter'] ?? 'all';
        $kw       = trim((string)($_GET['kw'] ?? ''));
        $phongModel = new PhongModel();
        $khuModel   = new KhuTroModel();
        $phongs = self::locTheoTuKhoa($phongModel->getAll($khu_id, $filter === 'all' ? null : $filter), $kw);
        $khus   = $khuModel->getAll();
        $phongCuaToi = $this->phongCuaToi((int)$_SESSION['user_id']);
        $title  = 'Danh sách phòng';
        require 'frontend/views/User/phong.php';
    }

    /** Ô "Tìm phòng..." trên thanh trên cùng (JSON): tìm theo số phòng / tên khu. */
    public function timPhong() {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q === '') {
            echo json_encode(['ok' => true, 'results' => []]);
            exit;
        }
        $phongCuaToi = $this->phongCuaToi((int)$_SESSION['user_id']);
        $tt = ['trong' => 'Còn trống', 'dang_thue' => 'Đang thuê', 'bao_tri' => 'Bảo trì'];
        $ra = [];
        foreach (array_slice(self::locTheoTuKhoa((new PhongModel())->getAll(), $q), 0, 10) as $p) {
            $xem   = self::xemDuocPhong($p, $phongCuaToi);
            $cuaToi = in_array((int)$p['id'], $phongCuaToi, true);
            $ra[] = [
                'room'     => (string)$p['so_phong'],
                'area'     => (string)($p['ten_khu'] ?? ''),
                'price'    => (int)round((float)$p['gia']),
                'status'   => $cuaToi ? 'Phòng của bạn' : ($tt[$p['trang_thai']] ?? 'Không rõ'),
                'viewable' => $xem,
                // Chỉ trả đường dẫn chi tiết cho phòng được phép xem
                'url'      => $xem ? 'index.php?controller=user&action=chiTietPhong&id=' . (int)$p['id'] : '',
            ];
        }
        echo json_encode(['ok' => true, 'results' => $ra], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function profile() {
        $account_id = (int)$_SESSION['user_id'];
        $db = \Database::getInstance();
        $error   = '';
        $success = '';
        $tabMo   = '';   // tab cần mở lại sau khi gửi form ('xe' = Xe của tôi)

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
            } elseif ($action === 'add_xe') {
                $tabMo = 'xe';
                $loi = (new XeDangKyModel())->them(
                    $account_id,
                    (string)($_POST['bien_so'] ?? ''),
                    (string)($_POST['loai_xe'] ?? 'xe_may'),
                    (string)($_POST['mau_sac'] ?? '')
                );
                if ($loi !== null) { $error = $loi; } else { $success = 'Đã đăng ký xe. Khi tạo hợp đồng, thông tin xe sẽ được điền sẵn.'; }
            } elseif ($action === 'delete_xe') {
                $tabMo = 'xe';
                if ((new XeDangKyModel())->xoa((int)($_POST['xe_id'] ?? 0), $account_id)) { $success = 'Đã xóa xe khỏi danh sách đăng ký.'; }
                else { $error = 'Không tìm thấy xe cần xóa.'; }
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
        // Xe tự đăng ký trong hồ sơ (chưa cần hợp đồng) + biển số nào đã nằm trong hợp đồng
        $xeDangKy   = (new XeDangKyModel())->getByAccount($account_id);
        $bienSoTrongHD = array_map(fn($x) => strtoupper(trim((string)$x['bien_so'])), array_filter($xeList, fn($x) => ($x['hd_trang_thai'] ?? '') === 'hieu_luc'));

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
        // Phòng người khác đang thuê (hoặc đang bảo trì) thì không cho xem chi tiết, kể cả khi gõ thẳng id lên thanh địa chỉ
        if (!self::xemDuocPhong($phong, $this->phongCuaToi((int)$_SESSION['user_id']))) {
            $lyDo = ($phong['trang_thai'] ?? '') === 'bao_tri' ? 'đang bảo trì' : 'đang có người thuê';
            header('Location: index.php?controller=user&action=phong&chan=' . urlencode('Phòng ' . $phong['so_phong'] . ' ' . $lyDo . ' nên không xem được chi tiết.'));
            exit;
        }
        $title = 'Chi tiết phòng ' . $phong['so_phong'];
        require 'frontend/views/User/chi_tiet_phong.php';
    }

    public function luuTru() {
        $account_id = (int)($_SESSION['user_id'] ?? 0);
        $nguoi_thue = $this->nguoiThueModel->getByAccountId($account_id);
        $luuTruModel = new LuuTruModel();
        $list = $luuTruModel->getByAccountId($account_id);
        $title = 'Thông tin khai báo lưu trú của tôi';
        require 'frontend/views/User/luu_tru.php';
    }

    public function baoChiSo() {
        $account_id = (int)($_SESSION['user_id'] ?? 0);
        $nguoi_thue = $this->nguoiThueModel->getByAccountId($account_id);
        $baoChiSoModel = new BaoChiSoModel();
        $title = 'Báo chỉ số điện nước (AI)';
        $error = '';
        $success = '';

        if (!$nguoi_thue || empty($nguoi_thue['phong_id'])) {
            $phong = null;
            $history = [];
            require 'frontend/views/User/bao_chi_so.php';
            return;
        }

        $phong_id = (int)$nguoi_thue['phong_id'];
        $phong = (new PhongModel())->getById($phong_id);
        $lastHd = (new HoaDonModel())->getChiSoCuoiCung($phong_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $thang = (int)($_POST['thang'] ?? date('m'));
            $nam   = (int)($_POST['nam']   ?? date('Y'));
            $csDien = (float)($_POST['chi_so_dien'] ?? 0);
            $csNuoc = (float)($_POST['chi_so_nuoc'] ?? 0);
            $anhDien = trim($_POST['anh_cong_to_dien'] ?? '');
            $anhNuoc = trim($_POST['anh_cong_to_nuoc'] ?? '');
            $ghiChu  = trim($_POST['ghi_chu'] ?? '');

            if ($csDien <= 0 && $csNuoc <= 0) {
                $error = 'Vui lòng nhập hoặc chụp ảnh chỉ số điện, nước!';
            } else {
                $baoChiSoModel->create([
                    'phong_id'         => $phong_id,
                    'nguoi_thue_id'    => $nguoi_thue['id'],
                    'thang'            => $thang,
                    'nam'              => $nam,
                    'chi_so_dien'      => $csDien,
                    'chi_so_nuoc'      => $csNuoc,
                    'anh_cong_to_dien' => $anhDien ?: null,
                    'anh_cong_to_nuoc' => $anhNuoc ?: null,
                    'ghi_chu'          => $ghiChu,
                    'trang_thai'       => 'cho_duyet'
                ]);
                $success = 'Đã gửi chỉ số điện nước thành công! Chủ trọ sẽ duyệt và lập hóa đơn.';
            }
        }

        $history = $baoChiSoModel->getByNguoiThue($nguoi_thue['id']);
        require 'frontend/views/User/bao_chi_so.php';
    }
}
