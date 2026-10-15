<?php
$title = 'Tạo hợp đồng mới';
require_once 'frontend/views/Layouts/header.php';
?>
<link rel="stylesheet" href="frontend/assets/css/hopdong-create.css">

<div class="page-header">
  <div class="page-title">
    <h1>Tạo hợp đồng mới</h1>
    <p>Chọn phòng → Thời hạn → Người làm HĐ → Người ở cùng → Ký tên → Lưu</p>
  </div>
  <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">← Quay lại</a>
</div>

<div style="max-width:720px;">

  <!-- BƯỚC HƯỚNG DẪN -->
  <div style="display:flex;gap:8px;margin-bottom:20px;">
    <?php
    $steps=[['1','Phòng & Chi phí','79,142,247'],['2','Người làm HĐ','247,169,79'],
            ['3','Người ở cùng','247,169,79'],['4','Nội dung HĐ','124,92,252'],['5','Ký tên','124,92,252']];
    foreach($steps as $s): ?>
    <div style="flex:1;padding:10px 12px;border-radius:10px;
                background:rgba(<?=$s[2]?>,.08);border:1px solid rgba(<?=$s[2]?>,.2);">
      <div style="font-size:11px;font-weight:800;color:rgb(<?=$s[2]?>);margin-bottom:2px;">Bước <?=$s[0]?></div>
      <div style="font-size:12px;color:var(--text2);"><?=$s[1]?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if(!empty($error)): ?>
  <div class="msg-alert msg-error" style="margin-bottom:16px;"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>

  <?php if(empty($phongs)): ?>
  <div class="card" style="text-align:center;padding:40px;">
    <div style="font-size:36px;margin-bottom:10px">🏠</div>
    <div style="font-size:15px;font-weight:700;color:var(--text2);margin-bottom:6px">Không có phòng trống!</div>
    <div style="font-size:13px;color:var(--text3);margin-bottom:16px">Cần có phòng trống mới tạo được hợp đồng</div>
    <a href="index.php?controller=phong&action=index" class="btn btn-primary">🏠 Xem danh sách phòng</a>
  </div>
  <?php else: ?>

  <form method="POST" action="index.php?controller=hopdong&action=create" id="hdForm">
            <?= Csrf::field() ?>

    <!-- BƯỚC 1 — PHÒNG + THỜI HẠN + PHÍ -->
    <div class="card" style="margin-bottom:14px;">
      <div class="card-header"><div class="card-title">🏠 Bước 1 — Phòng & Hợp đồng</div></div>
      <div class="card-body">

        <!-- Chọn khu & phòng -->
        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Khu trọ <span style="color:var(--red)">*</span></label>
            <select class="form-control" id="selKhu" onchange="filterPhong(this.value)">
              <option value="">— Chọn khu —</option>
              <?php foreach($khus as $k): ?>
              <option value="<?=$k['id']?>"
                      data-diachi="<?=htmlspecialchars($k['dia_chi']??'')?>">
                <?=htmlspecialchars($k['ten_khu'])?> (<?=htmlspecialchars($k['ma_khu'])?>)
              </option>
              <?php endforeach; ?>
            </select>
            <!-- Địa chỉ khu -->
            <div id="khuDiaChi" style="display:none;margin-top:6px;font-size:12px;color:var(--text3);">
              📍 <span id="khuDiaChiText"></span>
            </div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Phòng <span style="color:var(--red)">*</span></label>
            <select class="form-control" name="phong_id" id="selPhong" required onchange="updateGia(this)" disabled>
              <option value="">— Chọn khu trước —</option>
              <?php foreach($phongs as $p): ?>
              <option value="<?=$p['id']?>"
                      data-gia="<?=$p['gia']?>"
                      data-khu="<?=$p['khu_id']?>"
                      style="display:none;"
                      <?=($_POST['phong_id']??'')==$p['id']?'selected':''?>>
                <?=htmlspecialchars($p['so_phong'])?> — <?=number_format($p['gia'])?>đ/tháng
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;grid-column:1/-1;">
            <label class="form-label">Giá thuê / tháng</label>
            <div id="giaShow" style="padding:10px 14px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);font-size:14px;font-weight:700;color:var(--text3);">
              Chọn phòng để xem giá
            </div>
          </div>
        </div>

        <div style="height:1px;background:var(--border);margin:0 0 16px;"></div>

        <!-- Thời hạn & cọc -->
        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Ngày bắt đầu <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="date" name="ngay_bat_dau"
                   value="<?=$_POST['ngay_bat_dau']??date('Y-m-d')?>" required/>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Ngày kết thúc <span style="color:var(--red)">*</span></label>
            <input class="form-control" type="date" name="ngay_ket_thuc"
                   value="<?=$_POST['ngay_ket_thuc']??date('Y-m-d',strtotime('+6 months'))?>" required/>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Tiền cọc (đ)</label>
            <input class="form-control" name="tien_coc" min="0"
                   placeholder="0" value="<?=$_POST['tien_coc']??''?>"/>
          </div>
        </div>

        <div style="height:1px;background:var(--border);margin:0 0 16px;"></div>

        <!-- Phí dịch vụ -->
        <div style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:12px;">⚡ Phí dịch vụ hàng tháng</div>
        <div class="form-grid" style="margin-bottom:12px;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Điện <span style="font-size:11px;font-weight:400;color:var(--text3);">(đ / số)</span></label>
            <input class="form-control" name="gia_dien" placeholder="3.500"
                   value="<?=$_POST['gia_dien']??''?>"/>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Nước <span style="font-size:11px;font-weight:400;color:var(--text3);">(đ / người hoặc m³)</span></label>
            <input class="form-control" name="gia_nuoc" placeholder="50.000"
                   value="<?=$_POST['gia_nuoc']??''?>"/>
          </div>
        </div>

        <div style="font-size:11px;font-weight:700;color:var(--text3);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;">Phí dịch vụ cố định / tháng</div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
          <?php
          $phiList = [
            ['phi_wifi', 'WiFi / Internet', '100000'],
            ['phi_rac',  'Rác',             '20000'],
            ['phi_vs',   'Vệ sinh',         '30000'],
          ];
          foreach($phiList as [$name, $label, $default]):
            $val = $_POST[$name] ?? $default;
          ?>
          <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);">
            <span style="flex:1;font-size:13px;font-weight:600;color:var(--text2);"><?=$label?></span>
            <input class="form-control phi-input" name="<?=$name?>" placeholder="0"
                   value="<?=htmlspecialchars($val)?>"
                   oninput="tinhTongPhi()"
                   style="width:140px;text-align:right;"/>
            <span style="font-size:12px;color:var(--text3);white-space:nowrap;">đ / tháng</span>
          </div>
          <?php endforeach; ?>
          <!-- Tổng phí dịch vụ -->
          <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:rgba(79,142,247,.06);border-radius:10px;border:1px solid rgba(79,142,247,.2);">
            <span style="flex:1;font-size:13px;font-weight:700;color:var(--accent);">📊 Tổng phí dịch vụ</span>
            <span id="tongPhi" style="font-size:14px;font-weight:800;color:var(--accent);">150.000đ</span>
            <span style="font-size:12px;color:var(--text3);white-space:nowrap;">/ tháng</span>
          </div>
        </div>

        <!-- Tổng kết người & xe từ Bước 2, 3 -->
        <div style="height:1px;background:var(--border);margin:0 0 16px;"></div>
        <div style="font-size:11px;font-weight:700;color:var(--text3);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">👥 Tổng người & xe (tự động từ Bước 2, 3)</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div style="padding:14px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);text-align:center;">
            <div style="font-size:28px;font-weight:800;color:var(--accent);" id="tongNguoi">1</div>
            <div style="font-size:12px;color:var(--text3);margin-top:4px;">Người thuê</div>
            <div style="font-size:10px;color:var(--text3);margin-top:2px;">(1 người ký + người ở cùng)</div>
          </div>
          <div style="padding:14px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);text-align:center;">
            <div style="font-size:28px;font-weight:800;color:var(--green);" id="tongXe">0</div>
            <div style="font-size:12px;color:var(--text3);margin-top:4px;">Xe đăng ký</div>
            <div style="font-size:10px;color:var(--text3);margin-top:2px;">(từ người ký + người ở cùng)</div>
          </div>
        </div>

      </div>
    </div>

    <!-- BƯỚC 2 — NGƯỜI LÀM HỢP ĐỒNG -->
    <div class="card" style="margin-bottom:14px;">
      <div class="card-header">
        <div class="card-title">✍️ Bước 2 — Người làm hợp đồng</div>
        <div class="card-sub">Người đứng tên & ký kết hợp đồng</div>
      </div>
      <div class="card-body">

        <!-- Chọn từ danh sách người thuê đã đăng ký -->
        <?php if(!empty($nguoiThueList)): ?>
        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label" style="color:var(--accent);">⚡ Chọn nhanh từ danh sách người thuê</label>
          <!-- Search box -->
          <input type="text" id="ntSearch" class="form-control" placeholder="🔍 Tìm theo tên, SĐT, CCCD..."
                 oninput="filterNT(this.value)" style="margin-bottom:10px;"/>
          <!-- Card list - ẩn mặc định, hiện khi tìm -->
          <div id="ntList" style="display:none;flex-direction:column;gap:6px;max-height:220px;overflow-y:auto;padding-right:2px;">
            <?php foreach($nguoiThueList as $nt):
              $parts = explode(' ', $nt['ho_ten']);
              $init  = mb_strtoupper(mb_substr(end($parts), 0, 1, 'UTF-8'), 'UTF-8');
              $hasAv = !empty($nt['avatar']);
            ?>
            <div class="nt-card" tabindex="0"
                 data-id="<?=$nt['id']?>"
                 data-hoten="<?=htmlspecialchars($nt['ho_ten'])?>"
                 data-cccd="<?=htmlspecialchars($nt['cccd']??'')?>"
                 data-sdt="<?=htmlspecialchars($nt['sdt']??'')?>"
                 data-diachi="<?=htmlspecialchars($nt['dia_chi']??'')?>"
                 data-search="<?=strtolower(htmlspecialchars($nt['ho_ten'].' '.$nt['sdt'].' '.$nt['cccd']))?>"
                 onclick="selectNT(this)"
                 style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:var(--bg3);cursor:pointer;transition:.15s;"
                 onmouseover="this.style.borderColor='rgba(79,142,247,.4)';this.style.background='rgba(79,142,247,.06)'"
                 onmouseout="if(!this.classList.contains('selected')){this.style.borderColor='var(--border)';this.style.background='var(--bg3)'}">
              <!-- Avatar -->
              <?php if($hasAv): ?>
              <img src="<?=htmlspecialchars($nt['avatar'])?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'"/>
              <?php else: ?>
              <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#4f8ef7,#7c5cfc);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0;"><?=$init?></div>
              <?php endif; ?>
              <!-- Info -->
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:700;color:var(--text);"><?=htmlspecialchars($nt['ho_ten'])?></div>
                <div style="font-size:11px;color:var(--text3);margin-top:2px;">
                  <?=$nt['sdt'] ? '📱 '.$nt['sdt'] : ''?>
                </div>
              </div>
              <span class="nt-check" style="font-size:16px;display:none;color:var(--green);">✓</span>
            </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="nguoi_thue_id_existing" id="nguoiThueIdExisting" value=""/>
          <div id="ntSelected" style="display:none;margin-top:8px;font-size:12px;color:var(--green);font-weight:600;"></div>
          <button type="button" id="ntClearBtn" onclick="clearNT()" style="display:none;margin-top:6px;padding:4px 12px;font-size:12px;border-radius:8px;border:1px solid var(--border);background:var(--card);color:var(--text2);cursor:pointer;">✕ Bỏ chọn</button>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
          <input class="form-control" type="text" name="ho_ten" id="f_ho_ten"
                 placeholder="Nguyễn Văn A"
                 value="<?=htmlspecialchars($_POST['ho_ten']??'')?>" required/>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Số CCCD / CMND</label>
            <input class="form-control" type="text" name="cccd" id="f_cccd" placeholder="079......"
                   value="<?=htmlspecialchars($_POST['cccd']??'')?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input class="form-control" type="tel" name="sdt" id="f_sdt" placeholder="0901234567"
                   value="<?=htmlspecialchars($_POST['sdt']??'')?>"/>
          </div>
        </div>

        <!-- LIÊN KẾT TÀI KHOẢN USER -->
        <div class="form-group">
          <label class="form-label">🔗 Liên kết tài khoản user</label>
          <select class="form-control" name="account_id" id="f_account_id">
            <option value="">— Không liên kết (hoặc tự động theo SĐT) —</option>
            <?php foreach($accountList ?? [] as $acc): ?>
            <option value="<?= $acc['id'] ?>"
              <?= ($_POST['account_id'] ?? '') == $acc['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($acc['ho_ten']) ?>
              (@<?= htmlspecialchars($acc['username']) ?>)
              <?= $acc['sdt'] ? ' — ' . htmlspecialchars($acc['sdt']) : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div style="font-size:11px;color:var(--text3);margin-top:5px;">
            Chọn tài khoản để user có thể xem hợp đồng sau khi đăng nhập. Nếu không chọn, hệ thống tự khớp theo SĐT.
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Ngày sinh</label>
            <input class="form-control" type="date" name="ngay_sinh"
                   value="<?=$_POST['ngay_sinh']??''?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Giới tính</label>
            <select class="form-control" name="gioi_tinh">
              <option value="nam"  <?=($_POST['gioi_tinh']??'nam')==='nam'?'selected':''?>>Nam</option>
              <option value="nu"   <?=($_POST['gioi_tinh']??'')==='nu'?'selected':''?>>Nữ</option>
              <option value="khac" <?=($_POST['gioi_tinh']??'')==='khac'?'selected':''?>>Khác</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Địa chỉ thường trú</label>
          <input class="form-control" type="text" name="dia_chi" id="f_dia_chi"
                 placeholder="Số nhà, đường, phường, quận..."
                 value="<?=htmlspecialchars($_POST['dia_chi']??'')?>"/>
        </div>

        <!-- Xe của người ký hợp đồng - 1 xe cố định -->
        <div style="margin-top:4px;padding:14px;background:rgba(34,201,147,.04);border:1px solid rgba(34,201,147,.15);border-radius:12px;">
          <div style="font-size:12px;font-weight:700;color:var(--green);margin-bottom:10px;">🏍 Xe đăng ký <span style="font-weight:400;color:var(--text3);">(không bắt buộc)</span></div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
            <div>
              <label style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);display:block;margin-bottom:4px;">Biển số</label>
              <input class="form-control" type="text" name="xe_list[chu][bien_so]"
                     placeholder="51F1-23456" style="font-size:13px;text-transform:uppercase;"
                     oninput="this.value=this.value.toUpperCase()"/>
            </div>
            <div>
              <label style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);display:block;margin-bottom:4px;">Loại xe</label>
              <select class="form-control" name="xe_list[chu][loai_xe]" style="font-size:13px;">
                <option value="xe_may">🏍 Xe máy</option>
                <option value="xe_dien">⚡ Xe điện</option>
                <option value="xe_dap">🚲 Xe đạp</option>
                <option value="o_to">🚗 Ô tô</option>
              </select>
            </div>
            <div>
              <label style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);display:block;margin-bottom:4px;">Tên xe</label>
              <input class="form-control" type="text" name="xe_list[chu][mau_sac]"
                     placeholder="Wave Alpha, Exciter..." style="font-size:13px;"/>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
      <div class="card-header">
        <div class="card-title">👥 Bước 3 — Người ở cùng</div>
        <div class="card-sub">Không bắt buộc · Tối đa 3 người (phòng chứa tối đa 4 người)</div>
      </div>
      <div class="card-body">
        <div id="ocungList"></div>
        <button type="button" id="btnAddOCung" onclick="addOCung()"
                class="btn btn-outline btn-sm" style="width:100%;justify-content:center;gap:6px;">
          ＋ Thêm người ở cùng
        </button>
        <div id="ocungFull"
             style="display:none;font-size:12px;color:var(--amber);text-align:center;padding-top:10px;">
          ⚠ Đã đủ 3 người ở cùng (kể cả người làm HĐ = 4 người/phòng)
        </div>
      </div>
    </div>

    <!-- BƯỚC 4 — NỘI DUNG HỢP ĐỒNG -->
    <div class="card" style="margin-bottom:14px;">
      <div class="card-header">
        <div class="card-title">📋 Bước 4 — Nội dung hợp đồng</div>
        <div class="card-sub">Điều khoản & ghi chú thêm</div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Điều khoản chung</label>
          <textarea class="form-control" name="dieu_khoan" rows="20"
                    style="font-size:13px;line-height:1.7;"><?=htmlspecialchars($_POST['dieu_khoan']??'Hai bên cùng thỏa thuận và đồng ý với nội dung sau:

Điều 1:
• Bên A đồng ý cho bên B thuê một phòng thuộc nhà số nhà……………………………………….. với các điều kiện sau đây:
• Thời hạn thuê là: 06 tháng, kể từ ngày: …………..đến ngày: ……………..
• Mục đích thuê phòng của bên B là để ở và với tổng số người ở là: …….người, trong quá trình ở nếu có phát sinh thêm người vào ở thì bên B phải báo trước cho bên A, nếu như bên B tự tiện cho người lạ vào ở khi chưa có sự đồng ý của bên A thì coi như bên B vi phạm hợp đồng và phải chịu mất tiền đặt cọc.
• Khi Bên B giảm người và xe so với đăng ký ban đầu thì cũng phải báo cho bên A biết để kiểm soát, nếu không thông báo thì sẽ không giải quyết về tiền xe, người ở lại cũng phải chịu trách nhiệm khi người chuyển đi vi phạm pháp luật và các quy định của bên A.

Điều 2:
• Đơn giá phòng và các dịch vụ tiện ích như sau:
  - Đơn giá phòng: ……………… đồng/tháng
  - Đơn giá điện: 3.500 đồng/kw (Bằng chữ: Ba ngàn năm trăm đồng)
  - Đơn giá nước: 20.000 đồng/m³ (Bằng chữ: Hai mươi ngàn đồng)
  - Đơn giá DV Wifi, Rác, VS...: 150.000 đồng/phòng (Bằng chữ: Một trăm năm mươi ngàn đồng)
  - Giữ xe: 100.000 đồng/chiếc/tháng (Bằng chữ: Một trăm ngàn đồng)
• Các đơn giá trên sẽ giữ cố định, riêng giá thuê phòng được điều chỉnh tăng hàng năm từ 3-7%, nếu có bất cứ thay đổi nào khác thì bên A sẽ thông báo cho bên B biết trước ít nhất 20 ngày.
• Tiền thuê phòng bên B thanh toán cho bên A từ ngày 01-05 Tây hàng tháng.
• Bên B đặt tiền cọc là: ……………… đồng cho bên A.
• Tiền đặt cọc sẽ được trả lại cho bên thuê khi kết thúc hợp đồng thuê phòng sau khi trừ các khoản tiền điện, nước, phí dịch vụ và các khoản khác liên quan, khi bên B thực hiện đúng quy định các điều khoản trong hợp đồng.
• Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền đặt cọc và phải thanh toán chi phí phát sinh.
• Bên A ngưng hợp đồng trước thời hạn sẽ báo trước cho Bên B 30 ngày để sắp xếp chỗ ở mới.
• Bên A ngưng hợp đồng (lấy lại phòng) trước thời hạn mà không báo trước thì bồi thường gấp đôi số tiền bên B đã đặt cọc.

Điều 3: Trách nhiệm bên A
• Giao nhà, trang thiết bị trong nhà cho bên B đúng ngày ký hợp đồng.
• Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.
• Bố trí người để dọn vệ sinh cho những khu vực sinh hoạt chung nhằm tạo ra môi trường sống sạch sẽ cho người thuê phòng.
• Trang bị khóa vân tay cổng chính và nơi để xe cho khách thuê phòng.
• Bố trí bảo vệ trông xe cho Bên B, nếu xảy ra mất mát mà do lỗi bên A thì bên A phải bồi thường lại tài sản đó sau khi đã khấu hao sử dụng.

Điều 4: Trách nhiệm bên B
• Trả tiền thuê phòng và các khoản phí hàng tháng theo điều 2 của hợp đồng.
• Sử dụng đúng mục đích thuê nhà, khi cần sửa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.
• Cung cấp đầy đủ giấy tờ tùy thân, cho bên A để làm đăng ký tạm trú.
• Đồ đạc trang thiết bị trong nhà phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát, nếu hư hỏng mất mát thì phải bồi thường hoặc khắc phục lại trạng thái ban đầu.
• Phải thông báo cho bên A biết trước nếu muốn khoan, đục hay cải tạo lại căn phòng đang sử dụng, trường hợp nếu bên B cố ý cải tạo mà không xin phép thì bên A có quyền chấm dứt hợp đồng với bên B tại thời điểm đó và bên B phải khôi phục lại trạng thái ban đầu hoặc trả tiền để bên A thuê thợ làm.
• Quý khách tự ý dọn đi ra khỏi trọ là không còn quyền sử dụng phòng và không được tự ý ra vào trọ, không có quyền khiếu nại về sau và được xem là tự ý bỏ cọc. Quý khách tự ý bỏ cọc cũng phải thanh toán điện chi phí phát sinh.
• Để xe ngay ngắn, gọn gàng, đúng vạch đúng nơi quy định, theo sự hướng dẫn của Quản lý.
• Giữ vệ sinh môi trường chung và không được tụ tập gây ồn ào ảnh hưởng đến những người xung quanh.
• Báo cho bên A biết trước 30 ngày nếu muốn ngưng hợp đồng thuê không tiếp tục gia hạn ở nữa, không giải quyết trường hợp đi trước hạn hợp đồng. Ngày nhận báo trả phòng là 1-5 tây hàng tháng.
• Quý khách muốn sang phòng phải báo với quản lý và giá mới sẽ theo chủ nhà nếu có thay đổi.
• Khi trả phòng quý khách phải dọn phòng sạch sẽ, nếu phòng còn rác, toilet và bếp đóng vàng dơ thì phải trả tiền thuê người vệ sinh.
• Không được tàng trữ ma túy, hàng Quốc Cấm, các thiết bị hay vật liệu dễ gây cháy nổ vào trong khu trọ. Nghiêm cấm đánh bạc dưới mọi hình thức. Không được chứa chấp tội phạm liên quan đến pháp luật và thực hiện mọi hành vi khác vi phạm pháp luật, nếu có phải hoàn toàn tự chịu trách nhiệm trước pháp luật.
• Không làm ồn sau 21h30, không được dẫn bạn thường xuyên về phòng tổ chức ăn nhậu. Không được hát Karaoke gây ồn ào trong dãy trọ. Dắt bạn về phòng phải gặp quản lý để báo cáo trình giấy tờ.')?></textarea>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Ghi chú hợp đồng</label>
          <textarea class="form-control" name="ghi_chu" rows="3"
                    placeholder="Điều khoản đặc biệt, thoả thuận thêm..."><?=htmlspecialchars($_POST['ghi_chu']??'')?></textarea>
        </div>
      </div>
    </div>

    <!-- BƯỚC 5 — CHỮ KÝ -->
    <div class="card" style="margin-bottom:18px;">
      <div class="card-header">
        <div class="card-title">✍️ Bước 5 — Ký tên hợp đồng</div>
        <div class="card-sub">Người làm hợp đồng ký xác nhận</div>
      </div>
      <div class="card-body">
        <div style="display:flex;align-items:center;gap:14px;padding:14px;
                    background:var(--bg3);border:1px solid var(--border);border-radius:12px;">
          <!-- Thumbnail -->
          <div id="sigPreviewBox"
               style="width:160px;height:80px;border-radius:10px;background:#fff;
                      border:2px solid var(--border);display:flex;align-items:center;
                      justify-content:center;flex-shrink:0;overflow:hidden;">
            <img id="sigPreviewImg" src="" alt=""
                 style="max-width:100%;max-height:100%;display:none;object-fit:contain;"/>
            <span id="sigPreviewEmpty" style="font-size:11px;color:#bbb;">Chưa ký</span>
          </div>
          <!-- Nút -->
          <div style="flex:1;">
            <div id="sigDoneLabel"
                 style="display:none;font-size:13px;font-weight:700;color:var(--green);margin-bottom:8px;">
              ✓ Đã ký xong
            </div>
            <div style="font-size:12px;color:var(--text3);margin-bottom:12px;">
              Nhấn nút bên dưới để mở form ký tên.<br>
              Giữ chuột trái và di chuyển để ký tên.
            </div>
            <div style="display:flex;gap:8px;">
              <button type="button" onclick="openSigModal()"
                      class="btn btn-primary btn-sm" style="gap:6px;">✍️ Mở form ký tên</button>
              <button type="button" id="btnReSig" onclick="openSigModal()"
                      class="btn btn-outline btn-sm" style="display:none;">↺ Ký lại</button>
            </div>
          </div>
        </div>
        <input type="hidden" name="chu_ky" id="sigData"/>
      </div>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
        💾 Tạo hợp đồng
      </button>
      <a href="index.php?controller=hopdong&action=index" class="btn btn-outline">Hủy</a>
    </div>
  </form>

  <?php endif;?>
</div>

<!-- ══════ MODAL CHỮ KÝ ══════ -->
<div id="sigModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:1200;
            align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(6px);">
  <div style="background:var(--card);border:1px solid rgba(79,142,247,.25);border-radius:20px;
              width:100%;max-width:600px;box-shadow:0 28px 72px rgba(0,0,0,.7);overflow:hidden;">

    <!-- Header -->
    <div style="background:linear-gradient(135deg,#4f8ef7,#7c5cfc);padding:18px 22px;
                display:flex;align-items:center;justify-content:space-between;">
      <div>
        <div style="font-size:17px;font-weight:800;color:#fff;">✍️ Ký tên hợp đồng</div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:2px;">
          Giữ chuột trái &amp; di chuyển để ký · Hỗ trợ màn hình cảm ứng
        </div>
      </div>
      <button onclick="closeSigModal()"
              style="background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);
                     color:#fff;width:34px;height:34px;border-radius:50%;cursor:pointer;
                     font-size:17px;display:flex;align-items:center;justify-content:center;">✕</button>
    </div>

    <!-- Canvas area -->
    <div style="padding:20px;">
      <div id="sigModalWrap"
           style="border:2px dashed rgba(79,142,247,.4);border-radius:14px;background:#fff;
                  position:relative;overflow:hidden;cursor:crosshair;">
        <canvas id="sigCanvas" style="display:block;width:100%;touch-action:none;"></canvas>
        <div id="sigHint"
             style="position:absolute;inset:0;display:flex;flex-direction:column;
                    align-items:center;justify-content:center;gap:8px;pointer-events:none;">
          <span style="font-size:42px;opacity:.25;">✍️</span>
          <span style="font-size:13px;color:#aaa;font-weight:600;">Ký tên tại đây</span>
          <span style="font-size:11px;color:#ccc;">Giữ chuột trái và di chuyển</span>
        </div>
      </div>

      <!-- Actions -->
      <div style="display:flex;align-items:center;gap:10px;margin-top:14px;">
        <button type="button" onclick="clearSigCanvas()"
                class="btn btn-outline btn-sm">🗑 Xóa &amp; ký lại</button>
        <div style="flex:1;"></div>
        <span id="sigModalStatus"
              style="font-size:12px;color:var(--green);display:none;">✓ Đã vẽ chữ ký</span>
        <button type="button" onclick="confirmSig()" id="btnConfirmSig"
                class="btn btn-primary" style="padding:8px 22px;">
          ✓ Xác nhận chữ ký
        </button>
      </div>
    </div>
  </div>
</div>

<script src="frontend/assets/js/hopdong-create.js"></script>

<?php require_once 'frontend/views/Layouts/footer.php'; ?>