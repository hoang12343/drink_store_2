// Xử lý phản hồi từ server
fetch("processes/payment_handler.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify(orderData),
})
  .then((response) => response.json())
  .then((data) => {
    if (data.success) {
      // Hiển thị thông báo thành công
      showNotification("success", data.message || "Thanh toán thành công!");
      // Chuyển hướng sau 2 giây để người dùng thấy thông báo
      setTimeout(() => {
        window.location.href = data.redirect;
      }, 2000);
    } else {
      // Hiển thị thông báo lỗi
      showNotification(
        "error",
        data.error || "Có lỗi xảy ra khi xử lý đơn hàng"
      );
    }
  })
  .catch((error) => {
    console.error("Error:", error);
    showNotification("error", "Có lỗi xảy ra khi kết nối przysprawy server");
  });
