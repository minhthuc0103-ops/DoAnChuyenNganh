USE ql_phong_tro;

-- Chạy file này nếu database đã được tạo từ trước.
ALTER TABLE `hoa_don`
  MODIFY COLUMN `phuong_thuc_tt` ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat';

ALTER TABLE `lich_su_thanh_toan`
  MODIFY COLUMN `phuong_thuc` ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat';
