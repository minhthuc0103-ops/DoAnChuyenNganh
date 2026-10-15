<?php
$title = 'Chat nhóm';
require_once 'frontend/views/Layouts/header.php';
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']);

// Đếm thành viên online (có account)
$db = Database::getInstance();
$totalMembers = (int)$db->query("SELECT COUNT(*) FROM account")->fetchColumn();
?>

<link rel="stylesheet" href="frontend/assets/css/chat-index.css">

<div class="chat-wrap">
  <div class="chat-container" style="position:relative;">
    <!-- Header -->
    <div class="chat-hd">
      <div class="chat-hd-icon">💬</div>
      <div class="chat-hd-info">
        <div class="chat-hd-name">Nhóm Khu Trọ</div>
        <div class="chat-hd-sub">
          <span class="online-dot"></span>
          <?= $totalMembers ?> thành viên · Admin & Người thuê
        </div>
      </div>
      <?php if($isAdmin): ?>
      <button onclick="sendSystemMsg()" class="btn btn-outline btn-sm" title="Gửi thông báo hệ thống">📢</button>
      <?php endif; ?>
    </div>

    <?php $pinnedCount = count($pinnedMessages ?? []); ?>
    <div class="pin-board <?= $pinnedCount > 0 ? 'show' : '' ?>" id="pinBoard">
      <div class="pin-head">
        <span id="pinTitle">Danh sách ghim (<?= $pinnedCount ?>)</span>
        <button type="button" class="pin-toggle" onclick="togglePinBoard()">
          <span id="pinToggleText">Thu gọn</span>
          <span id="pinToggleIcon">⌃</span>
        </button>
      </div>
      <div class="pin-list" id="pinList">
        <?php foreach (($pinnedMessages ?? []) as $pin): ?>
          <?php
            $pinText = trim($pin['noi_dung'] ?? '');
            $pinPreview = mb_strlen($pinText, 'UTF-8') > 120 ? mb_substr($pinText, 0, 120, 'UTF-8') . '...' : $pinText;
          ?>
          <div class="pin-item" data-id="<?= (int)$pin['id'] ?>" onclick="jumpToPinned(<?= (int)$pin['id'] ?>)">
            <div class="pin-icon">≡</div>
            <div class="pin-info">
              <div class="pin-title">Tin nhắn</div>
              <div class="pin-text"><?= htmlspecialchars($pin['ho_ten'] ?? '') ?>: <?= htmlspecialchars($pinPreview) ?></div>
            </div>
            <button type="button" class="pin-more" onclick="event.stopPropagation();jumpToPinned(<?= (int)$pin['id'] ?>)">...</button>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="pin-all" onclick="togglePinBoard()">Xem tất cả ở bảng tin nhóm ›</div>
    </div>

    <!-- Messages -->
    <div class="chat-body" id="chatBody">
      <?php if(empty($messages)): ?>
      <div style="text-align:center;padding:40px;color:var(--text3);">
        <div style="font-size:36px;margin-bottom:8px;">💬</div>
        <div style="font-size:13px;">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</div>
      </div>
      <?php else:
        $lastDate = '';
        $lastUser = 0;
        foreach ($messages as $msg):
          $msgDate = date('d/m/Y', strtotime($msg['created_at']));
          if ($msgDate !== $lastDate):
            $lastDate = $msgDate;
            $lastUser = 0;
      ?>
      <div class="msg-date-sep"><span><?= $msgDate === date('d/m/Y') ? 'Hôm nay' : ($msgDate === date('d/m/Y', strtotime('-1 day')) ? 'Hôm qua' : $msgDate) ?></span></div>
      <?php endif; ?>

      <?php if ($msg['loai'] === 'system'): ?>
      <div class="msg-system">📢 <?= htmlspecialchars($msg['noi_dung']) ?></div>
      <?php else:
        $isMe = (int)$msg['user_id'] === $currentUserId;
        $isAdminMsg = in_array($msg['vai_tro'], ['quan_ly', 'chu_tro']);
        $colors = ['#4f8ef7,#7c5cfc','#22c993,#2dd4bf','#f7a94f,#f75c5c','#7c5cfc,#f472b6','#2dd4bf,#4f8ef7'];
        $col = $colors[$msg['user_id'] % count($colors)];
        $init = mb_strtoupper(mb_substr($msg['ho_ten'], 0, 1, 'UTF-8'));
        $showName = (!$isMe && (int)$msg['user_id'] !== $lastUser);
        $msgTextAttr = htmlspecialchars($msg['noi_dung'], ENT_QUOTES, 'UTF-8');
        $replyText = trim($msg['reply_text'] ?? '');
        $reaction = trim($msg['reaction'] ?? '');
        $pinned = (int)($msg['pinned'] ?? 0) === 1;
        $lastUser = (int)$msg['user_id'];
      ?>
      <div class="msg-row <?= $isMe ? 'me' : '' ?>" data-id="<?= $msg['id'] ?>" data-user-id="<?= (int)$msg['user_id'] ?>" data-text="<?= $msgTextAttr ?>" oncontextmenu="openMsgMenu(event,this)">
        <?php if (!$isMe): ?>
        <div class="msg-av" style="background:linear-gradient(135deg,<?= $col ?>);<?= !$showName ? 'visibility:hidden;' : '' ?>"><?= $init ?></div>
        <?php endif; ?>
        <div class="msg-wrap">
          <?php if ($showName && !$isMe): ?>
          <div class="msg-name">
            <?= htmlspecialchars($msg['ho_ten']) ?>
            <?php if ($isAdminMsg): ?><span class="badge-admin">ADMIN</span><?php endif; ?>
          </div>
          <?php endif; ?>
          <?php if ($pinned): ?><div class="msg-pin">📌 Đã ghim</div><?php endif; ?>
          <?php if ($replyText !== ''): ?><div class="msg-reply-ref">↩ <?= htmlspecialchars($replyText) ?></div><?php endif; ?>
          <div class="msg-bubble"><?= nl2br(htmlspecialchars($msg['noi_dung'])) ?></div>
          <div class="msg-time"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
        </div>
        <?php if ($reaction !== ''): ?><div class="msg-reaction"><?= htmlspecialchars($reaction) ?></div><?php endif; ?>
      </div>
      <?php endif;
        endforeach;
      endif; ?>
    </div>

    <!-- Scroll to bottom button -->
    <div class="scroll-bottom" id="scrollBtn" onclick="scrollToBottom()">↓</div>

    <!-- Typing indicator -->
    <div style="padding:0 20px 4px;">
      <div class="typing-indicator" id="typingIndicator">
        <div class="typing-dots"><span></span><span></span><span></span></div>
        <span id="typingText">đang nhập...</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="chat-ft">
      <div class="reply-preview" id="replyPreview">
        <div class="reply-preview-main">
          <div class="reply-preview-title">Đang trả lời</div>
          <div class="reply-preview-text" id="replyPreviewText"></div>
        </div>
        <button class="reply-close" type="button" onclick="clearReply()" title="Bỏ trả lời">×</button>
      </div>
      <div class="chat-input-row">
        <textarea class="chat-input" id="chatInput" placeholder="Nhập tin nhắn..." rows="1"></textarea>
        <button class="chat-send" id="chatSendBtn" onclick="sendMessage()" title="Gửi (Enter)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </div>
      <div class="chat-ft-hint">Enter gửi · Shift+Enter xuống dòng</div>
    </div>
  </div>
