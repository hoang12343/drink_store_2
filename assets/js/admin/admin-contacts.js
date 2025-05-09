document.addEventListener("DOMContentLoaded", function () {
  // Lấy CSRF token từ input ẩn
  const csrfToken = document.getElementById("csrf_token")?.value;
  if (!csrfToken) {
    console.error(
      "CSRF token not found. Ensure the hidden input with id='csrf_token' exists."
    );
    return; // Thoát nếu không tìm thấy token
  }

  // Mở modal trả lời
  function openReplyModal(id, email, subject) {
    document.getElementById("replyModal").style.display = "block";
    document.getElementById("replyContactId").value = id;
    document.getElementById("reply_email").value = email;
    document.getElementById("reply_subject").value = `Re: ${subject}`;
    document.getElementById("reply_message").focus();
  }

  // Đóng modal
  window.closeReplyModal = function () {
    document.getElementById("replyModal").style.display = "none";
    document.getElementById("replyForm").reset();
  };

  // Xử lý nút trả lời
  document.querySelectorAll(".btn-reply").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const email = this.dataset.email;
      const subject = this.dataset.subject;
      openReplyModal(id, email, subject);
    });
  });

  // Xử lý trạng thái đã đọc
  document.querySelectorAll(".btn-toggle-read").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const isRead = this.dataset.read === "1" ? 0 : 1;
      fetch(
        `processes/toggle_contact.php?action=read&id=${id}&value=${isRead}&csrf_token=${encodeURIComponent(
          csrfToken
        )}`,
        {
          method: "GET",
        }
      )
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

  // Xử lý trạng thái quan trọng
  document.querySelectorAll(".btn-toggle-important").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const isImportant = this.dataset.important === "1" ? 0 : 1;
      fetch(
        `processes/toggle_contact.php?action=important&id=${id}&value=${isImportant}&csrf_token=${encodeURIComponent(
          csrfToken
        )}`,
        {
          method: "GET",
        }
      )
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

  // Xuất Excel
  window.exportContacts = function () {
    const searchName = document.getElementById("search_name").value;
    const searchEmail = document.getElementById("search_email").value;
    const searchSubject = document.getElementById("search_subject").value;
    window.location.href = `?page=admin&subpage=admin-contacts&export=xlsx&search_name=${encodeURIComponent(
      searchName
    )}&search_email=${encodeURIComponent(
      searchEmail
    )}&search_subject=${encodeURIComponent(searchSubject)}`;
  };
});
