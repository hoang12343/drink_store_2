// Define exportChart immediately as a fallback
window.exportChart = function () {
  console.warn("exportChart called before chart initialization");
  alert("Biểu đồ chưa sẵn sàng. Vui lòng thử lại sau.");
};

console.log("dashboard.js loaded");

document.addEventListener("DOMContentLoaded", function () {
  let chartInstance = null;

  const ctx = document.getElementById("revenueChart").getContext("2d");

  function formatDateLabel(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString("vi-VN", {
      day: "2-digit",
      month: "2-digit",
    });
  }

  let labels = [];
  let data = [];

  if (filterType === "day") {
    const start = new Date(start_date);
    const end = new Date(end_date);
    const dayCount = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

    labels = Array.from({ length: dayCount }, (_, i) => {
      const date = new Date(start);
      date.setDate(start.getDate() + i);
      return date.toISOString().split("T")[0];
    });

    data = new Array(dayCount).fill(0);

    revenueData.forEach((item) => {
      const index = labels.indexOf(item.period);
      if (index !== -1) {
        data[index] = parseFloat(item.revenue);
      }
    });

    labels = labels.map(formatDateLabel);
  } else if (filterType === "month") {
    labels = [
      "Tháng 1",
      "Tháng 2",
      "Tháng 3",
      "Tháng 4",
      "Tháng 5",
      "Tháng 6",
      "Tháng 7",
      "Tháng 8",
      "Tháng 9",
      "Tháng 10",
      "Tháng 11",
      "Tháng 12",
    ];

    data = new Array(12).fill(0);

    revenueData.forEach((item) => {
      const monthIndex = parseInt(item.period.split("-")[1]) - 1;
      data[monthIndex] = parseFloat(item.revenue);
    });
  } else {
    labels = revenueData.map((item) => `Năm ${item.period}`);
    data = revenueData.map((item) => parseFloat(item.revenue));
  }

  chartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Doanh thu (VNĐ)",
          data: data,
          backgroundColor: "rgba(139, 94, 52, 0.6)",
          borderColor: "rgba(139, 94, 52, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Doanh thu (VNĐ)",
          },
          ticks: {
            callback: function (value) {
              return value.toLocaleString("vi-VN");
            },
          },
        },
        x: {
          title: {
            display: true,
            text:
              filterType === "day"
                ? "Ngày"
                : filterType === "month"
                ? "Tháng"
                : "Năm",
          },
        },
      },
      plugins: {
        legend: {
          display: true,
          position: "top",
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || "";
              if (label) {
                label += ": ";
              }
              label += context.parsed.y.toLocaleString("vi-VN") + " VNĐ";
              return label;
            },
          },
        },
      },
    },
  });

  // Override exportChart with the actual implementation
  window.exportChart = function () {
    if (chartInstance) {
      const link = document.createElement("a");
      link.href = chartInstance.toBase64Image();
      link.download = `revenue_chart_${start_date}_to_${end_date}.png`;
      link.click();
      console.log("Chart exported successfully");
    } else {
      console.warn("Chart instance not available");
      alert("Biểu đồ chưa sẵn sàng. Vui lòng thử lại sau.");
    }
  };
});
