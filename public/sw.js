// ════════════════════════════════════════════════════════════
//  NewsHub Service Worker  v1.0
//  Handles: PWA caching + Push notifications (disguised as news)
// ════════════════════════════════════════════════════════════

const CACHE_NAME   = 'newshub-v1.2';
const STATIC_CACHE = [
    '/',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// ── Install ───────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_CACHE))
    );
    self.skipWaiting();
});

// ── Activate ──────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ── Fetch (cache-first for static, network-first for API) ────
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET and chrome-extension requests
    if (event.request.method !== 'GET') return;
    if (url.protocol === 'chrome-extension:') return;

    // Network-first for API / dynamic routes
    if (url.pathname.startsWith('/_h') ||
        url.pathname.startsWith('/chat') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/broadcasting')) {
        return; // Let browser handle it normally
    }

    // Cache-first for static assets
    event.respondWith(
        caches.match(event.request).then(cached => {
            if (cached) return cached;
            return fetch(event.request).then(response => {
                // Cache successful responses for static assets only
                if (response.ok && (url.pathname.match(/\.(js|css|png|jpg|ico|woff2?)$/))) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                }
                return response;
            });
        }).catch(() => {
            // Offline fallback for HTML pages
            if (event.request.headers.get('accept')?.includes('text/html')) {
                return caches.match('/');
            }
        })
    );
});

// ── Push Notification ─────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {
        title:  '📰 Breaking News Alert',
        body:   'New update available — tap to read latest news',
        icon:   '/icons/icon-192.png',
        badge:  '/icons/icon-192.png',
        data:   { url: '/', type: 'news' },
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            
            // Handle Incoming Call specially
            if (payload.data?.type === 'incoming_call') {
                data = {
                    title: payload.title,
                    body: payload.body,
                    icon: payload.icon || '/icons/icon-192.png',
                    badge: payload.badge || '/icons/badge-72.png',
                    tag: 'incoming_call_' + payload.data.sender_id,
                    renotify: true,
                    silent: false,
                    requireInteraction: true,
                    data: payload.data,
                    // Long repeated vibration for ringing effect
                    vibrate: [500, 250, 500, 250, 500, 250, 500, 250, 500, 1000, 500, 250, 500, 250, 500],
                    actions: [
                        { action: 'answer', title: '📞 Answer' },
                        { action: 'decline', title: '❌ Decline' }
                    ]
                };
            } else {
                // Always use news-disguised title/body for normal chat messages
                data = {
                    title:  payload.title  || data.title,
                    body:   payload.body   || data.body,
                    icon:   payload.icon   || data.icon,
                    badge:  payload.badge  || data.badge,
                    tag:    payload.data?.type === 'message' ? 'msg-' + payload.data.sender_id : 'news',
                    renotify: true,
                    silent: false,
                    requireInteraction: true,
                    data:   payload.data   || data.data,
                    vibrate: [100, 50, 100],
                    actions: [
                        { action: 'open',    title: 'Read Now' },
                        { action: 'dismiss', title: 'Dismiss'  },
                    ],
                };
            }
        } catch (e) {}
    }

    event.waitUntil(
        self.registration.showNotification(data.title, data)
    );
});

// ── Notification Click ────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss' || event.action === 'decline') return;

    // Open chat directly if it's a call, otherwise open news page (for disguised chat)
    const type = event.notification.data?.type;
    const targetUrl = (type === 'incoming_call') ? '/chat' : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            return clients.openWindow(targetUrl);
        })
    );
});

// ── Background Sync (optional, for offline message queue) ────
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-messages') {
        // Could implement offline message queue here
        console.log('[SW] Background sync: messages');
    }
});
