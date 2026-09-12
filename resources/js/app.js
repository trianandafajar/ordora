import './echo';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

window.Alpine = Alpine;

function registerOrderBoardAlpineComponent() {
    if (Alpine.data.hasOwnProperty('orderBoardRealtimeState')) return;

    Alpine.data('orderBoardRealtimeState', () => ({
        now: new Date(),
        timer: null,
        soundEnabled: window.localStorage.getItem('ordora.soundEnabled') === 'true',
        toastMessage: '',
        toastTimer: null,
        realtimeHandler: null,
        formattedNow() {
            return this.now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        },
        init() {
            this.timer = window.setInterval(() => { this.now = new Date(); }, 1000);
            this.realtimeHandler = (event) => this.handleRealtime(event.detail);
            window.addEventListener('order-realtime', this.realtimeHandler);
        },
        destroy() {
            window.clearInterval(this.timer);
            window.removeEventListener('order-realtime', this.realtimeHandler);
            window.clearTimeout(this.toastTimer);
        },
        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            window.localStorage.setItem('ordora.soundEnabled', String(this.soundEnabled));
            if (this.soundEnabled) this.playSound();
        },
        playSound() {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const context = new AudioContext();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.value = 880;
            gain.gain.setValueAtTime(0.0001, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.12, context.currentTime + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.18);
            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.2);
        },
        handleRealtime(payload) {
            const order = payload?.order;
            if (!order) return;
            this.toastMessage = payload.change_type === 'created'
                ? `Pesanan baru #${order.id} masuk.`
                : `Order #${order.id} berubah ke ${order.status}.`;
            window.clearTimeout(this.toastTimer);
            this.toastTimer = window.setTimeout(() => { this.toastMessage = ''; }, 4200);
            if (payload.change_type === 'created' && this.soundEnabled) this.playSound();
        },
    }));
}

function sidebarState() {
    return {
        sidebarOpen: false,
        hoverOpen: false,
        mobileOpen: false,
        showLogoutModal: false,
        init() {
            const mql = window.matchMedia('(min-width: 1024px)');
            this.sidebarOpen = mql.matches;

            if (mql.addEventListener) {
                mql.addEventListener('change', (e) => {
                    this.sidebarOpen = e.matches;
                    if (!e.matches) {
                        this.hoverOpen = false;
                    }
                });
            }
        }
    };
}

Alpine.data('sidebarState', sidebarState);
registerOrderBoardAlpineComponent();

Alpine.start();
