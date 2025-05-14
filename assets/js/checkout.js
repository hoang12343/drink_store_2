document.addEventListener("DOMContentLoaded", () => {
  // Select the checkout form
  const checkoutForm = document.getElementById("checkout-form");
  let isSubmitting = false; // Flag to prevent multiple submissions

  if (checkoutForm) {
    // Add event listener for form submission
    checkoutForm.addEventListener("submit", function (event) {
      event.preventDefault(); // Prevent default form submission

      if (isSubmitting) {
        console.warn("Form submission already in progress");
        return;
      }
      isSubmitting = true;

      // Collect form data
      const formData = new FormData(checkoutForm);
      const orderData = {
        selected_items: formData.get("selected_items"),
        total_amount: parseFloat(formData.get("total_amount")) || 0,
        discount: parseFloat(formData.get("discount")) || 0,
        shipping: parseFloat(formData.get("shipping")) || 0,
        payment_method: formData.get("payment_method"),
        promo_code: formData.get("promo_code") || null,
      };

      // Validate form data
      try {
        if (
          !orderData.selected_items ||
          !JSON.parse(orderData.selected_items).length
        ) {
          throw new Error("Vui lòng chọn ít nhất một sản phẩm");
        }
        if (
          !orderData.payment_method ||
          !["cod", "zalopay"].includes(orderData.payment_method)
        ) {
          throw new Error("Vui lòng chọn phương thức thanh toán hợp lệ");
        }
        if (orderData.total_amount <= 0) {
          throw new Error("Tổng tiền không hợp lệ");
        }
      } catch (validationError) {
        showNotification("error", validationError.message);
        isSubmitting = false;
        return;
      }

      // Log orderData for debugging
      console.log("Sending orderData:", orderData);

      // Send fetch request to payment_handler.php
      fetch("processes/payment_handler.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(orderData),
      })
        .then((response) => {
          if (!response.ok) {
            return response.json().then((data) => {
              throw new Error(
                data.error || `HTTP error! status: ${response.status}`
              );
            });
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Show success notification
            showNotification(
              "success",
              data.message || "Thanh toán thành công!"
            );
            // Redirect after 2 seconds
            setTimeout(() => {
              window.location.href =
                data.redirect ||
                `../index.php?page=order_confirmation&order_id=${data.order_id}`;
            }, 2000);
          } else {
            // Show specific error from server
            showNotification(
              "error",
              data.error || "Có lỗi xảy ra khi xử lý đơn hàng"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification(
            "error",
            error.message || "Có lỗi xảy ra khi kết nối đến server"
          );
        })
        .finally(() => {
          isSubmitting = false; // Reset submission flag
        });
    });
  } else {
    console.error("Checkout form not found");
  }
});
