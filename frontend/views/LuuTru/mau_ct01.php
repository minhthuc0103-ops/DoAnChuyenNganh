<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mẫu CT01 - Tờ khai cư trú - <?= htmlspecialchars($item['ho_ten']) ?></title>
  <style>
    body {
      font-family: "Times New Roman", Times, serif;
      font-size: 14pt;
      line-height: 1.45;
      color: #000;
      background: #f3f4f6;
      margin: 0;
      padding: 20px;
    }
    .page-a4 {
      width: 210mm;
      min-height: 297mm;
      padding: 20mm 20mm 20mm 25mm;
      margin: 0 auto;
      background: #fff;
      box-shadow: 0 4px 15px rgba(0,0,0,.15);
      box-sizing: border-box;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    .header-table td {
      vertical-align: top;
      text-align: center;
    }
    .header-left {
      width: 45%;
      font-size: 11pt;
      text-align: left !important;
    }
    .header-right {
      width: 55%;
    }
    .quoc-hieu {
      font-weight: bold;
      font-size: 12pt;
    }
    .tieu-ngu {
      font-weight: bold;
      font-size: 13pt;
      border-bottom: 1px solid #000;
      display: inline-block;
      padding-bottom: 2px;
    }
    .doc-title {
      text-align: center;
      margin: 20px 0 10px 0;
    }
    .doc-title h1 {
      font-size: 16pt;
      font-weight: bold;
      margin: 0 0 5px 0;
      text-transform: uppercase;
    }
    .doc-sub {
      font-size: 12pt;
      font-style: italic;
    }
    .kinh-gui {
      text-align: center;
      font-weight: bold;
      font-size: 13pt;
      margin: 10px 0 20px 0;
    }
    .field-row {
      margin-bottom: 8px;
    }
    .field-label {
      font-weight: normal;
    }
    .field-val {
      font-weight: bold;
    }
    .dotted-line {
      border-bottom: 1px dotted #555;
      display: inline-block;
      min-width: 150px;
      padding: 0 5px;
    }
    .sign-table {
      width: 100%;
      margin-top: 25px;
      border-collapse: collapse;
    }
    .sign-table td {
      vertical-align: top;
      text-align: center;
      width: 50%;
    }
    .sign-title {
      font-weight: bold;
      font-size: 13pt;
    }
    .sign-sub {
      font-size: 11pt;
      font-style: italic;
    }
    .sign-space {
      height: 80px;
    }
    .no-print {
      max-width: 210mm;
      margin: 0 auto 15px auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-action {
      background: #2563eb;
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-back {
      background: #4b5563;
    }
    @media print {
      body {
        background: #fff;
        padding: 0;
      }
      .page-a4 {
        box-shadow: none;
        padding: 15mm 15mm 15mm 20mm;
        margin: 0;
        width: 100%;
        min-height: auto;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<div class="no-print">
  <a href="index.php?controller=luutru&action=index" class="btn-action btn-back">← Quay lại danh sách</a>
  <div>
    <button onclick="window.print()" class="btn-action">🖨 In Mẫu CT01 (A4 / PDF)</button>
  </div>
</div>

<div class="page-a4">
  <table class="header-table">
    <tr>
      <td class="header-left">
        <strong>Mẫu CT01</strong><br>
        <span style="font-size:10pt;">Ban hành kèm theo Thông tư số 56/2021/TT-BCA ngày 15/5/2021 của Bộ Công an</span>
      </td>
      <td class="header-right">
        <div class="quoc-hieu">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
        <div class="tieu-ngu">Độc lập - Tự do - Hạnh phúc</div>
      </td>
    </tr>
  </table>

  <div class="doc-title">
    <h1>TỜ KHAI THAY ĐỔI THÔNG TIN CƯ TRÚ</h1>
    <div class="doc-sub">(Dùng cho việc đăng ký thường trú, tạm trú, khai báo lưu trú)</div>
  </div>

  <div class="kinh-gui">
    Kính gửi: Công an <?= htmlspecialchars($item['ten_khu'] ? 'phường/xã quản lý ' . $item['ten_khu'] : 'phường/xã sở tại') ?>
  </div>

  <div class="field-row">
    1. Họ, chữ đệm và tên: <span class="field-val" style="text-transform:uppercase;"><?= htmlspecialchars($item['ho_ten']) ?></span>
  </div>

  <div class="field-row" style="display:flex;">
    <div style="flex:1;">
      2. Ngày, tháng, năm sinh: <span class="field-val"><?= $item['ngay_sinh'] ? date('d/m/Y', strtotime($item['ngay_sinh'])) : '................................' ?></span>
    </div>
    <div style="width:200px;">
      3. Giới tính: <span class="field-val"><?= $item['gioi_tinh']==='nu'?'Nữ':'Nam' ?></span>
    </div>
  </div>

  <div class="field-row" style="display:flex;">
    <div style="flex:1;">
      4. Số định danh cá nhân / CCCD: <span class="field-val"><?= htmlspecialchars($item['cccd']) ?></span>
    </div>
    <div style="flex:1;">
      5. Số điện thoại: <span class="field-val"><?= htmlspecialchars($item['sdt'] ?: '................................') ?></span>
    </div>
  </div>

  <div class="field-row">
    6. Ngày cấp CCCD: <span class="field-val"><?= $item['ngay_cap_cccd'] ? date('d/m/Y', strtotime($item['ngay_cap_cccd'])) : '........................' ?></span>;
    Nơi cấp: <span class="field-val"><?= htmlspecialchars($item['noi_cap_cccd'] ?: 'Cục Cảnh sát QLHC về TTXH') ?></span>
  </div>

  <div class="field-row">
    7. Nghề nghiệp, nơi làm việc: <span class="field-val"><?= htmlspecialchars($item['nghe_nghiep'] ?: 'Lao động / Tự do / Học sinh sinh viên') ?></span>
  </div>

  <div class="field-row">
    8. Nơi thường trú (Quê quán): <span class="field-val"><?= htmlspecialchars($item['thuong_tru'] ?: '....................................................................................................................') ?></span>
  </div>

  <div class="field-row">
    9. Nơi ở hiện tại: <span class="field-val">Phòng <?= htmlspecialchars($item['so_phong']) ?>, <?= htmlspecialchars($item['ten_khu']??'') ?> <?= htmlspecialchars($item['dia_chi_khu'] ? '— ' . $item['dia_chi_khu'] : '') ?></span>
  </div>

  <div class="field-row">
    10. Họ và tên chủ cơ sở lưu trú (Chủ nhà trọ): <span class="field-val"><?= htmlspecialchars($owner['ho_ten'] ?? 'Quản trị viên') ?></span> (SĐT: <?= htmlspecialchars($owner['sdt'] ?? '') ?>)
  </div>

  <div class="field-row">
    11. Nội dung đề nghị: <br>
    <div style="padding-left:15px;margin-top:4px;">
      <span class="field-val">Đăng ký tạm trú / Khai báo lưu trú theo quy định</span> tại Phòng <?= htmlspecialchars($item['so_phong']) ?>, <?= htmlspecialchars($item['ten_khu']??'') ?>.<br>
      - Thời gian lưu trú: Từ ngày <span class="field-val"><?= date('d/m/Y', strtotime($item['ngay_den'])) ?></span> đến ngày <span class="field-val"><?= $item['ngay_di'] ? date('d/m/Y', strtotime($item['ngay_di'])) : 'dài hạn theo hợp đồng thuê phòng' ?></span>.<br>
      - Lý do: <span class="field-val"><?= htmlspecialchars($item['ly_do'] ?: 'Thuê trọ sinh sống và làm việc') ?></span>.<br>
      <?php if(!empty($item['ma_dvc'])): ?>
      - Mã hồ sơ Cổng DVC: <span class="field-val"><?= htmlspecialchars($item['ma_dvc']) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="field-row" style="margin-top:10px;">
    Tôi cam đoan những lời khai trên đây là đúng sự thật và hoàn toàn chịu trách nhiệm trước pháp luật về lời khai của mình.
  </div>

  <table class="sign-table">
    <tr>
      <td>
        <div class="sign-title">Ý KIẾN CỦA CHỦ CƠ SỞ LƯU TRÚ</div>
        <div class="sign-sub">(Đồng ý cho đăng ký tạm trú / lưu trú)</div>
        <div class="sign-space"></div>
        <div class="field-val"><?= htmlspecialchars($owner['ho_ten'] ?? 'Chủ nhà trọ') ?></div>
      </td>
      <td>
        <div style="font-size:12pt;font-style:italic;margin-bottom:4px;">
          Ngày ..... tháng ..... năm 202...
        </div>
        <div class="sign-title">NGƯỜI KÊ KHAI</div>
        <div class="sign-sub">(Ký và ghi rõ họ tên)</div>
        <div class="sign-space"></div>
        <div class="field-val"><?= htmlspecialchars($item['ho_ten']) ?></div>
      </td>
    </tr>
  </table>
</div>

</body>
</html>

