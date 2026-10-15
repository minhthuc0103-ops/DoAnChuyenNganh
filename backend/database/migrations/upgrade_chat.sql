-- =====================================================
-- CHAT NHÓM (Group Chat) - Admin + tất cả người thuê
-- =====================================================

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ho_ten` VARCHAR(100) NOT NULL,
  `vai_tro` VARCHAR(20) DEFAULT 'user',
  `noi_dung` TEXT NOT NULL,
  `loai` ENUM('text','image','system') DEFAULT 'text',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `account`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
