import './echo';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

if (!window.Alpine) {
    window.Alpine = Alpine;
}

window.sidebarState = sidebarState;

let sortableInstances = [];
let sortableRefreshTimer = null;
let sortableMoveBusy = false;
let sortableDragging = false;
let sortableRefreshPending = false;

function registerAlpineComponents() {
    const A = window.Alpine;
    if (A.data.hasOwnProperty('sidebarState')) {
        A.data('sidebarState', sidebarState);
    }
    registerOrderBoardAlpineComponent();
}

function registerOrderBoardAlpineComponent() {
    const A = window.Alpine;
    if (A.data.hasOwnProperty('orderBoardRealtimeState')) return;

    A.data('orderBoardRealtimeState', () => ({
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

function destroyOrderBoardSortables() {
    if (sortableDragging) {
        sortableRefreshPending = true;
        return;
    }

    sortableInstances.forEach((instance) => instance.destroy());
    sortableInstances = [];
}

function initOrderBoardSortables() {
    const roots = [...document.querySelectorAll('[data-order-board]')];

    if (roots.length === 0) return;

    if (sortableDragging) {
        sortableRefreshPending = true;
        return;
    }

    destroyOrderBoardSortables();

    roots.forEach((root) => {
        root.querySelectorAll('[data-order-column]').forEach((column) => {
            sortableInstances.push(Sortable.create(column, {
                group: { name: 'orders', pull: true, put: true },
                animation: 150,
                draggable: '[data-order-id]',
                onStart: () => {
                    sortableDragging = true;
                },
                onEnd: ({ item, from, to }) => {
                    sortableDragging = false;

                    const orderId = Number(item.dataset.orderId);
                    const targetStatus = to.dataset.orderColumn;

                    if (!orderId || !targetStatus || from === to || sortableMoveBusy) {
                        if (sortableRefreshPending) window.setTimeout(initOrderBoardSortables, 0);
                        sortableRefreshPending = false;
                        return;
                    }

                    const componentRoot = root.closest('[wire\\:id]');
                    const componentId = componentRoot?.getAttribute('wire:id');
                    const component = componentId && window.Livewire?.find(componentId);

                    if (!component) return;

                    sortableMoveBusy = true;
                    component.moveOrder(orderId, targetStatus)
                        .catch(() => { })
                        .finally(() => {
                            sortableMoveBusy = false;
                            sortableRefreshPending = false;
                            window.setTimeout(initOrderBoardSortables, 0);
                        });
                },
            }));
        });
    });
}

function scheduleOrderBoardSortables() {
    window.clearTimeout(sortableRefreshTimer);
    sortableRefreshTimer = window.setTimeout(() => {
        if (sortableDragging || sortableMoveBusy) {
            sortableRefreshPending = true;
            return;
        }

        initOrderBoardSortables();
    }, 0);
}

function registerLivewireHooks() {
    if (!window.Livewire) return;

    window.Livewire.hook('morph.updated', ({ el }) => {
        if (el.matches?.('[data-order-board]') || el.querySelector?.('[data-order-board]')) {
            scheduleOrderBoardSortables();
        }
    });

    scheduleOrderBoardSortables();
}

document.addEventListener('livewire:init', () => {
    registerAlpineComponents();
    registerLivewireHooks();
});

if (!window.Livewire) {
    document.addEventListener('DOMContentLoaded', () => {
        registerAlpineComponents();
        window.Alpine.start();
    });
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
