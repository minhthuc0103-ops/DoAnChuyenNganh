<?php
class XeModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getByHopDong(int $hop_dong_id): array {
        $s = $this->db->prepare("SELECT * FROM xe WHERE hop_dong_id=? ORDER BY id");
        $s->execute([$hop_dong_id]);
        return $s->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM xe WHERE id=?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function countByHopDong(int $hop_dong_id): int {
        $s = $this->db->prepare("SELECT COUNT(*) FROM xe WHERE hop_dong_id=?");
        $s->execute([$hop_dong_id]);
        return (int)$s->fetchColumn();
    }

    public function create(array $d): bool {
        $s = $this->db->prepare(
            "INSERT INTO xe (hop_dong_id,phong_id,bien_so,loai_xe,mau_sac,ghi_chu) VALUES(?,?,?,?,?,?)"
        );
        return $s->execute([
            $d['hop_dong_id'], $d['phong_id'],
            $d['bien_so'], $d['loai_xe'],
            $d['mau_sac'] ?? '', $d['ghi_chu'] ?? '',
        ]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM xe WHERE id=?");
        return $s->execute([$id]);
    }
}
