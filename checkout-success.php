<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    header("Location: orders.php");
    exit();
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$order_number, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image, p.category_id, c.name as category_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Berhasil - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
            padding: 2rem 0;
        }

        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .success-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: #4caf50;
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            animation: successPulse 2s ease-in-out infinite;
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .success-title {
            font-size: 2.5rem;
            color: #2e7d32;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .success-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .order-info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .order-number-section {
            text-align: center;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .order-number-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .order-number {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .order-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-section h3 {
            color: #2e7d32;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dotted #ddd;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
        }

        .items-section {
            margin-top: 2rem;
        }

        .items-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .item-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #fff;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .item-category {
            color: #666;
            font-size: 0.9rem;
        }

        .item-quantity {
            color: #666;
            font-size: 0.9rem;
            margin-right: 1rem;
        }

        .item-price {
            color: #e91e63;
            font-weight: 600;
            font-size: 1rem;
        }

        .payment-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .payment-icon {
            font-size: 1.5rem;
        }

        .payment-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .payment-instructions {
            background: white;
            border-left: 4px solid #2e7d32;
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            margin-top: 1rem;
        }

        .bank-details {
            margin-top: 1rem;
            padding: 1rem;
            background: #f1f8e9;
            border-radius: 8px;
        }

        .bank-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #2e7d32;
            color: white;
        }

        .btn-primary:hover {
            background: #1b5e20;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #2e7d32;
            border: 2px solid #2e7d32;
        }

        .btn-secondary:hover {
            background: #2e7d32;
            color: white;
            transform: translateY(-2px);
        }

        .total-summary {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .total-row.final {
            font-size: 1.3rem;
            font-weight: 700;
            padding-top: 1rem;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 1rem;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .success-title {
                font-size: 2rem;
            }

            .order-number {
                font-size: 1.5rem;
            }

            .item-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .detail-item {
                flex-direction: column;
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="success-page">
        <div class="success-container">
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon">✅</div>
                <h1 class="success-title">Pesanan Berhasil Dibuat!</h1>
                <p class="success-subtitle">
                    Terima kasih <?php echo htmlspecialchars($order['customer_name']); ?>! 
                    Pesanan Anda telah berhasil diproses.
                </p>
            </div>

            <!-- Order Info Card -->
            <div class="order-info-card">
                <!-- Order Number -->
                <div class="order-number-section">
                    <div class="order-number-label">Nomor Pesanan</div>
                    <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                    <div class="order-status status-<?php echo $order['status']; ?>">
                        Status: <?php echo ucfirst($order['status']); ?>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="order-details">
                    <div class="detail-section">
                        <h3>📋 Detail Pesanan</h3>
                        <div class="detail-item">
                            <span class="detail-label">Tanggal:</span>
                            <span class="detail-value"><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status Pesanan:</span>
                            <span class="detail-value"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status Pembayaran:</span>
                            <span class="detail-value"><?php echo ucfirst($order['payment_status']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Metode Pembayaran:</span>
                            <span class="detail-value">
                                <?php
                                $payment_names = [
                                    'transfer' => 'Transfer Bank',
                                    'cod' => 'Bayar di Tempat (COD)',
                                    'ewallet' => 'E-Wallet'
                                ];
                                echo $payment_names[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>🚚 Informasi Pengiriman</h3>
                        <div class="detail-item">
                            <span class="detail-label">Penerima:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['recipient_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Telepon:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['recipient_phone']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Kota:</span>
                            <span class="detail-value"><?php echo ucfirst($order['city']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Kode Pos:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['postal_code']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <?php if ($order['payment_method'] === 'transfer'): ?>
                <div class="payment-info">
                    <div class="payment-method">
                        <span class="payment-icon">🏦</span>
                        <span class="payment-text">Instruksi Pembayaran - Transfer Bank</span>
                    </div>
                    
                    <div class="payment-instructions">
                        <p><strong>Silakan transfer ke rekening berikut:</strong></p>
                        <div class="bank-details">
                            <div class="bank-item">
                                <span><strong>Bank BCA:</strong></span>
                                <span>1234567890</span>
                            </div>
                            <div class="bank-item">
                                <span><strong>Bank Mandiri:</strong></span>
                                <span>9876543210</span>
                            </div>
                            <div class="bank-item">
                                <span><strong>Atas Nama:</strong></span>
                                <span>Toko Bunga Florisen</span>
                            </div>
                            <div class="bank-item">
                                <span><strong>Jumlah:</strong></span>
                                <span style="color: #e91e63; font-weight: 700;"><?php echo formatRupiah($order['total_amount']); ?></span>
                            </div>
                        </div>
                        <p style="margin-top: 1rem; color: #666;">
                            <strong>Catatan:</strong> Harap konfirmasi pembayaran dalam 24 jam. 
                            Setelah transfer, kirim bukti ke WhatsApp: <strong>0812-3456-7890</strong>
                        </p>
                    </div>
                </div>
                <?php elseif ($order['payment_method'] === 'cod'): ?>
                <div class="payment-info">
                    <div class="payment-method">
                        <span class="payment-icon">💵</span>
                        <span class="payment-text">Bayar di Tempat (COD)</span>
                    </div>
                    <div class="payment-instructions">
                        <p>Pesanan Anda akan dikirim dan pembayaran dilakukan saat barang diterima.</p>
                        <p><strong>Total yang harus dibayar: <?php echo formatRupiah($order['total_amount']); ?></strong></p>
                        <p style="color: #666;">Siapkan uang pas untuk memudahkan transaksi.</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="payment-info">
                    <div class="payment-method">
                        <span class="payment-icon">📱</span>
                        <span class="payment-text">E-Wallet</span>
                    </div>
                    <div class="payment-instructions">
                        <p>Instruksi pembayaran akan dikirim ke email Anda.</p>
                        <p><strong>Total: <?php echo formatRupiah($order['total_amount']); ?></strong></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Order Items -->
                <div class="items-section">
                    <h3>🛍️ Item Pesanan (<?php echo count($order_items); ?> item)</h3>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                        <div class="item-row">
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image"
                                 onerror="this.src='assets/images/placeholder.jpg'">
                            
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                            </div>
                            
                            <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            <div class="item-price"><?php echo formatRupiah($item['total']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Total Summary -->
                    <div class="total-summary">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatRupiah($order['subtotal']); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Biaya Pengiriman:</span>
                            <span><?php echo formatRupiah($order['shipping_cost']); ?></span>
                        </div>
                        <div class="total-row final">
                            <span>Total Pembayaran:</span>
                            <span><?php echo formatRupiah($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="order-detail.php?order_number=<?php echo urlencode($order['order_number']); ?>" class="btn btn-primary">
                        📄 Lihat Detail Lengkap
                    </a>
                    <a href="orders.php" class="btn btn-secondary">
                        📋 Daftar Pesanan Saya
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Auto refresh halaman setelah 5 menit untuk update status
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 menit

        // Show success animation
        document.addEventListener('DOMContentLoaded', function() {
            const successIcon = document.querySelector('.success-icon');
            successIcon.style.transform = 'scale(0)';
            setTimeout(() => {
                successIcon.style.transition = 'transform 0.5s ease-out';
                successIcon.style.transform = 'scale(1)';
            }, 500);
        });
    </script>
</body>
</html> 