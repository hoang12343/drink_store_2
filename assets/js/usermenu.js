document.addEventListener("DOMContentLoaded", () => {
  const userToggle = document.querySelector(".user-toggle");
  const dropdownMenu = document.querySelector(".dropdown-menu");
  const dropdownSubmenu = document.querySelector(".dropdown-submenu");
  const submenuHeader = document.querySelector(".submenu-header");

  if (userToggle && dropdownMenu) {
    // Toggle main dropdown
    userToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle("show");
      // Close submenu when toggling main menu
      if (dropdownSubmenu) {
        dropdownSubmenu.classList.remove("active");
      }
    });

    // Close dropdown and submenu when clicking outside
    document.addEventListener("click", (e) => {
      if (!userToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove("show");
        if (dropdownSubmenu) {
          dropdownSubmenu.classList.remove("active");
        }
      }
    });
  }

  // Handle submenu toggle
  if (submenuHeader && dropdownSubmenu) {
    submenuHeader.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdownSubmenu.classList.toggle("active");
    });

    // Close submenu when clicking outside
    document.addEventListener("click", (e) => {
      if (
        !submenuHeader.contains(e.target) &&
        !dropdownSubmenu.contains(e.target)
      ) {
        dropdownSubmenu.classList.remove("active");
      }
    });
  }
});
