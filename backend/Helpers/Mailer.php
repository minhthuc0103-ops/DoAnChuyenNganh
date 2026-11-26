<?php
require_once __DIR__ . '/../config/notify.php';

/**
 * Gửi email qua SMTP (Gmail) bằng PHPMailer. Không bao giờ ném lỗi ra ngoài:
 * mọi sự cố trả về [false, 'lý do'] để việc gửi mail không làm hỏng thao tác chính (tạo hóa đơn...).
 */
class Mailer
{
    private const LIB = __DIR__ . '/../Libs/PHPMailer/';

    /** Đã bật và đủ thông tin đăng nhập chưa. */
    public static function sanSang(): bool
    {
        return MAIL_ENABLED && MAIL_HOST !== ''
            && (!MAIL_AUTH || (MAIL_USERNAME !== '' && MAIL_PASSWORD !== ''));
    }

    /** @return array{0:bool,1:?string} [thành công, lý do lỗi] */
    public static function gui(string $den, string $tenNguoiNhan, string $tieuDe, string $html, string $text): array
    {
        if (!is_file(self::LIB . 'PHPMailer.php')) {
            return [false, 'Chưa cài thư viện PHPMailer (thư mục backend/Libs/PHPMailer).'];
        }
        if (!filter_var($den, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Địa chỉ email không hợp lệ.'];
        }
        require_once self::LIB . 'Exception.php';
        require_once self::LIB . 'PHPMailer.php';
        require_once self::LIB . 'SMTP.php';

        $m = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $m->isSMTP();
            $m->CharSet  = 'UTF-8';
            $m->Host     = MAIL_HOST;
            $m->Port     = (int)MAIL_PORT;
            $m->Timeout  = (int)MAIL_TIMEOUT;                          // chờ kết nối
            $m->getSMTPInstance()->Timelimit = (int)MAIL_TIMEOUT;      // chờ phản hồi mỗi lệnh (mặc định của thư viện là 300 giây!)
            $m->SMTPAuth = (bool)MAIL_AUTH;
            if (MAIL_AUTH) {
                $m->Username = MAIL_USERNAME;
                $m->Password = MAIL_PASSWORD;
            }
            if (MAIL_SECURE === 'ssl') {
                $m->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif (MAIL_SECURE === 'tls') {
                $m->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $m->SMTPSecure  = '';
                $m->SMTPAutoTLS = false;     // chỉ dùng khi thử trên máy
            }

            $tu = MAIL_FROM !== '' ? MAIL_FROM : (MAIL_USERNAME !== '' ? MAIL_USERNAME : 'no-reply@localhost');
            $m->setFrom($tu, MAIL_FROM_NAME);
            $m->addAddress($den, $tenNguoiNhan);
            $m->isHTML(true);
            $m->Subject = $tieuDe;
            $m->Body    = $html;
            $m->AltBody = $text;
            $m->send();
            return [true, null];
        } catch (\Throwable $e) {
            $loi = $m->ErrorInfo !== '' ? $m->ErrorInfo : $e->getMessage();
            return [false, mb_substr(trim(preg_replace('/\s+/', ' ', $loi)), 0, 300, 'UTF-8')];
        }
    }
}