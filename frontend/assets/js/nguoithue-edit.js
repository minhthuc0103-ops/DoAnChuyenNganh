
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img   = document.getElementById('avatarPreview');
      const init  = document.getElementById('avatarInit');
      img.src = e.target.result;
      img.style.display = 'block';
      if (init) init.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
