import './echo';
import Sortable from 'sortablejs';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

let sortableInstances = [];
let sortableRefreshTimer = null;
let sortableMoveBusy = false;
let sortableDragging = false;
let sortableRefreshPending = false;
let livewireHookRegistered = false;

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
                        .catch(() => {})
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

if (window.Livewire) {
    registerLivewireHooks();
} else {
    document.addEventListener('livewire:init', registerLivewireHooks, { once: true });
}

window.addEventListener('DOMContentLoaded', scheduleOrderBoardSortables);

document.addEventListener('DOMContentLoaded', () => {
    window.showModal = (id) => document.getElementById(id)?.classList.remove('hidden');
    window.hideModal = (id) => document.getElementById(id)?.classList.add('hidden');
});
