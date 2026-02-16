const CACHE_NAME = 'material-icons-v1';
const ICONS_URLS = [
  'https://fonts.googleapis.com/icon?family=Material+Icons',
  'https://fonts.gstatic.com/s/materialicons/v126/flUhRq6tzZclQEJ-Vdg-IuiaDsNc.woff2'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ICONS_URLS))
  );
});

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  if (ICONS_URLS.includes(url.href)) {
    event.respondWith(
      caches.match(event.request).then(res => res || fetch(event.request))
    );
  }
});
