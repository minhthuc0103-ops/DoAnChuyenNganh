
// ── CHỌN NGƯỜI THUÊ ──
function selectNT(card) {
  // Bỏ chọn cũ
  document.querySelectorAll('.nt-card.selected').forEach(c => {
    c.classList.remove('selected');
    c.style.borderColor = 'var(--border)';
    c.style.background  = 'var(--bg3)';
    c.querySelector('.nt-check').style.display = 'none';
  });
  // Chọn mới
  card.classList.add('selected');
  card.style.borderColor = 'rgba(34,201,147,.5)';
  card.style.background  = 'rgba(34,201,147,.06)';
  card.querySelector('.nt-check').style.display = 'inline';

  // Fill form: toàn bộ thông tin cá nhân mà hợp đồng cần
  document.getElementById('f_ho_ten').value  = card.dataset.hoten;
  document.getElementById('f_cccd').value     = card.dataset.cccd;
  document.getElementById('f_sdt').value      = card.dataset.sdt;
  const dc = document.querySelector('[name="dia_chi"]');
  if (dc) dc.value = card.dataset.diachi;
  datNgaySinhChu(card.dataset.ngaysinh);
  const gt = document.querySelector('[name="gioi_tinh"]');
  if (gt) gt.value = card.dataset.gioitinh || 'nam';
  const acc = document.getElementById('f_account_id');
  if (acc) acc.value = card.dataset.account && card.dataset.account !== '0' ? card.dataset.account : '';
  let xeDK = [];
  try { xeDK = JSON.parse(card.dataset.xe || '[]'); } catch (e) { xeDK = []; }
  datXeChu(xeDK);
  document.getElementById('nguoiThueIdExisting').value = card.dataset.id;

  // Hiện badge + nút bỏ chọn; báo rõ nếu hồ sơ người này còn thiếu gì (vd: tài khoản mới tạo bằng Google chưa cập nhật hồ sơ)
  const thieu = [];
  if (!card.dataset.cccd)      thieu.push('CCCD');
  if (!card.dataset.sdt)       thieu.push('số điện thoại');
  if (!card.dataset.ngaysinh)  thieu.push('ngày sinh');
  if (!card.dataset.diachi)    thieu.push('địa chỉ');
  if (xeDK.length === 0)       thieu.push('xe đăng ký');
  const tb = document.getElementById('ntSelected');
  tb.textContent = '✓ Đã chọn: ' + card.dataset.hoten;
  if (thieu.length) {
    const canh = document.createElement('div');
    canh.style.cssText = 'margin-top:6px;font-size:12px;font-weight:500;color:var(--amber);line-height:1.6;';
    canh.textContent = '⚠ Hồ sơ người này chưa có: ' + thieu.join(', ') + '. Bạn nhập tay vào các ô bên dưới, hoặc nhờ người thuê cập nhật trong "Hồ sơ của tôi" rồi chọn lại.';
    tb.appendChild(canh);
  }
  tb.style.display = 'block';
  document.getElementById('ntClearBtn').style.display = 'inline-block';
}

/** Ghi ô Ngày sinh của người ký (ô dùng flatpickr nên phải set qua API); rỗng thì xóa trắng. */
function datNgaySinhChu(v) {
  const el = document.querySelector('[name="ngay_sinh"]');
  if (!el) return;
  const gt = (v && v !== '0000-00-00') ? v : '';
  if (el._flatpickr) {
    if (gt) { el._flatpickr.setDate(gt, true); } else { el._flatpickr.clear(); }
  } else {
    el.value = gt;
  }
}

