-- =====================================================
-- UPGRADE TẤT CẢ TÍNH NĂNG MỚI
-- Chạy trên database ql_phong_tro hiện tại
-- =====================================================

USE ql_phong_tro;

-- =====================================================
-- 1. THÊM CHI TIẾT THÔNG TIN CÁ NHÂN (nguoi_thue)
-- =====================================================
ALTER TABLE `nguoi_thue`
  ADD COLUMN IF NOT EXISTS `gioi_tinh` ENUM('nam','nu','khac') DEFAULT 'nam' AFTER `ngay_sinh`,
  ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT '' AFTER `gioi_tinh`,
  ADD COLUMN IF NOT EXISTS `nghe_nghiep` VARCHAR(100) DEFAULT '' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `noi_cap_cccd` VARCHAR(100) DEFAULT '' AFTER `nghe_nghiep`,
  ADD COLUMN IF NOT EXISTS `ngay_cap_cccd` DATE DEFAULT NULL AFTER `noi_cap_cccd`,
  ADD COLUMN IF NOT EXISTS `ghi_chu` TEXT AFTER `ngay_cap_cccd`;

-- =====================================================
-- 2. LỊCH SỬ THAY ĐỔI GIÁ PHÒNG
-- =====================================================
CREATE TABLE IF NOT EXISTS `lich_su_gia` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `phong_id` INT NOT NULL,
  `gia_cu` DECIMAL(12,2) NOT NULL,
  `gia_moi` DECIMAL(12,2) NOT NULL,
  `ngay_thay_doi` DATE NOT NULL,
  `ghi_chu` TEXT,
  `nguoi_thay_doi` VARCHAR(100) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`phong_id`) REFERENCES `phong`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PHƯƠNG THỨC THANH TOÁN (hoa_don)
-- =====================================================
ALTER TABLE `hoa_don`
  ADD COLUMN IF NOT EXISTS `phuong_thuc_tt` ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat' AFTER `trang_thai`,
  ADD COLUMN IF NOT EXISTS `ngay_thanh_toan` DATETIME DEFAULT NULL AFTER `phuong_thuc_tt`,
  ADD COLUMN IF NOT EXISTS `nguoi_thu` VARCHAR(100) DEFAULT '' AFTER `ngay_thanh_toan`,
  ADD COLUMN IF NOT EXISTS `ghi_chu_tt` TEXT AFTER `nguoi_thu`;

-- =====================================================
-- 4. LỊCH SỬ THANH TOÁN
-- =====================================================
CREATE TABLE IF NOT EXISTS `lich_su_thanh_toan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hoa_don_id` INT NOT NULL,
  `phong_id` INT NOT NULL,
  `so_tien` DECIMAL(12,2) NOT NULL,
  `phuong_thuc` ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat',
  `trang_thai` ENUM('thanh_cong','that_bai','dang_xu_ly') DEFAULT 'thanh_cong',
  `nguoi_thu` VARCHAR(100) DEFAULT '',
  `nguoi_tra` VARCHAR(100) DEFAULT '',
  `ghi_chu` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`phong_id`) REFERENCES `phong`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. THÔNG BÁO (Box cho tất cả người thuê)
-- =====================================================
CREATE TABLE IF NOT EXISTS `thong_bao` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tieu_de` VARCHAR(200) NOT NULL,
  `noi_dung` TEXT NOT NULL,
  `loai` ENUM('chung','khan_cap','bao_tri','tien_phong','khac') DEFAULT 'chung',
  `nguoi_gui` VARCHAR(100) DEFAULT '',
  `ghim` TINYINT(1) DEFAULT 0 COMMENT 'Ghim lên đầu',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `thong_bao_da_doc` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `thong_bao_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `doc_luc` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`thong_bao_id`) REFERENCES `thong_bao`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `account`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_tb_user` (`thong_bao_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. NỘI DUNG HỢP ĐỒNG
-- =====================================================
ALTER TABLE `hop_dong`
  ADD COLUMN IF NOT EXISTS `noi_dung` TEXT AFTER `ghi_chu`;

