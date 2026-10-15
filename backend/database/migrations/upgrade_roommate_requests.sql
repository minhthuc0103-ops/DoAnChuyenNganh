USE ql_phong_tro;

CREATE TABLE IF NOT EXISTS `yeu_cau_nguoi_o_cung` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nguoi_thue_id` INT NOT NULL,
  `hop_dong_id` INT NOT NULL,
  `phong_id` INT NOT NULL,
  `ho_ten` VARCHAR(150) NOT NULL,
  `cccd` VARCHAR(30) DEFAULT '',
  `sdt` VARCHAR(20) DEFAULT '',
  `ngay_sinh` DATE DEFAULT NULL,
  `gioi_tinh` ENUM('nam','nu','khac') DEFAULT 'nam',
  `que_quan` VARCHAR(255) DEFAULT '',
  `ly_do` TEXT,
  `trang_thai` ENUM('cho_duyet','da_duyet','tu_choi','da_huy') DEFAULT 'cho_duyet',
  `phan_hoi_ql` TEXT,
  `nguoi_duyet_id` INT DEFAULT NULL,
  `duyet_luc` DATETIME DEFAULT NULL,
  `nguoi_thue_phong_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_ycnoc_user` (`user_id`),
  KEY `idx_ycnoc_hop_dong` (`hop_dong_id`),
  KEY `idx_ycnoc_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
