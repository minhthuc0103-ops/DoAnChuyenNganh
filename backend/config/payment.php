<?php
/**
 * Cấu hình thanh toán QR.
 * - VietQR: sinh QR ngân hàng động theo từng hóa đơn.
 * - MoMo / VNPay: dùng ảnh QR do chủ trọ hoặc merchant cung cấp.
 *
 * Thay ảnh thật tại:
 *   public/uploads/payment/momo_qr.png
 *   public/uploads/payment/vnpay_qr.png
 */

// Thông tin tài khoản nhận chuyển khoản ngân hàng
define('BANK_ID', '970416');              // ACB
define('BANK_ACCOUNT', '26332437');        // Số tài khoản của bạn
define('BANK_NAME', 'PHAN THANH TRONG');   // Tên chủ tài khoản, nên viết hoa không dấu
define('BANK_DISPLAY', 'ACB');             // Tên ngân hàng hiển thị

define('VIETQR_TEMPLATE', 'compact2');

// Ảnh QR riêng cho ví/cổng thanh toán. Thay bằng ảnh QR thật của bạn.
define('MOMO_QR_IMAGE', 'public/uploads/payment/momo_qr.png');
define('VNPAY_QR_IMAGE', 'public/uploads/payment/vnpay_qr.png');
