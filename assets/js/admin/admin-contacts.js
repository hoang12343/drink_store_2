document.addEventListener("DOMContentLoaded", function () {
  // Xử lý form xóa
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
          <p>Bạn có chắc muốn xóa tin nhắn này?</p>
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

  function openReplyModal(id, email, subject) {
    document.getElementById("replyModal").style.display = "block";
    document.getElementById("replyContactId").value = id;
    document.getElementById("reply_email").value = email;
    document.getElementById("reply_subject").value = `Re: ${subject}`;
    document.getElementById("reply_message").focus();
  }

  window.closeReplyModal = function () {
    document.getElementById("replyModal").style.display = "none";
    document.getElementById("replyForm").reset();
  };

  // Xử lý gửi form trả lời qua AJAX
  document.getElementById("replyForm").addEventListener("submit", function (e) {
    e.preventDefault();

    // Hiển thị trạng thái đang gửi
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Đang gửi...";
    submitBtn.disabled = true;

    const formData = new FormData(this);

    fetch("processes/reply_contact.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Lỗi kết nối: " + response.status);
        }
        return response.text();
      })
      .then((text) => {
        // Kiểm tra nếu phản hồi chứa HTML (thường là lỗi PHP)
        if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
          console.error("Server error:", text);
          throw new Error(
            "Lỗi máy chủ. Vui lòng kiểm tra console để biết chi tiết."
          );
        }

        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Invalid JSON:", text);
          throw new Error("Phản hồi không hợp lệ từ máy chủ");
        }

        if (data.success) {
          closeReplyModal();
          window.location.href = `index.php?page=admin&subpage=admin-contacts&success=${encodeURIComponent(
            data.message
          )}`;
        } else {
          alert("Lỗi: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Lỗi kết nối: " + error.message);
      })
      .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      });
  });

  document.querySelectorAll(".btn-reply").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const email = this.dataset.email;
      const subject = this.dataset.subject;
      openReplyModal(id, email, subject);
    });
  });

  document.querySelectorAll(".btn-toggle-read").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const isRead = this.dataset.read === "1" ? 0 : 1;

      // Sử dụng POST thay vì GET cho các thao tác thay đổi dữ liệu
      const formData = new FormData();
      formData.append("id", id);
      formData.append("action", "read");
      formData.append("value", isRead);
      // Loại bỏ CSRF token
      // formData.append('csrf_token', csrfToken);

      fetch("processes/toggle_contact.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.textContent = isRead ? "Đã đọc" : "Chưa đọc";
            this.dataset.read = isRead;
            this.classList.toggle("active", isRead);
          } else {
            alert("Lỗi: " + data.message);
          }
        })
        .catch((error) => alert("Lỗi kết nối: " + error));
    });
  });

  document.querySelectorAll(".btn-toggle-important").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const isImportant = this.dataset.important === "1" ? 0 : 1;

      // Sử dụng POST thay vì GET cho các thao tác thay đổi dữ liệu
      const formData = new FormData();
      formData.append("id", id);
      formData.append("action", "important");
      formData.append("value", isImportant);
      // Loại bỏ CSRF token
      // formData.append('csrf_token', csrfToken);

      fetch("processes/toggle_contact.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.textContent = isImportant ? "Quan trọng" : "Bình thường";
            this.dataset.important = isImportant;
            this.classList.toggle("active", isImportant);
          } else {
            alert("Lỗi: " + data.message);
          }
        })
        .catch((error) => alert("Lỗi kết nối: " + error));
    });
  });

  window.exportContacts = function () {
    const searchName = document.getElementById("search_name").value;
    const searchEmail = document.getElementById("search_email").value;
    const searchSubject = document.getElementById("search_subject").value;
    window.location.href = `index.php?page=admin&subpage=admin-contacts&export=xlsx&search_name=${encodeURIComponent(
      searchName
    )}&search_email=${encodeURIComponent(
      searchEmail
    )}&search_subject=${encodeURIComponent(searchSubject)}`;
  };
});
