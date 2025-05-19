<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once 'utils/product-functions.php';
require_once 'components/product-card.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$comment_page = filter_input(INPUT_GET, 'comment_page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
$product = get_product_by_id($product_id);

if (!$product) {
    http_response_code(404);
    include 'pages/404.php';
    exit;
}

$comments_data = get_product_comments($product_id, $comment_page, 10);
$comments = $comments_data['comments'];
$total_comments = $comments_data['total_comments'];
$comments_per_page = 10;
$total_pages = ceil($total_comments / $comments_per_page);
?>

<link rel="stylesheet" href="assets/css/product-detail.css?v=<?= time() ?>">
<script src="assets/js/product-detail.js?v=<?= time() ?>" defer></script>

<head>
    <!-- Các thẻ meta khác -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <meta name="user-id" content="<?= $_SESSION['user_id'] ?>">
    <?php endif; ?>
</head>

<section class="content product-detail-page">
    <div class="product-detail-layout">
        <!-- Product Images -->
        <div class="product-images">
            <div class="main-image">
                <img src="<?= htmlspecialchars($product['image'] ?? 'assets/images/placeholder.jpg') ?>"
                    alt="<?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?>">
            </div>
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <h1 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?></h1>

            <!-- Product Rating -->
            <div class="product-rating">
                <div class="rating-stars-display">
                    <?php
                    $rating = $product['rating'] ?? 0;
                    $full_stars = floor($rating);
                    $half_star = $rating - $full_stars >= 0.5;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

                    for ($i = 0; $i < $full_stars; $i++) {
                        echo '<i class="fas fa-star"></i>';
                    }

                    if ($half_star) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    }

                    for ($i = 0; $i < $empty_stars; $i++) {
                        echo '<i class="far fa-star"></i>';
                    }
                    ?>
                    <span class="rating-value"><?= number_format($rating, 1) ?></span>
                    <span class="rating-count">(<?= $product['reviews'] ?? 0 ?> đánh giá)</span>
                </div>
            </div>

            <!-- Product Labels -->
            <div class="product-labels">
                <div class="label-item">
                    <i class="fas fa-wine-bottle"></i>
                    <span>Loại: <?= htmlspecialchars($product['type'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-globe"></i>
                    <span>Quốc gia: <?= htmlspecialchars($product['country'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-percentage"></i>
                    <span>Nồng độ: <?= htmlspecialchars($product['abv'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-tint"></i>
                    <span>Dung tích: <?= htmlspecialchars($product['volume'] ?? '750ml') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-leaf"></i>
                    <span>Giống nho: <?= htmlspecialchars($product['grape'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-building"></i>
                    <span>Nhà sản xuất: <?= htmlspecialchars($product['brand'] ?? 'N/A') ?></span>
                </div>
            </div>

            <!-- Price -->
            <div class="product-price">
                <?php if (!empty($product['display_old_price'])): ?>
                    <span class="old-price"><?= htmlspecialchars($product['display_old_price']) ?></span>
                <?php endif; ?>
                <span class="current-price"><?= htmlspecialchars($product['display_price'] ?? 'Liên hệ') ?></span>
            </div>

            <!-- Stock Status -->
            <div class="stock-status">
                <span>Còn hàng: <?= ($product['stock'] ?? 0) > 0 ? $product['stock'] : 'Hết hàng' ?> sản phẩm</span>
            </div>

            <!-- Quantity Selector and Actions -->
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="quantity-btn decrease">-</button>
                    <input type="number" value="1" min="1" max="<?= $product['stock'] ?? 10 ?>" class="quantity-input">
                    <button class="quantity-btn increase">+</button>
                </div>
                <button class="add-to-cart-btn" data-product-code="<?= htmlspecialchars($product['id']) ?>"
                    <?= ($product['stock'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                    Thêm vào giỏ hàng
                </button>
                <button class="buy-now-btn" data-product-code="<?= htmlspecialchars($product['id']) ?>"
                    <?= ($product['stock'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                    Mua ngay
                </button>
            </div>

            <!-- Additional Info -->
            <div class="additional-info">
                <ul>
                    <?php
                    $additional_info = $product['additional_info'] ?? [
                        'Giá sản phẩm đã bao gồm VAT',
                        'Phí giao hàng tùy theo từng khu vực.',
                        'Đơn hàng từ 1.000.000 vnd miễn phí giao hàng.'
                    ];
                    foreach ($additional_info as $info): ?>
                        <li><?= htmlspecialchars($info) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <div class="product-description-section">
        <h2>Mô tả sản phẩm</h2>
        <p><?= htmlspecialchars($product['description'] ?? 'Không có mô tả') ?></p>
    </div>

    <!-- Product Comments -->
    <div class="product-comments-section">
        <h2>Bình luận sản phẩm</h2>

        <!-- Comment Form -->
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <form id="comment-form" action="processes/add_comment.php" method="POST" accept-charset="UTF-8">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">

                <!-- Rating Stars -->
                <div class="rating-stars">
                    <input type="radio" id="star5" name="rating" value="5" />
                    <label for="star5" class="star-icon"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star4" name="rating" value="4" />
                    <label for="star4" class="star-icon"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star3" name="rating" value="3" />
                    <label for="star3" class="star-icon"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star2" name="rating" value="2" />
                    <label for="star2" class="star-icon"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star1" name="rating" value="1" />
                    <label for="star1" class="star-icon"><i class="fas fa-star"></i></label>
                    <span class="rating-text">Đánh giá sản phẩm</span>
                </div>

                <div class="comment-input">
                    <textarea name="comment_text" placeholder="Viết bình luận của bạn..." required></textarea>
                    <button type="submit" class="submit-comment-btn">Gửi bình luận</button>
                </div>
            </form>
        <?php else: ?>
            <p>Vui lòng <a
                    href="index.php?page=login&redirect=<?= urlencode('index.php?page=product-detail&id=' . $product['id']) ?>">đăng
                    nhập</a> để gửi bình luận.</p>
        <?php endif; ?>

        <!-- Comments List -->
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <p>Chưa có bình luận nào cho sản phẩm này.</p>
            <?php else: ?>
                <?php
                $displayed_comments = []; // Prevent duplicate comments
                foreach ($comments as $comment):
                    if (!empty(trim($comment['comment_text'])) && !in_array($comment['id'], $displayed_comments)):
                        $displayed_comments[] = $comment['id'];
                        $is_owner = (isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id']);
                ?>
                        <div class="comment-item" data-comment-id="<?= htmlspecialchars($comment['id']) ?>">
                            <div class="comment-header">
                                <div class="comment-user-info">
                                    <span class="comment-user"><?= htmlspecialchars($comment['full_name']) ?></span>
                                    <?php if (!empty($comment['rating'])): ?>
                                        <div class="comment-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?= $i <= $comment['rating'] ? '' : 'empty' ?>">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="comment-date"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <p class="comment-text"><?= htmlspecialchars($comment['comment_text']) ?></p>
                            <?php if ($is_owner): ?>
                                <div class="comment-actions">
                                    <button class="edit-comment-btn"
                                        data-comment-id="<?= htmlspecialchars($comment['id']) ?>">Sửa</button>
                                    <button class="delete-comment-btn"
                                        data-comment-id="<?= htmlspecialchars($comment['id']) ?>">Xóa</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($comment_page > 1): ?>
                    <a href="#" class="pagination-btn" data-page="<?= $comment_page - 1 ?>"
                        data-product-id="<?= htmlspecialchars($product['id']) ?>">Trước</a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $comment_page - 2);
                $end_page = min($total_pages, $comment_page + 2);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="#" class="pagination-btn <?= $i === $comment_page ? 'active' : '' ?>" data-page="<?= $i ?>"
                        data-product-id="<?= htmlspecialchars($product['id']) ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($comment_page < $total_pages): ?>
                    <a href="#" class="pagination-btn" data-page="<?= $comment_page + 1 ?>"
                        data-product-id="<?= htmlspecialchars($product['id']) ?>">Sau</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Products -->
    <div class="related-products-section">
        <h2>Sản phẩm liên quan</h2>
        <div class="products-grid">
            <?php
            $related_products = get_related_products($product['id'], 4);
            foreach ($related_products as $related_product) {
                echo display_product($related_product);
            }
            ?>
        </div>
    </div>
</section>