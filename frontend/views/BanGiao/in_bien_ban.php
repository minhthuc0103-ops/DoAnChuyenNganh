<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Biên bản bàn giao phòng - <?= htmlspecialchars($bb['ma_bien_ban']) ?></title>
  <style>
    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 14pt;
      line-height: 1.5;
      color: #000;
      background: #fff;
      margin: 0;
      padding: 30px;
    }
    .print-container {
      max-width: 800px;
      margin: 0 auto;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .header-table td {
      vertical-align: top;
      text-align: center;
    }
    .title {
      text-align: center;
      font-size: 18pt;
      font-weight: bold;
      text-transform: uppercase;
      margin: 25px 0 10px 0;
    }
    .code {
      text-align: center;
      font-style: italic;
      font-size: 11pt;
      margin-bottom: 25px;
    }
    .section-title {
      font-weight: bold;
      margin: 15px 0 5px 0;
    }
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin: 15px 0;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #000;
      padding: 6px 10px;
    }
    table.data-table th {
      background: #f2f2f2;
      font-weight: bold;
      text-align: center;
    }
    .sig-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 40px;
    }
    .sig-table td {
      width: 50%;
      text-align: center;
      vertical-align: top;
    }
    .print-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #2563eb;
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      box-shadow: 0 4px 12px rgba(0,0,0,.15);
    }
    @media print {
      .print-btn { display: none; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 In biên bản (Ctrl + P)</button>

<div class="print-container">
  <table class="header-table">
    <tr>
      <td style="width:45%;">
        <strong>HỆ THỐNG PHÒNG TRỌ</strong><br>
        <?= htmlspecialchars($bb['ten_khu'] ?? 'Khu trọ') ?>
      </td>
      <td style="width:55%;">
        <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
        <strong><u>Độc lập - Tự do - Hạnh phúc</u></strong>
      </td>
    </tr>
  </table>

  <div class="title">
    BIÊN BẢN BÀN GIAO <?= $bb['loai'] === 'tra_phong' ? 'TRẢ PHÒNG TRỌ' : 'NHẬN PHÒNG TRỌ' ?>
  </div>
  <div class="code">
    Số: <?= htmlspecialchars($bb['ma_bien_ban']) ?> · Ngày lập: <?= date('d/m/Y', strtotime($bb['ngay_lap'])) ?>
  </div>

  <p>Hôm nay, ngày <?= date('d', strtotime($bb['ngay_lap'])) ?> tháng <?= date('m', strtotime($bb['ngay_lap'])) ?> năm <?= date('Y', strtotime($bb['ngay_lap'])) ?>, tại phòng số <strong><?= htmlspecialchars($bb['so_phong']) ?></strong>, chúng tôi gồm có:</p>

  <div class="section-title">BÊN GIAO (BÊN CHO THUÊ - BÊN A):</div>
  <p style="margin: 4px 0 4px 20px;">
    - Đại diện: <strong><?= htmlspecialchars($bb['nguoi_lap_ten'] ?? 'Quản lý phòng trọ') ?></strong><br>
    - Địa chỉ khu trọ: <?= htmlspecialchars($bb['dia_chi'] ?? 'Tại khu phòng trọ') ?>
  </p>

  <div class="section-title">BÊN NHẬN (BÊN THUÊ - BÊN B):</div>
  <p style="margin: 4px 0 15px 20px;">
    - Họ và tên: <strong><?= htmlspecialchars($bb['ten_nguoi_thue']) ?></strong><br>
    - Số điện thoại: <?= htmlspecialchars($bb['sdt']) ?><br>
    - Số CCCD/CMND: <?= htmlspecialchars($bb['cccd']) ?>
  </p>

  <p>Hai bên tiến hành kiểm kê và thống nhất bàn giao các hạng mục trang thiết bị, tài sản tại phòng <strong><?= htmlspecialchars($bb['so_phong']) ?></strong> như sau:</p>

  <div class="section-title">1. Chỉ số công tơ điện và đồng hồ nước tại thời điểm bàn giao:</div>
  <p style="margin: 4px 0 10px 20px;">
    - Chỉ số điện: <strong><?= number_format($bb['chi_so_dien']) ?></strong> kWh<br>
    - Chỉ số nước: <strong><?= number_format($bb['chi_so_nuoc']) ?></strong> m³
  </p>

  <div class="section-title">2. Danh mục tài sản và trang thiết bị kèm theo phòng:</div>
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 8%;">STT</th>
        <th style="width: 47%;">Tên thiết bị / Tài sản</th>
        <th style="width: 15%;">Số lượng</th>
        <th style="width: 30%;">Tình trạng ghi nhận</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($danhSachTaiSan)): ?>
        <?php foreach ($danhSachTaiSan as $i => $item): ?>
        <tr>
          <td style="text-align:center;"><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($item['ten']) ?></td>
          <td style="text-align:center;"><?= $item['so_luong'] ?></td>
          <td><?= htmlspecialchars($item['tinh_trang']) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" style="text-align:center;">Phòng trống không kèm thiết bị rời.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (!empty($bb['ghi_chu'])): ?>
  <div class="section-title">3. Ghi chú và thỏa thuận khác:</div>
  <p style="margin: 4px 0 15px 20px;"><?= nl2br(htmlspecialchars($bb['ghi_chu'])) ?></p>
  <?php endif; ?>

  <p>Biên bản được lập thành 02 bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản để làm căn cứ theo dõi và bàn giao trả phòng khi thanh lý hợp đồng.</p>

  <table class="sig-table">
    <tr>
      <td>
        <strong>ĐẠI DIỆN BÊN CHO THUÊ</strong><br>
        <i>(Ký và ghi rõ họ tên)</i>
        <div style="height: 80px;"></div>
        <strong><?= htmlspecialchars($bb['nguoi_lap_ten'] ?? 'Quản lý') ?></strong>
      </td>
      <td>
        <strong>ĐẠI DIỆN BÊN THUÊ</strong><br>
        <i>(Ký và ghi rõ họ tên)</i>
        <div style="height: 80px;"></div>
        <strong><?= htmlspecialchars($bb['ten_nguoi_thue']) ?></strong>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
