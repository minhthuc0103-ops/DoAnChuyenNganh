
function selectLoai(radio) {
  document.querySelectorAll('.loai-btn').forEach(el => el.classList.remove('active'));
  radio.closest('.loai-btn').classList.add('active');
}