</div>

<?php if($isAdmin): ?>
<div class="sys-modal" id="sysModal" onclick="if(event.target===this)closeSystemModal()">
  <div class="sys-dialog" role="dialog" aria-modal="true" aria-labelledby="sysModalTitle">
    <div class="sys-head">
      <div class="sys-ico">📢</div>
      <div>
        <div class="sys-title" id="sysModalTitle">Gửi thông báo hệ thống</div>
        <div class="sys-sub">Tin này sẽ hiện giữa luồng chat cho toàn bộ nhóm.</div>
      </div>
    </div>
    <div class="sys-body">
      <textarea class="sys-text" id="sysText" maxlength="1000" placeholder="Nhập nội dung thông báo..."></textarea>
      <div style="font-size:10px;color:var(--text3);margin-top:7px;text-align:right;">Ctrl + Enter để gửi</div>
    </div>
    <div class="sys-actions">
      <button type="button" class="sys-btn cancel" onclick="closeSystemModal()">Hủy</button>
      <button type="button" class="sys-btn send" id="sysSendBtn" onclick="submitSystemMsg()">Gửi thông báo</button>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="msg-action-layer" id="msgActionLayer">
  <div class="react-bar">
    <button type="button" onclick="reactToSelected('❤️')">❤️</button>
    <button type="button" onclick="reactToSelected('😆')">😆</button>
    <button type="button" onclick="reactToSelected('😮')">😮</button>
    <button type="button" onclick="reactToSelected('😢')">😢</button>
    <button type="button" onclick="reactToSelected('😡')">😡</button>
    <button type="button" onclick="reactToSelected('👍')">👍</button>
  </div>
  <div class="ctx-menu">
    <button type="button" class="ctx-item" onclick="replySelected()"><span>Trả lời</span><span class="ctx-ico">↩</span></button>
    <button type="button" class="ctx-item" onclick="copySelected()"><span>Sao chép</span><span class="ctx-ico">⧉</span></button>
    <button type="button" class="ctx-item" onclick="forwardSelected()"><span>Chuyển tiếp</span><span class="ctx-ico">➜</span></button>
    <button type="button" class="ctx-item" onclick="pinSelected()"><span>Ghim</span><span class="ctx-ico">📌</span></button>
    <button type="button" class="ctx-item danger" onclick="deleteSelected()"><span>Xóa</span><span class="ctx-ico">🗑</span></button>
  </div>
