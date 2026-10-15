<?php

class LichSuThanhToanModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $limit = 100): array
    {
        $s = $this->db->query(
            "SELECT ls.*, p.so_phong, k.ten_khu
             FROM lich_su_thanh_toan ls
             JOIN phong p ON ls.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             ORDER BY ls.created_at DESC
             LIMIT " . (int)$limit
        );
        return $s->fetchAll();
    }

    public function getByPhong(int $phong_id, int $limit = 50): array
    {
        $s = $this->db->prepare(
            "SELECT ls.*, p.so_phong
             FROM lich_su_thanh_toan ls
             JOIN phong p ON ls.phong_id = p.id
             WHERE ls.phong_id = ?
             ORDER BY ls.created_at DESC
             LIMIT " . (int)$limit
        );
        $s->execute([$phong_id]);
        return $s->fetchAll();
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
             (hoa_don_id, phong_id, so_tien, phuong_thuc, trang_thai, nguoi_thu, nguoi_tra, ghi_chu)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
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
        ]);
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
