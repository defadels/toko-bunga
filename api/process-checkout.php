<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get and validate form data
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $recipient_phone = trim($_POST['recipient_phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $order_notes = trim($_POST['order_notes'] ?? '');
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    
    // Validation
    if (empty($recipient_name) || empty($recipient_phone) || empty($shipping_address) || 
        empty($city) || empty($payment_method)) {
        throw new Exception('Semua field yang wajib harus diisi');
    }
    
    if ($total_amount <= 0) {
        throw new Exception('Total pembayaran tidak valid');
    }
    
    // Validate payment method
    $valid_payment_methods = ['transfer', 'cod', 'ewallet'];
    if (!in_array($payment_method, $valid_payment_methods)) {
        throw new Exception('Metode pembayaran tidak valid');
    }
    
    // Get cart items for this user
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.stock, p.weight
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND p.is_active = 1
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        throw new Exception('Keranjang belanja kosong');
    }
    
    // Check stock availability and calculate actual totals
    $calculated_subtotal = 0;
    $calculated_weight = 0;
    foreach ($cart_items as $item) {
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Stok tidak mencukupi untuk produk: {$item['name']}");
        }
        $calculated_subtotal += $item['price'] * $item['quantity'];
        $calculated_weight += $item['weight'] * $item['quantity'];
    }
    
    // Verify calculations (with small tolerance for rounding)
    if (abs($calculated_subtotal - $subtotal) > 1) {
        throw new Exception('Subtotal tidak sesuai dengan perhitungan server');
    }
    
    // Calculate shipping cost
    $calculated_shipping = max(15000, $calculated_weight * 5000);
    
    // Override with city-based shipping cost if provided
    $city_shipping_costs = [
        'jakarta' => 15000,
        'bandung' => 20000,
        'surabaya' => 25000,
        'yogyakarta' => 22000,
        'semarang' => 23000,
        'medan' => 30000,
        'makassar' => 35000,
        'palembang' => 28000,
        'other' => 40000
    ];
    
    if (isset($city_shipping_costs[$city])) {
        $calculated_shipping = $city_shipping_costs[$city];
    }
    
    $calculated_total = $calculated_subtotal + $calculated_shipping;
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Check if order number already exists
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    if ($stmt->fetch()) {
        // Generate a new one with timestamp
        $order_number = 'ORD-' . date('YmdHis') . '-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
    }
    
    // Determine order status based on payment method
    $status = ($payment_method === 'cod') ? 'confirmed' : 'pending';
    $payment_status = ($payment_method === 'cod') ? 'pending' : 'pending';
    
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, status, payment_status, payment_method,
            subtotal, shipping_cost, total_amount,
            recipient_name, recipient_phone, shipping_address, 
            city, postal_code, notes, order_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $order_number, $user_id, $status, $payment_status, $payment_method,
        $calculated_subtotal, $calculated_shipping, $calculated_total,
        $recipient_name, $recipient_phone, $shipping_address,
        $city, $postal_code, $order_notes
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert order items and update stock
    foreach ($cart_items as $item) {
        // Insert order item
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, total)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity']
        ]);
        
        // Update product stock
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ?
        ");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Clear user's cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dibuat',
        'order_number' => $order_number,
        'order_id' => $order_id,
        'total_amount' => $calculated_total,
        'payment_method' => $payment_method,
        'status' => $status
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 