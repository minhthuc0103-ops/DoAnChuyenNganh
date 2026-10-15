<?php

class LichSuGiaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByPhong(int $phong_id): array
    {
        $s = $this->db->prepare(
            "SELECT * FROM lich_su_gia WHERE phong_id=? ORDER BY ngay_thay_doi DESC, id DESC"
        );
        $s->execute([$phong_id]);
        return $s->fetchAll();
    }

    public function getAll(int $limit = 50): array
    {
        $s = $this->db->query(
            "SELECT lsg.*, p.so_phong, k.ten_khu
             FROM lich_su_gia lsg
             JOIN phong p ON lsg.phong_id = p.id
             LEFT JOIN khu_tro k ON p.khu_id = k.id
             ORDER BY lsg.created_at DESC
             LIMIT " . (int)$limit
        );
        return $s->fetchAll();
    }

    public function create(int $phong_id, float $gia_cu, float $gia_moi, string $ngay, string $ghi_chu = '', string $nguoi = ''): bool
    {
        $s = $this->db->prepare(
            "INSERT INTO lich_su_gia (phong_id, gia_cu, gia_moi, ngay_thay_doi, ghi_chu, nguoi_thay_doi)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $s->execute([$phong_id, $gia_cu, $gia_moi, $ngay, $ghi_chu, $nguoi]);
    }
}
