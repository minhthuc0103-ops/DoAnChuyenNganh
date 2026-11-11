<?php

class BanGiaoController
{
    private BanGiaoModel $model;

    public function __construct()
    {
        $this->model = new BanGiaoModel();
    }

    private function isAdmin(): bool
    {
        return in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true);
    }

    private function uploadFile(array $file, string $folder = 'ban_giao', string $prefix = 'bg_'): ?string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        $dir = 'public/uploads/' . trim($folder, '/') . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $name = $prefix . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $dir . $name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $target;
        }
        return null;
    }

    public function index(): void
    {
        $tab = $_GET['tab'] ?? 'tra_phong';
        $title = 'Bàn giao & Trả phòng';
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($this->isAdmin()) {
            $bienBanList = $this->model->getAllBienBan();
            $traPhongList = $this->model->getAllYeuCauTraPhong();
            $soChoKiemTra = $this->model->countPendingTraPhong();
            $activeRental = null;
            $donGia = $this->model->getDonGia();
            require 'frontend/views/BanGiao/admin_index.php';
        } else {
            $activeRental = $this->model->getActiveRentalByAccount($userId);
            $bienBanList = $this->model->getBienBanByUser($userId);
            $traPhongList = $this->model->getYeuCauTraPhongByUser($userId);
            $soChoKiemTra = $this->model->countPendingTraPhongByUser($userId);
            require 'frontend/views/BanGiao/user_index.php';
        }
    }

    // Admin lập biên bản bàn giao nhận phòng / trả phòng
    public function taoBienBan(): void
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        $error = '';
        $hopDongList = $this->model->getHopDongDangHieuLuc();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $hopDongId = (int)($_POST['hop_dong_id'] ?? 0);
            $loai = $_POST['loai'] ?? 'nhan_phong';
            $ngayLap = $_POST['ngay_lap'] ?? date('Y-m-d');
            $chiSoDien = (int)($_POST['chi_so_dien'] ?? 0);
            $chiSoNuoc = (int)($_POST['chi_so_nuoc'] ?? 0);
            $ghiChu = trim($_POST['ghi_chu'] ?? '');

            // Tìm thông tin hợp đồng được chọn
            $selectedHd = null;
            foreach ($hopDongList as $hd) {
                if ((int)$hd['hop_dong_id'] === $hopDongId) {
                    $selectedHd = $hd;
                    break;
                }
            }

            if (!$selectedHd) {
                $error = 'Vui lòng chọn hợp đồng thuê hợp lệ.';
            } else {
                // Thu thập danh mục tài sản bàn giao
                $taiSan = [];
                $tsNames = $_POST['tai_san_ten'] ?? [];
                $tsSoLuong = $_POST['tai_san_sl'] ?? [];
                $tsTinhTrang = $_POST['tai_san_tinh_trang'] ?? [];

                foreach ($tsNames as $i => $name) {
                    $name = trim($name);
                    if ($name !== '') {
                        $taiSan[] = [
                            'ten'        => $name,
                            'so_luong'   => (int)($tsSoLuong[$i] ?? 1),
                            'tinh_trang' => trim($tsTinhTrang[$i] ?? 'Hoạt động tốt')
                        ];
                    }
                }

                $anhBanGiao = null;
                if (!empty($_FILES['anh_ban_giao'])) {
                    $anhBanGiao = $this->uploadFile($_FILES['anh_ban_giao'], 'ban_giao', 'bg_');
                }

                $bbId = $this->model->createBienBan([
                    'hop_dong_id'       => $selectedHd['hop_dong_id'],
                    'phong_id'          => $selectedHd['phong_id'],
                    'nguoi_thue_id'     => $selectedHd['nguoi_thue_id'],
                    'loai'              => $loai,
                    'ngay_lap'          => $ngayLap,
                    'nguoi_lap_id'      => (int)($_SESSION['user_id'] ?? 0),
                    'chi_so_dien'       => $chiSoDien,
                    'chi_so_nuoc'       => $chiSoNuoc,
                    'danh_sach_tai_san' => json_encode($taiSan, JSON_UNESCAPED_UNICODE),
                    'anh_ban_giao'      => $anhBanGiao,
                    'ghi_chu'           => $ghiChu
                ]);

                header('Location: index.php?controller=bangiao&action=xemBienBan&id=' . $bbId . '&msg=created');
                exit;
            }
        }

        $title = 'Lập biên bản bàn giao';
        require 'frontend/views/BanGiao/tao_bien_ban.php';
    }

    // Xem chi tiết biên bản bàn giao
    public function xemBienBan(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $bb = $this->model->getBienBanById($id);

        if (!$bb) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        // Kiểm tra quyền nếu là user
        if (!$this->isAdmin()) {
            $userRental = $this->model->getActiveRentalByAccount((int)($_SESSION['user_id'] ?? 0));
            // Cho phép xem nếu là người thuê trong biên bản
            $db = Database::getInstance();
            $s = $db->prepare("SELECT account_id FROM nguoi_thue WHERE id = ?");
            $s->execute([$bb['nguoi_thue_id']]);
            $ownerAccId = (int)$s->fetchColumn();

            if ($ownerAccId !== (int)($_SESSION['user_id'] ?? 0)) {
                header('Location: index.php?controller=bangiao&action=index');
                exit;
            }
        }

        $danhSachTaiSan = json_decode($bb['danh_sach_tai_san'] ?? '[]', true) ?: [];
        $title = 'Biên bản ' . ($bb['loai'] === 'tra_phong' ? 'trả phòng' : 'nhận phòng') . ' #' . $bb['ma_bien_ban'];
        require 'frontend/views/BanGiao/xem_bien_ban.php';
    }

    // In biên bản định dạng chuẩn A4
    public function inBienBan(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $bb = $this->model->getBienBanById($id);

        if (!$bb) {
            die('Biên bản không tồn tại.');
        }

        $danhSachTaiSan = json_decode($bb['danh_sach_tai_san'] ?? '[]', true) ?: [];
        require 'frontend/views/BanGiao/in_bien_ban.php';
    }

    // Xóa biên bản bàn giao (Admin)
    public function xoaBienBan(): void
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->deleteBienBan($id);
        }
        header('Location: index.php?controller=bangiao&action=index&tab=ban_giao&msg=deleted');
        exit;
    }

    // User gửi yêu cầu báo trước trả phòng
    public function guiYeuCauTraPhong(): void
    {
        if ($this->isAdmin()) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $userId = (int)($_SESSION['user_id'] ?? 0);
            $activeRental = $this->model->getActiveRentalByAccount($userId);

            if (!$activeRental) {
                header('Location: index.php?controller=bangiao&action=index&err=' . urlencode('Bạn không có hợp đồng thuê phòng nào đang hiệu lực.'));
                exit;
            }

            $ngayDuKien = trim($_POST['ngay_du_kien_tra'] ?? '');
            $lyDo       = trim($_POST['ly_do'] ?? '');
            $soTk       = trim($_POST['so_tai_khoan'] ?? '');
            $nganHang   = trim($_POST['ngan_hang'] ?? '');
            $tenChuTk   = trim($_POST['ten_chu_tk'] ?? '');

            if (!$ngayDuKien || !$lyDo) {
                header('Location: index.php?controller=bangiao&action=index&err=' . urlencode('Vui lòng chọn ngày trả phòng và lý do.'));
                exit;
            }

            $ycId = $this->model->createYeuCauTraPhong([
                'hop_dong_id'      => $activeRental['id'],
                'phong_id'         => $activeRental['phong_id'],
                'nguoi_thue_id'    => $activeRental['nguoi_thue_id'],
                'user_id'          => $userId,
                'ngay_du_kien_tra' => $ngayDuKien,
                'ly_do'            => $lyDo,
                'so_tai_khoan'     => $soTk,
                'ngan_hang'        => $nganHang,
                'ten_chu_tk'       => $tenChuTk,
                'tien_coc_goc'     => $activeRental['tien_coc'] ?? 0
            ]);

            // Báo chat cho admin
            try {
                $db = Database::getInstance();
                $adminId = (int)$db->query("SELECT id FROM account WHERE vai_tro IN ('quan_ly','chu_tro') ORDER BY id LIMIT 1")->fetchColumn();
                if ($adminId <= 0) $adminId = $userId;
                $chatModel = new ChatModel();
                $chatModel->send(
                    $adminId,
                    'Hệ thống',
                    'system',
                    "📢 Yêu cầu trả phòng mới:\nKhách: {$activeRental['ho_ten']} - Phòng {$activeRental['so_phong']}\nNgày dự kiến trả: {$ngayDuKien}\nLý do: {$lyDo}",
                    'system'
                );
            } catch (\Throwable $e) {}

            header('Location: index.php?controller=bangiao&action=index&msg=requested');
            exit;
        }

        header('Location: index.php?controller=bangiao&action=index');
        exit;
    }

    // User hủy yêu cầu trả phòng
    public function huyYeuCauTraPhong(): void
    {
        if ($this->isAdmin()) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($id > 0 && $userId > 0) {
            $this->model->huyYeuCauTraPhong($id, $userId);
        }

        header('Location: index.php?controller=bangiao&action=index&msg=cancelled');
        exit;
    }

    // Admin kiểm tra phòng và quyết toán cọc
    public function kiemTraTraPhong(): void
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $yc = $this->model->getYeuCauTraPhongById($id);

        if (!$yc) {
            header('Location: index.php?controller=bangiao&action=index');
            exit;
        }

        $donGia = $this->model->getDonGia();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $chiSoDienDau   = (int)($_POST['chi_so_dien_dau'] ?? 0);
            $chiSoDienCuoi  = (int)($_POST['chi_so_dien_cuoi'] ?? 0);
            $donGiaDien     = (float)($_POST['don_gia_dien'] ?? $donGia['gia_dien']);
            $soDien         = max(0, $chiSoDienCuoi - $chiSoDienDau);
            $tienDien       = $soDien * $donGiaDien;

            $chiSoNuocDau   = (int)($_POST['chi_so_nuoc_dau'] ?? 0);
            $chiSoNuocCuoi  = (int)($_POST['chi_so_nuoc_cuoi'] ?? 0);
            $donGiaNuoc     = (float)($_POST['don_gia_nuoc'] ?? $donGia['gia_nuoc']);
            $soNuoc         = max(0, $chiSoNuocCuoi - $chiSoNuocDau);
            $tienNuoc       = $soNuoc * $donGiaNuoc;

            $tienCocGoc     = (float)($_POST['tien_coc_goc'] ?? $yc['tien_coc']);
            $chiPhiHuHong   = (float)($_POST['chi_phi_hu_hong'] ?? 0);
            $chiTietHuHong  = trim($_POST['chi_tiet_hu_hong'] ?? '');
            $phiVeSinh      = (float)($_POST['phi_ve_sinh'] ?? 0);
            $chiPhiKhac     = (float)($_POST['chi_phi_khac'] ?? 0);
            $ghiChuKhac     = trim($_POST['ghi_chu_khac'] ?? '');
            $ghiChuQl       = trim($_POST['ghi_chu_ql'] ?? '');

            // Tiền hoàn lại = Tiền cọc - (Điện + Nước + Hư hỏng + Vệ sinh + Chi phí khác)
            $tongKhauTru    = $tienDien + $tienNuoc + $chiPhiHuHong + $phiVeSinh + $chiPhiKhac;
            $tienHoanLai    = max(0, $tienCocGoc - $tongKhauTru);

            // Ảnh trả phòng nếu có
            $anhTraPhong = null;
            if (!empty($_FILES['anh_tra_phong'])) {
                $anhTraPhong = $this->uploadFile($_FILES['anh_tra_phong'], 'tra_phong', 'tp_');
            }

            $actionType = $_POST['action_type'] ?? 'luu_nhap';

            if ($actionType === 'hoan_tat') {
                // Lưu quyết toán trước
                $this->model->luuQuyetToan($id, [
                    'chi_so_dien_dau'  => $chiSoDienDau,
                    'chi_so_dien_cuoi' => $chiSoDienCuoi,
                    'don_gia_dien'     => $donGiaDien,
                    'tien_dien'        => $tienDien,
                    'chi_so_nuoc_dau'  => $chiSoNuocDau,
                    'chi_so_nuoc_cuoi' => $chiSoNuocCuoi,
                    'don_gia_nuoc'     => $donGiaNuoc,
                    'tien_nuoc'        => $tienNuoc,
                    'tien_coc_goc'     => $tienCocGoc,
                    'chi_phi_hu_hong'  => $chiPhiHuHong,
                    'chi_tiet_hu_hong' => $chiTietHuHong,
                    'phi_ve_sinh'      => $phiVeSinh,
                    'chi_phi_khac'     => $chiPhiKhac,
                    'ghi_chu_khac'     => $ghiChuKhac,
                    'tien_hoan_lai'    => $tienHoanLai,
                    'anh_tra_phong'    => $anhTraPhong,
                    'ghi_chu_ql'       => $ghiChuQl,
                    'trang_thai'       => 'dang_kiem_tra'
                ]);

                // Hoàn tất thanh lý & giải phóng phòng
                $adminId = (int)($_SESSION['user_id'] ?? 0);
                $this->model->hoanTatThanhLy($id, $adminId);

                header('Location: index.php?controller=bangiao&action=inBienBanTraPhong&id=' . $id . '&msg=settled');
                exit;
            } else {
                // Lưu bản nháp đang kiểm tra
                $this->model->luuQuyetToan($id, [
                    'chi_so_dien_dau'  => $chiSoDienDau,
                    'chi_so_dien_cuoi' => $chiSoDienCuoi,
                    'don_gia_dien'     => $donGiaDien,
                    'tien_dien'        => $tienDien,
                    'chi_so_nuoc_dau'  => $chiSoNuocDau,
                    'chi_so_nuoc_cuoi' => $chiSoNuocCuoi,
                    'don_gia_nuoc'     => $donGiaNuoc,
                    'tien_nuoc'        => $tienNuoc,
                    'tien_coc_goc'     => $tienCocGoc,
                    'chi_phi_hu_hong'  => $chiPhiHuHong,
                    'chi_tiet_hu_hong' => $chiTietHuHong,
                    'phi_ve_sinh'      => $phiVeSinh,
                    'chi_phi_khac'     => $chiPhiKhac,
                    'ghi_chu_khac'     => $ghiChuKhac,
                    'tien_hoan_lai'    => $tienHoanLai,
                    'anh_tra_phong'    => $anhTraPhong,
                    'ghi_chu_ql'       => $ghiChuQl,
                    'trang_thai'       => 'dang_kiem_tra'
                ]);

                header('Location: index.php?controller=bangiao&action=kiemTraTraPhong&id=' . $id . '&msg=saved');
                exit;
            }
        }

        $title = 'Kiểm tra & Quyết toán trả phòng #' . $yc['id'];
        require 'frontend/views/BanGiao/kiem_tra_tra_phong.php';
    }

    // In phiếu quyết toán trả phòng
    public function inBienBanTraPhong(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $yc = $this->model->getYeuCauTraPhongById($id);

        if (!$yc) {
            die('Yêu cầu trả phòng không tồn tại.');
        }

        require 'frontend/views/BanGiao/in_phieu_tra_phong.php';
    }
}
