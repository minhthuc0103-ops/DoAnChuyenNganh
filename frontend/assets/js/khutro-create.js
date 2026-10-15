
function updatePreview() {
  const ma  = document.getElementById('maKhu').value.trim().toUpperCase();
  const ten = document.getElementById('tenKhu').value.trim();
  const box = document.getElementById('preview');
  const txt = document.getElementById('previewText');
  if (ma) {
    box.style.display='block';
    txt.textContent = `${ten||'Khu '+ma} → ${ma}101, ${ma}102, ${ma}103, ${ma}201...`;
  } else {
    box.style.display='none';
  }
}
