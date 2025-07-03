<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause based on filters
$where_conditions = ['o.user_id = ?'];
$params = [$user_id];

if (!empty($status_filter)) {
    $where_conditions[] = 'o.status = ?';
    $params[] = $status_filter;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total orders count
$count_sql = "SELECT COUNT(*) as total FROM orders o $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_orders = $count_stmt->fetch()['total'];
$total_pages = ceil($total_orders / $limit);

// Get orders with pagination
$sql = "SELECT o.*, 
               COUNT(oi.id) as total_items,
               SUM(oi.quantity) as total_quantity
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        $where_clause 
        GROUP BY o.id 
        ORDER BY o.order_date DESC 
        LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Status mapping for display
$status_labels = [
    'pending' => ['label' => 'Menunggu Konfirmasi', 'class' => 'status-pending'],
    'confirmed' => ['label' => 'Dikonfirmasi', 'class' => 'status-confirmed'],
    'processing' => ['label' => 'Sedang Diproses', 'class' => 'status-processing'],
    'shipped' => ['label' => 'Dikirim', 'class' => 'status-shipped'],
    'delivered' => ['label' => 'Selesai', 'class' => 'status-delivered'],
    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'status-cancelled']
];

$payment_labels = [
    'transfer' => 'Transfer Bank',
    'cod' => 'Bayar di Tempat',
    'ewallet' => 'E-Wallet'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .orders-page {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 2rem 0;
        }

        .orders-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .orders-title {
            font-size: 2.5rem;
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }

        .orders-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .orders-filters {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .filter-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-label {
            font-weight: 600;
            color: #333;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-select:focus {
            border-color: #2e7d32;
            outline: none;
        }

        .orders-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #2e7d32;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2e7d32;
        }

        .order-date {
            color: #666;
            font-size: 0.9rem;
        }

        .order-status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        .order-total {
            font-size: 1.3rem;
            font-weight: bold;
            color: #e91e63;
        }

        .order-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-detail {
            background: #2e7d32;
            color: white;
        }

        .btn-detail:hover {
            background: #1b5e20;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background: #c82333;
        }

        .btn-reorder {
            background: #17a2b8;
            color: white;
        }

        .btn-reorder:hover {
            background: #138496;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: #666;
            margin-bottom: 2rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-btn {
            padding: 0.6rem 1rem;
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-btn:hover,
        .page-btn.active {
            background: #2e7d32;
            color: white;
            border-color: #2e7d32;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .orders-title {
                font-size: 2rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .filter-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-page">
        <!-- Page Header -->
        <div class="orders-header">
            <div class="container">
                <h1 class="orders-title">Pesanan Saya</h1>
                <p class="orders-subtitle">Kelola dan pantau status pesanan Anda</p>
            </div>
        </div>

        <div class="container">
            <!-- Filters -->
            <div class="orders-filters">
                <form method="GET" action="orders.php">
                    <div class="filter-group">
                        <span class="filter-label">Filter Status:</span>
                        <select name="status" class="filter-select" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <?php foreach($status_labels as $status => $info): ?>
                                <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                    <?php echo $info['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php if (!empty($status_filter)): ?>
                            <a href="orders.php" class="action-btn btn-detail">Reset Filter</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Orders List -->
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h3 class="empty-title">Belum Ada Pesanan</h3>
                    <p class="empty-text">
                        <?php if (!empty($status_filter)): ?>
                            Tidak ada pesanan dengan status "<?php echo $status_labels[$status_filter]['label']; ?>"
                        <?php else: ?>
                            Anda belum memiliki pesanan. Mulai berbelanja sekarang!
                        <?php endif; ?>
                    </p>
                    <a href="products.php" class="action-btn btn-detail">Mulai Berbelanja</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                                    <div class="order-date"><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></div>
                                </div>
                                <div class="order-status <?php echo $status_labels[$order['status']]['class']; ?>">
                                    <?php echo $status_labels[$order['status']]['label']; ?>
                                </div>
                            </div>

                            <div class="order-details">
                                <div class="detail-item">
                                    <span class="detail-label">Total Item</span>
                                    <span class="detail-value"><?php echo $order['total_quantity']; ?> produk</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Metode Pembayaran</span>
                                    <span class="detail-value"><?php echo $payment_labels[$order['payment_method']]; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Biaya Pengiriman</span>
                                    <span class="detail-value"><?php echo formatRupiah($order['shipping_cost']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Total Pembayaran</span>
                                    <span class="detail-value order-total"><?php echo formatRupiah($order['total_amount']); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($order['shipping_address'])): ?>
                                <div class="detail-item" style="margin-top: 1rem;">
                                    <span class="detail-label">Alamat Pengiriman</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($order['shipping_address']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($order['notes'])): ?>
                                <div class="detail-item" style="margin-top: 1rem;">
                                    <span class="detail-label">Catatan</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($order['notes']); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="order-actions">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="action-btn btn-detail">
                                    👁️ Lihat Detail
                                </a>
                                
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="action-btn btn-cancel">
                                        ❌ Batalkan
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (in_array($order['status'], ['delivered', 'cancelled'])): ?>
                                    <button onclick="reorderItems(<?php echo $order['id']; ?>)" class="action-btn btn-reorder">
                                        🔄 Pesan Lagi
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>" class="page-btn">
                                ⬅️ Sebelumnya
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>" 
                               class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>" class="page-btn">
                                Selanjutnya ➡️
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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
    </script>
</body>
</html> 