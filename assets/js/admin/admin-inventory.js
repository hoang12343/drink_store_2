document.addEventListener("DOMContentLoaded", () => {
  // Form validation
  const inventoryForm = document.querySelector(".inventory-form");
  if (inventoryForm) {
    inventoryForm.addEventListener("submit", (e) => {
      const product_id = document.querySelector("#product_id").value;
      const quantity = parseInt(document.querySelector("#quantity").value);
      const location = document.querySelector("#location").value.trim();

      let errors = [];

      if (!product_id) {
        errors.push("Vui lòng chọn sản phẩm.");
      }

      if (isNaN(quantity) || quantity < 0) {
        errors.push("Số lượng phải lớn hơn hoặc bằng 0.");
      }

      if (!location) {
        errors.push("Vị trí kho không được để trống.");
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n")); // Thay bằng modal lỗi nếu cần
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
                <p>Bạn có chắc muốn xóa bản ghi kho này?</p>
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

  // Quick search
  const searchInput = document.querySelector("#inventory-search");
  const tableRows = document.querySelectorAll(".inventory-table tbody tr");

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const searchTerm = searchInput.value.trim().toLowerCase();

      tableRows.forEach((row) => {
        const code = row
          .querySelector("td:nth-child(1)")
          .textContent.toLowerCase();
        const name = row
          .querySelector("td:nth-child(2)")
          .textContent.toLowerCase();

        if (code.includes(searchTerm) || name.includes(searchTerm)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });
  }
});
