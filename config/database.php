<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'toko_bunga');
define('DB_USER', 'root');
define('DB_PASS', '');

// Membuat koneksi database
function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Session configuration
session_start();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPetugas() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'petugas');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function generateOrderNumber() {
    return 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Database helper functions
function getAllCategories() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getAllCategories: " . $e->getMessage());
        return [];
    }
}

function getCategoryById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductCountByCategory($category_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

function getFeaturedProducts($limit = 8) {
    try {
        $pdo = getConnection();
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.stock > 0 AND p.is_active = 1
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getFeaturedProducts: " . $e->getMessage());
        return [];
    }
}

function getProductsWithFilters($where_conditions, $params, $order_by, $limit, $offset) {
    try {
        $pdo = getConnection();
        
        // Base WHERE clause with active products
        $base_conditions = ['p.is_active = 1'];
        if (!empty($where_conditions)) {
            $base_conditions = array_merge($base_conditions, $where_conditions);
        }
        $where_clause = 'WHERE ' . implode(' AND ', $base_conditions);
        
        // Sanitize order_by to prevent SQL injection
        $allowed_orders = [
            'p.created_at DESC', 'p.created_at ASC',
            'p.price ASC', 'p.price DESC', 
            'p.name ASC', 'p.name DESC'
        ];
        if (!in_array($order_by, $allowed_orders)) {
            $order_by = 'p.created_at DESC'; // default
        }
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where_clause 
                ORDER BY $order_by 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getProductsWithFilters: " . $e->getMessage());
        error_log("SQL: " . ($sql ?? 'Not set'));
        error_log("Params: " . print_r($params, true));
        return [];
    }
}

function getTotalProductsWithFilters($where_conditions, $params) {
    try {
        $pdo = getConnection();
        
        // Base WHERE clause with active products
        $base_conditions = ['p.is_active = 1'];
        if (!empty($where_conditions)) {
            $base_conditions = array_merge($base_conditions, $where_conditions);
        }
        $where_clause = 'WHERE ' . implode(' AND ', $base_conditions);
        
        $sql = "SELECT COUNT(*) as total FROM products p $where_clause";
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    } catch (PDOException $e) {
        error_log("Error in getTotalProductsWithFilters: " . $e->getMessage());
        return 0;
    }
}
?> 