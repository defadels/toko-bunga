<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$order_id = (int)($input['order_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (empty($action) || $order_id <= 0) {
    $response['message'] = 'Invalid parameters';
    echo json_encode($response);
    exit;
}

$pdo = getConnection();

try {
    // Verify order belongs to current user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $response['message'] = 'Pesanan tidak ditemukan';
        echo json_encode($response);
        exit;
    }
    
    switch ($action) {
        case 'cancel':
            // Only allow canceling pending orders
            if ($order['status'] !== 'pending') {
                $response['message'] = 'Pesanan ini tidak dapat dibatalkan';
                break;
            }
            
            // Update order status to cancelled
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Return stock to products
            $stmt = $pdo->prepare("
                UPDATE products p 
                INNER JOIN order_items oi ON p.id = oi.product_id 
                SET p.stock = p.stock + oi.quantity 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            $response['success'] = true;
            $response['message'] = 'Pesanan berhasil dibatalkan';
            break;
            
        case 'reorder':
            // Only allow reordering completed or cancelled orders
            if (!in_array($order['status'], ['delivered', 'cancelled'])) {
                $response['message'] = 'Pesanan ini belum dapat dipesan ulang';
                break;
            }
            
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.product_id, oi.quantity, p.name, p.stock, p.is_active
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
            
            if (empty($order_items)) {
                $response['message'] = 'Tidak ada item dalam pesanan ini';
                break;
            }
            
            $added_items = 0;
            $unavailable_items = [];
            
            foreach ($order_items as $item) {
                // Check if product is still active and available
                if (!$item['is_active']) {
                    $unavailable_items[] = $item['name'] . ' (tidak tersedia)';
                    continue;
                }
                
                if ($item['stock'] < $item['quantity']) {
                    $unavailable_items[] = $item['name'] . ' (stok tidak mencukupi)';
                    continue;
                }
                
                // Check if item already in cart
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $item['product_id']]);
                $existing_cart = $stmt->fetch();
                
                if ($existing_cart) {
                    // Update quantity in cart
                    $new_quantity = $existing_cart['quantity'] + $item['quantity'];
                    if ($new_quantity <= $item['stock']) {
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$new_quantity, $existing_cart['id']]);
                        $added_items++;
                    } else {
                        $unavailable_items[] = $item['name'] . ' (quantity melebihi stok)';
                    }
                } else {
                    // Add new item to cart
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$user_id, $item['product_id'], $item['quantity']]);
                    $added_items++;
                }
            }
            
            if ($added_items > 0) {
                $response['success'] = true;
                $message = "$added_items item berhasil ditambahkan ke keranjang";
                if (!empty($unavailable_items)) {
                    $message .= ". Item yang tidak dapat ditambahkan: " . implode(', ', $unavailable_items);
                }
                $response['message'] = $message;
            } else {
                $response['message'] = 'Tidak ada item yang dapat ditambahkan ke keranjang';
                if (!empty($unavailable_items)) {
                    $response['message'] .= ": " . implode(', ', $unavailable_items);
                }
            }
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
    
} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan sistem';
    error_log("Order action error: " . $e->getMessage());
}

echo json_encode($response);
?> 