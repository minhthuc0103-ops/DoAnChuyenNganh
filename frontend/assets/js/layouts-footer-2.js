
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('input[type=date]').forEach(function(inp) {
    const existing = inp.value;
    flatpickr(inp, {
      locale      : 'vn',
      dateFormat  : 'Y-m-d',
      altInput    : true,
      altFormat   : 'd/m/Y',
      allowInput  : true,
      defaultDate : existing || null,
    });
  });
});
