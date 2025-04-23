document.addEventListener("DOMContentLoaded", () => {
  // Form validation
  const userForm = document.querySelector(".user-form");
  if (userForm) {
    userForm.addEventListener("submit", (e) => {
      const fullName = document.querySelector("#full_name").value.trim();
      const username = document.querySelector("#username").value.trim();
      const email = document.querySelector("#email").value.trim();
      const phone = document.querySelector("#phone").value.trim();
      const password = document.querySelector("#password").value.trim();
      const isEdit =
        document.querySelector("input[name='action']").value === "edit";

      let errors = [];

      if (!fullName) {
        errors.push("Họ và tên không được để trống.");
      }

      if (!username.match(/^[A-Za-z0-9_]+$/)) {
        errors.push("Tên đăng nhập chỉ được chứa chữ, số và dấu gạch dưới.");
      }

      if (!email.match(/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/)) {
        errors.push("Email không hợp lệ.");
      }

      if (!phone.match(/^\d{10,15}$/)) {
        errors.push("Số điện thoại phải chứa 10-15 chữ số.");
      }

      if (!isEdit && !password) {
        errors.push("Mật khẩu không được để trống khi thêm mới.");
      }

      if (password && password.length < 8) {
        errors.push("Mật khẩu phải có ít nhất 8 ký tự.");
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
          <p>Bạn có chắc muốn xóa user này?</p>
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
  const searchInput = document.querySelector("#user-search");
  const tableRows = document.querySelectorAll(".users-table tbody tr");

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const searchTerm = searchInput.value.trim().toLowerCase();

      tableRows.forEach((row) => {
        const fullName = row
          .querySelector("td:nth-child(2)")
          .textContent.toLowerCase();
        const email = row
          .querySelector("td:nth-child(4)")
          .textContent.toLowerCase();

        if (fullName.includes(searchTerm) || email.includes(searchTerm)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });
  }
});
