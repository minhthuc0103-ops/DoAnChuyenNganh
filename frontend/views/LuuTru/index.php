<?php
$title = 'Khai báo lưu trú (Tạm trú)';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>📋 Quản lý khai báo lưu trú</h1>
    <p>Theo dõi, lập tờ khai tạm trú (Mẫu CT01) và quản lý lưu trú cho khách thuê trọ</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <form method="POST" action="index.php?controller=luutru&action=sync" style="display:inline;" onsubmit="return confirm('Hệ thống sẽ quét tất cả hợp đồng đang hiệu lực và tự động thêm những người thuê/ở cùng chưa được khai báo. Tiếp tục?')">
      <?= Csrf::field() ?>
      <button type="submit" class="btn btn-outline" style="background:rgba(124,92,252,.1);border-color:rgba(124,92,252,.3);color:var(--accent2);font-weight:600;">
        ⚡ Đồng bộ từ Hợp đồng
      </button>
    </form>
    <a href="index.php?controller=luutru&action=export" class="btn btn-outline">
      📥 Xuất file CSV/Excel
    </a>
    <a href="index.php?controller=luutru&action=create" class="btn btn-primary">
      ＋ Thêm người lưu trú
    </a>
  </div>
</div>

<?php if(isset($_GET['msg'])): ?>
  <?php if($_GET['msg'] === 'synced'): ?>
    <div class="msg-alert msg-success" style="margin-bottom:18px;">
      ✅ Đã quét và đồng bộ thành công <strong><?= (int)($_GET['count']??0) ?></strong> người thuê vào danh sách lưu trú!
    </div>
  <?php elseif($_GET['msg'] === 'created'): ?>
    <div class="msg-alert msg-success" style="margin-bottom:18px;">✅ Đã thêm thông tin lưu trú mới thành công!</div>
  <?php elseif($_GET['msg'] === 'updated'): ?>
    <div class="msg-alert msg-success" style="margin-bottom:18px;">✅ Cập nhật thông tin lưu trú thành công!</div>
  <?php elseif($_GET['msg'] === 'status_updated'): ?>
    <div class="msg-alert msg-success" style="margin-bottom:18px;">✅ Đã cập nhật trạng thái hồ sơ lưu trú!</div>
  <?php elseif($_GET['msg'] === 'deleted'): ?>
    <div class="msg-alert msg-success" style="margin-bottom:18px;">✅ Đã xóa bản ghi lưu trú thành công!</div>
  <?php endif; ?>
<?php endif; ?>

<?php require 'frontend/views/Layouts/loc_hop_dong.php'; ?>

<!-- THỐNG KÊ NHANH -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;">
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:12px;background:rgba(79,142,247,.12);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--accent);">👥</div>
    <div>
      <div style="font-size:12px;color:var(--text3);font-weight:600;text-transform:uppercase;">Tổng lưu trú</div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?= $stats['total'] ?> người</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:12px;background:rgba(247,169,79,.12);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--amber);">⏳</div>
    <div>
      <div style="font-size:12px;color:var(--text3);font-weight:600;text-transform:uppercase;">Chờ khai báo</div>
      <div style="font-size:22px;font-weight:800;color:var(--amber);"><?= $stats['cho_khai_bao'] ?> hồ sơ</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:12px;background:rgba(124,92,252,.12);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--accent2);">🌐</div>
    <div>
      <div style="font-size:12px;color:var(--text3);font-weight:600;text-transform:uppercase;">Đã gửi DVC</div>
      <div style="font-size:22px;font-weight:800;color:var(--accent2);"><?= $stats['da_gui_dvc'] ?> hồ sơ</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:12px;background:rgba(34,201,147,.12);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--green);">✅</div>
    <div>
      <div style="font-size:12px;color:var(--text3);font-weight:600;text-transform:uppercase;">Đã tiếp nhận</div>
      <div style="font-size:22px;font-weight:800;color:var(--green);"><?= $stats['da_tiep_nhan'] ?> hồ sơ</div>
    </div>
  </div>
</div>

