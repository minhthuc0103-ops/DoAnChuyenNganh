-- =====================================================
-- Thông báo: gắn với hóa đơn + thông báo RIÊNG có danh sách người nhận
--   thong_bao.hoa_don_id : bấm vào thông báo hóa đơn thì mở chi tiết hóa đơn
--   thong_bao.rieng_tu   : NULL = chưa phân loại (thông báo cũ), 0 = chung (ai cũng thấy), 1 = riêng (chỉ người nhận)
--   thong_bao_nguoi_nhan : ai được thấy thông báo riêng
-- Chạy lại nhiều lần không lỗi, không ghi đè dữ liệu đã có.
-- =====================================================
USE ql_phong_tro;

ALTER TABLE `thong_bao`
  ADD COLUMN IF NOT EXISTS `hoa_don_id` INT NULL,
  ADD COLUMN IF NOT EXISTS `rieng_tu` TINYINT(1) NULL DEFAULT NULL,
  ADD INDEX IF NOT EXISTS `idx_tb_hoa_don` (`hoa_don_id`);

CREATE TABLE IF NOT EXISTS `thong_bao_nguoi_nhan` (
  `thong_bao_id` INT NOT NULL,
  `user_id`      INT NOT NULL,
  PRIMARY KEY (`thong_bao_id`, `user_id`),
  KEY `idx_tbnn_user` (`user_id`),
  CONSTRAINT `fk_tbnn_tb`   FOREIGN KEY (`thong_bao_id`) REFERENCES `thong_bao`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tbnn_user` FOREIGN KEY (`user_id`)      REFERENCES `account`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thông báo hóa đơn cũ: lấy mã hóa đơn từ đường dẫn có sẵn trong nội dung (chỉ dòng chưa có hoa_don_id).
UPDATE `thong_bao` tb
JOIN `hoa_don` h ON h.id = CAST(SUBSTRING_INDEX(REGEXP_SUBSTR(tb.noi_dung, 'chiTiet&id=[0-9]+'), '=', -1) AS UNSIGNED)
SET tb.hoa_don_id = h.id
WHERE tb.loai = 'tien_phong' AND tb.hoa_don_id IS NULL AND tb.noi_dung LIKE '%chiTiet&id=%';

-- Phân loại thông báo cũ (chỉ dòng còn NULL, nên thông báo chung quản lý soạn sau này không bị đổi):
-- loại do hệ thống tự sinh (tiền phòng, chuyển phòng, người ở cùng) là thông báo riêng, còn lại là chung.
UPDATE `thong_bao` SET `rieng_tu` = IF(`loai` IN ('tien_phong','khac'), 1, 0) WHERE `rieng_tu` IS NULL;

-- Người nhận của thông báo hóa đơn cũ = người đang thuê phòng đó từ trước lúc thông báo được tạo.
-- (Thông báo chuyển phòng / người ở cùng cũ không còn thông tin người nhận nên được ẩn khỏi người thuê.)
INSERT IGNORE INTO `thong_bao_nguoi_nhan` (`thong_bao_id`, `user_id`)
SELECT DISTINCT tb.id, nt.account_id
FROM `thong_bao` tb
JOIN `hoa_don` h   ON h.id = tb.hoa_don_id
JOIN `hop_dong` hd ON hd.phong_id = h.phong_id AND hd.trang_thai = 'hieu_luc' AND tb.created_at >= hd.ngay_bat_dau
JOIN `nguoi_thue` nt ON nt.id = hd.nguoi_thue_id
WHERE tb.rieng_tu = 1 AND nt.account_id IS NOT NULL;
