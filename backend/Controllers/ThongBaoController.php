<?php

class ThongBaoController
{
    private ThongBaoModel $model;

    public function __construct()
    {
        $this->model = new ThongBaoModel();
        $this->ensureSuaChuaTable();
    }

    private function isAdmin(): bool
    {
        return in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']);
    }

    // Tạo bảng yêu cầu sửa chữa nếu chưa có
    private function ensureSuaChuaTable(): void
    {
        $db = Database::getInstance();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `yeu_cau_sua_chua` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT NOT NULL,
                `ho_ten`      VARCHAR(100) DEFAULT '',
                `phong`       VARCHAR(50)  DEFAULT '',
                `vi_tri`      VARCHAR(200) NOT NULL,
                `danh_muc`    VARCHAR(50)  DEFAULT 'khac',
                `mo_ta`       TEXT NOT NULL,
                `anh_truoc`   VARCHAR(255) DEFAULT NULL,
                `anh_sau`     VARCHAR(255) DEFAULT NULL,
                `ai_phan_loai` TEXT DEFAULT NULL,
                `muc_do`      ENUM('nhe','trung_binh','khan_cap') DEFAULT 'trung_binh',
                `trang_thai`  ENUM('cho_xu_ly','dang_xu_ly','da_xong') DEFAULT 'cho_xu_ly',
                `ghi_chu_ql`  TEXT,
                `danh_gia_sao` TINYINT DEFAULT NULL,
                `danh_gia_nhan_xet` TEXT DEFAULT NULL,
                `danh_gia_luc` DATETIME DEFAULT NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Đảm bảo các cột mới tồn tại
        try {
            $cols = $db->query("SHOW COLUMNS FROM yeu_cau_sua_chua")->fetchAll(PDO::FETCH_COLUMN);
            $newCols = [
                'anh_truoc'         => 'VARCHAR(255) NULL',
                'anh_sau'           => 'VARCHAR(255) NULL',
                'danh_muc'          => "VARCHAR(50) DEFAULT 'khac'",
                'ai_phan_loai'      => 'TEXT NULL',
                'danh_gia_sao'      => 'TINYINT DEFAULT NULL',
                'danh_gia_nhan_xet' => 'TEXT DEFAULT NULL',
                'danh_gia_luc'      => 'DATETIME DEFAULT NULL'
            ];
            foreach ($newCols as $col => $def) {
                if (!in_array($col, $cols, true)) {
                    $db->exec("ALTER TABLE `yeu_cau_sua_chua` ADD COLUMN `$col` $def");
                }
            }
        } catch (\Throwable $e) {
            // bỏ qua nếu đã có
        }
    }

    public function index()
    {
        $userId  = (int)($_SESSION['user_id'] ?? 0);
        $db      = Database::getInstance();

        if ($this->isAdmin()) {
            // Admin: thấy TẤT CẢ yêu cầu sửa chữa của mọi phòng
            $suaChuaList = $db->query(
                "SELECT * FROM yeu_cau_sua_chua ORDER BY
                 FIELD(trang_thai,'cho_xu_ly','dang_xu_ly','da_xong'),
                 FIELD(muc_do,'khan_cap','trung_binh','nhe'),
                 created_at DESC"
            )->fetchAll();
            $soChoXuLy = (int)$db->query(
                "SELECT COUNT(*) FROM yeu_cau_sua_chua WHERE trang_thai='cho_xu_ly'"
            )->fetchColumn();

            // Tính điểm đánh giá sao trung bình
            $ratingStmt = $db->query("SELECT AVG(danh_gia_sao) as avg_sao, COUNT(danh_gia_sao) as total_danh_gia FROM yeu_cau_sua_chua WHERE danh_gia_sao IS NOT NULL");
            $ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);
            $diemTrungBinh = $ratingData ? round((float)($ratingData['avg_sao'] ?? 0), 1) : 0;
            $tongDanhGia   = (int)($ratingData['total_danh_gia'] ?? 0);

        } else {
            // User: CHỈ thấy yêu cầu của chính mình (theo user_id)
            $stmt = $db->prepare(
                "SELECT * FROM yeu_cau_sua_chua WHERE user_id=? ORDER BY created_at DESC"
            );
            $stmt->execute([$userId]);
            $suaChuaList = $stmt->fetchAll();
            $soChoXuLy   = 0;
            $diemTrungBinh = 0;
            $tongDanhGia   = 0;

            // Lấy số phòng của user để hiển thị
            $phongStmt = $db->prepare(
                "SELECT p.so_phong FROM nguoi_thue nt
                 JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai='hieu_luc'
                 JOIN phong p ON hd.phong_id = p.id
                 WHERE nt.account_id = ? LIMIT 1"
            );
            $phongStmt->execute([$userId]);
            $soPhongUser = $phongStmt->fetchColumn() ?: '';
        }

