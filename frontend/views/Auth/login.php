<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/auth-login.css">
</head>
<body>
<div class="page-wrap">
  <div class="illus-side">
    <div class="illus-icon">🏠</div>
    <div class="illus-title">Quản lý phòng trọ thông minh</div>
    <div class="illus-sub">Theo dõi hợp đồng, hóa đơn và người thuê trong một nơi</div>
    <div class="dots"><div class="dot active"></div><div class="dot"></div><div class="dot"></div></div>
  </div>
  <div class="form-side">
    <div class="form-logo">
      <div class="form-logo-icon">🏠</div>
      <span class="form-logo-text">RoomManager</span>
    </div>
    <div class="form-heading">Chào mừng trở lại!</div>
    <div class="form-sub">Vui lòng đăng nhập để tiếp tục</div>
    <?php if (!empty($error)): ?>
    <div class="alert-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="index.php?controller=auth&action=login" id="loginForm">
            <?= Csrf::field() ?>
      <div class="field">
        <label>Tài khoản</label>
        <div class="input-wrap">
          <input type="text" name="username" placeholder="Nhập tên đăng nhập..." autocomplete="username" required/>
          <span class="input-icon">👤</span>
        </div>
      </div>
      <div class="field">
        <label>Mật khẩu</label>
        <div class="input-wrap">
          <input type="password" name="password" id="pwInput" placeholder="Nhập mật khẩu..." autocomplete="current-password" required/>
          <button type="button" class="toggle-pw" onclick="togglePw()">👁</button>
        </div>
      </div>
      <div class="row-opts">
        <label class="check-label">
          <input type="checkbox" name="remember" checked/>
          Ghi nhớ đăng nhập
        </label>
        <a href="index.php?controller=auth&action=forgotPassword" class="forgot">Quên mật khẩu?</a>
      </div>
      <button type="submit" class="btn-primary" id="loginBtn">Đăng nhập →</button>
    </form>
    <div class="alt-link">Chưa có tài khoản? <a href="index.php?controller=auth&action=register">Đăng ký ngay</a></div>
  </div>
</div>
<script src="frontend/assets/js/auth-login.js"></script>
</body>
</html>