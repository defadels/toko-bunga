<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'count' => 0];

if (isLoggedIn()) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        
        $response['success'] = true;
        $response['count'] = (int)($result['total'] ?? 0);
        
    } catch (Exception $e) {
        $response['count'] = 0;
    }
}

echo json_encode($response);
?> 