document.addEventListener("DOMContentLoaded", () => {
  console.log("product-detail.js loaded");
  initThumbnailGallery();
  initQuantitySelector();
  initActionButtons();
  initRelatedProductsNavigation();
  initCommentForm();
  initPagination();
});

function initThumbnailGallery() {
  const thumbnails = document.querySelectorAll(".thumbnail");
  const mainImage = document.querySelector(".main-image img");
  if (!mainImage) {
    console.warn("Main image not found");
    return;
  }

  thumbnails.forEach((thumbnail) => {
    thumbnail.addEventListener("click", () => {
      thumbnails.forEach((t) => t.classList.remove("active"));
      thumbnail.classList.add("active");
      mainImage.src = thumbnail.src;
    });
  });
}

function initQuantitySelector() {
  const decreaseBtn = document.querySelector(".quantity-btn.decrease");
  const increaseBtn = document.querySelector(".quantity-btn.increase");
  const quantityInput = document.querySelector(".quantity-input");

  if (!decreaseBtn || !increaseBtn || !quantityInput) {
    console.warn("Quantity selector elements not found");
    return;
  }

  const maxStock = parseInt(quantityInput.getAttribute("max") || 10);

  decreaseBtn.addEventListener("click", () => {
    let value = parseInt(quantityInput.value);
    if (value > 1) {
      quantityInput.value = value - 1;
    }
  });

  increaseBtn.addEventListener("click", () => {
    let value = parseInt(quantityInput.value);
    if (value < maxStock) {
      quantityInput.value = value + 1;
    }
  });

  quantityInput.addEventListener("change", () => {
    let value = parseInt(quantityInput.value);
    let max = parseInt(quantityInput.max);
    let min = parseInt(quantityInput.min);

    if (isNaN(value) || value < min) {
      quantityInput.value = min;
    }
    if (value > max) {
      quantityInput.value = max;
    }
  });
}

function initActionButtons() {
  const addToCartBtn = document.querySelector(".add-to-cart-btn");
  const buyNowBtn = document.querySelector(".buy-now-btn");
  const quantityInput = document.querySelector(".quantity-input");

  if (!quantityInput) {
    console.error("Quantity input not found");
    return;
  }

  if (!addToCartBtn && !buyNowBtn) {
    console.error("Add to cart or buy now buttons not found");
    return;
  }

  if (addToCartBtn) {
    addToCartBtn.addEventListener("click", () => {
      const productCode = addToCartBtn.getAttribute("data-product-code");
      const quantity = parseInt(quantityInput.value);
      console.log("Add to cart clicked", { productCode, quantity });

      if (quantity > 5) {
        if (
          confirm(`Bạn có chắc muốn thêm ${quantity} sản phẩm vào giỏ hàng?`)
        ) {
          addToCart(productCode, quantity);
        }
      } else {
        addToCart(productCode, quantity);
      }
    });
  }

  if (buyNowBtn) {
    buyNowBtn.addEventListener("click", () => {
      const productCode = buyNowBtn.getAttribute("data-product-code");
      const quantity = parseInt(quantityInput.value);
      console.log("Buy now clicked", { productCode, quantity });
      addToCart(productCode, quantity, true);
    });
  }
}

function addToCart(productCode, quantity, redirectToCart = false) {
  if (!productCode || quantity < 1) {
    console.error("Invalid product code or quantity", {
      productCode,
      quantity,
    });
    alert("Dữ liệu không hợp lệ. Vui lòng thử lại.");
    return;
  }

  fetch("/processes/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      product_id: productCode,
      quantity: quantity,
    }),
  })
    .then((response) => {
      console.log("Fetch response status:", response.status);
      if (!response.ok)
        throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json();
    })
    .then((data) => {
      console.log("Fetch response data:", data);
      if (data.success) {
        const notification = document.createElement("div");
        notification.className = "cart-notification";
        notification.textContent = "Sản phẩm đã được thêm vào giỏ hàng!";
        notification.style.cssText = `
                    position: fixed; top: 20px; right: 20px; background: #8B0000; color: white;
                    padding: 10px 20px; border-radius: 5px; z-index: 1000;
                `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);

        const cartCount = document.querySelector("#cartCount");
        if (cartCount) {
          cartCount.textContent = data.count;
          cartCount.classList.add("update-animation");
          setTimeout(
            () => cartCount.classList.remove("update-animation"),
            1000
          );
        }

        if (redirectToCart) {
          window.location.href = "index.php?page=cart";
        }
      } else {
        console.error("Add to cart failed:", data.message);
        alert(data.message || "Lỗi khi thêm vào giỏ hàng.");
        if (
          data.message === "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng"
        ) {
          window.location.href =
            "index.php?page=login&redirect=" +
            encodeURIComponent(window.location.href);
        }
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      alert("Lỗi hệ thống. Vui lòng thử lại sau.");
    });
}

