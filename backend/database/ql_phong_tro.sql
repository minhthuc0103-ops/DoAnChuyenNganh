-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 30, 2026 lúc 07:38 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ql_phong_tro`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `vai_tro` enum('quan_ly','chu_tro','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `account`
--

INSERT INTO `account` (`id`, `ho_ten`, `username`, `password`, `email`, `sdt`, `vai_tro`, `created_at`) VALUES
(1, 'Tô Minh Thức', 'minhthuc0103', '$2y$10$XBe49YovSVDjX1EKNu6lS.OZ8fbiUkRlX8gsQaG.00Vkp362WxuIy', 'minhthuc0103@gmail.com', '0941032534', 'quan_ly', '2026-05-08 04:32:33'),
(3, 'Phạm Thái Phong', 'thaiphong2306', '$2y$10$c4tvxok5OWcnEu9dXoOOPeriPHYS4lH94z7tf5lGg9RrdXInpFVAy', 'thaiphong2306@gmail.com', '0941032535', 'user', '2026-05-08 06:26:58'),
(5, 'trong', 'thanhtrong0852', '$2y$10$byIcZ9ON2q9YUxRzalJYjuqFTLTmSri5b/5xlTF.MKW2/AmzNRntG', 'thanhtrong0852@gmail.com', '', 'user', '2026-05-18 04:05:26'),
(6, 'tien', 'tien456', '$2y$10$8xQnDMFRQmBth1NS9NyfN.SV7FY5REU1ohD24aUo6uzdTYu3SsGMW', 'tien123@gmail.com', '015484512', 'user', '2026-05-21 04:24:47'),
(7, 'tri', 'tri123', '$2y$10$HZR5WMBre9iqVbvdAfKpcebBg.nHyvcRq2jTngvXIk2.uMeN.GqyW', 'tri123@gmail.com', '0251585542', 'user', '2026-05-21 04:25:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ai_knowledge`
--

