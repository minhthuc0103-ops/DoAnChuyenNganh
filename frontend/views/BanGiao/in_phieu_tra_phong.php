<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Phiếu quyết toán trả phòng #<?= $yc['id'] ?> - Phòng <?= htmlspecialchars($yc['so_phong']) ?></title>
  <style>
    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 13.5pt;
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
      font-size: 17pt;
      font-weight: bold;
      text-transform: uppercase;
      margin: 20px 0 8px 0;
    }
    .code {
      text-align: center;
      font-style: italic;
      font-size: 11pt;
      margin-bottom: 20px;
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
      margin-top: 35px;
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
      background: #16a34a;
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

<button class="print-btn" onclick="window.print()">🖨 In phiếu quyết toán (Ctrl + P)</button>

<div class="print-container">
  <table class="header-table">
    <tr>
      <td style="width:45%;">
        <strong>HỆ THỐNG PHÒNG TRỌ</strong><br>
        <?= htmlspecialchars($yc['ten_khu'] ?? 'Khu trọ') ?>
      </td>
      <td style="width:55%;">
        <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
        <strong><u>Độc lập - Tự do - Hạnh phúc</u></strong>
      </td>
    </tr>
  </table>

  <div class="title">
    PHIẾU THANH LÝ HỢP ĐỒNG & QUYẾT TOÁN TRẢ PHÒNG
  </div>
  <div class="code">
    Mã quyết toán: #TP-<?= $yc['id'] ?> · Ngày lập phiếu: <?= date('d/m/Y') ?>
  </div>

  <p>Hôm nay, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>, hai bên tiến hành kiểm tra nghiệm thu trả phòng <strong><?= htmlspecialchars($yc['so_phong']) ?></strong> (Hợp đồng thuê #<?= $yc['hop_dong_id'] ?>) với các thông tin chi tiết như sau:</p>

  <p style="margin-bottom: 6px;">
    <strong>1. Thông tin người thuê phòng:</strong><br>
    - Họ và tên: <strong><?= htmlspecialchars($yc['ten_nguoi_thue']) ?></strong><br>
    - Số điện thoại: <?= htmlspecialchars($yc['sdt']) ?><br>
    - Số CCCD: <?= htmlspecialchars($yc['cccd']) ?><br>
    - Lý do trả phòng: <?= htmlspecialchars($yc['ly_do']) ?>
  </p>

  <p style="margin-bottom: 6px;">
    <strong>2. Bảng kê quyết toán công nợ và hoàn trả tiền đặt cọc:</strong>
  </p>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width:8%;">STT</th>
        <th style="width:47%;">Khoản mục quyết toán</th>
        <th style="width:25%;">Chi tiết số liệu</th>
        <th style="width:20%;">Thành tiền (VNĐ)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:center;">1</td>
        <td><strong>Tiền đặt cọc giữ phòng (Bên A nhận ban đầu)</strong></td>
        <td>Hợp đồng #<?= $yc['hop_dong_id'] ?></td>
        <td style="text-align:right;font-weight:bold;"><?= number_format($yc['tien_coc_goc']) ?> đ</td>
      </tr>
      <tr>
        <td style="text-align:center;">2</td>
        <td>Khấu trừ tiền điện chưa thanh toán</td>
        <td>
          <?= number_format($yc['chi_so_dien_cuoi'] - $yc['chi_so_dien_dau']) ?> kWh
          (<?= number_format($yc['chi_so_dien_dau']) ?> → <?= number_format($yc['chi_so_dien_cuoi']) ?>)
        </td>
        <td style="text-align:right;color:#dc2626;">- <?= number_format($yc['tien_dien']) ?> đ</td>
      </tr>
      <tr>
        <td style="text-align:center;">3</td>
        <td>Khấu trừ tiền nước chưa thanh toán</td>
        <td>
          <?= number_format($yc['chi_so_nuoc_cuoi'] - $yc['chi_so_nuoc_dau']) ?> m³
          (<?= number_format($yc['chi_so_nuoc_dau']) ?> → <?= number_format($yc['chi_so_nuoc_cuoi']) ?>)
        </td>
        <td style="text-align:right;color:#dc2626;">- <?= number_format($yc['tien_nuoc']) ?> đ</td>
      </tr>
      <tr>
        <td style="text-align:center;">4</td>
        <td>Bồi thường hư hỏng / thiết bị mất</td>
        <td><?= htmlspecialchars($yc['chi_tiet_hu_hong'] ?: 'Không có') ?></td>
        <td style="text-align:right;color:#dc2626;">- <?= number_format($yc['chi_phi_hu_hong']) ?> đ</td>
      </tr>
      <tr>
        <td style="text-align:center;">5</td>
        <td>Phí dọn dẹp vệ sinh phòng</td>
        <td>Theo quy định trả phòng</td>
        <td style="text-align:right;color:#dc2626;">- <?= number_format($yc['phi_ve_sinh']) ?> đ</td>
      </tr>
      <?php if ($yc['chi_phi_khac'] > 0): ?>
      <tr>
        <td style="text-align:center;">6</td>
        <td>Chi phí phát sinh khác</td>
        <td><?= htmlspecialchars($yc['ghi_chu_khac'] ?: 'Khác') ?></td>
        <td style="text-align:right;color:#dc2626;">- <?= number_format($yc['chi_phi_khac']) ?> đ</td>
      </tr>
      <?php endif; ?>
      <tr style="background:#fef3c7;font-weight:bold;font-size:14pt;">
        <td colspan="3" style="text-align:right;text-transform:uppercase;">TỔNG SỐ TIỀN THỰC TẾ HOÀN TRẢ KHÁCH:</td>
        <td style="text-align:right;color:#16a34a;"><?= number_format($yc['tien_hoan_lai']) ?> đ</td>
      </tr>
    </tbody>
  </table>

  <?php if (!empty($yc['so_tai_khoan'])): ?>
  <p style="margin: 8px 0;">
    <strong>3. Hình thức chuyển khoản hoàn tiền:</strong><br>
    - Ngân hàng thụ hưởng: <strong><?= htmlspecialchars($yc['ngan_hang']) ?></strong><br>
    - Số tài khoản: <strong><?= htmlspecialchars($yc['so_tai_khoan']) ?></strong><br>
    - Chủ tài khoản: <strong><?= htmlspecialchars($yc['ten_chu_tk']) ?></strong>
  </p>
  <?php endif; ?>

  <p>Hai bên xác nhận đã bàn giao lại đầy đủ chìa khóa phòng, kiểm tra thiết bị nguyên vẹn và thống nhất thanh lý hợp đồng. Kể từ thời điểm ký biên bản này, hợp đồng thuê phòng chính thức chấm dứt hiệu lực.</p>

  <table class="sig-table">
    <tr>
      <td>
        <strong>ĐẠI DIỆN BÊN CHO THUÊ</strong><br>
        <i>(Ký và ghi rõ họ tên)</i>
        <div style="height: 75px;"></div>
        <strong><?= htmlspecialchars($yc['admin_thanh_ly_ten'] ?? 'Quản lý phòng trọ') ?></strong>
      </td>
      <td>
        <strong>NGƯỜI THUÊ PHÒNG</strong><br>
        <i>(Ký và ghi rõ họ tên)</i>
        <div style="height: 75px;"></div>
        <strong><?= htmlspecialchars($yc['ten_nguoi_thue']) ?></strong>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
