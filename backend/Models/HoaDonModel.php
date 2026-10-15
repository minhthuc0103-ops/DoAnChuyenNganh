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
             (phong_id,thang,nam,chi_so_dien_cu,chi_so_dien_moi,chi_so_nuoc_cu,chi_so_nuoc_moi,
              tien_phong,tien_dien,tien_nuoc,phi_dich_vu,phi_xe,so_xe,tong_tien,trang_thai)
             VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,'chua_tt')"
        );
        return $s->execute([
            $d['phong_id'],$d['thang'],$d['nam'],
            $d['chi_so_dien_cu'],$d['chi_so_dien_moi'],
            $d['chi_so_nuoc_cu'],$d['chi_so_nuoc_moi'],
            $d['tien_phong'],$d['tien_dien'],$d['tien_nuoc'],
            $d['phi_dich_vu'] ?? 0,
            $d['phi_xe'] ?? 0,
            $d['so_xe'] ?? 0,
            $d['tong_tien'],
        ]);
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
     * Lấy chỉ số điện/nước mới nhất của phòng (dùng làm chỉ số cũ cho hóa đơn tiếp theo)
     */
    public function getChiSoCuoiCung(int $phong_id): array {
        $s = $this->db->prepare(
            "SELECT chi_so_dien_moi, chi_so_nuoc_moi, thang, nam
             FROM hoa_don
             WHERE phong_id = ?
             ORDER BY nam DESC, thang DESC
             LIMIT 1"
        );
        $s->execute([$phong_id]);
        $row = $s->fetch();
        return $row ?: ['chi_so_dien_moi' => 0, 'chi_so_nuoc_moi' => 0, 'thang' => 0, 'nam' => 0];
    }
}
