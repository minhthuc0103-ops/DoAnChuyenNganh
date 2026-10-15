
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
  else { inp.type = 'password'; btn.textContent = '👁'; }
}

function checkStrength(pw) {
  const bar  = document.getElementById('pwBar');
  const hint = document.getElementById('pwHint');
  let score = 0;
  if (pw.length >= 8) score++;
  if (pw.length >= 12) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;

  const pct    = Math.min(score / 5 * 100, 100);
  const colors = ['#f75c5c','#f7a94f','#f7a94f','#22c993','#22c993'];
  const labels = ['Yếu','Trung bình','Trung bình','Mạnh','Rất mạnh'];

  bar.style.width = pct + '%';
  bar.style.background = colors[Math.min(score-1, 4)] || '#f75c5c';
  hint.textContent = pw.length > 0 ? ('Độ mạnh: ' + (labels[Math.min(score-1, 4)] || 'Yếu')) : 'Tối thiểu 8 ký tự';
  hint.style.color = colors[Math.min(score-1, 4)] || '#555b6e';
}

function checkMatch() {
  const pw1 = document.getElementById('pw1').value;
  const pw2 = document.getElementById('pw2').value;
  const hint = document.getElementById('matchHint');
  if (pw2.length === 0) { hint.style.display = 'none'; return; }
  hint.style.display = 'block';
  if (pw1 === pw2) {
    hint.textContent = '✓ Mật khẩu khớp';
    hint.style.color = '#22c993';
  } else {
    hint.textContent = '✗ Mật khẩu không khớp';
    hint.style.color = '#f75c5c';
  }
}
