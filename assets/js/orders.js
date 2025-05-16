document.addEventListener("DOMContentLoaded", function () {
  console.log("orders.js loaded at", new Date().toISOString());
  initOrderModals();
  initMessages(); // Thêm hàm khởi tạo thông báo
});

/**
 * Khởi tạo các modal xác nhận cho trang đơn hàng
 */
function initOrderModals() {
  // Xử lý form xác nhận đơn hàng
  const confirmForms = document.querySelectorAll(
    'form[action="processes/confirm_order.php"]'
  );
  const confirmModal = document.getElementById("confirm-order-modal");
  let currentConfirmForm = null;

  if (confirmForms.length > 0 && confirmModal) {
    confirmForms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        currentConfirmForm = form;
        confirmModal.style.display = "flex";
      });
    });

    // Xử lý nút xác nhận trong modal xác nhận đơn hàng
    document
      .getElementById("confirm-order-btn")
      .addEventListener("click", function () {
        if (currentConfirmForm) {
          currentConfirmForm.submit();
        }
        confirmModal.style.display = "none";
      });

    // Xử lý nút hủy trong modal xác nhận đơn hàng
    document
      .getElementById("cancel-confirm-btn")
      .addEventListener("click", function () {
        confirmModal.style.display = "none";
      });
  }

  // Xử lý form hủy đơn hàng
  const cancelForms = document.querySelectorAll(
    'form[action="processes/cancel_order.php"]'
  );
  const cancelModal = document.getElementById("cancel-order-modal");
  let currentCancelForm = null;

  if (cancelForms.length > 0 && cancelModal) {
    cancelForms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        currentCancelForm = form;
        cancelModal.style.display = "flex";
      });
    });

    // Xử lý nút xác nhận trong modal hủy đơn hàng
    document
      .getElementById("confirm-cancel-btn")
      .addEventListener("click", function () {
        if (currentCancelForm) {
          currentCancelForm.submit();
        }
        cancelModal.style.display = "none";
      });

    // Xử lý nút hủy trong modal hủy đơn hàng
    document
      .getElementById("cancel-cancel-btn")
      .addEventListener("click", function () {
        cancelModal.style.display = "none";
      });
  }

  // Đóng modal khi click bên ngoài
  window.addEventListener("click", function (event) {
    if (confirmModal && event.target === confirmModal) {
      confirmModal.style.display = "none";
    }
    if (cancelModal && event.target === cancelModal) {
      cancelModal.style.display = "none";
    }
  });

  // Đóng modal khi nhấn phím Escape
  window.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      if (confirmModal) confirmModal.style.display = "none";
      if (cancelModal) cancelModal.style.display = "none";
    }
  });
}

/**
 * Khởi tạo thông báo động giống product-detail.js
 */
function initMessages() {
  // Lấy thông báo từ biến toàn cục được truyền từ PHP
  const message = window.orderMessage?.message;
  const type = window.orderMessage?.type;

  if (!message) {
    return;
  }

  // Tạo thông báo động
  const notification = document.createElement("div");
  notification.className = `cart-notification ${
    type === "error" ? "error" : "success"
  }`;
  notification.textContent = message;
  document.body.appendChild(notification);

  // Thêm hiệu ứng fade-out sau 4.5s
  setTimeout(() => {
    notification.classList.add("fade-out");
    // Xóa phần tử sau 5s
    setTimeout(() => {
      notification.remove();
    }, 500); // Thời gian fade-out là 0.5s
  }, 4500); // Bắt đầu fade-out sau 4.5s
}
