<?php
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

        $error   = '';
        $success = '';
        $step    = 1;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $sdt      = trim($_POST['sdt'] ?? '');

            if (!$username) {
                $error = 'Vui lòng nhập tên đăng nhập!';
            } elseif (!$email && !$sdt) {
                $error = 'Vui lòng nhập email hoặc số điện thoại để xác minh!';
            } else {
                $db   = Database::getInstance();
                $stmt = $db->prepare("SELECT * FROM account WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if (!$user) {
                    $error = 'Thông tin xác minh không chính xác!'; // Không tiết lộ tài khoản có tồn tại
                } else {
                    $emailMatch = !empty($email) && strtolower($email) === strtolower($user['email'] ?? '');
                    $sdtMatch   = !empty($sdt) && $sdt === ($user['sdt'] ?? '');

                    if (!$emailMatch && !$sdtMatch) {
                        $error = 'Thông tin xác minh không chính xác!';
                    } else {
                        $token = bin2hex(random_bytes(32));
                        $_SESSION['reset_token']   = $token;
                        $_SESSION['reset_user_id'] = $user['id'];
                        $_SESSION['reset_expires'] = time() + 600;

                        header('Location: index.php?controller=auth&action=resetPassword&token=' . $token);
                        exit;
                    }
                }
            }
        }

        require 'frontend/views/Auth/forgot_password.php';
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
