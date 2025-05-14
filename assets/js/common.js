function showNotification(type, message) {
  // Tạo phần tử thông báo
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;

  // Thêm vào body
  document.body.appendChild(notification);

  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => {
      notification.remove();
    }, 500);
  }, 2500);
}
