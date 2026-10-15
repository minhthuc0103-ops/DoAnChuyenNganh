<?php
$title = 'Huấn luyện AI';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>🧠 Huấn luyện AI</h1>
    <p>Dạy AI trả lời những câu hỏi chưa biết</p>
  </div>
</div>

<?php if (!empty($msg)): ?>
<div class="msg-alert msg-success" style="margin-bottom:16px;">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
<div class="msg-alert msg-error" style="margin-bottom:16px;">⚠ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<!-- TABS -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
  <a href="?controller=aiknowledge&action=index&tab=unknown"
     class="btn <?= $tab==='unknown'?'btn-primary':'btn-outline' ?>"
     style="position:relative;">
    ❓ Câu hỏi chưa biết
    <?php if ($soUnknownMoi > 0): ?>
    <span style="position:absolute;top:-6px;right:-6px;background:#f75c5c;color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:10px;"><?= $soUnknownMoi ?></span>
    <?php endif; ?>
  </a>
  <a href="?controller=aiknowledge&action=index&tab=knowledge"
     class="btn <?= $tab==='knowledge'?'btn-primary':'btn-outline' ?>">
    📚 Kho kiến thức (<?= count($knowledgeList) ?>)
  </a>
  <a href="?controller=aiknowledge&action=index&tab=add"
     class="btn <?= $tab==='add'?'btn-primary':'btn-outline' ?>">
    ＋ Thêm kiến thức
  </a>
  <a href="?controller=aiknowledge&action=seedDefault"
     class="btn btn-outline"
     style="margin-left:auto;color:var(--accent);border-color:var(--accent);"
     onclick="return confirm('Nạp 15 câu hỏi/trả lời mặc định vào kho kiến thức?\n(Các kiến thức đã có sẽ không bị ghi đè)')">
    🚀 Nạp kiến thức mặc định
  </a>
</div>

