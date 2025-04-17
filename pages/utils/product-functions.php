<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

function get_products($category = 'all', $search = '', $sort = 'default', $limit = 12, $filters = [])
{
    $all_products = [
        [
            'id' => 1,
            'name' => 'Rượu vang Ý 60 Sessantanni Limited Edition (24 Karat Gold)',
            'code' => 'RV001',
            'price' => '1870000',
            'display_price' => '1.870.000 ₫',
            'image' => '/api/placeholder/220/300', // Adjusted height for bottle image
            'category' => 'wine',
            'stock' => 25,
            'grape' => 'Primitivo',
            'type' => 'Rượu vang đỏ',
            'brand' => 'San Marzano',
            'country' => 'Vang Ý (Italy)',
            'abv' => '13% ABV',
            'description' => 'Mẫu chai nho 60 năm từ thương hiệu Primitivo. Manduria D.O.P được làm phiên bản vang Ý đặc biệt.'
        ],
        [
            'id' => 4,
            'name' => 'Cognac Pháp',
            'code' => 'CG001',
            'price' => '2300000',
            'display_price' => '2.300.000 ₫',
            'image' => '/api/placeholder/220/300',
            'category' => 'wine',
            'stock' => 12,
            'grape' => 'Merlot',
            'type' => 'Rượu vang đỏ',
            'brand' => 'San Marzano',
            'country' => 'Pháp',
            'abv' => '14% ABV',
            'description' => 'Rượu vang cao cấp từ Pháp, hương vị đậm đà.'
        ],
        [
            'id' => 6,
            'name' => 'Rượu vang trắng Ý',
            'code' => 'RV045',
            'price' => '950000',
            'display_price' => '950.000 ₫',
            'image' => '/api/placeholder/220/300',
            'category' => 'wine',
            'stock' => 15,
            'grape' => 'Chardonnay',
            'type' => 'Rượu vang trắng',
            'brand' => 'San Marzano',
            'country' => 'Vang Ý (Italy)',
            'abv' => '12% ABV',
            'description' => 'Rượu vang trắng Ý, nhẹ nhàng và thanh lịch.'
        ],
        [
            'id' => 7,
            'name' => 'Champagne Pháp',
            'code' => 'CH001',
            'price' => '2100000',
            'display_price' => '2.100.000 ₫',
            'image' => '/api/placeholder/220/300',
            'category' => 'wine',
            'stock' => 10,
            'grape' => 'Pinot Noir',
            'type' => 'Rượu vang sủi',
            'brand' => 'San Marzano',
            'country' => 'Pháp',
            'abv' => '12.5% ABV',
            'description' => 'Champagne Pháp cao cấp, lý tưởng cho các dịp lễ.'
        ],
        [
            'id' => 11,
            'name' => 'Rượu vang Chile',
            'code' => 'RV023',
            'price' => '640000',
            'display_price' => '640.000 ₫',
            'old_price' => '800.000 ₫',
            'discount' => '-20%',
            'image' => '/api/placeholder/220/300',
            'category' => 'wine',
            'stock' => 28,
            'promotion' => true,
            'grape' => 'Syrah',
            'type' => 'Rượu vang đỏ',
            'brand' => 'San Marzano',
            'country' => 'Chile',
            'abv' => '13.5% ABV',
            'description' => 'Rượu vang Chile đậm đà, giá trị tốt.'
        ],
        [
            'id' => 15,
            'name' => 'Rượu vang Úc',
            'code' => 'RV056',
            'price' => '550000',
            'display_price' => '550.000 ₫',
            'old_price' => '690.000 ₫',
            'discount' => '-20%',
            'image' => '/api/placeholder/220/300',
            'category' => 'wine',
            'stock' => 20,
            'promotion' => true,
            'grape' => 'Sauvignon Blanc',
            'type' => 'Rượu vang trắng',
            'brand' => 'San Marzano',
            'country' => 'Úc',
            'abv' => '12% ABV',
            'description' => 'Rượu vang Úc tươi mát, dễ uống.'
        ],
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
                return $price <= $custom_price + 100000 && $price >= $custom_price - 100000;
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

function get_product_by_id($id)
{
    $products = get_products(); // Reuse function to get all products

    foreach ($products as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }

    return null; // Product not found
}