/** Điền (hoặc xóa trắng) 2 dòng xe của người ký từ danh sách xe người thuê đã đăng ký trong hồ sơ. */
function datXeChu(xeList) {
  ['chu', 'chu2'].forEach((khoa, i) => {
    const xe = (xeList && xeList[i]) || null;
    const bs = document.querySelector('[name="xe_list[' + khoa + '][bien_so]"]');
    const lx = document.querySelector('[name="xe_list[' + khoa + '][loai_xe]"]');
    const ms = document.querySelector('[name="xe_list[' + khoa + '][mau_sac]"]');
    if (bs) bs.value = xe ? xe.bien_so : '';
    if (lx) lx.value = xe ? xe.loai_xe : 'xe_may';
    if (ms) ms.value = xe ? (xe.mau_sac || '') : '';
  });
  if (typeof capNhatTongNguoiXe === 'function') capNhatTongNguoiXe();
}

function clearNT() {
  document.querySelectorAll('.nt-card.selected').forEach(c => {
    c.classList.remove('selected');
    c.style.borderColor = 'var(--border)';
    c.style.background  = 'var(--bg3)';
    c.querySelector('.nt-check').style.display = 'none';
  });
  document.getElementById('f_ho_ten').value  = '';
  document.getElementById('f_cccd').value    = '';
  document.getElementById('f_sdt').value     = '';
  const dc = document.querySelector('[name="dia_chi"]');
  if (dc) dc.value = '';
  datNgaySinhChu('');
  const gt = document.querySelector('[name="gioi_tinh"]');
  if (gt) gt.value = 'nam';
  const acc = document.getElementById('f_account_id');
  if (acc) acc.value = '';
  datXeChu([]);
  document.getElementById('nguoiThueIdExisting').value = '';
  document.getElementById('ntSelected').style.display = 'none';
  document.getElementById('ntClearBtn').style.display = 'none';
}

// Chọn tài khoản ở ô "Liên kết tài khoản user" → tự điền thông tin cá nhân của người đó
document.addEventListener('DOMContentLoaded', () => {
  const acc = document.getElementById('f_account_id');
  if (!acc) return;
  acc.addEventListener('change', () => {
    if (!acc.value) return;
    const card = document.querySelector('.nt-card[data-account="' + acc.value + '"]');
    if (card) { selectNT(card); return; }           // đã có hồ sơ người thuê: điền đủ mọi thông tin
    // Chưa có hồ sơ: chỉ lấy được họ tên và SĐT từ tài khoản (không ghi đè nếu admin đã nhập)
    const opt = acc.options[acc.selectedIndex];
    const ten = document.getElementById('f_ho_ten');
    const sdt = document.getElementById('f_sdt');
    if (ten && !ten.value.trim()) ten.value = opt.dataset.hoten || '';
    if (sdt && !sdt.value.trim()) sdt.value = opt.dataset.sdt || '';
  });
});

function filterNT(kw) {
  const q    = kw.trim().toLowerCase();
  const list = document.getElementById('ntList');
  if (!q) { list.style.display = 'none'; return; }
  list.style.display = 'flex';
  document.querySelectorAll('.nt-card').forEach(c => {
    c.style.display = c.dataset.search.includes(q) ? '' : 'none';
  });
}

function tinhTongPhi() {
  let tong = 0;
  document.querySelectorAll('.phi-input').forEach(inp => {
    tong += parseInt(inp.value.replace(/\D/g,'') || 0);
  });
  document.getElementById('tongPhi').textContent = tong.toLocaleString('vi-VN') + 'đ';
}

function capNhatTongNguoiXe() {
  // Người: 1 (người ký) + số block người ở cùng
  const soOCung = document.querySelectorAll('#ocungList .oc-block').length;
  document.getElementById('tongNguoi').textContent = 1 + soOCung;

  // Xe: đếm input biển số không rỗng từ người ký + người ở cùng
  let soXe = 0;
  // Xe người ký (input[name="xe_list[chu][bien_so]"])
  const xeChu = document.querySelector('input[name="xe_list[chu][bien_so]"]');
  if (xeChu && xeChu.value.trim()) soXe++;
  // Xe người ở cùng
  document.querySelectorAll('[name$="[bien_so]"]').forEach(inp => {
    if (inp.name !== 'xe_list[chu][bien_so]' && inp.value.trim()) soXe++;
  });
  document.getElementById('tongXe').textContent = soXe;
}

