<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>
<link rel="stylesheet" href="assets/css/banner.css">
<section class="banner-section" aria-label="Promotional Banners">
    <!-- Static Main Banner (Homepage Only) -->
    <?php if (isset($current_page) && $current_page === 'home'): ?>
    <div class="main-banner">
        <img src="/api/placeholder/1400/400?text=Main+Banner" alt="Welcome to Beverage Store" class="banner-img"
            loading="lazy">
        <div class="banner-content">
            <h1>Discover Premium Beverages</h1>
            <p>Explore our exclusive collection with the best prices</p>
            <a href="?page=products" class="btn-shop-now" aria-label="Shop Now for Beverages">Shop Now</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Banner Slider -->
    <div class="banner-slider" tabindex="0" aria-label="Promotional Slider">
        <div class="banner-slides">
            <!-- Slide 1 -->
            <div class="banner-slide">
                <img data-src="/api/placeholder/1400/500?text=Wine+Collection" alt="Premium Wine Collection"
                    loading="lazy">
                <div class="slide-content">
                    <h2>Premium Wine Collection</h2>
                    <p>Up to 30% off on imported wines</p>
                    <a href="?page=products&category=wine" class="btn-shop-now" aria-label="Shop Wine Collection">Shop
                        Now</a>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="banner-slide">
                <img data-src="assets/images/ruou-manh-home.jpg" alt="Exclusive Brandy Offers" loading="lazy">
                <div class="slide-content">
                    <h2>Exclusive Brandy Offers</h2>
                    <p>Discover premium Cognac and Brandy deals</p>
                    <a href="?page=products&category=brandy" class="btn-shop-now"
                        aria-label="Shop Brandy Offers">Explore Now</a>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="banner-slide">
                <img data-src="/api/placeholder/1400/500?text=Crystal+Glasses" alt="Luxury Crystal Glasses"
                    loading="lazy">
                <div class="slide-content">
                    <h2>Luxury Crystal Glasses</h2>
                    <p>Elevate your drinking experience</p>
                    <a href="?page=products&category=crystal_glasses" class="btn-shop-now"
                        aria-label="Shop Crystal Glasses">View Collection</a>
                </div>
            </div>
        </div>
        <!-- Navigation -->
        <button class="banner-prev">❮</button>
        <button class="banner-next">❯</button>
        <div class="banner-dots"></div>
    </div>
</section>

<?php if (isset($current_page) && $current_page === 'home'): ?>
<script src="assets/js/bannerSlider.js" defer></script>
<?php endif; ?>