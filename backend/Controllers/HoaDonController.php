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
        $trangThaiGui = $list ? (new HoaDonGuiModel())->moiNhat(array_column($list, 'id')) : [];
        $nguoiNhan    = $list ? HoaDonThongBao::nguoiNhanTheoPhong(array_column($list, 'phong_id')) : [];
        $title = 'Quản lý hóa đơn';
        require 'frontend/views/HoaDon/index.php';
    }

    public function create() {
        $title  = 'Tạo hóa đơn';
        $error  = '';
        $khus   = $this->khuModel->getAll();
        $phongs = $this->phongModel->getCoHopDong();   // chỉ phòng đang có hợp đồng hiệu lực mới lập được hóa đơn
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
                $anh_dien = trim($_POST['anh_cong_to_dien'] ?? '');
                $anh_nuoc = trim($_POST['anh_cong_to_nuoc'] ?? '');
                $bcs_id   = (int)($_POST['bao_chi_so_id'] ?? 0);

                $created = $this->model->create([
                    'phong_id'         => $phong_id, 'thang' => $thang, 'nam' => $nam,
                    'chi_so_dien_cu'   => $dc, 'chi_so_dien_moi' => $dm,
                    'anh_cong_to_dien' => $anh_dien ?: null,
                    'chi_so_nuoc_cu'   => $nc, 'chi_so_nuoc_moi' => $nm,
                    'anh_cong_to_nuoc' => $anh_nuoc ?: null,
                    'tien_phong'       => (float)$p['gia'],
                    'tien_dien'        => $td, 'tien_nuoc' => $tn,
                    'phi_dich_vu'      => $phi_dv,
                    'phi_xe'           => $phi_xe,
                    'so_xe'            => $so_xe,
                    'tong_tien'        => $tong_tien,
                ]);
                if ($created) {
                    if ($bcs_id) {
                        (new BaoChiSoModel())->updateStatus($bcs_id, 'da_duyet');
                    }
                    $hoaDonId = $this->model->getLastInsertId();
                    // Hóa đơn đã lưu rồi: lỗi tạo thông báo chỉ ghi nhật ký, không được làm trang báo lỗi 500
                    try {
                        $this->taoThongBaoDongTien($phong_id, $p['so_phong'] ?? '', $thang, $nam, $tong_tien, $hoaDonId);
                    } catch (\Throwable $e) {
                        error_log('Tao thong bao hoa don loi: ' . $e->getMessage());
                    }
                    // Tự gửi hóa đơn qua Gmail / Zalo. Gửi lỗi chỉ ghi nhật ký, không làm hỏng việc tạo hóa đơn.
                    try {
                        HoaDonThongBao::guiHoaDon($hoaDonId, true);
                    } catch (\Throwable $e) {
                        error_log('Gui hoa don tu dong loi: ' . $e->getMessage());
                    }
                }
                // Quản lý: tạo xong chỉ báo thành công rồi về danh sách hóa đơn của tháng đó.
                // Người thuê nhận thông báo ở chuông, bấm vào là thấy chi tiết hóa đơn kèm phương thức thanh toán.
                header('Location: index.php?controller=hoadon&action=index&thang=' . (int)$thang . '&nam=' . (int)$nam
                       . '&msg=' . ($created ? 'hoadon_created' : 'created'));
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

    /** Tài khoản của những người đang thuê phòng (hợp đồng hiệu lực): người nhận thông báo hóa đơn / thanh toán. */
    private function taiKhoanNguoiThuePhong(int $phong_id): array {
        $s = Database::getInstance()->prepare(
            "SELECT DISTINCT nt.account_id
             FROM nguoi_thue nt
             JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id
             WHERE hd.phong_id = ?
               AND hd.trang_thai = 'hieu_luc'
               AND nt.account_id IS NOT NULL"
        );
        $s->execute([$phong_id]);
        return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Tài khoản quản lý / chủ trọ: người nhận thông báo yêu cầu xác nhận thanh toán. */
    private function taiKhoanQuanTri(): array {
        $s = Database::getInstance()->query("SELECT id FROM account WHERE vai_tro IN ('quan_ly','chu_tro')");
        return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Thông báo riêng cho người đang thuê phòng về kết quả xử lý thanh toán. Lỗi chỉ ghi nhật ký, không làm hỏng thao tác chính. */
    private function thongBaoChoNguoiThue(array $hd, string $tieuDe, string $noiDung): void {
        try {
            $nguoiNhan = $this->taiKhoanNguoiThuePhong((int)$hd['phong_id']);
            if (!$nguoiNhan) return;
            (new ThongBaoModel())->createRieng(
                $tieuDe, $noiDung, 'tien_phong', $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Quản lý', $nguoiNhan, (int)$hd['id']
            );
        } catch (\Throwable $e) {
            error_log('Thong bao ket qua thanh toan loi: ' . $e->getMessage());
        }
    }

    /**
     * Lưu ảnh chứng từ người thuê tải lên (ảnh chụp lịch sử giao dịch).
     * @return array{0:?string,1:?string} [đường dẫn ảnh hoặc null nếu không gửi ảnh, thông báo lỗi hoặc null]
     */
    private function luuAnhChungTu(): array {
        $f = $_FILES['anh_chung_tu'] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Không tải được ảnh lên (mã lỗi ' . (int)$f['error'] . '). Ảnh tối đa 5MB.'];
        }
        if (($f['size'] ?? 0) > 5 * 1024 * 1024) {
            return [null, 'Ảnh chứng từ tối đa 5MB.'];
        }
        // Kiểm tra nội dung thật là ảnh, không tin đuôi file do người dùng đặt
        $info = @getimagesize($f['tmp_name']);
        $cho  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($cho[$info[2]])) {
            return [null, 'Ảnh chứng từ chỉ chấp nhận JPG, PNG hoặc WEBP.'];
        }
        $dir = 'public/uploads/chung_tu/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [null, 'Không tạo được thư mục lưu ảnh.'];
        }
        $duongDan = $dir . 'ct_' . bin2hex(random_bytes(12)) . '.' . $cho[$info[2]];   // tên ngẫu nhiên, không đoán được
        if (!move_uploaded_file($f['tmp_name'], $duongDan)) {
            return [null, 'Không lưu được ảnh, vui lòng thử lại.'];
        }
        return [$duongDan, null];
    }

    private function taoThongBaoDongTien(int $phong_id, string $so_phong, int $thang, int $nam, float $tong_tien, int $hoa_don_id): void {
        $nguoiNhan = $this->taiKhoanNguoiThuePhong($phong_id);
        if (empty($nguoiNhan)) return;

        $tieuDe = "Hóa đơn phòng $so_phong tháng $thang/$nam";
        $noiDung = "Hóa đơn phòng $so_phong tháng $thang/$nam đã được tạo.\n"
                 . "Tổng tiền cần thanh toán: " . number_format($tong_tien, 0, ',', '.') . "đ\n"
                 . "Vui lòng thanh toán trong hạn 01-05 hàng tháng.\n\n"
                 . "Bấm vào thông báo để xem chi tiết hóa đơn và chọn phương thức thanh toán.";

        // Thông báo RIÊNG: chỉ những người đang thuê phòng này thấy (lưu người nhận, không còn đánh dấu đã đọc cho người khác)
        (new ThongBaoModel())->createRieng(
            $tieuDe, $noiDung, 'tien_phong', $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Admin', $nguoiNhan, $hoa_don_id
        );
    }

    // AJAX: lấy danh sách phòng theo khu
    public function getPhongByKhu() {
        $khu_id = (int)($_GET['khu_id'] ?? 0);
        $phongs = $this->phongModel->getCoHopDong($khu_id ?: null);
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

            try {
                HoaDonThongBao::guiBienNhan($id, true);
            } catch (\Throwable $e) {
                error_log('Gui bien nhan loi: ' . $e->getMessage());
            }

            // Báo cho người đang thuê phòng ngay trong app: đã thanh toán thành công
            $this->thongBaoChoNguoiThue(
                $hd,
                'Thanh toán thành công – Phòng ' . $hd['so_phong'] . ' tháng ' . $hd['thang'] . '/' . $hd['nam'],
                'Quản lý đã xác nhận nhận ' . number_format((float)$hd['tong_tien'], 0, ',', '.') . 'đ cho hóa đơn phòng '
                . $hd['so_phong'] . ' tháng ' . $hd['thang'] . '/' . $hd['nam'] . ".\nCảm ơn bạn đã thanh toán!"
            );

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

        Csrf::check();

        $id = (int)($_GET['id'] ?? 0);
        $hd = $this->model->getById($id);
        if (!$hd || $hd['trang_thai'] !== 'chua_tt') {
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        // Chỉ được báo thanh toán hóa đơn của chính mình (trong thời gian hợp đồng của mình), không phải của người thuê trước
        if (!$this->model->thuocTaiKhoan($id, (int)$_SESSION['user_id'])) {
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

        // Ảnh chụp lịch sử giao dịch: bắt buộc khi chuyển khoản / ví điện tử, tiền mặt thì không cần
        [$anh, $loiAnh] = $this->luuAnhChungTu();
        if ($loiAnh === null && $anh === null && $phuong_thuc !== 'tien_mat') {
            $loiAnh = 'Vui lòng đính kèm ảnh chụp lịch sử giao dịch để quản lý xác nhận.';
        }
        if ($loiAnh !== null) {
            header('Location: index.php?controller=user&action=index&tt_err=' . urlencode($loiAnh));
            exit;
        }

        $nguoiTra = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? '';
        $ghiChu = 'Nguoi thue gui xac nhan da thanh toan thang ' . $hd['thang'] . '/' . $hd['nam'];
        $lsttModel->create([
            'hoa_don_id'   => $id,
            'phong_id'     => $hd['phong_id'],
            'so_tien'      => $hd['tong_tien'],
            'phuong_thuc'  => $phuong_thuc,
            'trang_thai'   => 'dang_xu_ly',
            'nguoi_thu'    => 'Cho quan ly xac nhan',
            'nguoi_tra'    => $nguoiTra,
            'ghi_chu'      => $ghiChu,
            'anh_chung_tu' => $anh,
        ]);

        // Báo cho quản lý trong mục Thông báo: bấm vào để mở hóa đơn, xem ảnh và xác nhận
        try {
            $ptTen = ['tien_mat' => 'tiền mặt', 'chuyen_khoan' => 'chuyển khoản', 'momo' => 'MoMo', 'vnpay' => 'VNPay'][$phuong_thuc];
            (new ThongBaoModel())->createRieng(
                'Yêu cầu xác nhận thanh toán – Phòng ' . $hd['so_phong'] . ' tháng ' . $hd['thang'] . '/' . $hd['nam'],
                ($nguoiTra !== '' ? $nguoiTra : 'Người thuê') . ' báo đã thanh toán ' . number_format((float)$hd['tong_tien'], 0, ',', '.') . 'đ bằng ' . $ptTen
                    . ($anh ? ' và gửi kèm ảnh giao dịch' : '') . ".\n"
                    . 'Vui lòng kiểm tra tiền đã về tài khoản rồi bấm "Xác nhận đã thu tiền" trong chi tiết hóa đơn.',
                'tien_phong', $nguoiTra, $this->taiKhoanQuanTri(), $id
            );
        } catch (\Throwable $e) {
            error_log('Thong bao yeu cau thanh toan loi: ' . $e->getMessage());
        }

        header('Location: index.php?controller=user&action=index&tt_ok=1');
        exit;
    }

    /** Quản lý từ chối yêu cầu xác nhận (chưa nhận được tiền hoặc ảnh không hợp lệ): người thuê được báo kèm lý do và gửi lại được. */
    public function tuChoiThanhToan() {
        if (!in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hoadon&action=index');
            exit;
        }
        Csrf::check();

        $id   = (int)($_POST['id'] ?? 0);
        $lyDo = trim($_POST['ly_do'] ?? '');
        $hd   = $id ? $this->model->getById($id) : false;
        $back = 'index.php?controller=hoadon&action=chiTiet&id=' . $id;
        if (!$hd || $hd['trang_thai'] !== 'chua_tt' || empty($hd['pending_count'])) {
            header('Location: ' . $back);
            exit;
        }

        $nguoiXuLy = $_SESSION['user'] ?? 'Admin';
        (new LichSuThanhToanModel())->markRejected($id, $nguoiXuLy, $lyDo);

        $this->thongBaoChoNguoiThue(
            $hd,
            'Chưa xác nhận được thanh toán – Phòng ' . $hd['so_phong'] . ' tháng ' . $hd['thang'] . '/' . $hd['nam'],
            "Quản lý chưa nhận được khoản thanh toán của hóa đơn này.\n"
            . 'Lý do: ' . ($lyDo !== '' ? $lyDo : 'chưa thấy tiền về tài khoản') . ".\n"
            . 'Bạn kiểm tra lại giao dịch rồi gửi lại yêu cầu xác nhận kèm ảnh chụp rõ hơn.'
        );

        header('Location: index.php?controller=hoadon&action=index&thang=' . (int)$hd['thang'] . '&nam=' . (int)$hd['nam']
               . '&gui=' . urlencode('Đã từ chối yêu cầu và báo cho người thuê.'));
        exit;
    }

    // Chủ trọ bấm "Gửi": gửi lại hóa đơn (hoặc biên nhận nếu đã thanh toán) qua các kênh đang bật
    public function guiLai() {
        if (!in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hoadon&action=index');
            exit;
        }
        Csrf::check();

        $id    = (int)($_POST['id'] ?? 0);
        $thang = (int)($_POST['thang'] ?? date('m'));
        $nam   = (int)($_POST['nam'] ?? date('Y'));
        $back  = "index.php?controller=hoadon&action=index&thang=$thang&nam=$nam";

        $hd = $id ? $this->model->getById($id) : false;
        if (!$hd) {
            header('Location: ' . $back . '&err=' . urlencode('Không tìm thấy hóa đơn.'));
            exit;
        }
        try {
            $ket = $hd['trang_thai'] === 'da_tt' ? HoaDonThongBao::guiBienNhan($id, false) : HoaDonThongBao::guiHoaDon($id, false);
            [$ok, $noiDung] = HoaDonThongBao::tomTat($ket);
        } catch (\Throwable $e) {
            error_log('Gui lai hoa don loi: ' . $e->getMessage());
            [$ok, $noiDung] = [false, 'Có lỗi khi gửi, xem log máy chủ.'];
        }
        header('Location: ' . $back . ($ok ? '&gui=' : '&err=') . urlencode($noiDung));
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

        // User chỉ xem hóa đơn của chính mình (cùng phòng VÀ trong thời gian hợp đồng của mình)
        if (($_SESSION['vai_tro'] ?? '') === 'user'
            && !$this->model->thuocTaiKhoan((int)$hd['id'], (int)$_SESSION['user_id'])) {
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        // Lấy thông tin phòng
        $phong = $this->phongModel->getById((int)$hd['phong_id']);
        $donGia = $this->donGiaModel->getCurrent();

        // Yêu cầu xác nhận thanh toán đang chờ (kèm ảnh chứng từ), nếu có
        $yeuCauCho = !empty($hd['pending_count']) ? (new LichSuThanhToanModel())->getPendingByInvoice((int)$hd['id']) : false;

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
        $laAdmin   = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true);
        $loc = [];
        $khus = $phongs = [];

        if (!$laAdmin) {
            // Người thuê chỉ xem giao dịch của hóa đơn thuộc thời gian hợp đồng của chính mình
            $list = $lsttModel->getByTaiKhoan((int)$_SESSION['user_id']);
        } else {
            // Quản trị xem tất cả, có thể lọc theo khu, phòng, trạng thái, kỳ hóa đơn
            $loc = [
                'khu_id'     => (int)($_GET['khu_id'] ?? 0),
                'phong_id'   => (int)($_GET['phong_id'] ?? 0),
                'trang_thai' => in_array($_GET['trang_thai'] ?? '', ['thanh_cong', 'dang_xu_ly', 'that_bai'], true) ? $_GET['trang_thai'] : '',
                'thang'      => (int)($_GET['thang'] ?? 0),
                'nam'        => (int)($_GET['nam'] ?? 0),
                'chi_hieu_luc' => !LocHopDong::xemLichSu(),   // mặc định: chỉ hợp đồng đang hiệu lực
            ];
            if ($loc['thang'] < 1 || $loc['thang'] > 12 || $loc['nam'] < 2000) {
                $loc['thang'] = $loc['nam'] = 0;
            }
            $list   = $lsttModel->getFiltered($loc, 500);
            $khus   = $this->khuModel->getAll();
            $phongs = $this->phongModel->getAll($loc['khu_id'] ?: null);
        }

        $tongThu = LichSuThanhToanModel::tongThanhCong($list);
        $title = 'Lịch sử thanh toán';
        require 'frontend/views/HoaDon/lich_su.php';
    }

    /** Chi tiết một giao dịch. Quản trị xem mọi giao dịch; người thuê chỉ xem giao dịch của phòng mình. */
    public function chiTietGiaoDich() {
        $id = (int)($_GET['id'] ?? 0);
        $gd = $id ? (new LichSuThanhToanModel())->getDetail($id) : false;
        if (!$gd) {
            header('Location: index.php?controller=hoadon&action=lichSu');
            exit;
        }
        $laAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true);
        if (!$laAdmin && !$this->model->thuocTaiKhoan((int)$gd['hoa_don_id'], (int)($_SESSION['user_id'] ?? 0))) {
            header('Location: index.php?controller=hoadon&action=lichSu');
            exit;
        }
        $title = 'Chi tiết giao dịch #' . $gd['id'];
        require 'frontend/views/HoaDon/chi_tiet_giao_dich.php';
    }

    /**
     * AJAX Lấy báo chỉ số của người thuê cho phòng/tháng/năm
     */
    public function getBaoChiSo() {
        header('Content-Type: application/json');
        $phong_id = (int)($_GET['phong_id'] ?? 0);
        $thang    = (int)($_GET['thang'] ?? date('m'));
        $nam      = (int)($_GET['nam'] ?? date('Y'));

        $bcs = (new BaoChiSoModel())->getByPhongThangNam($phong_id, $thang, $nam);
        if ($bcs) {
            echo json_encode(['found' => true, 'data' => $bcs]);
        } else {
            echo json_encode(['found' => false]);
        }
        exit;
    }

    /**
     * Danh sách báo chỉ số điện nước từ người thuê
     */
    public function baoChiSoList() {
        $model = new BaoChiSoModel();
        $list = $model->getAllPending();
        $title = 'Duyệt chỉ số điện nước (AI)';
        require 'frontend/views/HoaDon/bao_chi_so_list.php';
    }

    /**
     * Từ chối báo chỉ số
     */
    public function tuChoiBaoChiSo() {
        Csrf::check();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            (new BaoChiSoModel())->updateStatus($id, 'tu_choi');
        }
        header('Location: index.php?controller=hoadon&action=baoChiSoList&msg=rejected');
        exit;
    }
}

