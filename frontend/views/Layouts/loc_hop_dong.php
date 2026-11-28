<?php
// Nút chuyển "Hợp đồng đang hiệu lực" / "Tất cả (gồm lịch sử cũ)" cho các trang quản trị (xem backend/Helpers/LocHopDong.php).
// Giữ nguyên các tham số lọc khác trên URL, bỏ các tham số chỉ để hiện thông báo một lần.
$__lq = $_GET;
foreach (['lich_su', 'msg', 'gui', 'err', 'count'] as $__k) unset($__lq[$__k]);
$__xemCu   = LocHopDong::xemLichSu();
$__urlMoi  = 'index.php?' . http_build_query($__lq);
$__urlCu   = 'index.php?' . http_build_query($__lq + ['lich_su' => 1]);
$__kieu = fn(bool $on) => $on
    ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:1px solid transparent;'
    : 'background:var(--card);color:var(--text2);border:1px solid var(--border);';
?>
<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px;">
  <span style="font-size:12px;color:var(--text3);font-weight:600;">HIỂN THỊ:</span>
  <a href="<?= htmlspecialchars($__urlMoi) ?>" style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;<?= $__kieu(!$__xemCu) ?>">📄 Hợp đồng đang hiệu lực</a>
  <a href="<?= htmlspecialchars($__urlCu) ?>" style="padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;<?= $__kieu($__xemCu) ?>">🗂 Tất cả (gồm lịch sử cũ)</a>
  <span style="font-size:11px;color:var(--text3);"><?= $__xemCu ? 'Đang xem cả dữ liệu của người thuê trước và hợp đồng đã kết thúc.' : 'Chỉ dữ liệu của người đang thuê, từ ngày hợp đồng bắt đầu.' ?></span>
</div>
