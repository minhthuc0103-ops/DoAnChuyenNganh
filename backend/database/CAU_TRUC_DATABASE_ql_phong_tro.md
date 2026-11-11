# Cấu trúc database `ql_phong_tro`

Lập từ file `backend/database/ql_phong_tro.sql` cộng 2 migration (`migration_columns.sql`, `upgrade_roommate_requests.sql`) của dự án.

**20 bảng, 196 cột, 15 khóa ngoại.** Số dòng dưới đây là dữ liệu mẫu có sẵn trong file dump, database thật của bạn có thể khác.

## 1. Danh sách bảng theo nhóm

### Tài sản và cư trú

| Bảng | Là gì | Số cột | Dòng mẫu |
|---|---|---|---|
| `khu_tro` | **Khu trọ**. Danh sách khu trọ (tên khu, địa chỉ). | 6 | 2 |
| `phong` | **Phòng**. Phòng thuộc từng khu: số phòng, giá, diện tích, trạng thái (trống, đang thuê, bảo trì), ảnh. | 10 | 5 |
| `nguoi_thue` | **Hồ sơ người thuê**. Họ tên, CCCD và đường dẫn ảnh CCCD, SĐT, email, địa chỉ, ngày sinh. Liên kết với account. | 16 | 6 |
| `nguoi_thue_phong` | **Người ở trong phòng**. Người đứng tên hợp đồng và người ở cùng theo từng hợp đồng. | 11 | 4 |
| `xe` | **Xe gửi**. Biển số, loại xe, màu của từng hợp đồng (tính phí xe). | 7 | 4 |

### Hợp đồng

| Bảng | Là gì | Số cột | Dòng mẫu |
|---|---|---|---|
| `hop_dong` | **Hợp đồng thuê**. Ngày bắt đầu/kết thúc, tiền cọc, nội dung, trạng thái, yêu cầu hủy và ngày dự kiến ra. | 13 | 3 |
| `mau_hop_dong` | **Mẫu hợp đồng**. Nội dung mẫu để tạo hợp đồng mới. | 5 | 1 |

### Tài chính

| Bảng | Là gì | Số cột | Dòng mẫu |
|---|---|---|---|
| `hoa_don` | **Hóa đơn hàng tháng**. Chỉ số điện nước cũ/mới, tiền phòng, điện, nước, phí dịch vụ, phí xe, tổng tiền, trạng thái thanh toán. | 21 | 5 |
| `don_gia` | **Đơn giá hiện hành**. Giá điện, giá nước, phí dịch vụ đang áp dụng khi tạo hóa đơn. | 4 | 1 |
| `lich_su_gia` | **Lịch sử đổi giá phòng**. Giá cũ, giá mới, ngày đổi, người đổi. | 8 | 0 |
| `lich_su_thanh_toan` | **Lịch sử thu tiền**. Mỗi lần chủ trọ xác nhận thu tiền hóa đơn. | 10 | 2 |

### Tài khoản và tương tác

| Bảng | Là gì | Số cột | Dòng mẫu |
|---|---|---|---|
| `account` | **Tài khoản đăng nhập**. Quản lý/chủ trọ và người thuê. Có vai_tro, mật khẩu (đã băm), email, SĐT, trạng thái tài khoản. | 12 | 5 |
| `thong_bao` | **Thông báo**. Thông báo từ quản lý gửi cho người thuê. | 7 | 1 |
| `thong_bao_da_doc` | **Trạng thái đọc thông báo**. Ai đã đọc hoặc ẩn thông báo nào. | 4 | 4 |
| `chat_messages` | **Tin nhắn chat**. Chat nhóm giữa người thuê và quản lý (và tin hệ thống). | 7 | 13 |
| `ai_knowledge` | **Kiến thức chatbot AI**. Từ khóa, câu hỏi mẫu và câu trả lời để chatbot dùng. | 6 | 0 |
| `ai_unknown` | **Câu hỏi chatbot chưa trả lời được**. Để quản lý bổ sung kiến thức cho chatbot. | 7 | 3 |

