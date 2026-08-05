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

    // subscribe() is safe to call even when a subscription already exists —
    // the browser just hands back the existing one instead of re-prompting.
    // We still POST it below every time: that's what makes this self-healing
    // if the server-side row was ever deleted (e.g. ReportHandler pruning a
    // subscription after a failed delivery) — the admin doesn't have to
    // notice or do anything, it just gets recreated on next page load.
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

    if (button) {
        button.textContent = 'Notifikasi aktif';
        button.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('enable-push-notifications');

    if (!button) {
        return;
    }

    button.addEventListener('click', () => enablePushNotifications(button));

    // Re-sync on every load instead of only on the very first prompt —
    // previously this only ran once (permission === 'default'), so once
    // granted, the button just relabeled itself "active" from the browser
    // permission alone without ever re-confirming the server still has the
    // subscription. Skip only when the admin explicitly denied notifications.
    if (Notification?.permission !== 'denied') {
        enablePushNotifications(button);
    }
});
