document.addEventListener("DOMContentLoaded", () => {
  // Toggle password visibility
  const toggleButtons = document.querySelectorAll(".toggle-password");
  toggleButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const targetId = button.getAttribute("data-target");
      const input = document.getElementById(targetId);
      const icon = button.querySelector("i");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    });
  });

  // Client-side validation for profile form
  const profileForm = document.getElementById("updateProfileForm");
  if (profileForm) {
    profileForm.addEventListener("submit", (e) => {
      let valid = true;
      const fullName = document.getElementById("full_name").value.trim();
      const email = document.getElementById("email").value.trim();
      const phone = document.getElementById("phone").value.trim();
      const address = document.getElementById("address").value.trim();

      // Reset errors
      document
        .querySelectorAll(".error")
        .forEach((error) => (error.textContent = ""));

      // Validate full name
      if (!fullName || !/^[\p{L}\s]+$/u.test(fullName)) {
        document.getElementById("fullNameError").textContent =
          "Vui lòng nhập họ và tên hợp lệ (chỉ chữ và khoảng trắng).";
        valid = false;
      }

      // Validate email
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById("emailError").textContent =
          "Vui lòng nhập email hợp lệ.";
        valid = false;
      }

      // Validate phone
      if (!phone || !/^[0-9]{10,11}$/.test(phone)) {
        document.getElementById("phoneError").textContent =
          "Số điện thoại phải có 10-11 số.";
        valid = false;
      }

      // Validate address
      if (!address || !/^[\p{L}\p{N}\s,.-]+$/u.test(address)) {
        document.getElementById("addressError").textContent =
          "Vui lòng nhập địa chỉ hợp lệ (chữ, số, khoảng trắng, dấu phẩy, dấu chấm, dấu gạch ngang).";
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  }

  // Client-side validation for password form
  const passwordForm = document.getElementById("changePasswordForm");
  if (passwordForm) {
    passwordForm.addEventListener("submit", (e) => {
      let valid = true;
      const currentPassword = document.getElementById("current_password").value;
      const newPassword = document.getElementById("new_password").value;
      const confirmPassword = document.getElementById("confirm_password").value;

      // Reset errors
      document
        .querySelectorAll(".error")
        .forEach((error) => (error.textContent = ""));

      // Validate current password
      if (!currentPassword) {
        document.getElementById("currentPasswordError").textContent =
          "Vui lòng nhập mật khẩu hiện tại.";
        valid = false;
      }

      // Validate new password
      if (!newPassword || newPassword.length < 6) {
        document.getElementById("newPasswordError").textContent =
          "Mật khẩu mới phải từ 6 ký tự trở lên.";
        valid = false;
      }

      // Validate confirm password
      if (newPassword !== confirmPassword) {
        document.getElementById("confirmPasswordError").textContent =
          "Mật khẩu mới và xác nhận không khớp.";
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  }
});
