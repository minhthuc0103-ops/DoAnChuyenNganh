<?php

class BanGiaoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    public function ensureTables(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `bien_ban_ban_giao` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `ma_bien_ban` VARCHAR(50) NOT NULL UNIQUE,
              `hop_dong_id` INT NOT NULL,
              `phong_id` INT NOT NULL,
              `nguoi_thue_id` INT NOT NULL,
              `loai` ENUM('nhan_phong','tra_phong') NOT NULL DEFAULT 'nhan_phong',
              `ngay_lap` DATE NOT NULL,
              `nguoi_lap_id` INT DEFAULT NULL,
              `chi_so_dien` INT NOT NULL DEFAULT 0,
              `chi_so_nuoc` INT NOT NULL DEFAULT 0,
              `danh_sach_tai_san` TEXT DEFAULT NULL,
              `anh_ban_giao` TEXT DEFAULT NULL,
              `ghi_chu` TEXT DEFAULT NULL,
              `trang_thai` ENUM('cho_xac_nhan','da_xac_nhan') DEFAULT 'da_xac_nhan',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              INDEX `idx_bbbg_hd` (`hop_dong_id`),
              INDEX `idx_bbbg_phong` (`phong_id`),
              INDEX `idx_bbbg_nt` (`nguoi_thue_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `yeu_cau_tra_phong` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `hop_dong_id` INT NOT NULL,
              `phong_id` INT NOT NULL,
              `nguoi_thue_id` INT NOT NULL,
              `user_id` INT NOT NULL,
              `ngay_yeu_cau` DATE NOT NULL,
              `ngay_du_kien_tra` DATE NOT NULL,
              `ly_do` TEXT NOT NULL,
              `so_tai_khoan` VARCHAR(50) DEFAULT '',
              `ngan_hang` VARCHAR(100) DEFAULT '',
              `ten_chu_tk` VARCHAR(100) DEFAULT '',
              `trang_thai` ENUM('cho_kiem_tra','dang_kiem_tra','da_thanh_ly','tu_choi') DEFAULT 'cho_kiem_tra',
              `chi_so_dien_dau` INT DEFAULT 0,
              `chi_so_dien_cuoi` INT DEFAULT 0,
              `don_gia_dien` DECIMAL(12,2) DEFAULT 0,
              `tien_dien` DECIMAL(12,2) DEFAULT 0,
              `chi_so_nuoc_dau` INT DEFAULT 0,
              `chi_so_nuoc_cuoi` INT DEFAULT 0,
              `don_gia_nuoc` DECIMAL(12,2) DEFAULT 0,
              `tien_nuoc` DECIMAL(12,2) DEFAULT 0,
              `tien_coc_goc` DECIMAL(12,2) DEFAULT 0,
              `chi_phi_hu_hong` DECIMAL(12,2) DEFAULT 0,
              `chi_tiet_hu_hong` TEXT DEFAULT NULL,
              `phi_ve_sinh` DECIMAL(12,2) DEFAULT 0,
              `chi_phi_khac` DECIMAL(12,2) DEFAULT 0,
              `ghi_chu_khac` TEXT DEFAULT NULL,
              `tien_hoan_lai` DECIMAL(12,2) DEFAULT 0,
              `anh_tra_phong` TEXT DEFAULT NULL,
              `ngay_thanh_ly` DATE DEFAULT NULL,
              `nguoi_thanh_ly_id` INT DEFAULT NULL,
              `ghi_chu_ql` TEXT DEFAULT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              INDEX `idx_yctp_hd` (`hop_dong_id`),
              INDEX `idx_yctp_phong` (`phong_id`),
              INDEX `idx_yctp_user` (`user_id`),
              INDEX `idx_yctp_trang_thai` (`trang_thai`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function getDonGia(): array
    {
        $stmt = $this->db->query("SELECT * FROM don_gia ORDER BY id LIMIT 1");
        $dg = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dg) {
            return [
                'gia_dien' => 3500,
                'gia_nuoc' => 20000,
                'phi_dv'   => 150000
            ];
        }
        return $dg;
    }

    // ════════════ BIÊN BẢN BÀN GIAO ════════════

    /** @param bool $chiHieuLuc true = chỉ biên bản của hợp đồng đang hiệu lực (mặc định trang quản trị) */
    public function getAllBienBan(?string $loai = null, bool $chiHieuLuc = false): array
    {
        $sql = "
            SELECT bb.*, p.so_phong, kt.ten_khu, nt.ho_ten AS ten_nguoi_thue, nt.sdt,
                   hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.tien_coc
            FROM bien_ban_ban_giao bb
            JOIN phong p ON bb.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON bb.nguoi_thue_id = nt.id
            JOIN hop_dong hd ON bb.hop_dong_id = hd.id
        ";
        $params = [];
        $where  = [];
        if ($loai !== null && in_array($loai, ['nhan_phong', 'tra_phong'], true)) {
            $where[]  = "bb.loai = ?";
            $params[] = $loai;
        }
        if ($chiHieuLuc) {
            $where[] = "hd.trang_thai = 'hieu_luc'";
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY bb.ngay_lap DESC, bb.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBienBanById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT bb.*, p.so_phong, p.gia AS gia_thue, kt.ten_khu, kt.dia_chi,
                   nt.ho_ten AS ten_nguoi_thue, nt.sdt, nt.cccd,
                   hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.tien_coc,
                   acc.ho_ten AS nguoi_lap_ten
            FROM bien_ban_ban_giao bb
            JOIN phong p ON bb.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON bb.nguoi_thue_id = nt.id
            JOIN hop_dong hd ON bb.hop_dong_id = hd.id
            LEFT JOIN account acc ON bb.nguoi_lap_id = acc.id
            WHERE bb.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function getBienBanByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT bb.*, p.so_phong, kt.ten_khu, nt.ho_ten AS ten_nguoi_thue
            FROM bien_ban_ban_giao bb
            JOIN phong p ON bb.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON bb.nguoi_thue_id = nt.id
            WHERE nt.account_id = ?
            ORDER BY bb.ngay_lap DESC, bb.id DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBienBan(array $data): int
    {
        $loai = $data['loai'] ?? 'nhan_phong';
        $prefix = $loai === 'tra_phong' ? 'BBTP' : 'BBNP';
        $ma = $prefix . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $stmt = $this->db->prepare("
            INSERT INTO bien_ban_ban_giao (
                ma_bien_ban, hop_dong_id, phong_id, nguoi_thue_id, loai,
                ngay_lap, nguoi_lap_id, chi_so_dien, chi_so_nuoc,
                danh_sach_tai_san, anh_ban_giao, ghi_chu, trang_thai
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $ma,
            (int)$data['hop_dong_id'],
            (int)$data['phong_id'],
            (int)$data['nguoi_thue_id'],
            $loai,
            $data['ngay_lap'] ?? date('Y-m-d'),
            $data['nguoi_lap_id'] ?? null,
            (int)($data['chi_so_dien'] ?? 0),
            (int)($data['chi_so_nuoc'] ?? 0),
            $data['danh_sach_tai_san'] ?? '[]',
            $data['anh_ban_giao'] ?? null,
            $data['ghi_chu'] ?? '',
            'da_xac_nhan'
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteBienBan(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM bien_ban_ban_giao WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ════════════ YÊU CẦU TRẢ PHÒNG & QUYẾT TOÁN ════════════

    /**
     * @param bool $chiHieuLuc true = mặc định trang quản trị: yêu cầu của hợp đồng đang hiệu lực, cộng các yêu cầu
     *                         CHƯA xử lý xong (vẫn phải hiện để quản lý quyết toán dù hợp đồng đã chuyển trạng thái).
     */
    public function getAllYeuCauTraPhong(?string $filter = null, bool $chiHieuLuc = false): array
    {
        if ($chiHieuLuc) {
            $dk = "(hd.trang_thai = 'hieu_luc' OR yc.trang_thai IN ('cho_kiem_tra','dang_kiem_tra'))";
            return $this->getAllYeuCauTraPhongDieuKien($filter, $dk);
        }
        return $this->getAllYeuCauTraPhongDieuKien($filter, null);
    }

    private function getAllYeuCauTraPhongDieuKien(?string $filter, ?string $dieuKienThem): array
    {
        $sql = "
            SELECT yc.*, p.so_phong, kt.ten_khu, nt.ho_ten AS ten_nguoi_thue, nt.sdt,
                   hd.tien_coc, hd.ngay_bat_dau, hd.ngay_ket_thuc,
                   acc.email AS user_email
            FROM yeu_cau_tra_phong yc
            JOIN phong p ON yc.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON yc.nguoi_thue_id = nt.id
            JOIN hop_dong hd ON yc.hop_dong_id = hd.id
            LEFT JOIN account acc ON yc.user_id = acc.id
        ";
        $params = [];
        $where  = [];
        if ($filter !== null && in_array($filter, ['cho_kiem_tra', 'dang_kiem_tra', 'da_thanh_ly', 'tu_choi'], true)) {
            $where[]  = "yc.trang_thai = ?";
            $params[] = $filter;
        }
        if ($dieuKienThem !== null) {
            $where[] = $dieuKienThem;
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY
                  FIELD(yc.trang_thai, 'cho_kiem_tra', 'dang_kiem_tra', 'da_thanh_ly', 'tu_choi'),
                  yc.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getYeuCauTraPhongById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT yc.*, p.so_phong, p.gia AS gia_thue, kt.ten_khu, kt.dia_chi,
                   nt.ho_ten AS ten_nguoi_thue, nt.sdt, nt.cccd,
                   hd.tien_coc, hd.ngay_bat_dau, hd.ngay_ket_thuc,
                   acc.email AS user_email, acc_admin.ho_ten AS admin_thanh_ly_ten
            FROM yeu_cau_tra_phong yc
            JOIN phong p ON yc.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON yc.nguoi_thue_id = nt.id
            JOIN hop_dong hd ON yc.hop_dong_id = hd.id
            LEFT JOIN account acc ON yc.user_id = acc.id
            LEFT JOIN account acc_admin ON yc.nguoi_thanh_ly_id = acc_admin.id
            WHERE yc.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function getYeuCauTraPhongByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT yc.*, p.so_phong, kt.ten_khu, nt.ho_ten AS ten_nguoi_thue, hd.tien_coc
            FROM yeu_cau_tra_phong yc
            JOIN phong p ON yc.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON yc.nguoi_thue_id = nt.id
            JOIN hop_dong hd ON yc.hop_dong_id = hd.id
            WHERE yc.user_id = ?
            ORDER BY yc.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPendingTraPhong(): int
    {
        return (int)$this->db->query("
            SELECT COUNT(*) FROM yeu_cau_tra_phong WHERE trang_thai = 'cho_kiem_tra'
        ")->fetchColumn();
    }

    public function countPendingTraPhongByUser(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM yeu_cau_tra_phong WHERE user_id = ? AND trang_thai IN ('cho_kiem_tra', 'dang_kiem_tra')
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function createYeuCauTraPhong(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO yeu_cau_tra_phong (
                hop_dong_id, phong_id, nguoi_thue_id, user_id,
                ngay_yeu_cau, ngay_du_kien_tra, ly_do,
                so_tai_khoan, ngan_hang, ten_chu_tk,
                tien_coc_goc, trang_thai
            ) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, 'cho_kiem_tra')
        ");
        $stmt->execute([
            (int)$data['hop_dong_id'],
            (int)$data['phong_id'],
            (int)$data['nguoi_thue_id'],
            (int)$data['user_id'],
            $data['ngay_du_kien_tra'],
            $data['ly_do'],
            $data['so_tai_khoan'] ?? '',
            $data['ngan_hang'] ?? '',
            $data['ten_chu_tk'] ?? '',
            (float)($data['tien_coc_goc'] ?? 0)
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function huyYeuCauTraPhong(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE yeu_cau_tra_phong
            SET trang_thai = 'tu_choi', ghi_chu_ql = 'Người thuê tự hủy yêu cầu'
            WHERE id = ? AND user_id = ? AND trang_thai = 'cho_kiem_tra'
        ");
        return $stmt->execute([$id, $userId]);
    }

    public function luuQuyetToan(int $id, array $d): bool
    {
        $stmt = $this->db->prepare("
            UPDATE yeu_cau_tra_phong
            SET chi_so_dien_dau = ?,
                chi_so_dien_cuoi = ?,
                don_gia_dien = ?,
                tien_dien = ?,
                chi_so_nuoc_dau = ?,
                chi_so_nuoc_cuoi = ?,
                don_gia_nuoc = ?,
                tien_nuoc = ?,
                tien_coc_goc = ?,
                chi_phi_hu_hong = ?,
                chi_tiet_hu_hong = ?,
                phi_ve_sinh = ?,
                chi_phi_khac = ?,
                ghi_chu_khac = ?,
                tien_hoan_lai = ?,
                anh_tra_phong = COALESCE(?, anh_tra_phong),
                ghi_chu_ql = ?,
                trang_thai = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$d['chi_so_dien_dau'],
            (int)$d['chi_so_dien_cuoi'],
            (float)$d['don_gia_dien'],
            (float)$d['tien_dien'],
            (int)$d['chi_so_nuoc_dau'],
            (int)$d['chi_so_nuoc_cuoi'],
            (float)$d['don_gia_nuoc'],
            (float)$d['tien_nuoc'],
            (float)$d['tien_coc_goc'],
            (float)$d['chi_phi_hu_hong'],
            $d['chi_tiet_hu_hong'] ?? '',
            (float)$d['phi_ve_sinh'],
            (float)$d['chi_phi_khac'],
            $d['ghi_chu_khac'] ?? '',
            (float)$d['tien_hoan_lai'],
            $d['anh_tra_phong'] ?? null,
            $d['ghi_chu_ql'] ?? '',
            $d['trang_thai'] ?? 'dang_kiem_tra',
            $id
        ]);
    }

    public function hoanTatThanhLy(int $id, int $adminId): bool
    {
        $yc = $this->getYeuCauTraPhongById($id);
        if (!$yc) return false;

        $this->db->beginTransaction();
        try {
            // 1. Cập nhật yêu cầu trả phòng -> da_thanh_ly
            $this->db->prepare("
                UPDATE yeu_cau_tra_phong
                SET trang_thai = 'da_thanh_ly',
                    ngay_thanh_ly = CURDATE(),
                    nguoi_thanh_ly_id = ?
                WHERE id = ?
            ")->execute([$adminId, $id]);

            // 2. Tạo biên bản bàn giao trả phòng tự động
            $maBB = 'BBTP' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $danhSachTaiSan = json_encode([
                'chi_so_dien_cuoi'  => $yc['chi_so_dien_cuoi'],
                'chi_so_nuoc_cuoi'  => $yc['chi_so_nuoc_cuoi'],
                'tien_dien'         => $yc['tien_dien'],
                'tien_nuoc'         => $yc['tien_nuoc'],
                'chi_phi_hu_hong'   => $yc['chi_phi_hu_hong'],
                'chi_tiet_hu_hong'  => $yc['chi_tiet_hu_hong'],
                'phi_ve_sinh'       => $yc['phi_ve_sinh'],
                'tien_coc_goc'      => $yc['tien_coc_goc'],
                'tien_hoan_lai'     => $yc['tien_hoan_lai'],
            ], JSON_UNESCAPED_UNICODE);

            $this->db->prepare("
                INSERT INTO bien_ban_ban_giao (
                    ma_bien_ban, hop_dong_id, phong_id, nguoi_thue_id, loai,
                    ngay_lap, nguoi_lap_id, chi_so_dien, chi_so_nuoc,
                    danh_sach_tai_san, anh_ban_giao, ghi_chu, trang_thai
                ) VALUES (?, ?, ?, ?, 'tra_phong', CURDATE(), ?, ?, ?, ?, ?, ?, 'da_xoc_nhan')
            ")->execute([
                $maBB,
                $yc['hop_dong_id'],
                $yc['phong_id'],
                $yc['nguoi_thue_id'],
                $adminId,
                $yc['chi_so_dien_cuoi'],
                $yc['chi_so_nuoc_cuoi'],
                $danhSachTaiSan,
                $yc['anh_tra_phong'],
                'Thanh lý trả phòng theo yêu cầu #' . $yc['id'] . '. Tiền hoàn lại: ' . number_format($yc['tien_hoan_lai']) . 'đ'
            ]);

            // 3. Kết thúc hợp đồng -> het_han
            $this->db->prepare("
                UPDATE hop_dong SET trang_thai = 'het_han' WHERE id = ?
            ")->execute([$yc['hop_dong_id']]);

            // 4. Phòng chuyển về trạng thái 'trong'
            $this->db->prepare("
                UPDATE phong SET trang_thai = 'trong' WHERE id = ?
            ")->execute([$yc['phong_id']]);

            // 5. Gửi thông báo chat hệ thống cho người thuê
            try {
                $chatModel = new ChatModel();
                $chatModel->send(
                    $adminId,
                    'Quản lý',
                    'system',
                    "📋 Hoàn tất thanh lý & trả phòng {$yc['so_phong']}!\nSố tiền cọc thực tế hoàn trả: " . number_format($yc['tien_hoan_lai']) . "đ. Cảm ơn bạn đã đồng hành!",
                    'system'
                );
            } catch (\Throwable $ex) {}

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Loi thanh ly tra phong: ' . $e->getMessage());
            return false;
        }
    }

    // Lấy hợp đồng đang thuê của account
    public function getActiveRentalByAccount(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT hd.*, p.so_phong, p.gia AS gia_thue, kt.ten_khu, nt.id AS nguoi_thue_id, nt.ho_ten, nt.sdt
            FROM nguoi_thue nt
            JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
            JOIN phong p ON hd.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            WHERE nt.account_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    // Danh sách hợp đồng đang hiệu lực để admin chọn khi tạo biên bản bàn giao
    public function getHopDongDangHieuLuc(): array
    {
        return $this->db->query("
            SELECT hd.id AS hop_dong_id, hd.tien_coc, hd.ngay_bat_dau, hd.ngay_ket_thuc,
                   p.id AS phong_id, p.so_phong, kt.ten_khu,
                   nt.id AS nguoi_thue_id, nt.ho_ten AS ten_nguoi_thue, nt.sdt
            FROM hop_dong hd
            JOIN phong p ON hd.phong_id = p.id
            LEFT JOIN khu_tro kt ON p.khu_id = kt.id
            JOIN nguoi_thue nt ON hd.nguoi_thue_id = nt.id
            WHERE hd.trang_thai = 'hieu_luc'
            ORDER BY p.so_phong ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
}
