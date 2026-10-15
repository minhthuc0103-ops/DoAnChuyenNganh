<?php
class PhongModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll(?int $khu_id = null, ?string $trang_thai = null): array {
        $where  = [];
        $params = [];
        if ($khu_id)     { $where[] = 'p.khu_id=?';     $params[] = $khu_id; }
        if ($trang_thai) { $where[] = 'p.trang_thai=?';  $params[] = $trang_thai; }
        $sql = "SELECT p.*, k.ten_khu FROM phong p LEFT JOIN khu_tro k ON p.khu_id=k.id"
             . ($where ? ' WHERE '.implode(' AND ', $where) : '')
             . " ORDER BY k.ten_khu, p.so_phong";
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare(
            "SELECT p.*, k.ten_khu FROM phong p
             LEFT JOIN khu_tro k ON p.khu_id=k.id WHERE p.id=?"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function getTrong(?int $khu_id = null): array {
        if ($khu_id) {
            $s = $this->db->prepare(
                "SELECT p.*, k.ten_khu FROM phong p
                 LEFT JOIN khu_tro k ON p.khu_id=k.id
                 WHERE p.trang_thai='trong' AND p.khu_id=? ORDER BY p.so_phong"
            );
            $s->execute([$khu_id]);
        } else {
            $s = $this->db->query(
                "SELECT p.*, k.ten_khu FROM phong p
                 LEFT JOIN khu_tro k ON p.khu_id=k.id
                 WHERE p.trang_thai='trong' ORDER BY k.ten_khu, p.so_phong"
            );
        }
        return $s->fetchAll();
    }

    public function countByTrangThai(?int $khu_id = null): array {
        if ($khu_id) {
            $s = $this->db->prepare(
                "SELECT trang_thai, COUNT(*) AS so_luong FROM phong WHERE khu_id=? GROUP BY trang_thai"
            );
            $s->execute([$khu_id]);
        } else {
            $s = $this->db->query(
                "SELECT trang_thai, COUNT(*) AS so_luong FROM phong GROUP BY trang_thai"
            );
        }
        $rows   = $s->fetchAll();
        $result = ['trong'=>0,'dang_thue'=>0,'bao_tri'=>0];
        foreach ($rows as $r) $result[$r['trang_thai']] = (int)$r['so_luong'];
        return $result;
    }

    public function existsSoPhong(string $so_phong, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM phong WHERE so_phong=?";
        $params = [$so_phong];
        if ($excludeId) { $sql .= " AND id != ?"; $params[] = $excludeId; }
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return (int)$s->fetchColumn() > 0;
    }

    public function create(string $so_phong, float $gia, float $dien_tich, int $so_nguoi, string $mo_ta, ?int $khu_id=null, string $anh_phong=''): bool {
        $s = $this->db->prepare(
            "INSERT INTO phong (khu_id,so_phong,gia,dien_tich,so_nguoi,mo_ta,trang_thai,anh_phong) VALUES(?,?,?,?,?,?,'trong',?)"
        );
        return $s->execute([$khu_id, $so_phong, $gia, $dien_tich, $so_nguoi, $mo_ta, $anh_phong]);
    }

    public function update(int $id, string $so_phong, float $gia, float $dien_tich, int $so_nguoi, string $mo_ta, string $trang_thai, ?int $khu_id=null, string $anh_phong=''): bool {
        $s = $this->db->prepare(
            "UPDATE phong SET khu_id=?,so_phong=?,gia=?,dien_tich=?,so_nguoi=?,mo_ta=?,trang_thai=?,anh_phong=? WHERE id=?"
        );
        return $s->execute([$khu_id, $so_phong, $gia, $dien_tich, $so_nguoi, $mo_ta, $trang_thai, $anh_phong, $id]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM phong WHERE id=?");
        return $s->execute([$id]);
    }

    public function updateTrangThai(int $id, string $tt): bool {
        $s = $this->db->prepare("UPDATE phong SET trang_thai=? WHERE id=?");
        return $s->execute([$tt, $id]);
    }
}