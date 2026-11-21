<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quên mật khẩu — RoomManager</title>
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

    <div class="card-title">Quên mật khẩu</div>
    <div class="card-sub">Nhập tên đăng nhập, mã OTP sẽ được gửi tới email đã đăng ký</div>

    <?php if (!empty($error)): ?>
    <div class="alert-error"><span>⚠</span> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="info-box">
      <p>💡 Mã OTP gồm 6 số, gửi tới <strong>email đã đăng ký</strong> của tài khoản và có hiệu lực 1 phút.</p>
    </div>

    <form method="POST" action="index.php?controller=auth&action=forgotPassword">
            <?= Csrf::field() ?>

      <div class="field">
        <label>Tên đăng nhập <span style="color:#f75c5c">*</span></label>
        <div class="input-wrap">
          <input type="text" name="username" placeholder="Nhập tên đăng nhập..." required
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"/>
          <span class="ic">👤</span>
        </div>
      </div>

      <button type="submit" class="btn-submit">📧 Gửi mã OTP</button>

    </form>

    <a href="index.php?controller=auth&action=login" class="back-link">← Quay lại đăng nhập</a>

  </div>
</div>

</body>
</html>