<!-- FORM TÌM KIẾM / LỌC -->
<div class="card" style="padding:16px;margin-bottom:20px;">
  <form method="GET" action="index.php" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <input type="hidden" name="controller" value="luutru">
    <input type="hidden" name="action" value="index">
    <?php if (LocHopDong::xemLichSu()): ?><input type="hidden" name="lich_su" value="1"><?php endif; ?>

    <div style="flex:1;min-width:220px;position:relative;">
      <input type="text" name="keyword" id="ltKeyword" class="form-control" autocomplete="off"
             placeholder="Nhập tên, SĐT hoặc CCCD để xem người đã đăng ký lưu trú..." value="<?= htmlspecialchars($filter['keyword']??'') ?>">
      <div id="ltSuggest" style="display:none;position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:50;background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 30px rgba(0,0,0,.35);max-height:340px;overflow-y:auto;"></div>
    </div>

    <div style="min-width:160px;">
      <select name="khu_id" class="form-control" onchange="this.form.submit()">
        <option value="">— Tất cả khu trọ —</option>
        <?php foreach($khus as $k): ?>
        <option value="<?= $k['id'] ?>" <?= ($filter['khu_id']??'')==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['ten_khu']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="min-width:160px;">
      <select name="trang_thai" class="form-control" onchange="this.form.submit()">
        <option value="">— Tất cả trạng thái —</option>
        <option value="cho_khai_bao" <?= ($filter['trang_thai']??'')==='cho_khai_bao'?'selected':'' ?>>⏳ Chờ khai báo</option>
        <option value="da_gui_dvc" <?= ($filter['trang_thai']??'')==='da_gui_dvc'?'selected':'' ?>>🌐 Đã gửi Dịch vụ công</option>
        <option value="da_tiep_nhan" <?= ($filter['trang_thai']??'')==='da_tiep_nhan'?'selected':'' ?>>✅ Đã tiếp nhận</option>
        <option value="ket_thuc" <?= ($filter['trang_thai']??'')==='ket_thuc'?'selected':'' ?>>🚪 Đã kết thúc</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary" style="padding:8px 16px;">🔍 Lọc</button>
    <a href="index.php?controller=luutru&action=index" class="btn btn-outline" style="padding:8px 14px;">Xóa lọc</a>
  </form>
</div>

