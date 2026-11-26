// Nút "Zalo": sao chép nội dung hóa đơn rồi mở khung chat Zalo của người thuê để dán và gửi.
(function () {
  document.addEventListener('click', function (e) {
    var nut = e.target.closest ? e.target.closest('.hd-zalo') : null;
    if (!nut) { return; }
    var noiDung = nut.getAttribute('data-msg') || '';
    var sdt = nut.getAttribute('data-sdt') || '';
    var moZalo = function () { window.open('https://zalo.me/' + encodeURIComponent(sdt), '_blank', 'noopener'); };

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(noiDung).catch(function () {
        window.prompt('Sao chép nội dung rồi dán vào Zalo:', noiDung);
      });
      moZalo();   // mở ngay trong lúc bấm chuột để trình duyệt không chặn cửa sổ mới
    } else {
      window.prompt('Sao chép nội dung rồi dán vào Zalo:', noiDung);
      moZalo();
    }
  });
})();