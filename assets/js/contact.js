document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("contactForm");
  if (!form) return;

  const inputs = form.querySelectorAll("input[required], textarea[required]");
  const emailInput = form.querySelector("#email");
  const submitBtn = form.querySelector(".submit-btn");
  let formMessage = document.querySelector(".form-message");

  if (formMessage) {
    setTimeout(() => {
      if (formMessage.parentNode) {
        formMessage.remove();
      }
    }, 5000);
  }

  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const showMessage = (message, type) => {
    console.log(`Showing message: ${message} (${type})`);
    if (formMessage && formMessage.parentNode) {
      formMessage.remove();
    }

    formMessage = document.createElement("div");
    formMessage.className = `form-message ${type}`;
    formMessage.textContent = message;
    const formContainer = form.closest(".contact-form");
    formContainer.insertBefore(formMessage, formContainer.firstChild);

    setTimeout(() => {
      if (formMessage.parentNode) {
        formMessage.remove();
      }
    }, 5000);
    window.scrollTo(0, 0);
  };

  form.addEventListener("submit", (e) => {
    console.log("Form submission started");
    e.preventDefault();

    // Ngăn gửi nhiều lần
    if (submitBtn.disabled) {
      return;
    }

    let hasError = false;
    let errorMessage = "";

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        hasError = true;
        input.classList.add("error");
        errorMessage = "Vui lòng điền đầy đủ các trường bắt buộc.";
      } else {
        input.classList.remove("error");
      }
    });

    if (emailInput.value && !isValidEmail(emailInput.value)) {
      hasError = true;
      emailInput.classList.add("error");
      errorMessage = "Vui lòng nhập địa chỉ email hợp lệ.";
    }

    if (hasError) {
      showMessage(errorMessage, "error");
      return;
    }

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
            showMessage(
              jsonData.message || "Gửi liên hệ thành công",
              "success"
            );
            form.reset();
          } else {
            showMessage(jsonData.message || "Có lỗi xảy ra", "error");
          }
        } catch (e) {
          console.error("JSON parse error:", e, "Response:", data);
          showMessage(
            "Phản hồi không hợp lệ từ máy chủ. Vui lòng thử lại.",
            "error"
          );
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

  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      input.classList.remove("error");
    });
  });
});
