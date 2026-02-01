/**
 *  With service worker registered:
 *   - Offline access - Scanner page loads even without internet (after first visit)
 *   - Faster loads - Cached assets serve instantly from local storage
 *   - Install prompt - Users can "Add to Home Screen" with full PWA experience
 *   - Background sync - Could queue checkouts/returns when offline (not currently implemented)
 *
 *   Without service worker (current state):
 *   - Online only - Scanner requires internet connection every time
 *   - Network dependent - Each visit re-downloads Tailwind, Alpine.js, jsQR from CDN
 *   - Basic PWA - Manifest still works for home screen icon/theme, but no offline support
 *
 *  You can verify it's working in browser DevTools → Application → Service Workers.
 * @type {string}
 */
const CACHE_NAME = 'tool-scanner-v1';
const urlsToCache = [
    '/scanner',
    '/scanner/manifest.json',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js'
];

// Install event - cache resources
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Cache hit - return response
                if (response) {
                    return response;
                }

                return fetch(event.request).then(
                    response => {
                        // Check if valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(cache => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    }
                );
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];

    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
