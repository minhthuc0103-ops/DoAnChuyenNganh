
let photoList = [];
let photoIdx  = 0;

function openPhotoModal(images, soPhong) {
  photoList = images;
  photoIdx  = 0;
  document.getElementById('photoModalTitle').textContent = '📷 Ảnh phòng ' + soPhong + ' (' + images.length + ' ảnh)';
  renderPhoto();
  renderThumbs();
  document.getElementById('photoModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closePhotoModal() {
  document.getElementById('photoModal').style.display = 'none';
  document.body.style.overflow = '';
}

function renderPhoto() {
  document.getElementById('photoModalImg').src = photoList[photoIdx] || '';
  document.getElementById('photoModalCount').textContent = (photoIdx + 1) + ' / ' + photoList.length;
  // Highlight thumb
  document.querySelectorAll('.photo-thumb').forEach((el, i) => {
    el.style.borderColor = i === photoIdx ? 'var(--accent)' : 'var(--border)';
    el.style.opacity = i === photoIdx ? '1' : '0.6';
  });
}

function renderThumbs() {
  const box = document.getElementById('photoModalThumbs');
  box.innerHTML = '';
  photoList.forEach((src, i) => {
    const img = document.createElement('img');
    img.src = src;
    img.className = 'photo-thumb';
    img.style.cssText = 'width:56px;height:42px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid var(--border);transition:.15s;flex-shrink:0;';
    img.onclick = () => { photoIdx = i; renderPhoto(); };
    img.onerror = () => { img.style.display = 'none'; };
    box.appendChild(img);
  });
}

function photoNav(dir) {
  photoIdx = (photoIdx + dir + photoList.length) % photoList.length;
  renderPhoto();
}

document.addEventListener('keydown', function(e) {
  if (document.getElementById('photoModal').style.display === 'flex') {
    if (e.key === 'Escape') closePhotoModal();
    if (e.key === 'ArrowLeft') photoNav(-1);
    if (e.key === 'ArrowRight') photoNav(1);
  }
});