### Yêu cầu của người thuê

| Bảng | Là gì | Số cột | Dòng mẫu |
|---|---|---|---|
| `yeu_cau_chuyen_phong` | **Yêu cầu chuyển phòng**. Người thuê xin chuyển từ phòng này sang phòng khác, quản lý duyệt. | 13 | 0 |
| `yeu_cau_sua_chua` | **Yêu cầu sửa chữa**. Người thuê báo hỏng hóc: vị trí, mô tả, mức độ, trạng thái xử lý. | 10 | 1 |
| `yeu_cau_nguoi_o_cung` | **Yêu cầu thêm người ở cùng**. Người thuê xin thêm người ở cùng, quản lý duyệt. Tạo bởi upgrade_roommate_requests.sql. | 19 | 0 |

## 2. Quan hệ giữa các bảng (khóa ngoại)

| Bảng | Cột | Tham chiếu tới |
|---|---|---|
| `chat_messages` | `user_id` | `account.id` |
| `hoa_don` | `phong_id` | `phong.id` |
| `hop_dong` | `nguoi_thue_id` | `nguoi_thue.id` |
| `hop_dong` | `phong_id` | `phong.id` |
| `lich_su_gia` | `phong_id` | `phong.id` |
| `lich_su_thanh_toan` | `phong_id` | `phong.id` |
| `lich_su_thanh_toan` | `hoa_don_id` | `hoa_don.id` |
| `nguoi_thue` | `account_id` | `account.id` |
| `nguoi_thue_phong` | `hop_dong_id` | `hop_dong.id` |
| `nguoi_thue_phong` | `phong_id` | `phong.id` |
| `phong` | `khu_id` | `khu_tro.id` |
| `thong_bao_da_doc` | `thong_bao_id` | `thong_bao.id` |
| `thong_bao_da_doc` | `user_id` | `account.id` |
| `xe` | `phong_id` | `phong.id` |
| `xe` | `hop_dong_id` | `hop_dong.id` |

Sơ đồ chính: `khu_tro` → `phong` → `hop_dong` ← `nguoi_thue` ← `account`; `hoa_don` thuộc `phong`, `lich_su_thanh_toan` thuộc `hoa_don`.

## 3. Bảng tự tạo khi chạy app (không có trong file dump)

| Bảng | Khi nào có | Dùng để |
|---|---|---|
| `hoa_don_gui` | Lần đầu gửi hóa đơn (chức năng gửi Gmail/Zalo) | Nhật ký gửi hóa đơn và biên nhận: kênh, kết quả, lỗi |

Các bảng này tự tạo, không cần nhập file SQL.

## 4. Chi tiết từng bảng

### `account` — Tài khoản đăng nhập

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `ho_ten` | varchar(100) | không |  |  |  |
| `username` | varchar(50) | không | UNI |  |  |
| `password` | varchar(255) | không |  |  |  |
| `email` | varchar(100) | có |  | NULL |  |
| `sdt` | varchar(20) | có |  | NULL |  |
| `vai_tro` | enum('quan_ly','chu_tro','user') | có |  | 'user' |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |
| `chat_joined_at` | datetime | có |  | NULL |  |
| `account_status` | enum('dang_hoat_dong','ngung_hoat_dong','luu_tru') | không |  | 'dang_hoat_dong' |  |
| `account_status_changed_at` | datetime | có |  | NULL |  |
| `account_status_reason` | varchar(255) | có |  | NULL |  |

