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

    // Log dữ liệu form để debug
    console.log("Form data:");
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value}`);
    }

    // Sử dụng đường dẫn tuyệt đối
    const url = window.location.origin + "/processes/reply_contact.php";
    console.log("Sending request to:", url);

    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        console.log("Response status:", response.status);
        if (!response.ok) {
          throw new Error(`Server responded with status: ${response.status}`);
        }
        return response.text();
      })
      .then((text) => {
        console.log("Raw response:", text);

        // Kiểm tra nếu phản hồi chứa HTML (thường là lỗi PHP)
        if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
          throw new Error("Lỗi máy chủ. Phản hồi chứa HTML.");
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
          alert("Gửi trả lời thành công!");
          window.location.reload();
        } else {
          alert("Lỗi: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Lỗi: " + error.message);
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

      const formData = new FormData();
      formData.append("id", id);
      formData.append("action", "read");
      formData.append("value", isRead);

      const baseUrl = window.location.pathname.substring(
        0,
        window.location.pathname.lastIndexOf("/") + 1
      );
      const url = baseUrl + "processes/toggle_contact.php";

      // Ghi log request
      logToConsole(
        `Gửi request AJAX:<br>URL: ${url}<br>Phương thức: POST<br>Dữ liệu: id=${id}, action=read, value=${isRead}`,
        "info"
      );

      fetch(url, {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            logToConsole(
              `Lỗi kết nối: ${response.status} ${response.statusText}`,
              "error"
            );
            throw new Error("Lỗi kết nối: " + response.status);
          }
          return response.text();
        })
        .then((text) => {
          // Ghi log phản hồi
          logToConsole(
            `Phản hồi từ server:<br>${text.substring(0, 200)}${
              text.length > 200 ? "..." : ""
            }`,
            "info"
          );

          // Kiểm tra nếu phản hồi chứa HTML
          if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
            logToConsole(
              `Lỗi: Phản hồi chứa HTML:<br>${text.substring(0, 200)}${
                text.length > 200 ? "..." : ""
              }`,
              "error"
            );
            throw new Error("Lỗi máy chủ. Phản hồi chứa HTML.");
          }

          let data;
          try {
            data = JSON.parse(text);
            logToConsole(
              `Phản hồi JSON:<br>${JSON.stringify(data, null, 2)}`,
              "success"
            );
          } catch (e) {
            logToConsole(
              `Lỗi phân tích JSON:<br>${
                e.message
              }<br>Phản hồi gốc:<br>${text.substring(0, 200)}${
                text.length > 200 ? "..." : ""
              }`,
              "error"
            );
            throw new Error("Phản hồi không hợp lệ từ máy chủ");
          }

          if (data.success) {
            this.textContent = isRead ? "Đã đọc" : "Chưa đọc";
            this.dataset.read = isRead;
            this.classList.toggle("active", isRead);
          } else {
            logToConsole(`Lỗi từ server: ${data.message}`, "error");
            alert("Lỗi: " + data.message);
          }
        })
        .catch((error) => {
          logToConsole(`Lỗi: ${error.message}`, "error");
          alert("Lỗi kết nối: " + error);
        });
    });
  });

  document.querySelectorAll(".btn-toggle-important").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const isImportant = this.dataset.important === "1" ? 0 : 1;

      const formData = new FormData();
      formData.append("id", id);
      formData.append("action", "important");
      formData.append("value", isImportant);

      const baseUrl = window.location.pathname.substring(
        0,
        window.location.pathname.lastIndexOf("/") + 1
      );
      const url = baseUrl + "processes/toggle_contact.php";

      // Ghi log request
      logToConsole(
        `Gửi request AJAX:<br>URL: ${url}<br>Phương thức: POST<br>Dữ liệu: id=${id}, action=important, value=${isImportant}`,
        "info"
      );

      fetch(url, {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            logToConsole(
              `Lỗi kết nối: ${response.status} ${response.statusText}`,
              "error"
            );
            throw new Error("Lỗi kết nối: " + response.status);
          }
          return response.text();
        })
        .then((text) => {
          // Ghi log phản hồi
          logToConsole(
            `Phản hồi từ server:<br>${text.substring(0, 200)}${
              text.length > 200 ? "..." : ""
            }`,
            "info"
          );

          // Kiểm tra nếu phản hồi chứa HTML
          if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
            logToConsole(
              `Lỗi: Phản hồi chứa HTML:<br>${text.substring(0, 200)}${
                text.length > 200 ? "..." : ""
              }`,
              "error"
            );
            throw new Error("Lỗi máy chủ. Phản hồi chứa HTML.");
          }

          let data;
          try {
            data = JSON.parse(text);
            logToConsole(
              `Phản hồi JSON:<br>${JSON.stringify(data, null, 2)}`,
              "success"
            );
          } catch (e) {
            logToConsole(
              `Lỗi phân tích JSON:<br>${
                e.message
              }<br>Phản hồi gốc:<br>${text.substring(0, 200)}${
                text.length > 200 ? "..." : ""
              }`,
              "error"
            );
            throw new Error("Phản hồi không hợp lệ từ máy chủ");
          }

          if (data.success) {
            this.textContent = isImportant ? "Quan trọng" : "Bình thường";
            this.dataset.important = isImportant;
            this.classList.toggle("active", isImportant);
          } else {
            logToConsole(`Lỗi từ server: ${data.message}`, "error");
            alert("Lỗi: " + data.message);
          }
        })
        .catch((error) => {
          logToConsole(`Lỗi: ${error.message}`, "error");
          alert("Lỗi kết nối: " + error);
        });
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

  // Hàm ghi log vào bảng điều khiển gỡ lỗi
  function logToConsole(message, type = "info") {
    const debugLog = document.getElementById("debugLog");
    if (!debugLog) return;

    const logEntry = document.createElement("div");
    logEntry.className = `log-entry log-${type}`;
    logEntry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
    debugLog.appendChild(logEntry);
    debugLog.scrollTop = debugLog.scrollHeight; // Cuộn xuống cuối
  }

  // Xử lý xóa log
  const clearDebugButton = document.getElementById("clearDebugLog");
  if (clearDebugButton) {
    clearDebugButton.addEventListener("click", function () {
      debugLog.innerHTML = "";
      logToConsole("Log đã được xóa", "info");
    });
  }
});
// Gửi yêu cầu trả lời
$("#replyForm").on("submit", function (e) {
  e.preventDefault();
  const formData = $(this).serialize();
  $.ajax({
    url: "processes/reply_contact.php",
    type: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      console.log("Raw response:", response);
      if (response.success) {
        Swal.fire({
          icon: "success",
          title: "Thành công",
          text: response.message,
          confirmButtonText: "OK",
        }).then(() => {
          $("#replyModal").modal("hide");
          location.reload();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Lỗi",
          text: response.message,
          confirmButtonText: "OK",
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX error:", status, error);
      Swal.fire({
        icon: "error",
        title: "Lỗi",
        text: "Đã xảy ra lỗi khi gửi trả lời. Vui lòng thử lại!",
        confirmButtonText: "OK",
      });
    },
  });
});