function initRelatedProductsNavigation() {
  const productCards = document.querySelectorAll(
    ".related-products-section .product-card"
  );

  productCards.forEach((card) => {
    const productId = card.getAttribute("data-product-id");
    if (!productId) {
      console.warn("Product card missing data-product-id:", card);
      return;
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

    const productImage = card.querySelector(".product-img");
    if (productImage) {
      makeClickableForDetail(productImage, productId);
    }

    const productTitle = card.querySelector(".product-name");
    if (productTitle) {
      makeClickableForDetail(productTitle, productId);
    }

    const buyNowBtn = card.querySelector(".buy-now-btn");
    if (buyNowBtn) {
      buyNowBtn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        navigateToProductDetail(productId);
      });
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

function initCommentForm() {
  const commentForm = document.querySelector("#comment-form");
  if (!commentForm) {
    console.log("Comment form not found, user may not be logged in");
    return;
  }

  commentForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const commentText = commentForm.querySelector("textarea").value.trim();
    if (!commentText) {
      alert("Bình luận không được để trống");
      return;
    }

    const formData = new FormData(commentForm);
    fetch(commentForm.action, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok)
          throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
      })
      .then((data) => {
        if (data.success && data.comment && data.comment.comment_text) {
          commentForm.querySelector("textarea").value = "";
          loadComments(1, formData.get("product_id")); // Reload comments for page 1

          const notification = document.createElement("div");
          notification.className = "cart-notification";
          notification.textContent = data.message;
          notification.style.cssText = `
                        position: fixed; top: 20px; right: 20px; background: #8B0000; color: white;
                        padding: 10px 20px; border-radius: 5px; z-index: 1000;
                    `;
          document.body.appendChild(notification);
          setTimeout(() => notification.remove(), 3000);
        } else {
          alert(data.message || "Lỗi khi gửi bình luận.");
          if (data.message === "Vui lòng đăng nhập để gửi bình luận") {
            window.location.href =
              "index.php?page=login&redirect=" +
              encodeURIComponent(window.location.href);
          }
        }
      })
      .catch((error) => {
        console.error("Error submitting comment:", error);
        alert("Lỗi hệ thống. Vui lòng thử lại sau.");
      });
  });
}

function initPagination() {
  const paginationButtons = document.querySelectorAll(".pagination-btn");
  paginationButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const page = parseInt(this.getAttribute("data-page"));
      const productId = this.getAttribute("data-product-id");
      loadComments(page, productId);
    });
  });
}

function loadComments(page, productId) {
  fetch(`processes/get_comments.php?page=${page}&product_id=${productId}`, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => {
      if (!response.ok)
        throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json();
    })
    .then((data) => {
      const commentsList = document.querySelector(".comments-list");
      const paginationContainer = document.querySelector(".pagination");

      // Update comments list
      commentsList.innerHTML = "";
      if (data.comments.length === 0) {
        commentsList.innerHTML =
          "<p>Chưa có bình luận nào cho sản phẩm này.</p>";
      } else {
        const displayedComments = [];
        data.comments.forEach((comment) => {
          if (!displayedComments.includes(comment.id)) {
            displayedComments.push(comment.id);
            const commentItem = document.createElement("div");
            commentItem.className = "comment-item";
            commentItem.setAttribute("data-comment-id", comment.id);
            commentItem.innerHTML = `
                            <div class="comment-header">
                                <span class="comment-user">${
                                  comment.full_name
                                }</span>
                                <span class="comment-date">${new Date(
                                  comment.created_at
                                ).toLocaleString("vi-VN", {
                                  day: "2-digit",
                                  month: "2-digit",
                                  year: "numeric",
                                  hour: "2-digit",
                                  minute: "2-digit",
                                })}</span>
                            </div>
                            <p class="comment-text">${comment.comment_text}</p>
                        `;
            commentsList.appendChild(commentItem);
          }
        });
      }

      // Update pagination
      if (paginationContainer) {
        const totalPages = data.total_pages;
        const currentPage = data.current_page;

        paginationContainer.innerHTML = "";
        if (totalPages > 1) {
          if (currentPage > 1) {
            const prevBtn = document.createElement("a");
            prevBtn.href = "#";
            prevBtn.className = "pagination-btn";
            prevBtn.setAttribute("data-page", currentPage - 1);
            prevBtn.setAttribute("data-product-id", productId);
            prevBtn.textContent = "Trước";
            paginationContainer.appendChild(prevBtn);
          }

          const startPage = Math.max(1, currentPage - 2);
          const endPage = Math.min(totalPages, currentPage + 2);
          for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement("a");
            pageBtn.href = "#";
            pageBtn.className = `pagination-btn ${
              i === currentPage ? "active" : ""
            }`;
            pageBtn.setAttribute("data-page", i);
            pageBtn.setAttribute("data-product-id", productId);
            pageBtn.textContent = i;
            paginationContainer.appendChild(pageBtn);
          }

          if (currentPage < totalPages) {
            const nextBtn = document.createElement("a");
            nextBtn.href = "#";
            nextBtn.className = "pagination-btn";
            nextBtn.setAttribute("data-page", currentPage + 1);
            nextBtn.setAttribute("data-product-id", productId);
            nextBtn.textContent = "Sau";
            paginationContainer.appendChild(nextBtn);
          }

          // Re-attach event listeners to new pagination buttons
          initPagination();
        }
      }
    })
    .catch((error) => {
      console.error("Error loading comments:", error);
      alert("Lỗi khi tải bình luận. Vui lòng thử lại sau.");
    });
}
