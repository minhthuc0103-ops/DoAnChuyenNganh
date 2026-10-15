-- Add soft lifecycle status for tenant accounts.
-- Accounts are never hard-deleted by the dashboard auto check.

ALTER TABLE `account`
  ADD COLUMN IF NOT EXISTS `account_status`
    ENUM('dang_hoat_dong','ngung_hoat_dong','luu_tru')
    NOT NULL DEFAULT 'dang_hoat_dong' AFTER `vai_tro`,
  ADD COLUMN IF NOT EXISTS `account_status_changed_at`
    DATETIME NULL AFTER `account_status`,
  ADD COLUMN IF NOT EXISTS `account_status_reason`
    VARCHAR(255) NULL AFTER `account_status_changed_at`;

UPDATE `account`
SET `account_status` = 'dang_hoat_dong',
    `account_status_changed_at` = COALESCE(`account_status_changed_at`, NOW())
WHERE `account_status` IS NULL OR `account_status` = '';
