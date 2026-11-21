-- Đăng nhập bằng Google: lưu mã định danh Google (sub) của tài khoản. Chạy lại nhiều lần an toàn.
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `google_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `account` ADD UNIQUE INDEX IF NOT EXISTS `uq_account_google_id` (`google_id`);
