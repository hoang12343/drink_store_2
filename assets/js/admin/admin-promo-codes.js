$(document).ready(function () {
  // Xử lý tìm kiếm mã giảm giá
  $("#promo-search").on("input", function () {
    const searchValue = $(this).val().trim();
    const url = new URL(window.location.href);
    if (searchValue) {
      url.searchParams.set("search", searchValue);
    } else {
      url.searchParams.delete("search");
    }
    url.searchParams.delete("p"); // Reset về trang 1 khi tìm kiếm
    window.location.href = url.toString();
  });

  // Xử lý nút xóa
  $(".delete-promo-btn").click(function () {
    const promoId = $(this).data("id");
    const promoCode = $(this).data("code");
    $("#delete-promo-id").val(promoId);
    $("#delete-promo-code").text(promoCode);
    $("#delete-promo-modal").show();
  });

  // Đóng modal khi nhấp ra ngoài
  $("#delete-promo-modal").click(function (e) {
    if (e.target === this) {
      $(this).hide();
    }
  });

  // Validate form trước khi submit
  $("form.product-form").submit(function (e) {
    const startDate = new Date($("#start_date").val());
    const endDate = new Date($("#end_date").val());
    if (endDate <= startDate) {
      e.preventDefault();
      alert("Ngày kết thúc phải sau ngày bắt đầu.");
    }
  });
});
