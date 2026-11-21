(function () {
  'use strict';

  // ── 1. Đăng ký service worker ──
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('sw.js').catch(function (err) {
        console.warn('Không đăng ký được service worker:', err);
      });
    });
  }

  // ── 2. Thanh gợi ý cài app (Android / máy tính: nút Cài đặt; iPhone / iPad: hướng dẫn) ──
  var DISMISS_KEY = 'rm_install_dismissed';
  var DISMISS_DAYS = 14;

  function daDongGanDay() {
    try {
      var t = parseInt(localStorage.getItem(DISMISS_KEY), 10);
      return !!t && (Date.now() - t) < DISMISS_DAYS * 86400000;
    } catch (e) { return false; }
  }

  function ghiNhoDong() {
    try { localStorage.setItem(DISMISS_KEY, String(Date.now())); } catch (e) { /* bỏ qua */ }
  }

  // Đã cài rồi, hoặc đang chạy trong vỏ app Android (Capacitor) thì không hiện gợi ý
  function dangChayNhuApp() {
    var standalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
    return !!(standalone || window.navigator.standalone === true || window.Capacitor);
  }

  // iPhone / iPod / iPad (iPadOS mới tự nhận là Macintosh nhưng có cảm ứng)
  function laIOS() {
    var ua = navigator.userAgent || '';
    var ipadMoi = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
    return /iPhone|iPad|iPod/.test(ua) || ipadMoi;
  }

  var thanh = null;

  function anThanh() {
    if (thanh && thanh.parentNode) { thanh.parentNode.removeChild(thanh); }
    thanh = null;
  }

  function hienThanh(noiDung, chuNut, khiBamNut) {
    if (thanh || !document.body) { return; }
    thanh = document.createElement('div');
    thanh.className = 'rm-install' + (document.querySelector('.sidebar') ? ' rm-with-sidebar' : '');
    thanh.setAttribute('role', 'region');
    thanh.setAttribute('aria-label', 'Cài đặt ứng dụng');

    var chu = document.createElement('span');
    chu.textContent = noiDung;
    thanh.appendChild(chu);

    if (chuNut) {
      var nut = document.createElement('button');
      nut.type = 'button';
      nut.className = 'rm-go';
      nut.textContent = chuNut;
      nut.addEventListener('click', khiBamNut);
      thanh.appendChild(nut);
    }

    var dong = document.createElement('button');
    dong.type = 'button';
    dong.className = 'rm-x';
    dong.setAttribute('aria-label', 'Đóng');
    dong.textContent = '×';
    dong.addEventListener('click', function () { ghiNhoDong(); anThanh(); });
    thanh.appendChild(dong);

    document.body.appendChild(thanh);
  }

  if (dangChayNhuApp() || daDongGanDay()) { return; }

  // Android / Chrome / Edge trên máy tính
  var suKienCai = null;
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();                 // tự hiện nút của mình thay vì thanh mặc định của trình duyệt
    suKienCai = e;
    hienThanh('Cài RoomManager như một app trên thiết bị này', 'Cài đặt', function () {
      if (!suKienCai) { return; }
      suKienCai.prompt();
      suKienCai.userChoice.then(function () { suKienCai = null; anThanh(); });
    });
  });
  window.addEventListener('appinstalled', anThanh);

  // iPhone / iPad: không có nút cài tự động, chỉ hướng dẫn thủ công
  if (laIOS()) {
    window.addEventListener('load', function () {
      setTimeout(function () {
        hienThanh('Cài app: bấm nút Chia sẻ (📤) rồi chọn “Thêm vào MH chính”', null, null);
      }, 1500);
    });
  }
})();