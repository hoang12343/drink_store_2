<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/contact.css?v=<?php echo time(); ?>">
</head>

<body>
    <section class="contact-page">
        <h1 class="page-title">Liên hệ với chúng tôi</h1>

        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="form-message <?php echo htmlspecialchars($_SESSION['flash_message']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="contact-container">
            <div class="contact-form">
                <form id="contactForm" method="POST"
                    action="<?php echo htmlspecialchars(BASE_URL . 'processes/contact_process.php'); ?>">
                    <h2>Gửi liên hệ</h2>
                    <div class="form-group">
                        <label for="name">Họ và tên <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="subject">Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Nội dung <span class="required">*</span></label>
                        <textarea id="message" 偽 name="message" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="submit-btn">Gửi liên hệ</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="<?php echo BASE_URL; ?>assets/js/contact.js?v=<?php echo time(); ?>"></script>
</body>

</html>