CREATE TABLE IF NOT EXISTS `mau_hop_dong` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ten_mau` VARCHAR(200) NOT NULL,
  `noi_dung` TEXT NOT NULL,
  `mac_dinh` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mẫu hợp đồng mặc định
INSERT INTO `mau_hop_dong` (`ten_mau`, `noi_dung`, `mac_dinh`) VALUES (
'Hợp đồng thuê phòng trọ (Mẫu chuẩn)',
'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM\nĐộc lập – Tự do – Hạnh phúc\n—————————————\n\nHỢP ĐỒNG THUÊ PHÒNG TRỌ\n\nHôm nay, ngày {ngay_ky}, tại {dia_chi_phong}, chúng tôi gồm:\n\nBÊN CHO THUÊ (Bên A):\n- Họ và tên: {ten_chu_tro}\n- Điện thoại: {sdt_chu_tro}\n\nBÊN THUÊ (Bên B):\n- Họ và tên: {ho_ten}\n- CCCD/CMND: {cccd}\n- Điện thoại: {sdt}\n- Địa chỉ thường trú: {dia_chi}\n\nHai bên cùng thỏa thuận và đồng ý với nội dung sau:\n\nĐIỀU 1:\n• Bên A đồng ý cho bên B thuê một phòng thuộc nhà số: {so_phong} với các điều kiện sau đây:\n• Thời hạn thuê là: kể từ ngày {ngay_bat_dau} đến ngày {ngay_ket_thuc}.\n• Mục đích thuê phòng của bên B là để ở và với tổng số người ở là: {so_nguoi} người, trong quá trình ở nếu có phát sinh thêm người vào ở thì bên B phải báo trước cho bên A, nếu như bên B tự tiện cho người lạ vào ở khi chưa có sự đồng ý của bên A thì coi như bên B vi phạm hợp đồng và phải chịu mất tiền đặt cọc.\n• Khi Bên B giảm người và xe so với đăng ký ban đầu thì cũng phải báo cho bên A biết để kiểm soát, nếu không thông báo thì sẽ không giải quyết về tiền xe, người ở lại cũng phải chịu trách nhiệm khi người chuyển đi vi phạm pháp luật và các quy định của bên A.\n\nĐIỀU 2: ĐƠN GIÁ VÀ THANH TOÁN\n• Đơn giá phòng và các dịch vụ tiện ích như sau:\n  - Đơn giá phòng: {gia_thue} đồng/tháng\n  - Đơn giá điện: 3.500 đồng/kWh (Bằng chữ: Ba ngàn năm trăm đồng)\n  - Đơn giá nước: 20.000 đồng/m³ (Bằng chữ: Hai mươi ngàn đồng)\n  - Đơn giá DV Wifi, Rác, VS...: 150.000 đồng/phòng (Bằng chữ: Một trăm năm mươi ngàn đồng)\n  - Giữ xe: 100.000 đồng/chiếc/tháng (Bằng chữ: Một trăm ngàn đồng)\n• Các đơn giá trên sẽ giữ cố định, riêng giá thuê phòng được điều chỉnh tăng hàng năm từ 3-7%, nếu có bất cứ thay đổi nào khác thì bên A sẽ thông báo cho bên B biết trước ít nhất 20 ngày.\n• Tiền thuê phòng bên B thanh toán cho bên A từ ngày 01-05 Tây hàng tháng.\n• Bên B đặt tiền cọc là: {tien_coc} đồng cho bên A.\n• Tiền đặt cọc sẽ được trả lại cho bên thuê khi kết thúc hợp đồng thuê phòng sau khi trừ các khoản tiền điện, nước, phí dịch vụ và các khoản khác liên quan, khi bên B thực hiện đúng quy định các điều khoản trong hợp đồng.\n• Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền đặt cọc và phải thanh toán chi phí phát sinh.\n• Bên A ngưng hợp đồng trước thời hạn sẽ báo trước cho Bên B 30 ngày để sắp xếp chỗ ở mới.\n• Bên A ngưng hợp đồng (lấy lại phòng) trước thời hạn mà không báo trước thì bồi thường gấp đôi số tiền bên B đã đặt cọc.\n\nĐIỀU 3: TRÁCH NHIỆM BÊN A\n• Giao nhà, trang thiết bị trong nhà cho bên B đúng ngày ký hợp đồng.\n• Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.\n• Bố trí người để dọn vệ sinh cho những khu vực sinh hoạt chung nhằm tạo ra môi trường sống sạch sẽ cho người thuê phòng.\n• Trang bị khóa vân tay cổng chính và nơi để xe cho khách thuê phòng.\n• Bố trí bảo vệ trông xe cho Bên B, nếu xảy ra mất mát mà do lỗi bên A thì bên A phải bồi thường lại tài sản đó sau khi đã khấu hao sử dụng.\n\nĐIỀU 4: TRÁCH NHIỆM BÊN B\n• Trả tiền thuê phòng và các khoản phí hàng tháng theo điều 2 của hợp đồng.\n• Sử dụng đúng mục đích thuê nhà, khi cần sửa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.\n• Cung cấp đầy đủ giấy tờ tùy thân cho bên A để làm đăng ký tạm trú.\n• Đồ đạc trang thiết bị trong nhà phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát, nếu hư hỏng mất mát thì phải bồi thường hoặc khắc phục lại trạng thái ban đầu.\n• Phải thông báo cho bên A biết trước nếu muốn khoan, đục hay cải tạo lại căn phòng đang sử dụng, trường hợp nếu bên B cố ý cải tạo mà không xin phép thì bên A có quyền chấm dứt hợp đồng với bên B tại thời điểm đó và bên B phải khôi phục lại trạng thái ban đầu hoặc trả tiền để bên A thuê thợ làm.\n• Quý khách tự ý dọn đi ra khỏi trọ là không còn quyền sử dụng phòng và không được tự ý ra vào trọ, không có quyền khiếu nại về sau và được xem là tự ý bỏ cọc. Quý khách tự ý bỏ cọc cũng phải thanh toán điện chi phí phát sinh.\n• Để xe ngay ngắn, gọn gàng, đúng vạch đúng nơi quy định, theo sự hướng dẫn của Quản lý.\n• Giữ vệ sinh môi trường chung và không được tụ tập gây ồn ào ảnh hưởng đến những người xung quanh.\n• Báo cho bên A biết trước 30 ngày nếu muốn ngưng hợp đồng thuê không tiếp tục gia hạn ở nữa, không giải quyết trường hợp đi trước hạn hợp đồng. Ngày nhận báo trả phòng là 1-5 tây hàng tháng.\n• Quý khách muốn sang phòng phải báo với quản lý và giá mới sẽ theo chủ nhà nếu có thay đổi.\n• Khi trả phòng quý khách phải dọn phòng sạch sẽ, nếu phòng còn rác, toilet và bếp đóng vàng dơ thì phải trả tiền thuê người vệ sinh.\n• Không được tàng trữ ma túy, hàng Quốc Cấm, các thiết bị hay vật liệu dễ gây cháy nổ vào trong khu trọ. Nghiêm cấm đánh bạc dưới mọi hình thức. Không được chứa chấp tội phạm liên quan đến pháp luật và thực hiện mọi hành vi khác vi phạm pháp luật, nếu có phải hoàn toàn tự chịu trách nhiệm trước pháp luật.\n• Không làm ồn sau 21h30, không được dẫn bạn thường xuyên về phòng tổ chức ăn nhậu. Không được hát Karaoke gây ồn ào trong dãy trọ. Dắt bạn về phòng phải gặp quản lý để báo cáo trình giấy tờ.\n\nĐIỀU 5: ĐIỀU KHOẢN CHUNG\n• Hợp đồng có hiệu lực kể từ ngày ký.\n• Được lập thành 02 bản, mỗi bên giữ 01 bản có giá trị pháp lý như nhau.\n• Trong quá trình thực hiện hợp đồng, nếu có vấn đề phát sinh hai bên cùng nhau bàn bạc giải quyết trên tinh thần hợp tác, tôn trọng lẫn nhau.\n\n        BÊN A                              BÊN B\n   (Ký, ghi rõ họ tên)              (Ký, ghi rõ họ tên)',
1);

-- =====================================================
-- 7. SỐ NGƯỜI HIỆN TẠI ĐANG Ở (phong)
-- =====================================================
ALTER TABLE `phong`
  ADD COLUMN IF NOT EXISTS `so_nguoi_hien_tai` INT DEFAULT 0 AFTER `so_nguoi`;


-- =====================================================
-- BỔ SUNG VNPAY CHO DATABASE ĐÃ TỒN TẠI
-- =====================================================
ALTER TABLE `hoa_don`
  MODIFY COLUMN `phuong_thuc_tt` ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat';

ALTER TABLE `lich_su_thanh_toan`
  MODIFY COLUMN `phuong_thuc` ENUM('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat';

-- =====================================================
-- 8. YÊU CẦU CHUYỂN PHÒNG
-- =====================================================
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
