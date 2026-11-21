<?php
$title   = 'Bảo trì & Sửa chữa';
require_once 'frontend/views/Layouts/header.php';
$isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']);

$danhMucMap = [
  'dien'       => ['⚡', 'Điện & Đèn', 'p-amber'],
  'nuoc'       => ['💧', 'Nước & Vệ sinh', 'p-blue'],
  'thiet_bi'   => ['❄️', 'Điện lạnh & Thiết bị', 'p-purple'],
  'noi_that'   => ['🚪', 'Nội thất & Cửa', 'p-orange'],
  'tuong_tran' => ['🧱', 'Tường & Trần', 'p-green'],
  'khac'       => ['🔧', 'Khác', 'p-gray'],
];

$mucDoMap = [
  'nhe'        => ['🟢', 'Nhẹ',        'p-green'],
  'trung_binh' => ['🟡', 'Trung bình', 'p-amber'],
  'khan_cap'   => ['🔴', 'Khẩn cấp',  'p-red'],
];

$ttMap = [
  'cho_xu_ly'  => ['⏳', 'Chờ xử lý',  'p-amber'],
  'dang_xu_ly' => ['🔧', 'Đang xử lý', 'p-blue'],
  'da_xong'    => ['✅', 'Đã xong',    'p-green'],
];
?>

<div class="page-header">
  <div class="page-title">
    <h1>🔧 Bảo trì & Sửa chữa</h1>
    <p>
      <?php if($isAdmin): ?>
        <?= count($suaChuaList ?? []) ?> yêu cầu
        <?php if(($soChoXuLy ?? 0) > 0): ?>
          · <strong style="color:var(--amber);"><?= $soChoXuLy ?> chờ xử lý</strong>
        <?php endif; ?>
      <?php else: ?>
        Yêu cầu sửa chữa và bảo dưỡng phòng của bạn
      <?php endif; ?>
    </p>
  </div>
  <?php if(!$isAdmin): ?>
  <div class="header-actions">
    <button onclick="document.getElementById('formSuaChua').scrollIntoView({behavior:'smooth'})"
            class="btn btn-primary">＋ Gửi yêu cầu mới</button>
  </div>
  <?php endif; ?>
</div>

<?php if(!empty($_GET['msg'])): ?>
<div class="msg-alert msg-success" style="margin-bottom:14px;">
  <?php
    if ($_GET['msg'] === 'sent') echo '✓ Đã gửi yêu cầu sửa chữa! Quản lý sẽ liên hệ kiểm tra.';
    elseif ($_GET['msg'] === 'updated') echo '✓ Cập nhật tiến độ sửa chữa thành công.';
    elseif ($_GET['msg'] === 'rated') echo '✓ Cảm ơn bạn đã đánh giá dịch vụ sửa chữa!';
    else echo '✓ Thao tác thành công.';
  ?>
</div>
<?php endif; ?>

<?php if($isAdmin): ?>
<!-- ═══ ADMIN: Danh sách tất cả yêu cầu ═══ -->

<!-- Stat nhanh -->
<?php
  $sc_all   = count($suaChuaList ?? []);
  $sc_cho   = count(array_filter($suaChuaList??[], fn($x)=>$x['trang_thai']==='cho_xu_ly'));
  $sc_dang  = count(array_filter($suaChuaList??[], fn($x)=>$x['trang_thai']==='dang_xu_ly'));
  $sc_xong  = count(array_filter($suaChuaList??[], fn($x)=>$x['trang_thai']==='da_xong'));
