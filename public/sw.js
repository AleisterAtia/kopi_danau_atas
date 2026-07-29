// Service worker for browser push notifications (admin panel only).
// Kept as a plain static file at the origin root — not bundled by Vite —
// so its scope covers the whole site rather than just /build/.

self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(data.title || 'Notifikasi', {
            body: data.body,
            icon: data.icon || '/favicon.ico',
            data: data.data || {},
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data && event.notification.data.url;
    if (url) {
        event.waitUntil(clients.openWindow(url));
    }
});
