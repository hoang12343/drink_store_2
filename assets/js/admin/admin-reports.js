document.addEventListener("DOMContentLoaded", () => {
  // Report filter form validation
  const reportFilterForm = document.querySelector(".report-filter-form");
  if (reportFilterForm) {
    reportFilterForm.addEventListener("submit", (e) => {
      const startDate = document.querySelector("#start_date").value;
      const endDate = document.querySelector("#end_date").value;

      let errors = [];

      if (!startDate) {
        errors.push("Vui lòng chọn ngày bắt đầu.");
      }

      if (!endDate) {
        errors.push("Vui lòng chọn ngày kết thúc.");
      }

      if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
        errors.push("Ngày kết thúc phải sau ngày bắt đầu.");
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
      }
    });
  }
});
