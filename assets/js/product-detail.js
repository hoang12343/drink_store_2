document.addEventListener("DOMContentLoaded", () => {
  console.log("product-detail.js loaded at", new Date().toISOString());
  initThumbnailGallery();
  initQuantitySelector();
  initActionButtons();
  initRelatedProductsNavigation();
  initCommentForm();
  initPagination();
  initCommentActions();
});

// Initialize thumbnail gallery for product images
function initThumbnailGallery() {
  const thumbnails = document.querySelectorAll(".thumbnail");
  const mainImage = document.querySelector(".main-image img");
  if (!mainImage) {
    console.warn("Main image not found");
    return;
  }

  thumbnails.forEach((thumbnail) => {
    const newThumbnail = thumbnail.cloneNode(true);
    thumbnail.replaceWith(newThumbnail);
    newThumbnail.addEventListener("click", () => {
      thumbnails.forEach((t) => t.classList.remove("active"));
      newThumbnail.classList.add("active");
      mainImage.src = newThumbnail.src;
    });
  });
}

// Initialize quantity selector (increase/decrease buttons and input)
function initQuantitySelector() {
  const decreaseBtn = document.querySelector(".quantity-btn.decrease");
  const increaseBtn = document.querySelector(".quantity-btn.increase");
  const quantityInput = document.querySelector(".quantity-input");

  if (!decreaseBtn || !increaseBtn || !quantityInput) {
    console.warn("Quantity selector elements not found");
    return;
  }

  const maxStock = parseInt(quantityInput.getAttribute("max") || 10);

  // Clone to remove existing events
  const newDecreaseBtn = decreaseBtn.cloneNode(true);
  const newIncreaseBtn = increaseBtn.cloneNode(true);
  const newQuantityInput = quantityInput.cloneNode(true);
  decreaseBtn.replaceWith(newDecreaseBtn);
  increaseBtn.replaceWith(newIncreaseBtn);
  quantityInput.replaceWith(newQuantityInput);

  newDecreaseBtn.addEventListener("click", () => {
    let value = parseInt(newQuantityInput.value, 10);
    if (value > 1) {
      newQuantityInput.value = value - 1;
    }
  });

  newIncreaseBtn.addEventListener("click", () => {
    let value = parseInt(newQuantityInput.value, 10);
    if (value < maxStock) {
      newQuantityInput.value = value + 1;
    }
  });

  newQuantityInput.addEventListener("change", () => {
    let value = parseInt(newQuantityInput.value, 10);
    const max = parseInt(newQuantityInput.max, 10) || maxStock;
    const min = parseInt(newQuantityInput.min, 10) || 1;

    if (isNaN(value) || value < min) {
      newQuantityInput.value = min;
    } else if (value > max) {
      newQuantityInput.value = max;
    }
  });
}

// Initialize action buttons (add to cart, buy now)
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

  // Shared handler to prevent multiple calls
  const handleAddToCart = (productCode, quantity, redirectToCart) => {
    if (handleAddToCart.isProcessing) {
      console.warn("Add to cart is already processing");
      return;
    }
    handleAddToCart.isProcessing = true;

    addToCart(productCode, quantity, redirectToCart).finally(() => {
      handleAddToCart.isProcessing = false;
    });
  };

  if (addToCartBtn) {
    const newAddToCartBtn = addToCartBtn.cloneNode(true);
    addToCartBtn.replaceWith(newAddToCartBtn);
    newAddToCartBtn.addEventListener("click", () => {
      const productCode = newAddToCartBtn.getAttribute("data-product-code");
      const quantity = parseInt(quantityInput.value, 10);
      console.log("Add to cart clicked", { productCode, quantity });

      if (quantity > 5) {
        if (
          confirm(`Bạn có chắc muốn thêm ${quantity} sản phẩm vào giỏ hàng?`)
        ) {
          handleAddToCart(productCode, quantity, false);
        }
      } else {
        handleAddToCart(productCode, quantity, false);
      }
    });
  }

  if (buyNowBtn) {
    const newBuyNowBtn = buyNowBtn.cloneNode(true);
    buyNowBtn.replaceWith(newBuyNowBtn);
    newBuyNowBtn.addEventListener("click", () => {
      const productCode = newBuyNowBtn.getAttribute("data-product-code");
      const quantity = parseInt(quantityInput.value, 10);
      console.log("Buy now clicked", { productCode, quantity });
      handleAddToCart(productCode, quantity, true);
    });
  }
}

