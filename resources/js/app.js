import './echo';

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const sidebar = document.getElementById('appSidebar');
            if (sidebar) sidebar.classList.toggle('hidden');
        });
    }

    window.showModal = (id) => document.getElementById(id)?.classList.remove('hidden');
    window.hideModal = (id) => document.getElementById(id)?.classList.add('hidden');
});