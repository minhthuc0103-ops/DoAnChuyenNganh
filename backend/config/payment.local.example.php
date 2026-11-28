<?php
// TÀI KHOẢN NHẬN TIỀN CỦA MÁY NÀY — file đã nằm trong .gitignore, KHÔNG lên GitHub.
// Dòng nào bắt đầu bằng  define(  là đang được dùng; dòng nào có // ở đầu thì bị bỏ qua (dùng mặc định trong payment.php).

define('BANK_ID',      '970436');                // mã BIN ngân hàng, xem bảng bên dưới
define('BANK_ACCOUNT', '7941032534');          // số tài khoản, chỉ gồm chữ số
define('BANK_NAME',    'TO MINH THUC');     // viết HOA, KHÔNG dấu, đúng như trên thẻ/app ngân hàng
define('BANK_DISPLAY', 'Vietcombank');           // tên ngân hàng hiển thị trong email / trang hóa đơn

// Ảnh QR MoMo của bạn (file riêng, không lên Git). Muốn đổi ảnh thì ghi đè file này, giữ nguyên tên.
define('MOMO_QR_IMAGE', 'public/uploads/payment/momo_qr_rieng.png');

// Mã BIN một số ngân hàng (dùng cho BANK_ID):
//   Vietcombank 970436 · VietinBank 970415 · BIDV 970418 · Agribank 970405 · Techcombank 970407
//   MB Bank 970422 · ACB 970416 · VPBank 970432 · TPBank 970423 · Sacombank 970403
//   VIB 970441 · HDBank 970437 · MSB 970426 · SHB 970443
// Ngân hàng khác: tra "mã BIN <tên ngân hàng> VietQR".
