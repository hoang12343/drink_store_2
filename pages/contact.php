<?php
if (!defined('APP_START')) exit('No direct access');
?>

<div class="contact-page">
    <h1 class="page-title">Liên Hệ Với Chúng Tôi</h1>
    <div class="contact-container">
        <div class="contact-form">
            <h2>Gửi Tin Nhắn</h2>
            <?php if (isset($_GET['success']) && $_GET['success'] === 'sent'): ?>
                <div class="form-message success">Tin nhắn của bạn đã được gửi thành công!</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="form-message error"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            <?php endif; ?>
            <form action="index.php?page=contact_process" method="post">
                <div class="form-group">
                    <label for="name">Họ và Tên <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="Nhập họ và tên">
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="Nhập email">
                </div>
                <div class="form-group">
                    <label for="phone">Số Điện Thoại</label>
                    <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại">
                </div>
                <div class="form-group">
                    <label for="subject">Tiêu Đề <span class="required">*</span></label>
                    <input type="text" id="subject" name="subject" required placeholder="Nhập tiêu đề">
                </div>
                <div class="form-group">
                    <label for="message">Nội Dung <span class="required">*</span></label>
                    <textarea id="message" name="message" required placeholder="Nhập nội dung tin nhắn"></textarea>
                </div>
                <button type="submit" class="submit-btn">Gửi Tin Nhắn</button>
            </form>
        </div>
    </div>
</div>