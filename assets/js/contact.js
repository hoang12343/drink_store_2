document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("contactForm");
  if (!form) return;

  const inputs = form.querySelectorAll("input[required], textarea[required]");
  const emailInput = form.querySelector("#email");
  const submitBtn = form.querySelector(".submit-btn");
  let formMessage = document.querySelector(".form-message");

  // Xóa thông báo mặc định nếu có
  if (formMessage) {
    setTimeout(() => {
      if (formMessage.parentNode) {
        formMessage.remove();
      }
    }, 5000);
  }

  // Hàm kiểm tra định dạng email
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // Hàm hiển thị thông báo
  const showMessage = (message, type) => {
    console.log(`Showing message: ${message} (${type})`);

    // Xóa thông báo cũ nếu có
    if (formMessage && formMessage.parentNode) {
      formMessage.remove();
    }

    // Tạo thông báo mới
    formMessage = document.createElement("div");
    formMessage.className = `form-message ${type}`;
    formMessage.textContent = message;

    // Chèn vào đầu form container
    const formContainer = form.closest(".contact-form");
    formContainer.insertBefore(formMessage, formContainer.firstChild);

    // Tự động xóa sau 5 giây
    setTimeout(() => {
      if (formMessage.parentNode) {
        formMessage.remove();
      }
    }, 5000);

    // Cuộn lên đầu để hiển thị thông báo
    window.scrollTo(0, 0);
  };

  // Xử lý sự kiện submit
  form.addEventListener("submit", (e) => {
    console.log("Form submission started");
    e.preventDefault(); // Luôn ngăn submit mặc định để xử lý bằng AJAX

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

    // Kiểm tra CSRF token
    const csrfToken = form.querySelector('input[name="csrf_token"]');
    if (!csrfToken || !csrfToken.value) {
      hasError = true;
      errorMessage =
        "Token bảo mật không tồn tại. Vui lòng tải lại trang và thử lại.";
      console.error("CSRF token missing");
    }

    // Nếu có lỗi, hiển thị thông báo và dừng lại
    if (hasError) {
      showMessage(errorMessage, "error");
      return;
    }

    // Sử dụng AJAX để gửi form
    const formData = new FormData(form);
    const originalText = submitBtn.textContent;

    submitBtn.textContent = "Đang gửi...";
    submitBtn.disabled = true;

    console.log("Sending AJAX request to:", form.getAttribute("action"));

    fetch(form.getAttribute("action"), {
      method: "POST",
      body: formData,
      credentials: "same-origin", // Đảm bảo gửi cookie session
    })
      .then((response) => {
        console.log("Response status:", response.status);
        if (!response.ok) {
          throw new Error(`Lỗi mạng: ${response.status}`);
        }
        return response.text();
      })
      .then((data) => {
        console.log("Response data:", data);
        try {
          // Thử phân tích dữ liệu JSON
          const jsonData = JSON.parse(data);
          if (jsonData.success) {
            showMessage(
              jsonData.message || "Gửi liên hệ thành công",
              "success"
            );
            form.reset();
          } else {
            showMessage(jsonData.message || "Có lỗi xảy ra", "error");
          }
        } catch (e) {
          console.error("JSON parse error:", e);
          // Nếu không phải JSON, hiển thị nội dung phản hồi
          showMessage("Phản hồi không hợp lệ từ máy chủ", "error");
        }
      })
      .catch((error) => {
        console.error("Fetch error:", error);
        showMessage(`Đã xảy ra lỗi: ${error.message}`, "error");
      })
      .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      });
  });

  // Xóa lỗi khi người dùng nhập lại
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      input.classList.remove("error");
    });
  });
});
