
function openTeachModal(id, question) {
    document.getElementById('teachUnknownId').value  = id;
    document.getElementById('teachCauHoiMau').value  = question;
    document.getElementById('teachQuestion').textContent = question;
    // Tự gợi ý từ khóa từ câu hỏi
    const words = question.split(' ').filter(w => w.length > 3).slice(0, 4);
    document.getElementById('teachTuKhoa').value = words.join(', ');
    document.getElementById('teachModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeTeach() {
    document.getElementById('teachModal').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeTeach(); });
