-- ═══════════════════════════════════════════════════
-- Migration: Thêm các cột cần thiết
-- Chạy 1 lần trên phpMyAdmin hoặc MySQL CLI
-- ═══════════════════════════════════════════════════

-- Cột chat_joined_at cho bảng account (trước đây chạy trong AuthController)
ALTER TABLE account ADD COLUMN IF NOT EXISTS chat_joined_at DATETIME NULL;
UPDATE account SET chat_joined_at = '1970-01-01 00:00:00' WHERE chat_joined_at IS NULL;

-- Cột vòng đời tài khoản (trước đây chạy trong AutoCleanController constructor)
ALTER TABLE account ADD COLUMN IF NOT EXISTS account_status ENUM('dang_hoat_dong','ngung_hoat_dong','luu_tru') NOT NULL DEFAULT 'dang_hoat_dong';
ALTER TABLE account ADD COLUMN IF NOT EXISTS account_status_changed_at DATETIME NULL;
ALTER TABLE account ADD COLUMN IF NOT EXISTS account_status_reason VARCHAR(255) NULL;

UPDATE account
SET account_status = 'dang_hoat_dong',
    account_status_changed_at = COALESCE(account_status_changed_at, NOW())
WHERE account_status IS NULL OR account_status = '';
