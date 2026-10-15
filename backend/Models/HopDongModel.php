<?php
class HopDongModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll(?string $trangThai = null): array {
        $sql = "SELECT hd.*, p.so_phong,
                    nt.ho_ten, nt.sdt, nt.cccd, nt.dia_chi, nt.avatar
             FROM hop_dong hd
             JOIN phong p       ON hd.phong_id=p.id
             JOIN nguoi_thue nt ON hd.nguoi_thue_id=nt.id";
        if ($trangThai) {
            $sql .= " WHERE hd.trang_thai = ?";
            $sql .= " ORDER BY hd.created_at DESC";
            $s = $this->db->prepare($sql);
            $s->execute([$trangThai]);
            return $s->fetchAll();
        }
        $sql .= " ORDER BY hd.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): array|false {
        $s = $this->db->prepare(
            "SELECT hd.*, p.so_phong,
                    nt.ho_ten, nt.sdt, nt.cccd, nt.dia_chi, nt.avatar
             FROM hop_dong hd
             JOIN phong p       ON hd.phong_id=p.id
             JOIN nguoi_thue nt ON hd.nguoi_thue_id=nt.id
             WHERE hd.id=?"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function getSapHetHan(int $days = 30): array {
        return $this->db->query(
            "SELECT hd.*, p.so_phong, nt.ho_ten
             FROM hop_dong hd
             JOIN phong p ON hd.phong_id=p.id
             JOIN nguoi_thue nt ON hd.nguoi_thue_id=nt.id
             WHERE hd.trang_thai='hieu_luc'
               AND hd.ngay_ket_thuc BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $days DAY)
             ORDER BY hd.ngay_ket_thuc"
        )->fetchAll();
    }

    public function create(int $phong_id, int $nguoi_thue_id, string $ngay_bd, string $ngay_kt, float $tien_coc, string $ghi_chu, array $phi=[], string $noi_dung=''): bool {
        $s = $this->db->prepare(
            "INSERT INTO hop_dong (phong_id,nguoi_thue_id,ngay_bat_dau,ngay_ket_thuc,tien_coc,ghi_chu,noi_dung)
             VALUES(?,?,?,?,?,?,?)"
        );
        return $s->execute([
            $phong_id, $nguoi_thue_id, $ngay_bd, $ngay_kt, $tien_coc, $ghi_chu, $noi_dung,
        ]);
    }

    public function getLastInsertId(): int {
        return (int)$this->db->lastInsertId();
    }

    public function updateTrangThai(int $id, string $tt): bool {
        $s = $this->db->prepare("UPDATE hop_dong SET trang_thai=? WHERE id=?");
        return $s->execute([$tt, $id]);
    }

    // Người thuê báo hủy hợp đồng
    public function baoCaoHuy(int $id, string $ngayDuKienRa): bool {
        $s = $this->db->prepare(
            "UPDATE hop_dong SET yeu_cau_huy=1, ngay_bao_huy=CURDATE(), ngay_du_kien_ra=? WHERE id=?"
        );
        return $s->execute([$ngayDuKienRa, $id]);
    }

    // Admin xác nhận kết thúc HĐ (sau khi người thuê đã báo)
    public function ketThuc(int $id): bool {
        $s = $this->db->prepare(
            "UPDATE hop_dong SET trang_thai='het_han' WHERE id=?"
        );
        return $s->execute([$id]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM hop_dong WHERE id=?");
        return $s->execute([$id]);
    }

    public function countHieuLuc(): int {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM hop_dong WHERE trang_thai='hieu_luc'"
        )->fetchColumn();
    }
    public function countByNguoiThue(int $nguoi_thue_id): int {
        $s = $this->db->prepare("SELECT COUNT(*) FROM hop_dong WHERE nguoi_thue_id=?");
        $s->execute([$nguoi_thue_id]);
        return (int)$s->fetchColumn();
    }

    /**
     * Tự động xử lý trạng thái HĐ:
     * - Chưa báo hủy + hết hạn → gia hạn thêm 1 tháng (tự động)
     * - Đã báo hủy + hôm nay >= ngày 25 tháng báo → phòng bao_tri
     * - Đã báo hủy + hôm nay >= ngày 5 tháng kế → het_han, phòng trong
     */
    public function autoProcess(): void {
        $today = new DateTime();

        // 1. HĐ hieu_luc đã báo hủy → xử lý theo ngày
        $stmt = $this->db->query(
            "SELECT hd.*, p.id AS pid
             FROM hop_dong hd
             JOIN phong p ON p.id = hd.phong_id
             WHERE hd.trang_thai = 'hieu_luc'
               AND hd.yeu_cau_huy = 1
               AND hd.ngay_du_kien_ra IS NOT NULL"
        );
        foreach ($stmt->fetchAll() as $hd) {
            $ngayRa = new DateTime($hd['ngay_du_kien_ra']); // ngày 25
            $nextMonth = (clone $ngayRa)->modify('+1 month');
            $ngayKetThuc = new DateTime($nextMonth->format('Y-m') . '-05');

            if ($today >= $ngayKetThuc) {
                // Kết thúc HĐ + phòng về trống
                $this->db->prepare("UPDATE hop_dong SET trang_thai='het_han' WHERE id=?")
                    ->execute([$hd['id']]);
                $this->db->prepare("UPDATE phong SET trang_thai='trong' WHERE id=?")
                    ->execute([$hd['pid']]);
            } elseif ($today >= $ngayRa) {
                // Chuyển phòng sang bảo trì
                $this->db->prepare("UPDATE phong SET trang_thai='bao_tri' WHERE id=? AND trang_thai='dang_thue'")
                    ->execute([$hd['pid']]);
            }
        }

        // 2. HĐ hieu_luc chưa báo hủy mà đã qua ngày kết thúc → gia hạn thêm 1 tháng
        $this->db->prepare(
            "UPDATE hop_dong
             SET ngay_ket_thuc = DATE_ADD(ngay_ket_thuc, INTERVAL 1 MONTH)
             WHERE trang_thai = 'hieu_luc'
               AND (yeu_cau_huy = 0 OR yeu_cau_huy IS NULL)
               AND ngay_ket_thuc < CURDATE()"
        )->execute();
    }

    // Người thuê/admin báo hủy HĐ — ngay_du_kien_ra = ngày 25 tháng báo
    // Admin force hủy (bất kể ngày)
    public function forceBaoCaoHuy(int $id): bool {
        $ngayRa = (new DateTime())->format('Y-m') . '-25';
        $s = $this->db->prepare(
            "UPDATE hop_dong SET yeu_cau_huy=1, ngay_bao_huy=CURDATE(), ngay_du_kien_ra=? WHERE id=?"
        );
        return $s->execute([$ngayRa, $id]);
    }

}