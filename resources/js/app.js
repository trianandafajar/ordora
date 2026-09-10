import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    window.showModal = (id) => document.getElementById(id)?.classList.remove('hidden');
    window.hideModal = (id) => document.getElementById(id)?.classList.add('hidden');
});