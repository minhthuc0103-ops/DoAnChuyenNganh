
    (function(){
      const input = document.getElementById('globalSearchInput');
      const box = document.getElementById('globalSearchResults');
      const list = document.getElementById('globalSearchList');
      const all = document.getElementById('globalSearchAll');
      if (!input || !box || !list) return;

      // Người dùng (vai trò user): ô này tìm PHÒNG; quản trị: tìm NGƯỜI THUÊ
      const wrapEl = document.getElementById('globalSearchBox');
      const modePhong = !!(wrapEl && wrapEl.dataset.mode === 'phong');
      const urlDanhSach = (q) => modePhong
        ? 'index.php?controller=user&action=phong&kw=' + encodeURIComponent(q)
        : 'index.php?controller=nguoithue&action=index&kw=' + encodeURIComponent(q);

      let timer = null;
      let activeIndex = -1;

      const esc = (s) => String(s || '').replace(/[&<>"']/g, c => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
      }[c]));

      function showBox() { box.classList.add('show'); }
      function hideBox() { box.classList.remove('show'); activeIndex = -1; }

      function setActive(index) {
        const items = list.querySelectorAll('.gs-item');
        items.forEach(i => i.classList.remove('active'));
        activeIndex = index;
        if (items[activeIndex]) items[activeIndex].classList.add('active');
      }

      /** Kết quả tìm phòng: phòng xem được là link, phòng người khác đang thuê chỉ hiện thông tin chung (không bấm được). */
      function renderPhong(results, q) {
        if (all) all.href = urlDanhSach(q);
        if (!results.length) {
          list.innerHTML = '<div class="gs-empty">Không tìm thấy phòng phù hợp</div>';
          showBox();
          return;
        }
        list.innerHTML = results.map(r => {
          const gia = Number(r.price || 0).toLocaleString('vi-VN') + 'đ/tháng';
          const inner = `
            <div class="gs-av">${r.viewable ? '🏠' : '🔒'}</div>
            <div class="gs-main">
              <div class="gs-name">Phòng ${esc(r.room)} <span class="gs-pill ${r.viewable ? '' : 'off'}">${esc(r.status)}</span></div>
              <div class="gs-meta">${r.area ? esc(r.area) + ' · ' : ''}${esc(gia)}</div>
              <div class="gs-meta">${r.viewable ? 'Bấm để xem chi tiết' : 'Phòng đang có người thuê, không xem được chi tiết'}</div>
            </div>`;
          return r.viewable && r.url
            ? `<a class="gs-item" href="${esc(r.url)}">${inner}</a>`
            : `<div class="gs-item" style="cursor:not-allowed;opacity:.7;">${inner}</div>`;
        }).join('');
        setActive(-1);
        showBox();
      }

      function render(results, q) {
        if (modePhong) { renderPhong(results, q); return; }
        if (all) all.href = 'index.php?controller=nguoithue&action=index&kw=' + encodeURIComponent(q);
        if (!q || q.length < 2) {
          list.innerHTML = '<div class="gs-empty">Nhập ít nhất 2 ký tự để tìm người thuê</div>';
          showBox();
          return;
        }
        if (!results.length) {
          list.innerHTML = '<div class="gs-empty">Không tìm thấy người thuê phù hợp</div>';
          showBox();
          return;
        }
        list.innerHTML = results.map(r => {
          const room = r.room ? `Phòng ${esc(r.room)}${r.area ? ' · ' + esc(r.area) : ''}` : 'Chưa có phòng hiệu lực';
          const contact = [r.phone ? 'SĐT: ' + r.phone : '', r.cccd ? 'CCCD: ' + r.cccd : '', r.email ? 'Email: ' + r.email : ''].filter(Boolean).join(' · ');
          const init = esc((r.name || '?').trim().charAt(0).toUpperCase());
          const typeLabel = r.typeLabel || (r.type === 'roommate' ? 'Người ở cùng' : 'Người thuê chính');
          const statusText = r.active ? 'Đang thuê' : 'Chưa thuê';
          return `<a class="gs-item" href="${esc(r.url)}">
            <div class="gs-av">${init}</div>
            <div class="gs-main">
              <div class="gs-name">${esc(r.name)} <span class="gs-pill ${r.type === 'roommate' ? 'off' : ''}">${esc(typeLabel)}</span> <span class="gs-pill ${r.active ? '' : 'off'}">${statusText}</span></div>
              <div class="gs-meta">${room}</div>
              <div class="gs-meta">${esc(contact || r.username || 'Chưa có thông tin liên hệ')}</div>
            </div>
          </a>`;
        }).join('');
        setActive(-1);
        showBox();
      }

      async function search(q) {
        if (!q) { hideBox(); return; }
        if (!modePhong && q.length < 2) { render([], q); return; }   // số phòng ngắn (VD "A1") vẫn tìm được
        list.innerHTML = '<div class="gs-empty">Đang tìm...</div>';
        showBox();
        try {
          const res = await fetch(modePhong
            ? 'index.php?controller=user&action=timPhong&q=' + encodeURIComponent(q)
            : 'index.php?controller=nguoithue&action=searchQuick&q=' + encodeURIComponent(q));
          const data = await res.json();
          render(data.ok ? (data.results || []) : [], q);
        } catch(e) {
          list.innerHTML = '<div class="gs-empty">Không tải được kết quả tìm kiếm</div>';
          showBox();
        }
      }

      input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(timer);
        timer = setTimeout(() => search(q), 220);
      });

      input.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('.gs-item');
        if (e.key === 'ArrowDown' && items.length) {
          e.preventDefault();
          setActive(Math.min(activeIndex + 1, items.length - 1));
        } else if (e.key === 'ArrowUp' && items.length) {
          e.preventDefault();
          setActive(Math.max(activeIndex - 1, 0));
        } else if (e.key === 'Enter') {
          e.preventDefault();
          if (items[activeIndex]) {
            if (items[activeIndex].href) window.location.href = items[activeIndex].href;   // phòng bị khóa (div) thì không đi đâu
          } else {
            window.location.href = urlDanhSach(input.value.trim());
          }
        } else if (e.key === 'Escape') {
          hideBox();
        }
      });

      document.addEventListener('click', (e) => {
        const wrap = document.getElementById('globalSearchBox');
        if (wrap && !wrap.contains(e.target)) hideBox();
      });
    })();
    