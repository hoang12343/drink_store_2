document.addEventListener("DOMContentLoaded", function () {
  // Lấy canvas
  const ctx = document.getElementById("revenueChart").getContext("2d");

  // Tạo nhãn cố định từ tháng 1 đến tháng 12
  const labels = [
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

  // Khởi tạo dữ liệu doanh thu với giá trị 0 cho tất cả các tháng
  const data = new Array(12).fill(0);

  // Ánh xạ dữ liệu từ monthlyRevenue
  monthlyRevenue.forEach((item) => {
    // Giả sử item.month có định dạng "YYYY-MM"
    const monthIndex = parseInt(item.month.split("-")[1]) - 1; // Lấy số tháng (0-11)
    data[monthIndex] = parseFloat(item.revenue);
  });

  // Tạo biểu đồ cột
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Doanh thu (VNĐ)",
          data: data,
          backgroundColor: "rgba(139, 94, 52, 0.6)", // Màu cognac-brown nhạt
          borderColor: "rgba(139, 94, 52, 1)", // Màu cognac-brown đậm
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
            text: "Tháng",
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
});