<!-- ═══ TAB: CÂU HỎI CHƯA BIẾT ═══ -->
<?php if ($tab === 'unknown'): ?>
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">❓ Câu hỏi user đã hỏi nhưng AI chưa trả lời được</div>
      <div class="card-sub">Click "Dạy AI" để thêm câu trả lời cho câu hỏi đó</div>
    </div>
  </div>
  <?php if (empty($unknownList)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">🎉</div>
    <div>AI đã trả lời được tất cả câu hỏi!</div>
  </div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="tbl">
      <thead>
        <tr>
          <th>Câu hỏi</th>
          <th>Người hỏi</th>
          <th>Số lần</th>
          <th>Thời gian</th>
          <th>Trạng thái</th>
          <th style="text-align:center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($unknownList as $u): ?>
        <tr style="<?= $u['da_xu_ly'] ? 'opacity:0.5' : '' ?>">
          <td style="max-width:300px;">
            <div style="font-size:13px;font-weight:600;color:var(--text);">
              <?= htmlspecialchars($u['cau_hoi']) ?>
            </div>
          </td>
          <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($u['ho_ten'] ?? '—') ?></td>
          <td>
            <span style="font-size:13px;font-weight:800;color:<?= $u['so_lan'] >= 3 ? 'var(--red)' : 'var(--text)' ?>;">
              <?= $u['so_lan'] ?>x
            </span>
          </td>
          <td style="font-size:11px;color:var(--text3);"><?= date('d/m H:i', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['da_xu_ly']): ?>
              <span class="pill p-green">Đã xử lý</span>
            <?php else: ?>
              <span class="pill p-red">Chưa xử lý</span>
            <?php endif; ?>
          </td>
          <td style="text-align:center;">
            <?php if (!$u['da_xu_ly']): ?>
            <button onclick="openTeachModal(<?= $u['id'] ?>, `<?= addslashes(htmlspecialchars($u['cau_hoi'])) ?>`)"
                    class="btn btn-primary btn-xs">🧠 Dạy AI</button>
            <?php endif; ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa câu hỏi này?')">
            <?= Csrf::field() ?>
              <input type="hidden" name="_action" value="delete_unknown"/>
              <input type="hidden" name="id" value="<?= $u['id'] ?>"/>
              <button class="btn btn-outline btn-xs" style="color:var(--red);">🗑</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══ TAB: KHO KIẾN THỨC ═══ -->
<?php if ($tab === 'knowledge'): ?>
<div class="card">
  <div class="card-header">
    <div class="card-title">📚 Kho kiến thức AI đã học</div>
  </div>
  <?php if (empty($knowledgeList)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">📭</div>
    <div>Chưa có kiến thức nào. <a href="?controller=aiknowledge&action=index&tab=add" style="color:var(--accent);">Thêm ngay</a></div>
  </div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="tbl">
      <thead>
        <tr><th>Từ khóa</th><th>Câu hỏi mẫu</th><th>Câu trả lời</th><th>Dùng</th><th style="text-align:center">Xóa</th></tr>
      </thead>
      <tbody>
        <?php foreach ($knowledgeList as $k): ?>
        <tr>
          <td>
            <?php foreach (explode(',', $k['tu_khoa']) as $kw): ?>
            <span style="display:inline-block;padding:2px 8px;border-radius:6px;background:rgba(79,142,247,.12);color:var(--accent);font-size:11px;font-weight:600;margin:1px;"><?= trim(htmlspecialchars($kw)) ?></span>
            <?php endforeach; ?>
          </td>
          <td style="font-size:12px;color:var(--text2);max-width:200px;"><?= htmlspecialchars($k['cau_hoi_mau']) ?></td>
          <td style="font-size:12px;color:var(--text);max-width:250px;white-space:pre-wrap;"><?= htmlspecialchars(mb_substr($k['tra_loi'], 0, 100)) ?><?= mb_strlen($k['tra_loi']) > 100 ? '...' : '' ?></td>
          <td><span style="font-size:13px;font-weight:700;color:var(--accent);"><?= $k['so_lan_dung'] ?>x</span></td>
          <td style="text-align:center;">
            <form method="POST" onsubmit="return confirm('Xóa kiến thức này?')">
            <?= Csrf::field() ?>
              <input type="hidden" name="_action" value="delete_knowledge"/>
              <input type="hidden" name="id" value="<?= $k['id'] ?>"/>
              <button class="btn btn-outline btn-xs" style="color:var(--red);">🗑 Xóa</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══ TAB: THÊM KIẾN THỨC ═══ -->
<?php if ($tab === 'add'): ?>
<div class="card" style="max-width:640px;">
  <div class="card-header">
    <div class="card-title">➕ Thêm kiến thức mới cho AI</div>
  </div>
  <div class="card-body">
    <form method="POST" action="?controller=aiknowledge&action=index&tab=knowledge">
            <?= Csrf::field() ?>
      <input type="hidden" name="_action" value="add_knowledge"/>
      <div class="form-group">
        <label class="form-label">Từ khóa <span style="color:var(--red)">*</span></label>
        <input class="form-control" type="text" name="tu_khoa"
               placeholder="vd: wifi, mật khẩu wifi, pass wifi"
               value="<?= htmlspecialchars($_POST['tu_khoa'] ?? '') ?>" required/>
        <div style="font-size:11px;color:var(--text3);margin-top:4px;">Cách nhau bằng dấu phẩy. AI sẽ match khi câu hỏi chứa bất kỳ từ nào.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Câu hỏi mẫu <span style="color:var(--red)">*</span></label>
        <input class="form-control" type="text" name="cau_hoi_mau"
               placeholder="vd: mật khẩu wifi là gì"
               value="<?= htmlspecialchars($_POST['cau_hoi_mau'] ?? '') ?>" required/>
        <div style="font-size:11px;color:var(--text3);margin-top:4px;">Dùng để so sánh độ giống nhau khi không match từ khóa.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Câu trả lời <span style="color:var(--red)">*</span></label>
        <textarea class="form-control" name="tra_loi" rows="5"
                  placeholder="vd: 📶 Mật khẩu Wifi khu A: roomA@2024&#10;Khu B: roomB@2024&#10;Liên hệ quản lý nếu cần hỗ trợ thêm."
                  required><?= htmlspecialchars($_POST['tra_loi'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
        🧠 Lưu kiến thức
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- MODAL DẠY AI TỪ CÂU HỎI CHƯA BIẾT -->
<div id="teachModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:1300;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(6px);"
     onclick="if(event.target===this)closeTeach()">
  <div style="background:var(--card);border:1px solid rgba(79,142,247,.2);border-radius:18px;width:100%;max-width:520px;overflow:hidden;animation:payIn .2s ease;">
    <div style="background:linear-gradient(135deg,#4f8ef7,#7c5cfc);padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
      <div style="font-size:15px;font-weight:800;color:#fff;">🧠 Dạy AI câu trả lời</div>
      <button onclick="closeTeach()" style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;">✕</button>
    </div>
    <div style="padding:22px;">
      <div style="padding:12px 14px;background:rgba(247,92,92,.07);border:1px solid rgba(247,92,92,.2);border-radius:10px;margin-bottom:18px;">
        <div style="font-size:11px;font-weight:700;color:var(--text2);margin-bottom:4px;">❓ Câu hỏi của user:</div>
        <div style="font-size:14px;font-weight:600;color:var(--text);" id="teachQuestion"></div>
      </div>
      <form method="POST" action="?controller=aiknowledge&action=index&tab=knowledge">
            <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="add_knowledge"/>
        <input type="hidden" name="unknown_id" id="teachUnknownId"/>
        <input type="hidden" name="cau_hoi_mau" id="teachCauHoiMau"/>
        <div class="form-group">
          <label class="form-label">Từ khóa</label>
          <input class="form-control" type="text" name="tu_khoa" id="teachTuKhoa"
                 placeholder="vd: wifi, mật khẩu" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Câu trả lời</label>
          <textarea class="form-control" name="tra_loi" rows="4"
                    placeholder="Nhập câu trả lời cho AI..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
          💾 Lưu & Dạy AI
        </button>
      </form>
    </div>
  </div>
</div>

<script src="frontend/assets/js/ai-knowledge.js"></script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>