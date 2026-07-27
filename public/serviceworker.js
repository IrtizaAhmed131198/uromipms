var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/css/app.css',
    '/js/app.js',
    '/js/vendor.js',
    '/js/common.js',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                // Add files one by one to handle failures gracefully
                return Promise.allSettled(
                    filesToCache.map(url => 
                        cache.add(url).catch(err => {
                            console.log('Failed to cache:', url, err);
                            return null;
                        })
                    )
                );
            })
            .then(() => {
                console.log('Service Worker: Cache installation completed');
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    // Don't cache manifest.json - always fetch fresh
    if (event.request.url.includes('manifest.json')) {
        event.respondWith(fetch(event.request));
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});