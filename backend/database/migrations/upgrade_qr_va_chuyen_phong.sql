-- =====================================================
-- NÂNG CẤP NHANH: MOMO + VNPAY + YÊU CẦU CHUYỂN PHÒNG
-- Chạy trên database ql_phong_tro hiện tại bằng phpMyAdmin.
-- Nếu database của bạn rất cũ, ưu tiên chạy upgrade_all.sql.
-- =====================================================
USE ql_phong_tro;

ALTER TABLE `hoa_don`
  MODIFY COLUMN `phuong_thuc_tt`
  ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac')
  DEFAULT 'tien_mat';

ALTER TABLE `lich_su_thanh_toan`
  MODIFY COLUMN `phuong_thuc`
  ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac')
  DEFAULT 'tien_mat';

CREATE TABLE IF NOT EXISTS `yeu_cau_chuyen_phong` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`        INT NOT NULL,
  `nguoi_thue_id`  INT NOT NULL,
  `hop_dong_id`    INT NOT NULL,
  `phong_cu_id`    INT NOT NULL,
  `phong_moi_id`   INT NOT NULL,
  `ly_do`          TEXT NOT NULL,
  `trang_thai`     ENUM('cho_duyet','da_duyet','tu_choi','da_huy') DEFAULT 'cho_duyet',
  `phan_hoi_ql`    TEXT,
  `nguoi_duyet_id` INT DEFAULT NULL,
  `duyet_luc`      DATETIME DEFAULT NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_yccp_user` (`user_id`),
  KEY `idx_yccp_hop_dong` (`hop_dong_id`),
  KEY `idx_yccp_phong_moi` (`phong_moi_id`),
  KEY `idx_yccp_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