</div>

<script>
const CURRENT_USER_ID = <?= $currentUserId ?>;
const CURRENT_NAME    = <?= json_encode($_SESSION['ho_ten'] ?? $_SESSION['user'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
const CURRENT_ROLE    = '<?= $_SESSION['vai_tro'] ?? 'user' ?>';
const IS_ADMIN        = <?= $isAdmin ? 'true' : 'false' ?>;
const COLORS = ['#4f8ef7,#7c5cfc','#22c993,#2dd4bf','#f7a94f,#f75c5c','#7c5cfc,#f472b6','#2dd4bf,#4f8ef7'];

let lastMsgId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;
let polling = null;
let lastRenderUser = 0;
let selectedMessage = null;
let replyTo = null;
let touchTimer = null;

function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function attr(s) { return esc(s).replace(/"/g,'&quot;'); }

function renderMsg(msg, showAvatar) {
  const isMe = parseInt(msg.user_id) === CURRENT_USER_ID;
  const isAdminMsg = ['quan_ly','chu_tro'].includes(msg.vai_tro);
  const col = COLORS[msg.user_id % COLORS.length];
  const init = msg.ho_ten.charAt(0).toUpperCase();
  const time = msg.created_at ? msg.created_at.substring(11, 16) : '';
  const showName = !isMe && showAvatar;

  if (msg.loai === 'system') {
    return `<div class="msg-system">📢 ${esc(msg.noi_dung)}</div>`;
  }

  const replyText = msg.reply_text ? `<div class="msg-reply-ref">↩ ${esc(msg.reply_text)}</div>` : '';
  const pinMark = parseInt(msg.pinned || 0) === 1 ? `<div class="msg-pin">📌 Đã ghim</div>` : '';
  const reaction = msg.reaction ? `<div class="msg-reaction">${esc(msg.reaction)}</div>` : '';

  let html = `<div class="msg-row ${isMe?'me':''}" data-id="${msg.id}" data-user-id="${msg.user_id}" data-text="${attr(msg.noi_dung)}" oncontextmenu="openMsgMenu(event,this)">`;
  if (!isMe) {
    html += `<div class="msg-av" style="background:linear-gradient(135deg,${col});${!showName?'visibility:hidden;':''}">${init}</div>`;
  }
  html += `<div class="msg-wrap">`;
  if (showName) {
    html += `<div class="msg-name">${esc(msg.ho_ten)}${isAdminMsg?'<span class="badge-admin">ADMIN</span>':''}</div>`;
  }
  html += `${pinMark}${replyText}<div class="msg-bubble">${esc(msg.noi_dung).replace(/\n/g,'<br>')}</div>`;
  html += `<div class="msg-time">${time}</div>`;
  html += `</div>${reaction}</div>`;
  return html;
}

async function sendMessage() {
  const input = document.getElementById('chatInput');
  const btn   = document.getElementById('chatSendBtn');
  const text  = input.value.trim();
  if (!text) return;

  input.value = '';
  input.style.height = '40px';
  btn.disabled = true;

  try {
    const res = await fetch('index.php?controller=chat&action=send', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ noi_dung: text, reply_to_id: replyTo ? replyTo.id : 0 })
    });
    const data = await res.json();
    if (data.ok && data.message) {
      const showAv = parseInt(data.message.user_id) !== lastRenderUser;
      appendHtml(renderMsg(data.message, showAv));
      lastMsgId = data.message.id;
      lastRenderUser = parseInt(data.message.user_id);
      clearReply();
    }
  } catch(e) {}

  btn.disabled = false;
  input.focus();
}

function appendHtml(html) {
  const body = document.getElementById('chatBody');
  body.insertAdjacentHTML('beforeend', html);
  scrollToBottom();
}

function scrollToBottom() {
  const body = document.getElementById('chatBody');
  body.scrollTop = body.scrollHeight;
}

function insertEmoji(emoji) {
  const input = document.getElementById('chatInput');
  const start = input.selectionStart;
  input.value = input.value.substring(0, start) + emoji + input.value.substring(input.selectionEnd);
  input.selectionStart = input.selectionEnd = start + emoji.length;
  input.focus();
}

function openMsgMenu(e, row) {
  e.preventDefault();
  selectedMessage = {
    id: parseInt(row.dataset.id),
    userId: parseInt(row.dataset.userId || 0),
    text: row.dataset.text || '',
    row
  };

  const layer = document.getElementById('msgActionLayer');
  const x = e.touches ? e.touches[0].clientX : e.clientX;
  const y = e.touches ? e.touches[0].clientY : e.clientY;
  layer.classList.add('show');
  layer.style.left = Math.min(x, window.innerWidth - layer.offsetWidth - 12) + 'px';
  layer.style.top = Math.max(12, Math.min(y - 58, window.innerHeight - layer.offsetHeight - 12)) + 'px';
}

function closeMsgMenu() {
  const layer = document.getElementById('msgActionLayer');
  if (layer) layer.classList.remove('show');
}

function setReactionBadge(row, reaction) {
  let badge = row.querySelector('.msg-reaction');
  if (!badge) {
    badge = document.createElement('div');
    badge.className = 'msg-reaction';
    row.appendChild(badge);
  }
  badge.textContent = reaction;
}

async function reactToSelected(reaction) {
  if (!selectedMessage) return;
  const msg = selectedMessage;
  setReactionBadge(msg.row, reaction);
  closeMsgMenu();
  await fetch('index.php?controller=chat&action=react', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id: msg.id, reaction })
  }).catch(() => {});
}

