<?php
$title = 'Báo cáo thống kê doanh thu';
require_once 'frontend/views/Layouts/header.php';
$nam = (int)($_GET['nam']??date('Y'));
?>
<div class="page-header">
  <div class="page-title">
    <h1>Báo cáo doanh thu</h1>
    <p>Thống kê thu nhập năm <?= $nam ?></p>
  </div>
  <div class="header-actions">
    <form method="GET" action="index.php" style="display:flex;gap:8px;align-items:center;">
      <input type="hidden" name="controller" value="baocao"/>
      <input type="hidden" name="action" value="index"/>
      <select class="form-control" name="nam" style="width:100px">
        <?php for($y=date('Y');$y>=date('Y')-4;$y--): ?>
        <option value="<?= $y ?>" <?= $y===$nam?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
      <button type="submit" class="btn btn-outline">Lọc</button>
    </form>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon si-green">💰</div>
    <div class="stat-val"><?= number_format(($tongNam??0)/1000000,1) ?>M</div>
    <div class="stat-lbl">Tổng doanh thu năm <?= $nam ?></div>
    <div class="stat-chg chg-up">Đã thu được</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-blue">📅</div>
    <div class="stat-val"><?= number_format(($doanhThuThang??0)/1000000,1) ?>M</div>
    <div class="stat-lbl">Tháng <?= date('n') ?>/<?= $nam ?></div>
    <div class="stat-chg chg-flat">Tháng hiện tại</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-blue">🏠</div>
    <div class="stat-val"><?= ($stats['dang_thue']??0) ?></div>
    <div class="stat-lbl">Phòng đang thuê</div>
    <div class="stat-chg chg-up">Trên tổng <?= array_sum($stats??[]) ?> phòng</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-amber">🔑</div>
    <div class="stat-val"><?= ($stats['trong']??0) ?></div>
    <div class="stat-lbl">Phòng còn trống</div>
    <div class="stat-chg chg-dn">Chưa có người thuê</div>
  </div>
</div>

<!-- CHART -->
<div class="card" style="margin-bottom:18px;">
  <div class="card-header">
    <div><div class="card-title">Biểu đồ doanh thu theo tháng — Năm <?= $nam ?></div></div>
    <div style="display:flex;gap:12px;">
      <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text2);"><span style="width:10px;height:10px;border-radius:2px;background:var(--accent);display:inline-block;"></span>Doanh thu (đã thu)</span>
    </div>
  </div>
  <div style="padding:20px 24px 24px;">
    <?php $chartData = $chartData ?? array_fill(1,12,0); $maxVal = max(array_values($chartData))?:1; ?>
    <div style="display:flex;align-items:flex-end;gap:8px;height:160px;">
      <?php for($m=1;$m<=12;$m++):
        $val   = $chartData[$m]??0;
        $pct   = round($val/$maxVal*100);
        $isCur = ($m===(int)date('m'));
      ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;height:100%;justify-content:flex-end;">
        <?php if($val>0): ?>
        <div style="font-size:9px;color:var(--text3);font-weight:600"><?= number_format($val/1000000,1) ?>M</div>
        <?php endif; ?>
        <div style="width:100%;height:<?= max($pct,3) ?>%;border-radius:5px 5px 0 0;
                    background:<?= $isCur?'linear-gradient(180deg,var(--green),rgba(34,201,147,.3))':'linear-gradient(180deg,var(--accent),rgba(79,142,247,.3))' ?>;
                    cursor:pointer;transition:opacity .15s;min-height:4px;"
             onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'"
             title="Tháng <?= $m ?>: <?= number_format($val) ?>đ">
        </div>
        <div style="font-size:10px;color:<?= $isCur?'var(--green)':'var(--text3)' ?>;font-weight:<?= $isCur?'700':'500' ?>">
          T<?= $m ?><?= $isCur?' ●':'' ?>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- BẢNG CHI TIẾT -->
<div class="card">
  <div class="card-header"><div class="card-title">Chi tiết từng tháng</div></div>
  <table class="tbl">
    <thead>
      <tr><th>Tháng</th><th>Doanh thu</th><th>So sánh</th><th></th></tr>
    </thead>
    <tbody>
      <?php
      $prev = 0;
      for($m=1;$m<=12;$m++):
        $val  = $chartData[$m]??0;
        $diff = $val - $prev;
        $isCur= ($m===(int)date('m') && $nam===(int)date('Y'));
      ?>
      <tr style="<?= $isCur?'background:rgba(79,142,247,.04)':'' ?>">
        <td>
          <strong style="color:var(--text)">Tháng <?= $m ?>/<?= $nam ?></strong>
          <?php if($isCur): ?><span class="pill p-blue" style="margin-left:6px">Tháng này</span><?php endif; ?>
        </td>
        <td>
          <?php if($val>0): ?>
            <strong style="color:var(--green)"><?= number_format($val) ?>đ</strong>
          <?php else: ?>
            <span style="color:var(--text3)">— Chưa có</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($m>1 && $val>0 && $prev>0):
            $pct=round(($val-$prev)/$prev*100);
          ?>
          <span style="font-size:12px;font-weight:600;color:<?= $pct>=0?'var(--green)':'var(--red)' ?>">
            <?= $pct>=0?'↑':'↓' ?> <?= abs($pct) ?>%
          </span>
          <?php else: ?><span style="color:var(--text3)">—</span><?php endif; ?>
        </td>
        <td>
          <a href="index.php?controller=hoadon&action=index&thang=<?= $m ?>&nam=<?= $nam ?>"
             style="font-size:12px;color:var(--accent)">Xem HĐ →</a>
        </td>
      </tr>
      <?php $prev=$val; endfor; ?>
    </tbody>
  </table>
</div>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>