// Observer theo dõi thay đổi DOM trong ocungList
document.addEventListener('DOMContentLoaded', () => {
  const ocungList = document.getElementById('ocungList');
  if (ocungList) {
    new MutationObserver(capNhatTongNguoiXe).observe(ocungList, { childList: true, subtree: true });
  }
  // Theo dõi input biển số thay đổi
  document.addEventListener('input', e => {
    if (e.target.name && e.target.name.includes('bien_so')) capNhatTongNguoiXe();
  });
  capNhatTongNguoiXe();
});

// ── LỌC PHÒNG THEO KHU ──
function filterPhong(khuId) {
  const selKhu  = document.getElementById('selKhu');
  const selPhong = document.getElementById('selPhong');
  const opts = selPhong.querySelectorAll('option[data-khu]');
  selPhong.value = '';
  document.getElementById('giaShow').textContent = 'Chọn phòng để xem giá';
  document.getElementById('giaShow').style.color = 'var(--text3)';

  // Hiện địa chỉ khu + điền vào điều khoản
  const diaChiBox  = document.getElementById('khuDiaChi');
  const diaChiText = document.getElementById('khuDiaChiText');
  const selOpt = selKhu.options[selKhu.selectedIndex];
  const dc = selOpt?.dataset?.diachi || '';
  if (khuId && dc) {
    diaChiText.textContent = dc;
    diaChiBox.style.display = 'block';
    // Điền vào điều khoản Điều 1
    const ta = document.querySelector('textarea[name="dieu_khoan"]');
    if (ta) {
      ta.value = ta.value.replace(
        /thuộc nhà số nhà[\.…]*/,
        'thuộc nhà số nhà ' + dc
      );
    }
  } else {
    diaChiBox.style.display = 'none';
  }

  if (!khuId) {
    opts.forEach(o => o.style.display = 'none');
    selPhong.disabled = true;
    selPhong.options[0].textContent = '— Chọn khu trước —';
    return;
  }

  let count = 0;
  opts.forEach(o => {
    if (o.dataset.khu === khuId) { o.style.display = ''; count++; }
    else o.style.display = 'none';
  });

  selPhong.disabled = false;
  selPhong.options[0].textContent = count ? '— Chọn phòng trống —' : '— Không có phòng trống —';
}

// ── GIÁ PHÒNG ──
function updateGia(sel) {
  const opt = sel.options[sel.selectedIndex];
  const gia = parseFloat(opt.dataset.gia || 0);
  const box = document.getElementById('giaShow');
  box.textContent = gia > 0 ? gia.toLocaleString('vi-VN') + 'đ / tháng' : 'Chọn phòng để xem giá';
  box.style.color = gia > 0 ? 'var(--amber)' : 'var(--text3)';

  // Tự điền tiền cọc = giá thuê
  if (gia > 0) {
    const cocInp = document.querySelector('input[name="tien_coc"]');
    if (cocInp && (!cocInp.value || cocInp.value === '0')) {
      cocInp.value = gia;
      cocInp.dispatchEvent(new Event('input'));
    }
  }

  // Tự điền giá phòng & tiền cọc vào điều khoản
  const ta = document.querySelector('textarea[name="dieu_khoan"]');
  if (ta && gia > 0) {
    const giaFmt  = gia.toLocaleString('vi-VN');
    const giaChu  = soThanhChu(gia);
    ta.value = ta.value
      .replace(/- Đơn giá phòng:.*?đồng\/tháng/,
               `- Đơn giá phòng: ${giaFmt} đồng/tháng (Bằng chữ: ${giaChu})`)
      .replace(/Bên B đặt tiền cọc là:.*?đồng cho bên A\./,
               `Bên B đặt tiền cọc là: ${giaFmt} đồng (Bằng chữ: ${giaChu}) cho bên A.`);
  }
}

