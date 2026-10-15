
function togglePw(id,btn){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
function checkStrength(v){
  const f=document.getElementById('strFill'),l=document.getElementById('strLbl');
  let s=0;if(v.length>=8)s++;if(v.length>=10)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;
  const lv=[{w:'0%',bg:'transparent',t:'Nhập mật khẩu',c:'#555a72'},{w:'25%',bg:'#f87171',t:'Yếu',c:'#f87171'},{w:'50%',bg:'#fb923c',t:'Trung bình',c:'#fb923c'},{w:'75%',bg:'#60a5fa',t:'Khá mạnh',c:'#60a5fa'},{w:'100%',bg:'#4ade80',t:'Mạnh',c:'#4ade80'}][Math.min(s,4)];
  f.style.width=lv.w;f.style.background=lv.bg;l.textContent=lv.t;l.style.color=lv.c;checkMatch();
}
function checkMatch(){const p1=document.getElementById('pw1').value,p2=document.getElementById('pw2').value,fb=document.getElementById('matchFb');if(!p2){fb.textContent='';return;}fb.textContent=p1===p2?'✓ Mật khẩu khớp':'✗ Không khớp';fb.className='fb '+(p1===p2?'fb-ok':'fb-err');}
function checkUsername(v){const fb=document.getElementById('userFb');if(!v){fb.textContent='';return;}if(v.length<4){fb.textContent='✗ Tối thiểu 4 ký tự';fb.className='fb fb-err';}else if(/^[a-zA-Z0-9_]+$/.test(v)){fb.textContent='✓ Hợp lệ';fb.className='fb fb-ok';}else{fb.textContent='✗ Chỉ dùng chữ, số và _';fb.className='fb fb-err';}}
document.querySelectorAll('input').forEach(f=>f.addEventListener('focus',()=>{document.getElementById('sc3').className='step-circle active';document.getElementById('sl2').className='step-line done';}));
document.getElementById('regForm').addEventListener('submit',function(e){
  if(document.getElementById('pw1').value!==document.getElementById('pw2').value){e.preventDefault();document.getElementById('matchFb').textContent='✗ Mật khẩu không khớp!';document.getElementById('matchFb').className='fb fb-err';return;}
  const btn=document.getElementById('regBtn');btn.textContent='Đang xử lý...';btn.style.opacity='0.7';btn.style.pointerEvents='none';
});