### `ai_knowledge` — Kiến thức chatbot AI

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `tu_khoa` | text | không |  |  |  |
| `cau_hoi_mau` | text | không |  |  |  |
| `tra_loi` | text | không |  |  |  |
| `so_lan_dung` | int(11) | có |  | 0 |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `ai_unknown` — Câu hỏi chatbot chưa trả lời được

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `cau_hoi` | text | không |  |  |  |
| `user_id` | int(11) | có |  | NULL |  |
| `ho_ten` | varchar(100) | có |  | NULL |  |
| `so_lan` | int(11) | có |  | 1 |  |
| `da_xu_ly` | tinyint(4) | có |  | 0 |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `chat_messages` — Tin nhắn chat

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `user_id` | int(11) | không | MUL |  |  |
| `ho_ten` | varchar(100) | không |  |  |  |
| `vai_tro` | varchar(20) | có |  | 'user' |  |
| `noi_dung` | text | không |  |  |  |
| `loai` | enum('text','image','system') | có |  | 'text' |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `don_gia` — Đơn giá hiện hành

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `gia_dien` | decimal(10,2) | có |  | 3500.00 |  |
| `gia_nuoc` | decimal(10,2) | có |  | 15000.00 |  |
| `phi_dv` | decimal(12,2) | có |  | 150000.00 |  |

### `hoa_don` — Hóa đơn hàng tháng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `phong_id` | int(11) | không | MUL |  |  |
| `thang` | int(11) | không |  |  |  |
| `nam` | int(11) | không |  |  |  |
| `chi_so_dien_cu` | decimal(10,2) | có |  | 0.00 |  |
| `chi_so_dien_moi` | decimal(10,2) | có |  | 0.00 |  |
| `chi_so_nuoc_cu` | decimal(10,2) | có |  | 0.00 |  |
| `chi_so_nuoc_moi` | decimal(10,2) | có |  | 0.00 |  |
| `tien_phong` | decimal(12,2) | có |  | 0.00 |  |
| `tien_dien` | decimal(12,2) | có |  | 0.00 |  |
| `tien_nuoc` | decimal(12,2) | có |  | 0.00 |  |
| `tong_tien` | decimal(12,2) | có |  | 0.00 |  |
| `trang_thai` | enum('chua_tt','da_tt') | có |  | 'chua_tt' |  |
| `phuong_thuc_tt` | enum('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') | có |  | 'tien_mat' |  |
| `ngay_thanh_toan` | datetime | có |  | NULL |  |
| `nguoi_thu` | varchar(100) | có |  | '' |  |
| `ghi_chu_tt` | text | có |  | NULL |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |
| `phi_dich_vu` | decimal(12,2) | có |  | 0.00 |  |
| `phi_xe` | decimal(12,2) | có |  | 0.00 |  |
| `so_xe` | int(11) | có |  | 0 |  |

### `hop_dong` — Hợp đồng thuê

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `phong_id` | int(11) | không | MUL |  |  |
| `nguoi_thue_id` | int(11) | không | MUL |  |  |
| `ngay_bat_dau` | date | không |  |  |  |
| `ngay_ket_thuc` | date | không |  |  |  |
| `tien_coc` | decimal(12,2) | có |  | 0.00 |  |
| `ghi_chu` | text | có |  | NULL |  |
| `noi_dung` | text | có |  | NULL |  |
| `trang_thai` | enum('hieu_luc','het_han','da_huy') | có |  | 'hieu_luc' |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |
| `yeu_cau_huy` | tinyint(1) | có |  | 0 |  |
| `ngay_bao_huy` | date | có |  | NULL |  |
| `ngay_du_kien_ra` | date | có |  | NULL |  |

### `khu_tro` — Khu trọ

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `ten_khu` | varchar(100) | không |  |  |  |
| `ma_khu` | varchar(10) | không | UNI |  |  |
| `dia_chi` | text | có |  | NULL |  |
| `mo_ta` | text | có |  | NULL |  |
| `quan_ly_id` | int(11) | có |  | NULL |  |