// Add product to cart via API
function addToCart(productCode, quantity, redirectToCart = false) {
  if (!productCode || quantity < 1) {
    console.error("Invalid product code or quantity", {
      productCode,
      quantity,
    });
    alert("Dữ liệu không hợp lệ. Vui lòng thử lại.");
    return Promise.reject();
  }

  console.log("Calling addToCart API", {
    productCode,
    quantity,
    redirectToCart,
  });
  return fetch("/processes/add_to_cart.php", {
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
      console.log("addToCart response status:", response.status);
      if (!response.ok)
        throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json();
    })
    .then((data) => {
      console.log("addToCart response data:", data);
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
      console.error("addToCart fetch error:", error);
      alert("Lỗi hệ thống. Vui lòng thử lại sau.");
    });
}

// Initialize navigation for related products
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
      const newCard = card.cloneNode(true);
      card.replaceWith(newCard);
      newCard.addEventListener("click", function (e) {
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
      newCard.style.cursor = "pointer";
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
      const newBuyNowBtn = buyNowBtn.cloneNode(true);
      buyNowBtn.replaceWith(newBuyNowBtn);
      newBuyNowBtn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        navigateToProductDetail(productId);
      });
    }
  });
}

// Make element clickable for product detail navigation
function makeClickableForDetail(element, productId) {
  element.style.cursor = "pointer";
  const newElement = element.cloneNode(true);
  element.replaceWith(newElement);
  newElement.addEventListener("click", function (e) {
    e.preventDefault();
    navigateToProductDetail(productId);
  });
}

// Navigate to product detail page
function navigateToProductDetail(productId) {
  window.location.href =
    "index.php?page=product-detail&id=" + encodeURIComponent(productId);
}

// Initialize comment form
function initCommentForm() {
  const commentForm = document.querySelector("#comment-form");
  if (!commentForm) {
    console.log("Comment form not found, user may not be logged in");
    return;
  }

  // Xử lý sự kiện khi người dùng chọn đánh giá sao
  const ratingInputs = commentForm.querySelectorAll('input[name="rating"]');
  ratingInputs.forEach((input) => {
    input.addEventListener("change", function () {
      // Hiển thị thông báo khi người dùng chọn đánh giá
      const ratingValue = this.value;
      showNotification(`Bạn đã chọn đánh giá ${ratingValue} sao`);
    });
  });

  const newForm = commentForm.cloneNode(true);
  commentForm.replaceWith(newForm);
  newForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const commentText = newForm.querySelector("textarea").value.trim();
    if (!commentText) {
      showNotification("Bình luận không được để trống", "error");
      return;
    }

    const formData = new FormData(newForm);
    fetch(newForm.action, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok)
          throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
      })
      .then((data) => {
        console.log("Comment response:", data); // Thêm log để debug

        // Hiển thị thông báo
        showNotification(data.message || "Đã gửi bình luận thành công");

        if (data.success) {
          // Đợi 1 giây để người dùng thấy thông báo, sau đó reload trang
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          if (data.message === "Vui lòng đăng nhập để gửi bình luận") {
            window.location.href =
              "index.php?page=login&redirect=" +
              encodeURIComponent(window.location.href);
          }
        }
      })
      .catch((error) => {
        console.error("Error submitting comment:", error);
        showNotification("Lỗi hệ thống. Vui lòng thử lại sau.", "error");
      });
  });
}

