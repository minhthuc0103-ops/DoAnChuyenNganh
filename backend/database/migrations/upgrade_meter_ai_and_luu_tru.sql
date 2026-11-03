-- Migration cho Chức năng 2 (AI đọc công tơ điện nước) & Chức năng 5 (Khai báo lưu trú)

-- 1. Thêm cột ảnh công tơ vào hoa_don (nếu chưa có)
SET @exist_col_dien = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hoa_don' AND COLUMN_NAME = 'anh_cong_to_dien');
SET @sql_dien = IF(@exist_col_dien = 0, 'ALTER TABLE `hoa_don` ADD COLUMN `anh_cong_to_dien` VARCHAR(255) NULL AFTER `chi_so_dien_moi`', 'SELECT 1');
PREPARE stmt_dien FROM @sql_dien;
EXECUTE stmt_dien;
DEALLOCATE PREPARE stmt_dien;

SET @exist_col_nuoc = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hoa_don' AND COLUMN_NAME = 'anh_cong_to_nuoc');
SET @sql_nuoc = IF(@exist_col_nuoc = 0, 'ALTER TABLE `hoa_don` ADD COLUMN `anh_cong_to_nuoc` VARCHAR(255) NULL AFTER `chi_so_nuoc_moi`', 'SELECT 1');
PREPARE stmt_nuoc FROM @sql_nuoc;
EXECUTE stmt_nuoc;
DEALLOCATE PREPARE stmt_nuoc;

-- 2. Tạo bảng người thuê tự báo chỉ số điện nước (kèm ảnh công tơ)
CREATE TABLE IF NOT EXISTS `bao_chi_so_dien_nuoc` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `phong_id` INT(11) NOT NULL,
  `nguoi_thue_id` INT(11) NOT NULL,
  `thang` INT(11) NOT NULL,
  `nam` INT(11) NOT NULL,
  `chi_so_dien` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `chi_so_nuoc` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `anh_cong_to_dien` VARCHAR(255) NULL,
  `anh_cong_to_nuoc` VARCHAR(255) NULL,
  `ghi_chu` TEXT NULL,
  `trang_thai` ENUM('cho_duyet', 'da_duyet', 'tu_choi') DEFAULT 'cho_duyet',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_bcs_phong` (`phong_id`),
  KEY `fk_bcs_nguoi_thue` (`nguoi_thue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tạo bảng khai báo lưu trú (Tạm trú / Cư trú)
CREATE TABLE IF NOT EXISTS `khai_bao_luu_tru` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `phong_id` INT(11) NOT NULL,
  `nguoi_thue_id` INT(11) NOT NULL,
  `ho_ten` VARCHAR(100) NOT NULL,
  `ngay_sinh` DATE NULL,
  `gioi_tinh` ENUM('nam', 'nu', 'khac') DEFAULT 'nam',
  `cccd` VARCHAR(20) NOT NULL,
  `ngay_cap_cccd` DATE NULL,
  `noi_cap_cccd` VARCHAR(150) NULL,
  `sdt` VARCHAR(20) NULL,
  `thuong_tru` VARCHAR(255) NULL,
  `nghe_nghiep` VARCHAR(100) NULL,
  `ngay_den` DATE NOT NULL,
  `ngay_di` DATE NULL,
  `ly_do` VARCHAR(255) DEFAULT 'Thuê trọ để sinh sống, học tập và làm việc',
  `ma_dvc` VARCHAR(50) NULL,
  `trang_thai` ENUM('cho_khai_bao', 'da_gui_dvc', 'da_tiep_nhan', 'ket_thuc') DEFAULT 'cho_khai_bao',
  `ghi_chu` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_kblt_phong` (`phong_id`),
  KEY `fk_kblt_nguoi_thue` (`nguoi_thue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

