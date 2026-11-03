<?php
class BaoChiSoModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO bao_chi_so_dien_nuoc 
                (phong_id, nguoi_thue_id, thang, nam, chi_so_dien, chi_so_nuoc, anh_cong_to_dien, anh_cong_to_nuoc, ghi_chu, trang_thai)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['phong_id'],
            $data['nguoi_thue_id'],
            $data['thang'],
            $data['nam'],
            $data['chi_so_dien'] ?? 0,
            $data['chi_so_nuoc'] ?? 0,
            $data['anh_cong_to_dien'] ?? null,
            $data['anh_cong_to_nuoc'] ?? null,
            $data['ghi_chu'] ?? '',
            $data['trang_thai'] ?? 'cho_duyet'
        ]);
    }

    public function getByPhongThangNam(int $phong_id, int $thang, int $nam) {
        $stmt = $this->db->prepare("
            SELECT b.*, nt.ho_ten AS ten_nguoi_thue, nt.sdt, p.so_phong
            FROM bao_chi_so_dien_nuoc b
            JOIN nguoi_thue nt ON b.nguoi_thue_id = nt.id
            JOIN phong p ON b.phong_id = p.id
            WHERE b.phong_id = ? AND b.thang = ? AND b.nam = ?
            ORDER BY b.id DESC
            LIMIT 1
        ");
        $stmt->execute([$phong_id, $thang, $nam]);
        return $stmt->fetch();
    }

    public function getByNguoiThue(int $nguoi_thue_id): array {
        $stmt = $this->db->prepare("
            SELECT b.*, p.so_phong, k.ten_khu
            FROM bao_chi_so_dien_nuoc b
            JOIN phong p ON b.phong_id = p.id
            LEFT JOIN khu_tro k ON p.khu_id = k.id
            WHERE b.nguoi_thue_id = ?
            ORDER BY b.nam DESC, b.thang DESC, b.id DESC
        ");
        $stmt->execute([$nguoi_thue_id]);
        return $stmt->fetchAll();
    }

    public function getAllPending(): array {
        $stmt = $this->db->query("
            SELECT b.*, nt.ho_ten AS ten_nguoi_thue, nt.sdt, p.so_phong, k.ten_khu
            FROM bao_chi_so_dien_nuoc b
            JOIN nguoi_thue nt ON b.nguoi_thue_id = nt.id
            JOIN phong p ON b.phong_id = p.id
            LEFT JOIN khu_tro k ON p.khu_id = k.id
            WHERE b.trang_thai = 'cho_duyet'
            ORDER BY b.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE bao_chi_so_dien_nuoc SET trang_thai = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("
            SELECT b.*, nt.ho_ten, nt.sdt, p.so_phong, k.ten_khu
            FROM bao_chi_so_dien_nuoc b
            JOIN nguoi_thue nt ON b.nguoi_thue_id = nt.id
            JOIN phong p ON b.phong_id = p.id
            LEFT JOIN khu_tro k ON p.khu_id = k.id
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

