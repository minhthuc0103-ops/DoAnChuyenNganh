/* Service worker RoomManager.
 * Nguyên tắc: trang PHP (có session, CSRF, dữ liệu cá nhân, ảnh CCCD) KHÔNG BAO GIỜ được cache.
 * Chỉ cache file tĩnh trong frontend/assets/. Mất mạng -> hiện offline.html.
 * Đổi VERSION khi muốn ép trình duyệt xoá cache cũ. */
const VERSION = 'v2';
const CACHE = 'roommanager-static-' + VERSION;
const OFFLINE_URL = 'offline.html';
const PRECACHE = [
  OFFLINE_URL,
  'manifest.json',
  'frontend/assets/icons/icon-192.png',
  'frontend/assets/icons/icon-512.png'
];
const ASSET_PREFIX = new URL('frontend/assets/', self.location).pathname;

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k.startsWith('roommanager-') && k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;                           // POST (form, upload) đi thẳng mạng
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;            // CDN, Google Fonts: để trình duyệt tự xử lý

  if (req.mode === 'navigate') {                              // mở trang: luôn lấy từ server
    e.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  if (url.pathname.startsWith(ASSET_PREFIX)) {                // CSS/JS/icon: dùng cache, cập nhật ngầm
    e.respondWith(
      caches.open(CACHE).then((cache) =>
        cache.match(req).then((hit) => {
          const net = fetch(req)
            .then((res) => { if (res.ok) cache.put(req, res.clone()); return res; })
            .catch(() => hit);
          e.waitUntil(net.catch(() => {}));
          return hit || net;
        })
      )
    );
  }
  // còn lại (AJAX chat/AI, public/uploads...) -> không can thiệp
});