### `lich_su_gia` — Lịch sử đổi giá phòng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `phong_id` | int(11) | không | MUL |  |  |
| `gia_cu` | decimal(12,2) | không |  |  |  |
| `gia_moi` | decimal(12,2) | không |  |  |  |
| `ngay_thay_doi` | date | không |  |  |  |
| `ghi_chu` | text | có |  | NULL |  |
| `nguoi_thay_doi` | varchar(100) | có |  | '' |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `lich_su_thanh_toan` — Lịch sử thu tiền

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `hoa_don_id` | int(11) | không | MUL |  |  |
| `phong_id` | int(11) | không | MUL |  |  |
| `so_tien` | decimal(12,2) | không |  |  |  |
| `phuong_thuc` | enum('tien_mat','chuyen_khoan','momo','vnpay','zalopay','khac') | có |  | 'tien_mat' |  |
| `trang_thai` | enum('thanh_cong','that_bai','dang_xu_ly') | có |  | 'thanh_cong' |  |
| `nguoi_thu` | varchar(100) | có |  | '' |  |
| `nguoi_tra` | varchar(100) | có |  | '' |  |
| `ghi_chu` | text | có |  | NULL |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `mau_hop_dong` — Mẫu hợp đồng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `ten_mau` | varchar(200) | không |  |  |  |
| `noi_dung` | text | không |  |  |  |
| `mac_dinh` | tinyint(1) | có |  | 0 |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `nguoi_thue` — Hồ sơ người thuê

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `ho_ten` | varchar(100) | không |  |  |  |
| `cccd` | varchar(20) | có |  | NULL |  |
| `sdt` | varchar(20) | có |  | NULL |  |
| `dia_chi` | text | có |  | NULL |  |
| `avatar` | varchar(255) | có |  | '' |  |
| `account_id` | int(11) | có | MUL | NULL |  |
| `cccd_truoc` | varchar(255) | có |  | '' |  |
| `cccd_sau` | varchar(255) | có |  | '' |  |
| `ngay_sinh` | date | có |  | NULL |  |
| `gioi_tinh` | enum('nam','nu','khac') | có |  | 'nam' |  |
| `email` | varchar(100) | có |  | '' |  |
| `nghe_nghiep` | varchar(100) | có |  | '' |  |
| `noi_cap_cccd` | varchar(100) | có |  | '' |  |
| `ngay_cap_cccd` | date | có |  | NULL |  |
| `ghi_chu` | text | có |  | NULL |  |

### `nguoi_thue_phong` — Người ở trong phòng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `hop_dong_id` | int(11) | không | MUL |  |  |
| `phong_id` | int(11) | không | MUL |  |  |
| `ho_ten` | varchar(100) | không |  |  |  |
| `cccd` | varchar(20) | có |  | NULL |  |
| `sdt` | varchar(20) | có |  | NULL |  |
| `ngay_sinh` | date | có |  | NULL |  |
| `gioi_tinh` | enum('nam','nu','khac') | có |  | 'nam' |  |
| `que_quan` | text | có |  | NULL |  |
| `avatar` | varchar(255) | có |  | '' |  |
| `la_chu_hop_dong` | tinyint(1) | có |  | 0 |  |

### `phong` — Phòng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `khu_id` | int(11) | có | MUL | NULL |  |
| `so_phong` | varchar(50) | không |  |  |  |
| `gia` | decimal(12,2) | không |  |  |  |
| `dien_tich` | decimal(6,2) | có |  | 0.00 |  |
| `so_nguoi` | int(11) | có |  | 4 |  |
| `so_nguoi_hien_tai` | int(11) | có |  | 0 |  |
| `mo_ta` | text | có |  | NULL |  |
| `anh_phong` | varchar(255) | có |  | '' |  |
| `trang_thai` | enum('trong','dang_thue','bao_tri') | có |  | 'trong' |  |

### `thong_bao` — Thông báo

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `tieu_de` | varchar(200) | không |  |  |  |
| `noi_dung` | text | không |  |  |  |
| `loai` | enum('chung','khan_cap','bao_tri','tien_phong','khac') | có |  | 'chung' |  |
| `nguoi_gui` | varchar(100) | có |  | '' |  |
| `ghim` | tinyint(1) | có |  | 0 |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |

