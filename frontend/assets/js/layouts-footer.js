
/* ── Format tiền VNĐ toàn cục ── */
(function() {
  const VND_NAMES = ['gia','tien_coc','tien_phong','tien_dien','tien_nuoc'];
  const sel       = VND_NAMES.map(n => `input[name="${n}"]`).join(',');

  function toNum(v)    { return parseInt(String(v).replace(/\D/g,''), 10); }
  function fmtDot(v)   { const n=toNum(v); return isNaN(n)?'':(n===0?'0':n.toLocaleString('vi-VN')); }
  function fmtVND(v)   { const d=fmtDot(v); return d ? d+' VNĐ' : ''; }

  document.addEventListener('DOMContentLoaded', function() {
    const forms = new Set();

    document.querySelectorAll(sel).forEach(inp => {
      inp.type        = 'text';
      inp.inputMode   = 'numeric';
      inp.placeholder = inp.placeholder || 'Nhập số tiền...';

      /* hiển thị ban đầu */
      if (inp.value !== '') inp.value = fmtVND(inp.value);

      inp.addEventListener('focus', function() {
        const raw = toNum(this.value);
        this.value = isNaN(raw) || raw === 0 ? '' : String(raw);
      });

      inp.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g,'');
        if (!raw) { this.value = ''; return; }
        this.value = fmtDot(raw);
        /* luôn đặt cursor về cuối — người gõ số không cần giữ giữa */
        const len = this.value.length;
        this.setSelectionRange(len, len);
      });

      inp.addEventListener('blur', function() {
        const raw = toNum(this.value);
        this.value = (!isNaN(raw) && raw >= 0) ? fmtVND(raw) : '';
      });

      /* strip VNĐ trước khi submit để PHP nhận số sạch */
      const form = inp.closest('form');
      if (form && !forms.has(form)) {
        forms.add(form);
        form.addEventListener('submit', function() {
          this.querySelectorAll(sel).forEach(i => {
            i.value = String(toNum(i.value));
          });
        });
      }
    });
  });
})();
