<?php

class LichSuThanhToanModel
{
    private PDO $db;

    private static bool $schemaOk = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    /** Dự phòng cho máy chưa chạy Database.bat: tự thêm cột ảnh chứng từ (migration upgrade_thanh_toan_chung_tu.sql). */
    private function ensureSchema(): void
    {
        if (self::$schemaOk) return;
        self::$schemaOk = true;
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM lich_su_thanh_toan")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('anh_chung_tu', $cols, true)) {
                $this->db->exec("ALTER TABLE lich_su_thanh_toan ADD COLUMN anh_chung_tu VARCHAR(255) NULL DEFAULT NULL");
            }
        } catch (\Throwable $e) {
            error_log('LichSuThanhToanModel: khong them duoc cot anh_chung_tu: ' . $e->getMessage());
        }
    }

    public function getAll(int $limit = 100): array
    {
        return $this->getFiltered([], $limit);
    }

    /**
     * Lịch sử giao dịch cho QUẢN TRỊ, có lọc: phong_id, khu_id, trang_thai, thang + nam (kỳ hóa đơn).
     * Mỗi dòng kèm số phòng, khu và kỳ hóa đơn (thang/nam).
     */
    public function getFiltered(array $f = [], int $limit = 200): array
    {
        $where  = [];
        $params = [];
        if (!empty($f['phong_id']))   { $where[] = 'ls.phong_id = ?';   $params[] = (int)$f['phong_id']; }
        if (!empty($f['khu_id']))     { $where[] = 'p.khu_id = ?';      $params[] = (int)$f['khu_id']; }
        if (!empty($f['trang_thai'])) { $where[] = 'ls.trang_thai = ?'; $params[] = (string)$f['trang_thai']; }
        if (!empty($f['thang']) && !empty($f['nam'])) {
            $where[] = 'hd.thang = ? AND hd.nam = ?';
            $params[] = (int)$f['thang'];
            $params[] = (int)$f['nam'];
        }
        if (!empty($f['chi_hieu_luc'])) {
            // Mặc định trang quản trị: chỉ giao dịch của hóa đơn trong thời gian hợp đồng đang hiệu lực của phòng đó
            $where[] = "EXISTS (SELECT 1 FROM hop_dong hlq
                                WHERE hlq.phong_id = ls.phong_id AND hlq.trang_thai = 'hieu_luc'
                                  AND (hd.nam * 12 + hd.thang) >= (YEAR(hlq.ngay_bat_dau) * 12 + MONTH(hlq.ngay_bat_dau)))";
        }
        $s = $this->db->prepare(
            "SELECT ls.*, p.so_phong, k.ten_khu, hd.thang, hd.nam
             FROM lich_su_thanh_toan ls
             JOIN phong p ON ls.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             LEFT JOIN hoa_don hd ON hd.id = ls.hoa_don_id"
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
            " ORDER BY ls.created_at DESC, ls.id DESC
             LIMIT " . max(1, min(1000, (int)$limit))
        );
        $s->execute($params);
        return $s->fetchAll();
    }

    /** Lịch sử giao dịch của MỘT phòng (người thuê chỉ được xem phòng mình). */
    public function getByPhong(int $phong_id, int $limit = 50): array
    {
        $s = $this->db->prepare(
            "SELECT ls.*, p.so_phong, k.ten_khu, hd.thang, hd.nam
             FROM lich_su_thanh_toan ls
             JOIN phong p ON ls.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             LEFT JOIN hoa_don hd ON hd.id = ls.hoa_don_id
             WHERE ls.phong_id = ?
             ORDER BY ls.created_at DESC, ls.id DESC
             LIMIT " . (int)$limit
        );
        $s->execute([$phong_id]);
        return $s->fetchAll();
    }

    /** Lịch sử giao dịch của NGƯỜI THUÊ: chỉ giao dịch của hóa đơn thuộc thời gian hợp đồng của chính họ. */
    public function getByTaiKhoan(int $account_id, int $limit = 100): array
    {
        $s = $this->db->prepare(
            "SELECT ls.*, p.so_phong, k.ten_khu, hd.thang, hd.nam
             FROM lich_su_thanh_toan ls
             JOIN hoa_don hd ON hd.id = ls.hoa_don_id
             JOIN phong p ON ls.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             WHERE " . HoaDonModel::dieuKienThuocTaiKhoan('hd') . "
             ORDER BY ls.created_at DESC, ls.id DESC
             LIMIT " . max(1, min(500, (int)$limit))
        );
        $s->execute([$account_id]);
        return $s->fetchAll();
    }

    /** Chi tiết đầy đủ một giao dịch: phòng, khu, hóa đơn liên quan và thời điểm xác nhận (hoa_don.ngay_thanh_toan). */
    public function getDetail(int $id): array|false
    {
        $s = $this->db->prepare(
            "SELECT ls.*, p.so_phong, k.ten_khu,
                    hd.thang, hd.nam, hd.tong_tien AS tong_tien_hd, hd.trang_thai AS trang_thai_hd,
                    hd.ngay_thanh_toan, hd.tien_phong, hd.tien_dien, hd.tien_nuoc, hd.phi_dich_vu, hd.phi_xe
             FROM lich_su_thanh_toan ls
             JOIN phong p ON ls.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             LEFT JOIN hoa_don hd ON hd.id = ls.hoa_don_id
             WHERE ls.id = ?"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Tổng tiền các giao dịch THÀNH CÔNG trong danh sách đã lọc. */
    public static function tongThanhCong(array $list): float
    {
        $t = 0.0;
        foreach ($list as $r) {
            if (($r['trang_thai'] ?? '') === 'thanh_cong') $t += (float)$r['so_tien'];
        }
        return $t;
    }

    public function getByThangNam(int $thang, int $nam): array
    {
        $s = $this->db->prepare(
            "SELECT ls.*, p.so_phong, k.ten_khu
             FROM lich_su_thanh_toan ls
             JOIN phong p ON ls.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             WHERE MONTH(ls.created_at) = ? AND YEAR(ls.created_at) = ?
             ORDER BY ls.created_at DESC"
        );
        $s->execute([$thang, $nam]);
        return $s->fetchAll();
    }

    public function create(array $d): bool
    {
        $s = $this->db->prepare(
            "INSERT INTO lich_su_thanh_toan
             (hoa_don_id, phong_id, so_tien, phuong_thuc, trang_thai, nguoi_thu, nguoi_tra, ghi_chu, anh_chung_tu)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $s->execute([
            $d['hoa_don_id'],
            $d['phong_id'],
            $d['so_tien'],
            $d['phuong_thuc'] ?? 'tien_mat',
            $d['trang_thai'] ?? 'thanh_cong',
            $d['nguoi_thu'] ?? '',
            $d['nguoi_tra'] ?? '',
            $d['ghi_chu'] ?? '',
            ($d['anh_chung_tu'] ?? '') !== '' ? $d['anh_chung_tu'] : null,
        ]);
    }

    /** Yêu cầu xác nhận thanh toán đang chờ của một hóa đơn (mới nhất), kèm ảnh chứng từ. */
    public function getPendingByInvoice(int $hoaDonId): array|false
    {
        $s = $this->db->prepare(
            "SELECT * FROM lich_su_thanh_toan
             WHERE hoa_don_id = ? AND trang_thai = 'dang_xu_ly'
             ORDER BY id DESC LIMIT 1"
        );
        $s->execute([$hoaDonId]);
        return $s->fetch();
    }

    /** Quản lý từ chối yêu cầu (chưa nhận được tiền): yêu cầu chuyển sang thất bại để người thuê gửi lại được. */
    public function markRejected(int $hoaDonId, string $nguoiXuLy = '', string $lyDo = ''): bool
    {
        $s = $this->db->prepare(
            "UPDATE lich_su_thanh_toan
             SET trang_thai = 'that_bai',
                 nguoi_thu = ?,
                 ghi_chu = CONCAT(COALESCE(ghi_chu, ''), '\nQuan ly tu choi: ', ?)
             WHERE hoa_don_id = ? AND trang_thai = 'dang_xu_ly'"
        );
        $s->execute([$nguoiXuLy, $lyDo !== '' ? $lyDo : 'Chua nhan duoc tien', $hoaDonId]);
        return $s->rowCount() > 0;
    }

    /** Số yêu cầu xác nhận thanh toán đang chờ quản lý (cho chuông thông báo). */
    public function countPending(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM lich_su_thanh_toan WHERE trang_thai = 'dang_xu_ly'"
        )->fetchColumn();
    }

    public function hasPendingForInvoice(int $hoaDonId): bool
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM lich_su_thanh_toan WHERE hoa_don_id = ? AND trang_thai = 'dang_xu_ly'"
        );
        $s->execute([$hoaDonId]);
        return (int)$s->fetchColumn() > 0;
    }

    public function markPendingResolved(int $hoaDonId, string $nguoiThu = '', string $ghiChu = ''): bool
    {
        $s = $this->db->prepare(
            "UPDATE lich_su_thanh_toan
             SET trang_thai = 'thanh_cong',
                 nguoi_thu = ?,
                 ghi_chu = CASE WHEN ? <> '' THEN CONCAT(COALESCE(ghi_chu, ''), '\nAdmin xac nhan: ', ?) ELSE ghi_chu END
             WHERE hoa_don_id = ? AND trang_thai = 'dang_xu_ly'"
        );
        $s->execute([$nguoiThu, $ghiChu, $ghiChu, $hoaDonId]);
        return $s->rowCount() > 0;
    }

    public function countToday(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM lich_su_thanh_toan WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();
    }
}
