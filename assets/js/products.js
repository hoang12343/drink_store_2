document.addEventListener("DOMContentLoaded", function () {
  initFilterToggle();
  initBuyNowButtons();
  initProductDetailNavigation();
  initFilterValidation();
});

function initFilterToggle() {
  const productsHeader = document.querySelector(".products-header");
  const leftBar = document.querySelector(".left-bar");

  if (productsHeader && leftBar) {
    const filterToggle = document.createElement("button");
    filterToggle.classList.add("filter-toggle");

    productsHeader.prepend(filterToggle);

    filterToggle.addEventListener("click", function () {
      leftBar.classList.toggle("active");
    });
  }
}

function initFilterValidation() {
  const filterForms = document.querySelectorAll(".filter-form");
  filterForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const priceMinInput = form.querySelector("#price_min");
      const priceMaxInput = form.querySelector("#price_max");
      const customPriceInput = form.querySelector("#custom_price");

      if (priceMinInput && priceMaxInput) {
        const priceMin = priceMinInput.value;
        const priceMax = priceMaxInput.value;

        if (
          priceMin &&
          priceMax &&
          parseFloat(priceMin) > parseFloat(priceMax)
        ) {
          e.preventDefault();
          showNotification(
            "Giá tối thiểu không được lớn hơn giá tối đa.",
            "error"
          );
          return;
        }

        if (
          (priceMin && parseFloat(priceMin) < 0) ||
          (priceMax && parseFloat(priceMax) < 0)
        ) {
          e.preventDefault();
          showNotification("Giá không được nhỏ hơn 0.", "error");
          return;
        }
      }

      if (
        customPriceInput &&
        customPriceInput.value &&
        parseFloat(customPriceInput.value) < 0
      ) {
        e.preventDefault();
        showNotification("Giá tùy chỉnh không được nhỏ hơn 0.", "error");
        return;
      }
    });
  });
}

function initBuyNowButtons() {
  const buyNowButtons = document.querySelectorAll(".buy-now-btn");

  buyNowButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const productId = this.getAttribute("data-product-id");

      if (!productId) {
        console.error("Product ID not found");
        return;
      }

      navigateToProductDetail(productId);
    });
  });
}

function addToCart(productCode, quantity) {
  fetch("processes/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body:
      "product_code=" +
      encodeURIComponent(productCode) +
      "&quantity=" +
      encodeURIComponent(quantity),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const cartCountElement = document.getElementById("cartCount");
        if (cartCountElement) {
          cartCountElement.textContent = data.cart_count;
        }
        showNotification("Sản phẩm đã được thêm vào giỏ hàng!", "success");
      } else {
        showNotification(
          data.message || "Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification(
        "Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.",
        "error"
      );
    });
}

function initProductDetailNavigation() {
  const productCards = document.querySelectorAll(".product-card");

  productCards.forEach((card) => {
    const productId = card.getAttribute("data-product-id");
    if (!productId) {
      console.warn("Product card missing data-product-id:", card);
      return;
    }

    const productImage = card.querySelector(".product-image");
    if (productImage) {
      makeClickableForDetail(productImage, productId);
    }

    const productTitle = card.querySelector(".product-title");
    if (productTitle) {
      makeClickableForDetail(productTitle, productId);
    }

    const addToCartBtn = card.querySelector(".add-to-cart-btn");
    if (addToCartBtn) {
      addToCartBtn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const productCode =
          this.getAttribute("data-product-code") ||
          card.getAttribute("data-product-code");
        if (productCode) {
          addToCart(productCode, 1);
        }
      });
    }

    if (card.classList.contains("clickable")) {
      card.addEventListener("click", function (e) {
        if (
          e.target.tagName === "BUTTON" ||
          e.target.tagName === "A" ||
          e.target.closest("button") ||
          e.target.closest("a")
        ) {
          return;
        }

        navigateToProductDetail(productId);
      });
      card.style.cursor = "pointer";
    }
  });
}

function makeClickableForDetail(element, productId) {
  element.style.cursor = "pointer";
  element.addEventListener("click", function (e) {
    e.preventDefault();
    navigateToProductDetail(productId);
  });
}

function navigateToProductDetail(productId) {
  window.location.href =
    "index.php?page=product-detail&id=" + encodeURIComponent(productId);
}

function showNotification(message, type = "info") {
  let notificationContainer = document.querySelector(".notification-container");
  if (!notificationContainer) {
    notificationContainer = document.createElement("div");
    notificationContainer.className = "notification-container";
    document.body.appendChild(notificationContainer);

    const style = document.createElement("style");
    style.textContent = `
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }
            .notification {
                background-color: white;
                color: #333;
                border-radius: 4px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
                padding: 15px 20px;
                margin-bottom: 10px;
                transform: translateX(120%);
                transition: transform 0.4s ease;
                display: flex;
                align-items: center;
                min-width: 300px;
            }
            .notification.show {
                transform: translateX(0);
            }
            .notification.success {
                border-left: 4px solid #4CAF50;
            }
            .notification.error {
                border-left: 4px solid #F44336;
            }
            .notification.info {
                border-left: 4px solid #2196F3;
            }
            .notification-icon {
                margin-right: 15px;
                font-size: 20px;
            }
            .notification.success .notification-icon {
                color: #4CAF50;
            }
            .notification.error .notification-icon {
                color: #F44336;
            }
            .notification.info .notification-icon {
                color: #2196F3;
            }
            .notification-message {
                flex: 1;
            }
            .notification-close {
                margin-left: 15px;
                cursor: pointer;
                color: #aaa;
                font-size: 16px;
            }
            .notification-close:hover {
                color: #333;
            }
        `;
    document.head.appendChild(style);
  }

  const notification = document.createElement("div");
  notification.className = `notification ${type}`;

  let iconClass = "";
  switch (type) {
    case "success":
      iconClass = "fas fa-check-circle";
      break;
    case "error":
      iconClass = "fas fa-exclamation-circle";
      break;
    default:
      iconClass = "fas fa-info-circle";
  }

  notification.innerHTML = `
        <div class="notification-icon">
            <i class="${iconClass}"></i>
        </div>
        <div class="notification-message">${message}</div>
        <div class="notification-close">×</div>
    `;

  notificationContainer.appendChild(notification);

  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

  const closeBtn = notification.querySelector(".notification-close");
  closeBtn.addEventListener("click", function () {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 400);
  });

  setTimeout(() => {
    if (notification.parentNode) {
      notification.classList.remove("show");
      setTimeout(() => {
        notification.remove();
      }, 400);
    }
  }, 5000);
}