// Đổi số thành chữ (đơn giản, đủ dùng cho số tiền VND)
function soThanhChu(so) {
  const dvDon = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
  const dvChuc = ['', 'mười', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi',
                  'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
  if (so === 0) return 'không';
  let chu = '';
  const ty   = Math.floor(so / 1e9);
  const trieu= Math.floor((so % 1e9) / 1e6);
  const nghin= Math.floor((so % 1e6) / 1e3);
  const tram = Math.floor((so % 1e3) / 100);
  const chuc  = Math.floor((so % 100) / 10);
  const don   = so % 10;
  if (ty)    chu += dvDon[ty] + ' tỷ ';
  if (trieu) chu += dvDon[trieu] + ' triệu ';
  if (nghin) chu += (nghin < 10 ? dvDon[nghin] : dvChuc[Math.floor(nghin/10)] + (nghin%10?' '+dvDon[nghin%10]:'')) + ' nghìn ';
  if (tram)  chu += dvDon[tram] + ' trăm ';
  if (chuc || don) chu += dvChuc[chuc] + (don ? ' ' + dvDon[don] : '');
  return chu.trim();
}

// Set default dates cho HopDong (override Flatpickr)
document.addEventListener('DOMContentLoaded', () => {
  const batDau   = document.querySelector('input[name="ngay_bat_dau"]');
  const ketThuc  = document.querySelector('input[name="ngay_ket_thuc"]');
  if (!batDau || !ketThuc) return;

  // Nếu chưa có giá trị (không phải POST lại)
  if (!batDau._flatpickr) return;

  const today = new Date();
  const after6 = new Date(today);
  after6.setMonth(after6.getMonth() + 6);

  if (!batDau.value) batDau._flatpickr.setDate(today, true);
  if (!ketThuc.value) ketThuc._flatpickr.setDate(after6, true);
});

// ── MODAL CHỮ KÝ ──
(function () {
  const modal  = document.getElementById('sigModal');
  const canvas = document.getElementById('sigCanvas');
  const ctx    = canvas.getContext('2d');
  let drawing  = false;
  let hasSig   = false;

  function initCanvas() {
    canvas.width  = canvas.offsetWidth;
    canvas.height = 260;
    ctx.strokeStyle = '#ffffff';   // nét trắng trên nền tối của ô ký (ảnh lưu vào hợp đồng được đổi sang nét tối ở confirmSig)
    ctx.lineWidth   = 2.8;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';
  }

  function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return {
      x: (t.clientX - r.left) * (canvas.width  / r.width),
      y: (t.clientY - r.top)  * (canvas.height / r.height),
    };
  }

  function onStart(e) {
    e.preventDefault();
    drawing = true;
    const p = getPos(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
    document.getElementById('sigHint').style.display = 'none';
    document.getElementById('sigModalWrap').style.borderStyle = 'solid';
    document.getElementById('sigModalWrap').style.borderColor = 'rgba(79,142,247,.6)';
  }

  function onMove(e) {
    if (!drawing) return;
    e.preventDefault();
    const p = getPos(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    hasSig = true;
    document.getElementById('sigModalStatus').style.display = 'inline';
  }

  function onStop() {
    if (!drawing) return;
    drawing = false;
    ctx.beginPath();
  }

  canvas.addEventListener('mousedown',  onStart);
  canvas.addEventListener('mousemove',  onMove);
  canvas.addEventListener('mouseup',    onStop);
  canvas.addEventListener('mouseleave', onStop);
  canvas.addEventListener('touchstart', onStart, { passive: false });
  canvas.addEventListener('touchmove',  onMove,  { passive: false });
  canvas.addEventListener('touchend',   onStop);

  window.openSigModal = function () {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    /* Khởi tạo canvas sau khi modal hiển thị */
    requestAnimationFrame(() => { initCanvas(); hasSig = false; });
  };

  window.closeSigModal = function () {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  };

  window.clearSigCanvas = function () {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasSig = false;
    document.getElementById('sigHint').style.display        = 'flex';
    document.getElementById('sigModalStatus').style.display = 'none';
    document.getElementById('sigModalWrap').style.borderStyle  = 'dashed';
    document.getElementById('sigModalWrap').style.borderColor  = 'rgba(79,142,247,.4)';
  };

  window.confirmSig = function () {
    if (!hasSig) { alert('Vui lòng ký tên trước khi xác nhận!'); return; }
    /* Xem trước: giữ nét trắng như lúc ký */
    const previewURL = canvas.toDataURL('image/png');

    /* Ảnh LƯU vào hợp đồng: đổi mọi nét vẽ sang màu tối (giữ nền trong suốt) để vẫn đọc được trên giấy trắng khi in */
    const out = document.createElement('canvas');
    out.width = canvas.width; out.height = canvas.height;
    const octx = out.getContext('2d');
    octx.drawImage(canvas, 0, 0);
    octx.globalCompositeOperation = 'source-in';
    octx.fillStyle = '#0f172a';
    octx.fillRect(0, 0, out.width, out.height);
    document.getElementById('sigData').value = out.toDataURL('image/png');

    /* Hiện thumbnail */
    const img  = document.getElementById('sigPreviewImg');
    img.src    = previewURL;
    img.style.display = 'block';
    document.getElementById('sigPreviewEmpty').style.display = 'none';
    document.getElementById('sigDoneLabel').style.display    = 'block';
    document.getElementById('btnReSig').style.display        = 'inline-flex';
    closeSigModal();
  };

  /* Đóng khi click nền */
  modal.addEventListener('click', e => { if (e.target === modal) closeSigModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSigModal(); });
})();

// ── XE CỦA NGƯỜI Ở CÙNG ──
const ocXeCounts = {};
window.addOCXe = function(ocIdx) {
  ocXeCounts[ocIdx] = (ocXeCounts[ocIdx] || 0) + 1;
  if (ocXeCounts[ocIdx] > 2) { ocXeCounts[ocIdx] = 2; return; } // tối đa 2 xe/người
  const xeIdx = Date.now();
  const num   = ocXeCounts[ocIdx];
  const div   = document.createElement('div');
  div.id = `ocxe_${ocIdx}_${xeIdx}`;
  div.style.cssText = 'margin-bottom:10px;padding:10px;background:var(--bg);border-radius:8px;border:1px solid var(--border);';
  div.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:12px;font-weight:600;color:var(--text2);">Xe #${num}</span>
      <button type="button" onclick="removeOCXe('${ocIdx}','${xeIdx}')"
              style="background:none;border:none;color:var(--red);cursor:pointer;font-size:13px;">✕</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
      <div>
        <label style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);display:block;margin-bottom:4px;">Biển số</label>
        <input class="form-control" type="text" name="nguoi_o_cung[${ocIdx}][xe][${xeIdx}][bien_so]"
               placeholder="51F1-23456" style="font-size:13px;text-transform:uppercase;"
               oninput="this.value=this.value.toUpperCase()"/>
      </div>
      <div>
        <label style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);display:block;margin-bottom:4px;">Loại xe</label>
        <select class="form-control" name="nguoi_o_cung[${ocIdx}][xe][${xeIdx}][loai_xe]" style="font-size:13px;">
          <option value="xe_may">🏍 Xe máy</option>
          <option value="xe_dien">⚡ Xe điện</option>
          <option value="xe_dap">🚲 Xe đạp</option>
          <option value="o_to">🚗 Ô tô</option>
        </select>
      </div>
      <div>
        <label style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);display:block;margin-bottom:4px;">Tên xe</label>
        <input class="form-control" type="text" name="nguoi_o_cung[${ocIdx}][xe][${xeIdx}][mau_sac]"
               placeholder="Wave Alpha, Exciter, SH..." style="font-size:13px;"/>
      </div>
    </div>`;
  document.getElementById(`ocXeList_${ocIdx}`).appendChild(div);
};

window.removeOCXe = function(ocIdx, xeIdx) {
  const el = document.getElementById(`ocxe_${ocIdx}_${xeIdx}`);
  if (el) { el.remove(); ocXeCounts[ocIdx] = Math.max(0, (ocXeCounts[ocIdx]||1) - 1); }
};

// ── NGƯỜI Ở CÙNG ──
const MAX_OC  = 3;
let   ocCount = 0;
const ocIds   = [];

window.addOCung = function () {
  if (ocCount >= MAX_OC) return;
  const idx = Date.now();
  ocIds.push(idx);
  const num = ocIds.length;
  const div = document.createElement('div');
  div.className = 'oc-block';
  div.id = `ocb_${idx}`;
  div.innerHTML = `
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
      <div class="oc-num">${num}</div>
      <span style="font-size:13px;font-weight:700;color:var(--text);flex:1;">Người ở cùng #${num}</span>
      <button type="button" onclick="removeOCung('${idx}')"
              style="background:rgba(247,92,92,.12);border:1px solid rgba(247,92,92,.2);
                     border-radius:8px;color:var(--red);font-size:11px;font-weight:700;
                     padding:5px 12px;cursor:pointer;">✕ Xóa</button>
    </div>
    <div class="form-group">
      <label class="form-label">Họ và tên <span style="color:var(--red)">*</span></label>
      <div class="oc-suggest-wrap">
        <input class="form-control" type="text" autocomplete="off"
               name="nguoi_o_cung[${idx}][ho_ten]" placeholder="Bấm để chọn hoặc nhập tên mới" required/>
        <div class="oc-suggest"></div>
      </div>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">CCCD / CMND</label>
        <input class="form-control" type="text"
               name="nguoi_o_cung[${idx}][cccd]" placeholder="079......"/>
      </div>
      <div class="form-group">
        <label class="form-label">Số điện thoại</label>
        <input class="form-control" type="tel"
               name="nguoi_o_cung[${idx}][sdt]" placeholder="0901234567"/>
      </div>
    </div>
    <div class="form-grid" style="margin-bottom:0">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Ngày sinh</label>
        <input class="form-control oc-date" type="date"
               name="nguoi_o_cung[${idx}][ngay_sinh]"/>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Giới tính</label>
        <select class="form-control" name="nguoi_o_cung[${idx}][gioi_tinh]">
          <option value="nam">Nam</option>
          <option value="nu">Nữ</option>
          <option value="khac">Khác</option>
        </select>
      </div>
    </div>

    <!-- Xe của người ở cùng -->
    <div style="margin-top:14px;padding:12px;background:rgba(34,201,147,.04);border:1px solid rgba(34,201,147,.15);border-radius:10px;">
      <div style="font-size:12px;font-weight:700;color:var(--green);margin-bottom:10px;">🏍 Xe đăng ký <span style="font-weight:400;color:var(--text3);">(không bắt buộc)</span></div>
      <div id="ocXeList_${idx}"></div>
      <button type="button" onclick="addOCXe('${idx}')"
              style="display:flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;border:1px dashed rgba(34,201,147,.4);background:transparent;color:var(--green);font-size:12px;font-weight:600;cursor:pointer;width:100%;justify-content:center;">
        + Thêm xe
      </button>
    </div>`;

  document.getElementById('ocungList').appendChild(div);
  capNhatTongNguoiXe();

  /* Flatpickr cho date input mới */
  if (typeof flatpickr !== 'undefined') {
    div.querySelectorAll('input[type=date]').forEach(i => {
      flatpickr(i, { locale:'vn', dateFormat:'Y-m-d', altInput:true, altFormat:'d/m/Y', allowInput:true });
    });
  }

  ocCount++;
  document.getElementById('btnAddOCung').style.display = ocCount >= MAX_OC ? 'none' : 'flex';
  document.getElementById('ocungFull').style.display   = ocCount >= MAX_OC ? 'block' : 'none';
};

window.removeOCung = function (idx) {
  const el = document.getElementById(`ocb_${idx}`);
  if (el) el.remove();
  const i = ocIds.indexOf(idx);
  if (i > -1) ocIds.splice(i, 1);
  ocCount = Math.max(0, ocCount - 1);
  document.getElementById('btnAddOCung').style.display = 'flex';
  document.getElementById('ocungFull').style.display   = 'none';
  capNhatTongNguoiXe();
  /* Cập nhật số thứ tự */
  document.querySelectorAll('.oc-block').forEach((b, n) => {
    b.querySelector('.oc-num').textContent = n + 1;
    b.querySelector('span[style*="font-weight:700"]').textContent = `Người ở cùng #${n + 1}`;
  });
};


