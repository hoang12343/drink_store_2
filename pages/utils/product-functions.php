<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Fetch products with filtering
function get_products($category = 'all', $search = '', $sort = 'default', $limit = 12, $filters = [])
{
    // Sample product data (replace with database query in a real application)
    $all_products = [
        ['id' => 1, 'name' => 'Rượu vang đỏ Pháp', 'code' => 'RV001', 'price' => '1200000', 'display_price' => '1.200.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 25, 'rating' => 4.5, 'country' => 'Pháp', 'type' => 'Đỏ', 'volume' => '750ml', 'grape' => 'Cabernet Sauvignon'],
        ['id' => 4, 'name' => 'Cognac Pháp', 'code' => 'CG001', 'price' => '2300000', 'display_price' => '2.300.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 12, 'rating' => 4.9, 'country' => 'Pháp', 'type' => 'Đỏ', 'volume' => '700ml', 'grape' => 'Merlot'],
        ['id' => 6, 'name' => 'Rượu vang trắng Ý', 'code' => 'RV045', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 15, 'rating' => 4.2, 'country' => 'Ý', 'type' => 'Trắng', 'volume' => '750ml', 'grape' => 'Chardonnay'],
        ['id' => 7, 'name' => 'Champagne Pháp', 'code' => 'CH001', 'price' => '2100000', 'display_price' => '2.100.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 10, 'rating' => 4.7, 'country' => 'Pháp', 'type' => 'Sủi', 'volume' => '750ml', 'grape' => 'Pinot Noir'],
        ['id' => 11, 'name' => 'Rượu vang Chile', 'code' => 'RV023', 'price' => '640000', 'display_price' => '640.000 ₫', 'old_price' => '800.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 28, 'rating' => 4.1, 'promotion' => true, 'country' => 'Chile', 'type' => 'Đỏ', 'volume' => '750ml', 'grape' => 'Syrah'],
        ['id' => 15, 'name' => 'Rượu vang Úc', 'code' => 'RV056', 'price' => '550000', 'display_price' => '550.000 ₫', 'old_price' => '690.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 20, 'rating' => 4.2, 'promotion' => true, 'country' => 'Úc', 'type' => 'Trắng', 'volume' => '750ml', 'grape' => 'Sauvignon Blanc'],
    ];

    // Filter by category
    if ($category !== 'all') {
        $filtered_products = array_filter($all_products, function ($product) use ($category) {
            if ($category === 'promotion' && isset($product['promotion']) && $product['promotion']) {
                return true;
            }
            return $product['category'] === $category;
        });
    } else {
        $filtered_products = $all_products;
    }

    // Filter by search term
    if (!empty($search)) {
        $filtered_products = array_filter($filtered_products, function ($product) use ($search) {
            return stripos($product['name'], $search) !== false ||
                stripos($product['code'], $search) !== false;
        });
    }

    // Apply additional filters (price range, country, type, volume, grape)
    if (!empty($filters)) {
        if (isset($filters['price_min']) && isset($filters['price_max'])) {
            $filtered_products = array_filter($filtered_products, function ($product) use ($filters) {
                $price = (int) str_replace('.', '', $product['price']);
                return $price >= $filters['price_min'] && $price <= $filters['price_max'];
            });
        }
        if (isset($filters['custom_price'])) {
            $custom_price = (int) str_replace('.', '', $filters['custom_price']);
            $filtered_products = array_filter($filtered_products, function ($product) use ($custom_price) {
                $price = (int) str_replace('.', '', $product['price']);
                return $price <= $custom_price + 100000 && $price >= $custom_price - 100000; // ±100,000 range
            });
        }
        if (isset($filters['country']) && $filters['country'] !== 'all') {
            $filtered_products = array_filter($filtered_products, function ($product) use ($filters) {
                return $product['country'] === $filters['country'];
            });
        }
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $filtered_products = array_filter($filtered_products, function ($product) use ($filters) {
                return $product['type'] === $filters['type'];
            });
        }
        if (isset($filters['volume']) && $filters['volume'] !== 'all') {
            $filtered_products = array_filter($filtered_products, function ($product) use ($filters) {
                return $product['volume'] === $filters['volume'];
            });
        }
        if (isset($filters['grape']) && $filters['grape'] !== 'all') {
            $filtered_products = array_filter($filtered_products, function ($product) use ($filters) {
                return $product['grape'] === $filters['grape'];
            });
        }
    }

    // Sort products
    switch ($sort) {
        case 'price_asc':
            usort($filtered_products, function ($a, $b) {
                return $a['price'] - $b['price'];
            });
            break;
        case 'price_desc':
            usort($filtered_products, function ($a, $b) {
                return $b['price'] - $a['price'];
            });
            break;
        case 'name_asc':
            usort($filtered_products, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            break;
        case 'name_desc':
            usort($filtered_products, function ($a, $b) {
                return strcmp($b['name'], $a['name']);
            });
            break;
        case 'rating':
            usort($filtered_products, function ($a, $b) {
                return $b['rating'] - $a['rating'];
            });
            break;
        default:
            usort($filtered_products, function ($a, $b) {
                return $b['id'] - $a['id'];
            });
    }

    return array_slice($filtered_products, 0, $limit);
}