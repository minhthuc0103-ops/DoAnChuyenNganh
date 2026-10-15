<?php
/**
 * CSRF Protection Helper
 * Include qua autoload hoặc require trực tiếp.
 */
class Csrf
{
    /** Tạo token mới nếu chưa có, trả về token hiện tại */
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /** Trả về thẻ <input hidden> chứa token — dùng trong form */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /** Kiểm tra token từ POST có khớp session không */
    public static function verify(): bool
    {
        $posted = $_POST['_csrf_token'] ?? '';
        $stored = $_SESSION['_csrf_token'] ?? '';
        return $posted !== '' && $stored !== '' && hash_equals($stored, $posted);
    }

    /** Kiểm tra + die nếu sai (dùng nhanh đầu controller action) */
    public static function check(): void
    {
        if (!self::verify()) {
            http_response_code(403);
            die('Yêu cầu không hợp lệ (CSRF token sai hoặc hết hạn). Vui lòng tải lại trang.');
        }
    }
}
