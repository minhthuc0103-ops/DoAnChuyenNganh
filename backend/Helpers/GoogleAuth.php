<?php
/**
 * Đăng nhập bằng Google (OAuth2 authorization code + PKCE). Không dùng thư viện ngoài.
 * Cấu hình: backend/config/google.local.php (không commit). Chưa cấu hình thì nút Google tự ẩn.
 */
class GoogleAuth
{
    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const HET_HAN   = 600; // giây: phiên đăng nhập Google tối đa 10 phút

    private static function cfg(): ?array
    {
        static $c = false;
        if ($c === false) {
            $f = __DIR__ . '/../config/google.local.php';
            $c = is_file($f) ? (include $f) : null;
            if (!is_array($c) || empty($c['client_id']) || empty($c['client_secret']) || empty($c['redirect_uri'])
                || str_starts_with((string)$c['client_id'], 'xxxxxxxx')) {
                $c = null;
            }
        }
        return $c;
    }

    public static function sanSang(): bool
    {
        return self::cfg() !== null;
    }

    private static function b64url(string $b): string
    {
        return rtrim(strtr(base64_encode($b), '+/', '-_'), '=');
    }

    /** Tạo state/nonce/PKCE trong session và trả về địa chỉ chuyển sang Google. */
    public static function urlBatDau(): ?string
    {
        $c = self::cfg();
        if (!$c) return null;
        $verifier = self::b64url(random_bytes(48));
        $_SESSION['google_oauth'] = [
            'state'    => bin2hex(random_bytes(24)),
            'nonce'    => bin2hex(random_bytes(16)),
            'verifier' => $verifier,
            'expires'  => time() + self::HET_HAN,
        ];
        return self::AUTH_URL . '?' . http_build_query([
            'client_id'             => $c['client_id'],
            'redirect_uri'          => $c['redirect_uri'],
            'response_type'         => 'code',
            'scope'                 => 'openid email profile',
            'state'                 => $_SESSION['google_oauth']['state'],
            'nonce'                 => $_SESSION['google_oauth']['nonce'],
            'code_challenge'        => self::b64url(hash('sha256', $verifier, true)),
            'code_challenge_method' => 'S256',
            'prompt'                => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Xử lý địa chỉ Google gọi về.
     * @return array{0:?array,1:?string} [thông tin người dùng {sub,email,name}, thông báo lỗi]
     */
    public static function xuLyPhanHoi(array $get): array
    {
        $c  = self::cfg();
        $ss = $_SESSION['google_oauth'] ?? null;
        unset($_SESSION['google_oauth']); // dùng một lần
        if (!$c || !$ss) {
            return [null, 'Phiên đăng nhập Google không hợp lệ. Vui lòng thử lại.'];
        }
        if (isset($get['error'])) {
            return [null, 'Bạn đã hủy đăng nhập bằng Google.'];
        }
        $state = (string)($get['state'] ?? '');
        $code  = (string)($get['code'] ?? '');
        if ($code === '' || !hash_equals($ss['state'], $state) || time() > $ss['expires']) {
            return [null, 'Phiên đăng nhập Google đã hết hạn hoặc không hợp lệ. Vui lòng thử lại.'];
        }

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => $c['client_id'],
                'client_secret' => $c['client_secret'],
                'redirect_uri'  => $c['redirect_uri'],
                'grant_type'    => 'authorization_code',
                'code_verifier' => $ss['verifier'],
            ]),
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $j = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($j) || empty($j['id_token'])) {
            error_log('GoogleAuth: đổi code lấy token thất bại: ' . (is_array($j) ? ($j['error'] ?? '?') : 'không kết nối được'));
            return [null, 'Không đăng nhập được bằng Google. Vui lòng thử lại sau.'];
        }

        // id_token nhận trực tiếp từ Google qua TLS nên không cần kiểm chữ ký; vẫn kiểm các trường bắt buộc.
        $parts = explode('.', $j['id_token']);
        $p = count($parts) === 3 ? json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true) : null;
        if (!is_array($p)
            || !in_array($p['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)
            || ($p['aud'] ?? '') !== $c['client_id']
            || (int)($p['exp'] ?? 0) < time()
            || !hash_equals($ss['nonce'], (string)($p['nonce'] ?? ''))
            || empty($p['sub'])) {
            return [null, 'Thông tin đăng nhập từ Google không hợp lệ.'];
        }
        if (empty($p['email']) || ($p['email_verified'] ?? false) !== true) {
            return [null, 'Email Google chưa được xác minh nên không thể đăng nhập.'];
        }
        return [[
            'sub'   => (string)$p['sub'],
            'email' => (string)$p['email'],
            'name'  => trim((string)($p['name'] ?? '')),
        ], null];
    }
}