?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;">
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(79,142,247,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">🔧</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$sc_all?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Tổng yêu cầu</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(247,169,79,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">⏳</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$sc_cho?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Chờ xử lý</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(79,142,247,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">⚙️</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$sc_dang?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Đang xử lý</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(34,201,147,.12);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">✅</div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--text);"><?=$sc_xong?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;">Đã hoàn thành</div>
    </div>
  </div>
  <div class="card" style="padding:16px;display:flex;align-items:center;gap:12px;border:1px solid rgba(251,191,36,.3);">
    <div style="width:40px;height:40px;border-radius:11px;background:rgba(251,191,36,.15);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;">⭐</div>
    <div>
      <div style="font-size:20px;font-weight:800;color:#f59e0b;"><?= ($diemTrungBinh ?? 0) > 0 ? $diemTrungBinh . ' / 5.0' : 'Chưa có' ?></div>
      <div style="font-size:11px;color:var(--text3);font-weight:600;"><?= $tongDanhGia ?? 0 ?> lượt đánh giá sao</div>
    </div>
  </div>
</div>

<!-- Bộ lọc -->
<?php $filterTT = $_GET['filter'] ?? 'all'; ?>
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach(['all'=>'Tất cả','cho_xu_ly'=>'⏳ Chờ xử lý','dang_xu_ly'=>'🔧 Đang xử lý','da_xong'=>'✅ Đã xong'] as $val=>$lbl): ?>
  <a href="?controller=thongbao&action=index&filter=<?=$val?>"
     class="btn <?= $filterTT===$val ? 'btn-primary' : 'btn-outline' ?>"
     style="font-size:12px;padding:6px 14px;"><?=$lbl?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php
    $filtered = array_filter($suaChuaList??[], fn($x) => $filterTT==='all' || $x['trang_thai']===$filterTT);
  ?>
  <?php if(empty($filtered)): ?>
  <div style="text-align:center;padding:40px;color:var(--text3);">
    <div style="font-size:36px;margin-bottom:10px;">✅</div>
    Không có yêu cầu nào.
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;">
    <?php foreach($filtered as $sc):
      $md = $mucDoMap[$sc['muc_do']] ?? $mucDoMap['trung_binh'];
      $tt = $ttMap[$sc['trang_thai']] ?? $ttMap['cho_xu_ly'];
      $dmKey = $sc['danh_muc'] ?? 'khac';
      $dm = $danhMucMap[$dmKey] ?? $danhMucMap['khac'];
    ?>
    <div style="padding:18px 20px;border-bottom:1px solid var(--border);
                <?= $sc['trang_thai']==='cho_xu_ly' ? 'border-left:4px solid var(--amber);' : ($sc['trang_thai']==='da_xong' ? 'border-left:4px solid var(--green);' : 'border-left:4px solid var(--accent);') ?>">
      <div style="display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap;">
        <div style="flex:1;min-width:260px;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
            <span class="pill <?=$tt[2]?>"><?=$tt[0].' '.$tt[1]?></span>
            <span class="pill <?=$md[2]?>"><?=$md[0].' '.$md[1]?></span>
            <span class="pill <?=$dm[2]?>"><?=$dm[0].' '.$dm[1]?></span>
            <?php if(!empty($sc['phong'])): ?>
            <span class="pill p-blue" style="font-weight:700;">🚪 Phòng <?= htmlspecialchars($sc['phong']) ?></span>
            <?php endif; ?>
          </div>

          <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:5px;">
            📍 <?= htmlspecialchars($sc['vi_tri']) ?>
          </div>

          <div style="font-size:13px;color:var(--text2);line-height:1.6;margin-bottom:10px;background:var(--bg3);padding:10px 14px;border-radius:8px;">
            <?= nl2br(htmlspecialchars($sc['mo_ta'])) ?>
          </div>

          <!-- AI Phân tích -->
          <?php if(!empty($sc['ai_phan_loai'])): ?>
          <div style="font-size:12px;padding:8px 12px;background:rgba(124,92,252,.08);border:1px solid rgba(124,92,252,.2);border-radius:8px;color:var(--text2);margin-bottom:10px;">
            🤖 <strong>AI phân loại:</strong> <?= htmlspecialchars($sc['ai_phan_loai']) ?>
          </div>
          <?php endif; ?>

          <!-- Khối so sánh Ảnh trước / Ảnh sau -->
          <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:10px;">
            <?php if(!empty($sc['anh_truoc']) && file_exists($sc['anh_truoc'])): ?>
            <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg3);width:140px;">
              <div style="font-size:10px;font-weight:700;padding:4px 6px;background:rgba(239,68,68,.12);color:var(--red);text-align:center;">📷 Ảnh trước sửa</div>
              <img src="<?= htmlspecialchars($sc['anh_truoc']) ?>"
                   alt="Ảnh trước"
                   onclick="zoomImage(this.src)"
                   style="width:140px;height:95px;object-fit:cover;cursor:pointer;display:block;"/>
            </div>
            <?php else: ?>
            <div style="border:1px dashed var(--border);border-radius:8px;padding:12px;width:140px;text-align:center;font-size:11px;color:var(--text3);display:flex;align-items:center;justify-content:center;">
              Chưa có ảnh trước
            </div>
            <?php endif; ?>

            <?php if(!empty($sc['anh_sau']) && file_exists($sc['anh_sau'])): ?>
            <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg3);width:140px;">
              <div style="font-size:10px;font-weight:700;padding:4px 6px;background:rgba(34,197,94,.12);color:var(--green);text-align:center;">📸 Ảnh sau sửa</div>
              <img src="<?= htmlspecialchars($sc['anh_sau']) ?>"
                   alt="Ảnh sau"
                   onclick="zoomImage(this.src)"
                   style="width:140px;height:95px;object-fit:cover;cursor:pointer;display:block;"/>
            </div>
            <?php else: ?>
            <div style="border:1px dashed var(--border);border-radius:8px;padding:12px;width:140px;text-align:center;font-size:11px;color:var(--text3);display:flex;align-items:center;justify-content:center;">
              <?= $sc['trang_thai']==='da_xong' ? 'Chưa chụp ảnh sau' : 'Chờ hoàn tất...' ?>
            </div>
            <?php endif; ?>
          </div>

          <!-- Đánh giá sao của người thuê -->
          <?php if(!empty($sc['danh_gia_sao'])): ?>
          <div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.25);border-radius:8px;padding:10px 14px;margin-bottom:8px;">
            <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:#d97706;">
              <span>⭐ Đánh giá của khách:</span>
              <span style="font-size:15px;letter-spacing:2px;color:#f59e0b;">
                <?= str_repeat('★', (int)$sc['danh_gia_sao']) ?><?= str_repeat('☆', 5 - (int)$sc['danh_gia_sao']) ?>
              </span>
              <span>(<?= $sc['danh_gia_sao'] ?>/5 sao)</span>
            </div>
            <?php if(!empty($sc['danh_gia_nhan_xet'])): ?>
            <div style="font-size:12px;color:var(--text2);margin-top:4px;font-style:italic;">
              "<?= htmlspecialchars($sc['danh_gia_nhan_xet']) ?>"
            </div>
            <?php endif; ?>
            <div style="font-size:10px;color:var(--text3);margin-top:4px;">
              Đánh giá lúc <?= date('d/m/Y H:i', strtotime($sc['danh_gia_luc'] ?? $sc['created_at'])) ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if(!empty($sc['ghi_chu_ql'])): ?>
          <div style="font-size:12px;padding:8px 12px;background:rgba(79,142,247,.06);border:1px solid rgba(79,142,247,.15);border-radius:8px;color:var(--text2);">
            💬 <strong>Phản hồi quản lý:</strong> <?= htmlspecialchars($sc['ghi_chu_ql']) ?>
          </div>
          <?php endif; ?>

          <div style="font-size:11px;color:var(--text3);margin-top:8px;">
            👤 Người gửi: <strong><?= htmlspecialchars($sc['ho_ten']) ?></strong>
            · 🕐 Gửi lúc <?= date('d/m/Y H:i', strtotime($sc['created_at'])) ?>
          </div>
        </div>

        <!-- Cập nhật trạng thái + tải ảnh sau -->
        <div style="min-width:220px;max-width:280px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:14px;flex-shrink:0;">
          <form method="POST" action="index.php?controller=thongbao&action=capNhatSuaChua" enctype="multipart/form-data"
                style="display:flex;flex-direction:column;gap:10px;">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $sc['id'] ?>"/>

            <label style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;">Trạng thái xử lý</label>
            <select name="trang_thai" class="form-control" style="font-size:12px;padding:7px 10px;">
              <?php foreach($ttMap as $val=>$info): ?>
              <option value="<?=$val?>" <?= $sc['trang_thai']===$val?'selected':'' ?>>
                <?= $info[0].' '.$info[1] ?>
              </option>
              <?php endforeach; ?>
            </select>

            <label style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;">Ảnh sau sửa (Nghiệm thu)</label>
            <input type="file" name="anh_sau" class="form-control" accept="image/*" style="font-size:11px;padding:6px;"/>

            <label style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;">Ghi chú phản hồi</label>
            <textarea class="form-control" name="ghi_chu_ql" rows="2"
                      placeholder="Ghi chú chi phí hoặc hướng dẫn..."
                      style="font-size:12px;padding:6px 10px;"><?= htmlspecialchars($sc['ghi_chu_ql']??'') ?></textarea>

            <button type="submit" class="btn btn-primary" style="font-size:12px;padding:8px;justify-content:center;">
              💾 Cập nhật tiến độ
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- ═══ USER: Form gửi + Lịch sử yêu cầu của mình ═══ -->

