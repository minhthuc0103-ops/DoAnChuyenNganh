# QuanLyPhongTro (RoomManager)

PHP MVC thuần, chạy trên XAMPP. Mở bằng `MoUngDung.bat` hoặc `http://localhost/QuanLyPhongTro/index.php`.

## Cấu trúc

```
index.php                  Điểm vào duy nhất (router) — giữ ở root để URL cũ không đổi
backend/                   ── PHẦN BACKEND (không truy cập trực tiếp qua URL)
  bootstrap.php            Autoload + hàm dùng chung e(), normalize_room_images()
  Controllers/             Xử lý request, phân quyền, gọi Model, chọn view
  Models/                  Truy vấn CSDL (PDO)
  Helpers/Csrf.php         Chống CSRF
  config/                  database.php, payment.php, ai.php
  database/
    ql_phong_tro.sql       Dump đầy đủ (import trước)
    migrations/            upgrade_*.sql — chạy sau khi import dump
frontend/                  ── PHẦN FRONTEND
  views/                   Template PHP (Layouts/header + footer dùng chung)
  assets/css/              CSS đã tách khỏi view
  assets/js/               JS đã tách khỏi view
public/uploads/            Ảnh người dùng upload (đường dẫn lưu trong DB, KHÔNG đổi)
```

## Lưu ý
- Sau khi import `ql_phong_tro.sql`, chạy thêm `backend/database/migrations/upgrade_roommate_requests.sql`
  (bảng `yeu_cau_nguoi_o_cung` không có trong dump chính).
- JS có chứa biến PHP (`<?= ... ?>`) vẫn để inline trong view, vì không thể tách thành file tĩnh.
