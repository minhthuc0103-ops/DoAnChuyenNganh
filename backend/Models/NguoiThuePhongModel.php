<?php
class NguoiThuePhongModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // Lấy tất cả người thuê trong 1 hợp đồng/phòng
    public function getByHopDong(int $hop_dong_id): array {
        $s = $this->db->prepare(
            "SELECT * FROM nguoi_thue_phong WHERE hop_dong_id=? ORDER BY la_chu_hop_dong DESC, id ASC"
        );
        $s->execute([$hop_dong_id]);
        return $s->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM nguoi_thue_phong WHERE id=?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create(array $d): bool {
        $s = $this->db->prepare(
            "INSERT INTO nguoi_thue_phong
             (hop_dong_id,phong_id,ho_ten,cccd,sdt,ngay_sinh,gioi_tinh,que_quan,avatar,la_chu_hop_dong)
             VALUES(?,?,?,?,?,?,?,?,?,?)"
        );
        return $s->execute([
            $d['hop_dong_id'], $d['phong_id'],
            $d['ho_ten'], $d['cccd'], $d['sdt'],
            $d['ngay_sinh'] ?: null,
            $d['gioi_tinh'], $d['que_quan'],
            $d['avatar'] ?? '', $d['la_chu_hop_dong'] ?? 0,
        ]);
    }

    public function update(int $id, array $d): bool {
        $s = $this->db->prepare(
            "UPDATE nguoi_thue_phong SET
             ho_ten=?,cccd=?,sdt=?,ngay_sinh=?,gioi_tinh=?,que_quan=?,avatar=?
             WHERE id=?"
        );
        return $s->execute([
            $d['ho_ten'], $d['cccd'], $d['sdt'],
            $d['ngay_sinh'] ?: null,
            $d['gioi_tinh'], $d['que_quan'],
            $d['avatar'] ?? '', $id,
        ]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM nguoi_thue_phong WHERE id=?");
        return $s->execute([$id]);
    }

    public function countByHopDong(int $hop_dong_id): int {
        $s = $this->db->prepare("SELECT COUNT(*) FROM nguoi_thue_phong WHERE hop_dong_id=?");
        $s->execute([$hop_dong_id]);
        return (int)$s->fetchColumn();
    }
}