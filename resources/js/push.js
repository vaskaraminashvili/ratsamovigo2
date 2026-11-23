/**
 * Device push for the settings page. Subscribing has to follow a tap: iOS only shows
 * the permission prompt from a user gesture, and only for an app installed to the
 * Home Screen (iOS 16.4+).
 */

const messages = {
    checking: 'მოწმდება…',
    unsupported: 'ამ ბრაუზერში შეტყობინებები არ მუშაობს.',
    install: 'iPhone-ზე ჯერ დაამატე აპი მთავარ ეკრანზე: გაზიარება → „მთავარ ეკრანზე დამატება“.',
    off: 'ამ მოწყობილობაზე შეტყობინებები გამორთულია. ჩართე, რომ სიის ცვლილებები აქედან მოგივიდეს.',
    on: 'ამ მოწყობილობაზე შეტყობინებები ჩართულია.',
    denied: 'შეტყობინებები დაბლოკილია. ჩართე ბრაუზერის პარამეტრებში.',
    error: 'ვერ მოხერხდა. სცადე ხელახლა.',
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const ensureOk = (response) => {
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response;
};

const isIos = () => /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

const toUint8Array = (base64Url) => {
    const padding = '='.repeat((4 - (base64Url.length % 4)) % 4);
    const base64 = (base64Url + padding).replace(/-/g, '+').replace(/_/g, '/');

    return Uint8Array.from(atob(base64), (char) => char.charCodeAt(0));
};

const sendSubscription = (method, body) => fetch('/push-subscriptions', {
    method,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    body: JSON.stringify(body),
}).then(ensureOk);

window.pushSettings = function (publicKey) {
    return {
        publicKey,
        status: 'checking',
        busy: false,

        get message() {
            return messages[this.status];
        },

        async init() {
            if (!publicKey || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                this.status = 'unsupported';

                return;
            }

            if (isIos() && !isStandalone()) {
                this.status = 'install';

                return;
            }

            if (Notification.permission === 'denied') {
                this.status = 'denied';

                return;
            }

            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            this.status = subscription ? 'on' : 'off';
        },

        async enable() {
            this.busy = true;

            try {
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    this.status = permission === 'denied' ? 'denied' : 'off';

                    return;
                }

                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: toUint8Array(this.publicKey),
                });

                await sendSubscription('POST', subscription.toJSON());

                this.status = 'on';
            } catch (error) {
                this.status = 'error';
            } finally {
                this.busy = false;
            }
        },

        async disable() {
            this.busy = true;

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    await sendSubscription('DELETE', { endpoint: subscription.endpoint });
                    await subscription.unsubscribe();
                }

                this.status = 'off';
            } catch (error) {
                this.status = 'error';
            } finally {
                this.busy = false;
            }
        },
    };
};
