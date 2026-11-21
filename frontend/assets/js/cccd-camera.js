/* Chụp ảnh CCCD bằng camera.
 *
 * Gắn vào nút:  <button type="button" data-cccd-camera="cccd_truoc" data-cccd-title="Mặt trước">
 *   data-cccd-camera = name của <input type="file"> sẽ nhận ảnh (cùng form).
 *
 * Ảnh chụp được cắt đúng khung thẻ, nén JPEG rồi gán vào input đó và bắn sự kiện "change",
 * nên phần xem trước và phần lưu phía server dùng lại nguyên như khi chọn file.
 *
 * Camera trực tiếp (getUserMedia) chỉ chạy trên https hoặc localhost. Mở bằng http://IP-LAN
 * thì trình duyệt không cho, khi đó chuyển sang ô chọn file có capture="environment":
 * điện thoại sẽ mở thẳng app camera.
 */
(function () {
  'use strict';

  var TI_LE_THE = 85.6 / 53.98;   // kích thước thẻ CCCD theo chuẩn ID-1
  var RONG_TOI_DA = 1600;         // px, đủ đọc rõ chữ mà file vẫn nhẹ
  var CHAT_LUONG = 0.9;
  var GIOI_HAN = 5 * 1024 * 1024; // khớp giới hạn 5MB phía server
  var KICH_THUOC_TOI_THIEU = 160; // px; nhỏ hơn là camera chưa gửi hình thật

  var hop, video, khung, anhChup, thongBao, nutChup, nutChupLai, nutDung, nutDoiCam, tieuDe;
  var luong = null, inputDich = null, blobChup = null, dsCamera = [], camHienTai = 0;

  function coCameraTrucTiep() {
    return !!(window.isSecureContext && navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
  }

  function taoHop() {
    if (hop) return;
    hop = document.createElement('div');
    hop.className = 'cccd-cam';
    hop.setAttribute('role', 'dialog');
    hop.setAttribute('aria-modal', 'true');
    hop.setAttribute('aria-labelledby', 'cccdCamTitle');
    hop.hidden = true;
    hop.innerHTML =
      '<div class="cccd-cam__box">' +
        '<div class="cccd-cam__head">' +
          '<div id="cccdCamTitle" class="cccd-cam__title">Chụp CCCD</div>' +
          '<button type="button" class="cccd-cam__x" data-act="dong" aria-label="Đóng">✕</button>' +
        '</div>' +
        '<div class="cccd-cam__stage">' +
          '<video class="cccd-cam__video" autoplay playsinline muted></video>' +
          '<div class="cccd-cam__frame"><span></span></div>' +
          '<img class="cccd-cam__shot" alt="Ảnh vừa chụp" hidden>' +
        '</div>' +
        '<div class="cccd-cam__msg" aria-live="polite"></div>' +
        '<div class="cccd-cam__actions">' +
          '<button type="button" class="btn btn-outline" data-act="doicam" hidden>🔄 Đổi camera</button>' +
          '<button type="button" class="btn btn-outline" data-act="chuplai" hidden>↺ Chụp lại</button>' +
          '<button type="button" class="btn btn-primary" data-act="chup">📸 Chụp</button>' +
          '<button type="button" class="btn btn-primary" data-act="dung" hidden>✓ Dùng ảnh này</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(hop);

    tieuDe     = hop.querySelector('.cccd-cam__title');
    video      = hop.querySelector('video');
    khung      = hop.querySelector('.cccd-cam__frame');
    anhChup    = hop.querySelector('.cccd-cam__shot');
    thongBao   = hop.querySelector('.cccd-cam__msg');
    nutChup    = hop.querySelector('[data-act="chup"]');
    nutChupLai = hop.querySelector('[data-act="chuplai"]');
    nutDung    = hop.querySelector('[data-act="dung"]');
    nutDoiCam  = hop.querySelector('[data-act="doicam"]');

    hop.addEventListener('click', function (e) {
      if (e.target === hop) { dong(); return; }
      var nut = e.target.closest('[data-act]');
      if (!nut) return;
      var act = nut.getAttribute('data-act');
      if (act === 'dong') dong();
      else if (act === 'chup') chup();
      else if (act === 'chuplai') cheDoXemTruoc();
      else if (act === 'dung') dungAnh();
      else if (act === 'doicam') doiCamera();
    });
    document.addEventListener('keydown', function (e) {
      if (!hop.hidden && e.key === 'Escape') dong();
    });
  }

  function bao(msg, loi) {
    thongBao.textContent = msg || '';
    thongBao.classList.toggle('is-error', !!loi);
  }

  function dungLuong() {
    if (luong) { luong.getTracks().forEach(function (t) { t.stop(); }); luong = null; }
    video.srcObject = null;
  }

  function batCamera() {
    dungLuong();
    bao('Đang mở camera...');
    nutChup.disabled = true;
    var rangBuoc = { width: { ideal: 1920 }, height: { ideal: 1080 } };
    if (dsCamera.length && dsCamera[camHienTai].deviceId) {
      rangBuoc.deviceId = { exact: dsCamera[camHienTai].deviceId };
    } else {
      rangBuoc.facingMode = { ideal: 'environment' };   // điện thoại: ưu tiên camera sau
    }
    return navigator.mediaDevices.getUserMedia({ video: rangBuoc, audio: false })
      .then(function (s) {
        luong = s;
        video.srcObject = s;
        video.play().catch(function () {});   // không chờ: play() có thể treo mãi khi luồng chưa có hình
        return choKhungHinh();
      })
      .then(function () {
        nutChup.disabled = false;
        bao('Đặt thẻ nằm gọn trong khung, đủ sáng, không bị lóa rồi bấm Chụp.');
        if (!dsCamera.length && navigator.mediaDevices.enumerateDevices) {
          return navigator.mediaDevices.enumerateDevices().then(function (ds) {
            dsCamera = ds.filter(function (d) { return d.kind === 'videoinput'; });
            var dangDung = luong && luong.getVideoTracks()[0] && luong.getVideoTracks()[0].getSettings().deviceId;
            var i = dsCamera.findIndex(function (d) { return d.deviceId === dangDung; });
            camHienTai = i >= 0 ? i : 0;
            nutDoiCam.hidden = dsCamera.length < 2;
          });
        }
      })
      .catch(function (err) {
        var ten = err && err.name;
        if (ten === 'NotAllowedError' || ten === 'SecurityError') {
          bao('Bạn chưa cho phép dùng camera. Bấm biểu tượng ổ khóa cạnh thanh địa chỉ, bật Camera rồi thử lại.', true);
        } else if (ten === 'NotFoundError' || ten === 'OverconstrainedError') {
          bao('Không tìm thấy camera trên thiết bị này. Hãy dùng nút "Chọn ảnh".', true);
        } else if (ten === 'NotReadableError') {
          bao('Camera đang bị ứng dụng khác dùng (Zalo, Zoom...). Tắt ứng dụng đó rồi thử lại.', true);
        } else {
          bao('Không mở được camera: ' + (err && err.message ? err.message : err), true);
        }
      });
  }

  /** play() xong chưa chắc đã có hình: lúc đầu video có thể chỉ 2x2 px đen. Đợi tới khi có khung hình thật. */
  function khungHinhSanSang() {
    return video.readyState >= 2 && video.videoWidth >= KICH_THUOC_TOI_THIEU && video.videoHeight >= KICH_THUOC_TOI_THIEU;
  }

  function choKhungHinh() {
    return new Promise(function (xong, loi) {
      var batDau = Date.now();
      (function kiemTra() {
        if (!luong) { loi(new Error('Camera đã đóng.')); return; }
        if (khungHinhSanSang()) { xong(); return; }
        if (Date.now() - batDau > 8000) { loi(new Error('Camera không gửi hình về.')); return; }
        setTimeout(kiemTra, 100);
      })();
    });
  }

  function doiCamera() {
    if (dsCamera.length < 2) return;
    camHienTai = (camHienTai + 1) % dsCamera.length;
    batCamera();
  }

  function cheDoXemTruoc() {
    blobChup = null;
    anhChup.hidden = true;
    if (anhChup.src) { URL.revokeObjectURL(anhChup.src); anhChup.removeAttribute('src'); }
    video.hidden = false;
    khung.hidden = false;
    nutChup.hidden = false;
    nutChupLai.hidden = true;
    nutDung.hidden = true;
    nutDoiCam.hidden = dsCamera.length < 2;
    bao('Đặt thẻ nằm gọn trong khung, đủ sáng, không bị lóa rồi bấm Chụp.');
  }

  /** Cắt đúng phần nằm trong khung: tính vị trí khung trên video đang hiển thị rồi quy đổi sang pixel thật. */
  function chup() {
    if (!khungHinhSanSang()) { bao('Camera chưa sẵn sàng, đợi một chút rồi bấm lại.', true); return; }
    var vw = video.videoWidth, vh = video.videoHeight;
    // Video hiển thị đúng tỉ lệ gốc (CSS không dùng object-fit:cover) nên chỉ cần 1 hệ số co giãn
    var rv = video.getBoundingClientRect(), rk = khung.getBoundingClientRect();
    var tl = vw / rv.width;
    var sx = Math.max(0, (rk.left - rv.left) * tl), sy = Math.max(0, (rk.top - rv.top) * tl);
    var sw = Math.min(vw - sx, rk.width * tl), sh = Math.min(vh - sy, rk.height * tl);
    var dw = Math.min(RONG_TOI_DA, Math.round(sw)), dh = Math.round(dw * sh / sw);

    var c = document.createElement('canvas');
    c.width = dw; c.height = dh;
    c.getContext('2d').drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);
    c.toBlob(function (blob) {
      if (!blob) { bao('Không tạo được ảnh, thử lại.', true); return; }
      if (blob.size > GIOI_HAN) { bao('Ảnh quá lớn (trên 5MB), thử chụp lại.', true); return; }
      blobChup = blob;
      anhChup.src = URL.createObjectURL(blob);
      anhChup.hidden = false;
      video.hidden = true;
      khung.hidden = true;
      nutChup.hidden = true;
      nutDoiCam.hidden = true;
      nutChupLai.hidden = false;
      nutDung.hidden = false;
      bao('Kiểm tra chữ trên thẻ có đọc rõ không. Mờ thì bấm Chụp lại.');
    }, 'image/jpeg', CHAT_LUONG);
  }

  function ganFile(input, file) {
    try {
      var dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
    } catch (e) {
      alert('Trình duyệt này quá cũ, không gắn được ảnh chụp. Hãy dùng nút "Chọn ảnh".');
      return false;
    }
    input.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
  }

  function dungAnh() {
    if (!blobChup || !inputDich) return;
    var file = new File([blobChup], inputDich.name + '_' + Date.now() + '.jpg', { type: 'image/jpeg' });
    if (ganFile(inputDich, file)) dong();
  }

  function mo(input, ten) {
    taoHop();
    inputDich = input;
    tieuDe.textContent = 'Chụp CCCD — ' + (ten || '');
    hop.hidden = false;
    document.documentElement.classList.add('cccd-cam-open');
    cheDoXemTruoc();
    batCamera();
    nutChup.focus();
  }

  function dong() {
    dungLuong();
    if (anhChup && anhChup.src) { URL.revokeObjectURL(anhChup.src); anhChup.removeAttribute('src'); }
    blobChup = null;
    if (hop) hop.hidden = true;
    document.documentElement.classList.remove('cccd-cam-open');
  }

  /** Không có camera trực tiếp (http qua IP LAN, trình duyệt cũ): mở app camera của điện thoại. */
  function moAppCamera(input) {
    var tam = document.createElement('input');
    tam.type = 'file';
    tam.accept = 'image/*';
    tam.setAttribute('capture', 'environment');
    tam.addEventListener('change', function () {
      if (tam.files && tam.files[0]) ganFile(input, tam.files[0]);
    });
    tam.click();
  }

  document.addEventListener('click', function (e) {
    var nut = e.target.closest ? e.target.closest('[data-cccd-camera]') : null;
    if (!nut) return;
    e.preventDefault();
    var form = nut.form || nut.closest('form') || document;
    var input = form.querySelector('input[type="file"][name="' + nut.getAttribute('data-cccd-camera') + '"]');
    if (!input) return;
    if (coCameraTrucTiep()) mo(input, nut.getAttribute('data-cccd-title'));
    else moAppCamera(input);
  });

  window.addEventListener('pagehide', dong);
})();