<!-- BẢNG DANH SÁCH -->
<div class="card" style="overflow:hidden;">
  <div style="overflow-x:auto;">
    <table class="table" style="width:100%;margin-bottom:0;">
      <thead>
        <tr>
          <th>Người lưu trú</th>
          <th>Thông tin CCCD</th>
          <th>Phòng / Khu</th>
          <th>Thời gian lưu trú</th>
          <th>Trạng thái</th>
          <th>Mã hồ sơ DVC</th>
          <th style="text-align:right;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($list)): ?>
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:var(--text3);">
            <div style="font-size:36px;margin-bottom:8px;">📁</div>
            Chưa có thông tin lưu trú nào. Bấm <strong>"⚡ Đồng bộ từ Hợp đồng"</strong> hoặc <strong>"＋ Thêm người lưu trú"</strong> để bắt đầu!
          </td>
        </tr>
        <?php else: ?>
        <?php foreach($list as $r): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0;">
                <?= strtoupper(mb_substr($r['ho_ten'], 0, 1, 'UTF-8')) ?>
              </div>
              <div>
                <a href="index.php?controller=luutru&action=detail&id=<?= $r['id'] ?>" style="color:var(--text);font-size:14px;font-weight:700;text-decoration:none;" title="Xem chi tiết thông tin cá nhân"><?= htmlspecialchars($r['ho_ten']) ?></a>
                <div style="font-size:12px;color:var(--text3);">
                  <?= $r['gioi_tinh']==='nu'?'Nữ':'Nam' ?> · <?= $r['ngay_sinh'] ? date('d/m/Y', strtotime($r['ngay_sinh'])) : 'Chưa có NS' ?>
                </div>
              </div>
            </div>
          </td>

          <td>
            <div style="font-size:13px;font-weight:600;color:var(--accent);">
              🪪 <?= htmlspecialchars($r['cccd']) ?>
            </div>
            <div style="font-size:12px;color:var(--text3);">
              📞 <?= htmlspecialchars($r['sdt'] ?: 'Chưa có SĐT') ?>
            </div>
            <?php if(!empty($r['thuong_tru'])): ?>
            <div style="font-size:11px;color:var(--text3);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($r['thuong_tru']) ?>">
              📍 <?= htmlspecialchars($r['thuong_tru']) ?>
            </div>
            <?php endif; ?>
          </td>

          <td>
            <span class="badge" style="background:rgba(79,142,247,.12);color:var(--accent);font-weight:700;">
              Phòng <?= htmlspecialchars($r['so_phong']) ?>
            </span>
            <div style="font-size:12px;color:var(--text3);margin-top:2px;">
              <?= htmlspecialchars($r['ten_khu'] ?? '') ?>
            </div>
          </td>

          <td>
            <div style="font-size:12px;">
              <span style="color:var(--green)">Từ:</span> <?= date('d/m/Y', strtotime($r['ngay_den'])) ?>
            </div>
            <div style="font-size:12px;color:var(--text3);">
              <span style="color:var(--red)">Đến:</span> <?= $r['ngay_di'] ? date('d/m/Y', strtotime($r['ngay_di'])) : 'Không xác định' ?>
            </div>
          </td>

          <td>
            <?php if($r['trang_thai'] === 'cho_khai_bao'): ?>
              <span class="badge" style="background:rgba(247,169,79,.15);color:var(--amber);border:1px solid rgba(247,169,79,.3);">⏳ Chờ khai báo</span>
            <?php elseif($r['trang_thai'] === 'da_gui_dvc'): ?>
              <span class="badge" style="background:rgba(124,92,252,.15);color:var(--accent2);border:1px solid rgba(124,92,252,.3);">🌐 Đã gửi DVC</span>
            <?php elseif($r['trang_thai'] === 'da_tiep_nhan'): ?>
              <span class="badge" style="background:rgba(34,201,147,.15);color:var(--green);border:1px solid rgba(34,201,147,.3);">✅ Đã tiếp nhận</span>
            <?php else: ?>
              <span class="badge" style="background:rgba(156,163,175,.15);color:#9ca3af;border:1px solid rgba(156,163,175,.3);">🚪 Đã kết thúc</span>
            <?php endif; ?>
          </td>

          <td>
            <?php if(!empty($r['ma_dvc'])): ?>
              <code style="background:var(--bg3);padding:3px 6px;border-radius:4px;font-size:12px;color:var(--accent);"><?= htmlspecialchars($r['ma_dvc']) ?></code>
            <?php else: ?>
              <span style="color:var(--text3);font-size:12px;">—</span>
            <?php endif; ?>
          </td>

          <td style="text-align:right;">
            <div style="display:inline-flex;gap:6px;align-items:center;">
              <!-- Nút In Mẫu CT01 -->
              <a href="index.php?controller=luutru&action=mauCT01&id=<?= $r['id'] ?>" target="_blank" class="btn btn-outline" style="padding:5px 10px;font-size:12px;background:rgba(79,142,247,.08);color:var(--accent);" title="In Tờ khai thay đổi thông tin cư trú Mẫu CT01 (Bộ Công An)">
                📄 Mẫu CT01
              </a>

              <!-- Nút Đổi trạng thái nhanh -->
              <button type="button" class="btn btn-outline" style="padding:5px 8px;font-size:12px;" onclick="openStatusModal(<?= $r['id'] ?>, '<?= $r['trang_thai'] ?>', '<?= htmlspecialchars($r['ma_dvc']??'', ENT_QUOTES) ?>')">
                ⚙️
              </button>

              <!-- Sửa -->
              <a href="index.php?controller=luutru&action=edit&id=<?= $r['id'] ?>" class="btn btn-outline" style="padding:5px 8px;font-size:12px;">
                ✏️
              </a>

              <!-- Xóa -->
              <form method="POST" action="index.php?controller=luutru&action=delete" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa bản ghi lưu trú này?')">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn btn-outline" style="padding:5px 8px;font-size:12px;color:var(--red);">
                  🗑
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL CẬP NHẬT TRẠNG THÁI -->
<div id="statusModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;width:90%;max-width:440px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.4);">
    <h3 style="margin-top:0;margin-bottom:14px;font-size:18px;">Cập nhật trạng thái lưu trú</h3>
    <form method="POST" action="index.php?controller=luutru&action=updateStatus">
      <?= Csrf::field() ?>
      <input type="hidden" name="id" id="modal_id" value="">
      
      <div class="form-group">
        <label class="form-label">Trạng thái hồ sơ</label>
        <select class="form-control" name="trang_thai" id="modal_status" required>
          <option value="cho_khai_bao">⏳ Chờ khai báo</option>
          <option value="da_gui_dvc">🌐 Đã nộp Cổng Dịch vụ công (DVC)</option>
          <option value="da_tiep_nhan">✅ Đã tiếp nhận (Công an phường phê duyệt)</option>
          <option value="ket_thuc">🚪 Đã kết thúc / Đã trả phòng</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Mã hồ sơ Cổng Dịch vụ công (nếu có)</label>
        <input type="text" class="form-control" name="ma_dvc" id="modal_dvc" placeholder="VD: DVC-2026-98124">
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
        <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Đóng</button>
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusModal(id, status, dvc) {
  document.getElementById('modal_id').value = id;
  document.getElementById('modal_status').value = status;
  document.getElementById('modal_dvc').value = dvc || '';
  document.getElementById('statusModal').style.display = 'flex';
}

