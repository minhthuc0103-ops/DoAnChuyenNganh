<?php
require_once __DIR__ . '/../Helpers/Mailer.php';
require_once __DIR__ . '/../Helpers/GoogleAuth.php';
class AuthController {

    public function login() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check(); // ═══ Lỗi #3: CSRF ═══

            $u = trim($_POST['username'] ?? '');
            $p = $_POST['password']     ?? '';

            // ═══ Lỗi #7: Brute-force protection ═══
            $key = '_login_attempts';
            $lockKey = '_login_locked_until';
            if (isset($_SESSION[$lockKey]) && time() < $_SESSION[$lockKey]) {
                $wait = $_SESSION[$lockKey] - time();
                $error = "Tài khoản tạm khóa. Vui lòng thử lại sau {$wait} giây.";
            } else {
                // Hết lock → reset
                if (isset($_SESSION[$lockKey]) && time() >= $_SESSION[$lockKey]) {
                    unset($_SESSION[$key], $_SESSION[$lockKey]);
                }

                $db   = Database::getInstance();
                $stmt = $db->prepare("SELECT * FROM account WHERE username=? LIMIT 1");
                $stmt->execute([$u]);
                $user = $stmt->fetch();

                if ($user && password_verify($p, $user['password'])) {
                    // ═══ Lỗi #4: Session Fixation Fix ═══
                    session_regenerate_id(true);

                    unset($_SESSION[$key], $_SESSION[$lockKey]); // reset attempts

                    $_SESSION['user']    = $user['username'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['ho_ten']  = $user['ho_ten'];
                    $_SESSION['vai_tro'] = $user['vai_tro'];
                    $dest = ($user['vai_tro'] === 'user')
                        ? 'index.php?controller=user&action=index'
                        : 'index.php?controller=dashboard&action=index';
                    header('Location: ' . $dest);
                    exit;
                }

                // Đếm lần sai
                $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
                if ($_SESSION[$key] >= 5) {
                    $_SESSION[$lockKey] = time() + 300; // lock 5 phút
                    $error = 'Đăng nhập sai quá 5 lần. Vui lòng thử lại sau 5 phút.';
                } else {
                    $left = 5 - $_SESSION[$key];
                    $error = "Sai tài khoản hoặc mật khẩu! (còn {$left} lần thử)";
                }
            }
        }
        require 'frontend/views/Auth/login.php';
    }

    /** Chuyển sang trang đăng nhập của Google. */
    public function googleStart() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        $url = GoogleAuth::urlBatDau();
        if (!$url) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        header('Location: ' . $url);
        exit;
    }

    /** Google gọi về sau khi người dùng đồng ý. */
    public function googleCallback() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        [$gu, $error] = GoogleAuth::xuLyPhanHoi($_GET);
        $error = $error ?? '';
        if ($gu) {
            try {
                $db = Database::getInstance();
                // Tự thêm cột google_id nếu chưa chạy migration.
                if (!$db->query("SHOW COLUMNS FROM account LIKE 'google_id'")->fetch()) {
                    $db->exec("ALTER TABLE account ADD COLUMN google_id VARCHAR(255) NULL DEFAULT NULL");
                    $db->exec("ALTER TABLE account ADD UNIQUE INDEX uq_account_google_id (google_id)");
                }
                $st = $db->prepare("SELECT * FROM account WHERE google_id = ? LIMIT 1");
                $st->execute([$gu['sub']]);
                $user = $st->fetch();

                if (!$user) {
                    [$user, $error] = $this->timHoacTaoTaiKhoan($db, $gu);
                    $error = $error ?? '';
                }
                if ($user && $error === '') {
                    $this->dangNhapThanhCong($user);
                }
            } catch (Throwable $e) {
                error_log('Google login: ' . $e->getMessage());
                $error = 'Không đăng nhập được bằng Google. Vui lòng thử lại sau.';
            }
        }
        require 'frontend/views/Auth/login.php';
    }

    /**
     * Chưa có tài khoản gắn với Google này: liên kết vào tài khoản QUẢN LÝ trùng email, hoặc tạo tài khoản người thuê mới.
     * Chỉ tự liên kết với quản lý/chủ trọ (do chủ app tạo, không ai tự đăng ký được). Tài khoản người thuê trùng email
     * thì không tự gộp, vì đăng ký thường không xác minh email nên có thể bị người khác dùng trước.
     * @return array{0:?array,1:?string} [tài khoản, lỗi]
     */
    private function timHoacTaoTaiKhoan(PDO $db, array $gu): array {
        $st = $db->prepare("SELECT * FROM account WHERE LOWER(email) = LOWER(?)");
        $st->execute([$gu['email']]);
        $trung = $st->fetchAll();
        if (count($trung) === 0) {
            return [$this->taoTaiKhoanGoogle($db, $gu), null];
        }
        if (count($trung) === 1 && in_array($trung[0]['vai_tro'], ['quan_ly', 'chu_tro'], true) && empty($trung[0]['google_id'])) {
            $db->prepare("UPDATE account SET google_id = ? WHERE id = ? AND google_id IS NULL")
               ->execute([$gu['sub'], $trung[0]['id']]);
            return [$trung[0], null];
        }
        return [null, 'Email này đã đăng ký tài khoản bằng mật khẩu. Vui lòng đăng nhập bằng tên đăng nhập và mật khẩu.'];
    }
    private function dangNhapThanhCong(array $user): void {
        session_regenerate_id(true);
        unset($_SESSION['_login_attempts'], $_SESSION['_login_locked_until']);
        $_SESSION['user']    = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ho_ten']  = $user['ho_ten'];
        $_SESSION['vai_tro'] = $user['vai_tro'];
        header('Location: ' . ($user['vai_tro'] === 'user'
            ? 'index.php?controller=user&action=index'
            : 'index.php?controller=dashboard&action=index'));
        exit;
    }

    /** Tạo tài khoản người thuê mới từ Google; thông tin còn thiếu (sđt, CCCD...) người dùng điền sau trong hồ sơ. */
    private function taoTaiKhoanGoogle(PDO $db, array $gu): array {
        $local = explode('@', $gu['email'])[0];
        $base  = substr(preg_replace('/[^a-zA-Z0-9_]/', '', $local), 0, 30);
        if (strlen($base) < 4) $base .= 'user';
        $hoTen = $gu['name'] !== '' ? mb_substr($gu['name'], 0, 100) : $local;
        $mk    = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT); // mật khẩu ngẫu nhiên, không ai biết

        for ($i = 0; $i < 5; $i++) {
            $username = $base . '_' . bin2hex(random_bytes(2));
            $chk = $db->prepare("SELECT 1 FROM account WHERE username = ?");
            $chk->execute([$username]);
            if ($chk->fetch()) continue;

            $db->beginTransaction();
            try {
                $db->prepare("INSERT INTO account (ho_ten, username, password, email, sdt, vai_tro, chat_joined_at, google_id)
                              VALUES (?,?,?,?,NULL,'user',NOW(),?)")
                   ->execute([$hoTen, $username, $mk, $gu['email'], $gu['sub']]);
                $id = (int)$db->lastInsertId();
                $db->prepare("INSERT INTO nguoi_thue (account_id, ho_ten, sdt, cccd, dia_chi) VALUES (?, ?, '', '', '')")
                   ->execute([$id, $hoTen]);
                $db->commit();
                return ['id' => $id, 'username' => $username, 'ho_ten' => $hoTen, 'vai_tro' => 'user'];
            } catch (Throwable $e) {
                $db->rollBack();
                throw $e;
            }
        }
        throw new RuntimeException('Không tạo được tên đăng nhập.');
    }

    public function register() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $ho_ten   = trim($_POST['ho_ten']          ?? '');
            $username = trim($_POST['username']         ?? '');
            $password = $_POST['password']              ?? '';
            $confirm  = $_POST['confirm_password']      ?? '';
            $email    = trim($_POST['email']            ?? '');
            $sdt      = trim($_POST['sdt']              ?? '');
            $vai_tro  = 'user';
            $terms    = isset($_POST['terms']);

            if (!$ho_ten || !$username || !$password || !$email || !$sdt) {
                $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không hợp lệ!';
            } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $sdt)) {
                $error = 'Số điện thoại không hợp lệ!';
            } elseif (strlen($username) < 4) {
                $error = 'Tên đăng nhập tối thiểu 4 ký tự!';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $error = 'Tên đăng nhập chỉ dùng chữ, số và dấu _';
            } elseif (strlen($password) < 8) {
                $error = 'Mật khẩu tối thiểu 8 ký tự!';
            } elseif ($password !== $confirm) {
                $error = 'Mật khẩu xác nhận không khớp!';
            } elseif (!$terms) {
                $error = 'Vui lòng đồng ý với điều khoản!';
            } else {
                $db = Database::getInstance();

                $check = $db->prepare("SELECT id FROM account WHERE username=?");
                $check->execute([$username]);
                if ($check->fetch()) {
                    $error = 'Tên đăng nhập đã tồn tại!';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins  = $db->prepare(
                        "INSERT INTO account (ho_ten, username, password, email, sdt, vai_tro, chat_joined_at)
                         VALUES (?,?,?,?,?,?,NOW())"
                    );
                    $ins->execute([$ho_ten, $username, $hash, $email, $sdt, $vai_tro]);
                    $newId = $db->lastInsertId();

                    $insNT = $db->prepare(
                        "INSERT INTO nguoi_thue (account_id, ho_ten, sdt, cccd, dia_chi)
                         VALUES (?, ?, ?, '', '')"
                    );
                    $insNT->execute([$newId, $ho_ten, $sdt]);

                    session_regenerate_id(true);

                    $_SESSION['user']    = $username;
                    $_SESSION['user_id'] = $newId;
                    $_SESSION['ho_ten']  = $ho_ten;
                    $_SESSION['vai_tro'] = $vai_tro;
                    header('Location: index.php?controller=user&action=index');
                    exit;
                }
            }
        }
        require 'frontend/views/Auth/register.php';
    }

    public function terms() {
        require 'frontend/views/Auth/terms.php';
    }

    public function privacy() {
        require 'frontend/views/Auth/privacy.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    public function forgotPassword() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $username = trim($_POST['username'] ?? '');
            if (!$username) {
                $error = 'Vui lòng nhập tên đăng nhập!';
            } elseif (!Mailer::sanSang()) {
                $error = 'Hệ thống chưa cấu hình gửi email nên chưa thể gửi mã OTP. Vui lòng liên hệ chủ trọ.';
            } else {
                $db   = Database::getInstance();
                $stmt = $db->prepare("SELECT id, ho_ten, email FROM account WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                $cho = $_SESSION['otp_resend_at'] ?? 0;
                if (time() < $cho) {
                    $error = 'Vui lòng đợi ' . ($cho - time()) . ' giây rồi yêu cầu mã mới.';
                } else {
                    $_SESSION['otp_resend_at'] = time() + 30;
                    $ma = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $coDich = $user && filter_var($user['email'] ?? '', FILTER_VALIDATE_EMAIL);
                    // Luôn lưu phiên OTP (kể cả tài khoản không tồn tại) để không lộ tên đăng nhập nào có thật.
                    $_SESSION['otp'] = [
                        'user_id' => $coDich ? (int)$user['id'] : 0,
                        'hash'    => password_hash($ma, PASSWORD_DEFAULT),
                        'expires' => time() + 60,
                        'tries'   => 0,
                    ];
                    if ($coDich) {
                        $ten  = htmlspecialchars($user['ho_ten'], ENT_QUOTES, 'UTF-8');
                        $html = "<p>Xin chào <b>{$ten}</b>,</p><p>Mã OTP đặt lại mật khẩu của bạn là:</p>"
                              . "<p style=\"font-size:28px;letter-spacing:6px;font-weight:700\">{$ma}</p>"
                              . "<p>Mã có hiệu lực trong 1 phút. Nếu không phải bạn yêu cầu, hãy bỏ qua email này.</p>";
                        $text = "Ma OTP dat lai mat khau: {$ma} (hieu luc 1 phut).";
                        Mailer::gui($user['email'], $user['ho_ten'], 'Mã OTP đặt lại mật khẩu', $html, $text);
                    }
                    header('Location: index.php?controller=auth&action=verifyOtp');
                    exit;
                }
            }
        }

        require 'frontend/views/Auth/forgot_password.php';
    }

    /** Nhập mã OTP đã gửi qua email; đúng thì cho đặt mật khẩu mới. */
    public function verifyOtp() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        $otp = $_SESSION['otp'] ?? null;
        if (!$otp) {
            header('Location: index.php?controller=auth&action=forgotPassword');
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $ma = preg_replace('/\D/', '', $_POST['otp'] ?? '');
            if (time() > $otp['expires']) {
                unset($_SESSION['otp']);
                $error = 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.';
            } elseif ($otp['tries'] >= 5) {
                unset($_SESSION['otp']);
                $error = 'Nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.';
            } elseif ($otp['user_id'] > 0 && strlen($ma) === 6 && password_verify($ma, $otp['hash'])) {
                session_regenerate_id(true);
                $token = bin2hex(random_bytes(32));
                $_SESSION['reset_token']   = $token;
                $_SESSION['reset_user_id'] = $otp['user_id'];
                $_SESSION['reset_expires'] = time() + 600;
                unset($_SESSION['otp']);
                header('Location: index.php?controller=auth&action=resetPassword&token=' . $token);
                exit;
            } else {
                $_SESSION['otp']['tries']++;
                $error = 'Mã OTP không đúng! (còn ' . max(0, 5 - $_SESSION['otp']['tries']) . ' lần thử)';
            }
        }
        require 'frontend/views/Auth/verify_otp.php';
    }


    public function resetPassword() {
        if (!empty($_SESSION['user'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }

        $token = $_GET['token'] ?? '';
        $error   = '';
        $success = '';

        if (empty($token) || $token !== ($_SESSION['reset_token'] ?? '')) {
            $error = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!';
            require 'frontend/views/Auth/reset_password.php';
            return;
        }

        if (time() > ($_SESSION['reset_expires'] ?? 0)) {
            unset($_SESSION['reset_token'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);
            $error = 'Link đặt lại mật khẩu đã hết hạn (10 phút). Vui lòng thử lại!';
            require 'frontend/views/Auth/reset_password.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 8) {
                $error = 'Mật khẩu mới tối thiểu 8 ký tự!';
            } elseif ($password !== $confirm) {
                $error = 'Mật khẩu xác nhận không khớp!';
            } else {
                $userId = $_SESSION['reset_user_id'] ?? 0;
                $hash   = password_hash($password, PASSWORD_DEFAULT);

                $db = Database::getInstance();
                $stmt = $db->prepare("UPDATE account SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $userId]);

                unset($_SESSION['reset_token'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);

                $success = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.';
            }
        }

        require 'frontend/views/Auth/reset_password.php';
    }
}
