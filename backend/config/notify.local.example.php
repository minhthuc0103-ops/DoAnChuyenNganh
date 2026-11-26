<?php
// Sao chép file này thành  notify.local.php  rồi điền giá trị thật. notify.local.php KHÔNG được commit lên Git.

// ── Gmail ──
define('MAIL_ENABLED',  true);
define('MAIL_USERNAME', 'ten-ban@gmail.com');
define('MAIL_PASSWORD', 'abcdefghijklmnop');       // mật khẩu ứng dụng 16 ký tự (Tài khoản Google → Bảo mật → Mật khẩu ứng dụng; cần bật xác minh 2 bước)
define('MAIL_FROM_NAME', 'Nhà trọ ABC');
define('NOTIFY_APP_URL', 'https://ten-mien.com/QuanLyPhongTro');

// ── Zalo ZNS (bỏ comment nếu đã có OA + mẫu tin được duyệt) ──
// define('ZALO_ZNS_ENABLED', true);
// define('ZALO_ZNS_ACCESS_TOKEN', '...');
// define('ZALO_ZNS_TEMPLATE_HOADON', '123456');
// define('ZALO_ZNS_PARAMS_HOADON', ['customer_name' => 'ten_khach', 'room' => 'phong', 'month' => 'ky', 'amount' => 'tong_tien', 'due_date' => 'han']);