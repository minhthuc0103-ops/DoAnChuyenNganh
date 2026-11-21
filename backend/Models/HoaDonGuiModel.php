<?php
/**
 * Nhật ký gửi hóa đơn / biên nhận (email, Zalo): để chủ trọ biết đã gửi chưa, lỗi gì.
 */
class HoaDonGuiModel
{
    private PDO $db;
    private static bool $schemaOk = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (!self::$schemaOk) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `hoa_don_gui` (
                    `id`          INT AUTO_INCREMENT PRIMARY KEY,
                    `hoa_don_id`  INT NOT NULL,
                    `loai`        ENUM('hoa_don','bien_nhan') NOT NULL DEFAULT 'hoa_don',
                    `kenh`        ENUM('email','zalo') NOT NULL,
                    `trang_thai`  ENUM('thanh_cong','that_bai','bo_qua') NOT NULL,
                    `nguoi_nhan`  VARCHAR(150) NOT NULL DEFAULT '',
                    `chi_tiet`    VARCHAR(500) NOT NULL DEFAULT '',
                    `tu_dong`     TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    KEY `idx_hoa_don` (`hoa_don_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            self::$schemaOk = true;
        }
    }

    public function ghi(int $hoaDonId, string $loai, string $kenh, string $trangThai, string $nguoiNhan, string $chiTiet, bool $tuDong): void
    {
        $this->db->prepare(
            "INSERT INTO hoa_don_gui (hoa_don_id, loai, kenh, trang_thai, nguoi_nhan, chi_tiet, tu_dong) VALUES (?,?,?,?,?,?,?)"
        )->execute([
            $hoaDonId, $loai, $kenh, $trangThai,
            mb_substr($nguoiNhan, 0, 150, 'UTF-8'), mb_substr($chiTiet, 0, 500, 'UTF-8'), $tuDong ? 1 : 0,
        ]);
    }

    /**
     * Lần gửi gần nhất của mỗi hóa đơn theo từng kênh (cả hóa đơn lẫn biên nhận).
     * @param int[] $ids
     * @return array<int, array{email?:array, zalo?:array}>
     */
    public function moiNhat(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $s = $this->db->prepare(
            "SELECT g.* FROM hoa_don_gui g
             JOIN (SELECT MAX(id) AS mid FROM hoa_don_gui WHERE hoa_don_id IN ($in) GROUP BY hoa_don_id, kenh) m ON m.mid = g.id"
        );
        $s->execute($ids);
        $ra = [];
        foreach ($s->fetchAll() as $r) {
            $ra[(int)$r['hoa_don_id']][$r['kenh']] = $r;
        }
        return $ra;
    }
}