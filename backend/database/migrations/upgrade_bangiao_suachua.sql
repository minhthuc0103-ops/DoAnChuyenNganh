-- =====================================================
-- NÂNG CẤP HỆ THỐNG:
-- 1. SỬA CHỮA (Ảnh trước/sau, chấm sao, AI phân loại)
-- 2. BÀN GIAO VÀ TRẢ PHÒNG (Biên bản nhận/trả phòng & Quyết toán cọc)
-- =====================================================
USE ql_phong_tro;

-- 1. Bổ sung các cột cho bảng yeu_cau_sua_chua
ALTER TABLE `yeu_cau_sua_chua`
  ADD COLUMN `anh_truoc` VARCHAR(255) NULL AFTER `mo_ta`,
  ADD COLUMN `anh_sau` VARCHAR(255) NULL AFTER `anh_truoc`,
  ADD COLUMN `danh_muc` VARCHAR(50) DEFAULT 'khac' AFTER `vi_tri`,
  ADD COLUMN `ai_phan_loai` TEXT NULL AFTER `danh_muc`,
  ADD COLUMN `danh_gia_sao` TINYINT DEFAULT NULL AFTER `ghi_chu_ql`,
  ADD COLUMN `danh_gia_nhan_xet` TEXT DEFAULT NULL AFTER `danh_gia_sao`,
  ADD COLUMN `danh_gia_luc` DATETIME DEFAULT NULL AFTER `danh_gia_nhan_xet`;

-- 2. Bảng biên bản bàn giao phòng (nhận phòng và trả phòng)
CREATE TABLE IF NOT EXISTS `bien_ban_ban_giao` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ma_bien_ban` VARCHAR(50) NOT NULL UNIQUE,
  `hop_dong_id` INT NOT NULL,
  `phong_id` INT NOT NULL,
  `nguoi_thue_id` INT NOT NULL,
  `loai` ENUM('nhan_phong','tra_phong') NOT NULL DEFAULT 'nhan_phong',
  `ngay_lap` DATE NOT NULL,
  `nguoi_lap_id` INT DEFAULT NULL,
  `chi_so_dien` INT NOT NULL DEFAULT 0,
  `chi_so_nuoc` INT NOT NULL DEFAULT 0,
  `danh_sach_tai_san` TEXT DEFAULT NULL,
  `anh_ban_giao` TEXT DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `trang_thai` ENUM('cho_xac_nhan','da_xac_nhan') DEFAULT 'da_xac_nhan',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_bbbg_hd` (`hop_dong_id`),
  INDEX `idx_bbbg_phong` (`phong_id`),
  INDEX `idx_bbbg_nt` (`nguoi_thue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng yêu cầu trả phòng & Quyết toán thanh lý cọc
CREATE TABLE IF NOT EXISTS `yeu_cau_tra_phong` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hop_dong_id` INT NOT NULL,
  `phong_id` INT NOT NULL,
  `nguoi_thue_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `ngay_yeu_cau` DATE NOT NULL,
  `ngay_du_kien_tra` DATE NOT NULL,
  `ly_do` TEXT NOT NULL,
  `so_tai_khoan` VARCHAR(50) DEFAULT '',
  `ngan_hang` VARCHAR(100) DEFAULT '',
  `ten_chu_tk` VARCHAR(100) DEFAULT '',
  `trang_thai` ENUM('cho_kiem_tra','dang_kiem_tra','da_thanh_ly','tu_choi') DEFAULT 'cho_kiem_tra',
  -- Quyết toán điện nước & khấu trừ cọc
  `chi_so_dien_dau` INT DEFAULT 0,
  `chi_so_dien_cuoi` INT DEFAULT 0,
  `don_gia_dien` DECIMAL(12,2) DEFAULT 0,
  `tien_dien` DECIMAL(12,2) DEFAULT 0,
  `chi_so_nuoc_dau` INT DEFAULT 0,
  `chi_so_nuoc_cuoi` INT DEFAULT 0,
  `don_gia_nuoc` DECIMAL(12,2) DEFAULT 0,
  `tien_nuoc` DECIMAL(12,2) DEFAULT 0,
  `tien_coc_goc` DECIMAL(12,2) DEFAULT 0,
  `chi_phi_hu_hong` DECIMAL(12,2) DEFAULT 0,
  `chi_tiet_hu_hong` TEXT DEFAULT NULL,
  `phi_ve_sinh` DECIMAL(12,2) DEFAULT 0,
  `chi_phi_khac` DECIMAL(12,2) DEFAULT 0,
  `ghi_chu_khac` TEXT DEFAULT NULL,
  `tien_hoan_lai` DECIMAL(12,2) DEFAULT 0,
  `anh_tra_phong` TEXT DEFAULT NULL,
  `ngay_thanh_ly` DATE DEFAULT NULL,
  `nguoi_thanh_ly_id` INT DEFAULT NULL,
  `ghi_chu_ql` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_yctp_hd` (`hop_dong_id`),
  INDEX `idx_yctp_phong` (`phong_id`),
  INDEX `idx_yctp_user` (`user_id`),
  INDEX `idx_yctp_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
