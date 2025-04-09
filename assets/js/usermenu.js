document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('userMenuToggle');
    const dropdown = document.getElementById('userDropdown');

    if (toggle && dropdown) {
        toggle.addEventListener('click', function() {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Đóng menu khi nhấp ra ngoài
        document.addEventListener('click', function(event) {
            if (!toggle.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    }
});