### `thong_bao_da_doc` — Trạng thái đọc thông báo

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `thong_bao_id` | int(11) | không | MUL |  |  |
| `user_id` | int(11) | không | MUL |  |  |
| `doc_luc` | timestamp | không |  | current_timestamp() |  |

### `xe` — Xe gửi

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `hop_dong_id` | int(11) | không | MUL |  |  |
| `phong_id` | int(11) | không | MUL |  |  |
| `bien_so` | varchar(20) | không |  |  |  |
| `loai_xe` | enum('xe_may','xe_dien','xe_dap') | có |  | 'xe_may' |  |
| `mau_sac` | varchar(50) | có |  | NULL |  |
| `ghi_chu` | text | có |  | NULL |  |

### `yeu_cau_chuyen_phong` — Yêu cầu chuyển phòng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `user_id` | int(11) | không | MUL |  |  |
| `nguoi_thue_id` | int(11) | không |  |  |  |
| `hop_dong_id` | int(11) | không | MUL |  |  |
| `phong_cu_id` | int(11) | không |  |  |  |
| `phong_moi_id` | int(11) | không | MUL |  |  |
| `ly_do` | text | không |  |  |  |
| `trang_thai` | enum('cho_duyet','da_duyet','tu_choi','da_huy') | có | MUL | 'cho_duyet' |  |
| `phan_hoi_ql` | text | có |  | NULL |  |
| `nguoi_duyet_id` | int(11) | có |  | NULL |  |
| `duyet_luc` | datetime | có |  | NULL |  |
| `created_at` | timestamp | có |  | current_timestamp() |  |
| `updated_at` | timestamp | có |  | current_timestamp() | on update current_timestamp() |

### `yeu_cau_nguoi_o_cung` — Yêu cầu thêm người ở cùng

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `user_id` | int(11) | không | MUL |  |  |
| `nguoi_thue_id` | int(11) | không |  |  |  |
| `hop_dong_id` | int(11) | không | MUL |  |  |
| `phong_id` | int(11) | không |  |  |  |
| `ho_ten` | varchar(150) | không |  |  |  |
| `cccd` | varchar(30) | có |  | '' |  |
| `sdt` | varchar(20) | có |  | '' |  |
| `ngay_sinh` | date | có |  | NULL |  |
| `gioi_tinh` | enum('nam','nu','khac') | có |  | 'nam' |  |
| `que_quan` | varchar(255) | có |  | '' |  |
| `ly_do` | text | có |  | NULL |  |
| `trang_thai` | enum('cho_duyet','da_duyet','tu_choi','da_huy') | có | MUL | 'cho_duyet' |  |
| `phan_hoi_ql` | text | có |  | NULL |  |
| `nguoi_duyet_id` | int(11) | có |  | NULL |  |
| `duyet_luc` | datetime | có |  | NULL |  |
| `nguoi_thue_phong_id` | int(11) | có |  | NULL |  |
| `created_at` | timestamp | có |  | current_timestamp() |  |
| `updated_at` | timestamp | có |  | current_timestamp() | on update current_timestamp() |

### `yeu_cau_sua_chua` — Yêu cầu sửa chữa

| Cột | Kiểu | Null | Khóa | Mặc định | Ghi chú |
|---|---|---|---|---|---|
| `id` | int(11) | không | PRI |  | auto_increment |
| `user_id` | int(11) | không |  |  |  |
| `ho_ten` | varchar(100) | có |  | '' |  |
| `phong` | varchar(50) | có |  | '' |  |
| `vi_tri` | varchar(200) | không |  |  |  |
| `mo_ta` | text | không |  |  |  |
| `muc_do` | enum('nhe','trung_binh','khan_cap') | có |  | 'trung_binh' |  |
| `trang_thai` | enum('cho_xu_ly','dang_xu_ly','da_xong') | có |  | 'cho_xu_ly' |  |
| `ghi_chu_ql` | text | có |  | NULL |  |
| `created_at` | timestamp | không |  | current_timestamp() |  |