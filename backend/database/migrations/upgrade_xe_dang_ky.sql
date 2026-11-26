-- =====================================================
-- Xe người dùng tự đăng ký trong hồ sơ (trước khi có hợp đồng).
--   xe_dang_ky : xe của tài khoản; khi quản lý tạo hợp đồng, xe này tự điền vào form rồi mới thành bản ghi trong bảng `xe`
--   xe.loai_xe : thêm 'o_to' (form hợp đồng vốn đã có lựa chọn Ô tô nhưng cột chưa nhận giá trị này)
-- Chạy lại nhiều lần không lỗi, không ghi đè dữ liệu đã có.
-- =====================================================
USE ql_phong_tro;

CREATE TABLE IF NOT EXISTS `xe_dang_ky` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) NOT NULL,
  `bien_so`    VARCHAR(20) NOT NULL,
  `loai_xe`    ENUM('xe_may','xe_dien','xe_dap','o_to') NOT NULL DEFAULT 'xe_may',
  `mau_sac`    VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_xdk_account_bien_so` (`account_id`, `bien_so`),
  CONSTRAINT `fk_xdk_account` FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `xe`
  MODIFY COLUMN `loai_xe` ENUM('xe_may','xe_dien','xe_dap','o_to') NULL DEFAULT 'xe_may';
