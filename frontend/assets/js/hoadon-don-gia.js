
function toNum(v) { return parseInt(String(v).replace(/\D/g,''),10)||0; }
function fmt(v)   { return toNum(v).toLocaleString('vi-VN'); }

function updateExample() {
  const d = toNum(document.getElementById('inp_dien').value);
  const n = toNum(document.getElementById('inp_nuoc').value);
  document.getElementById('ex_dien').textContent = (d*50).toLocaleString('vi-VN')+'đ';
  document.getElementById('ex_nuoc').textContent = (n*5).toLocaleString('vi-VN')+'đ';
  document.getElementById('ex_tong').textContent = (d*50+n*5).toLocaleString('vi-VN')+'đ';
  // Cảnh báo
  const warn = d > 5000 || n > 25000;
  document.getElementById('warnBox').style.display = warn ? 'block' : 'none';
}

function onDienInput(inp) {
  const raw = inp.value.replace(/\D/g,'');
  inp.value = raw ? parseInt(raw).toLocaleString('vi-VN') : '';
  document.getElementById('sl_dien').value = toNum(inp.value);
  updateExample();
}

function onNuocInput(inp) {
  const raw = inp.value.replace(/\D/g,'');
  inp.value = raw ? parseInt(raw).toLocaleString('vi-VN') : '';
  document.getElementById('sl_nuoc').value = toNum(inp.value);
  updateExample();
}

function onDvInput(inp) {
  const raw = inp.value.replace(/\D/g,'');
  inp.value = raw ? parseInt(raw).toLocaleString('vi-VN') : '';
}

function syncSlider(type, val) {
  const id = type === 'dien' ? 'inp_dien' : 'inp_nuoc';
  document.getElementById(id).value = parseInt(val).toLocaleString('vi-VN');
  updateExample();
}

// Strip format trước khi submit
document.getElementById('donGiaForm').addEventListener('submit', function() {
  document.getElementById('inp_dien').value = toNum(document.getElementById('inp_dien').value);
  document.getElementById('inp_nuoc').value = toNum(document.getElementById('inp_nuoc').value);
  document.getElementById('inp_dv').value   = toNum(document.getElementById('inp_dv').value);
});
