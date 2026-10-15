<?php
$title = 'Danh sách người thuê';
require_once 'frontend/views/Layouts/header.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>Danh sách người thuê</h1>
    <p>Người thuê được tạo tự động khi tạo hợp đồng</p>
  </div>
  <div class="header-actions">
    <a href="index.php?controller=nguoithue&action=yeuCauOCung" class="btn btn-outline">➕ Duyệt ở cùng</a>
    <a href="index.php?controller=hopdong&action=create" class="btn btn-primary">＋ Tạo hợp đồng mới</a>
  </div>
</div>

<!-- THÔNG BÁO -->
<div style="display:flex;gap:10px;margin-bottom:18px;padding:12px 16px;background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.18);border-radius:10px;align-items:center;">
  <span style="font-size:16px">ℹ</span>
  <span style="font-size:13px;color:var(--text2);">
    Người thuê được tạo khi <strong style="color:var(--accent)">tạo hợp đồng</strong>.
    Khi hợp đồng kết thúc hoặc bị xóa → người thuê sẽ không còn gắn với phòng.
  </span>
</div>

<!-- TÌM KIẾM -->
<form method="GET" action="index.php" style="margin-bottom:16px;">
  <input type="hidden" name="controller" value="nguoithue"/>
  <input type="hidden" name="action" value="index"/>
  <div style="display:flex;gap:10px;max-width:500px;">
    <input class="form-control" type="text" name="kw"
           placeholder="🔍 Tìm theo tên, CCCD, SĐT..."
           value="<?= htmlspecialchars($_GET['kw']??'') ?>"/>
    <button type="submit" class="btn btn-outline" style="white-space:nowrap;">Tìm</button>
    <?php if(!empty($_GET['kw'])): ?>
    <a href="index.php?controller=nguoithue&action=index" class="btn btn-outline">✕</a>
    <?php endif; ?>
  </div>
</form>

<div class="card">
  <?php if(!empty($list)): ?>
  <table class="tbl tbl-r">
    <thead>
      <tr>
        <th>#</th>
        <th>Họ và tên</th>
        <th>CCCD</th>
        <th>Số điện thoại</th>
        <th>Phòng đang ở</th>
        <th>Trạng thái</th>
        <th>Tài khoản</th>
        <th style="text-align:center">Thao tác</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($list as $i=>$nt):
      $colors=['#4f8ef7,#7c5cfc','#22c993,#4f8ef7','#f7a94f,#f75c5c','#7c5cfc,#f472b6','#2dd4bf,#22c993'];
      $col = $colors[$i % count($colors)];
      $init = mb_strtoupper(mb_substr($nt['ho_ten'],0,1,'UTF-8'));
      $dangThue = !empty($nt['hop_dong_id']);
      $accountStatus = $nt['account_status'] ?? 'dang_hoat_dong';
      $accountStatusMap = [
        'dang_hoat_dong' => ['p-green', 'Đang hoạt động'],
        'ngung_hoat_dong' => ['p-amber', 'Ngừng hoạt động'],
        'luu_tru' => ['p-purple', 'Lưu trữ'],
      ];
      $accountStatusInfo = $accountStatusMap[$accountStatus] ?? ['p-blue', $accountStatus];
    ?>
    <tr>
      <td class="mob-hide" style="color:var(--text3)"><?= $i+1 ?></td>
      <td data-label="Họ tên">
        <div class="av-row">
          <?php if(!empty($nt['avatar'])): ?>
          <img src="<?= htmlspecialchars($nt['avatar']) ?>"
               style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;"/>
          <?php else: ?>
          <div class="av" style="background:linear-gradient(135deg,<?= $col ?>)"><?= $init ?></div>
          <?php endif; ?>
          <span class="td-name"><?= htmlspecialchars($nt['ho_ten']) ?></span>
        </div>
      </td>
      <td data-label="CCCD" class="mob-hide"><?= htmlspecialchars($nt['cccd']??'—') ?></td>
      <td data-label="SĐT"><?= htmlspecialchars($nt['sdt']??'—') ?></td>
      <td data-label="Phòng">
        <?php if($dangThue): ?>
        <div style="text-align:right;">
          <span style="font-size:13px;font-weight:700;color:var(--accent);">
            🏠 <?= htmlspecialchars($nt['so_phong']??'') ?>
          </span>
          <?php if(!empty($nt['ten_khu'])): ?>
          <span style="font-size:11px;color:var(--text3);margin-left:4px;">(<?= htmlspecialchars($nt['ten_khu']) ?>)</span>
          <?php endif; ?>
          <div style="font-size:11px;color:var(--text3);">
            HĐ đến <?= date('d/m/Y', strtotime($nt['ngay_ket_thuc'])) ?>
          </div>
        </div>
        <?php else: ?>
        <span style="color:var(--text3);font-size:12px;">— Không có phòng</span>
        <?php endif; ?>
      </td>
      <td data-label="Trạng thái" class="mob-hide">
        <?php if($dangThue): ?>
        <span class="pill p-blue">Đang thuê</span>
        <?php else: ?>
        <span class="pill p-amber">Hết HĐ</span>
        <?php endif; ?>
      </td>
      <td data-label="Tài khoản" class="mob-hide">
        <span class="pill <?= $accountStatusInfo[0] ?>"><?= htmlspecialchars($accountStatusInfo[1]) ?></span>
        <?php if(!empty($nt['account_status_reason'])): ?>
        <div style="font-size:10px;color:var(--text3);margin-top:4px;max-width:180px;">
          <?= htmlspecialchars($nt['account_status_reason']) ?>
        </div>
        <?php endif; ?>
      </td>
      <td class="mob-actions" style="text-align:center;">
        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
          <?php if($dangThue): ?>
          <a href="index.php?controller=hopdong&action=index"
             class="btn btn-outline btn-xs">📄 Xem HĐ</a>
          <?php endif; ?>
          <a href="index.php?controller=nguoithue&action=edit&id=<?= $nt['id'] ?>"
             class="btn btn-outline btn-xs">✏ Sửa</a>
          <?php if(!$dangThue): ?>
          <form method="POST" action="index.php?controller=nguoithue&action=delete" style="display:inline" onsubmit="return confirm('Xóa người thuê <?= htmlspecialchars($nt['ho_ten'], ENT_QUOTES) ?>?')">
            <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $nt['id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs" style="cursor:pointer">🗑</button>
          </form>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:48px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px">👥</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);margin-bottom:4px">
      <?= !empty($_GET['kw']) ? 'Không tìm thấy kết quả' : 'Chưa có người thuê nào' ?>
    </div>
    <?php if(empty($_GET['kw'])): ?>
    <div style="font-size:13px;color:var(--text3);margin-bottom:14px">Tạo hợp đồng để thêm người thuê</div>
    <a href="index.php?controller=hopdong&action=create" class="btn btn-primary">＋ Tạo hợp đồng mới</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