// Initialize pagination for comments
function initPagination() {
  const paginationButtons = document.querySelectorAll(".pagination-btn");
  paginationButtons.forEach((button) => {
    const newButton = button.cloneNode(true);
    button.replaceWith(newButton);
    newButton.addEventListener("click", function (e) {
      e.preventDefault();
      const page = parseInt(newButton.getAttribute("data-page"));
      const productId = newButton.getAttribute("data-product-id");
      loadComments(page, productId);
    });
  });
}

// Initialize comment actions (edit and delete)
function initCommentActions() {
  const editButtons = document.querySelectorAll(".edit-comment-btn");
  const deleteButtons = document.querySelectorAll(".delete-comment-btn");

  // Xử lý nút sửa bình luận
  editButtons.forEach((button) => {
    // Xóa sự kiện cũ nếu có
    const newButton = button.cloneNode(true);
    button.replaceWith(newButton);

    newButton.addEventListener("click", function () {
      const commentId = newButton.getAttribute("data-comment-id");
      const commentItem = document.querySelector(
        `.comment-item[data-comment-id="${commentId}"]`
      );
      const commentText = commentItem
        .querySelector(".comment-text")
        .textContent.trim();

      // Replace comment text with edit form
      commentItem.innerHTML = `
        <form class="edit-comment-form" data-comment-id="${commentId}">
          <textarea name="comment_text" required>${commentText}</textarea>
          <div class="edit-comment-actions">
            <button type="submit" class="submit-edit-comment-btn">Lưu</button>
            <button type="button" class="cancel-edit-comment-btn">Hủy</button>
          </div>
        </form>
      `;

      const editForm = commentItem.querySelector(".edit-comment-form");
      editForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const newCommentText = editForm.querySelector("textarea").value.trim();
        if (!newCommentText) {
          showNotification("Bình luận không được để trống", "error");
          return;
        }

        fetch("processes/edit_comment.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            comment_id: commentId,
            comment_text: newCommentText,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            console.log("Edit response:", data); // Thêm log để debug

            // Hiển thị thông báo
            showNotification(data.message || "Đã cập nhật bình luận");

            if (data.success) {
              // Đợi 1 giây để người dùng thấy thông báo, sau đó reload trang
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            } else {
              if (data.message === "Vui lòng đăng nhập để sửa bình luận") {
                window.location.href =
                  "index.php?page=login&redirect=" +
                  encodeURIComponent(window.location.href);
              }
            }
          })
          .catch((error) => {
            console.error("Error editing comment:", error);
            showNotification("Lỗi hệ thống. Vui lòng thử lại sau.", "error");
          });
      });

      const cancelButton = commentItem.querySelector(
        ".cancel-edit-comment-btn"
      );
      cancelButton.addEventListener("click", function () {
        // Reload trang để hủy chỉnh sửa
        window.location.reload();
      });
    });
  });

  deleteButtons.forEach((button) => {
    // Xóa sự kiện cũ nếu có
    const newButton = button.cloneNode(true);
    button.replaceWith(newButton);

    newButton.addEventListener("click", function () {
      if (!confirm("Bạn có chắc muốn xóa bình luận này?")) {
        return;
      }

      const commentId = newButton.getAttribute("data-comment-id");
      console.log("Deleting comment ID:", commentId); // Thêm log để debug

      fetch("processes/delete_comment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          comment_id: commentId,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          console.log("Delete response:", data); // Thêm log để debug

          // Hiển thị thông báo
          showNotification(data.message || "Đã xóa bình luận");

          if (data.success) {
            // Đợi 1 giây để người dùng thấy thông báo, sau đó reload trang
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          } else {
            if (data.message === "Vui lòng đăng nhập để xóa bình luận") {
              window.location.href =
                "index.php?page=login&redirect=" +
                encodeURIComponent(window.location.href);
            }
          }
        })
        .catch((error) => {
          console.error("Error deleting comment:", error);
          showNotification("Lỗi hệ thống. Vui lòng thử lại sau.", "error");
        });
    });
  });
}

