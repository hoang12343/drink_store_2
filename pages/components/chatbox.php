<!-- Chatbox Container -->
<div class="chatbox-wrapper">
    <div class="chatbox-toggle">
        <i class="fas fa-comment-dots"></i>
    </div>
    <div class="chatbox-content">
        <div class="chatbox-header">
            <h4>Trợ lý AI - Cửa hàng đồ uống</h4>
            <span class="chatbox-close"><i class="fas fa-times"></i></span>
        </div>
        <div class="chatbox-messages" id="chatboxMessages">
            <div class="message bot-message">
                <p>Xin chào! Tôi là trợ lý AI của Cửa hàng đồ uống. Bạn cần giúp gì hôm nay?</p>
            </div>
        </div>
        <div class="chatbox-input">
            <textarea id="chatboxInput" placeholder="Nhập câu hỏi của bạn..." rows="1"></textarea>
            <button id="chatboxSend"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<!-- Liên kết CSS và JS cho chatbox -->
<link rel="stylesheet" href="assets/css/chatbox.css?v=<?= time() ?>">
<script src="assets/js/chatbox.js?v=<?= time() ?>" defer></script>