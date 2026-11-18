const CACHE_NAME = 'attendance-app-v1';
const urlsToCache = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    if (event.request.headers.get('X-Inertia') === 'true' && event.request.method === 'GET') {
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetch(event.request))
        );
    } else {
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetch(event.request))
        );
    }
});
