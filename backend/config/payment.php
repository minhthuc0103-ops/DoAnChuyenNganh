<?php
/**
 * Cấu hình thanh toán QR.
 * - VietQR: sinh QR ngân hàng động theo từng hóa đơn.
 * - MoMo / VNPay: dùng ảnh QR do chủ trọ hoặc merchant cung cấp.
 *
 * TÀI KHOẢN NHẬN TIỀN CỦA RIÊNG MÁY BẠN: đặt trong  backend/config/payment.local.php
 * (file đó nằm trong .gitignore, không lên GitHub, nên mỗi người trong nhóm dùng tài khoản của mình).
 * Mẫu: payment.local.example.php. Dòng nào không khai báo ở file local thì dùng giá trị mặc định bên dưới.
 *
 * Thay ảnh thật tại:
 *   public/uploads/payment/momo_qr.png
 *   public/uploads/payment/vnpay_qr.png
 */
// Cả hai file đều nằm trong .gitignore (chỉ ở máy này). File nào khai báo trước thì được dùng.
foreach (['payment.local.php', 'payment.local.example.php'] as $__f) {
    if (is_file(__DIR__ . '/' . $__f)) {
        require_once __DIR__ . '/' . $__f;
    }
}
unset($__f);

$__thanhToanMacDinh = [
    // Thông tin tài khoản nhận chuyển khoản ngân hàng (mặc định của dự án)
    'BANK_ID'         => '970416',             // mã BIN ngân hàng (ACB)
    'BANK_ACCOUNT'    => '26332437',           // số tài khoản
    'BANK_NAME'       => 'PHAN THANH TRONG',   // tên chủ tài khoản, viết hoa không dấu
    'BANK_DISPLAY'    => 'ACB',                // tên ngân hàng hiển thị
    'VIETQR_TEMPLATE' => 'compact2',
    // Ảnh QR riêng cho ví/cổng thanh toán. Thay bằng ảnh QR thật của bạn.
    'MOMO_QR_IMAGE'   => 'public/uploads/payment/momo_qr_rieng.png',   // QR MoMo dùng chung cho cả nhóm
    'VNPAY_QR_IMAGE'  => 'public/uploads/payment/vnpay_qr.png',
];
foreach ($__thanhToanMacDinh as $__k => $__v) {
    if (!defined($__k)) {
        define($__k, $__v);
    }
}
unset($__thanhToanMacDinh, $__k, $__v);
