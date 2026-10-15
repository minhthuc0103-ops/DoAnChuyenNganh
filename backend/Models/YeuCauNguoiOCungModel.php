<?php

class YeuCauNguoiOCungModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `yeu_cau_nguoi_o_cung` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `nguoi_thue_id` INT NOT NULL,
                `hop_dong_id` INT NOT NULL,
                `phong_id` INT NOT NULL,
                `ho_ten` VARCHAR(150) NOT NULL,
                `cccd` VARCHAR(30) DEFAULT '',
                `sdt` VARCHAR(20) DEFAULT '',
                `ngay_sinh` DATE DEFAULT NULL,
                `gioi_tinh` ENUM('nam','nu','khac') DEFAULT 'nam',
                `que_quan` VARCHAR(255) DEFAULT '',
                `ly_do` TEXT,
                `trang_thai` ENUM('cho_duyet','da_duyet','tu_choi','da_huy') DEFAULT 'cho_duyet',
                `phan_hoi_ql` TEXT,
                `nguoi_duyet_id` INT DEFAULT NULL,
                `duyet_luc` DATETIME DEFAULT NULL,
                `nguoi_thue_phong_id` INT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_ycnoc_user` (`user_id`),
                KEY `idx_ycnoc_hop_dong` (`hop_dong_id`),
                KEY `idx_ycnoc_trang_thai` (`trang_thai`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function getCurrentRentalByAccount(int $accountId): array|false
    {
        $s = $this->db->prepare(
            "SELECT nt.id AS nguoi_thue_id, nt.account_id, nt.ho_ten,
                    hd.id AS hop_dong_id, hd.phong_id,
                    p.so_phong, p.so_nguoi, k.ten_khu
             FROM nguoi_thue nt
             JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
             JOIN phong p ON p.id = hd.phong_id
             LEFT JOIN khu_tro k ON k.id = p.khu_id
             WHERE nt.account_id = ?
             LIMIT 1"
        );
        $s->execute([$accountId]);
        return $s->fetch();
    }

    public function createRequest(array $d): bool
    {
        if ($this->countPendingByUser((int)$d['user_id']) > 0) {
            throw new RuntimeException('Bạn đang có một yêu cầu thêm người ở cùng chờ duyệt.');
        }

        $this->assertRoomHasSlot((int)$d['hop_dong_id'], (int)$d['phong_id']);

        $s = $this->db->prepare(
            "INSERT INTO yeu_cau_nguoi_o_cung
             (user_id, nguoi_thue_id, hop_dong_id, phong_id, ho_ten, cccd, sdt, ngay_sinh, gioi_tinh, que_quan, ly_do)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $s->execute([
            $d['user_id'],
            $d['nguoi_thue_id'],
            $d['hop_dong_id'],
            $d['phong_id'],
            $d['ho_ten'],
            $d['cccd'] ?? '',
            $d['sdt'] ?? '',
            ($d['ngay_sinh'] ?? '') !== '' ? $d['ngay_sinh'] : null,
            $d['gioi_tinh'] ?? 'nam',
            $d['que_quan'] ?? '',
            $d['ly_do'] ?? '',
        ]);
    }

    public function getByUser(int $userId): array
    {
        $s = $this->db->prepare(
            "SELECT yc.*, p.so_phong, k.ten_khu, a.ho_ten AS nguoi_duyet
             FROM yeu_cau_nguoi_o_cung yc
             JOIN phong p ON p.id = yc.phong_id
             LEFT JOIN khu_tro k ON k.id = p.khu_id
             LEFT JOIN account a ON a.id = yc.nguoi_duyet_id
             WHERE yc.user_id = ?
             ORDER BY yc.created_at DESC"
        );
        $s->execute([$userId]);
        return $s->fetchAll();
    }

    public function getAll(): array
    {
        return $this->db->query(
            "SELECT yc.*, p.so_phong, p.so_nguoi, k.ten_khu,
                    nt.ho_ten AS nguoi_gui, nt.sdt AS sdt_nguoi_gui,
                    a.ho_ten AS nguoi_duyet
             FROM yeu_cau_nguoi_o_cung yc
             JOIN phong p ON p.id = yc.phong_id
             LEFT JOIN khu_tro k ON k.id = p.khu_id
             JOIN nguoi_thue nt ON nt.id = yc.nguoi_thue_id
             LEFT JOIN account a ON a.id = yc.nguoi_duyet_id
             ORDER BY FIELD(yc.trang_thai, 'cho_duyet', 'da_duyet', 'tu_choi', 'da_huy'), yc.created_at DESC"
        )->fetchAll();
    }

    public function countPending(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM yeu_cau_nguoi_o_cung WHERE trang_thai = 'cho_duyet'"
        )->fetchColumn();
    }

    public function countPendingByUser(int $userId): int
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM yeu_cau_nguoi_o_cung WHERE user_id = ? AND trang_thai = 'cho_duyet'"
        );
        $s->execute([$userId]);
        return (int)$s->fetchColumn();
    }

    public function cancelByUser(int $id, int $userId): bool
    {
        $s = $this->db->prepare(
            "UPDATE yeu_cau_nguoi_o_cung
             SET trang_thai = 'da_huy'
             WHERE id = ? AND user_id = ? AND trang_thai = 'cho_duyet'"
        );
        $s->execute([$id, $userId]);
        return $s->rowCount() > 0;
    }

    public function approve(int $id, int $adminId, string $phanHoi = ''): array
    {
        try {
            $this->db->beginTransaction();
            $request = $this->getRequestForUpdate($id);
            if (!$request) {
                throw new RuntimeException('Không tìm thấy yêu cầu thêm người ở cùng.');
            }
            if ($request['trang_thai'] !== 'cho_duyet') {
                throw new RuntimeException('Yêu cầu này đã được xử lý trước đó.');
            }

            $this->assertRoomHasSlot((int)$request['hop_dong_id'], (int)$request['phong_id'], true);

            $s = $this->db->prepare(
                "INSERT INTO nguoi_thue_phong
                 (hop_dong_id, phong_id, ho_ten, cccd, sdt, ngay_sinh, gioi_tinh, que_quan, avatar, la_chu_hop_dong)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, '', 0)"
            );
            $s->execute([
                $request['hop_dong_id'],
                $request['phong_id'],
                $request['ho_ten'],
                $request['cccd'],
                $request['sdt'],
                $request['ngay_sinh'],
                $request['gioi_tinh'],
                $request['que_quan'],
            ]);
            $roommateId = (int)$this->db->lastInsertId();

            $this->db->prepare(
                "UPDATE yeu_cau_nguoi_o_cung
                 SET trang_thai = 'da_duyet', phan_hoi_ql = ?, nguoi_duyet_id = ?, duyet_luc = NOW(), nguoi_thue_phong_id = ?
                 WHERE id = ?"
            )->execute([$phanHoi, $adminId, $roommateId, $id]);

            $this->syncRoomOccupancy((int)$request['phong_id']);
            $this->db->commit();
            return $request;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function reject(int $id, int $adminId, string $phanHoi = ''): array
    {
        try {
            $this->db->beginTransaction();
            $request = $this->getRequestForUpdate($id);
            if (!$request) {
                throw new RuntimeException('Không tìm thấy yêu cầu thêm người ở cùng.');
            }
            if ($request['trang_thai'] !== 'cho_duyet') {
                throw new RuntimeException('Yêu cầu này đã được xử lý trước đó.');
            }

            $this->db->prepare(
                "UPDATE yeu_cau_nguoi_o_cung
                 SET trang_thai = 'tu_choi', phan_hoi_ql = ?, nguoi_duyet_id = ?, duyet_luc = NOW()
                 WHERE id = ?"
            )->execute([$phanHoi, $adminId, $id]);
            $this->db->commit();
            return $request;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function notifyAdmins(string $tieuDe, string $noiDung, string $nguoiGui): void
    {
        $ids = $this->db->query(
            "SELECT id FROM account WHERE vai_tro IN ('quan_ly', 'chu_tro')"
        )->fetchAll(PDO::FETCH_COLUMN);
        $this->createTargetedNotification($ids, $tieuDe, $noiDung, $nguoiGui);
    }

    public function notifyUser(int $accountId, string $tieuDe, string $noiDung, string $nguoiGui): void
    {
        if ($accountId > 0) {
            $this->createTargetedNotification([$accountId], $tieuDe, $noiDung, $nguoiGui);
        }
    }

    private function getRequestForUpdate(int $id): array|false
    {
        $s = $this->db->prepare(
            "SELECT yc.*, p.so_phong, p.so_nguoi, k.ten_khu, nt.account_id, nt.ho_ten AS nguoi_gui
             FROM yeu_cau_nguoi_o_cung yc
             JOIN phong p ON p.id = yc.phong_id
             LEFT JOIN khu_tro k ON k.id = p.khu_id
             JOIN nguoi_thue nt ON nt.id = yc.nguoi_thue_id
             WHERE yc.id = ?
             FOR UPDATE"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    private function assertRoomHasSlot(int $hopDongId, int $phongId, bool $lockRoom = false): void
    {
        $roomSql = "SELECT so_nguoi FROM phong WHERE id = ?" . ($lockRoom ? " FOR UPDATE" : "");
        $roomStmt = $this->db->prepare($roomSql);
        $roomStmt->execute([$phongId]);
        $capacity = (int)$roomStmt->fetchColumn();
        if ($capacity <= 0) {
            $capacity = 4;
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM nguoi_thue_phong WHERE hop_dong_id = ?");
        $countStmt->execute([$hopDongId]);
        if ((int)$countStmt->fetchColumn() >= $capacity) {
            throw new RuntimeException('Phòng đã đủ số người đăng ký.');
        }
    }

    private function syncRoomOccupancy(int $phongId): void
    {
        if (!$this->hasColumn('phong', 'so_nguoi_hien_tai')) {
            return;
        }

        $s = $this->db->prepare(
            "SELECT COUNT(*)
             FROM nguoi_thue_phong ntp
             JOIN hop_dong hd ON hd.id = ntp.hop_dong_id
             WHERE hd.phong_id = ? AND hd.trang_thai = 'hieu_luc'"
        );
        $s->execute([$phongId]);
        $this->db->prepare("UPDATE phong SET so_nguoi_hien_tai = ? WHERE id = ?")
                 ->execute([(int)$s->fetchColumn(), $phongId]);
    }

    private function hasColumn(string $table, string $column): bool
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
        );
        $s->execute([$table, $column]);
        return (int)$s->fetchColumn() > 0;
    }

    private function createTargetedNotification(array $accountIds, string $tieuDe, string $noiDung, string $nguoiGui): void
    {
        $accountIds = array_values(array_unique(array_filter(array_map('intval', $accountIds))));
        if (!$accountIds) {
            return;
        }

        try {
            $tbModel = new ThongBaoModel();
            $id = $tbModel->createAndGetId($tieuDe, $noiDung, 'khac', $nguoiGui, false);
            if ($id > 0) {
                $tbModel->anVoiTatCaTruNguoiNhan($id, $accountIds);
            }
        } catch (Throwable $e) {
            error_log('Khong the tao thong bao yeu cau nguoi o cung: ' . $e->getMessage());
        }
    }
}
