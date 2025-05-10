document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("contactForm");
  if (!form) {
    console.warn("Contact form not found");
    return;
  }

  const inputs = form.querySelectorAll("input[required], textarea[required]");
  const emailInput = form.querySelector("#email");
  const submitBtn = form.querySelector(".submit-btn");

  // Hàm hiển thị thông báo
  const showMessage = (message, type) => {
    console.log(`Showing message: ${message} (${type})`);

    // Xóa thông báo cũ
    const existingMessage = document.querySelector(".form-message");
    if (existingMessage) {
      existingMessage.remove();
    }

    // Tạo thông báo mới
    const messageDiv = document.createElement("div");
    messageDiv.className = `form-message ${type}`;
    messageDiv.textContent = message;

    // Chèn vào đầu form container
    const formContainer = form.closest(".contact-form");
    formContainer.insertBefore(messageDiv, formContainer.firstChild);

    // Tự động xóa sau 5 giây
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.remove();
      }
    }, 5000);

    // Cuộn lên đầu
    window.scrollTo(0, 0);
  };

  // Hàm kiểm tra định dạng email
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // Xử lý submit form
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("Form submission started");

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

    if (hasError) {
      showMessage(errorMessage, "error");
      return;
    }

    // Gửi AJAX
    const formData = new FormData(form);
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Đang gửi...";
    submitBtn.disabled = true;

    console.log("Sending AJAX request to:", form.getAttribute("action"));

    fetch(form.getAttribute("action"), {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => {
        console.log("Response status:", response.status);
        if (!response.ok) {
          throw new Error(`Lỗi máy chủ: ${response.status}`);
        }
        return response.text();
      })
      .then((data) => {
        console.log("Response data:", data);
        try {
          const jsonData = JSON.parse(data);
          if (jsonData.success) {
            showMessage(jsonData.message, "success");
            form.reset();
          } else {
            showMessage(jsonData.message || "Có lỗi xảy ra", "error");
          }
        } catch (e) {
          console.error("JSON parse error:", e, "Response:", data);
          showMessage("Phản hồi không hợp lệ từ máy chủ", "error");
        }
      })
      .catch((error) => {
        console.error("Fetch error:", error);
        showMessage(`Lỗi: ${error.message}`, "error");
      })
      .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      });
  });

  // Xóa lỗi khi nhập lại
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      input.classList.remove("error");
    });
  });
});
