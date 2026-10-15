<?php
class NguoiThueModel {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureAccountLifecycleColumns();
    }

    private function ensureAccountLifecycleColumns(): void {
        $columns = [
            'account_status' => "ENUM('dang_hoat_dong','ngung_hoat_dong','luu_tru') NOT NULL DEFAULT 'dang_hoat_dong'",
            'account_status_changed_at' => 'DATETIME NULL',
            'account_status_reason' => 'VARCHAR(255) NULL',
        ];

        foreach ($columns as $column => $definition) {
            $s = $this->db->prepare("SHOW COLUMNS FROM account LIKE ?");
            $s->execute([$column]);
            if (!$s->fetch()) {
                $this->db->exec("ALTER TABLE account ADD COLUMN {$column} {$definition}");
            }
        }
    }

    // Lấy danh sách người thuê kèm thông tin phòng/HĐ hiện tại
    public function getAll(string $kw = ''): array {
        $sql = "SELECT nt.*,
                    a.account_status,
                    a.account_status_reason,
                    hd.id          AS hop_dong_id,
                    hd.trang_thai  AS hd_trang_thai,
                    hd.ngay_ket_thuc,
                    hd.id          AS hd_id,
                    p.so_phong,
                    p.id           AS phong_id,
                    k.ten_khu
                FROM nguoi_thue nt
                LEFT JOIN account a
                    ON a.id = nt.account_id
                LEFT JOIN hop_dong hd
                    ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
                LEFT JOIN phong p   ON hd.phong_id = p.id
                LEFT JOIN khu_tro k ON p.khu_id = k.id";
        if ($kw) {
            $sql .= " WHERE nt.ho_ten LIKE ?
                       OR nt.cccd LIKE ?
                       OR nt.sdt LIKE ?
                       OR nt.dia_chi LIKE ?
                       OR a.username LIKE ?
                       OR a.email LIKE ?
                       OR p.so_phong LIKE ?
                       OR k.ten_khu LIKE ?";
            $s = $this->db->prepare($sql . " ORDER BY nt.ho_ten");
            $k = "%$kw%";
            $s->execute([$k, $k, $k, $k, $k, $k, $k, $k]);
        } else {
            $s = $this->db->query($sql . " ORDER BY nt.ho_ten");
        }
        return $s->fetchAll();
    }

    public function quickSearch(string $kw, int $limit = 8): array {
        $kw = trim($kw);
        if ($kw === '') return [];

        $sql = "SELECT * FROM (
                SELECT
                    'tenant' AS result_type,
                    nt.id,
                    nt.id AS nguoi_thue_id,
                    NULL AS nguoi_o_cung_id,
                    nt.ho_ten, nt.sdt, nt.cccd, nt.dia_chi,
                    hd.id AS hop_dong_id, hd.trang_thai AS hd_trang_thai,
                    hd.ngay_bat_dau, hd.ngay_ket_thuc,
                    p.id AS phong_id, p.so_phong,
                    k.ten_khu,
                    a.username, a.email AS account_email,
                    1 AS la_chu_hop_dong
                FROM nguoi_thue nt
                LEFT JOIN account a ON a.id = nt.account_id
                LEFT JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
                LEFT JOIN phong p ON hd.phong_id = p.id
                LEFT JOIN khu_tro k ON p.khu_id = k.id
                WHERE nt.ho_ten LIKE ?
                   OR nt.sdt LIKE ?
                   OR nt.cccd LIKE ?
                   OR nt.dia_chi LIKE ?
                   OR a.username LIKE ?
                   OR a.email LIKE ?
                   OR p.so_phong LIKE ?
                   OR k.ten_khu LIKE ?
                UNION ALL
                SELECT
                    'roommate' AS result_type,
                    ntp.id AS id,
                    hd.nguoi_thue_id,
                    ntp.id AS nguoi_o_cung_id,
                    ntp.ho_ten, ntp.sdt, ntp.cccd, ntp.que_quan AS dia_chi,
                    hd.id AS hop_dong_id, hd.trang_thai AS hd_trang_thai,
                    hd.ngay_bat_dau, hd.ngay_ket_thuc,
                    p.id AS phong_id, p.so_phong,
                    k.ten_khu,
                    NULL AS username, NULL AS account_email,
                    ntp.la_chu_hop_dong
                FROM nguoi_thue_phong ntp
                JOIN hop_dong hd ON hd.id = ntp.hop_dong_id
                LEFT JOIN phong p ON ntp.phong_id = p.id
                LEFT JOIN khu_tro k ON p.khu_id = k.id
                WHERE ntp.la_chu_hop_dong = 0
                  AND (
                    ntp.ho_ten LIKE ?
                    OR ntp.sdt LIKE ?
                    OR ntp.cccd LIKE ?
                    OR ntp.que_quan LIKE ?
                    OR p.so_phong LIKE ?
                    OR k.ten_khu LIKE ?
                  )
                ) results
                ORDER BY
                    CASE
                        WHEN ho_ten LIKE ? THEN 0
                        WHEN so_phong LIKE ? THEN 1
                        WHEN sdt LIKE ? THEN 2
                        ELSE 3
                    END,
                    result_type,
                    ho_ten
                LIMIT " . max(1, min(20, $limit));

        $like = "%$kw%";
        $prefix = "$kw%";
        $s = $this->db->prepare($sql);
        $s->execute([
            $like, $like, $like, $like, $like, $like, $like, $like,
            $like, $like, $like, $like, $like, $like,
            $prefix, $prefix, $prefix
        ]);
        return $s->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare(
            "SELECT nt.*,
                    hd.id AS hop_dong_id, hd.trang_thai AS hd_trang_thai,
                    hd.ngay_ket_thuc, p.so_phong, p.id AS phong_id, k.ten_khu
             FROM nguoi_thue nt
             LEFT JOIN hop_dong hd ON hd.nguoi_thue_id=nt.id AND hd.trang_thai='hieu_luc'
             LEFT JOIN phong p ON hd.phong_id=p.id
             LEFT JOIN khu_tro k ON p.khu_id=k.id
             WHERE nt.id=?"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create(string $ho_ten, string $cccd, string $sdt, string $dia_chi): bool {
        $s = $this->db->prepare(
            "INSERT INTO nguoi_thue (ho_ten,cccd,sdt,dia_chi) VALUES(?,?,?,?)"
        );
        return $s->execute([$ho_ten, $cccd, $sdt, $dia_chi]);
    }

    public function getLastId(): int {
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $ho_ten, string $cccd, string $sdt, string $dia_chi, string $avatar = ''): bool {
        $s = $this->db->prepare(
            "UPDATE nguoi_thue SET ho_ten=?,cccd=?,sdt=?,dia_chi=?,avatar=? WHERE id=?"
        );
        return $s->execute([$ho_ten, $cccd, $sdt, $dia_chi, $avatar, $id]);
    }

    // Xóa mềm - chỉ xóa khi không còn HĐ
    public function delete(int $id): bool {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM hop_dong WHERE nguoi_thue_id=? AND trang_thai='hieu_luc'"
        );
        $s->execute([$id]);
        if ($s->fetchColumn() > 0) return false;
        $s = $this->db->prepare("DELETE FROM nguoi_thue WHERE id=?");
        return $s->execute([$id]);
    }

    // Xóa cứng - dùng khi xóa HĐ
    public function forceDelete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM nguoi_thue WHERE id=?");
        return $s->execute([$id]);
    }

    public function getByAccountId(int $account_id): array|false {
        $s = $this->db->prepare(
            "SELECT nt.*,
                    hd.id AS hop_dong_id, hd.trang_thai AS hd_trang_thai,
                    hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.tien_coc, hd.ghi_chu,
                    p.so_phong, p.id AS phong_id, p.gia,
                    k.ten_khu
             FROM nguoi_thue nt
             LEFT JOIN hop_dong hd ON hd.nguoi_thue_id=nt.id AND hd.trang_thai='hieu_luc'
             LEFT JOIN phong p ON hd.phong_id=p.id
             LEFT JOIN khu_tro k ON p.khu_id=k.id
             WHERE nt.account_id=?
             ORDER BY hd.id IS NULL, hd.id DESC, nt.id DESC
             LIMIT 1"
        );
        $s->execute([$account_id]);
        return $s->fetch();
    }

    public function linkAccount(int $id, ?int $account_id): bool {
        $s = $this->db->prepare("UPDATE nguoi_thue SET account_id=? WHERE id=?");
        return $s->execute([$account_id ?: null, $id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM nguoi_thue")->fetchColumn();
    }

    public function countDangThue(): int {
        return (int)$this->db->query(
            "SELECT COUNT(DISTINCT nguoi_thue_id) FROM hop_dong WHERE trang_thai='hieu_luc'"
        )->fetchColumn();
    }
}
