document.addEventListener('DOMContentLoaded', () => {
    const userMenu = document.getElementById('userMenu');
    if (!userMenu) return;

    const toggle = userMenu.querySelector('.user-toggle');
    const dropdown = userMenu.querySelector('.dropdown-menu');

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
        if (!userMenu.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    toggle.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
    });
});