<?php
/**
 * Cấu hình gửi hóa đơn tự động (Gmail + Zalo).
 *
 * GIÁ TRỊ THẬT (mật khẩu ứng dụng, token) đặt trong  backend/config/notify.local.php
 * — file đó nằm trong .gitignore, KHÔNG commit lên Git. Mẫu: notify.local.example.php
 */
if (is_file(__DIR__ . '/notify.local.php')) {
    require __DIR__ . '/notify.local.php';
}

$__notifyMacDinh = [
    // Địa chỉ web của app, dùng để chèn link "Xem hóa đơn" trong email. Để trống = không chèn link.
    'NOTIFY_APP_URL'  => '',          // vd: 'https://ten-mien.com/QuanLyPhongTro'

    // ── Gmail (SMTP) ──
    'MAIL_ENABLED'    => false,       // true = bật gửi email
    'MAIL_HOST'       => 'smtp.gmail.com',
    'MAIL_PORT'       => 587,
    'MAIL_SECURE'     => 'tls',       // 'tls' (cổng 587) | 'ssl' (cổng 465) | '' (không mã hóa, chỉ để thử trên máy)
    'MAIL_AUTH'       => true,
    'MAIL_USERNAME'   => '',          // địa chỉ Gmail dùng để gửi
    'MAIL_PASSWORD'   => '',          // MẬT KHẨU ỨNG DỤNG 16 ký tự (không phải mật khẩu đăng nhập Gmail)
    'MAIL_FROM'       => '',          // để trống = dùng MAIL_USERNAME
    'MAIL_FROM_NAME'  => 'RoomManager',
    'MAIL_TIMEOUT'    => 10,          // giây; quá hạn thì bỏ qua, không treo trang

    // ── Khi nào tự gửi ──
    'HOADON_TU_DONG_GUI' => true,     // tạo hóa đơn xong tự gửi cho người thuê
    'HOADON_GUI_BIEN_NHAN' => true,   // chủ trọ xác nhận đã thu tiền thì tự gửi biên nhận

    // ── Zalo ZNS (tùy chọn): cần Zalo OA đã xác minh + mẫu tin ZNS được Zalo duyệt ──
    'ZALO_ZNS_ENABLED'            => false,
    'ZALO_ZNS_URL'                => 'https://business.openapi.zalo.me/message/template',
    'ZALO_ZNS_ACCESS_TOKEN'       => '',      // access token của OA (hết hạn định kỳ, phải làm mới)
    'ZALO_ZNS_MODE'               => 'production',   // 'development' để thử (chỉ gửi tới số quản trị OA)
    'ZALO_ZNS_TIMEOUT'            => 10,
    'ZALO_ZNS_TEMPLATE_HOADON'    => '',      // ID mẫu tin "Hóa đơn"
    'ZALO_ZNS_TEMPLATE_BIENNHAN'  => '',      // ID mẫu tin "Biên nhận" (để trống = không gửi biên nhận qua Zalo)
    // Tên tham số trong mẫu ZNS đã duyệt  =>  dữ liệu của app. Sửa vế TRÁI cho khớp mẫu của bạn.
    // Dữ liệu có sẵn: ten_khach, phong, ky, tong_tien, han, tien_phong, tien_dien, tien_nuoc, so_tien_thu
    'ZALO_ZNS_PARAMS_HOADON'      => ['customer_name' => 'ten_khach', 'room' => 'phong', 'month' => 'ky', 'amount' => 'tong_tien', 'due_date' => 'han'],
    'ZALO_ZNS_PARAMS_BIENNHAN'    => ['customer_name' => 'ten_khach', 'room' => 'phong', 'month' => 'ky', 'amount' => 'so_tien_thu'],
];
foreach ($__notifyMacDinh as $__k => $__v) {
    if (!defined($__k)) {
        define($__k, $__v);
    }
}
unset($__notifyMacDinh, $__k, $__v);