function replySelected() {
  if (!selectedMessage) return;
  replyTo = { id: selectedMessage.id, text: selectedMessage.text };
  const box = document.getElementById('replyPreview');
  const text = document.getElementById('replyPreviewText');
  text.textContent = replyTo.text;
  box.classList.add('show');
  closeMsgMenu();
  document.getElementById('chatInput').focus();
}

function clearReply() {
  replyTo = null;
  const box = document.getElementById('replyPreview');
  if (box) box.classList.remove('show');
}

async function copySelected() {
  if (!selectedMessage) return;
  try {
    await navigator.clipboard.writeText(selectedMessage.text);
  } catch (e) {
    const input = document.getElementById('chatInput');
    input.value = selectedMessage.text;
    input.select();
    document.execCommand('copy');
    input.value = '';
  }
  closeMsgMenu();
}

function forwardSelected() {
  if (!selectedMessage) return;
  const input = document.getElementById('chatInput');
  input.value = selectedMessage.text;
  input.dispatchEvent(new Event('input'));
  closeMsgMenu();
  input.focus();
}

async function deleteSelected() {
  if (!selectedMessage) return;
  if (!IS_ADMIN && selectedMessage.userId !== CURRENT_USER_ID) {
    closeMsgMenu();
    return;
  }
  const msg = selectedMessage;
  closeMsgMenu();
  const res = await fetch('index.php?controller=chat&action=delete', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id: msg.id })
  }).catch(() => null);
  if (res) {
    removePinnedItem(msg.id);
    msg.row.remove();
  }
}

