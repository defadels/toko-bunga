<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isLoggedIn()) {
    $response['message'] = 'Silakan login terlebih dahulu.';
    echo json_encode($response);
    exit;
}

$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to cart
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    $user_id = $_SESSION['user_id'];
    
    if ($product_id <= 0 || $quantity <= 0) {
        $response['message'] = 'Data tidak valid.';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if product exists and is active
        $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $response['message'] = 'Produk tidak ditemukan.';
            echo json_encode($response);
            exit;
        }
        
        if ($product['stock'] < $quantity) {
            $response['message'] = 'Stok tidak mencukupi.';
            echo json_encode($response);
            exit;
        }
        
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                $response['message'] = 'Total quantity melebihi stok yang tersedia.';
                echo json_encode($response);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing['id']]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        $response['success'] = true;
        $response['message'] = 'Produk berhasil ditambahkan ke keranjang.';
        
    } catch (Exception $e) {
        $response['message'] = 'Terjadi kesalahan sistem.';
    }
    
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Update cart item quantity
    parse_str(file_get_contents("php://input"), $data);
    $cart_id = (int)$data['cart_id'];
    $quantity = (int)$data['quantity'];
    $user_id = $_SESSION['user_id'];
    
    if ($cart_id <= 0 || $quantity < 0) {
        $response['message'] = 'Data tidak valid.';
        echo json_encode($response);
        exit;
    }
    
    try {
        if ($quantity === 0) {
            // Remove item from cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
        } else {
            // Update quantity
            $stmt = $pdo->prepare("
                UPDATE cart c 
                JOIN products p ON c.product_id = p.id 
                SET c.quantity = ? 
                WHERE c.id = ? AND c.user_id = ? AND p.stock >= ?
            ");
            $stmt->execute([$quantity, $cart_id, $user_id, $quantity]);
            
            if ($stmt->rowCount() === 0) {
                $response['message'] = 'Quantity melebihi stok yang tersedia.';
                echo json_encode($response);
                exit;
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Keranjang berhasil diupdate.';
        
    } catch (Exception $e) {
        $response['message'] = 'Terjadi kesalahan sistem.';
    }
    
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Remove item from cart
    parse_str(file_get_contents("php://input"), $data);
    $cart_id = (int)$data['cart_id'];
    $user_id = $_SESSION['user_id'];
    
    if ($cart_id <= 0) {
        $response['message'] = 'Data tidak valid.';
        echo json_encode($response);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        
        $response['success'] = true;
        $response['message'] = 'Item berhasil dihapus dari keranjang.';
        
    } catch (Exception $e) {
        $response['message'] = 'Terjadi kesalahan sistem.';
    }
}

echo json_encode($response);
?> 