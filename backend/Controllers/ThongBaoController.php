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
                `mo_ta`       TEXT NOT NULL,
                `muc_do`      ENUM('nhe','trung_binh','khan_cap') DEFAULT 'trung_binh',
                `trang_thai`  ENUM('cho_xu_ly','dang_xu_ly','da_xong') DEFAULT 'cho_xu_ly',
                `ghi_chu_ql`  TEXT,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
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

        } else {
            // User: CHỈ thấy yêu cầu của chính mình (theo user_id)
            $stmt = $db->prepare(
                "SELECT * FROM yeu_cau_sua_chua WHERE user_id=? ORDER BY created_at DESC"
            );
            $stmt->execute([$userId]);
            $suaChuaList = $stmt->fetchAll();
            $soChoXuLy   = 0;

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

    // User gửi yêu cầu sửa chữa
    public function guiSuaChua()
    {
        if ($this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $db      = Database::getInstance();
            $userId  = (int)($_SESSION['user_id'] ?? 0);
            $hoTen   = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? '';
            $vi_tri  = trim($_POST['vi_tri'] ?? '');
            $mo_ta   = trim($_POST['mo_ta']  ?? '');
            $muc_do  = $_POST['muc_do'] ?? 'trung_binh';
            if (!in_array($muc_do, ['nhe', 'trung_binh', 'khan_cap'], true)) {
                $muc_do = 'trung_binh';
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
                    "INSERT INTO yeu_cau_sua_chua (user_id, ho_ten, phong, vi_tri, mo_ta, muc_do)
                     VALUES (?, ?, ?, ?, ?, ?)"
                )->execute([$userId, $hoTen, $soPhong, $vi_tri, $mo_ta, $muc_do]);

                // Thông báo vào chat cho admin biết. chat_messages.user_id có khóa ngoại tới account.id,
                // nên tin hệ thống vẫn cần dùng một tài khoản có thật làm sender.
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

    // Admin cập nhật trạng thái yêu cầu sửa chữa
    public function capNhatSuaChua()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?controller=thongbao&action=index');
            exit;
        }

        $id         = (int)($_POST['id'] ?? 0);
        $trang_thai = $_POST['trang_thai'] ?? '';
        $ghi_chu_ql = trim($_POST['ghi_chu_ql'] ?? '');

        if ($id && $trang_thai) {
            $db = Database::getInstance();
            $db->prepare("UPDATE yeu_cau_sua_chua SET trang_thai=?, ghi_chu_ql=? WHERE id=?")
               ->execute([$trang_thai, $ghi_chu_ql, $id]);
        }

        header('Location: index.php?controller=thongbao&action=index&tab=sua_chua&msg=updated');
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