<div style="display:grid;grid-template-columns:minmax(320px,460px) 1fr;gap:20px;align-items:start;">

  <!-- Form gửi yêu cầu -->
  <div class="card" id="formSuaChua">
    <div class="card-header">
      <div class="card-title">📤 Gửi yêu cầu sửa chữa</div>
      <?php if(!empty($soPhongUser)): ?>
      <span class="pill p-blue" style="font-weight:700;">Phòng <?= htmlspecialchars($soPhongUser) ?></span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <form method="POST" action="index.php?controller=thongbao&action=guiSuaChua" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="ai_phan_loai" id="inputAiPhanLoai" value=""/>

        <div class="form-group">
          <label class="form-label">Vị trí sự cố <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="vi_tri" id="inputViTri"
                 placeholder="vd: Bồn cầu phòng tắm, Ổ cắm máy giặt, Máy lạnh..." required/>
        </div>

        <div class="form-group">
          <label class="form-label">Danh mục sự cố</label>
          <select class="form-control" name="danh_muc" id="selectDanhMuc">
            <option value="khac">🔧 Khác / Chưa rõ</option>
            <option value="dien">⚡ Hệ thống Điện & Đèn</option>
            <option value="nuoc">💧 Hệ thống Nước & Vệ sinh</option>
            <option value="thiet_bi">❄️ Thiết bị Gia dụng & Điện lạnh</option>
            <option value="noi_that">🚪 Nội thất & Khóa cửa</option>
            <option value="tuong_tran">🧱 Tường & Trần nhà</option>
          </select>
        </div>

        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <label class="form-label" style="margin-bottom:0;">Mô tả chi tiết <span style="color:var(--red)">*</span></label>
            <button type="button" class="btn btn-outline" id="btnAiPhanLoai"
                    style="font-size:11px;padding:3px 10px;display:flex;align-items:center;gap:5px;border-color:var(--accent);color:var(--accent);">
              🤖 AI Phân tích & Gợi ý an toàn
            </button>
          </div>
          <textarea class="form-control" name="mo_ta" id="inputMoTa" rows="3"
                    placeholder="Mô tả cụ thể hiện trạng hư hỏng..." required></textarea>
        </div>

        <!-- Hộp hiển thị kết quả AI -->
        <div id="aiResultBox" style="display:none;margin-bottom:16px;padding:12px 14px;border-radius:10px;background:rgba(124,92,252,.08);border:1px solid rgba(124,92,252,.3);">
          <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--accent);margin-bottom:6px;">
            <span>🤖 AI Tư vấn:</span>
            <span id="aiTagCategory" class="pill p-purple" style="font-size:10px;"></span>
            <span id="aiTagUrgency" class="pill p-red" style="font-size:10px;"></span>
          </div>
          <div id="aiAdviceText" style="font-size:12px;color:var(--text2);line-height:1.5;"></div>
        </div>

        <!-- Ảnh trước khi sửa -->
        <div class="form-group">
          <label class="form-label">📷 Chụp / Tải ảnh hiện trạng hư hỏng (Ảnh trước khi sửa)</label>
          <input type="file" name="anh_truoc" id="inputAnhTruoc" class="form-control" accept="image/*" onchange="previewAnhTruoc(this)"/>
          <div id="previewBox" style="display:none;margin-top:10px;">
            <img id="imgPreview" src="" alt="Xem trước" style="max-height:140px;border-radius:8px;border:1px solid var(--border);"/>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:18px;">
          <label class="form-label">Mức độ khẩn cấp</label>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
            <?php foreach([
              'nhe'        => ['🟢','Nhẹ',       'Có thể đợi'],
              'trung_binh' => ['🟡','Trung bình','Cần sửa sớm'],
              'khan_cap'   => ['🔴','Khẩn cấp', 'Ảnh hưởng sinh hoạt'],
            ] as $val=>$info): ?>
            <label class="muc-do-label <?= $val==='trung_binh'?'active':'' ?>"
                   id="lbl_muc_do_<?=$val?>"
                   style="border:1.5px solid var(--border);border-radius:10px;padding:10px;cursor:pointer;transition:all .15s;text-align:center;"
                   onclick="selectMucDo(this)">
              <input type="radio" name="muc_do" value="<?=$val?>"
                     <?= $val==='trung_binh'?'checked':'' ?> style="display:none;"/>
              <div style="font-size:22px;margin-bottom:4px;"><?=$info[0]?></div>
              <div style="font-size:13px;font-weight:700;color:var(--text);"><?=$info[1]?></div>
              <div style="font-size:11px;color:var(--text3);"><?=$info[2]?></div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;font-size:14px;font-weight:700;">
          📤 Gửi yêu cầu sửa chữa
        </button>
      </form>
    </div>
  </div>

  <!-- Lịch sử yêu cầu của user -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📋 Lịch sử yêu cầu của bạn</div>
      <span style="font-size:12px;color:var(--text3);"><?= count($suaChuaList??[]) ?> yêu cầu</span>
    </div>
    <?php if(empty($suaChuaList)): ?>
    <div style="text-align:center;padding:40px;color:var(--text3);">
      <div style="font-size:36px;margin-bottom:10px;">📭</div>
      Bạn chưa có yêu cầu sửa chữa nào.
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;">
      <?php foreach($suaChuaList as $sc):
        $md = $mucDoMap[$sc['muc_do']] ?? $mucDoMap['trung_binh'];
        $tt = $ttMap[$sc['trang_thai']] ?? $ttMap['cho_xu_ly'];
        $dmKey = $sc['danh_muc'] ?? 'khac';
        $dm = $danhMucMap[$dmKey] ?? $danhMucMap['khac'];
      ?>
      <div style="padding:16px 20px;border-bottom:1px solid var(--border);
                  <?= $sc['trang_thai']==='da_xong' ? 'border-left:4px solid var(--green);' : 'border-left:4px solid var(--amber);' ?>">
        <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
          <div style="width:42px;height:42px;border-radius:11px;
                      background:<?= $sc['muc_do']==='khan_cap'?'rgba(247,92,92,.12)':($sc['muc_do']==='trung_binh'?'rgba(247,169,79,.12)':'rgba(34,201,147,.12)') ?>;
                      display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <?= $md[0] ?>
          </div>
          <div style="flex:1;min-width:200px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
              <span class="pill <?=$tt[2]?>"><?=$tt[0].' '.$tt[1]?></span>
              <span class="pill <?=$md[2]?>"><?=$md[1]?></span>
              <span class="pill <?=$dm[2]?>"><?=$dm[0].' '.$dm[1]?></span>
            </div>

            <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px;">
              📍 <?= htmlspecialchars($sc['vi_tri']) ?>
            </div>

            <div style="font-size:13px;color:var(--text2);margin-bottom:8px;line-height:1.5;">
              <?= nl2br(htmlspecialchars($sc['mo_ta'])) ?>
            </div>

            <!-- Khối hiển thị ảnh trước / sau -->
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
              <?php if(!empty($sc['anh_truoc']) && file_exists($sc['anh_truoc'])): ?>
              <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;width:120px;">
                <div style="font-size:9px;font-weight:700;padding:3px;background:rgba(239,68,68,.12);color:var(--red);text-align:center;">📷 Ảnh trước sửa</div>
                <img src="<?= htmlspecialchars($sc['anh_truoc']) ?>"
                     onclick="zoomImage(this.src)"
                     style="width:120px;height:80px;object-fit:cover;cursor:pointer;display:block;"/>
              </div>
              <?php endif; ?>

              <?php if(!empty($sc['anh_sau']) && file_exists($sc['anh_sau'])): ?>
              <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;width:120px;">
                <div style="font-size:9px;font-weight:700;padding:3px;background:rgba(34,197,94,.12);color:var(--green);text-align:center;">📸 Ảnh sau sửa</div>
                <img src="<?= htmlspecialchars($sc['anh_sau']) ?>"
                     onclick="zoomImage(this.src)"
                     style="width:120px;height:80px;object-fit:cover;cursor:pointer;display:block;"/>
              </div>
              <?php endif; ?>
            </div>

            <?php if(!empty($sc['ghi_chu_ql'])): ?>
            <div style="font-size:12px;padding:8px 12px;background:rgba(34,201,147,.07);border:1px solid rgba(34,201,147,.2);border-radius:8px;color:var(--text2);margin-bottom:8px;">
              💬 <strong>Quản lý:</strong> <?= htmlspecialchars($sc['ghi_chu_ql']) ?>
            </div>
            <?php endif; ?>

            <!-- Đánh giá sao khi đã sửa xong -->
            <?php if($sc['trang_thai'] === 'da_xong'): ?>
              <?php if(!empty($sc['danh_gia_sao'])): ?>
              <div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.25);border-radius:8px;padding:8px 12px;margin-top:6px;">
                <div style="font-size:12px;font-weight:700;color:#d97706;">
                  ⭐ Bạn đã đánh giá:
                  <span style="font-size:14px;letter-spacing:2px;color:#f59e0b;">
                    <?= str_repeat('★', (int)$sc['danh_gia_sao']) ?><?= str_repeat('☆', 5 - (int)$sc['danh_gia_sao']) ?>
                  </span>
                  (<?= $sc['danh_gia_sao'] ?>/5)
                </div>
                <?php if(!empty($sc['danh_gia_nhan_xet'])): ?>
                <div style="font-size:12px;color:var(--text2);margin-top:2px;font-style:italic;">
                  "<?= htmlspecialchars($sc['danh_gia_nhan_xet']) ?>"
                </div>
                <?php endif; ?>
              </div>
              <?php else: ?>
              <!-- Form chấm sao -->
              <div style="background:var(--bg3);border:1px dashed var(--accent);border-radius:10px;padding:12px 14px;margin-top:8px;">
                <div style="font-size:12px;font-weight:700;color:var(--accent);margin-bottom:6px;">
                  ⭐ Nghiệm thu & Chấm sao dịch vụ sửa chữa:
                </div>
                <form method="POST" action="index.php?controller=thongbao&action=danhGiaSuaChua" style="display:flex;flex-direction:column;gap:8px;">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="id" value="<?= $sc['id'] ?>"/>

                  <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:12px;color:var(--text2);">Chọn số sao:</span>
                    <div class="star-rating-box" id="star_box_<?= $sc['id'] ?>">
                      <?php for($i=1; $i<=5; $i++): ?>
                      <span class="star-item <?= $i<=5?'active':'' ?>"
                            onclick="setStar(<?= $sc['id'] ?>, <?= $i ?>)"
                            data-val="<?=$i?>"
                            style="font-size:22px;cursor:pointer;color:#f59e0b;user-select:none;">★</span>
                      <?php endfor; ?>
                      <input type="hidden" name="danh_gia_sao" id="star_input_<?= $sc['id'] ?>" value="5"/>
                    </div>
                  </div>

                  <input type="text" class="form-control" name="danh_gia_nhan_xet"
                         placeholder="Nhận xét thợ sửa/dịch vụ (ví dụ: Nhanh gọn, nhiệt tình...)"
                         style="font-size:12px;padding:6px 10px;"/>

                  <button type="submit" class="btn btn-primary" style="font-size:12px;padding:6px 14px;align-self:flex-start;">
                    ⭐ Gửi đánh giá
                  </button>
                </form>
              </div>
              <?php endif; ?>
            <?php endif; ?>

            <div style="font-size:11px;color:var(--text3);margin-top:8px;">
              🕐 <?= date('d/m/Y H:i', strtotime($sc['created_at'])) ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>
