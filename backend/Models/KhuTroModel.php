<?php
class KhuTroModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll(): array {
        return $this->db->query(
            "SELECT k.*,
                    COUNT(p.id) AS so_phong,
                    SUM(CASE WHEN p.trang_thai='dang_thue' THEN 1 ELSE 0 END) AS dang_thue,
                    SUM(CASE WHEN p.trang_thai='trong'     THEN 1 ELSE 0 END) AS phong_trong
             FROM khu_tro k
             LEFT JOIN phong p ON p.khu_id=k.id
             GROUP BY k.id ORDER BY k.ma_khu"
        )->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM khu_tro WHERE id=?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function getMaKhu(int $id): string {
        $s = $this->db->prepare("SELECT ma_khu FROM khu_tro WHERE id=?");
        $s->execute([$id]);
        return (string)($s->fetchColumn() ?: '');
    }

    // Tạo số phòng tự động: A101, A102...
    public function genSoPhong(int $khu_id, int $tang = 1): string {
        $ma = $this->getMaKhu($khu_id);
        // Đếm phòng hiện có trong khu
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM phong WHERE khu_id=? AND so_phong LIKE ?"
        );
        $s->execute([$khu_id, $ma.$tang.'%']);
        $count = (int)$s->fetchColumn();
        return $ma . $tang . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    // Lấy tầng tiếp theo tự động: A101, A201...
    public function getNextSoPhong(int $khu_id): string {
        $ma = $this->getMaKhu($khu_id);
        $s  = $this->db->prepare(
            "SELECT so_phong FROM phong WHERE khu_id=? AND so_phong LIKE ? ORDER BY so_phong DESC LIMIT 1"
        );
        $s->execute([$khu_id, $ma.'%']);
        $last = $s->fetchColumn();
        if (!$last) return $ma . '101';
        // Tách số cuối
        $num = (int)substr($last, strlen($ma));
        return $ma . ($num + 1);
    }

    public function create(string $ten, string $ma_khu, string $dia_chi, string $mo_ta, ?int $quan_ly_id=null): bool {
        $s = $this->db->prepare(
            "INSERT INTO khu_tro (ten_khu, ma_khu, dia_chi, mo_ta, quan_ly_id) VALUES(?,?,?,?,?)"
        );
        return $s->execute([strtoupper(trim($ten)), strtoupper(trim($ma_khu)), $dia_chi, $mo_ta, $quan_ly_id]);
    }

    public function update(int $id, string $ten, string $ma_khu, string $dia_chi, string $mo_ta): bool {
        $s = $this->db->prepare(
            "UPDATE khu_tro SET ten_khu=?, ma_khu=?, dia_chi=?, mo_ta=? WHERE id=?"
        );
        return $s->execute([strtoupper(trim($ten)), strtoupper(trim($ma_khu)), $dia_chi, $mo_ta, $id]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM khu_tro WHERE id=?");
        return $s->execute([$id]);
    }

    public function getLastInsertId(): int {
        return (int)$this->db->lastInsertId();
    }
}