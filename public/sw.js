/* რა წამოვიღო — service worker.
 *  - Static assets (build, fonts, icons): cache first, refreshed in the background.
 *  - Pages: network first. Signed-in list pages are kept so the last seen list can be read offline.
 *  - Anything else offline falls back to /offline.html.
 * Livewire updates (POST) and cross-origin requests are never touched.
 */
const VERSION = 'v1';
const ASSETS = `rw-assets-${VERSION}`;
const PAGES = `rw-pages-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const CACHEABLE_PAGE = /^\/(lists(\/\d+)?|settings\/profile)$/;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(ASSETS).then((cache) => cache.addAll([OFFLINE_URL, '/manifest.webmanifest', '/icons/icon-192.png'])).then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => ![ASSETS, PAGES].includes(key)).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'clear-pages') {
        event.waitUntil(caches.delete(PAGES));
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigation(request, url));

        return;
    }

    if (/^\/(build|fonts|icons)\//.test(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

async function handleNavigation(request, url) {
    try {
        const response = await fetch(request);

        if (response.ok && !response.redirected && CACHEABLE_PAGE.test(url.pathname)) {
            const cache = await caches.open(PAGES);
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request, { cacheName: PAGES });

        return cached || (await caches.match(OFFLINE_URL)) || Response.error();
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(ASSETS);
    const cached = await cache.match(request);

    const network = fetch(request)
        .then((response) => {
            if (response.ok) {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cached);

    return cached || network;
}
