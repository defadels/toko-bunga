<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.weight, p.stock, cat.name as category_name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = ? AND p.is_active = 1
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Redirect if cart is empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Calculate totals
$subtotal = 0;
$total_weight = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_weight += $item['weight'] * $item['quantity'];
}

// Calculate shipping cost (simple calculation based on weight)
$shipping_cost = 0;
if ($total_weight > 0) {
    $shipping_cost = max(15000, $total_weight * 5000); // Minimum Rp 15.000, Rp 5.000 per kg
}

$total = $subtotal + $shipping_cost;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-page {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 2rem 0;
        }

        .page-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 2.5rem;
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .checkout-progress {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #2e7d32;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .checkout-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .form-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .section-title {
            font-size: 1.3rem;
            color: #2e7d32;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .payment-methods {
            display: grid;
            gap: 1rem;
        }

        .payment-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .payment-option:hover,
        .payment-option.selected {
            border-color: #2e7d32;
            background: #f8fdf8;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .payment-icon {
            font-size: 1.2rem;
        }

        .payment-description {
            color: #666;
            font-size: 0.9rem;
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            font-size: 1.3rem;
            color: #2e7d32;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .item-category {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .item-price {
            color: #e91e63;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .item-quantity {
            color: #666;
            font-size: 0.85rem;
        }

        .summary-rows {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }

        .summary-row.total {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2e7d32;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
        }

        .checkout-btn {
            width: 100%;
            background: #2e7d32;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .checkout-btn:hover {
            background: #1b5e20;
            transform: translateY(-1px);
        }

        .checkout-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #2e7d32;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #1b5e20;
        }

        .required {
            color: #dc3545;
        }

        .shipping-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .shipping-info h4 {
            color: #2e7d32;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .shipping-info p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
                order: -1;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .page-title {
                font-size: 2rem;
            }

            .checkout-progress {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-page">
        <div class="page-header">
            <div class="container">
                <h1 class="page-title">Checkout</h1>
                <p class="page-subtitle">Selesaikan pesanan Anda</p>
            </div>
        </div>

        <div class="container">
            <div class="checkout-progress">
                <div class="progress-step">
                    <span class="step-number">3</span>
                    <span>Checkout</span>
                </div>
            </div>

            <a href="cart.php" class="back-link">
                ⬅️ Kembali ke Keranjang
            </a>

            <div class="checkout-content">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <form id="checkoutForm" method="POST" action="api/process-checkout.php">
                        <!-- Shipping Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                🚚 Informasi Pengiriman
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="recipient_name">
                                        Nama Penerima <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           id="recipient_name" 
                                           name="recipient_name" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="recipient_phone">
                                        Nomor Telepon <span class="required">*</span>
                                    </label>
                                    <input type="tel" 
                                           id="recipient_phone" 
                                           name="recipient_phone" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="shipping_address">
                                    Alamat Lengkap <span class="required">*</span>
                                </label>
                                <textarea id="shipping_address" 
                                          name="shipping_address" 
                                          class="form-input form-textarea" 
                                          placeholder="Masukkan alamat lengkap untuk pengiriman..."
                                          required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="city">
                                        Kota <span class="required">*</span>
                                    </label>
                                    <select id="city" name="city" class="form-select" required onchange="calculateShipping()">
                                        <option value="">Pilih Kota</option>
                                        <option value="jakarta" data-cost="15000">Jakarta - Rp 15.000</option>
                                        <option value="bandung" data-cost="20000">Bandung - Rp 20.000</option>
                                        <option value="surabaya" data-cost="25000">Surabaya - Rp 25.000</option>
                                        <option value="yogyakarta" data-cost="22000">Yogyakarta - Rp 22.000</option>
                                        <option value="semarang" data-cost="23000">Semarang - Rp 23.000</option>
                                        <option value="medan" data-cost="30000">Medan - Rp 30.000</option>
                                        <option value="makassar" data-cost="35000">Makassar - Rp 35.000</option>
                                        <option value="palembang" data-cost="28000">Palembang - Rp 28.000</option>
                                        <option value="other" data-cost="40000">Kota Lainnya - Rp 40.000</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="postal_code">
                                        Kode Pos
                                    </label>
                                    <input type="text" 
                                           id="postal_code" 
                                           name="postal_code" 
                                           class="form-input" 
                                           placeholder="12345">
                                </div>
                            </div>

                            <div class="shipping-info">
                                <h4>📋 Informasi Pengiriman</h4>
                                <p><strong>Estimasi:</strong> 1-3 hari kerja</p>
                                <p><strong>Kurir:</strong> JNE, TIKI, atau kurir toko</p>
                                <p><strong>Total Berat:</strong> <?php echo number_format($total_weight, 1); ?> kg</p>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3 class="section-title">
                                💳 Metode Pembayaran
                            </h3>
                            
                            <div class="payment-methods">
                                <div class="payment-option" onclick="selectPayment('transfer')">
                                    <input type="radio" name="payment_method" value="transfer" id="transfer" required>
                                    <div class="payment-header">
                                        <span class="payment-icon">🏦</span>
                                        <span>Transfer Bank</span>
                                    </div>
                                    <div class="payment-description">
                                        Transfer ke rekening bank kami. Konfirmasi pembayaran dalam 24 jam.
                                    </div>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment('cod')">
                                    <input type="radio" name="payment_method" value="cod" id="cod" required>
                                    <div class="payment-header">
                                        <span class="payment-icon">💵</span>
                                        <span>Bayar di Tempat (COD)</span>
                                    </div>
                                    <div class="payment-description">
                                        Bayar langsung saat produk diterima. Tersedia untuk area tertentu.
                                    </div>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment('ewallet')">
                                    <input type="radio" name="payment_method" value="ewallet" id="ewallet" required>
                                    <div class="payment-header">
                                        <span class="payment-icon">📱</span>
                                        <span>E-Wallet</span>
                                    </div>
                                    <div class="payment-description">
                                        OVO, GoPay, DANA, LinkAja, atau e-wallet lainnya.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="form-section">
                            <h3 class="section-title">
                                📝 Catatan Pesanan
                            </h3>
                            
                            <div class="form-group">
                                <label class="form-label" for="order_notes">
                                    Catatan Tambahan (Opsional)
                                </label>
                                <textarea id="order_notes" 
                                          name="order_notes" 
                                          class="form-input form-textarea" 
                                          placeholder="Contoh: Kirim pagi hari, jangan bel, dll..."></textarea>
                            </div>
                        </div>

                        <!-- Hidden inputs for totals -->
                        <input type="hidden" id="subtotal" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" id="shipping_cost" name="shipping_cost" value="<?php echo $shipping_cost; ?>">
                        <input type="hidden" id="total_amount" name="total_amount" value="<?php echo $total; ?>">
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h3 class="summary-title">📋 Ringkasan Pesanan</h3>
                    
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image"
                                 onerror="this.src='assets/images/placeholder.jpg'">
                            
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                                <div class="item-price"><?php echo formatRupiah($item['price']); ?></div>
                                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Summary Calculation -->
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>Subtotal (<?php echo count($cart_items); ?> item):</span>
                            <span id="subtotal-display"><?php echo formatRupiah($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Biaya Pengiriman:</span>
                            <span id="shipping-display"><?php echo formatRupiah($shipping_cost); ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total Pembayaran:</span>
                            <span id="total-display"><?php echo formatRupiah($total); ?></span>
                        </div>
                    </div>

                    <button type="submit" form="checkoutForm" class="checkout-btn" id="checkoutBtn">
                        🛒 Buat Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Select payment method
        function selectPayment(method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById(method).checked = true;
        }

        // Calculate shipping cost based on city selection
        function calculateShipping() {
            const citySelect = document.getElementById('city');
            const selectedOption = citySelect.options[citySelect.selectedIndex];
            const shippingCost = parseInt(selectedOption.dataset.cost) || 0;
            
            // Get subtotal
            const subtotal = parseInt(document.getElementById('subtotal').value);
            
            // Calculate new total
            const total = subtotal + shippingCost;
            
            // Update displays
            document.getElementById('shipping-display').textContent = formatRupiah(shippingCost);
            document.getElementById('total-display').textContent = formatRupiah(total);
            
            // Update hidden inputs
            document.getElementById('shipping_cost').value = shippingCost;
            document.getElementById('total_amount').value = total;
        }

        // Format rupiah helper
        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const checkoutBtn = document.getElementById('checkoutBtn');
            const originalText = checkoutBtn.innerHTML;
            
            // Disable button and show loading
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = '⏳ Memproses...';
            
            // Collect form data
            const formData = new FormData(this);
            
            // Send to server
            fetch('api/process-checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Pesanan berhasil dibuat!', 'success');
                    setTimeout(() => {
                        window.location.href = `checkout-success.php?order_number=${data.order_number}`;
                    }, 1500);
                } else {
                    showAlert(data.message || 'Gagal membuat pesanan', 'error');
                    checkoutBtn.disabled = false;
                    checkoutBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan sistem', 'error');
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = originalText;
            });
        });

        // Auto-select first payment method
        document.addEventListener('DOMContentLoaded', function() {
            selectPayment('transfer');
        });
    </script>
</body>
</html>
