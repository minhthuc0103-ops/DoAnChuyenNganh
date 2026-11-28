<?php
class HoaDonModel {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
        try {
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS phi_dich_vu DECIMAL(12,2) DEFAULT 0");
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS phi_xe DECIMAL(12,2) DEFAULT 0");
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS so_xe INT DEFAULT 0");
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS phuong_thuc_tt VARCHAR(50) DEFAULT NULL");
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS ngay_thanh_toan DATETIME DEFAULT NULL");
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS nguoi_thu VARCHAR(100) DEFAULT NULL");
            $this->db->exec("ALTER TABLE hoa_don ADD COLUMN IF NOT EXISTS ghi_chu_tt TEXT DEFAULT NULL");
            // Ghi nhớ chỉ số đồng hồ điện/nước hiện tại ngay trên bảng phong để luôn nhớ số cũ cho kỳ sau
            $this->db->exec("ALTER TABLE phong ADD COLUMN IF NOT EXISTS chi_so_dien_hien_tai DECIMAL(10,2) DEFAULT 0");
            $this->db->exec("ALTER TABLE phong ADD COLUMN IF NOT EXISTS chi_so_nuoc_hien_tai DECIMAL(10,2) DEFAULT 0");
        } catch (\Exception $e) {}
    }

    public function getByThangNam(int $thang, int $nam): array {
        $s = $this->db->prepare(
            "SELECT hd.*, p.so_phong,
                    pend.pending_count, pend.pending_method, pend.pending_by, pend.pending_note
             FROM hoa_don hd
             JOIN phong p ON hd.phong_id=p.id
             LEFT JOIN (
                SELECT hoa_don_id,
                       COUNT(*) AS pending_count,
                       SUBSTRING_INDEX(GROUP_CONCAT(phuong_thuc ORDER BY created_at DESC), ',', 1) AS pending_method,
                       SUBSTRING_INDEX(GROUP_CONCAT(nguoi_tra ORDER BY created_at DESC), ',', 1) AS pending_by,
                       SUBSTRING_INDEX(GROUP_CONCAT(ghi_chu ORDER BY created_at DESC SEPARATOR '||'), '||', 1) AS pending_note
                FROM lich_su_thanh_toan
                WHERE trang_thai='dang_xu_ly'
                GROUP BY hoa_don_id
             ) pend ON pend.hoa_don_id = hd.id
             WHERE hd.thang=? AND hd.nam=?
             ORDER BY p.so_phong"
        );
        $s->execute([$thang, $nam]);
        return $s->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare(
            "SELECT hd.*, p.so_phong,
                    pend.pending_count, pend.pending_method, pend.pending_by, pend.pending_note
             FROM hoa_don hd
             JOIN phong p ON hd.phong_id=p.id
             LEFT JOIN (
                SELECT hoa_don_id,
                       COUNT(*) AS pending_count,
                       SUBSTRING_INDEX(GROUP_CONCAT(phuong_thuc ORDER BY created_at DESC), ',', 1) AS pending_method,
                       SUBSTRING_INDEX(GROUP_CONCAT(nguoi_tra ORDER BY created_at DESC), ',', 1) AS pending_by,
                       SUBSTRING_INDEX(GROUP_CONCAT(ghi_chu ORDER BY created_at DESC SEPARATOR '||'), '||', 1) AS pending_note
                FROM lich_su_thanh_toan
                WHERE trang_thai='dang_xu_ly'
                GROUP BY hoa_don_id
             ) pend ON pend.hoa_don_id = hd.id
             WHERE hd.id=?"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function existsForPhongThang(int $phong_id, int $thang, int $nam): bool {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM hoa_don WHERE phong_id=? AND thang=? AND nam=?"
        );
        $s->execute([$phong_id, $thang, $nam]);
        return $s->fetchColumn() > 0;
    }

    public function create(array $d): bool {
        $s = $this->db->prepare(
            "INSERT INTO hoa_don
             (phong_id,thang,nam,chi_so_dien_cu,chi_so_dien_moi,anh_cong_to_dien,chi_so_nuoc_cu,chi_so_nuoc_moi,anh_cong_to_nuoc,
              tien_phong,tien_dien,tien_nuoc,phi_dich_vu,phi_xe,so_xe,tong_tien,trang_thai)
             VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'chua_tt')"
        );
        $ok = $s->execute([
            $d['phong_id'],$d['thang'],$d['nam'],
            $d['chi_so_dien_cu'],$d['chi_so_dien_moi'],$d['anh_cong_to_dien'] ?? null,
            $d['chi_so_nuoc_cu'],$d['chi_so_nuoc_moi'],$d['anh_cong_to_nuoc'] ?? null,
            $d['tien_phong'],$d['tien_dien'],$d['tien_nuoc'],
            $d['phi_dich_vu'] ?? 0,
            $d['phi_xe'] ?? 0,
            $d['so_xe'] ?? 0,
            $d['tong_tien'],
        ]);
        if ($ok) {
            $this->luuChiSoDongHoHienTai((int)$d['phong_id'], (float)$d['chi_so_dien_moi'], (float)$d['chi_so_nuoc_moi']);
        }
        return $ok;
    }

    /**
     * Ghi nhớ chỉ số đồng hồ mới nhất vào bảng phong để lần quét tiếp theo hoặc kỳ sau tự động lấy làm chỉ số cũ
     */
    public function luuChiSoDongHoHienTai(int $phong_id, float $dien_moi, float $nuoc_moi): void {
        try {
            $stmt = $this->db->prepare(
                "UPDATE phong
                 SET chi_so_dien_hien_tai = IF(? > 0, ?, COALESCE(chi_so_dien_hien_tai, 0)),
                     chi_so_nuoc_hien_tai = IF(? > 0, ?, COALESCE(chi_so_nuoc_hien_tai, 0))
                 WHERE id = ?"
            );
            $stmt->execute([$dien_moi, $dien_moi, $nuoc_moi, $nuoc_moi, $phong_id]);
        } catch (\Throwable $e) {}
    }

    public function getLastInsertId(): int {
        return (int)$this->db->lastInsertId();
    }

    public function updateThanhToan(int $id, string $phuong_thuc = 'tien_mat', string $nguoi_thu = '', string $ghi_chu = ''): bool {
        $s = $this->db->prepare(
            "UPDATE hoa_don SET trang_thai='da_tt', phuong_thuc_tt=?, ngay_thanh_toan=NOW(), nguoi_thu=?, ghi_chu_tt=? WHERE id=?"
        );
        return $s->execute([$phuong_thuc, $nguoi_thu, $ghi_chu, $id]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM hoa_don WHERE id=?");
        return $s->execute([$id]);
    }

    public function getCongNo(): array {
        return $this->db->query(
            "SELECT hd.*, p.so_phong FROM hoa_don hd
             JOIN phong p ON hd.phong_id=p.id
             WHERE hd.trang_thai='chua_tt'
             ORDER BY hd.nam DESC, hd.thang DESC"
        )->fetchAll();
    }

    public function getDoanhThuTheoThang(int $nam): array {
        $s = $this->db->prepare(
            "SELECT thang, SUM(tong_tien) AS tong
             FROM hoa_don WHERE nam=? AND trang_thai='da_tt'
             GROUP BY thang ORDER BY thang"
        );
        $s->execute([$nam]);
        $rows = $s->fetchAll();
        $result = array_fill(1, 12, 0);
        foreach ($rows as $r) $result[(int)$r['thang']] = (float)$r['tong'];
        return $result;
    }

    public function getTongDoanhThuThang(int $thang, int $nam): float {
        $s = $this->db->prepare(
            "SELECT COALESCE(SUM(tong_tien),0) FROM hoa_don WHERE thang=? AND nam=? AND trang_thai='da_tt'"
        );
        $s->execute([$thang, $nam]);
        return (float)$s->fetchColumn();
    }

    /**
     * Điều kiện SQL "hóa đơn (alias $a) thuộc về tài khoản ?": tài khoản đó có hợp đồng CÙNG PHÒNG với hóa đơn
     * và kỳ hóa đơn (tháng/năm) nằm trong khoảng tháng bắt đầu → tháng kết thúc của hợp đồng.
     * Nhờ vậy người thuê mới không thấy hóa đơn / giao dịch của người thuê trước ở cùng phòng. Chứa đúng 1 dấu ?.
     */
    public static function dieuKienThuocTaiKhoan(string $a = 'hd'): string {
        return "EXISTS (
                    SELECT 1 FROM hop_dong hdg
                    JOIN nguoi_thue ntg ON ntg.id = hdg.nguoi_thue_id
                    WHERE ntg.account_id = ?
                      AND hdg.phong_id = $a.phong_id
                      AND ($a.nam * 12 + $a.thang)
                          BETWEEN (YEAR(hdg.ngay_bat_dau) * 12 + MONTH(hdg.ngay_bat_dau))
                              AND (YEAR(hdg.ngay_ket_thuc) * 12 + MONTH(hdg.ngay_ket_thuc)))";
    }

    /** Hóa đơn của người thuê (theo thời gian các hợp đồng của họ), kèm yêu cầu thanh toán đang chờ. */
    public function getByTaiKhoan(int $account_id): array {
        $s = $this->db->prepare(
            "SELECT hd.*, p.so_phong,
                    pend.pending_count, pend.pending_method, pend.pending_by, pend.pending_note
             FROM hoa_don hd
             JOIN phong p ON p.id = hd.phong_id
             LEFT JOIN (
                SELECT hoa_don_id,
                       COUNT(*) AS pending_count,
                       SUBSTRING_INDEX(GROUP_CONCAT(phuong_thuc ORDER BY created_at DESC), ',', 1) AS pending_method,
                       SUBSTRING_INDEX(GROUP_CONCAT(nguoi_tra ORDER BY created_at DESC), ',', 1) AS pending_by,
                       SUBSTRING_INDEX(GROUP_CONCAT(ghi_chu ORDER BY created_at DESC SEPARATOR '||'), '||', 1) AS pending_note
                FROM lich_su_thanh_toan
                WHERE trang_thai='dang_xu_ly'
                GROUP BY hoa_don_id
             ) pend ON pend.hoa_don_id = hd.id
             WHERE " . self::dieuKienThuocTaiKhoan('hd') . "
             ORDER BY hd.nam DESC, hd.thang DESC, hd.id DESC"
        );
        $s->execute([$account_id]);
        return $s->fetchAll();
    }

    /** Hóa đơn $hoa_don_id có thuộc về tài khoản này không (kiểm tra quyền xem / thanh toán của người thuê). */
    public function thuocTaiKhoan(int $hoa_don_id, int $account_id): bool {
        if ($hoa_don_id <= 0 || $account_id <= 0) return false;
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM hoa_don hd WHERE hd.id = ? AND " . self::dieuKienThuocTaiKhoan('hd')
        );
        $s->execute([$hoa_don_id, $account_id]);
        return (int)$s->fetchColumn() > 0;
    }

    public function getByPhongId(int $phong_id): array {
        $s = $this->db->prepare(
            "SELECT hd.*,
                    pend.pending_count, pend.pending_method, pend.pending_by, pend.pending_note
             FROM hoa_don hd
             LEFT JOIN (
                SELECT hoa_don_id,
                       COUNT(*) AS pending_count,
                       SUBSTRING_INDEX(GROUP_CONCAT(phuong_thuc ORDER BY created_at DESC), ',', 1) AS pending_method,
                       SUBSTRING_INDEX(GROUP_CONCAT(nguoi_tra ORDER BY created_at DESC), ',', 1) AS pending_by,
                       SUBSTRING_INDEX(GROUP_CONCAT(ghi_chu ORDER BY created_at DESC SEPARATOR '||'), '||', 1) AS pending_note
                FROM lich_su_thanh_toan
                WHERE trang_thai='dang_xu_ly'
                GROUP BY hoa_don_id
             ) pend ON pend.hoa_don_id = hd.id
             WHERE hd.phong_id=? ORDER BY hd.nam DESC, hd.thang DESC"
        );
        $s->execute([$phong_id]);
        return $s->fetchAll();
    }

    public function countChuaTT(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM hoa_don WHERE trang_thai='chua_tt'")->fetchColumn();
    }

    /**
     * Lấy chỉ số điện/nước cũ đã ghi nhớ của phòng (dùng làm chỉ số cũ cho lần quét / lập hóa đơn tiếp theo).
     * Nguyên tắc cuốn chiếu:
     * 1. Nếu tháng hiện tại đã từng quét/lập hóa đơn có chi_so_dien_moi > 0, hoặc kỳ trước có chi_so_dien_moi > 0:
     *    lấy chi_so_dien_moi gần nhất làm số cũ khi người dùng quét ảnh mới có chỉ số lớn hơn.
     * 2. Lấy từ cột ghi nhớ chỉ số trên bảng phong (chi_so_dien_hien_tai / chi_so_nuoc_hien_tai)
     * 3. Biên bản bàn giao nhận phòng gần nhất (nếu phòng mới thuê lần đầu)
     */
    public function getChiSoCuoiCung(int $phong_id, ?int $excludeThang = null, ?int $excludeNam = null): array {
        // Tìm chỉ số từ hóa đơn kỳ trước (khác tháng/năm hiện tại)
        $prevRow = null;
        if ($excludeThang !== null && $excludeNam !== null) {
            $s = $this->db->prepare(
                "SELECT chi_so_dien_cu, chi_so_dien_moi, chi_so_nuoc_cu, chi_so_nuoc_moi, thang, nam
                 FROM hoa_don
                 WHERE phong_id = ? AND NOT (thang = ? AND nam = ?)
                 ORDER BY nam DESC, thang DESC, id DESC
                 LIMIT 1"
            );
            $s->execute([$phong_id, $excludeThang, $excludeNam]);
            $prevRow = $s->fetch();
        }

        // Tìm hóa đơn mới nhất (kể cả tháng hiện tại nếu đã từng quét lần trước)
        $sLatest = $this->db->prepare(
            "SELECT chi_so_dien_cu, chi_so_dien_moi, chi_so_nuoc_cu, chi_so_nuoc_moi, thang, nam
             FROM hoa_don
             WHERE phong_id = ?
             ORDER BY nam DESC, thang DESC, id DESC
             LIMIT 1"
        );
        $sLatest->execute([$phong_id]);
        $latestRow = $sLatest->fetch();

        // Lấy chỉ số đang ghi nhớ trên bảng phong
        $pRow = null;
        try {
            $sp = $this->db->prepare("SELECT chi_so_dien_hien_tai, chi_so_nuoc_hien_tai FROM phong WHERE id = ? LIMIT 1");
            $sp->execute([$phong_id]);
            $pRow = $sp->fetch();
        } catch (\Throwable $e) {}

        // Lấy chỉ số từ biên bản bàn giao phòng
        $bbRow = null;
        try {
            $sbb = $this->db->prepare(
                "SELECT chi_so_dien, chi_so_nuoc
                 FROM bien_ban_ban_giao
                 WHERE phong_id = ?
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $sbb->execute([$phong_id]);
            $bbRow = $sbb->fetch();
        } catch (\Throwable $e) {}

        // Xác định chỉ số điện cũ & nước cũ từ kỳ trước
        $dienKyTruoc = 0.0;
        $nuocKyTruoc = 0.0;
        $nguon = 'Chưa có kỳ trước';

        if ($prevRow && ((float)$prevRow['chi_so_dien_moi'] > 0 || (float)$prevRow['chi_so_nuoc_moi'] > 0)) {
            $dienKyTruoc = (float)$prevRow['chi_so_dien_moi'];
            $nuocKyTruoc = (float)$prevRow['chi_so_nuoc_moi'];
            $nguon = "Hóa đơn T{$prevRow['thang']}/{$prevRow['nam']}";
        } elseif ($bbRow && ((float)$bbRow['chi_so_dien'] > 0 || (float)$bbRow['chi_so_nuoc'] > 0)) {
            $dienKyTruoc = (float)$bbRow['chi_so_dien'];
            $nuocKyTruoc = (float)$bbRow['chi_so_nuoc'];
            $nguon = 'Biên bản bàn giao phòng';
        }

        // Chỉ số công tơ gần nhất đã từng quét (kể cả vừa quét trong tháng này)
        $dienGanNhat = $dienKyTruoc;
        $nuocGanNhat = $nuocKyTruoc;
        if ($latestRow) {
            if ((float)$latestRow['chi_so_dien_moi'] > $dienGanNhat) {
                $dienGanNhat = (float)$latestRow['chi_so_dien_moi'];
                if ($nguon === 'Chưa có kỳ trước') {
                    $nguon = "Số công tơ đã nhớ (T{$latestRow['thang']}/{$latestRow['nam']})";
                }
            }
            if ((float)$latestRow['chi_so_nuoc_moi'] > $nuocGanNhat) {
                $nuocGanNhat = (float)$latestRow['chi_so_nuoc_moi'];
            }
        }
        if ($pRow) {
            if ((float)$pRow['chi_so_dien_hien_tai'] > $dienGanNhat) {
                $dienGanNhat = (float)$pRow['chi_so_dien_hien_tai'];
                if ($nguon === 'Chưa có kỳ trước') {
                    $nguon = 'Số công tơ đã nhớ của phòng';
                }
            }
            if ((float)$pRow['chi_so_nuoc_hien_tai'] > $nuocGanNhat) {
                $nuocGanNhat = (float)$pRow['chi_so_nuoc_hien_tai'];
            }
        }

        return [
            'chi_so_dien_moi'      => $dienGanNhat > 0 ? $dienGanNhat : $dienKyTruoc,
            'chi_so_nuoc_moi'      => $nuocGanNhat > 0 ? $nuocGanNhat : $nuocKyTruoc,
            'chi_so_dien_ky_truoc' => $dienKyTruoc,
            'chi_so_nuoc_ky_truoc' => $nuocKyTruoc,
            'chi_so_dien_gan_nhat' => $dienGanNhat,
            'chi_so_nuoc_gan_nhat' => $nuocGanNhat,
            'thang'                => (int)($prevRow['thang'] ?? ($latestRow['thang'] ?? 0)),
            'nam'                  => (int)($prevRow['nam']   ?? ($latestRow['nam']   ?? 0)),
            'nguon'                => $nguon
        ];
    }

    public function getLastByPhong(int $phong_id): array {
        return $this->getChiSoCuoiCung($phong_id);
    }
}
