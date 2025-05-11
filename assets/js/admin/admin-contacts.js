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

  // Mở modal trả lời
  function openReplyModal(id, email, subject) {
    document.getElementById("replyModal").style.display = "block";
    document.getElementById("replyContactId").value = id;
    document.getElementById("reply_email").value = email;
    document.getElementById("reply_subject").value = `Re: ${decodeURIComponent(
      subject
    )}`;
    document.getElementById("reply_message").focus();
  }

  // Đóng modal trả lời
  window.closeReplyModal = function () {
    document.getElementById("replyModal").style.display = "none";
    document.getElementById("replyForm").reset();
  };

  // Xử lý gửi form trả lời qua AJAX
  document.getElementById("replyForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Đang gửi...";
    submitBtn.disabled = true;

    const formData = new FormData(this);

    console.log("Form data:");
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value}`);
    }

    const url = window.location.origin + "/processes/reply_contact.php";
    console.log("Sending request to:", url);

    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        console.log("Response status:", response.status);
        if (!response.ok) {
          return response.text().then((text) => {
            console.log("Error response:", text);
            throw new Error(
              `Server responded with status: ${
                response.status
              }, Response: ${text.substring(0, 200)}`
            );
          });
        }
        return response.text();
      })
      .then((text) => {
        console.log("Raw response:", text);

        if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
          const errorSnippet =
            text.substring(0, 200) + (text.length > 200 ? "..." : "");
          throw new Error(`Lỗi máy chủ. Phản hồi chứa HTML: ${errorSnippet}`);
        }

        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Invalid JSON:", text);
          throw new Error(
            `Phản hồi không hợp lệ từ máy chủ: ${text.substring(0, 200)}`
          );
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

  // Xử lý nút trả lời
  document.querySelectorAll(".btn-reply").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const email = this.dataset.email;
      const subject = this.dataset.subject;
      openReplyModal(id, email, subject);
    });
  });

  // Xử lý toggle read
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
            return response.text().then((text) => {
              logToConsole(
                `Lỗi kết nối: ${response.status} ${
                  response.statusText
                }, Response: ${text.substring(0, 200)}`,
                "error"
              );
              throw new Error(
                `Lỗi kết nối: ${response.status}, Response: ${text.substring(
                  0,
                  200
                )}`
              );
            });
          }
          return response.text();
        })
        .then((text) => {
          logToConsole(
            `Phản hồi từ server:<br>${text.substring(0, 200)}${
              text.length > 200 ? "..." : ""
            }`,
            "info"
          );

          if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
            const errorSnippet =
              text.substring(0, 200) + (text.length > 200 ? "..." : "");
            logToConsole(
              `Lỗi: Phản hồi chứa HTML:<br>${errorSnippet}`,
              "error"
            );
            throw new Error(`Lỗi máy chủ. Phản hồi chứa HTML: ${errorSnippet}`);
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
            throw new Error(
              `Phản hồi không hợp lệ từ máy chủ: ${text.substring(0, 200)}`
            );
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
          alert("Lỗi: " + error.message);
        });
    });
  });

  // Xử lý toggle important
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
            return response.text().then((text) => {
              logToConsole(
                `Lỗi kết nối: ${response.status} ${
                  response.statusText
                }, Response: ${text.substring(0, 200)}`,
                "error"
              );
              throw new Error(
                `Lỗi kết nối: ${response.status}, Response: ${text.substring(
                  0,
                  200
                )}`
              );
            });
          }
          return response.text();
        })
        .then((text) => {
          logToConsole(
            `Phản hồi từ server:<br>${text.substring(0, 200)}${
              text.length > 200 ? "..." : ""
            }`,
            "info"
          );

          if (text.includes("<!DOCTYPE html>") || text.includes("<br />")) {
            const errorSnippet =
              text.substring(0, 200) + (text.length > 200 ? "..." : "");
            logToConsole(
              `Lỗi: Phản hồi chứa HTML:<br>${errorSnippet}`,
              "error"
            );
            throw new Error(`Lỗi máy chủ. Phản hồi chứa HTML: ${errorSnippet}`);
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
            throw new Error(
              `Phản hồi không hợp lệ từ máy chủ: ${text.substring(0, 200)}`
            );
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
          alert("Lỗi: " + error.message);
        });
    });
  });

  // Xuất contacts
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

  // Ghi log debug
  function logToConsole(message, type = "info") {
    const debugLog = document.getElementById("debugLog");
    if (!debugLog) return;

    const logEntry = document.createElement("div");
    logEntry.className = `log-entry log-${type}`;
    logEntry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
    debugLog.appendChild(logEntry);
    debugLog.scrollTop = debugLog.scrollHeight;
  }

  // Xóa log debug
  const clearDebugButton = document.getElementById("clearDebugLog");
  if (clearDebugButton) {
    clearDebugButton.addEventListener("click", function () {
      debugLog.innerHTML = "";
      logToConsole("Log đã được xóa", "info");
    });
  }
});
