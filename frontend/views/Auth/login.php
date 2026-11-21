<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/auth-login.css">
<?php require 'frontend/views/Layouts/pwa_head.php'; ?>
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
    <?php if (class_exists('GoogleAuth') && GoogleAuth::sanSang()): ?>
    <div class="or-sep"><span>hoặc</span></div>
    <a href="index.php?controller=auth&action=googleStart" class="btn-google">
      <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 7.1-10.1 7.1-17.5z"/><path fill="#FBBC05" d="M10.5 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.9-6.1A24 24 0 0 0 0 24c0 3.9.9 7.5 2.6 10.8l7.9-6.1z"/><path fill="#34A853" d="M24 48c6.3 0 11.6-2.1 15.5-5.7l-7.5-5.8c-2.1 1.4-4.8 2.3-8 2.3-6.3 0-11.6-4.1-13.5-9.8l-7.9 6.1C6.5 42.6 14.6 48 24 48z"/></svg>
      Đăng nhập bằng Google
    </a>
    <?php endif; ?>

    <div class="alt-link">Chưa có tài khoản? <a href="index.php?controller=auth&action=register">Đăng ký ngay</a></div>
  </div>
</div>
<script src="frontend/assets/js/auth-login.js"></script>
</body>
</html>