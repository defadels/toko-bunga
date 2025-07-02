<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock, cat.name as category_name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = ? AND p.is_active = 1
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping_cost = 15000; // Fixed shipping cost
$total = $subtotal + $shipping_cost;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-container {
            padding: 2rem 0;
            min-height: 80vh;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            gap: 1rem;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }
        
        .item-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            font-weight: 600;
            color: #e91e63;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        
        .quantity-btn {
            background: #e91e63;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: #c2185b;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 4px;
            padding: 6px;
        }
        
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .remove-btn:hover {
            background: #c82333;
        }
        
        .cart-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            height: fit-content;
        }
        
        .summary-title {
            font-size: 1.5rem;
            color: #2e7d32;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e91e63;
            padding-bottom: 0.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        
        .summary-row.total {
            border-top: 2px solid #eee;
            margin-top: 1rem;
            padding-top: 1rem;
            font-weight: bold;
            font-size: 1.2rem;
            color: #2e7d32;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .empty-cart h3 {
            color: #666;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container cart-container">
        <h1>Keranjang Belanja</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h3>🛒 Keranjang Anda kosong</h3>
                <p>Belum ada produk yang ditambahkan ke keranjang.</p>
                <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                        <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                        
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                            <div class="item-price"><?php echo formatRupiah($item['price']); ?></div>
                        </div>
                        
                        <div class="item-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="<?php echo $item['stock']; ?>" 
                                   onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                        </div>
                        
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">
                                <?php echo formatRupiah($item['price'] * $item['quantity']); ?>
                            </div>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                Hapus
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Ringkasan Pesanan</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?php echo count($cart_items); ?> item)</span>
                        <span id="subtotal"><?php echo formatRupiah($subtotal); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span><?php echo formatRupiah($shipping_cost); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="total"><?php echo formatRupiah($total); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; margin-top: 1.5rem;">
                        Lanjutkan ke Checkout
                    </a>
                    
                    <a href="products.php" class="btn btn-outline" style="width: 100%; text-align: center; margin-top: 1rem;">
                        Lanjutkan Belanja
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        
        function updateQuantity(cartId, newQuantity) {
            if (newQuantity < 1) {
                if (confirm('Hapus item dari keranjang?')) {
                    removeFromCart(cartId);
                }
                return;
            }
            
            const formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('quantity', newQuantity);
            
            fetch('api/cart.php', {
                method: 'PUT',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal mengupdate quantity');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
        
        function removeFromCart(cartId) {
            if (!confirm('Yakin ingin menghapus item ini dari keranjang?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('cart_id', cartId);
            
            fetch('api/cart.php', {
                method: 'DELETE',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 