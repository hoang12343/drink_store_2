document.addEventListener("DOMContentLoaded", () => {
  // Form validation
  const productForm = document.querySelector(".product-form");
  if (productForm) {
    productForm.addEventListener("submit", (e) => {
      const code = document.querySelector("#code").value.trim();
      const name = document.querySelector("#name").value.trim();
      const price = parseFloat(document.querySelector("#price").value);
      const rating = parseFloat(document.querySelector("#rating").value);
      const category = document.querySelector("#category_id").value;

      let errors = [];

      if (!code.match(/^[A-Za-z0-9-_]+$/)) {
        errors.push(
          "Mã sản phẩm chỉ được chứa chữ, số, dấu gạch ngang hoặc gạch dưới."
        );
      }

      if (!name) {
        errors.push("Tên sản phẩm không được để trống.");
      }

      if (!category) {
        errors.push("Vui lòng chọn danh mục.");
      }

      if (isNaN(price) || price <= 0) {
        errors.push("Giá sản phẩm phải lớn hơn 0.");
      }

      if (isNaN(rating) || rating < 0 || rating > 5) {
        errors.push("Đánh giá phải từ 0 đến 5.");
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n")); // Thay bằng modal lỗi nếu cần
      }
    });
  }

  // Image preview
  const imageInput = document.querySelector("#image");
  const imagePreview = document.querySelector(".image-preview");
  if (imageInput && imagePreview) {
    imageInput.addEventListener("input", () => {
      const url = imageInput.value.trim();
      if (url) {
        imagePreview.innerHTML = `<img src="${url}" alt="Image Preview" onerror="this.parentElement.innerHTML='Không thể tải hình ảnh.'">`;
      } else {
        imagePreview.innerHTML = "";
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
    // Tạo modal
    const modal = document.createElement("div");
    modal.className = "delete-modal";
    modal.innerHTML = `
            <div class="delete-modal-content">
                <h3>Xác nhận xóa</h3>
                <p>Bạn có chắc muốn xóa sản phẩm này?</p>
                <button class="confirm-btn">Xóa</button>
                <button class="cancel-btn">Hủy</button>
            </div>
        `;

    document.body.appendChild(modal);

    // Xử lý nút xác nhận
    modal.querySelector(".confirm-btn").addEventListener("click", () => {
      form.submit();
    });

    // Xử lý nút hủy
    modal.querySelector(".cancel-btn").addEventListener("click", () => {
      modal.remove();
    });
  }

  // Quick search
  const searchInput = document.querySelector("#product-search");
  const tableRows = document.querySelectorAll(".products-table tbody tr");

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
