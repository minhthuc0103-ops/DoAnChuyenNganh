<?php
$title = 'Quản lý hóa đơn';
require_once 'frontend/views/Layouts/header.php';
$thang = (int)($_GET['thang']??date('m'));
$nam   = (int)($_GET['nam']  ??date('Y'));
?>
<div class="page-header">
  <div class="page-title">
    <h1>Hóa đơn tháng <?= $thang ?>/<?= $nam ?></h1>
    <p><?= count($list??[]) ?> hóa đơn · <?= count(array_filter($list??[],fn($x)=>$x['trang_thai']==='chua_tt')) ?> chưa thanh toán</p>
  </div>
  <div class="header-actions">
    <a href="index.php?controller=hoadon&action=congNo" class="btn btn-outline">⚠ Công nợ</a>
    <a href="index.php?controller=hoadon&action=create" class="btn btn-primary">＋ Tạo hóa đơn</a>
  </div>
</div>

<link rel="stylesheet" href="frontend/assets/css/hoadon-gui.css">
<?php if (!empty($_GET['err'])): ?>
<div class="msg-alert msg-error" style="margin-bottom:14px;">⚠️ <?= htmlspecialchars($_GET['err']) ?></div>
<?php elseif (!empty($_GET['gui'])): ?>
<div class="msg-alert msg-success" style="margin-bottom:14px;">📤 <?= htmlspecialchars($_GET['gui']) ?></div>
<?php endif; ?>

<!-- FILTER THÁNG -->
<form method="GET" action="index.php" style="margin-bottom:16px;">
  <input type="hidden" name="controller" value="hoadon"/>
  <input type="hidden" name="action" value="index"/>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:8px;">
      <label style="font-size:13px;color:var(--text2)">Tháng:</label>
      <select class="form-control" name="thang" style="width:100px">
        <?php for($m=1;$m<=12;$m++): ?>
        <option value="<?= $m ?>" <?= $m===$thang?'selected':'' ?>>Tháng <?= $m ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <label style="font-size:13px;color:var(--text2)">Năm:</label>
      <select class="form-control" name="nam" style="width:100px">
        <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
        <option value="<?= $y ?>" <?= $y===$nam?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-outline">Lọc</button>
  </div>
</form>

<div class="card">
  <?php if(!empty($list)): ?>
  <table class="tbl tbl-r">
    <thead>
      <tr>
        <th>#</th><th>Phòng</th><th>Tiền phòng</th>
        <th>Tiền điện</th><th>Tiền nước</th>
        <th>Tổng tiền</th><th>Trạng thái</th>
        <th style="text-align:center">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($list as $i=>$hd): ?>
      <tr>
        <td class="mob-hide" style="color:var(--text3)"><?= $i+1 ?></td>
        <td data-label="Phòng"><span class="td-name"><?= htmlspecialchars($hd['so_phong']) ?></span></td>
        <td data-label="Tiền phòng"><?= number_format($hd['tien_phong']) ?>đ</td>
        <td data-label="Tiền điện">
          <div style="text-align:right;">
            <?= number_format($hd['tien_dien']) ?>đ
            <div style="font-size:10px;color:var(--text3)"><?= $hd['chi_so_dien_cu'] ?>→<?= $hd['chi_so_dien_moi'] ?> kWh</div>
          </div>
        </td>
        <td data-label="Tiền nước">
          <div style="text-align:right;">
            <?= number_format($hd['tien_nuoc']) ?>đ
            <div style="font-size:10px;color:var(--text3)"><?= $hd['chi_so_nuoc_cu'] ?>→<?= $hd['chi_so_nuoc_moi'] ?> m³</div>
          </div>
        </td>
        <td data-label="Tổng tiền"><strong style="color:var(--text);font-size:14px"><?= number_format($hd['tong_tien']) ?>đ</strong></td>
        <td data-label="Trạng thái">
          <?php if($hd['trang_thai']==='da_tt'): ?><span class="pill p-green">Đã TT</span>
          <?php elseif(!empty($hd['pending_count'])): ?><span class="pill p-amber">Chờ xác nhận</span>
          <?php else: ?><span class="pill p-red">Chưa TT</span>
          <?php endif; ?>
          <?php foreach (['email' => '✉️', 'zalo' => '💬'] as $kenh => $bieuTuong): $g = $trangThaiGui[$hd['id']][$kenh] ?? null; if (!$g) continue; ?>
          <div class="hd-gui hd-gui-<?= htmlspecialchars($g['trang_thai']) ?>" title="<?= htmlspecialchars($g['chi_tiet']) ?>">
            <?= $bieuTuong ?> <?= ['thanh_cong' => 'Đã gửi', 'that_bai' => 'Gửi lỗi', 'bo_qua' => 'Bỏ qua'][$g['trang_thai']] ?>
            <?= date('d/m H:i', strtotime($g['created_at'])) ?><?= $g['loai'] === 'bien_nhan' ? ' (biên nhận)' : '' ?>
          </div>
          <?php endforeach; ?>
        </td>
        <td class="mob-actions" style="text-align:center;">
          <div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap;">
            <a href="index.php?controller=hoadon&action=chiTiet&id=<?= $hd['id'] ?>"
               class="btn btn-outline btn-xs">👁 Xem</a>
            <?php if($hd['trang_thai']==='chua_tt'): ?>
            <a href="index.php?controller=hoadon&action=thanhToan&id=<?= $hd['id'] ?>"
               class="btn btn-success btn-xs"><?= !empty($hd['pending_count']) ? '✓ Xác nhận' : '💳 Thu tiền' ?></a>
            <?php endif; ?>
              <form method="POST" action="index.php?controller=hoadon&action=guiLai" style="display:inline;">
              <?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= (int)$hd['id'] ?>">
              <input type="hidden" name="thang" value="<?= $thang ?>"><input type="hidden" name="nam" value="<?= $nam ?>">
              <button type="submit" class="btn btn-outline btn-xs" style="cursor:pointer;" title="Gửi qua Gmail / Zalo ZNS">📤 <?= $hd['trang_thai'] === 'da_tt' ? 'Biên nhận' : 'Gửi' ?></button>
            </form>
            <?php $ng = $nguoiNhan[$hd['phong_id']] ?? null; if ($ng && !empty($ng['sdt'])): ?>
            <button type="button" class="btn btn-outline btn-xs hd-zalo" title="Sao chép nội dung và mở Zalo"
                    data-sdt="<?= htmlspecialchars(preg_replace('/\D/', '', $ng['sdt'])) ?>"
                    data-msg="<?= htmlspecialchars(HoaDonThongBao::vanBanZalo($hd, $ng), ENT_QUOTES) ?>">💬 Zalo</button>
            <?php endif; ?>
            <form method="POST" action="index.php?controller=hoadon&action=delete" style="display:inline;" onsubmit="return confirm('Xóa hóa đơn này?')">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $hd['id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs" style="cursor:pointer;">🗑</button>
          </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:48px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px">⚡</div>
    <div style="font-size:15px;font-weight:600;color:var(--text2);margin-bottom:4px">Chưa có hóa đơn tháng <?= $thang ?>/<?= $nam ?></div>
    <a href="index.php?controller=hoadon&action=create" style="color:var(--accent);font-size:13px">Tạo hóa đơn ngay →</a>
  </div>
  <?php endif; ?>
</div>

<script src="frontend/assets/js/hoadon-gui.js"></script>
<?php require_once 'frontend/views/Layouts/footer.php'; ?>