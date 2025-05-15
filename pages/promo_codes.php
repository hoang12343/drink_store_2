<?php
// Đảm bảo session đã được khởi tạo bởi index.php
if (!isset($_SESSION)) {
    session_start();
}
require_once ROOT_PATH . '/includes/db_connect.php'; // Kết nối cơ sở dữ liệu

// Xử lý thêm mã giảm giá
$success = $error = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_promo'])) {
    $code = $_POST['code'];
    $discount_percentage = $_POST['discount_percentage'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $min_order_value = $_POST['min_order_value'] ?? 0.00;
    $max_discount_value = $_POST['max_discount_value'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $sql = "INSERT INTO promo_codes (code, discount_percentage, start_date, end_date, min_order_value, max_discount_value, is_active)
                VALUES (:code, :discount_percentage, :start_date, :end_date, :min_order_value, :max_discount_value, :is_active)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':code' => $code,
            ':discount_percentage' => $discount_percentage,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':min_order_value' => $min_order_value,
            ':max_discount_value' => $max_discount_value,
            ':is_active' => $is_active
        ]);
        $success = "Thêm mã giảm giá thành công!";
    } catch(PDOException $e) {
        $error = "Lỗi khi thêm mã giảm giá: " . $e->getMessage();
    }
}

// Lấy danh sách mã giảm giá
$sql = "SELECT * FROM promo_codes";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$promo_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="promo-codes-container">
    <h2>Quản lý mã giảm giá</h2>

    <!-- Thông báo thành công hoặc lỗi -->
    <?php if (isset($success)) { ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>
    <?php if (isset($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <!-- Form thêm mã giảm giá -->
    <div class="form-container">
        <h3>Thêm mã giảm giá mới</h3>
        <form method="POST" action="">
            <label>Mã giảm giá:</label>
            <input type="text" name="code" required maxlength="20"><br>
            <label>Phần trăm giảm (%):</label>
            <input type="number" name="discount_percentage" step="0.01" min="0" max="100" required><br>
            <label>Ngày bắt đầu:</label>
            <input type="datetime-local" name="start_date" required><br>
            <label>Ngày kết thúc:</label>
            <input type="datetime-local" name="end_date" required><br>
            <label>Giá trị đơn tối thiểu:</label>
            <input type="number" name="min_order_value" step="0.01" min="0" value="0.00"><br>
            <label>Giảm tối đa:</label>
            <input type="number" name="max_discount_value" step="0.01" min="0"><br>
            <label>Trạng thái hoạt động:</label>
            <input type="checkbox" name="is_active" checked><br>
            <input type="submit" name="add_promo" value="Thêm mã giảm giá">
        </form>
    </div>

    <!-- Danh sách mã giảm giá -->
    <h3>Danh sách mã giảm giá</h3>
    <?php if (count($promo_codes) > 0) { ?>
    <table>
        <thead>
            <tr>
                <th>Chọn</th>
                <th>Mã giảm giá</th>
                <th>Phần trăm giảm (%)</th>
                <th>Giảm tối đa</th>
                <th>Ngày bắt đầu</th>
                <th>Ngày kết thúc</th>
                <th>Giá trị đơn tối thiểu</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promo_codes as $promo) { ?>
            <tr>
                <td>
                    <input type="radio" name="promo_code" class="promo-radio"
                        value="<?php echo htmlspecialchars($promo['code']); ?>"
                        data-code="<?php echo htmlspecialchars($promo['code']); ?>"
                        data-discount="<?php echo number_format($promo['discount_percentage'], 2); ?>"
                        data-max-discount="<?php echo $promo['max_discount_value'] ? number_format($promo['max_discount_value'], 2) : 'Không giới hạn'; ?>"
                        data-start-date="<?php echo date('d/m/Y H:i', strtotime($promo['start_date'])); ?>"
                        data-end-date="<?php echo date('d/m/Y H:i', strtotime($promo['end_date'])); ?>"
                        data-min-order="<?php echo number_format($promo['min_order_value'], 2); ?>"
                        data-status="<?php echo $promo['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>">
                </td>
                <td><?php echo htmlspecialchars($promo['code']); ?></td>
                <td><?php echo number_format($promo['discount_percentage'], 2); ?>%</td>
                <td><?php echo $promo['max_discount_value'] ? number_format($promo['max_discount_value'], 2) : 'Không giới hạn'; ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($promo['start_date'])); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($promo['end_date'])); ?></td>
                <td><?php echo number_format($promo['min_order_value'], 2); ?></td>
                <td><?php echo $promo['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <button class="apply-button">Áp dụng mã</button>
    <div id="result"></div>
    <?php } else { ?>
    <p>Chưa có mã giảm giá nào.</p>
    <?php } ?>

    <!-- Popup chi tiết mã giảm giá -->
    <div id="promo-detail-popup" class="popup">
        <div class="popup-content">
            <span class="close-button">×</span>
            <h3>Chi tiết mã giảm giá</h3>
            <p><strong>Mã giảm giá:</strong> <span id="popup-code"></span></p>
            <p><strong>Phần trăm giảm:</strong> <span id="popup-discount"></span>%</p>
            <p><strong>Giảm tối đa:</strong> <span id="popup-max-discount"></span></p>
            <p><strong>Ngày bắt đầu:</strong> <span id="popup-start-date"></span></p>
            <p><strong>Ngày kết thúc:</strong> <span id="popup-end-date"></span></p>
            <p><strong>Giá trị đơn tối thiểu:</strong> <span id="popup-min-order"></span></p>
            <p><strong>Trạng thái:</strong> <span id="popup-status"></span></p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Xử lý áp dụng mã giảm giá
    $('.apply-button').click(function() {
        const selectedPromo = $('input[name="promo_code"]:checked').val();
        const selectedItems = []; // Giả sử danh sách sản phẩm từ giỏ hàng

        if (!selectedPromo) {
            alert('Vui lòng chọn một mã giảm giá.');
            return;
        }

        $.ajax({
            url: '<?php echo BASE_URL; ?>processes/apply_promo.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                code: selectedPromo,
                selected_items: selectedItems
            }),
            success: function(response) {
                const resultDiv = $('#result');
                if (response.success) {
                    resultDiv.html(`
                        <p><strong>Áp dụng mã thành công!</strong></p>
                        <p>Tổng phụ: ${response.subtotal}</p>
                        <p>Giảm giá: ${response.discount}</p>
                        <p>Phí vận chuyển: ${response.shipping}</p>
                        <p>Tổng cộng: ${response.total}</p>
                    `).css('border-color', 'green');
                } else {
                    resultDiv.html(`<p>Lỗi: ${response.message}</p>`).css('border-color',
                        'red');
                }
            },
            error: function() {
                $('#result').html('<p>Lỗi hệ thống. Vui lòng thử lại.</p>').css(
                    'border-color', 'red');
            }
        });
    });

    // Xử lý hiển thị popup chi tiết khi chọn radio button
    $('.promo-radio').click(function() {
        const radio = $(this);
        $('#popup-code').text(radio.data('code'));
        $('#popup-discount').text(radio.data('discount'));
        $('#popup-max-discount').text(radio.data('max-discount'));
        $('#popup-start-date').text(radio.data('start-date'));
        $('#popup-end-date').text(radio.data('end-date'));
        $('#popup-min-order').text(radio.data('min-order'));
        $('#popup-status').text(radio.data('status'));
        $('#promo-detail-popup').fadeIn();
    });

    // Đóng popup
    $('.close-button').click(function() {
        $('#promo-detail-popup').fadeOut();
    });

    // Đóng popup khi nhấp ra ngoài
    $(window).click(function(event) {
        if (event.target.id === 'promo-detail-popup') {
            $('#promo-detail-popup').fadeOut();
        }
    });
});
</script>