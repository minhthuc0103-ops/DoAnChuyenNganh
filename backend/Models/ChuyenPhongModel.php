<?php

class ChuyenPhongModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    /**
     * Tự tạo bảng để tính năng chạy được ngay cả khi người dùng chưa chạy file upgrade SQL.
     */
    private function ensureTable(): void
    {
        $this->db->exec("\n            CREATE TABLE IF NOT EXISTS `yeu_cau_chuyen_phong` (\n                `id`             INT AUTO_INCREMENT PRIMARY KEY,\n                `user_id`        INT NOT NULL,\n                `nguoi_thue_id`  INT NOT NULL,\n                `hop_dong_id`    INT NOT NULL,\n                `phong_cu_id`    INT NOT NULL,\n                `phong_moi_id`   INT NOT NULL,\n                `ly_do`          TEXT NOT NULL,\n                `trang_thai`     ENUM('cho_duyet','da_duyet','tu_choi','da_huy') DEFAULT 'cho_duyet',\n                `phan_hoi_ql`    TEXT,\n                `nguoi_duyet_id` INT DEFAULT NULL,\n                `duyet_luc`      DATETIME DEFAULT NULL,\n                `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n                `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n                KEY `idx_yccp_user` (`user_id`),\n                KEY `idx_yccp_hop_dong` (`hop_dong_id`),\n                KEY `idx_yccp_phong_moi` (`phong_moi_id`),\n                KEY `idx_yccp_trang_thai` (`trang_thai`)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n        ");
    }

    public function getCurrentRentalByAccount(int $accountId): array|false
    {
        $s = $this->db->prepare(
            "SELECT nt.id AS nguoi_thue_id, nt.account_id, nt.ho_ten,
                    hd.id AS hop_dong_id, hd.phong_id,
                    p.so_phong, p.gia, k.ten_khu
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

    public function getPhongTrong(int $excludePhongId = 0): array
    {
        $sql = "SELECT p.*, k.ten_khu
                FROM phong p
                LEFT JOIN khu_tro k ON k.id = p.khu_id
                WHERE p.trang_thai = 'trong'";
        $params = [];
        if ($excludePhongId > 0) {
            $sql .= " AND p.id <> ?";
            $params[] = $excludePhongId;
        }
        $sql .= " ORDER BY k.ten_khu, p.so_phong";
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function getByUser(int $userId): array
    {
        $s = $this->db->prepare(
            "SELECT yc.*, pc.so_phong AS phong_cu, pm.so_phong AS phong_moi,
                    kc.ten_khu AS khu_cu, km.ten_khu AS khu_moi,
                    a.ho_ten AS nguoi_duyet
             FROM yeu_cau_chuyen_phong yc
             JOIN phong pc ON pc.id = yc.phong_cu_id
             JOIN phong pm ON pm.id = yc.phong_moi_id
             LEFT JOIN khu_tro kc ON kc.id = pc.khu_id
             LEFT JOIN khu_tro km ON km.id = pm.khu_id
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
            "SELECT yc.*, pc.so_phong AS phong_cu, pm.so_phong AS phong_moi,
                    kc.ten_khu AS khu_cu, km.ten_khu AS khu_moi,
                    nt.ho_ten, nt.sdt, a.ho_ten AS nguoi_duyet
             FROM yeu_cau_chuyen_phong yc
             JOIN phong pc ON pc.id = yc.phong_cu_id
             JOIN phong pm ON pm.id = yc.phong_moi_id
             LEFT JOIN khu_tro kc ON kc.id = pc.khu_id
             LEFT JOIN khu_tro km ON km.id = pm.khu_id
             JOIN nguoi_thue nt ON nt.id = yc.nguoi_thue_id
             LEFT JOIN account a ON a.id = yc.nguoi_duyet_id
             ORDER BY FIELD(yc.trang_thai, 'cho_duyet', 'da_duyet', 'tu_choi', 'da_huy'), yc.created_at DESC"
        )->fetchAll();
    }

    public function countPending(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM yeu_cau_chuyen_phong WHERE trang_thai = 'cho_duyet'"
        )->fetchColumn();
    }

    public function countPendingByUser(int $userId): int
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM yeu_cau_chuyen_phong WHERE user_id = ? AND trang_thai = 'cho_duyet'"
        );
        $s->execute([$userId]);
        return (int)$s->fetchColumn();
    }

    public function createRequest(array $d): bool
    {
        if ($this->countPendingByUser((int)$d['user_id']) > 0) {
            throw new RuntimeException('Bạn đang có một yêu cầu chuyển phòng chờ duyệt. Vui lòng chờ quản lý xử lý trước khi gửi yêu cầu mới.');
        }

        $target = $this->getRoomById((int)$d['phong_moi_id']);
        if (!$target || $target['trang_thai'] !== 'trong') {
            throw new RuntimeException('Phòng muốn chuyển đến không còn trống. Vui lòng chọn phòng khác.');
        }
        if ((int)$d['phong_cu_id'] === (int)$d['phong_moi_id']) {
            throw new RuntimeException('Phòng mới phải khác phòng hiện tại.');
        }

        $s = $this->db->prepare(
            "INSERT INTO yeu_cau_chuyen_phong
             (user_id, nguoi_thue_id, hop_dong_id, phong_cu_id, phong_moi_id, ly_do)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $s->execute([
            $d['user_id'], $d['nguoi_thue_id'], $d['hop_dong_id'],
            $d['phong_cu_id'], $d['phong_moi_id'], $d['ly_do'],
        ]);
    }

    public function cancelByUser(int $id, int $userId): bool
    {
        $s = $this->db->prepare(
            "UPDATE yeu_cau_chuyen_phong
             SET trang_thai = 'da_huy'
             WHERE id = ? AND user_id = ? AND trang_thai = 'cho_duyet'"
        );
        $s->execute([$id, $userId]);
        return $s->rowCount() > 0;
    }

    /**
     * Duyệt yêu cầu trong transaction để không thể cấp cùng một phòng cho hai hợp đồng.
     */
    public function approve(int $id, int $adminId, string $phanHoi = ''): array
    {
        try {
            $this->db->beginTransaction();

            $request = $this->getRequestForUpdate($id);
            if (!$request) {
                throw new RuntimeException('Không tìm thấy yêu cầu chuyển phòng.');
            }
            if ($request['trang_thai'] !== 'cho_duyet') {
                throw new RuntimeException('Yêu cầu này đã được xử lý trước đó.');
            }

            $hdStmt = $this->db->prepare("SELECT * FROM hop_dong WHERE id = ? FOR UPDATE");
            $hdStmt->execute([(int)$request['hop_dong_id']]);
            $hopDong = $hdStmt->fetch();
            if (!$hopDong || $hopDong['trang_thai'] !== 'hieu_luc') {
                throw new RuntimeException('Hợp đồng hiện tại không còn hiệu lực.');
            }
            if ((int)$hopDong['nguoi_thue_id'] !== (int)$request['nguoi_thue_id'] ||
                (int)$hopDong['phong_id'] !== (int)$request['phong_cu_id']) {
                throw new RuntimeException('Thông tin phòng hiện tại đã thay đổi. Vui lòng kiểm tra lại yêu cầu.');
            }

            $targetStmt = $this->db->prepare("SELECT * FROM phong WHERE id = ? FOR UPDATE");
            $targetStmt->execute([(int)$request['phong_moi_id']]);
            $target = $targetStmt->fetch();
            if (!$target || $target['trang_thai'] !== 'trong') {
                throw new RuntimeException('Phòng đích đã không còn trống. Không thể duyệt yêu cầu này.');
            }

            // Kiểm tra thêm hợp đồng để không phụ thuộc hoàn toàn vào cột trạng thái phòng.
            $targetContractStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM hop_dong WHERE phong_id = ? AND trang_thai = 'hieu_luc'"
            );
            $targetContractStmt->execute([(int)$request['phong_moi_id']]);
            if ((int)$targetContractStmt->fetchColumn() > 0) {
                throw new RuntimeException('Phòng đích đã có hợp đồng hiệu lực. Không thể duyệt yêu cầu này.');
            }

            // Chuyển toàn bộ dữ liệu đang phụ thuộc vào phòng của hợp đồng.
            // TODO: bổ sung nghiệp vụ bù trừ tiền phòng giữa tháng, chênh lệch tiền cọc
            // và ngày bắt đầu áp dụng giá phòng mới trước khi coi đây là quyết toán đầy đủ.
            $this->db->prepare("UPDATE hop_dong SET phong_id = ? WHERE id = ?")
                     ->execute([(int)$request['phong_moi_id'], (int)$request['hop_dong_id']]);
            $this->db->prepare("UPDATE nguoi_thue_phong SET phong_id = ? WHERE hop_dong_id = ?")
                     ->execute([(int)$request['phong_moi_id'], (int)$request['hop_dong_id']]);
            $this->db->prepare("UPDATE xe SET phong_id = ? WHERE hop_dong_id = ?")
                     ->execute([(int)$request['phong_moi_id'], (int)$request['hop_dong_id']]);

            $this->syncRoomStatusAndOccupancy((int)$request['phong_cu_id']);
            $this->syncRoomStatusAndOccupancy((int)$request['phong_moi_id']);

            $this->db->prepare(
                "UPDATE yeu_cau_chuyen_phong
                 SET trang_thai = 'da_duyet', phan_hoi_ql = ?, nguoi_duyet_id = ?, duyet_luc = NOW()
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

    public function reject(int $id, int $adminId, string $phanHoi = ''): array
    {
        try {
            $this->db->beginTransaction();
            $request = $this->getRequestForUpdate($id);
            if (!$request) {
                throw new RuntimeException('Không tìm thấy yêu cầu chuyển phòng.');
            }
            if ($request['trang_thai'] !== 'cho_duyet') {
                throw new RuntimeException('Yêu cầu này đã được xử lý trước đó.');
            }
            $this->db->prepare(
                "UPDATE yeu_cau_chuyen_phong
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

    private function createTargetedNotification(array $accountIds, string $tieuDe, string $noiDung, string $nguoiGui): void
    {
        $accountIds = array_values(array_unique(array_filter(array_map('intval', $accountIds))));
        if (!$accountIds) {
            return;
        }

        // Thông báo là bước phụ: không được làm hỏng thao tác chuyển phòng đã thành công.
        try {
            $tbModel = new ThongBaoModel();
            $id = $tbModel->createAndGetId($tieuDe, $noiDung, 'khac', $nguoiGui, false);
            if ($id > 0) {
                $tbModel->anVoiTatCaTruNguoiNhan($id, $accountIds);
            }
        } catch (Throwable $e) {
            error_log('Khong the tao thong bao chuyen phong: ' . $e->getMessage());
        }
    }

    private function getRequestForUpdate(int $id): array|false
    {
        $s = $this->db->prepare(
            "SELECT yc.*, pc.so_phong AS phong_cu, pm.so_phong AS phong_moi,
                    kc.ten_khu AS khu_cu, km.ten_khu AS khu_moi,
                    nt.ho_ten, nt.account_id
             FROM yeu_cau_chuyen_phong yc
             JOIN phong pc ON pc.id = yc.phong_cu_id
             JOIN phong pm ON pm.id = yc.phong_moi_id
             LEFT JOIN khu_tro kc ON kc.id = pc.khu_id
             LEFT JOIN khu_tro km ON km.id = pm.khu_id
             JOIN nguoi_thue nt ON nt.id = yc.nguoi_thue_id
             WHERE yc.id = ?
             FOR UPDATE"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    private function getRoomById(int $id): array|false
    {
        $s = $this->db->prepare("SELECT * FROM phong WHERE id = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    private function syncRoomStatusAndOccupancy(int $phongId): void
    {
        $hdStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hop_dong WHERE phong_id = ? AND trang_thai = 'hieu_luc'"
        );
        $hdStmt->execute([$phongId]);
        $soHopDong = (int)$hdStmt->fetchColumn();

        $ntStmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM nguoi_thue_phong ntp
             JOIN hop_dong hd ON hd.id = ntp.hop_dong_id
             WHERE hd.phong_id = ? AND hd.trang_thai = 'hieu_luc'"
        );
        $ntStmt->execute([$phongId]);
        $soNguoi = (int)$ntStmt->fetchColumn();
        $trangThai = $soHopDong > 0 ? 'dang_thue' : 'trong';

        $this->db->prepare(
            "UPDATE phong SET trang_thai = ?, so_nguoi_hien_tai = ? WHERE id = ?"
        )->execute([$trangThai, $soNguoi, $phongId]);
    }
}
