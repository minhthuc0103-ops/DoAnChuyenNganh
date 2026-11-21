<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nhập mã OTP — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/auth-forgot-password.css">
<?php require 'frontend/views/Layouts/pwa_head.php'; ?>
</head>
<body>

<div class="corner-glow glow-tl"></div>
<div class="corner-glow glow-br"></div>

<div class="wrap">
  <div class="card">

    <div class="logo-row">
      <div class="logo-icon">🔑</div>
      <span class="logo-text">RoomManager</span>
    </div>

    <div class="card-title">Nhập mã OTP</div>
    <div class="card-sub">Mã 6 số đã được gửi tới email đăng ký (nếu tài khoản tồn tại). Mã chỉ có hiệu lực 1 phút. Kiểm tra cả mục Spam.</div>

    <?php if (!empty($error)): ?>
    <div class="alert-error"><span>⚠</span> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=auth&action=verifyOtp" autocomplete="off">
      <?= Csrf::field() ?>
      <div class="field">
        <label>Mã OTP <span style="color:#f75c5c">*</span></label>
        <div class="input-wrap">
          <input type="text" name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}"
                 placeholder="------" required autofocus style="letter-spacing:6px;font-size:20px"/>
          <span class="ic">🔢</span>
        </div>
      </div>
      <button type="submit" class="btn-submit">✅ Xác nhận</button>
    </form>

    <a href="index.php?controller=auth&action=forgotPassword" class="back-link">Gửi lại mã / đổi tên đăng nhập</a>
    <a href="index.php?controller=auth&action=login" class="back-link">← Quay lại đăng nhập</a>

  </div>
</div>

</body>
</html>
