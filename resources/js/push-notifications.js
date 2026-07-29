// "Aktifkan Notifikasi" button in the admin topbar (see AdminPanelProvider) —
// subscribes the browser to Web Push and stores the subscription server-side
// so MidtransService can reach this admin even with no tab open.

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');

    return Uint8Array.from([...atob(base64)].map((c) => c.charCodeAt(0)));
}

async function enablePushNotifications(button) {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        alert('Browser ini tidak mendukung notifikasi push.');
        return;
    }

    if (await Notification.requestPermission() !== 'granted') {
        return;
    }

    const registration = await navigator.serviceWorker.register('/sw.js');
    const vapidKey = document.querySelector('meta[name="vapid-public-key"]').content;
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidKey),
    });

    await fetch('/push-subscription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(subscription),
    });

    button.textContent = 'Notifikasi aktif';
    button.disabled = true;
}

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('enable-push-notifications');

    if (!button) {
        return;
    }

    if (Notification?.permission === 'granted') {
        button.textContent = 'Notifikasi aktif';
        button.disabled = true;
    }

    button.addEventListener('click', () => enablePushNotifications(button));
});
