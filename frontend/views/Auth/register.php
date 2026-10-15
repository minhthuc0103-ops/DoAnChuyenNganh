<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng ký — RoomManager</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="frontend/assets/css/auth-register.css">
</head>
<body>
<div class="page-wrap">
  <div class="logo-row">
    <div class="logo-icon">🏠</div>
    <span class="logo-text">RoomManager</span>
  </div>
  <div class="steps-bar">
    <div class="step-item"><div class="step-circle done" id="sc1">✓</div><div class="step-lbl">Cơ bản</div></div>
    <div class="step-line done" id="sl1"></div>
    <div class="step-item"><div class="step-circle active" id="sc2">2</div><div class="step-lbl">Bảo mật</div></div>
    <div class="step-line" id="sl2"></div>
    <div class="step-item"><div class="step-circle" id="sc3">3</div><div class="step-lbl">Hoàn tất</div></div>
  </div>
  <div class="form-heading">Tạo tài khoản mới</div>
  <div class="form-sub">Điền thông tin để bắt đầu sử dụng</div>
  <?php if (!empty($error)): ?>
  <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
  <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <form method="POST" action="index.php?controller=auth&action=register" id="regForm">
            <?= Csrf::field() ?>
    <div class="field-row">
      <div class="field">
        <label>Họ và tên</label>
        <div class="input-wrap"><input type="text" name="ho_ten" placeholder="Nguyễn Văn A" required value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>"/></div>
      </div>
      <div class="field">
        <label>Số điện thoại</label>
        <div class="input-wrap"><input type="tel" name="sdt" placeholder="0901234567" required value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"/></div>
      </div>
    </div>
    <div class="field">
      <label>Email</label>
      <div class="input-wrap"><input type="email" name="email" placeholder="example@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/></div>
    </div>
    <div class="field">
      <label>Tên đăng nhập</label>
      <div class="input-wrap"><input type="text" name="username" placeholder="Tối thiểu 4 ký tự" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" oninput="checkUsername(this.value)"/></div>
      <div class="fb" id="userFb"></div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Mật khẩu</label>
        <div class="input-wrap">
          <input type="password" name="password" id="pw1" placeholder="Tối thiểu 8 ký tự" required oninput="checkStrength(this.value)"/>
          <button type="button" class="toggle-pw" onclick="togglePw('pw1',this)">👁</button>
        </div>
        <div class="strength-wrap">
          <div class="strength-bar"><div class="strength-fill" id="strFill"></div></div>
          <div class="strength-lbl" id="strLbl">Nhập mật khẩu</div>
        </div>
      </div>
      <div class="field">
        <label>Xác nhận</label>
        <div class="input-wrap">
          <input type="password" name="confirm_password" id="pw2" placeholder="Nhập lại..." required oninput="checkMatch()"/>
          <button type="button" class="toggle-pw" onclick="togglePw('pw2',this)">👁</button>
        </div>
        <div class="fb" id="matchFb"></div>
      </div>
    </div>
    <div class="terms-row">
      <input type="checkbox" id="terms" name="terms" required/>
      <div class="terms-txt">Tôi đồng ý với <a href="index.php?controller=auth&action=terms" target="_blank" rel="noopener">Điều khoản dịch vụ</a> và <a href="index.php?controller=auth&action=privacy" target="_blank" rel="noopener">Chính sách bảo mật</a></div>
    </div>
    <button type="submit" class="btn-primary" id="regBtn">Tạo tài khoản →</button>
  </form>
  <div class="alt-link">Đã có tài khoản? <a href="index.php?controller=auth&action=login">Đăng nhập ngay</a></div>
</div>
<script src="frontend/assets/js/auth-register.js"></script>
</body>
</html>
