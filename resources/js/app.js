import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Set up a unique buyer reference in localStorage to identify the user across sessions
function getBuyerRef() {
    let ref = localStorage.getItem('buyer-ref');
    if (!ref) {
        const rand = (crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random()}`)
            .replace(/[^a-z0-9]/gi, '')
            .slice(0, 8)
            .toLowerCase();
        ref = `buyer-${rand}`;
        localStorage.setItem('buyer-ref', ref);
    }
    return ref;
}

const tokenKey = (holdId) => `release-token:${holdId}`;
const storeReleaseToken = (holdId, token) => localStorage.setItem(tokenKey(holdId), token);
const getReleaseToken = (holdId) => localStorage.getItem(tokenKey(holdId));
const clearReleaseToken = (holdId) => localStorage.removeItem(tokenKey(holdId));

Alpine.data('dashboard', () => ({
    toast: null,
    toastTone: 'info',
    toastTimer: null,
    init() {
        this.$root.addEventListener('toast', (e) => {
            this.showToast(e.detail.message, e.detail.tone);
        });
    },
    showToast(message, tone = 'info') {
        this.toast = message;
        this.toastTone = tone;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => { this.toast = null; }, 4000);
    },
}));

Alpine.data('vehicleCard', (initial) => ({
    vehicle: { id: initial.id, name: initial.name, vin: initial.vin },
    hold: initial.hold,
    apiKey: document.querySelector('meta[name="api-key"]').content,
    busy: false,
    countdown: '',
    tickHandle: null,
    init() {
        if (this.hold) this.startCountdown();
    },
 startCountdown() {
        clearInterval(this.tickHandle);
        const tick = () => {
            if (!this.hold) return;
            const remaining = Math.max(0, Math.floor((new Date(this.hold.expires_at) - new Date()) / 1000));
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            this.countdown = `${m}:${s}`;
            if (remaining === 0) {
                this.hold = null;
                clearInterval(this.tickHandle);
            }
        };
        tick();
        this.tickHandle = setInterval(tick, 1000);
    },
    async reserve() {
        this.busy = true;
        try {
            const res = await fetch('/api/v1/holds', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Api-Key': this.apiKey },
                body: JSON.stringify({ vehicle_id: this.vehicle.id, buyer_ref: getBuyerRef() }),
            });
            const body = await res.json();
            if (res.status === 201) {
                this.hold = { id: body.data.id, buyer_ref: body.data.buyer_ref, expires_at: body.data.expires_at };
                storeReleaseToken(body.data.id, body.data.release_token);
                this.startCountdown();
                this.$dispatch('toast', { message: `Reserved ${this.vehicle.name}`, tone: 'info' });
            } else if (res.status === 409) {
                const secs = body.error.details.seconds_until_expiry;
                this.$dispatch('toast', { message: `Already reserved — expires in ${secs}s`, tone: 'error' });
            } else {
                this.$dispatch('toast', { message: body.error?.message ?? `HTTP ${res.status}`, tone: 'error' });
            }
        } catch (e) {
            this.$dispatch('toast', { message: 'Network error', tone: 'error' });
        } finally {
            this.busy = false;
        }
    },
    async release() {
        if (!this.hold) return;
        this.busy = true;
        const token = getReleaseToken(this.hold.id);
        try {
            const res = await fetch(`/api/v1/holds/${this.hold.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-Api-Key': this.apiKey, 'X-Release-Token': token },
            });
            if (res.ok) {
                clearReleaseToken(this.hold.id);
                this.hold = null;
                clearInterval(this.tickHandle);
                this.$dispatch('toast', { message: `Released ${this.vehicle.name}`, tone: 'info' });
            } else {
                const body = await res.json().catch(() => ({}));
                this.$dispatch('toast', { message: body.error?.message ?? `HTTP ${res.status}`, tone: 'error' });
            }
        } catch (e) {
            this.$dispatch('toast', { message: 'Network error', tone: 'error' });
        } finally {
            this.busy = false;
        }
    },
}));

Alpine.start();