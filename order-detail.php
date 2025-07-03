<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];
$order_id = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    header("Location: orders.php");
    exit();
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*
    FROM orders o 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image, p.weight, c.name as category_name
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Status mapping
$status_labels = [
    'pending' => ['label' => 'Menunggu Konfirmasi', 'class' => 'status-pending', 'icon' => '⏳'],
    'confirmed' => ['label' => 'Dikonfirmasi', 'class' => 'status-confirmed', 'icon' => '✅'],
    'processing' => ['label' => 'Sedang Diproses', 'class' => 'status-processing', 'icon' => '🔄'],
    'shipped' => ['label' => 'Dikirim', 'class' => 'status-shipped', 'icon' => '🚚'],
    'delivered' => ['label' => 'Selesai', 'class' => 'status-delivered', 'icon' => '📦'],
    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'status-cancelled', 'icon' => '❌']
];

$payment_labels = [
    'transfer' => 'Transfer Bank',
    'cod' => 'Bayar di Tempat',
    'ewallet' => 'E-Wallet'
];

// Calculate totals
$subtotal = array_sum(array_map(function($item) {
    return $item['total'];
}, $order_items));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan <?php echo htmlspecialchars($order['order_number']); ?> - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .order-detail-page {
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

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #2e7d32;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }

        .back-btn:hover {
            color: #1b5e20;
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

        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .detail-title {
            font-size: 1.4rem;
            color: #2e7d32;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
            text-align: right;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-processing {
            background: #d4edda;
            color: #155724;
        }

        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .items-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .items-title {
            font-size: 1.4rem;
            color: #2e7d32;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .item-row {
            display: grid;
            grid-template-columns: 80px 1fr auto auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .item-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        .item-category {
            color: #666;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            color: #e91e63;
            text-align: right;
        }

        .item-quantity {
            text-align: center;
            font-weight: 600;
            color: #333;
        }

        .item-total {
            font-weight: bold;
            color: #2e7d32;
            text-align: right;
            font-size: 1.1rem;
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #2e7d32;
        }

        .summary-title {
            font-size: 1.4rem;
            color: #2e7d32;
            margin-bottom: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-row.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2e7d32;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .action-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #2e7d32;
            color: white;
        }

        .btn-primary:hover {
            background: #1b5e20;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 2rem;
            }

            .item-row {
                grid-template-columns: 60px 1fr;
                gap: 0.5rem;
            }

            .item-price,
            .item-quantity,
            .item-total {
                display: none;
            }

            .item-info::after {
                content: "<?php echo formatRupiah($item['price']); ?> × <?php echo $item['quantity']; ?> = <?php echo formatRupiah($item['total']); ?>";
                color: #666;
                font-size: 0.9rem;
                margin-top: 0.25rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="order-detail-page">
        <div class="page-header">
            <div class="container">
                <a href="orders.php" class="back-btn">
                    ⬅️ Kembali ke Daftar Pesanan
                </a>
                <h1 class="page-title">Detail Pesanan</h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>
        </div>

        <div class="container">
            <!-- Order Details Grid -->
            <div class="order-details-grid">
                <!-- Order Info -->
                <div class="detail-card">
                    <h3 class="detail-title">
                        📋 Informasi Pesanan
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">Nomor Pesanan:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tanggal Pesanan:</span>
                        <span class="detail-value"><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="order-status <?php echo $status_labels[$order['status']]['class']; ?>">
                                <?php echo $status_labels[$order['status']]['icon']; ?>
                                <?php echo $status_labels[$order['status']]['label']; ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Metode Pembayaran:</span>
                        <span class="detail-value"><?php echo $payment_labels[$order['payment_method']]; ?></span>
                    </div>
                    <?php if (!empty($order['notes'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Catatan:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['notes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Shipping Info -->
                <div class="detail-card">
                    <h3 class="detail-title">
                        🚚 Informasi Pengiriman
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">Alamat Pengiriman:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['shipping_address']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Biaya Pengiriman:</span>
                        <span class="detail-value"><?php echo formatRupiah($order['shipping_cost']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Berat:</span>
                        <span class="detail-value">
                            <?php 
                            $total_weight = array_sum(array_map(function($item) {
                                return $item['weight'] * $item['quantity'];
                            }, $order_items));
                            echo number_format($total_weight, 1) . ' kg';
                            ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Estimasi Pengiriman:</span>
                        <span class="detail-value">
                            <?php
                            $status = $order['status'];
                            if ($status === 'pending') {
                                echo '2-3 hari setelah dikonfirmasi';
                            } elseif ($status === 'confirmed') {
                                echo '1-2 hari kerja';
                            } elseif ($status === 'processing') {
                                echo 'Sedang diproses';
                            } elseif ($status === 'shipped') {
                                echo 'Dalam perjalanan';
                            } elseif ($status === 'delivered') {
                                echo 'Sudah diterima';
                            } else {
                                echo '-';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="items-card">
                <h3 class="items-title">
                    🌺 Produk yang Dipesan
                </h3>
                
                <!-- Table Header -->
                <div class="item-row" style="font-weight: 600; color: #666; border-bottom: 2px solid #f0f0f0;">
                    <span>Gambar</span>
                    <span>Produk</span>
                    <span style="text-align: right;">Harga</span>
                    <span style="text-align: center;">Qty</span>
                    <span style="text-align: right;">Subtotal</span>
                </div>

                <?php foreach ($order_items as $item): ?>
                <div class="item-row">
                    <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                         class="item-image"
                         onerror="this.src='assets/images/placeholder.jpg'">
                    
                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                    </div>
                    
                    <div class="item-price"><?php echo formatRupiah($item['price']); ?></div>
                    <div class="item-quantity"><?php echo $item['quantity']; ?></div>
                    <div class="item-total"><?php echo formatRupiah($item['total']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="summary-card">
                <h3 class="summary-title">💰 Ringkasan Pembayaran</h3>
                
                <div class="summary-row">
                    <span>Subtotal (<?php echo count($order_items); ?> item):</span>
                    <span><?php echo formatRupiah($subtotal); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Biaya Pengiriman:</span>
                    <span><?php echo formatRupiah($order['shipping_cost']); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total Pembayaran:</span>
                    <span><?php echo formatRupiah($order['total_amount']); ?></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($order['status'] === 'pending'): ?>
                    <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="action-btn btn-danger">
                        ❌ Batalkan Pesanan
                    </button>
                <?php endif; ?>
                
                <?php if (in_array($order['status'], ['delivered', 'cancelled'])): ?>
                    <button onclick="reorderItems(<?php echo $order['id']; ?>)" class="action-btn btn-info">
                        🔄 Pesan Lagi
                    </button>
                <?php endif; ?>
                
                <button onclick="printOrder()" class="action-btn btn-secondary">
                    🖨️ Print Pesanan
                </button>
                
                <a href="orders.php" class="action-btn btn-primary">
                    📋 Kembali ke Daftar Pesanan
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Cancel order function
        function cancelOrder(orderId) {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                fetch('api/order-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'cancel',
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Pesanan berhasil dibatalkan', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message || 'Gagal membatalkan pesanan', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan', 'error');
                });
            }
        }

        // Reorder function
        function reorderItems(orderId) {
            if (confirm('Tambahkan semua item dari pesanan ini ke keranjang?')) {
                fetch('api/order-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'reorder',
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Item berhasil ditambahkan ke keranjang!', 'success');
                        updateCartCount();
                    } else {
                        showAlert(data.message || 'Gagal menambahkan item ke keranjang', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan', 'error');
                });
            }
        }

        // Print order function
        function printOrder() {
            window.print();
        }
    </script>

    <style>
        @media print {
            .header,
            .footer,
            .action-buttons,
            .back-btn {
                display: none !important;
            }
            
            .order-detail-page {
                background: white !important;
                padding: 0 !important;
            }
            
            .page-header {
                background: white !important;
                box-shadow: none !important;
                padding: 1rem 0 !important;
                margin-bottom: 1rem !important;
            }
            
            .detail-card,
            .items-card,
            .summary-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid;
            }
        }
    </style>
</body>
</html> 