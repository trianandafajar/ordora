import './echo';
import Sortable from 'sortablejs';

let sortableInstances = [];
let sortableRefreshTimer = null;
let sortableMoveBusy = false;
let sortableDragging = false;
let sortableRefreshPending = false;
let livewireHookRegistered = false;
let orderBoardAlpineRegistered = false;

function registerOrderBoardAlpineComponent() {
    const Alpine = window.Alpine;

    if (orderBoardAlpineRegistered || !Alpine) return;

    orderBoardAlpineRegistered = true;
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
    if (livewireHookRegistered || !window.Livewire) return;

    livewireHookRegistered = true;
    window.Livewire.hook('morph.updated', ({ el }) => {
        if (el.matches?.('[data-order-board]') || el.querySelector?.('[data-order-board]')) {
            scheduleOrderBoardSortables();
        }
    });

    scheduleOrderBoardSortables();
}

document.addEventListener('alpine:init', registerOrderBoardAlpineComponent, { once: true });
document.addEventListener('livewire:init', () => {
    registerOrderBoardAlpineComponent();
    registerLivewireHooks();
}, { once: true });

registerOrderBoardAlpineComponent();
registerLivewireHooks();

window.addEventListener('DOMContentLoaded', scheduleOrderBoardSortables);

document.addEventListener('DOMContentLoaded', () => {
    window.showModal = (id) => document.getElementById(id)?.classList.remove('hidden');
    window.hideModal = (id) => document.getElementById(id)?.classList.add('hidden');
});

Alpine.start();
