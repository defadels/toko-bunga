<?php
require_once 'config/database.php';

$pdo = getConnection();

// Get product ID
$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    redirect('products.php');
}

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

// Get related products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-detail-container {
            padding: 2rem 0;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            color: #666;
        }
        
        .breadcrumb a {
            color: #e91e63;
            text-decoration: none;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .product-image-container {
            position: relative;
        }
        
        .product-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .product-info {
            padding: 2rem 0;
        }
        
        .product-category {
            color: #e91e63;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .product-title {
            font-size: 2.5rem;
            color: #2e7d32;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .product-price {
            font-size: 2rem;
            font-weight: bold;
            color: #e91e63;
            margin-bottom: 1.5rem;
        }
        
        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .product-specs {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .spec-label {
            font-weight: 500;
            color: #333;
        }
        
        .spec-value {
            color: #666;
        }
        
        .quantity-section {
            margin-bottom: 2rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .quantity-label {
            font-weight: 500;
            color: #333;
        }
        
        .quantity-input-group {
            display: flex;
            align-items: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: #e91e63;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
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
            border: none;
            width: 80px;
            height: 40px;
            text-align: center;
            font-size: 16px;
        }
        
        .total-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2e7d32;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .btn-large {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .stock-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .stock-available {
            color: #28a745;
        }
        
        .stock-low {
            color: #ffc107;
        }
        
        .stock-out {
            color: #dc3545;
        }
        
        .related-products {
            margin-top: 4rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-title {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container product-detail-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Home</a> / 
            <a href="products.php">Produk</a> / 
            <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> / 
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <!-- Product Detail -->
        <div class="product-detail">
            <div class="product-image-container">
                <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjUwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjVmNWY1Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkdhbWJhciBQcm9kdWs8L3RleHQ+PC9zdmc+'">
            </div>

            <div class="product-info">
                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-price" id="product-price" data-price="<?php echo $product['price']; ?>">
                    <?php echo formatRupiah($product['price']); ?>
                </div>

                <?php if ($product['description']): ?>
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                <?php endif; ?>

                <!-- Product Specifications -->
                <div class="product-specs">
                    <h3 style="margin-bottom: 1rem; color: #2e7d32;">Spesifikasi Produk</h3>
                    <div class="spec-item">
                        <span class="spec-label">Kategori:</span>
                        <span class="spec-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Berat:</span>
                        <span class="spec-value"><?php echo $product['weight']; ?> kg</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">SKU:</span>
                        <span class="spec-value">FLW-<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>

                <!-- Stock Info -->
                <div class="stock-info">
                    <?php if ($product['stock'] > 10): ?>
                        <span class="stock-available">✅ Stok tersedia (<?php echo $product['stock']; ?> unit)</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="stock-low">⚠️ Stok terbatas (<?php echo $product['stock']; ?> unit tersisa)</span>
                    <?php else: ?>
                        <span class="stock-out">❌ Stok habis</span>
                    <?php endif; ?>
                </div>

                <!-- Quantity Selection -->
                <?php if ($product['stock'] > 0): ?>
                <div class="quantity-section">
                    <div class="quantity-controls">
                        <span class="quantity-label">Jumlah:</span>
                        <div class="quantity-input-group">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>" onchange="updateTotalPrice()">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                    <div class="total-price">
                        Total: <span id="total-price"><?php echo formatRupiah($product['price']); ?></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary btn-large add-to-cart" data-product-id="<?php echo $product['id']; ?>" onclick="addToCartWithQuantity()">
                        🛒 Tambah ke Keranjang
                    </button>
                    <button class="btn btn-secondary btn-large" onclick="buyNow()">
                        🚀 Beli Sekarang
                    </button>
                </div>
                <?php else: ?>
                <div class="action-buttons">
                    <button class="btn btn-outline btn-large" disabled>
                        Stok Habis
                    </button>
                </div>
                <?php endif; ?>

                <!-- Share Buttons -->
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem; color: #2e7d32;">Bagikan Produk</h4>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" class="btn btn-outline" onclick="shareProduct('whatsapp')">📱 WhatsApp</a>
                        <a href="#" class="btn btn-outline" onclick="shareProduct('facebook')">📘 Facebook</a>
                        <a href="#" class="btn btn-outline" onclick="copyProductLink()">🔗 Salin Link</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h2 class="section-title">Produk Terkait</h2>
            <div class="related-grid">
                <?php foreach($related_products as $related): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/products/<?php echo htmlspecialchars($related['image']); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <div class="product-overlay">
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo htmlspecialchars($related['category_name']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                        <div class="product-price"><?php echo formatRupiah($related['price']); ?></div>
                        <div class="product-actions">
                            <button class="btn btn-outline add-to-cart" data-product-id="<?php echo $related['id']; ?>">
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        
        function changeQuantity(change) {
            const quantityInput = document.getElementById('quantity');
            let currentValue = parseInt(quantityInput.value) || 1;
            let newValue = currentValue + change;
            
            const maxStock = parseInt(quantityInput.getAttribute('max'));
            
            if (newValue < 1) newValue = 1;
            if (newValue > maxStock) newValue = maxStock;
            
            quantityInput.value = newValue;
            updateTotalPrice();
        }
        
        function updateTotalPrice() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const price = parseInt(document.getElementById('product-price').getAttribute('data-price'));
            const total = quantity * price;
            
            document.getElementById('total-price').textContent = formatRupiah(total);
        }
        
        function addToCartWithQuantity() {
            if (!userLoggedIn) {
                showAlert('Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
                return;
            }
            
            const productId = <?php echo $product['id']; ?>;
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            
            addToCart(productId, quantity);
        }
        
        function buyNow() {
            if (!userLoggedIn) {
                showAlert('Silakan login terlebih dahulu.', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
                return;
            }
            
            // Add to cart then redirect to checkout
            const productId = <?php echo $product['id']; ?>;
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('api/cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'checkout.php';
                } else {
                    showAlert(data.message || 'Gagal menambahkan produk ke keranjang.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan. Silakan coba lagi.', 'error');
            });
        }
        
        function shareProduct(platform) {
            const url = window.location.href;
            const title = <?php echo json_encode($product['name']); ?>;
            
            if (platform === 'whatsapp') {
                window.open(`https://wa.me/?text=${encodeURIComponent(title + ' - ' + url)}`, '_blank');
            } else if (platform === 'facebook') {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
            }
        }
        
        function copyProductLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showAlert('Link produk berhasil disalin!', 'success');
            });
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 