
function prevAv(input) {
  if(input.files&&input.files[0]){
    const r=new FileReader();
    r.onload=e=>{
      document.getElementById('avPreview').src=e.target.result;
      document.getElementById('avPreview').style.display='block';
      document.getElementById('avInit').style.display='none';
    };
    r.readAsDataURL(input.files[0]);
  }
}
function updateInit(name) {
  const w=name.trim().split(' ');
  const i=w.slice(-2).map(x=>(x[0]||'').toUpperCase()).join('');
  document.getElementById('avInit').textContent=i||'?';
}
