
function togglePw() {
  const inp = document.getElementById('pwInput');
  const btn = document.querySelector('.toggle-pw');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.textContent = 'Đang xử lý...'; btn.style.opacity = '0.7'; btn.style.pointerEvents = 'none';
});