// Show notification
function showNotification(message, type = "success") {
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    border-radius: 5px;
    z-index: 1000;
    background-color: ${type === "success" ? "#4CAF50" : "#F44336"};
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  `;

  document.body.appendChild(notification);

  // Tự động xóa thông báo sau 3 giây
  setTimeout(() => {
    notification.style.opacity = "0";
    notification.style.transition = "opacity 0.5s";
    setTimeout(() => {
      notification.remove();
    }, 500);
  }, 3000);
}

// Load comments via API
function loadComments(page, productId) {
  const fetchUrl = `/processes/get_comments.php?page=${page}&product_id=${productId}`;
  fetch(fetchUrl, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => {
      if (!response.ok)
        throw new Error(
          `Lỗi tải bình luận: ${response.status} - ${response.statusText}`
        );
      return response.json();
    })
    .then((data) => {
      const commentsList = document.querySelector(".comments-list");
      const paginationContainer = document.querySelector(".pagination");

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

            // Tạo HTML cho đánh giá sao
            let ratingHTML = "";
            if (comment.rating) {
              ratingHTML = '<div class="comment-rating">';
              for (let i = 1; i <= 5; i++) {
                ratingHTML += `<span class="star ${
                  i <= comment.rating ? "" : "empty"
                }">
                  <i class="fas fa-star"></i>
                </span>`;
              }
              ratingHTML += "</div>";
            }

            commentItem.innerHTML = `
              <div class="comment-header">
                <div class="comment-user-info">
                  <span class="comment-user">${comment.full_name}</span>
                  ${ratingHTML}
                </div>
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

            // Thêm nút sửa/xóa nếu người dùng là chủ bình luận
            const currentUserId = document.querySelector(
              'meta[name="user-id"]'
            )?.content;
            if (currentUserId && comment.user_id == currentUserId) {
              const actionsDiv = document.createElement("div");
              actionsDiv.className = "comment-actions";
              actionsDiv.innerHTML = `
                <button class="edit-comment-btn" data-comment-id="${comment.id}">Sửa</button>
                <button class="delete-comment-btn" data-comment-id="${comment.id}">Xóa</button>
              `;
              commentItem.appendChild(actionsDiv);
            }

            commentsList.appendChild(commentItem);
          }
        });
      }

      // Hiển thị đánh giá trung bình nếu có
      if (data.avg_rating && data.rating_count) {
        const ratingElement = document.querySelector(".product-rating");
        if (ratingElement) {
          // Cập nhật đánh giá trung bình
          const stars = ratingElement.querySelectorAll(".fa-star");
          stars.forEach((star, index) => {
            if (index < Math.floor(data.avg_rating)) {
              star.className = "fas fa-star filled";
            } else if (
              index < data.avg_rating &&
              index >= Math.floor(data.avg_rating)
            ) {
              star.className = "fas fa-star-half-alt filled";
            } else {
              star.className = "fas fa-star";
            }
          });

          // Cập nhật số lượng đánh giá
          const ratingCount = ratingElement.querySelector("span");
          if (ratingCount) {
            ratingCount.textContent = `(${data.rating_count} bình chọn)`;
          }
        }
      }

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
            nextBtn.setAttribute("data-product-id", productId); // Sửa lỗi ở đây
            nextBtn.textContent = "Sau";
            paginationContainer.appendChild(nextBtn);
          }

          initPagination();
        }
      }

      // Re-initialize comment actions after loading comments
      initCommentActions();
    })
    .catch((error) => {
      console.error("Error loading comments:", error);
      const commentsList = document.querySelector(".comments-list");
      commentsList.innerHTML = `<p>Lỗi tải bình luận: ${error.message}. Vui lòng kiểm tra đường dẫn hoặc thử lại.</p>`;
    });
}
