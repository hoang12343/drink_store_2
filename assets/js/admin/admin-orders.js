document.addEventListener("DOMContentLoaded", () => {
  // Form validation for order edit form
  const orderForm = document.querySelector(".order-form");
  if (orderForm) {
    orderForm.addEventListener("submit", (e) => {
      const status = document.querySelector("#status").value;

      let errors = [];

      if (!status) {
        errors.push("Vui lòng chọn trạng thái đơn hàng.");
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
      }
    });
  }

  // Custom delete confirmation modal
  const deleteForms = document.querySelectorAll(".delete-form");
  deleteForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      showDeleteModal(form);
    });
  });

  function showDeleteModal(form) {
    const modal = document.createElement("div");
    modal.className = "delete-modal";
    modal.innerHTML = `
        <div class="delete-modal-content">
          <h3>Xác nhận xóa</h3>
          <p>Bạn có chắc muốn xóa đơn hàng này?</p>
          <button class="confirm-btn">Xóa</button>
          <button class="cancel-btn">Hủy</button>
        </div>
      `;

    document.body.appendChild(modal);

    modal.querySelector(".confirm-btn").addEventListener("click", () => {
      form.submit();
    });

    modal.querySelector(".cancel-btn").addEventListener("click", () => {
      modal.remove();
    });
  }
});
