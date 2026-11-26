<?php
/**
 * Xe người dùng tự đăng ký trong hồ sơ (trước khi có hợp đồng).
 * Khi quản lý tạo hợp đồng và chọn người đó, các xe này tự điền vào form hợp đồng.
 * Cấu trúc do migration upgrade_xe_dang_ky.sql (Database.bat) tạo; ensureSchema chỉ là dự phòng cho máy chưa chạy.
 */
class XeDangKyModel
{
    public const TOI_DA = 2;                                   // tối đa 2 xe / người (giống người ở cùng)
    public const LOAI   = ['xe_may', 'xe_dien', 'xe_dap', 'o_to'];

    private PDO $db;
    private static bool $schemaOk = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        if (self::$schemaOk) return;
        self::$schemaOk = true;
        try {
            $this->db->exec(
                "CREATE TABLE IF NOT EXISTS xe_dang_ky (
                    id         INT(11) NOT NULL AUTO_INCREMENT,
                    account_id INT(11) NOT NULL,
                    bien_so    VARCHAR(20) NOT NULL,
                    loai_xe    ENUM('xe_may','xe_dien','xe_dap','o_to') NOT NULL DEFAULT 'xe_may',
                    mau_sac    VARCHAR(50) NULL DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_xdk_account_bien_so (account_id, bien_so),
                    CONSTRAINT fk_xdk_account FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Throwable $e) {
            error_log('XeDangKyModel: khong tao duoc bang xe_dang_ky: ' . $e->getMessage());
        }
    }

    public function getByAccount(int $account_id): array
    {
        $s = $this->db->prepare("SELECT * FROM xe_dang_ky WHERE account_id = ? ORDER BY id");
        $s->execute([$account_id]);
        return $s->fetchAll();
    }

    /** Xe đã đăng ký của nhiều tài khoản: [account_id => [xe, ...]] (dùng cho form tạo hợp đồng). */
    public function getByAccounts(array $account_ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $account_ids))));
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $s = $this->db->prepare("SELECT account_id, bien_so, loai_xe, mau_sac FROM xe_dang_ky WHERE account_id IN ($in) ORDER BY id");
        $s->execute($ids);
        $ra = [];
        foreach ($s->fetchAll() as $r) {
            $ra[(int)$r['account_id']][] = ['bien_so' => $r['bien_so'], 'loai_xe' => $r['loai_xe'], 'mau_sac' => (string)$r['mau_sac']];
        }
        return $ra;
    }

    /** Chuẩn hóa biển số: in hoa, gọn khoảng trắng. */
    public static function chuanHoaBienSo(string $bs): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $bs)));
    }

    /** @return string|null thông báo lỗi, null nếu hợp lệ */
    public static function kiemTra(string $bienSo, string $loai, string $mauSac): ?string
    {
        if (!preg_match('/^[0-9A-Z][0-9A-Z .\-]{3,14}$/', $bienSo)) {
            return 'Biển số không hợp lệ (4–15 ký tự gồm chữ, số, dấu gạch ngang hoặc chấm).';
        }
        if (!in_array($loai, self::LOAI, true)) {
            return 'Loại xe không hợp lệ.';
        }
        if (mb_strlen($mauSac, 'UTF-8') > 50) {
            return 'Tên xe tối đa 50 ký tự.';
        }
        return null;
    }

    /** @return string|null thông báo lỗi, null nếu đã thêm */
    public function them(int $account_id, string $bienSo, string $loai, string $mauSac): ?string
    {
        $bienSo = self::chuanHoaBienSo($bienSo);
        $mauSac = trim($mauSac);
        if ($loi = self::kiemTra($bienSo, $loai, $mauSac)) return $loi;

        $c = $this->db->prepare("SELECT COUNT(*) FROM xe_dang_ky WHERE account_id = ?");
        $c->execute([$account_id]);
        if ((int)$c->fetchColumn() >= self::TOI_DA) {
            return 'Mỗi người đăng ký tối đa ' . self::TOI_DA . ' xe. Hãy xóa một xe trước khi thêm xe mới.';
        }
        try {
            $this->db->prepare("INSERT INTO xe_dang_ky (account_id, bien_so, loai_xe, mau_sac) VALUES (?, ?, ?, ?)")
                     ->execute([$account_id, $bienSo, $loai, $mauSac !== '' ? $mauSac : null]);
        } catch (\PDOException $e) {
            if ((int)$e->getCode() === 23000) return 'Biển số này đã có trong danh sách xe của bạn.';
            throw $e;
        }
        return null;
    }

    /** Chỉ xóa được xe của chính tài khoản đó. */
    public function xoa(int $id, int $account_id): bool
    {
        $s = $this->db->prepare("DELETE FROM xe_dang_ky WHERE id = ? AND account_id = ?");
        $s->execute([$id, $account_id]);
        return $s->rowCount() > 0;
    }
}
