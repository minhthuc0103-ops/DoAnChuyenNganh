
function openImgModal(src){document.getElementById('imgModalSrc').src=src;document.getElementById('imgModal').style.display='flex';document.body.style.overflow='hidden';}
function closeImgModal(){document.getElementById('imgModal').style.display='none';document.body.style.overflow='';}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeImgModal();});
