<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'products' => []];

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $query = trim($_GET['q']);
    $searchTerm = '%' . $query . '%';
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?) 
            AND p.is_active = 1 
            AND c.is_active = 1
            ORDER BY p.name ASC 
            LIMIT 10
        ");
        
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $products = $stmt->fetchAll();
        
        $response['success'] = true;
        $response['products'] = $products;
        
    } catch (Exception $e) {
        $response['error'] = 'Terjadi kesalahan dalam pencarian.';
    }
}

echo json_encode($response);
?> 