document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".contact-form form");
  const inputs = form.querySelectorAll("input[required], textarea[required]");
  const emailInput = form.querySelector("#email");
  const submitBtn = form.querySelector(".submit-btn");
  let formMessage = document.querySelector(".form-message");

  // Xóa thông báo mặc định nếu có
  if (formMessage) {
    setTimeout(() => formMessage.remove(), 5000);
  }

  // Hàm kiểm tra định dạng email
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // Xử lý sự kiện submit
  form.addEventListener("submit", (e) => {
    let hasError = false;
    let errorMessage = "";

    // Kiểm tra các trường bắt buộc
    inputs.forEach((input) => {
      if (!input.value.trim()) {
        hasError = true;
        input.classList.add("error");
        errorMessage = "Vui lòng điền đầy đủ các trường bắt buộc.";
      } else {
        input.classList.remove("error");
      }
    });

    // Kiểm tra định dạng email
    if (emailInput.value && !isValidEmail(emailInput.value)) {
      hasError = true;
      emailInput.classList.add("error");
      errorMessage = "Vui lòng nhập địa chỉ email hợp lệ.";
    }

    // Nếu có lỗi, ngăn gửi form và hiển thị thông báo
    if (hasError) {
      e.preventDefault();
      if (!formMessage) {
        formMessage = document.createElement("div");
        formMessage.className = "form-message error";
        form.insertBefore(formMessage, form.firstChild);
      }
      formMessage.textContent = errorMessage;
      formMessage.className = "form-message error";
      setTimeout(() => formMessage.remove(), 5000);
    }
  });

  // Xóa lỗi khi người dùng nhập lại
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      input.classList.remove("error");
      if (formMessage && formMessage.className.includes("error")) {
        formMessage.remove();
      }
    });
  });
});