<?php endif; ?>

<!-- Lightbox Modal phóng to ảnh -->
<div id="imageModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:99999;align-items:center;justify-content:center;" onclick="closeZoom()">
  <span style="position:absolute;top:20px;right:25px;font-size:32px;color:#fff;cursor:pointer;">&times;</span>
  <img id="modalImg" src="" alt="Zoom" style="max-width:90%;max-height:90%;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.5);"/>
</div>

<script>
function selectMucDo(el) {
  document.querySelectorAll('.muc-do-label').forEach(l => {
    l.classList.remove('active');
    l.style.borderColor = 'var(--border)';
  });
  el.classList.add('active');
  el.style.borderColor = 'var(--accent)';
  el.querySelector('input[type=radio]').checked = true;
}

function previewAnhTruoc(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imgPreview').src = e.target.result;
      document.getElementById('previewBox').style.display = 'block';
    }
    reader.readAsDataURL(input.files[0]);
  }
}

function zoomImage(src) {
  const modal = document.getElementById('imageModal');
  const img = document.getElementById('modalImg');
  img.src = src;
  modal.style.display = 'flex';
}

function closeZoom() {
  document.getElementById('imageModal').style.display = 'none';
}

function setStar(id, val) {
  const input = document.getElementById('star_input_' + id);
  if (!input) return;
  input.value = val;
  const box = document.getElementById('star_box_' + id);
  const stars = box.querySelectorAll('.star-item');
  stars.forEach((s, idx) => {
    if (idx < val) {
      s.innerText = '★';
      s.style.color = '#f59e0b';
    } else {
      s.innerText = '☆';
      s.style.color = '#d1d5db';
    }
  });
}

