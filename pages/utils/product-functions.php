<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

/**
 * Format price to Vietnamese currency format (e.g., 500000 -> 500.000 VND)
 * @param int|float $price The price to format
 * @return string Formatted price string
 */
function format_price($price)
{
    return number_format($price, 0, ',', '.') . ' VND';
}

/**
 * Retrieve products based on category, search, sort, limit, filters, and page
 * @param string $category Category name or 'all'
 * @param string $search Search query
 * @param string $sort Sort option
 * @param int $limit Number of products per page
 * @param array $filters Additional filters (price, country, etc.)
 * @param int $page Current page number
 * @return array Products and total count
 */
function get_products($category, $search, $sort, $limit, $filters, $page)
{
    global $pdo;
    $offset = ($page - 1) * $limit;
    $conditions = [];
    $params = [];

    // Handle search
    if (!empty($search)) {
        $search = trim($search);
        $conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Handle category
    if ($category !== 'all') {
        $category_array = array_map('trim', explode(',', $category));
        $placeholders = implode(',', array_fill(0, count($category_array), '?'));
        $conditions[] = "c.name IN ($placeholders)";
        $params = array_merge($params, $category_array);
    }

    // Handle filters
    if (!empty($filters['price_min'])) {
        $conditions[] = "p.price >= ?";
        $params[] = $filters['price_min'];
    }
    if (!empty($filters['price_max'])) {
        $conditions[] = "p.price <= ?";
        $params[] = $filters['price_max'];
    }
    if (!empty($filters['custom_price'])) {
        $conditions[] = "p.price BETWEEN ? AND ?";
        $params[] = $filters['custom_price'] * 0.9;
        $params[] = $filters['custom_price'] * 1.1;
    }
    if (!empty($filters['country']) && $filters['country'] !== 'all') {
        $conditions[] = "p.country = ?";
        $params[] = $filters['country'];
    }
    if (!empty($filters['type']) && $filters['type'] !== 'all') {
        $conditions[] = "p.type = ?";
        $params[] = $filters['type'];
    }
    if (!empty($filters['volume']) && $filters['volume'] !== 'all') {
        $conditions[] = "p.volume = ?";
        $params[] = $filters['volume'];
    }
    if (!empty($filters['grape']) && $filters['grape'] !== 'all') {
        $conditions[] = "p.grape = ?";
        $params[] = $filters['grape'];
    }

    // Build query
    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $query = "SELECT p.*, c.display_name AS category_name FROM products p JOIN categories c ON p.category_id = c.id $where";

    // Handle sorting
    switch ($sort) {
        case 'price_asc':
            $query .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY p.price DESC";
            break;
        case 'name_asc':
            $query .= " ORDER BY p.name ASC";
            break;
        case 'name_desc':
            $query .= " ORDER BY p.name DESC";
            break;
        case 'rating':
            $query .= " ORDER BY p.rating DESC";
            break;
        default:
            $query .= " ORDER BY p.id DESC";
    }

    // Count total products
    $count_query = "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id $where";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_products = $stmt->fetchColumn();

    // Fetch products with pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format prices
    foreach ($products as &$product) {
        $product['display_price'] = format_price($product['price']);
        if (!empty($product['old_price'])) {
            $product['display_old_price'] = format_price($product['old_price']);
        }
    }

    return [
        'products' => $products,
        'total_products' => $total_products
    ];
}

/**
 * Retrieve a single product by ID
 * @param int $product_id Product ID
 * @return array|null Product data or null if not found
 */
function get_product_by_id($product_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.display_name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $product['display_price'] = format_price($product['price']);
        if (!empty($product['old_price'])) {
            $product['display_old_price'] = format_price($product['old_price']);
        }
    }
    return $product;
}

/**
 * Retrieve related products in the same category
 * @param int $product_id Product ID
 * @param int $limit Number of related products
 * @return array Related products
 */
function get_related_products($product_id, $limit = 4)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.display_name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id != ? AND p.category_id = (SELECT category_id FROM products WHERE id = ?) LIMIT ?");
    $stmt->execute([$product_id, $product_id, $limit]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as &$product) {
        $product['display_price'] = format_price($product['price']);
        if (!empty($product['old_price'])) {
            $product['display_old_price'] = format_price($product['old_price']);
        }
    }
    return $products;
}

/**
 * Retrieve best-selling products based on order items
 * @param int $limit Number of products to retrieve
 * @return array Best-selling products
 */
function get_best_selling_products($limit = 4)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.display_name AS category_name, SUM(oi.quantity) as total_sold
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format prices
        foreach ($products as &$product) {
            $product['display_price'] = format_price($product['price']);
            if (!empty($product['old_price'])) {
                $product['display_old_price'] = format_price($product['old_price']);
            }
        }
        return $products;
    } catch (PDOException $e) {
        error_log("Error fetching best-selling products: " . $e->getMessage());
        return [];
    }
}

/**
 * Retrieve comments for a product with pagination
 * @param int $product_id Product ID
 * @param int $page Current page number
 * @param int $limit Number of comments per page
 * @return array List of comments and total count
 */
function get_product_comments($product_id, $page = 1, $limit = 10)
{
    global $pdo;
    try {
        $offset = ($page - 1) * $limit;

        // Count total comments
        $count_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM product_comments pc
            WHERE pc.product_id = :product_id
            AND TRIM(pc.comment_text) != ''
        ");
        $count_stmt->execute(['product_id' => $product_id]);
        $total_comments = $count_stmt->fetchColumn();

        // Fetch comments for the current page
        $stmt = $pdo->prepare("
            SELECT pc.id, pc.product_id, pc.user_id, pc.comment_text, pc.rating, pc.created_at, u.full_name
            FROM product_comments pc
            JOIN users u ON pc.user_id = u.id
            WHERE pc.product_id = :product_id
            AND TRIM(pc.comment_text) != ''
            ORDER BY pc.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'comments' => $comments,
            'total_comments' => $total_comments
        ];
    } catch (PDOException $e) {
        error_log('Error fetching comments: ' . $e->getMessage());
        return ['comments' => [], 'total_comments' => 0];
    }
}