async function pinSelected() {
  if (!selectedMessage) return;
  if (!IS_ADMIN && selectedMessage.userId !== CURRENT_USER_ID) {
    closeMsgMenu();
    return;
  }
  const msg = selectedMessage;
  closeMsgMenu();
  const res = await fetch('index.php?controller=chat&action=pin', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id: msg.id })
  }).catch(() => null);
  if (!res) return;
  const data = await res.json();
  if (!data.ok) return;

  let pin = msg.row.querySelector('.msg-pin');
  if (parseInt(data.pinned || 0) === 1) {
    if (!pin) {
      pin = document.createElement('div');
      pin.className = 'msg-pin';
      pin.textContent = '📌 Đã ghim';
      msg.row.querySelector('.msg-wrap').prepend(pin);
    }
    upsertPinnedItem(data.message || {id: msg.id, ho_ten: CURRENT_NAME, noi_dung: msg.text});
  } else if (pin) {
    pin.remove();
    removePinnedItem(msg.id);
  }
}

function pinCount() {
  return document.querySelectorAll('#pinList .pin-item').length;
}

function updatePinTitle() {
  const board = document.getElementById('pinBoard');
  const title = document.getElementById('pinTitle');
  const count = pinCount();
  if (title) title.textContent = `Danh sách ghim (${count})`;
  if (board) board.classList.toggle('show', count > 0);
}

function pinnedPreview(msg) {
  const text = String(msg.noi_dung || '');
  return text.length > 120 ? text.substring(0, 120) + '...' : text;
}

function upsertPinnedItem(msg) {
  const list = document.getElementById('pinList');
  if (!list || !msg) return;
  removePinnedItem(parseInt(msg.id), false);
  const item = document.createElement('div');
  item.className = 'pin-item';
  item.dataset.id = msg.id;
  item.onclick = () => jumpToPinned(parseInt(msg.id));
  item.innerHTML = `
    <div class="pin-icon">≡</div>
    <div class="pin-info">
      <div class="pin-title">Tin nhắn</div>
      <div class="pin-text">${esc(msg.ho_ten || CURRENT_NAME)}: ${esc(pinnedPreview(msg))}</div>
    </div>
    <button type="button" class="pin-more">...</button>
  `;
  item.querySelector('.pin-more').onclick = (e) => {
    e.stopPropagation();
    jumpToPinned(parseInt(msg.id));
  };
  list.prepend(item);
  updatePinTitle();
}

function removePinnedItem(id, update = true) {
  const item = document.querySelector(`#pinList .pin-item[data-id="${parseInt(id)}"]`);
  if (item) item.remove();
  if (update) updatePinTitle();
}

function renderPinnedBoard(messages) {
  const list = document.getElementById('pinList');
  if (!list) return;
  list.innerHTML = '';
  (messages || []).forEach(msg => upsertPinnedItem(msg));
  updatePinTitle();
}

async function refreshPinnedBoard() {
  try {
    const res = await fetch('index.php?controller=chat&action=pins');
    const data = await res.json();
    if (data.ok) renderPinnedBoard(data.messages || []);
  } catch(e) {}
}

