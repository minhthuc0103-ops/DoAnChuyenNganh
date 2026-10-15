
function selectMucDo(el) {
    document.querySelectorAll('.muc-do-label').forEach(l => l.classList.remove('active'));
    el.classList.add('active');
    el.querySelector('input[type=radio]').checked = true;
}
// Set active mặc định
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.muc-do-label input[type=radio]').forEach(input => {
        input.addEventListener('change', () => selectMucDo(input.closest('.muc-do-label')));
    });
});
