<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';

/**
 * Định dạng giá thành chuỗi tiền tệ Việt Nam
 */
function format_price($price)
{
    return number_format($price, 0, ',', '.') . ' ₫';
}

/**
 * Khởi tạo cơ sở dữ liệu và bảng
 */
function initialize_database()
{
    global $db_config, $pdo;

    try {
        // Kết nối không chọn database để tạo database
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};charset={$db_config['charset']}";
        $temp_pdo = new PDO($dsn, $db_config['user'], $db_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        // Tạo cơ sở dữ liệu
        $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS {$db_config['name']} CHARACTER SET {$db_config['charset']} COLLATE utf8mb4_unicode_ci");
        $temp_pdo->exec("USE {$db_config['name']}");

        // Tạo bảng categories
        $temp_pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                display_name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tạo bảng products
        $temp_pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(20) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                category_id INT NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                old_price DECIMAL(10, 2) DEFAULT NULL,
                discount VARCHAR(10) DEFAULT NULL,
                stock INT NOT NULL DEFAULT 0,
                image VARCHAR(255) DEFAULT NULL,
                grape VARCHAR(100) DEFAULT NULL,
                type VARCHAR(100) DEFAULT NULL,
                brand VARCHAR(100) DEFAULT NULL,
                country VARCHAR(100) DEFAULT NULL,
                abv VARCHAR(10) DEFAULT NULL,
                volume VARCHAR(50) DEFAULT '750ml',
                description TEXT,
                rating DECIMAL(3, 1) DEFAULT 0.0,
                reviews INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
            )
        ");

        // Tạo bảng promotions
        $temp_pdo->exec("
            CREATE TABLE IF NOT EXISTS promotions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                discount_percentage DECIMAL(5, 2) NOT NULL,
                start_date DATETIME NOT NULL,
                end_date DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        ");

        return true;
    } catch (PDOException $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy danh sách sản phẩm
 */
function get_products($category = 'all', $search = '', $sort = 'default', $limit = 30, $filters = [])
{
    global $pdo;
    try {
        $query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];

        if ($category !== 'all' && $category !== 'promotion') {
            $query .= " AND c.name = :category";
            $params[':category'] = $category;
        } elseif ($category === 'promotion') {
            $query .= " AND EXISTS (SELECT 1 FROM promotions pr WHERE pr.product_id = p.id)";
        }

        if (!empty($search)) {
            $query .= " AND (p.name LIKE :search OR p.code LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Áp dụng bộ lọc
        if (!empty($filters['price_min']) && !empty($filters['price_max'])) {
            $query .= " AND p.price BETWEEN :price_min AND :price_max";
            $params[':price_min'] = $filters['price_min'];
            $params[':price_max'] = $filters['price_max'];
        }
        if (!empty($filters['custom_price'])) {
            $query .= " AND p.price BETWEEN :custom_price_min AND :custom_price_max";
            $params[':custom_price_min'] = $filters['custom_price'] - 100000;
            $params[':custom_price_max'] = $filters['custom_price'] + 100000;
        }
        if (!empty($filters['country']) && $filters['country'] !== 'all') {
            $query .= " AND p.country = :country";
            $params[':country'] = $filters['country'];
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query .= " AND p.type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['volume']) && $filters['volume'] !== 'all') {
            $query .= " AND p.volume = :volume";
            $params[':volume'] = $filters['volume'];
        }
        if (!empty($filters['grape']) && $filters['grape'] !== 'all') {
            $query .= " AND p.grape = :grape";
            $params[':grape'] = $filters['grape'];
        }

        // Sắp xếp
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

        $query .= " LIMIT :limit";
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        // Thêm display_price và display_old_price
        foreach ($products as &$product) {
            $product['display_price'] = format_price($product['price']);
            if ($product['old_price']) {
                $product['display_old_price'] = format_price($product['old_price']);
            } else {
                $product['display_old_price'] = '';
            }
        }

        return $products;
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy sản phẩm theo ID
 */
function get_product_by_id($id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch();

        if ($product) {
            $product['display_price'] = format_price($product['price']);
            $product['display_old_price'] = $product['old_price'] ? format_price($product['old_price']) : '';
        }

        return $product ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching product by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Lấy sản phẩm liên quan
 */
function get_related_products($product_id, $limit = 4)
{
    global $pdo;
    try {
        // Lấy danh mục của sản phẩm hiện tại
        $stmt = $pdo->prepare("SELECT category_id FROM products WHERE id = :id");
        $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $category_id = $stmt->fetchColumn();

        // Lấy sản phẩm cùng danh mục, loại trừ sản phẩm hiện tại
        $query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = :category_id AND p.id != :product_id 
                  ORDER BY p.rating DESC LIMIT :limit";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        // Thêm display_price và display_old_price
        foreach ($products as &$product) {
            $product['display_price'] = format_price($product['price']);
            $product['display_old_price'] = $product['old_price'] ? format_price($product['old_price']) : '';
        }

        return $products;
    } catch (PDOException $e) {
        error_log("Error fetching related products: " . $e->getMessage());
        return [];
    }
}

// Khởi tạo cơ sở dữ liệu (chỉ chạy khi cần)
if (isset($_GET['init_db']) && $_GET['init_db'] === 'true') {
    if (initialize_database()) {
        echo "Cơ sở dữ liệu và các bảng đã được tạo thành công!";
    } else {
        echo "Không thể khởi tạo cơ sở dữ liệu.";
    }
}
