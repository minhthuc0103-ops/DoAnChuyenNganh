| MSSV | Họ và Tên | Công việc đã thực hiện | Merge Requests |
|---|---|---|---|
| 2380602201 | Tô Minh Thức | Tách cấu trúc frontend/backend; chuyển thành PWA (manifest, service worker, trang offline); sửa autoload cho hosting Linux; `.gitignore` chặn ảnh CCCD | ** |
| | Trọng | 1. Sửa chữa (ảnh trước/sau, chấm sao, AI phân loại & gợi ý an toàn); 4. Bàn giao và trả phòng (biên bản giao nhận phòng, yêu cầu trả phòng, quyết toán cọc và khấu trừ điện nước/hư hỏng, xuất in A4) | ** |
| | Nguyễn Văn Vũ | Chức năng 2 (Điện nước: Chủ trọ tự chụp hoặc người ở chụp cho AI đọc công tơ tự động); Chức năng 5 (Quản lý khai báo lưu trú, đồng bộ từ HĐ, xuất Mẫu CT01 chuẩn Bộ Công An, xuất CSV/Excel) | ** |

## Cài đặt khi tải code về

1. Bật MySQL (XAMPP), rồi chạy `Database.bat` ở thư mục dự án để tạo database `ql_phong_tro` và các bảng.
2. Chạy PHP: `php -S localhost:8000` tại thư mục dự án, mở http://localhost:8000/index.php
3. **Nút "Đăng nhập bằng Google"** chỉ hiện khi có file khóa riêng (file này không nằm trên Git):
   - Sao chép `backend/config/google.local.example.php` thành `backend/config/google.local.php`
   - Điền `client_id`, `client_secret` lấy ở Google Cloud Console → APIs & Services → Credentials → OAuth client ID (Web application)
   - `redirect_uri` trong file phải giống hệt "Authorized redirect URIs" đã khai báo trên Google (mặc định `http://localhost:8000/index.php?controller=auth&action=googleCallback`)
   - Nếu app Google đang ở chế độ Testing, thêm email muốn đăng nhập vào danh sách Test users
   - Tuyệt đối không commit `google.local.php` lên GitHub