function togglePinBoard() {
  const board = document.getElementById('pinBoard');
  const text = document.getElementById('pinToggleText');
  const icon = document.getElementById('pinToggleIcon');
  if (!board) return;
  board.classList.toggle('collapsed');
  const collapsed = board.classList.contains('collapsed');
  if (text) text.textContent = collapsed ? 'Mở rộng' : 'Thu gọn';
  if (icon) icon.textContent = collapsed ? '⌄' : '⌃';
}

function jumpToPinned(id) {
  const row = document.querySelector(`.msg-row[data-id="${parseInt(id)}"]`);
  if (!row) return;
  row.scrollIntoView({behavior:'smooth', block:'center'});
  row.classList.add('pin-focus');
  setTimeout(() => row.classList.remove('pin-focus'), 1400);
}

function sendSystemMsg() {
  openSystemModal();
}

function openSystemModal() {
  const modal = document.getElementById('sysModal');
  const input = document.getElementById('sysText');
  if (!modal || !input) return;
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
  input.value = '';
  setTimeout(() => input.focus(), 30);
}

function closeSystemModal() {
  const modal = document.getElementById('sysModal');
  if (!modal) return;
  modal.classList.remove('show');
  document.body.style.overflow = '';
}

function submitSystemMsg() {
  const input = document.getElementById('sysText');
  const btn = document.getElementById('sysSendBtn');
  const text = input ? input.value.trim() : '';
  if (!text) return;
  if (btn) btn.disabled = true;

  fetch('index.php?controller=chat&action=send', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ noi_dung: text, loai: 'system' })
  }).then(r => r.json()).then(data => {
    if (data.ok) {
      appendHtml(`<div class="msg-system">📢 ${esc(text)}</div>`);
      lastMsgId = data.message.id;
      closeSystemModal();
    }
  }).finally(() => {
    if (btn) btn.disabled = false;
  });
}

// Polling
function startPolling() {
  polling = setInterval(async () => {
    try {
      const res = await fetch('index.php?controller=chat&action=poll&after=' + lastMsgId);
      const data = await res.json();
      if (data.ok && data.messages.length > 0) {
        data.messages.forEach(msg => {
          if (parseInt(msg.user_id) !== CURRENT_USER_ID) {
            const showAv = parseInt(msg.user_id) !== lastRenderUser;
            appendHtml(renderMsg(msg, showAv));
            lastRenderUser = parseInt(msg.user_id);
          }
          lastMsgId = Math.max(lastMsgId, parseInt(msg.id));
        });
        // Sound notification
        if (document.hidden) document.title = '💬 Tin nhắn mới!';
      }
      refreshPinnedBoard();
    } catch(e) {}
  }, 2500);
}

// Scroll button
document.addEventListener('DOMContentLoaded', function() {
  const body = document.getElementById('chatBody');
  const scrollBtn = document.getElementById('scrollBtn');

  body.addEventListener('scroll', () => {
    const atBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 100;
    scrollBtn.classList.toggle('show', !atBottom);
  });

  // Input handlers
  const input = document.getElementById('chatInput');
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
  input.addEventListener('input', function() {
    this.style.height = '40px';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
  });

  document.addEventListener('click', function(e) {
    const layer = document.getElementById('msgActionLayer');
    if (layer && !layer.contains(e.target)) closeMsgMenu();
  });

  body.addEventListener('touchstart', function(e) {
    const row = e.target.closest('.msg-row');
    if (!row) return;
    touchTimer = setTimeout(() => openMsgMenu(e, row), 520);
  }, {passive:false});

  body.addEventListener('touchend', function() {
    if (touchTimer) clearTimeout(touchTimer);
  });

  body.addEventListener('touchmove', function() {
    if (touchTimer) clearTimeout(touchTimer);
  });

  const sysText = document.getElementById('sysText');
  if (sysText) {
    sysText.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        submitSystemMsg();
      }
      if (e.key === 'Escape') {
        closeSystemModal();
      }
    });
  }

  scrollToBottom();
  startPolling();
});

// Reset title on focus
window.addEventListener('focus', () => { document.title = '<?= e($title ?? '') ?> — Quản lý phòng trọ'; });
window.addEventListener('beforeunload', () => { if(polling) clearInterval(polling); });
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
