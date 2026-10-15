<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quên mật khẩu — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/auth-forgot-password.css">
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
    <div class="card-sub">Nhập tài khoản và xác minh bằng email hoặc số điện thoại đã đăng ký</div>

    <?php if (!empty($error)): ?>
    <div class="alert-error"><span>⚠</span> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="info-box">
      <p>💡 Nhập đúng <strong>tên đăng nhập</strong> và <strong>email</strong> hoặc <strong>số điện thoại</strong> đã đăng ký để xác minh danh tính.</p>
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

      <div class="divider">
        <div class="div-line"></div>
        <span class="div-txt">Xác minh bằng 1 trong 2</span>
        <div class="div-line"></div>
      </div>

      <div class="field">
        <label>Email đã đăng ký</label>
        <div class="input-wrap">
          <input type="email" name="email" placeholder="email@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
          <span class="ic">📧</span>
        </div>
      </div>

      <div class="field">
        <label>Hoặc số điện thoại</label>
        <div class="input-wrap">
          <input type="tel" name="sdt" placeholder="0xxx xxx xxx"
                 value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"/>
          <span class="ic">📱</span>
        </div>
      </div>

      <button type="submit" class="btn-submit">🔓 Xác minh & Đặt lại mật khẩu</button>

    </form>

    <a href="index.php?controller=auth&action=login" class="back-link">← Quay lại đăng nhập</a>

  </div>
</div>

</body>
</html>
