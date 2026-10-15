
function toggleAI() {
  const box = document.getElementById('aiBox');
  const fab = document.getElementById('aiFab');
  box.classList.toggle('open');
  fab.classList.toggle('open');
  if (box.classList.contains('open')) {
    document.getElementById('aiInput').focus();
  }
}

function appendMsg(role, html) {
  const box = document.getElementById('aiMessages');
  const div = document.createElement('div');
  div.className = 'ai-msg ' + role;
  div.innerHTML = '<div class="ai-bubble">' + html + '</div>';
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
  return div;
}

function showTyping() {
  return appendMsg('bot', '<div class="typing"><span></span><span></span><span></span></div>');
}

async function sendAI() {
  const input = document.getElementById('aiInput');
  const btn   = document.getElementById('aiSendBtn');
  const text  = input.value.trim();
  if (!text) return;

  appendMsg('user', escHtml(text));
  input.value = '';
  input.style.height = '42px';
  btn.disabled = true;

  const typing = showTyping();

  try {
    const res = await fetch('index.php?controller=ai&action=chat', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ message: text })
    });

    const data = await res.json();
    typing.remove();

    if (data.ok) {
      appendMsg('bot', formatAI(data.reply));
    } else {
      appendMsg('bot', '<span style="color:#f75c5c;">⚠ ' + escHtml(data.message || 'Lỗi không xác định') + '</span>');
    }
  } catch (e) {
    typing.remove();
    appendMsg('bot', '<span style="color:#f75c5c;">⚠ Không kết nối được tới AI</span>');
  }

  btn.disabled = false;
  input.focus();
}

async function clearAI() {
  if (!confirm('Xóa lịch sử chat?')) return;
  try { await fetch('index.php?controller=ai&action=clear', {method:'POST'}); } catch(e) {}
  const box = document.getElementById('aiMessages');
  box.innerHTML = '';
  appendMsg('bot', 'Đã xóa lịch sử. Bạn cần hỗ trợ gì? 🏠');
}

function escHtml(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function askSuggestion(text) {
  document.getElementById('aiInput').value = text;
  // Ẩn gợi ý sau khi click
  const sug = document.getElementById('aiSuggestions');
  if (sug) sug.style.display = 'none';
  sendAI();
}

function formatAI(text) {
  // Bold: **text**
  let html = escHtml(text);
  html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  // Bullet points
  html = html.replace(/^[•\-]\s/gm, '• ');
  // Line breaks
  html = html.replace(/\n/g, '<br>');
  return html;
}

// Enter to send, Shift+Enter for newline
document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('aiInput');
  if (input) {
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendAI();
      }
    });
    // Auto-resize
    input.addEventListener('input', function() {
      this.style.height = '42px';
      this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });
  }
});
function openAppConfirm(options) {
  return new Promise(resolve => {
    const modal = document.getElementById('appConfirmModal');
    const title = document.getElementById('appConfirmTitle');
    const message = document.getElementById('appConfirmMessage');
    const ok = document.getElementById('appConfirmOk');
    const cancel = document.getElementById('appConfirmCancel');
    if (!modal || !title || !message || !ok || !cancel) {
      resolve(window.confirm(options.message || 'Xác nhận thao tác?'));
      return;
    }

    title.textContent = options.title || 'Xác nhận thao tác';
    message.textContent = options.message || 'Bạn có chắc muốn thực hiện thao tác này?';
    ok.textContent = options.okText || 'Đồng ý';
    ok.classList.toggle('app-confirm-danger', options.danger !== false);
    modal.classList.add('show');
    cancel.focus();

    const cleanup = result => {
      modal.classList.remove('show');
      ok.onclick = null;
      cancel.onclick = null;
      modal.onclick = null;
      document.removeEventListener('keydown', onKey);
      resolve(result);
    };
    const onKey = e => {
      if (e.key === 'Escape') cleanup(false);
      if (e.key === 'Enter') cleanup(true);
    };
    ok.onclick = () => cleanup(true);
    cancel.onclick = () => cleanup(false);
    modal.onclick = e => {
      if (e.target === modal) cleanup(false);
    };
    document.addEventListener('keydown', onKey);
  });
}

document.addEventListener('click', async function(e) {
  const link = e.target.closest('.js-confirm-link');
  if (!link) return;
  e.preventDefault();
  const ok = await openAppConfirm({
    title: link.dataset.confirmTitle,
    message: link.dataset.confirmMessage,
    okText: link.dataset.confirmOk,
    danger: link.dataset.confirmDanger !== '0',
  });
  if (ok) window.location.href = link.href;
});
