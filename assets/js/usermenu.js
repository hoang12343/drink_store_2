document.addEventListener("DOMContentLoaded", () => {
  const userToggle = document.querySelector(".user-toggle");
  const dropdownMenu = document.querySelector(".dropdown-menu");

  if (userToggle && dropdownMenu) {
    userToggle.addEventListener("click", () => {
      dropdownMenu.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!userToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove("show");
      }
    });
  }
});
