-- =====================================================
-- Thanh toán: ảnh chứng từ (ảnh chụp lịch sử giao dịch) người thuê gửi kèm yêu cầu xác nhận đã thanh toán.
--   lich_su_thanh_toan.anh_chung_tu : đường dẫn ảnh, NULL nếu không có (ví dụ trả tiền mặt)
-- Chạy lại nhiều lần không lỗi, không ghi đè dữ liệu đã có.
-- =====================================================
USE ql_phong_tro;

ALTER TABLE `lich_su_thanh_toan`
  ADD COLUMN IF NOT EXISTS `anh_chung_tu` VARCHAR(255) NULL DEFAULT NULL;