CREATE TABLE `ai_knowledge` (
  `id` int(11) NOT NULL,
  `tu_khoa` text NOT NULL COMMENT 'Từ khóa cách nhau bằng dấu phẩy',
  `cau_hoi_mau` text NOT NULL COMMENT 'Câu hỏi mẫu để so sánh',
  `tra_loi` text NOT NULL COMMENT 'Câu trả lời',
  `so_lan_dung` int(11) DEFAULT 0 COMMENT 'Số lần được dùng',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ai_unknown`
--

CREATE TABLE `ai_unknown` (
  `id` int(11) NOT NULL,
  `cau_hoi` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ho_ten` varchar(100) DEFAULT NULL,
  `so_lan` int(11) DEFAULT 1 COMMENT 'Hỏi nhiều lần = ưu tiên dạy trước',
  `da_xu_ly` tinyint(4) DEFAULT 0 COMMENT '0=chưa, 1=đã dạy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ai_unknown`
--

INSERT INTO `ai_unknown` (`id`, `cau_hoi`, `user_id`, `ho_ten`, `so_lan`, `da_xu_ly`, `created_at`) VALUES
(1, 'điện nước như nào nhỉ', 6, 'tien', 1, 0, '2026-05-26 13:18:20'),
(2, 'tiền điện nước', 6, 'tien', 1, 0, '2026-05-26 13:18:33'),
(3, 'cho tôi xem tiền điện và tiền nước', 6, 'tien', 1, 0, '2026-05-26 13:18:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `vai_tro` varchar(20) DEFAULT 'user',
  `noi_dung` text NOT NULL,
  `loai` enum('text','image','system') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `ho_ten`, `vai_tro`, `noi_dung`, `loai`, `created_at`) VALUES
(1, 1, 'Tô Minh Thức', 'quan_ly', 'alo mn', 'text', '2026-05-18 09:21:25'),
(2, 1, 'Tô Minh Thức', 'quan_ly', 'mn ổn ko', 'text', '2026-05-18 09:21:43'),
(3, 3, 'Phạm Thái Phong', 'user', 'ổn', 'text', '2026-05-18 09:21:47'),
(4, 6, 'tien', 'user', 'hi', 'text', '2026-05-21 04:25:39'),
(5, 7, 'tri', 'user', 'hrr', 'text', '2026-05-21 04:25:46'),
(6, 5, 'trong', 'user', 'hi', 'text', '2026-05-21 04:25:52'),
(7, 3, 'Phạm Thái Phong', 'user', '😍😍😍😍', 'text', '2026-05-21 04:39:07'),
(8, 1, 'Tô Minh Thức', 'quan_ly', 'hhj', 'text', '2026-05-21 05:08:17'),
(9, 1, 'Tô Minh Thức', 'quan_ly', '📢 Thông báo cập nhật đơn giá:\n⚡ Giá điện: 3,000đ → 3,500đ/kWh\n\nÁp dụng từ: 26/05/2026 09:55', 'text', '2026-05-26 07:55:43'),
(10, 1, 'Tô Minh Thức', 'quan_ly', '📢 Thông báo cập nhật đơn giá:\n💧 Giá nước: 17,000đ → 19,000đ/m³\n\nÁp dụng từ: 26/05/2026 09:55', 'text', '2026-05-26 07:55:52'),
(11, 1, 'Tô Minh Thức', 'quan_ly', '📢 Thông báo cập nhật đơn giá:\n⚡ Giá điện: 3,500đ → 3,900đ/kWh\n💧 Giá nước: 19,000đ → 20,000đ/m³\n\nÁp dụng từ: 26/05/2026 09:55', 'text', '2026-05-26 07:55:59'),
(12, 1, 'Tô Minh Thức', 'quan_ly', 'ksadhjsa4', 'text', '2026-05-26 08:13:14'),
(13, 1, 'Tô Minh Thức', 'quan_ly', 'adsakdhhkj', 'text', '2026-05-26 08:13:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_gia`
--

CREATE TABLE `don_gia` (
  `id` int(11) NOT NULL,
  `gia_dien` decimal(10,2) DEFAULT 3500.00,
  `gia_nuoc` decimal(10,2) DEFAULT 15000.00,
  `phi_dv` decimal(12,2) DEFAULT 150000.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_gia`
--

INSERT INTO `don_gia` (`id`, `gia_dien`, `gia_nuoc`, `phi_dv`) VALUES
(1, 3900.00, 20000.00, 150000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa_don`
--

CREATE TABLE `hoa_don` (
  `id` int(11) NOT NULL,
  `phong_id` int(11) NOT NULL,
  `thang` int(11) NOT NULL,
  `nam` int(11) NOT NULL,
  `chi_so_dien_cu` decimal(10,2) DEFAULT 0.00,
  `chi_so_dien_moi` decimal(10,2) DEFAULT 0.00,
  `chi_so_nuoc_cu` decimal(10,2) DEFAULT 0.00,
  `chi_so_nuoc_moi` decimal(10,2) DEFAULT 0.00,
  `tien_phong` decimal(12,2) DEFAULT 0.00,
  `tien_dien` decimal(12,2) DEFAULT 0.00,
  `tien_nuoc` decimal(12,2) DEFAULT 0.00,
  `tong_tien` decimal(12,2) DEFAULT 0.00,
  `trang_thai` enum('chua_tt','da_tt') DEFAULT 'chua_tt',
  `phuong_thuc_tt` enum('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat',
  `ngay_thanh_toan` datetime DEFAULT NULL,
  `nguoi_thu` varchar(100) DEFAULT '',
  `ghi_chu_tt` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phi_dich_vu` decimal(12,2) DEFAULT 0.00,
  `phi_xe` decimal(12,2) DEFAULT 0.00,
  `so_xe` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoa_don`
--

INSERT INTO `hoa_don` (`id`, `phong_id`, `thang`, `nam`, `chi_so_dien_cu`, `chi_so_dien_moi`, `chi_so_nuoc_cu`, `chi_so_nuoc_moi`, `tien_phong`, `tien_dien`, `tien_nuoc`, `tong_tien`, `trang_thai`, `phuong_thuc_tt`, `ngay_thanh_toan`, `nguoi_thu`, `ghi_chu_tt`, `created_at`, `phi_dich_vu`, `phi_xe`, `so_xe`) VALUES
(1, 2, 5, 2026, 10.00, 19.90, 15.00, 17.80, 4900000.00, 34650.00, 56000.00, 4990650.00, 'da_tt', 'tien_mat', NULL, '', NULL, '2026-05-18 03:35:47', 0.00, 0.00, 0),
(2, 5, 5, 2026, 120.00, 135.00, 15.00, 17.80, 3000000.00, 52500.00, 56000.00, 3108500.00, 'da_tt', 'tien_mat', NULL, '', NULL, '2026-05-18 05:47:40', 0.00, 0.00, 0),
(3, 2, 7, 2026, 19.90, 22.00, 17.80, 24.90, 4900000.00, 7350.00, 142000.00, 5049350.00, 'chua_tt', 'tien_mat', NULL, '', NULL, '2026-05-21 05:11:26', 0.00, 0.00, 0),
(4, 4, 5, 2026, 15.00, 20.00, 20.00, 24.30, 3300000.00, 17500.00, 86000.00, 3403500.00, 'chua_tt', 'tien_mat', NULL, '', NULL, '2026-05-21 05:14:34', 0.00, 0.00, 0),
(5, 6, 5, 2026, 0.00, 76.00, 0.00, 10.00, 4000000.00, 296400.00, 200000.00, 4746400.00, 'chua_tt', 'tien_mat', NULL, '', NULL, '2026-05-27 04:50:20', 150000.00, 100000.00, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hop_dong`
--

CREATE TABLE `hop_dong` (
  `id` int(11) NOT NULL,
  `phong_id` int(11) NOT NULL,
  `nguoi_thue_id` int(11) NOT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `tien_coc` decimal(12,2) DEFAULT 0.00,
  `ghi_chu` text DEFAULT NULL,
  `noi_dung` text DEFAULT NULL,
  `trang_thai` enum('hieu_luc','het_han','da_huy') DEFAULT 'hieu_luc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `yeu_cau_huy` tinyint(1) DEFAULT 0 COMMENT '1 = đã báo hủy',
  `ngay_bao_huy` date DEFAULT NULL COMMENT 'Ngày người thuê báo hủy',
  `ngay_du_kien_ra` date DEFAULT NULL COMMENT 'Ngày dự kiến ra (ngày 25 tháng báo)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hop_dong`
--

INSERT INTO `hop_dong` (`id`, `phong_id`, `nguoi_thue_id`, `ngay_bat_dau`, `ngay_ket_thuc`, `tien_coc`, `ghi_chu`, `noi_dung`, `trang_thai`, `created_at`, `yeu_cau_huy`, `ngay_bao_huy`, `ngay_du_kien_ra`) VALUES
(1, 2, 4, '2026-05-18', '2026-11-18', 4900000.00, 'luật cần thực hiện', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM\nĐộc lập – Tự do – Hạnh phúc\n—————————————\n\nHỢP ĐỒNG THUÊ PHÒNG TRỌ\n\nHôm nay, ngày {ngay_ky}, tại {dia_chi_phong}, chúng tôi gồm:\n\nBÊN CHO THUÊ (Bên A):\n- Họ và tên: {ten_chu_tro}\n- Điện thoại: {sdt_chu_tro}\n\nBÊN THUÊ (Bên B):\n- Họ và tên: {ho_ten}\n- CCCD/CMND: {cccd}\n- Điện thoại: {sdt}\n- Địa chỉ thường trú: {dia_chi}\n\nHai bên cùng thỏa thuận và đồng ý với nội dung sau:\n\nĐIỀU 1:\n• Bên A đồng ý cho bên B thuê một phòng thuộc nhà số: {so_phong} với các điều kiện sau đây:\n• Thời hạn thuê là: kể từ ngày {ngay_bat_dau} đến ngày {ngay_ket_thuc}.\n• Mục đích thuê phòng của bên B là để ở và với tổng số người ở là: {so_nguoi} người, trong quá trình ở nếu có phát sinh thêm người vào ở thì bên B phải báo trước cho bên A, nếu như bên B tự tiện cho người lạ vào ở khi chưa có sự đồng ý của bên A thì coi như bên B vi phạm hợp đồng và phải chịu mất tiền đặt cọc.\n• Khi Bên B giảm người và xe so với đăng ký ban đầu thì cũng phải báo cho bên A biết để kiểm soát, nếu không thông báo thì sẽ không giải quyết về tiền xe, người ở lại cũng phải chịu trách nhiệm khi người chuyển đi vi phạm pháp luật và các quy định của bên A.\n\nĐIỀU 2: ĐƠN GIÁ VÀ THANH TOÁN\n• Đơn giá phòng và các dịch vụ tiện ích như sau:\n  - Đơn giá phòng: {gia_thue} đồng/tháng\n  - Đơn giá điện: 3.500 đồng/kWh (Bằng chữ: Ba ngàn năm trăm đồng)\n  - Đơn giá nước: 20.000 đồng/m³ (Bằng chữ: Hai mươi ngàn đồng)\n  - Đơn giá DV Wifi, Rác, VS...: 150.000 đồng/phòng (Bằng chữ: Một trăm năm mươi ngàn đồng)\n  - Giữ xe: 100.000 đồng/chiếc/tháng (Bằng chữ: Một trăm ngàn đồng)\n• Các đơn giá trên sẽ giữ cố định, riêng giá thuê phòng được điều chỉnh tăng hàng năm từ 3-7%, nếu có bất cứ thay đổi nào khác thì bên A sẽ thông báo cho bên B biết trước ít nhất 20 ngày.\n• Tiền thuê phòng bên B thanh toán cho bên A từ ngày 01-05 Tây hàng tháng.\n• Bên B đặt tiền cọc là: {tien_coc} đồng cho bên A.\n• Tiền đặt cọc sẽ được trả lại cho bên thuê khi kết thúc hợp đồng thuê phòng sau khi trừ các khoản tiền điện, nước, phí dịch vụ và các khoản khác liên quan, khi bên B thực hiện đúng quy định các điều khoản trong hợp đồng.\n• Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền đặt cọc và phải thanh toán chi phí phát sinh.\n• Bên A ngưng hợp đồng trước thời hạn sẽ báo trước cho Bên B 30 ngày để sắp xếp chỗ ở mới.\n• Bên A ngưng hợp đồng (lấy lại phòng) trước thời hạn mà không báo trước thì bồi thường gấp đôi số tiền bên B đã đặt cọc.\n\nĐIỀU 3: TRÁCH NHIỆM BÊN A\n• Giao nhà, trang thiết bị trong nhà cho bên B đúng ngày ký hợp đồng.\n• Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.\n• Bố trí người để dọn vệ sinh cho những khu vực sinh hoạt chung nhằm tạo ra môi trường sống sạch sẽ cho người thuê phòng.\n• Trang bị khóa vân tay cổng chính và nơi để xe cho khách thuê phòng.\n• Bố trí bảo vệ trông xe cho Bên B, nếu xảy ra mất mát mà do lỗi bên A thì bên A phải bồi thường lại tài sản đó sau khi đã khấu hao sử dụng.\n\nĐIỀU 4: TRÁCH NHIỆM BÊN B\n• Trả tiền thuê phòng và các khoản phí hàng tháng theo điều 2 của hợp đồng.\n• Sử dụng đúng mục đích thuê nhà, khi cần sửa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.\n• Cung cấp đầy đủ giấy tờ tùy thân cho bên A để làm đăng ký tạm trú.\n• Đồ đạc trang thiết bị trong nhà phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát, nếu hư hỏng mất mát thì phải bồi thường hoặc khắc phục lại trạng thái ban đầu.\n• Phải thông báo cho bên A biết trước nếu muốn khoan, đục hay cải tạo lại căn phòng đang sử dụng, trường hợp nếu bên B cố ý cải tạo mà không xin phép thì bên A có quyền chấm dứt hợp đồng với bên B tại thời điểm đó và bên B phải khôi phục lại trạng thái ban đầu hoặc trả tiền để bên A thuê thợ làm.\n• Quý khách tự ý dọn đi ra khỏi trọ là không còn quyền sử dụng phòng và không được tự ý ra vào trọ, không có quyền khiếu nại về sau và được xem là tự ý bỏ cọc. Quý khách tự ý bỏ cọc cũng phải thanh toán điện chi phí phát sinh.\n• Để xe ngay ngắn, gọn gàng, đúng vạch đúng nơi quy định, theo sự hướng dẫn của Quản lý.\n• Giữ vệ sinh môi trường chung và không được tụ tập gây ồn ào ảnh hưởng đến những người xung quanh.\n• Báo cho bên A biết trước 30 ngày nếu muốn ngưng hợp đồng thuê không tiếp tục gia hạn ở nữa, không giải quyết trường hợp đi trước hạn hợp đồng. Ngày nhận báo trả phòng là 1-5 tây hàng tháng.\n• Quý khách muốn sang phòng phải báo với quản lý và giá mới sẽ theo chủ nhà nếu có thay đổi.\n• Khi trả phòng quý khách phải dọn phòng sạch sẽ, nếu phòng còn rác, toilet và bếp đóng vàng dơ thì phải trả tiền thuê người vệ sinh.\n• Không được tàng trữ ma túy, hàng Quốc Cấm, các thiết bị hay vật liệu dễ gây cháy nổ vào trong khu trọ. Nghiêm cấm đánh bạc dưới mọi hình thức. Không được chứa chấp tội phạm liên quan đến pháp luật và thực hiện mọi hành vi khác vi phạm pháp luật, nếu có phải hoàn toàn tự chịu trách nhiệm trước pháp luật.\n• Không làm ồn sau 21h30, không được dẫn bạn thường xuyên về phòng tổ chức ăn nhậu. Không được hát Karaoke gây ồn ào trong dãy trọ. Dắt bạn về phòng phải gặp quản lý để báo cáo trình giấy tờ.\n\nĐIỀU 5: ĐIỀU KHOẢN CHUNG\n• Hợp đồng có hiệu lực kể từ ngày ký.\n• Được lập thành 02 bản, mỗi bên giữ 01 bản có giá trị pháp lý như nhau.\n• Trong quá trình thực hiện hợp đồng, nếu có vấn đề phát sinh hai bên cùng nhau bàn bạc giải quyết trên tinh thần hợp tác, tôn trọng lẫn nhau.\n\n        BÊN A                              BÊN B\n   (Ký, ghi rõ họ tên)              (Ký, ghi rõ họ tên)', 'hieu_luc', '2026-05-18 03:46:39', 0, NULL, NULL),
(4, 4, 10, '2026-05-21', '2026-11-21', 3300000.00, 'chú ý', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM\nĐộc lập – Tự do – Hạnh phúc\n—————————————\n\nHỢP ĐỒNG THUÊ PHÒNG TRỌ\n\nHôm nay, ngày {ngay_ky}, tại {dia_chi_phong}, chúng tôi gồm:\n\nBÊN CHO THUÊ (Bên A):\n- Họ và tên: {ten_chu_tro}\n- Điện thoại: {sdt_chu_tro}\n\nBÊN THUÊ (Bên B):\n- Họ và tên: {ho_ten}\n- CCCD/CMND: {cccd}\n- Điện thoại: {sdt}\n- Địa chỉ thường trú: {dia_chi}\n\nHai bên cùng thỏa thuận và đồng ý với nội dung sau:\n\nĐIỀU 1:\n• Bên A đồng ý cho bên B thuê một phòng thuộc nhà số: {so_phong} với các điều kiện sau đây:\n• Thời hạn thuê là: kể từ ngày {ngay_bat_dau} đến ngày {ngay_ket_thuc}.\n• Mục đích thuê phòng của bên B là để ở và với tổng số người ở là: {so_nguoi} người, trong quá trình ở nếu có phát sinh thêm người vào ở thì bên B phải báo trước cho bên A, nếu như bên B tự tiện cho người lạ vào ở khi chưa có sự đồng ý của bên A thì coi như bên B vi phạm hợp đồng và phải chịu mất tiền đặt cọc.\n• Khi Bên B giảm người và xe so với đăng ký ban đầu thì cũng phải báo cho bên A biết để kiểm soát, nếu không thông báo thì sẽ không giải quyết về tiền xe, người ở lại cũng phải chịu trách nhiệm khi người chuyển đi vi phạm pháp luật và các quy định của bên A.\n\nĐIỀU 2: ĐƠN GIÁ VÀ THANH TOÁN\n• Đơn giá phòng và các dịch vụ tiện ích như sau:\n  - Đơn giá phòng: {gia_thue} đồng/tháng\n  - Đơn giá điện: 3.500 đồng/kWh (Bằng chữ: Ba ngàn năm trăm đồng)\n  - Đơn giá nước: 20.000 đồng/m³ (Bằng chữ: Hai mươi ngàn đồng)\n  - Đơn giá DV Wifi, Rác, VS...: 150.000 đồng/phòng (Bằng chữ: Một trăm năm mươi ngàn đồng)\n  - Giữ xe: 100.000 đồng/chiếc/tháng (Bằng chữ: Một trăm ngàn đồng)\n• Các đơn giá trên sẽ giữ cố định, riêng giá thuê phòng được điều chỉnh tăng hàng năm từ 3-7%, nếu có bất cứ thay đổi nào khác thì bên A sẽ thông báo cho bên B biết trước ít nhất 20 ngày.\n• Tiền thuê phòng bên B thanh toán cho bên A từ ngày 01-05 Tây hàng tháng.\n• Bên B đặt tiền cọc là: {tien_coc} đồng cho bên A.\n• Tiền đặt cọc sẽ được trả lại cho bên thuê khi kết thúc hợp đồng thuê phòng sau khi trừ các khoản tiền điện, nước, phí dịch vụ và các khoản khác liên quan, khi bên B thực hiện đúng quy định các điều khoản trong hợp đồng.\n• Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền đặt cọc và phải thanh toán chi phí phát sinh.\n• Bên A ngưng hợp đồng trước thời hạn sẽ báo trước cho Bên B 30 ngày để sắp xếp chỗ ở mới.\n• Bên A ngưng hợp đồng (lấy lại phòng) trước thời hạn mà không báo trước thì bồi thường gấp đôi số tiền bên B đã đặt cọc.\n\nĐIỀU 3: TRÁCH NHIỆM BÊN A\n• Giao nhà, trang thiết bị trong nhà cho bên B đúng ngày ký hợp đồng.\n• Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.\n• Bố trí người để dọn vệ sinh cho những khu vực sinh hoạt chung nhằm tạo ra môi trường sống sạch sẽ cho người thuê phòng.\n• Trang bị khóa vân tay cổng chính và nơi để xe cho khách thuê phòng.\n• Bố trí bảo vệ trông xe cho Bên B, nếu xảy ra mất mát mà do lỗi bên A thì bên A phải bồi thường lại tài sản đó sau khi đã khấu hao sử dụng.\n\nĐIỀU 4: TRÁCH NHIỆM BÊN B\n• Trả tiền thuê phòng và các khoản phí hàng tháng theo điều 2 của hợp đồng.\n• Sử dụng đúng mục đích thuê nhà, khi cần sửa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.\n• Cung cấp đầy đủ giấy tờ tùy thân cho bên A để làm đăng ký tạm trú.\n• Đồ đạc trang thiết bị trong nhà phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát, nếu hư hỏng mất mát thì phải bồi thường hoặc khắc phục lại trạng thái ban đầu.\n• Phải thông báo cho bên A biết trước nếu muốn khoan, đục hay cải tạo lại căn phòng đang sử dụng, trường hợp nếu bên B cố ý cải tạo mà không xin phép thì bên A có quyền chấm dứt hợp đồng với bên B tại thời điểm đó và bên B phải khôi phục lại trạng thái ban đầu hoặc trả tiền để bên A thuê thợ làm.\n• Quý khách tự ý dọn đi ra khỏi trọ là không còn quyền sử dụng phòng và không được tự ý ra vào trọ, không có quyền khiếu nại về sau và được xem là tự ý bỏ cọc. Quý khách tự ý bỏ cọc cũng phải thanh toán điện chi phí phát sinh.\n• Để xe ngay ngắn, gọn gàng, đúng vạch đúng nơi quy định, theo sự hướng dẫn của Quản lý.\n• Giữ vệ sinh môi trường chung và không được tụ tập gây ồn ào ảnh hưởng đến những người xung quanh.\n• Báo cho bên A biết trước 30 ngày nếu muốn ngưng hợp đồng thuê không tiếp tục gia hạn ở nữa, không giải quyết trường hợp đi trước hạn hợp đồng. Ngày nhận báo trả phòng là 1-5 tây hàng tháng.\n• Quý khách muốn sang phòng phải báo với quản lý và giá mới sẽ theo chủ nhà nếu có thay đổi.\n• Khi trả phòng quý khách phải dọn phòng sạch sẽ, nếu phòng còn rác, toilet và bếp đóng vàng dơ thì phải trả tiền thuê người vệ sinh.\n• Không được tàng trữ ma túy, hàng Quốc Cấm, các thiết bị hay vật liệu dễ gây cháy nổ vào trong khu trọ. Nghiêm cấm đánh bạc dưới mọi hình thức. Không được chứa chấp tội phạm liên quan đến pháp luật và thực hiện mọi hành vi khác vi phạm pháp luật, nếu có phải hoàn toàn tự chịu trách nhiệm trước pháp luật.\n• Không làm ồn sau 21h30, không được dẫn bạn thường xuyên về phòng tổ chức ăn nhậu. Không được hát Karaoke gây ồn ào trong dãy trọ. Dắt bạn về phòng phải gặp quản lý để báo cáo trình giấy tờ.\n\nĐIỀU 5: ĐIỀU KHOẢN CHUNG\n• Hợp đồng có hiệu lực kể từ ngày ký.\n• Được lập thành 02 bản, mỗi bên giữ 01 bản có giá trị pháp lý như nhau.\n• Trong quá trình thực hiện hợp đồng, nếu có vấn đề phát sinh hai bên cùng nhau bàn bạc giải quyết trên tinh thần hợp tác, tôn trọng lẫn nhau.\n\n        BÊN A                              BÊN B\n   (Ký, ghi rõ họ tên)              (Ký, ghi rõ họ tên)', 'hieu_luc', '2026-05-21 05:13:53', 0, NULL, NULL),
(5, 6, 11, '2026-05-26', '2026-11-26', 4000000.00, '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM\nĐộc lập – Tự do – Hạnh phúc\n—————————————\n\nHỢP ĐỒNG THUÊ PHÒNG TRỌ\n\nHôm nay, ngày {ngay_ky}, tại {dia_chi_phong}, chúng tôi gồm:\n\nBÊN CHO THUÊ (Bên A):\n- Họ và tên: {ten_chu_tro}\n- Điện thoại: {sdt_chu_tro}\n\nBÊN THUÊ (Bên B):\n- Họ và tên: {ho_ten}\n- CCCD/CMND: {cccd}\n- Điện thoại: {sdt}\n- Địa chỉ thường trú: {dia_chi}\n\nHai bên cùng thỏa thuận và đồng ý với nội dung sau:\n\nĐIỀU 1:\n• Bên A đồng ý cho bên B thuê một phòng thuộc nhà số: {so_phong} với các điều kiện sau đây:\n• Thời hạn thuê là: kể từ ngày {ngay_bat_dau} đến ngày {ngay_ket_thuc}.\n• Mục đích thuê phòng của bên B là để ở và với tổng số người ở là: {so_nguoi} người, trong quá trình ở nếu có phát sinh thêm người vào ở thì bên B phải báo trước cho bên A, nếu như bên B tự tiện cho người lạ vào ở khi chưa có sự đồng ý của bên A thì coi như bên B vi phạm hợp đồng và phải chịu mất tiền đặt cọc.\n• Khi Bên B giảm người và xe so với đăng ký ban đầu thì cũng phải báo cho bên A biết để kiểm soát, nếu không thông báo thì sẽ không giải quyết về tiền xe, người ở lại cũng phải chịu trách nhiệm khi người chuyển đi vi phạm pháp luật và các quy định của bên A.\n\nĐIỀU 2: ĐƠN GIÁ VÀ THANH TOÁN\n• Đơn giá phòng và các dịch vụ tiện ích như sau:\n  - Đơn giá phòng: {gia_thue} đồng/tháng\n  - Đơn giá điện: 3.500 đồng/kWh (Bằng chữ: Ba ngàn năm trăm đồng)\n  - Đơn giá nước: 20.000 đồng/m³ (Bằng chữ: Hai mươi ngàn đồng)\n  - Đơn giá DV Wifi, Rác, VS...: 150.000 đồng/phòng (Bằng chữ: Một trăm năm mươi ngàn đồng)\n  - Giữ xe: 100.000 đồng/chiếc/tháng (Bằng chữ: Một trăm ngàn đồng)\n• Các đơn giá trên sẽ giữ cố định, riêng giá thuê phòng được điều chỉnh tăng hàng năm từ 3-7%, nếu có bất cứ thay đổi nào khác thì bên A sẽ thông báo cho bên B biết trước ít nhất 20 ngày.\n• Tiền thuê phòng bên B thanh toán cho bên A từ ngày 01-05 Tây hàng tháng.\n• Bên B đặt tiền cọc là: {tien_coc} đồng cho bên A.\n• Tiền đặt cọc sẽ được trả lại cho bên thuê khi kết thúc hợp đồng thuê phòng sau khi trừ các khoản tiền điện, nước, phí dịch vụ và các khoản khác liên quan, khi bên B thực hiện đúng quy định các điều khoản trong hợp đồng.\n• Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền đặt cọc và phải thanh toán chi phí phát sinh.\n• Bên A ngưng hợp đồng trước thời hạn sẽ báo trước cho Bên B 30 ngày để sắp xếp chỗ ở mới.\n• Bên A ngưng hợp đồng (lấy lại phòng) trước thời hạn mà không báo trước thì bồi thường gấp đôi số tiền bên B đã đặt cọc.\n\nĐIỀU 3: TRÁCH NHIỆM BÊN A\n• Giao nhà, trang thiết bị trong nhà cho bên B đúng ngày ký hợp đồng.\n• Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.\n• Bố trí người để dọn vệ sinh cho những khu vực sinh hoạt chung nhằm tạo ra môi trường sống sạch sẽ cho người thuê phòng.\n• Trang bị khóa vân tay cổng chính và nơi để xe cho khách thuê phòng.\n• Bố trí bảo vệ trông xe cho Bên B, nếu xảy ra mất mát mà do lỗi bên A thì bên A phải bồi thường lại tài sản đó sau khi đã khấu hao sử dụng.\n\nĐIỀU 4: TRÁCH NHIỆM BÊN B\n• Trả tiền thuê phòng và các khoản phí hàng tháng theo điều 2 của hợp đồng.\n• Sử dụng đúng mục đích thuê nhà, khi cần sửa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.\n• Cung cấp đầy đủ giấy tờ tùy thân cho bên A để làm đăng ký tạm trú.\n• Đồ đạc trang thiết bị trong nhà phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát, nếu hư hỏng mất mát thì phải bồi thường hoặc khắc phục lại trạng thái ban đầu.\n• Phải thông báo cho bên A biết trước nếu muốn khoan, đục hay cải tạo lại căn phòng đang sử dụng, trường hợp nếu bên B cố ý cải tạo mà không xin phép thì bên A có quyền chấm dứt hợp đồng với bên B tại thời điểm đó và bên B phải khôi phục lại trạng thái ban đầu hoặc trả tiền để bên A thuê thợ làm.\n• Quý khách tự ý dọn đi ra khỏi trọ là không còn quyền sử dụng phòng và không được tự ý ra vào trọ, không có quyền khiếu nại về sau và được xem là tự ý bỏ cọc. Quý khách tự ý bỏ cọc cũng phải thanh toán điện chi phí phát sinh.\n• Để xe ngay ngắn, gọn gàng, đúng vạch đúng nơi quy định, theo sự hướng dẫn của Quản lý.\n• Giữ vệ sinh môi trường chung và không được tụ tập gây ồn ào ảnh hưởng đến những người xung quanh.\n• Báo cho bên A biết trước 30 ngày nếu muốn ngưng hợp đồng thuê không tiếp tục gia hạn ở nữa, không giải quyết trường hợp đi trước hạn hợp đồng. Ngày nhận báo trả phòng là 1-5 tây hàng tháng.\n• Quý khách muốn sang phòng phải báo với quản lý và giá mới sẽ theo chủ nhà nếu có thay đổi.\n• Khi trả phòng quý khách phải dọn phòng sạch sẽ, nếu phòng còn rác, toilet và bếp đóng vàng dơ thì phải trả tiền thuê người vệ sinh.\n• Không được tàng trữ ma túy, hàng Quốc Cấm, các thiết bị hay vật liệu dễ gây cháy nổ vào trong khu trọ. Nghiêm cấm đánh bạc dưới mọi hình thức. Không được chứa chấp tội phạm liên quan đến pháp luật và thực hiện mọi hành vi khác vi phạm pháp luật, nếu có phải hoàn toàn tự chịu trách nhiệm trước pháp luật.\n• Không làm ồn sau 21h30, không được dẫn bạn thường xuyên về phòng tổ chức ăn nhậu. Không được hát Karaoke gây ồn ào trong dãy trọ. Dắt bạn về phòng phải gặp quản lý để báo cáo trình giấy tờ.\n\nĐIỀU 5: ĐIỀU KHOẢN CHUNG\n• Hợp đồng có hiệu lực kể từ ngày ký.\n• Được lập thành 02 bản, mỗi bên giữ 01 bản có giá trị pháp lý như nhau.\n• Trong quá trình thực hiện hợp đồng, nếu có vấn đề phát sinh hai bên cùng nhau bàn bạc giải quyết trên tinh thần hợp tác, tôn trọng lẫn nhau.\n\n        BÊN A                              BÊN B\n   (Ký, ghi rõ họ tên)              (Ký, ghi rõ họ tên)', 'hieu_luc', '2026-05-26 08:11:00', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khu_tro`
--

CREATE TABLE `khu_tro` (
  `id` int(11) NOT NULL,
  `ten_khu` varchar(100) NOT NULL,
  `ma_khu` varchar(10) NOT NULL,
  `dia_chi` text DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `quan_ly_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khu_tro`
--

INSERT INTO `khu_tro` (`id`, `ten_khu`, `ma_khu`, `dia_chi`, `mo_ta`, `quan_ly_id`) VALUES
(1, 'KHU A', 'A', '71 Đường 185 Phước Long B', 'bãi xe rộng, camera an ninh, khóa vân tay', NULL),
(2, 'KHU B', 'B', '256 Bưng Ông Thoàn, Phú Hữu', 'camera an ninh, khóa vân tay', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_gia`
--

CREATE TABLE `lich_su_gia` (
  `id` int(11) NOT NULL,
  `phong_id` int(11) NOT NULL,
  `gia_cu` decimal(12,2) NOT NULL,
  `gia_moi` decimal(12,2) NOT NULL,
  `ngay_thay_doi` date NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `nguoi_thay_doi` varchar(100) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_thanh_toan`
--

CREATE TABLE `lich_su_thanh_toan` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `phong_id` int(11) NOT NULL,
  `so_tien` decimal(12,2) NOT NULL,
  `phuong_thuc` enum('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') DEFAULT 'tien_mat',
  `trang_thai` enum('thanh_cong','that_bai','dang_xu_ly') DEFAULT 'thanh_cong',
  `nguoi_thu` varchar(100) DEFAULT '',
  `nguoi_tra` varchar(100) DEFAULT '',
  `ghi_chu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_su_thanh_toan`
--

INSERT INTO `lich_su_thanh_toan` (`id`, `hoa_don_id`, `phong_id`, `so_tien`, `phuong_thuc`, `trang_thai`, `nguoi_thu`, `nguoi_tra`, `ghi_chu`, `created_at`) VALUES
(1, 1, 2, 4990650.00, 'tien_mat', 'thanh_cong', 'minhthuc0103', '', 'Thu tiền tháng 5/2026', '2026-05-18 03:49:14'),
(2, 2, 5, 3108500.00, 'tien_mat', 'thanh_cong', 'minhthuc0103', '', 'Thu tiền tháng 5/2026', '2026-05-18 05:58:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mau_hop_dong`
--

CREATE TABLE `mau_hop_dong` (
  `id` int(11) NOT NULL,
  `ten_mau` varchar(200) NOT NULL,
  `noi_dung` text NOT NULL,
  `mac_dinh` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `mau_hop_dong`
--

INSERT INTO `mau_hop_dong` (`id`, `ten_mau`, `noi_dung`, `mac_dinh`, `created_at`) VALUES
(1, 'Hợp đồng thuê phòng trọ (Mẫu chuẩn)', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM\nĐộc lập – Tự do – Hạnh phúc\n—————————————\n\nHỢP ĐỒNG THUÊ PHÒNG TRỌ\n\nHôm nay, ngày {ngay_ky}, tại {dia_chi_phong}, chúng tôi gồm:\n\nBÊN CHO THUÊ (Bên A):\n- Họ và tên: {ten_chu_tro}\n- Điện thoại: {sdt_chu_tro}\n\nBÊN THUÊ (Bên B):\n- Họ và tên: {ho_ten}\n- CCCD/CMND: {cccd}\n- Điện thoại: {sdt}\n- Địa chỉ thường trú: {dia_chi}\n\nHai bên cùng thỏa thuận và đồng ý với nội dung sau:\n\nĐIỀU 1:\n• Bên A đồng ý cho bên B thuê một phòng thuộc nhà số: {so_phong} với các điều kiện sau đây:\n• Thời hạn thuê là: kể từ ngày {ngay_bat_dau} đến ngày {ngay_ket_thuc}.\n• Mục đích thuê phòng của bên B là để ở và với tổng số người ở là: {so_nguoi} người, trong quá trình ở nếu có phát sinh thêm người vào ở thì bên B phải báo trước cho bên A, nếu như bên B tự tiện cho người lạ vào ở khi chưa có sự đồng ý của bên A thì coi như bên B vi phạm hợp đồng và phải chịu mất tiền đặt cọc.\n• Khi Bên B giảm người và xe so với đăng ký ban đầu thì cũng phải báo cho bên A biết để kiểm soát, nếu không thông báo thì sẽ không giải quyết về tiền xe, người ở lại cũng phải chịu trách nhiệm khi người chuyển đi vi phạm pháp luật và các quy định của bên A.\n\nĐIỀU 2: ĐƠN GIÁ VÀ THANH TOÁN\n• Đơn giá phòng và các dịch vụ tiện ích như sau:\n  - Đơn giá phòng: {gia_thue} đồng/tháng\n  - Đơn giá điện: 3.500 đồng/kWh (Bằng chữ: Ba ngàn năm trăm đồng)\n  - Đơn giá nước: 20.000 đồng/m³ (Bằng chữ: Hai mươi ngàn đồng)\n  - Đơn giá DV Wifi, Rác, VS...: 150.000 đồng/phòng (Bằng chữ: Một trăm năm mươi ngàn đồng)\n  - Giữ xe: 100.000 đồng/chiếc/tháng (Bằng chữ: Một trăm ngàn đồng)\n• Các đơn giá trên sẽ giữ cố định, riêng giá thuê phòng được điều chỉnh tăng hàng năm từ 3-7%, nếu có bất cứ thay đổi nào khác thì bên A sẽ thông báo cho bên B biết trước ít nhất 20 ngày.\n• Tiền thuê phòng bên B thanh toán cho bên A từ ngày 01-05 Tây hàng tháng.\n• Bên B đặt tiền cọc là: {tien_coc} đồng cho bên A.\n• Tiền đặt cọc sẽ được trả lại cho bên thuê khi kết thúc hợp đồng thuê phòng sau khi trừ các khoản tiền điện, nước, phí dịch vụ và các khoản khác liên quan, khi bên B thực hiện đúng quy định các điều khoản trong hợp đồng.\n• Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền đặt cọc và phải thanh toán chi phí phát sinh.\n• Bên A ngưng hợp đồng trước thời hạn sẽ báo trước cho Bên B 30 ngày để sắp xếp chỗ ở mới.\n• Bên A ngưng hợp đồng (lấy lại phòng) trước thời hạn mà không báo trước thì bồi thường gấp đôi số tiền bên B đã đặt cọc.\n\nĐIỀU 3: TRÁCH NHIỆM BÊN A\n• Giao nhà, trang thiết bị trong nhà cho bên B đúng ngày ký hợp đồng.\n• Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.\n• Bố trí người để dọn vệ sinh cho những khu vực sinh hoạt chung nhằm tạo ra môi trường sống sạch sẽ cho người thuê phòng.\n• Trang bị khóa vân tay cổng chính và nơi để xe cho khách thuê phòng.\n• Bố trí bảo vệ trông xe cho Bên B, nếu xảy ra mất mát mà do lỗi bên A thì bên A phải bồi thường lại tài sản đó sau khi đã khấu hao sử dụng.\n\nĐIỀU 4: TRÁCH NHIỆM BÊN B\n• Trả tiền thuê phòng và các khoản phí hàng tháng theo điều 2 của hợp đồng.\n• Sử dụng đúng mục đích thuê nhà, khi cần sửa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.\n• Cung cấp đầy đủ giấy tờ tùy thân cho bên A để làm đăng ký tạm trú.\n• Đồ đạc trang thiết bị trong nhà phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát, nếu hư hỏng mất mát thì phải bồi thường hoặc khắc phục lại trạng thái ban đầu.\n• Phải thông báo cho bên A biết trước nếu muốn khoan, đục hay cải tạo lại căn phòng đang sử dụng, trường hợp nếu bên B cố ý cải tạo mà không xin phép thì bên A có quyền chấm dứt hợp đồng với bên B tại thời điểm đó và bên B phải khôi phục lại trạng thái ban đầu hoặc trả tiền để bên A thuê thợ làm.\n• Quý khách tự ý dọn đi ra khỏi trọ là không còn quyền sử dụng phòng và không được tự ý ra vào trọ, không có quyền khiếu nại về sau và được xem là tự ý bỏ cọc. Quý khách tự ý bỏ cọc cũng phải thanh toán điện chi phí phát sinh.\n• Để xe ngay ngắn, gọn gàng, đúng vạch đúng nơi quy định, theo sự hướng dẫn của Quản lý.\n• Giữ vệ sinh môi trường chung và không được tụ tập gây ồn ào ảnh hưởng đến những người xung quanh.\n• Báo cho bên A biết trước 30 ngày nếu muốn ngưng hợp đồng thuê không tiếp tục gia hạn ở nữa, không giải quyết trường hợp đi trước hạn hợp đồng. Ngày nhận báo trả phòng là 1-5 tây hàng tháng.\n• Quý khách muốn sang phòng phải báo với quản lý và giá mới sẽ theo chủ nhà nếu có thay đổi.\n• Khi trả phòng quý khách phải dọn phòng sạch sẽ, nếu phòng còn rác, toilet và bếp đóng vàng dơ thì phải trả tiền thuê người vệ sinh.\n• Không được tàng trữ ma túy, hàng Quốc Cấm, các thiết bị hay vật liệu dễ gây cháy nổ vào trong khu trọ. Nghiêm cấm đánh bạc dưới mọi hình thức. Không được chứa chấp tội phạm liên quan đến pháp luật và thực hiện mọi hành vi khác vi phạm pháp luật, nếu có phải hoàn toàn tự chịu trách nhiệm trước pháp luật.\n• Không làm ồn sau 21h30, không được dẫn bạn thường xuyên về phòng tổ chức ăn nhậu. Không được hát Karaoke gây ồn ào trong dãy trọ. Dắt bạn về phòng phải gặp quản lý để báo cáo trình giấy tờ.\n\nĐIỀU 5: ĐIỀU KHOẢN CHUNG\n• Hợp đồng có hiệu lực kể từ ngày ký.\n• Được lập thành 02 bản, mỗi bên giữ 01 bản có giá trị pháp lý như nhau.\n• Trong quá trình thực hiện hợp đồng, nếu có vấn đề phát sinh hai bên cùng nhau bàn bạc giải quyết trên tinh thần hợp tác, tôn trọng lẫn nhau.\n\n        BÊN A                              BÊN B\n   (Ký, ghi rõ họ tên)              (Ký, ghi rõ họ tên)', 1, '2026-05-18 03:16:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_thue`
--

CREATE TABLE `nguoi_thue` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `cccd` varchar(20) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `dia_chi` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT '',
  `account_id` int(11) DEFAULT NULL,
  `cccd_truoc` varchar(255) DEFAULT '' COMMENT 'Đường dẫn ảnh CCCD mặt trước',
  `cccd_sau` varchar(255) DEFAULT '' COMMENT 'Đường dẫn ảnh CCCD mặt sau',
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('nam','nu','khac') DEFAULT 'nam',
  `email` varchar(100) DEFAULT '',
  `nghe_nghiep` varchar(100) DEFAULT '',
  `noi_cap_cccd` varchar(100) DEFAULT '',
  `ngay_cap_cccd` date DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_thue`
--

INSERT INTO `nguoi_thue` (`id`, `ho_ten`, `cccd`, `sdt`, `dia_chi`, `avatar`, `account_id`, `cccd_truoc`, `cccd_sau`, `ngay_sinh`, `gioi_tinh`, `email`, `nghe_nghiep`, `noi_cap_cccd`, `ngay_cap_cccd`, `ghi_chu`) VALUES
(2, 'Tô Thế Kiệt', '354383586168', '0357168681', 'ạhduhwiuhksabiughwighasndna12', '', NULL, '', '', '2004-11-20', 'nam', '', '', '', NULL, NULL),
(4, 'Phạm Thái Phong', '897684643612', '0941032535', 'mnasmbnasjhfashfjkoaslk 1231', '', 3, '', '', NULL, 'nam', '', '', '', NULL, NULL),
(8, 'tien', '3543835863815', '015484512', '', '', 6, '', '', '2006-08-06', 'nam', '', '', '', NULL, NULL),
(9, 'tri', '', '0251585542', '', '', 7, '', '', NULL, 'nam', '', '', '', NULL, NULL),
(10, 'trong', '', '', '', '', 5, '', '', NULL, 'nam', '', '', '', NULL, NULL),
(11, 'tien', '3543835863815', '015484512', '', '', 6, '', '', NULL, 'nam', '', '', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_thue_phong`
--

CREATE TABLE `nguoi_thue_phong` (
  `id` int(11) NOT NULL,
  `hop_dong_id` int(11) NOT NULL,
  `phong_id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `cccd` varchar(20) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('nam','nu','khac') DEFAULT 'nam',
  `que_quan` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT '',
  `la_chu_hop_dong` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_thue_phong`
--

INSERT INTO `nguoi_thue_phong` (`id`, `hop_dong_id`, `phong_id`, `ho_ten`, `cccd`, `sdt`, `ngay_sinh`, `gioi_tinh`, `que_quan`, `avatar`, `la_chu_hop_dong`) VALUES
(1, 1, 2, 'Phạm Thái Phong', '897684643612', '0941032535', NULL, 'nam', 'mnasmbnasjhfashfjkoaslk 1231', 'public/uploads/signatures/sig_1_1779075999.png', 1),
(2, 1, 2, 'thế kiệt', '01526255622', '03549571', '2005-05-19', 'nam', '', '', 0),
(5, 4, 4, 'trong', '', '', NULL, 'nam', '', 'public/uploads/signatures/sig_4_1779340433.png', 1),
(6, 5, 6, 'tien', '3543835863815', '015484512', '2005-05-05', 'nam', '', 'public/uploads/signatures/sig_5_1779783060.png', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phong`
--

CREATE TABLE `phong` (
  `id` int(11) NOT NULL,
  `khu_id` int(11) DEFAULT NULL,
  `so_phong` varchar(50) NOT NULL,
  `gia` decimal(12,2) NOT NULL,
  `dien_tich` decimal(6,2) DEFAULT 0.00,
  `so_nguoi` int(11) DEFAULT 4 COMMENT 'Sức chứa tối đa',
  `so_nguoi_hien_tai` int(11) DEFAULT 0,
  `mo_ta` text DEFAULT NULL,
  `anh_phong` varchar(255) DEFAULT '',
  `trang_thai` enum('trong','dang_thue','bao_tri') DEFAULT 'trong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phong`
--

INSERT INTO `phong` (`id`, `khu_id`, `so_phong`, `gia`, `dien_tich`, `so_nguoi`, `so_nguoi_hien_tai`, `mo_ta`, `anh_phong`, `trang_thai`) VALUES
(2, 1, 'A101', 4900000.00, 20.00, 4, 0, 'điều hòa', '[\"public\\/uploads\\/phong\\/phong_1778220823_7816_0.jpg\",\"public\\/uploads\\/phong\\/phong_1778220823_6171_1.jpg\",\"public\\/uploads\\/phong\\/phong_1778220823_8486_2.jpg\",\"public\\/uploads\\/phong\\/phong_1778220823_8349_3.jpg\",\"public\\/uploads\\/phong\\/phong_1778220', 'dang_thue'),
(3, 1, 'A102', 4900000.00, 18.00, 2, 0, 'Điều Hòa, Nóng Lạnh', '', 'trong'),
(4, 2, 'B101', 3300000.00, 20.00, 4, 0, 'Thoáng mát', '[\"public\\/uploads\\/phong\\/phong_1778221175_4148_0.jpg\",\"public\\/uploads\\/phong\\/phong_1778221175_6433_1.jpg\"]', 'dang_thue'),
(5, 2, 'B102', 3000000.00, 17.00, 3, 0, 'ban công', '', 'trong'),
(6, 1, 'A103', 4000000.00, 18.00, 3, 0, '', '', 'dang_thue');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL,
  `tieu_de` varchar(200) NOT NULL,
  `noi_dung` text NOT NULL,
  `loai` enum('chung','khan_cap','bao_tri','tien_phong','khac') DEFAULT 'chung',
  `nguoi_gui` varchar(100) DEFAULT '',
  `ghim` tinyint(1) DEFAULT 0 COMMENT 'Ghim lên đầu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_bao`
--

INSERT INTO `thong_bao` (`id`, `tieu_de`, `noi_dung`, `loai`, `nguoi_gui`, `ghim`, `created_at`) VALUES
(1, 'cần sửa máy giặt', 'máy gặt bị hư', 'bao_tri', 'Tô Minh Thức', 0, '2026-05-18 05:39:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao_da_doc`
--

CREATE TABLE `thong_bao_da_doc` (
  `id` int(11) NOT NULL,
  `thong_bao_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doc_luc` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_bao_da_doc`
--

INSERT INTO `thong_bao_da_doc` (`id`, `thong_bao_id`, `user_id`, `doc_luc`) VALUES
(1, 1, 5, '2026-05-18 05:39:24'),
(2, 1, 1, '2026-05-18 08:38:37'),
(3, 1, 3, '2026-05-18 08:58:51'),
(7, 1, 6, '2026-05-28 04:47:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `xe`
--

CREATE TABLE `xe` (
  `id` int(11) NOT NULL,
  `hop_dong_id` int(11) NOT NULL,
  `phong_id` int(11) NOT NULL,
  `bien_so` varchar(20) NOT NULL,
  `loai_xe` enum('xe_may','xe_dien','xe_dap') DEFAULT 'xe_may',
  `mau_sac` varchar(50) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `xe`
--

INSERT INTO `xe` (`id`, `hop_dong_id`, `phong_id`, `bien_so`, `loai_xe`, `mau_sac`, `ghi_chu`) VALUES
(1, 1, 2, '36L1-456.12', 'xe_may', 'ab', 'Xe của: thế kiệt'),
(2, 1, 2, '51L1-123.45', 'xe_may', 'sh', ''),
(5, 4, 4, '49H-123.54', 'xe_may', 'wave', ''),
(6, 5, 6, '60B1-53515', 'xe_may', 'Air Black', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeu_cau_sua_chua`
--

CREATE TABLE `yeu_cau_sua_chua` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ho_ten` varchar(100) DEFAULT '',
  `phong` varchar(50) DEFAULT '',
  `vi_tri` varchar(200) NOT NULL,
  `mo_ta` text NOT NULL,
  `muc_do` enum('nhe','trung_binh','khan_cap') DEFAULT 'trung_binh',
  `trang_thai` enum('cho_xu_ly','dang_xu_ly','da_xong') DEFAULT 'cho_xu_ly',
  `ghi_chu_ql` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `yeu_cau_sua_chua`
--

INSERT INTO `yeu_cau_sua_chua` (`id`, `user_id`, `ho_ten`, `phong`, `vi_tri`, `mo_ta`, `muc_do`, `trang_thai`, `ghi_chu_ql`, `created_at`) VALUES
(1, 6, 'tien', 'A103', 'máy lạnh', 'bật máy lạnh thì nó chảy nước', 'khan_cap', 'cho_xu_ly', '', '2026-05-28 05:19:20');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `ai_knowledge`
--
ALTER TABLE `ai_knowledge`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `ai_unknown`
--
ALTER TABLE `ai_unknown`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `don_gia`
--
ALTER TABLE `don_gia`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_phong_thang_nam` (`phong_id`,`thang`,`nam`);

--
-- Chỉ mục cho bảng `hop_dong`
--
ALTER TABLE `hop_dong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phong_id` (`phong_id`),
  ADD KEY `nguoi_thue_id` (`nguoi_thue_id`);

--
-- Chỉ mục cho bảng `khu_tro`
--
ALTER TABLE `khu_tro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_khu` (`ma_khu`);

--
-- Chỉ mục cho bảng `lich_su_gia`
--
ALTER TABLE `lich_su_gia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phong_id` (`phong_id`);

--
-- Chỉ mục cho bảng `lich_su_thanh_toan`
--
ALTER TABLE `lich_su_thanh_toan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoa_don_id` (`hoa_don_id`),
  ADD KEY `phong_id` (`phong_id`);

--
-- Chỉ mục cho bảng `mau_hop_dong`
--
ALTER TABLE `mau_hop_dong`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `nguoi_thue`
--
ALTER TABLE `nguoi_thue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Chỉ mục cho bảng `nguoi_thue_phong`
--
ALTER TABLE `nguoi_thue_phong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hop_dong_id` (`hop_dong_id`),
  ADD KEY `phong_id` (`phong_id`);

--
-- Chỉ mục cho bảng `phong`
--
ALTER TABLE `phong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `khu_id` (`khu_id`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `thong_bao_da_doc`
--
ALTER TABLE `thong_bao_da_doc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tb_user` (`thong_bao_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `xe`
--
ALTER TABLE `xe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hop_dong_id` (`hop_dong_id`),
  ADD KEY `phong_id` (`phong_id`);

--
-- Chỉ mục cho bảng `yeu_cau_sua_chua`
--
ALTER TABLE `yeu_cau_sua_chua`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `ai_knowledge`
--
ALTER TABLE `ai_knowledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ai_unknown`
--
ALTER TABLE `ai_unknown`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `don_gia`
--
ALTER TABLE `don_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `hop_dong`
--
ALTER TABLE `hop_dong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `khu_tro`
--
ALTER TABLE `khu_tro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `lich_su_gia`
--
ALTER TABLE `lich_su_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_su_thanh_toan`
--
ALTER TABLE `lich_su_thanh_toan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `mau_hop_dong`
--
ALTER TABLE `mau_hop_dong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `nguoi_thue`
--
ALTER TABLE `nguoi_thue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `nguoi_thue_phong`
--
ALTER TABLE `nguoi_thue_phong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `phong`
--
ALTER TABLE `phong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `thong_bao_da_doc`
--
ALTER TABLE `thong_bao_da_doc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `xe`
--
ALTER TABLE `xe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `yeu_cau_sua_chua`
--
ALTER TABLE `yeu_cau_sua_chua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `hoa_don_ibfk_1` FOREIGN KEY (`phong_id`) REFERENCES `phong` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hop_dong`
--
ALTER TABLE `hop_dong`
  ADD CONSTRAINT `hop_dong_ibfk_1` FOREIGN KEY (`phong_id`) REFERENCES `phong` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hop_dong_ibfk_2` FOREIGN KEY (`nguoi_thue_id`) REFERENCES `nguoi_thue` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lich_su_gia`
--
ALTER TABLE `lich_su_gia`
  ADD CONSTRAINT `lich_su_gia_ibfk_1` FOREIGN KEY (`phong_id`) REFERENCES `phong` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lich_su_thanh_toan`
--
ALTER TABLE `lich_su_thanh_toan`
  ADD CONSTRAINT `lich_su_thanh_toan_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_su_thanh_toan_ibfk_2` FOREIGN KEY (`phong_id`) REFERENCES `phong` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nguoi_thue`
--
ALTER TABLE `nguoi_thue`
  ADD CONSTRAINT `nguoi_thue_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `nguoi_thue_phong`
--
ALTER TABLE `nguoi_thue_phong`
  ADD CONSTRAINT `nguoi_thue_phong_ibfk_1` FOREIGN KEY (`hop_dong_id`) REFERENCES `hop_dong` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nguoi_thue_phong_ibfk_2` FOREIGN KEY (`phong_id`) REFERENCES `phong` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `phong`
--
ALTER TABLE `phong`
  ADD CONSTRAINT `phong_ibfk_1` FOREIGN KEY (`khu_id`) REFERENCES `khu_tro` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `thong_bao_da_doc`
--
ALTER TABLE `thong_bao_da_doc`
  ADD CONSTRAINT `thong_bao_da_doc_ibfk_1` FOREIGN KEY (`thong_bao_id`) REFERENCES `thong_bao` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `thong_bao_da_doc_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `xe`
--
ALTER TABLE `xe`
  ADD CONSTRAINT `xe_ibfk_1` FOREIGN KEY (`hop_dong_id`) REFERENCES `hop_dong` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `xe_ibfk_2` FOREIGN KEY (`phong_id`) REFERENCES `phong` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- BỔ SUNG TÍNH NĂNG YÊU CẦU CHUYỂN PHÒNG
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
