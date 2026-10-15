<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đặt lại mật khẩu — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/auth-reset-password.css">
</head>
<body>

<div class="corner-glow glow-tl"></div>
<div class="corner-glow glow-br"></div>

<div class="wrap">
  <div class="card">

    <div class="logo-row">
      <div class="logo-icon">🔐</div>
      <span class="logo-text">RoomManager</span>
    </div>

    <?php if (!empty($success)): ?>
      <div class="card-title">✅ Thành công!</div>
      <div class="card-sub">&nbsp;</div>
      <div class="alert-success">
        <span>✓</span>
        <div><?= htmlspecialchars($success) ?></div>
      </div>
      <a href="index.php?controller=auth&action=login" class="btn-submit" style="text-decoration:none;margin-top:10px;">
        🔑 Đăng nhập ngay
      </a>

    <?php elseif (!empty($error) && empty($token)): ?>
      <div class="card-title">⚠ Lỗi</div>
      <div class="card-sub">&nbsp;</div>
      <div class="alert-error"><span>⚠</span> <?= htmlspecialchars($error) ?></div>
      <a href="index.php?controller=auth&action=forgotPassword" class="btn-submit" style="text-decoration:none;background:linear-gradient(135deg,#4f8ef7,#7c5cfc);">
        ← Thử lại
      </a>

    <?php else: ?>
      <div class="card-title">Đặt lại mật khẩu</div>
      <div class="card-sub">Nhập mật khẩu mới cho tài khoản của bạn</div>

      <?php if (!empty($error)): ?>
      <div class="alert-error"><span>⚠</span> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?controller=auth&action=resetPassword&token=<?= htmlspecialchars($token) ?>
            <?= Csrf::field() ?>">

        <div class="field">
          <label>Mật khẩu mới <span style="color:#f75c5c">*</span></label>
          <div class="input-wrap">
            <input type="password" name="password" id="pw1" placeholder="Tối thiểu 8 ký tự..." required minlength="8" oninput="checkStrength(this.value)"/>
            <span class="ic">🔒</span>
            <button type="button" class="toggle-pw" onclick="togglePw('pw1',this)">👁</button>
          </div>
          <div class="pw-strength"><div class="pw-bar" id="pwBar"></div></div>
          <div style="font-size:11px;color:#555b6e;margin-top:4px;" id="pwHint">Tối thiểu 8 ký tự</div>
        </div>

        <div class="field">
          <label>Xác nhận mật khẩu <span style="color:#f75c5c">*</span></label>
          <div class="input-wrap">
            <input type="password" name="confirm_password" id="pw2" placeholder="Nhập lại mật khẩu..." required minlength="8" oninput="checkMatch()"/>
            <span class="ic">🔒</span>
            <button type="button" class="toggle-pw" onclick="togglePw('pw2',this)">👁</button>
          </div>
          <div style="font-size:11px;margin-top:4px;display:none;" id="matchHint"></div>
        </div>

        <button type="submit" class="btn-submit">✓ Đặt lại mật khẩu</button>

      </form>
    <?php endif; ?>

    <a href="index.php?controller=auth&action=login" class="back-link">← Quay lại đăng nhập</a>

  </div>
</div>

<script src="frontend/assets/js/auth-reset-password.js"></script>

</body>
</html>
