const CACHE_NAME = 'siganteng-cache-v1';
const urlsToCache = [
  './',
  './login.php',
  './logo.png',
  './manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  // Strategi Network First (Biar data selalu update, kalau offline baru ambil cache)
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});