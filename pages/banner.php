<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>
<link rel="stylesheet" href="../assets/css/banner.css">
<section class="banner">
    <div class="slider">
        <div class="slides" id="slidesContainer">
            <?php
            $banners = [
                'gilliot-do6.jpg' => 'Banner 1',
                'banner2.jpg' => 'Banner 2',
                'banner3.jpg' => 'Banner 3',
                'banner4.jpg' => 'Banner 4',
                'banner5.jpg' => 'Banner 5'
            ];
            foreach ($banners as $img => $alt) {
                echo "<div class='slide'><img src='../assets/images/$img' alt='$alt' loading='lazy'></div>";
            }
            ?>
        </div>
        <button class="prev" id="sliderPrev" aria-label="Previous Slide">&#10094;</button>
        <button class="next" id="sliderNext" aria-label="Next Slide">&#10095;</button>
        <div class="slider-dots" id="sliderDots"></div>
    </div>
</section>