function closeStatusModal() {
  document.getElementById('statusModal').style.display = 'none';
}

// ── Gợi ý khi gõ tên / SĐT / CCCD: bấm vào để xem chi tiết thông tin cá nhân ──
(function () {
  var input = document.getElementById('ltKeyword');
  var box   = document.getElementById('ltSuggest');
  if (!input || !box) return;
  var timer = null, seq = 0;

  function hide() { box.style.display = 'none'; box.textContent = ''; }

  function render(rows) {
    box.textContent = '';
    if (!rows.length) {
      var empty = document.createElement('div');
      empty.style.cssText = 'padding:14px;color:var(--text3);font-size:13px;';
      empty.textContent = 'Không có ai đã đăng ký lưu trú khớp với từ khóa.';
      box.appendChild(empty);
    }
    rows.forEach(function (r) {
      var a = document.createElement('a');
      a.href = 'index.php?controller=luutru&action=detail&id=' + encodeURIComponent(r.id);
      a.style.cssText = 'display:block;padding:10px 14px;text-decoration:none;color:var(--text);border-bottom:1px solid var(--border);';
      a.onmouseover = function () { a.style.background = 'var(--bg3)'; };
      a.onmouseout  = function () { a.style.background = 'transparent'; };

      var name = document.createElement('div');
      name.style.cssText = 'font-weight:700;font-size:14px;';
      name.textContent = r.name;

      var meta = document.createElement('div');
      meta.style.cssText = 'font-size:12px;color:var(--text3);margin-top:2px;';
      meta.textContent = 'CCCD ' + (r.cccd || '—') + ' · SĐT ' + (r.phone || '—') +
                         ' · Phòng ' + r.room + (r.area ? ' (' + r.area + ')' : '');

      a.appendChild(name);
      a.appendChild(meta);
      box.appendChild(a);
    });
    box.style.display = 'block';
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = input.value.trim();
    if (q.length < 2) { hide(); return; }
    timer = setTimeout(function () {
      var mine = ++seq;
      fetch('index.php?controller=luutru&action=suggest&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) { if (mine === seq && data.ok) render(data.results || []); })
        .catch(hide);
    }, 200);
  });

  document.addEventListener('click', function (e) {
    if (e.target !== input && !box.contains(e.target)) hide();
  });
  input.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
})();
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>