// AI Phân loại AJAX
document.addEventListener('DOMContentLoaded', () => {
  const btnAi = document.getElementById('btnAiPhanLoai');
  if (btnAi) {
    btnAi.addEventListener('click', async () => {
      const viTri = document.getElementById('inputViTri')?.value.trim() || '';
      const moTa  = document.getElementById('inputMoTa')?.value.trim() || '';

      if (!viTri && !moTa) {
        alert('Vui lòng nhập vị trí hoặc mô tả trước để AI có thể phân loại.');
        return;
      }

      btnAi.disabled = true;
      btnAi.innerHTML = '⏳ Đang phân tích...';

      try {
        const formData = new FormData();
        formData.append('vi_tri', viTri);
        formData.append('mo_ta', moTa);

        const res = await fetch('index.php?controller=thongbao&action=aiPhanLoai', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          // Tự động chọn danh mục
          const sel = document.getElementById('selectDanhMuc');
          if (sel && data.danh_muc) {
            sel.value = data.danh_muc;
          }

          // Tự động chọn mức độ
          const radio = document.querySelector(`input[name="muc_do"][value="${data.muc_do}"]`);
          if (radio) {
            radio.checked = true;
            const lbl = document.getElementById(`lbl_muc_do_${data.muc_do}`);
            if (lbl) selectMucDo(lbl);
          }

          // Hiển thị hộp tư vấn AI
          const box = document.getElementById('aiResultBox');
          const tagCat = document.getElementById('aiTagCategory');
          const tagUrg = document.getElementById('aiTagUrgency');
          const advice = document.getElementById('aiAdviceText');
          const hiddenInput = document.getElementById('inputAiPhanLoai');

          tagCat.textContent = data.danh_muc_ten;
          tagUrg.textContent = data.muc_do_ten;
          advice.textContent = data.loi_khuyen;
          if (hiddenInput) hiddenInput.value = data.phan_tich;

          box.style.display = 'block';
        }
      } catch (err) {
        console.error(err);
      } finally {
        btnAi.disabled = false;
        btnAi.innerHTML = '🤖 AI Phân tích & Gợi ý an toàn';
      }
    });
  }
});
</script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>
