document.addEventListener("DOMContentLoaded", function () {
  // Get sidebar elements
  const sidebar = document.querySelector(".management-sidebar");
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const sidebarClose = document.getElementById("management-sidebar-close");
  const contentArea = document.querySelector(".content.admin-page");

  // Variables for drag and toggle
  let isDragging = false;
  let startX = 0;
  let currentTranslate = 0;
  let prevTranslate = 0;
  let sidebarWidth = sidebar.offsetWidth;
  let isToggling = false; // Prevent toggle during drag

  // Function to toggle sidebar
  function toggleSidebar(event) {
    if (isDragging || isToggling) return; // Prevent toggle during drag or ongoing toggle
    isToggling = true;

    sidebar.classList.toggle("active");
    sidebarToggle.classList.toggle("active");
    document.body.classList.toggle("sidebar-open");

    // Update content margin
    if (contentArea) {
      contentArea.style.transition = "margin-left 0.3s ease-in-out";
      contentArea.style.marginLeft =
        sidebar.classList.contains("active") && window.innerWidth > 768
          ? "280px"
          : "0";
    }

    // Update sidebar transform
    sidebar.style.transition = "transform 0.3s ease-in-out";
    setSidebarTranslate(
      sidebar.classList.contains("active") ? 0 : -sidebarWidth
    );

    // Store state
    const isActive = sidebar.classList.contains("active");
    localStorage.setItem("sidebar-state", isActive ? "open" : "closed");

    // Reset toggle flag after animation
    setTimeout(() => {
      isToggling = false;
    }, 300); // Match transition duration
  }

  // Set sidebar translation
  function setSidebarTranslate(translateX) {
    currentTranslate = translateX;
    sidebar.style.transform = `translateX(${translateX}px)`;
  }

  // Handle drag start
  function dragStart(event) {
    if (window.innerWidth > 768 || isToggling) return; // Disable drag on desktop or during toggle
    isDragging = true;
    startX =
      event.type === "touchstart" ? event.touches[0].clientX : event.clientX;
    prevTranslate = currentTranslate;
    sidebar.style.transition = "none";
  }

  // Handle drag move
  function dragMove(event) {
    if (!isDragging) return;
    const currentX =
      event.type === "touchmove" ? event.touches[0].clientX : event.clientX;
    const deltaX = currentX - startX;
    let newTranslate = prevTranslate + deltaX;

    // Restrict translation within bounds
    newTranslate = Math.min(0, Math.max(-sidebarWidth, newTranslate));
    setSidebarTranslate(newTranslate);
  }

  // Handle drag end
  function dragEnd() {
    if (!isDragging) return;
    isDragging = false;
    sidebar.style.transition = "transform 0.3s ease-in-out";

    // Determine if sidebar should open or close
    const threshold = -sidebarWidth / 2;
    const isActive = currentTranslate > threshold;

    setSidebarTranslate(isActive ? 0 : -sidebarWidth);
    sidebar.classList.toggle("active", isActive);
    sidebarToggle.classList.toggle("active", isActive);
    document.body.classList.toggle("sidebar-open", isActive);
    localStorage.setItem("sidebar-state", isActive ? "open" : "closed");

    // Update content margin
    if (contentArea) {
      contentArea.style.transition = "margin-left 0.3s ease-in-out";
      contentArea.style.marginLeft =
        isActive && window.innerWidth > 768 ? "280px" : "0";
    }
  }

  // Event listeners for toggle buttons
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", toggleSidebar);
  }

  if (sidebarClose) {
    sidebarClose.addEventListener("click", toggleSidebar);
  }

  // Event listeners for drag
  sidebar.addEventListener("mousedown", dragStart);
  sidebar.addEventListener("touchstart", dragStart);
  document.addEventListener("mousemove", dragMove);
  document.addEventListener("touchmove", dragMove);
  document.addEventListener("mouseup", dragEnd);
  document.addEventListener("touchend", dragEnd);

  // Prevent default touch behavior during drag
  sidebar.addEventListener("touchmove", (e) => {
    if (isDragging) e.preventDefault();
  });

  // Initialize sidebar state
  function initSidebar() {
    const mediaQueryMobile = window.matchMedia("(max-width: 768px)");
    const savedState = localStorage.getItem("sidebar-state");

    // Update sidebar width
    sidebarWidth = sidebar.offsetWidth;

    if (mediaQueryMobile.matches) {
      // Mobile: closed by default unless saved as open
      const isActive = savedState === "open";
      sidebar.classList.toggle("active", isActive);
      sidebarToggle.classList.toggle("active", isActive);
      document.body.classList.toggle("sidebar-open", isActive);
      setSidebarTranslate(isActive ? 0 : -sidebarWidth);
    } else {
      // Desktop: open by default unless saved as closed
      const isActive = savedState !== "closed";
      sidebar.classList.toggle("active", isActive);
      sidebarToggle.classList.toggle("active", isActive);
      document.body.classList.toggle("sidebar-open", isActive);
      setSidebarTranslate(0);
    }

    // Update content margin
    if (contentArea) {
      contentArea.style.marginLeft =
        sidebar.classList.contains("active") && !mediaQueryMobile.matches
          ? "280px"
          : "0";
    }
  }

  // Handle window resize
  window.addEventListener("resize", function () {
    sidebarWidth = sidebar.offsetWidth;
    const mediaQueryMobile = window.matchMedia("(max-width: 768px)");
    const savedState = localStorage.getItem("sidebar-state");

    if (!mediaQueryMobile.matches) {
      // Desktop: open unless explicitly closed
      const isActive = savedState !== "closed";
      sidebar.classList.toggle("active", isActive);
      sidebarToggle.classList.toggle("active", isActive);
      document.body.classList.toggle("sidebar-open", isActive);
      setSidebarTranslate(0);
    } else {
      // Mobile: respect saved state
      const isActive = savedState === "open";
      sidebar.classList.toggle("active", isActive);
      sidebarToggle.classList.toggle("active", isActive);
      document.body.classList.toggle("sidebar-open", isActive);
      setSidebarTranslate(isActive ? 0 : -sidebarWidth);
    }

    // Update content margin
    if (contentArea) {
      contentArea.style.transition = "margin-left 0.3s ease-in-out";
      contentArea.style.marginLeft =
        sidebar.classList.contains("active") && !mediaQueryMobile.matches
          ? "280px"
          : "0";
    }
  });

  // Initialize sidebar
  initSidebar();
});