        $title = 'Bảo trì';
        require 'frontend/views/ThongBao/index.php';
    }

    private function uploadAnh(array $file, string $prefix = 'sc_'): ?string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        $uploadDir = 'public/uploads/sua_chua/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = $prefix . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $target;
        }
        return null;
    }

    // AI Phân loại sự cố & Gợi ý xử lý
    public function aiPhanLoai(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $vi_tri = trim($_POST['vi_tri'] ?? $_GET['vi_tri'] ?? '');
        $mo_ta  = trim($_POST['mo_ta'] ?? $_GET['mo_ta'] ?? '');
        $text   = mb_strtolower($vi_tri . ' ' . $mo_ta, 'UTF-8');

        if ($text === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập vị trí hoặc mô tả để AI phân loại.'
            ]);
            exit;
        }

        // 1. Phân loại danh mục
        $danh_muc = 'khac';
        $danh_muc_ten = 'Khác';

        if (preg_match('/(điện|chập|cháy|cầu dao|aptomat|ổ cắm|công tắc|bóng đèn|đèn|mất điện|hở điện|tia lửa|giật điện|cb)/u', $text)) {
            $danh_muc = 'dien';
            $danh_muc_ten = 'Hệ thống Điện & Chiếu sáng';
        } elseif (preg_match('/(nước|vòi|rò rỉ|bồn cầu|nghẹt|tắc|thoát sàn|ống nước|áp lực|xả nước|chậu rửa|lavabo|thấm dột)/u', $text)) {
            $danh_muc = 'nuoc';
            $danh_muc_ten = 'Hệ thống Nước & Vệ sinh';
        } elseif (preg_match('/(máy lạnh|điều hòa|tủ lạnh|bình nóng lạnh|nóng lạnh|quạt|bếp|máy giặt|hút mùi)/u', $text)) {
            $danh_muc = 'thiet_bi';
            $danh_muc_ten = 'Thiết bị Gia dụng & Điện lạnh';
        } elseif (preg_match('/(cửa|khóa|ổ khóa|bản lề|giường|tủ|bàn|ghế|kính|cửa sổ|tay nắm|then)/u', $text)) {
            $danh_muc = 'noi_that';
            $danh_muc_ten = 'Nội thất & Khóa cửa';
        } elseif (preg_match('/(tường|trần|nứt|sơn|ẩm mốc|bong tróc|sàn|gạch)/u', $text)) {
            $danh_muc = 'tuong_tran';
            $danh_muc_ten = 'Tường, Trần & Kết cấu';
        }

        // 2. Phân loại mức độ khẩn cấp
        $muc_do = 'trung_binh';
        $muc_do_ten = 'Trung bình';
        if (preg_match('/(cháy|chập|tia lửa|mùi khét|khét|giật|vỡ ống|ngập|phun nước|sập|kẹt khóa|nguy hiểm|khẩn cấp)/u', $text)) {
            $muc_do = 'khan_cap';
            $muc_do_ten = 'Khẩn cấp';
        } elseif (preg_match('/(hơi|nhẹ|kêu nhỏ|trầy|xước|lỏng ốc|bụi|thay pin|chờ được)/u', $text)) {
            $muc_do = 'nhe';
            $muc_do_ten = 'Nhẹ';
        }

        // 3. Khuyến nghị an toàn & Xử lý sơ bộ tức thì
        $loi_khuyen = '';
        if ($danh_muc === 'dien') {
            if ($muc_do === 'khan_cap') {
                $loi_khuyen = '⚠️ NGUY HIỂM: Ngắt ngay Aptomat (cầu dao) tổng của phòng! Tuyệt đối không chạm tay ướt vào khu vực chập điện và tránh xa vật liệu dễ bắt lửa.';
            } else {
                $loi_khuyen = 'Tạm thời không cắm thêm các thiết bị công suất lớn vào ổ cắm bị chập chờn. Quản lý sẽ cử thợ điện kiểm tra dây nguồn và đồng hồ đo.';
            }
        } elseif ($danh_muc === 'nuoc') {
            if ($muc_do === 'khan_cap') {
                $loi_khuyen = '⚠️ Khóa ngay van nước tổng (thường ở góc nhà tắm hoặc đồng hồ nước ngoài hành lang). Kê cao đồ điện tử và dùng giẻ/khăn chặn dòng nước.';
            } else {
                $loi_khuyen = 'Đặt xô/chậu hứng tạm nước rò rỉ để tiết kiệm nước và tránh trơn trượt sàn nhà trong lúc chờ thợ.';
            }
        } elseif ($danh_muc === 'thiet_bi') {
            $loi_khuyen = 'Hãy ngắt phích cắm điện của thiết bị để bảo vệ block máy và đề phòng chập cháy nguồn điện.';
        } elseif ($danh_muc === 'noi_that') {
            $loi_khuyen = 'Nếu kẹt khóa cửa phòng, không nên bẻ giật mạnh tránh làm gãy chìa trong ổ. Hãy liên hệ hotline quản lý ngay.';
        } else {
            $loi_khuyen = 'Đã ghi nhận tình trạng sự cố. Quản lý tòa nhà sẽ sắp xếp kiểm tra xử lý sớm nhất.';
        }

        $phan_tich = "AI nhận diện: [{$danh_muc_ten}] - Mức độ: [{$muc_do_ten}]. {$loi_khuyen}";

        echo json_encode([
            'success'       => true,
            'danh_muc'      => $danh_muc,
            'danh_muc_ten'  => $danh_muc_ten,
            'muc_do'        => $muc_do,
            'muc_do_ten'    => $muc_do_ten,
            'loi_khuyen'    => $loi_khuyen,
            'phan_tich'     => $phan_tich
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // User gửi yêu cầu sửa chữa
    public function guiSuaChua()
    {
        if ($this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $db           = Database::getInstance();
            $userId       = (int)($_SESSION['user_id'] ?? 0);
            $hoTen        = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? '';
            $vi_tri       = trim($_POST['vi_tri'] ?? '');
            $mo_ta        = trim($_POST['mo_ta']  ?? '');
            $danh_muc     = trim($_POST['danh_muc'] ?? 'khac');
            $ai_phan_loai = trim($_POST['ai_phan_loai'] ?? '');
            $muc_do       = $_POST['muc_do'] ?? 'trung_binh';
            if (!in_array($muc_do, ['nhe', 'trung_binh', 'khan_cap'], true)) {
                $muc_do = 'trung_binh';
            }

            // Xử lý upload ảnh trước sửa chữa (nếu có)
            $anh_truoc = null;
            if (!empty($_FILES['anh_truoc'])) {
                $anh_truoc = $this->uploadAnh($_FILES['anh_truoc'], 'truoc_');
            }

            // Lấy số phòng
            $phongInfo = $db->prepare(
                "SELECT p.so_phong FROM nguoi_thue nt
                 JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai='hieu_luc'
                 JOIN phong p ON hd.phong_id = p.id
                 WHERE nt.account_id = ? LIMIT 1"
            );
            $phongInfo->execute([$userId]);
            $soPhong = $phongInfo->fetchColumn() ?: '';

            if ($vi_tri && $mo_ta) {
                $db->prepare(
                    "INSERT INTO yeu_cau_sua_chua (user_id, ho_ten, phong, vi_tri, danh_muc, mo_ta, anh_truoc, ai_phan_loai, muc_do)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                )->execute([$userId, $hoTen, $soPhong, $vi_tri, $danh_muc, $mo_ta, $anh_truoc, $ai_phan_loai, $muc_do]);

                // Thông báo vào chat cho admin biết.
                try {
                    $systemSenderId = (int)$db->query(
                        "SELECT id FROM account WHERE vai_tro IN ('quan_ly','chu_tro') ORDER BY id LIMIT 1"
                    )->fetchColumn();
                    if ($systemSenderId <= 0) {
                        $systemSenderId = $userId;
                    }

                    $chatModel = new ChatModel();
                    $chatModel->send($systemSenderId, 'Hệ thống', 'system',
                        "🔧 Yêu cầu sửa chữa mới từ {$hoTen} (Phòng {$soPhong}):\n📍 {$vi_tri}\n📝 {$mo_ta}",
                        'system'
                    );
                } catch (\Throwable $e) {
                    error_log('Khong the gui thong bao sua chua vao chat: ' . $e->getMessage());
                }
            }
        }

        header('Location: index.php?controller=thongbao&action=index&tab=sua_chua&msg=sent');
        exit;
    }

    // Admin cập nhật trạng thái yêu cầu sửa chữa (có thể kèm ảnh sau sửa chữa)
    public function capNhatSuaChua()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }

        Csrf::check();
        $id         = (int)($_POST['id'] ?? 0);
        $trang_thai = $_POST['trang_thai'] ?? '';
        $ghi_chu_ql = trim($_POST['ghi_chu_ql'] ?? '');

        if ($id && $trang_thai) {
            $db = Database::getInstance();

            // Xử lý upload ảnh sau sửa chữa (ảnh nghiệm thu)
            $anh_sau = null;
            if (!empty($_FILES['anh_sau'])) {
                $anh_sau = $this->uploadAnh($_FILES['anh_sau'], 'sau_');
            }

            if ($anh_sau) {
                $db->prepare("UPDATE yeu_cau_sua_chua SET trang_thai=?, ghi_chu_ql=?, anh_sau=? WHERE id=?")
                   ->execute([$trang_thai, $ghi_chu_ql, $anh_sau, $id]);
            } else {
                $db->prepare("UPDATE yeu_cau_sua_chua SET trang_thai=?, ghi_chu_ql=? WHERE id=?")
                   ->execute([$trang_thai, $ghi_chu_ql, $id]);
            }
        }

        header('Location: index.php?controller=thongbao&action=index&tab=sua_chua&msg=updated');
        exit;
    }

    // Người thuê chấm sao và nhận xét đánh giá chất lượng sửa chữa
    public function danhGiaSuaChua()
    {
        if ($this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $userId   = (int)($_SESSION['user_id'] ?? 0);
            $id       = (int)($_POST['id'] ?? 0);
            $sao      = (int)($_POST['danh_gia_sao'] ?? 5);
            $nhan_xet = trim($_POST['danh_gia_nhan_xet'] ?? '');

            if ($sao < 1) $sao = 1;
            if ($sao > 5) $sao = 5;

            if ($id > 0 && $userId > 0) {
                $db = Database::getInstance();
                $db->prepare(
                    "UPDATE yeu_cau_sua_chua
                     SET danh_gia_sao=?, danh_gia_nhan_xet=?, danh_gia_luc=NOW()
                     WHERE id=? AND user_id=? AND trang_thai='da_xong'"
                )->execute([$sao, $nhan_xet, $id, $userId]);
            }
        }

        header('Location: index.php?controller=thongbao&action=index&tab=sua_chua&msg=rated');
        exit;
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }
        $title = 'Tạo thông báo mới';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $tieu_de  = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');
            $loai     = $_POST['loai'] ?? 'chung';
            $ghim     = isset($_POST['ghim']);
            if (!$tieu_de || !$noi_dung) {
                $error = 'Vui lòng nhập tiêu đề và nội dung!';
            } else {
                $nguoi_gui = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Admin';
                $this->model->create($tieu_de, $noi_dung, $loai, $nguoi_gui, $ghim);
                header('Location: index.php?controller=thongbao&action=index&msg=created');
                exit;
            }
        }
        require 'frontend/views/ThongBao/create.php';
    }

    public function xem()
    {
        $id     = (int)($_GET['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $tb     = $this->model->getById($id);
        if (!$tb) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }
        $this->model->danhDauDaDoc($id, $userId);
        $title = $tb['tieu_de'];
        require 'frontend/views/ThongBao/xem.php';
    }

    public function edit()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $tb = $this->model->getById($id);
        if (!$tb) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }
        $title = 'Sửa thông báo';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $tieu_de  = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');
            $loai     = $_POST['loai'] ?? 'chung';
            $ghim     = isset($_POST['ghim']);
            if (!$tieu_de || !$noi_dung) {
                $error = 'Vui lòng nhập tiêu đề và nội dung!';
            } else {
                $this->model->update($id, $tieu_de, $noi_dung, $loai, $ghim);
                header('Location: index.php?controller=thongbao&action=index&msg=updated');
                exit;
            }
        }
        require 'frontend/views/ThongBao/create.php';
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?controller=thongbao&action=index&msg=deleted');
        exit;
    }

    public function ghim()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->model->toggleGhim($id);
        header('Location: index.php?controller=thongbao&action=index&msg=updated');
        exit;
    }

    public function docTatCa()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $this->model->docTatCa($userId);
        header('Location: index.php?controller=thongbao&action=index&msg=updated');
        exit;
    }

    public function countChuaDoc()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $count  = $this->model->countChuaDoc($userId);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}
