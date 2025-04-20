<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>

<!-- Banner Section -->
<section class="banner-section">
    <?php if (isset($current_page) && $current_page === 'home'): ?>
        <!-- Static Main Banner (Homepage Only) -->
        <div class="main-banner">
            <img src="/api/placeholder/1400/400?text=Main+Banner" alt="Main Banner" class="banner-img" loading="lazy">
            <div class="banner-content">
                <h1>Chào mừng đến với Cửa hàng đồ uống</h1>
                <p>Khám phá các loại đồ uống cao cấp, đa dạng với giá tốt nhất</p>
                <a href="?page=products" class="btn-shop-now">Mua ngay</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Banner Slider -->
    <div class="slider">
        <div class="slides">
            <!-- Slide 1: Premium Wine Collection -->
            <div class="slide">
                <img src="/api/placeholder/1400/500?text=Premium+Wine+Collection" alt="Premium Wine Collection"
                    loading="lazy">
                <div class="slide-content">
                    <h2>Khám Phá Bộ Sưu Tập Rượu Vang Cao Cấp</h2>
                    <p>Ưu đãi đặc biệt lên đến 30% cho các dòng rượu vang nhập khẩu</p>
                    <a href="?page=products&category=wine" class="btn-shop-now">Mua Ngay</a>
                </div>
            </div>
            <!-- Slide 2: Exclusive Brandy Offers -->
            <div class="slide">
                <img src="/api/placeholder/1400/500?text=Exclusive+Brandy+Offers" alt="Exclusive Brandy Offers"
                    loading="lazy">
                <div class="slide-content">
                    <h2>Rượu Mạnh Thượng Hạng</h2>
                    <p>Trải nghiệm Cognac và Brandy với giá ưu đãi đặc biệt</p>
                    <a href="?page=products&category=brandy" class="btn-shop-now">Khám Phá Ngay</a>
                </div>
            </div>
            <!-- Slide 3: Luxury Crystal Glasses -->
            <div class="slide">
                <img src="/api/placeholder/1400/500?text=Luxury+Crystal+Glasses" alt="Luxury Crystal Glasses"
                    loading="lazy">
                <div class="slide-content">
                    <h2>Ly Pha Lê Riedel Sang Trọng</h2>
                    <p>Nâng tầm trải nghiệm thưởng thức đồ uống của bạn</p>
                    <a href="?page=products&category=crystal_glasses" class="btn-shop-now">Xem Bộ Sưu Tập</a>
                </div>
            </div>
        </div>
        <!-- Navigation Buttons -->
        <button class="prev">❮</button>
        <button class="next">❯</button>
        <!-- Slider Dots -->
        <div class="slider-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>
</section>

<?php
// Include the banner slider JavaScript
?>
<script src="assets/js/bannerSlider.js" defer></script>