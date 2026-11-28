<?php

/**
 * Thông báo gửi người thuê.
 *
 *  - Thông báo CHUNG (admin soạn): rieng_tu = 0, mọi người thuê đều thấy.
 *  - Thông báo RIÊNG (hóa đơn, chuyển phòng, người ở cùng...): rieng_tu = 1, chỉ những tài khoản có tên trong bảng
 *    thong_bao_nguoi_nhan mới thấy. Trước đây "gửi riêng" bằng cách đánh dấu đã đọc sẵn cho mọi người khác, nhưng
 *    "đã đọc" và "bị ẩn" lẫn vào nhau và tài khoản tạo sau vẫn thấy, nên không dùng cách đó để liệt kê được.
 *
 * Cấu trúc DB do migration upgrade_thongbao_hoadon.sql (Database.bat) tạo; phần dưới chỉ là dự phòng cho máy chưa chạy.
 */
class ThongBaoModel
{
    private PDO $db;
    private static bool $schemaOk = false;
    /** Đủ cột/bảng mới chưa. Nếu không tạo được thì tự lùi về chế độ an toàn (ẩn thông báo riêng), không làm lỗi cả trang. */
    private static bool $coCot = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        if (self::$schemaOk) return;
        self::$schemaOk = true;   // mỗi request chỉ thử 1 lần, dù thành công hay không
        $vuaThemHoaDon = $vuaThemRieng = false;
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM thong_bao")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('hoa_don_id', $cols, true)) {
                $this->db->exec("ALTER TABLE thong_bao ADD COLUMN hoa_don_id INT NULL, ADD INDEX idx_tb_hoa_don (hoa_don_id)");
                $vuaThemHoaDon = true;
            }
            if (!in_array('rieng_tu', $cols, true)) {
                // NULL = thông báo cũ chưa phân loại (migration sẽ điền); thông báo mới luôn ghi rõ 0 hoặc 1
                $this->db->exec("ALTER TABLE thong_bao ADD COLUMN rieng_tu TINYINT(1) NULL DEFAULT NULL");
                $vuaThemRieng = true;
            }
            if (!$this->db->query("SHOW TABLES LIKE 'thong_bao_nguoi_nhan'")->fetch()) {
                $this->db->exec(
                    "CREATE TABLE IF NOT EXISTS thong_bao_nguoi_nhan (
                        thong_bao_id INT NOT NULL,
                        user_id      INT NOT NULL,
                        PRIMARY KEY (thong_bao_id, user_id),
                        KEY idx_tbnn_user (user_id),
                        CONSTRAINT fk_tbnn_tb   FOREIGN KEY (thong_bao_id) REFERENCES thong_bao(id) ON DELETE CASCADE,
                        CONSTRAINT fk_tbnn_user FOREIGN KEY (user_id)      REFERENCES account(id)   ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                );
            }
            self::$coCot = true;
        } catch (\Throwable $e) {
            self::$coCot = false;
            error_log('ThongBaoModel: khong tao duoc cau truc moi, chuyen sang che do an toan: ' . $e->getMessage());
            return;
        }

        // Điền dữ liệu cũ đúng 1 lần, ngay khi vừa thêm cột (tách riêng để lỗi ở đây không làm mất cấu trúc;
        // nếu lỗi thì chạy migration upgrade_thongbao_hoadon.sql, các câu lệnh đều chạy lại an toàn)
        // Mỗi bước 1 khối try riêng: bước này lỗi không được làm bỏ qua bước sau (nhất là phân loại rieng_tu).
        $buoc = function (string $moTa, string $sql): void {
            try { $this->db->exec($sql); } catch (\Throwable $e) { error_log("ThongBaoModel: loi buoc '$moTa': " . $e->getMessage()); }
        };
        if ($vuaThemHoaDon) {
            $buoc('hoa_don_id cho thong bao cu',
                "UPDATE thong_bao tb
                 JOIN hoa_don h ON h.id = CAST(SUBSTRING_INDEX(REGEXP_SUBSTR(tb.noi_dung, 'chiTiet&id=[0-9]+'), '=', -1) AS UNSIGNED)
                 SET tb.hoa_don_id = h.id
                 WHERE tb.loai = 'tien_phong' AND tb.hoa_don_id IS NULL AND tb.noi_dung LIKE '%chiTiet&id=%'");
        }
        if ($vuaThemRieng) {
            // Loại do hệ thống tự sinh (tiền phòng, chuyển phòng, người ở cùng) là thông báo riêng; còn lại là chung
            $buoc('phan loai rieng_tu',
                "UPDATE thong_bao SET rieng_tu = IF(loai IN ('tien_phong','khac'), 1, 0) WHERE rieng_tu IS NULL");
            // Thông báo hóa đơn cũ: người nhận = người đang thuê phòng đó từ trước lúc thông báo được tạo
            $buoc('nguoi nhan thong bao hoa don cu',
                "INSERT IGNORE INTO thong_bao_nguoi_nhan (thong_bao_id, user_id)
                 SELECT DISTINCT tb.id, nt.account_id
                 FROM thong_bao tb
                 JOIN hoa_don h  ON h.id = tb.hoa_don_id
                 JOIN hop_dong hd ON hd.phong_id = h.phong_id AND hd.trang_thai = 'hieu_luc' AND tb.created_at >= hd.ngay_bat_dau
                 JOIN nguoi_thue nt ON nt.id = hd.nguoi_thue_id
                 WHERE tb.rieng_tu = 1 AND nt.account_id IS NOT NULL");
        }
    }

    /**
     * Điều kiện "người dùng này được thấy thông báo" (alias bảng là tb), dùng chung cho danh sách, chuông, đếm, xem.
     * Có cấu trúc mới: thông báo chung ai cũng thấy, thông báo riêng chỉ người nhận. Chứa 1 dấu ? (account id).
     * Chưa có cấu trúc mới: chỉ cho thấy loại thông báo chung, ẩn hẳn loại hệ thống tự sinh (không rò rỉ).
     */
    private function dieuKienQuyen(): array
    {
        // Thông báo CHUNG chỉ hiện từ khi người này bắt đầu thuê (ngày bắt đầu hợp đồng sớm nhất của họ);
        // chưa có hợp đồng thì từ lúc tạo tài khoản. Người thuê mới không thấy thông báo cũ trước thời gian thuê.
        // Mọi dấu ? ở đây đều là account id (caller truyền array_fill(0, $n, $user_id)).
        $tuNgay = "tb.created_at >= COALESCE(
                        (SELECT MIN(hdq.ngay_bat_dau) FROM hop_dong hdq
                         JOIN nguoi_thue ntq ON ntq.id = hdq.nguoi_thue_id WHERE ntq.account_id = ?),
                        (SELECT DATE(acq.created_at) FROM account acq WHERE acq.id = ?))";
        if (self::$coCot) {
            // rieng_tu NULL = thông báo cũ chưa được phân loại (backfill lỗi hoặc chưa chạy): xử lý như chế độ an toàn,
            // tức loại hệ thống tự sinh (tien_phong, khac) bị coi là riêng, không để lộ.
            return ["((COALESCE(tb.rieng_tu, tb.loai IN ('tien_phong','khac')) = 0 AND $tuNgay) OR EXISTS (
                        SELECT 1 FROM thong_bao_nguoi_nhan r WHERE r.thong_bao_id = tb.id AND r.user_id = ?))", 3];
        }
        return ["(tb.loai NOT IN ('tien_phong','khac') AND $tuNgay)", 2];
    }

    public function coQuyenXem(array $tb, int $user_id): bool
    {
        [$quyen, $n] = $this->dieuKienQuyen();
        $s = $this->db->prepare("SELECT COUNT(*) FROM thong_bao tb WHERE tb.id = ? AND " . $quyen);
        $s->execute(array_merge([(int)$tb['id']], array_fill(0, $n, $user_id)));
        return (int)$s->fetchColumn() > 0;
    }

    /** Quản lý: mọi thông báo, có thể lọc theo loại. */
    public function getAll(int $limit = 50, ?string $loai = null): array
    {
        $sql = "SELECT * FROM thong_bao" . ($loai !== null ? " WHERE loai = ?" : "")
             . " ORDER BY ghim DESC, created_at DESC LIMIT " . (int)$limit;
        $s = $this->db->prepare($sql);
        $s->execute($loai !== null ? [$loai] : []);
        return $s->fetchAll();
    }

    /** Người thuê: mọi thông báo mình được thấy (kể cả đã đọc), kèm cờ da_doc. */
    public function getDanhSachChoNguoiDung(int $user_id, int $limit = 100, ?string $loai = null): array
    {
        [$quyen, $n] = $this->dieuKienQuyen();
        $sql = "SELECT tb.*, (d.id IS NOT NULL) AS da_doc
                FROM thong_bao tb
                LEFT JOIN thong_bao_da_doc d ON d.thong_bao_id = tb.id AND d.user_id = ?
                WHERE " . $quyen . ($loai !== null ? " AND tb.loai = ?" : "") . "
                ORDER BY tb.ghim DESC, tb.created_at DESC
                LIMIT " . (int)$limit;
        $s = $this->db->prepare($sql);
        $s->execute(array_merge([$user_id], array_fill(0, $n, $user_id), $loai !== null ? [$loai] : []));
        return $s->fetchAll();
    }

    public function getChuaDoc(int $user_id, int $limit = 20): array
    {
        [$quyen, $n] = $this->dieuKienQuyen();
        $s = $this->db->prepare(
            "SELECT tb.* FROM thong_bao tb
             WHERE NOT EXISTS (
                SELECT 1 FROM thong_bao_da_doc d WHERE d.thong_bao_id = tb.id AND d.user_id = ?
             )
             AND " . $quyen . "
             ORDER BY tb.ghim DESC, tb.created_at DESC
             LIMIT " . (int)$limit
        );
        $s->execute(array_merge([$user_id], array_fill(0, $n, $user_id)));
        return $s->fetchAll();
    }

    public function countChuaDoc(int $user_id): int
    {
        [$quyen, $n] = $this->dieuKienQuyen();
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM thong_bao tb
             WHERE NOT EXISTS (
                SELECT 1 FROM thong_bao_da_doc d WHERE d.thong_bao_id = tb.id AND d.user_id = ?
             )
             AND " . $quyen
        );
        $s->execute(array_merge([$user_id], array_fill(0, $n, $user_id)));
        return (int)$s->fetchColumn();
    }

    public function getById(int $id): array|false
    {
        $s = $this->db->prepare("SELECT * FROM thong_bao WHERE id = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Thông báo CHUNG do quản lý soạn. */
    public function create(string $tieu_de, string $noi_dung, string $loai = 'chung', string $nguoi_gui = '', bool $ghim = false): bool
    {
        if (self::$coCot) {
            $s = $this->db->prepare(
                "INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui, ghim, rieng_tu) VALUES (?, ?, ?, ?, ?, 0)"
            );
        } else {
            $s = $this->db->prepare(
                "INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui, ghim) VALUES (?, ?, ?, ?, ?)"
            );
        }
        return $s->execute([$tieu_de, $noi_dung, $loai, $nguoi_gui, $ghim ? 1 : 0]);
    }

    public function createAndGetId(string $tieu_de, string $noi_dung, string $loai = 'chung', string $nguoi_gui = '', bool $ghim = false, ?int $hoa_don_id = null): int
    {
        if (!$this->create($tieu_de, $noi_dung, $loai, $nguoi_gui, $ghim)) return 0;
        $id = (int)$this->db->lastInsertId();
        if ($hoa_don_id && self::$coCot) {
            $this->db->prepare("UPDATE thong_bao SET hoa_don_id = ? WHERE id = ?")->execute([$hoa_don_id, $id]);
        }
        return $id;
    }

    /**
     * Thông báo RIÊNG: chỉ $user_ids (account id) thấy. Ghi thông báo và người nhận trong 1 giao dịch để không có
     * lúc thông báo tồn tại mà chưa có người nhận. Trả 0 nếu thất bại.
     */
    public function createRieng(string $tieu_de, string $noi_dung, string $loai, string $nguoi_gui, array $user_ids, ?int $hoa_don_id = null): int
    {
        $user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));
        if (!$user_ids) return 0;

        if (!self::$coCot) {
            // Chế độ dự phòng: tạo như cũ rồi đánh dấu đã đọc sẵn cho người khác (và model sẽ ẩn hẳn loại hệ thống khỏi danh sách)
            $id = $this->createAndGetId($tieu_de, $noi_dung, $loai, $nguoi_gui, false, $hoa_don_id);
            if ($id > 0) $this->anVoiTatCaTruNguoiNhan($id, $user_ids);
            return $id;
        }

        $tuMo = !$this->db->inTransaction();
        if ($tuMo) $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui, ghim, rieng_tu, hoa_don_id) VALUES (?, ?, ?, ?, 0, 1, ?)"
            )->execute([$tieu_de, $noi_dung, $loai, $nguoi_gui, $hoa_don_id]);
            $id = (int)$this->db->lastInsertId();
            $ins = $this->db->prepare("INSERT IGNORE INTO thong_bao_nguoi_nhan (thong_bao_id, user_id) VALUES (?, ?)");
            foreach ($user_ids as $uid) $ins->execute([$id, $uid]);
            if ($tuMo) $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($tuMo && $this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** Cách cũ (chỉ còn dùng ở chế độ dự phòng): đánh dấu đã đọc sẵn cho mọi tài khoản trừ người nhận. */
    public function anVoiTatCaTruNguoiNhan(int $thong_bao_id, array $user_ids): bool
    {
        $user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));
        if (!$thong_bao_id) return false;

        if (empty($user_ids)) {
            $s = $this->db->prepare(
                "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id)
                 SELECT ?, id FROM account"
            );
            return $s->execute([$thong_bao_id]);
        }

        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        $s = $this->db->prepare(
            "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id)
             SELECT ?, id FROM account WHERE id NOT IN ($placeholders)"
        );
        return $s->execute(array_merge([$thong_bao_id], $user_ids));
    }

    public function update(int $id, string $tieu_de, string $noi_dung, string $loai, bool $ghim = false): bool
    {
        $s = $this->db->prepare(
            "UPDATE thong_bao SET tieu_de = ?, noi_dung = ?, loai = ?, ghim = ? WHERE id = ?"
        );
        return $s->execute([$tieu_de, $noi_dung, $loai, $ghim ? 1 : 0, $id]);
    }

    /** Xóa thông báo; dòng đã đọc và người nhận tự xóa theo (khóa ngoại ON DELETE CASCADE). */
    public function delete(int $id): bool
    {
        $s = $this->db->prepare("DELETE FROM thong_bao WHERE id = ?");
        return $s->execute([$id]);
    }

    public function toggleGhim(int $id): bool
    {
        $s = $this->db->prepare("UPDATE thong_bao SET ghim = NOT ghim WHERE id = ?");
        return $s->execute([$id]);
    }

    public function danhDauDaDoc(int $thong_bao_id, int $user_id): bool
    {
        $s = $this->db->prepare(
            "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id) VALUES (?, ?)"
        );
        return $s->execute([$thong_bao_id, $user_id]);
    }

    /** Đánh dấu đã đọc mọi thông báo mà người này được thấy. */
    public function docTatCa(int $user_id): bool
    {
        [$quyen, $n] = $this->dieuKienQuyen();
        $s = $this->db->prepare(
            "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id)
             SELECT tb.id, ? FROM thong_bao tb
             WHERE NOT EXISTS (SELECT 1 FROM thong_bao_da_doc d WHERE d.thong_bao_id = tb.id AND d.user_id = ?)
             AND " . $quyen
        );
        return $s->execute(array_merge([$user_id, $user_id], array_fill(0, $n, $user_id)));
    }

    public function isDaDoc(int $thong_bao_id, int $user_id): bool
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM thong_bao_da_doc WHERE thong_bao_id = ? AND user_id = ?"
        );
        $s->execute([$thong_bao_id, $user_id]);
        return $s->fetchColumn() > 0;
    }
}