// ── GỢI Ý TÊN NGƯỜI Ở CÙNG ──
// Bấm vào ô "Họ và tên" của người ở cùng sẽ hiện danh sách người CHƯA có hợp đồng
// (window.UNG_VIEN_OCUNG do controller truyền xuống). Chọn một người thì điền sẵn CCCD,
// SĐT, ngày sinh, giới tính. Vẫn có thể gõ tay tên người chưa đăng ký.
(function () {
  const boDau = s => (s || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '')
                      .replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase();
  const isNameInput = el => el && el.tagName === 'INPUT' && /^nguoi_o_cung\[[^\]]+\]\[ho_ten\]$/.test(el.name || '');
  let dangMo = null;     // { input, box, items, active }

  function nguoiChinhDangChon() {
    const idEl = document.getElementById('nguoiThueIdExisting');
    return {
      id:   idEl && idEl.value ? parseInt(idEl.value, 10) : 0,
      cccd: (document.getElementById('f_cccd') || {}).value || '',
      sdt:  (document.getElementById('f_sdt') || {}).value || ''
    };
  }

  /** Bỏ: người làm HĐ ở Bước 2 và những người đã được chọn ở khối khác (so theo id, CCCD hoặc SĐT). */
  function ungVienConLai(block) {
    const chinh = nguoiChinhDangChon();
    const daChon = [];
    document.querySelectorAll('#ocungList .oc-block').forEach(b => {
      if (b !== block && b.dataset.ntId) daChon.push(parseInt(b.dataset.ntId, 10));
    });
    return (window.UNG_VIEN_OCUNG || []).filter(u => {
      if (chinh.id && u.id === chinh.id) return false;
      if (chinh.cccd && u.cccd && chinh.cccd.trim() === u.cccd) return false;
      if (chinh.sdt && u.sdt && chinh.sdt.trim() === u.sdt) return false;
      return daChon.indexOf(u.id) === -1;
    });
  }

  function dong() {
    if (dangMo) { dangMo.box.classList.remove('open'); dangMo = null; }
  }

  function ve(input) {
    const block = input.closest('.oc-block');
    const box = input.parentElement.querySelector('.oc-suggest');
    if (!block || !box) return;
    // Ô đang giữ đúng tên người đã chọn: bấm vào thì hiện đủ danh sách để đổi người, không lọc theo tên đó
    const daChonDung = block.dataset.ntId && input.value === block.dataset.ntTen;
    const q = daChonDung ? '' : boDau(input.value.trim());
    const ds = ungVienConLai(block).filter(u => !q || boDau(u.ho_ten + ' ' + u.cccd + ' ' + u.sdt).indexOf(q) !== -1);

    box.textContent = '';
    const head = document.createElement('div');
    head.className = 'oc-suggest-head';
    head.textContent = 'Người chưa có hợp đồng';
    box.appendChild(head);

    if (!ds.length) {
      const rong = document.createElement('div');
      rong.className = 'oc-suggest-empty';
      rong.textContent = q ? 'Không có ai khớp. Cứ nhập tên đầy đủ nếu là người mới.'
                           : 'Hiện không còn ai chưa có hợp đồng. Hãy nhập tên người mới.';
      box.appendChild(rong);
    }
    const items = ds.map(u => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'oc-suggest-item';
      const av = document.createElement('div');
      av.className = 'oc-suggest-av';
      av.textContent = (u.ho_ten.trim().charAt(0) || '?').toUpperCase();
      const txt = document.createElement('div');
      const ten = document.createElement('div');
      ten.className = 'oc-suggest-name';
      ten.textContent = u.ho_ten;
      const meta = document.createElement('div');
      meta.className = 'oc-suggest-meta';
      meta.textContent = [u.sdt && ('SĐT ' + u.sdt), u.cccd && ('CCCD ' + u.cccd)].filter(Boolean).join(' · ') || 'Chưa có SĐT / CCCD';
      txt.appendChild(ten); txt.appendChild(meta);
      b.appendChild(av); b.appendChild(txt);
      // mousedown (không dùng click) để chọn được trước khi ô nhập mất focus
      b.addEventListener('mousedown', e => { e.preventDefault(); chon(input, u); });
      box.appendChild(b);
      return b;
    });
    box.classList.add('open');
    dangMo = { input, box, items, active: -1, ds };
  }

  /** Ghi đè 1 ô; giá trị rỗng thì xóa trắng (tránh sót dữ liệu của người chọn trước đó). */
  function dat(block, ten, v, macDinh) {
    const el = block.querySelector('[name$="[' + ten + ']"]');
    if (!el) return;
    const gt = (v === undefined || v === null || v === '' || v === '0000-00-00') ? (macDinh || '') : v;
    if (el._flatpickr) {
      if (gt) { el._flatpickr.setDate(gt, true); } else { el._flatpickr.clear(); }
    } else {
      el.value = gt;
    }
  }

  const CAC_O_TU_DIEN = [['cccd', ''], ['sdt', ''], ['ngay_sinh', ''], ['gioi_tinh', 'nam']];

  /** Bỏ chọn: xóa các ô đã tự điền từ người đó để không thành "tên mới + CCCD người cũ". */
  function boChon(block) {
    CAC_O_TU_DIEN.forEach(c => dat(block, c[0], '', c[1]));
    delete block.dataset.ntId; delete block.dataset.ntTen;
  }

  function chon(input, u) {
    const block = input.closest('.oc-block');
    input.value = u.ho_ten;
    dat(block, 'cccd', u.cccd);
    dat(block, 'sdt', u.sdt);
    dat(block, 'ngay_sinh', u.ngay_sinh);
    dat(block, 'gioi_tinh', u.gioi_tinh, 'nam');
    block.dataset.ntId = u.id;
    block.dataset.ntTen = u.ho_ten;
    dong();
    if (typeof capNhatTongNguoiXe === 'function') capNhatTongNguoiXe();
  }

  document.addEventListener('focusin', e => { if (isNameInput(e.target)) ve(e.target); });
  document.addEventListener('click',   e => {
    if (isNameInput(e.target)) { if (!dangMo || dangMo.input !== e.target) ve(e.target); return; }
    if (!e.target.closest || !e.target.closest('.oc-suggest')) dong();
  });
  document.addEventListener('input', e => {
    if (!isNameInput(e.target)) return;
    const block = e.target.closest('.oc-block');
    // gõ lại tên khác tên đã chọn thì không còn coi là người đã chọn
    if (block && block.dataset.ntId && e.target.value !== block.dataset.ntTen) {
      boChon(block);
    }
    ve(e.target);
  });
  document.addEventListener('keydown', e => {
    if (!dangMo || e.target !== dangMo.input) return;
    const n = dangMo.items.length;
    if (e.key === 'Escape') { dong(); return; }
    if (!n) return;
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      dangMo.active = (dangMo.active + (e.key === 'ArrowDown' ? 1 : -1) + n) % n;
      dangMo.items.forEach((it, i) => it.classList.toggle('active', i === dangMo.active));
      dangMo.items[dangMo.active].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter' && dangMo.active >= 0) {
      e.preventDefault();
      chon(dangMo.input, dangMo.ds[dangMo.active]);
    }
  });
  document.addEventListener('focusout', e => {
    if (isNameInput(e.target)) setTimeout(() => { if (dangMo && dangMo.input === e.target && document.activeElement !== e.target) dong(); }, 150);
  });
})();
