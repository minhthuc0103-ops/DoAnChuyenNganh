<?php
/**
 * AutoCleanController
 *
 * Runs a light account lifecycle check when an admin opens the dashboard.
 * It never deletes accounts automatically. User accounts move through:
 *   dang_hoat_dong -> ngung_hoat_dong -> luu_tru
 *
 * Hard deletion must be done from an explicit manager action, outside this job.
 */
class AutoCleanController {
    private const STATUS_ACTIVE   = 'dang_hoat_dong';
    private const STATUS_INACTIVE = 'ngung_hoat_dong';
    private const STATUS_ARCHIVED = 'luu_tru';

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureLifecycleColumns();
    }

    public function run(): array {
        $changed = [];

        $accounts = $this->db->query("
            SELECT id, username, ho_ten, created_at,
                   account_status, account_status_changed_at
            FROM account
            WHERE vai_tro = 'user'
        ")->fetchAll();

        foreach ($accounts as $acc) {
            $status = $acc['account_status'] ?: self::STATUS_ACTIVE;
            $reason = $this->shouldDeactivate((int)$acc['id'], (string)$acc['created_at']);

            if ($reason === '') {
                if ($status !== self::STATUS_ACTIVE) {
                    $this->setStatus((int)$acc['id'], self::STATUS_ACTIVE, 'Có hợp đồng hiệu lực hoặc đã được khôi phục');
                    $changed[] = $this->changeRow($acc, self::STATUS_ACTIVE, 'Khôi phục hoạt động');
                }
                continue;
            }

            if ($status === self::STATUS_ACTIVE) {
                $this->setStatus((int)$acc['id'], self::STATUS_INACTIVE, $reason);
                $changed[] = $this->changeRow($acc, self::STATUS_INACTIVE, $reason);
                continue;
            }

            if ($status === self::STATUS_INACTIVE && $this->inactiveForMoreThanDays($acc['account_status_changed_at'], 10)) {
                $this->setStatus((int)$acc['id'], self::STATUS_ARCHIVED, $reason);
                $changed[] = $this->changeRow($acc, self::STATUS_ARCHIVED, $reason);
            }
        }

        if ($changed) {
            $this->notifyLifecycleChanges($changed);
        }

        return $changed;
    }

    private function ensureLifecycleColumns(): void {
        // ═══ Lỗi #12: Không ALTER TABLE runtime — chạy migration_columns.sql 1 lần ═══
        $s = $this->db->prepare("SHOW COLUMNS FROM account LIKE ?");
        $s->execute(['account_status']);
        if (!$s->fetch()) {
            error_log('AutoCleanController: Cột account_status chưa tồn tại. Hãy chạy database/migration_columns.sql');
            throw new RuntimeException('Migration chưa chạy. Xem database/migration_columns.sql');
        }
    }

    private function shouldDeactivate(int $userId, string $createdAt): string {
        $now = time();
        $created = strtotime($createdAt) ?: $now;
        $daysSinceCreated = ($now - $created) / 86400;

        $stmt = $this->db->prepare("
            SELECT trang_thai, ngay_ket_thuc
            FROM hop_dong hd
            JOIN nguoi_thue nt ON nt.id = hd.nguoi_thue_id
            WHERE nt.account_id = ?
            ORDER BY
                CASE WHEN hd.trang_thai = 'hieu_luc' THEN 0 ELSE 1 END,
                hd.ngay_ket_thuc DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $hopDong = $stmt->fetch();

        if ($hopDong && $hopDong['trang_thai'] === 'hieu_luc') {
            return '';
        }

        if (!$hopDong && $daysSinceCreated > 10) {
            return 'Không có hợp đồng sau ' . (int)$daysSinceCreated . ' ngày';
        }

        if ($hopDong && in_array($hopDong['trang_thai'], ['het_han', 'da_huy'], true)) {
            $ended = strtotime($hopDong['ngay_ket_thuc'] ?? '') ?: $now;
            $daysSinceEnded = ($now - $ended) / 86400;
            if ($daysSinceEnded > 10) {
                return 'Hợp đồng hết hạn/hủy ' . (int)$daysSinceEnded . ' ngày chưa gia hạn';
            }
        }

        return '';
    }

    private function inactiveForMoreThanDays(?string $changedAt, int $days): bool {
        $changed = $changedAt ? strtotime($changedAt) : false;
        if (!$changed) {
            return false;
        }
        return ((time() - $changed) / 86400) > $days;
    }

    private function setStatus(int $userId, string $status, string $reason): void {
        $s = $this->db->prepare("
            UPDATE account
            SET account_status = ?,
                account_status_changed_at = NOW(),
                account_status_reason = ?
            WHERE id = ?
        ");
        $s->execute([$status, $reason, $userId]);
    }

    private function changeRow(array $account, string $status, string $reason): array {
        return [
            'ho_ten' => $account['ho_ten'],
            'username' => $account['username'],
            'status' => $status,
            'reason' => $reason,
        ];
    }

    private function notifyLifecycleChanges(array $changed): void {
        $labels = [
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_INACTIVE => 'Ngừng hoạt động',
            self::STATUS_ARCHIVED => 'Lưu trữ',
        ];

        $lines = ['ℹ Hệ thống đã cập nhật trạng thái tài khoản người thuê:'];
        foreach ($changed as $row) {
            $label = $labels[$row['status']] ?? $row['status'];
            $lines[] = "• {$row['ho_ten']} (@{$row['username']}) → {$label}: {$row['reason']}";
        }

        $ql = $this->db->query("
            SELECT id FROM account
            WHERE vai_tro IN ('quan_ly','chu_tro')
            ORDER BY id
            LIMIT 1
        ")->fetch();

        if ($ql) {
            try {
                $chatModel = new ChatModel();
                $chatModel->send((int)$ql['id'], 'Hệ thống', 'system', implode("\n", $lines), 'system');
            } catch (Throwable $e) {
                error_log('Khong the gui thong bao vong doi tai khoan: ' . $e->getMessage());
            }
        }
    }
}
