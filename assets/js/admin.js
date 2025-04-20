document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".management-sidebar");
  const closeBtn = document.querySelector("#management-sidebar-close");
  const toggleBtn = document.querySelector("#sidebar-toggle");
  const content = document.querySelector(".content.admin-page");

  if (!sidebar || !toggleBtn) {
    console.warn("Sidebar or toggle button not found");
    return;
  }

  // Toggle sidebar
  const toggleSidebar = () => {
    sidebar.classList.toggle("active");
    updateContentMargin();
    updateToggleIcon();
  };

  // Update content margin based on sidebar state
  const updateContentMargin = () => {
    if (content) {
      if (sidebar.classList.contains("active") || window.innerWidth > 768) {
        content.style.marginLeft = "280px";
      } else {
        content.style.marginLeft = "0";
      }
      content.style.marginTop = "60px"; // Fixed header height
    }
  };

  // Update toggle button icon
  const updateToggleIcon = () => {
    toggleBtn.innerHTML = sidebar.classList.contains("active")
      ? '<i class="fas fa-times"></i>'
      : '<i class="fas fa-bars"></i>';
  };

  // Initial setup
  updateContentMargin();
  updateToggleIcon();

  // Toggle button click
  toggleBtn.addEventListener("click", toggleSidebar);

  // Close button click
  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      sidebar.classList.remove("active");
      updateContentMargin();
      updateToggleIcon();
    });
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", (e) => {
    if (
      window.innerWidth <= 768 &&
      !e.target.closest(".management-sidebar") &&
      !e.target.closest("#sidebar-toggle") &&
      sidebar.classList.contains("active")
    ) {
      sidebar.classList.remove("active");
      updateContentMargin();
      updateToggleIcon();
    }
  });

  // Update margin on window resize
  window.addEventListener("resize", updateContentMargin);
});
