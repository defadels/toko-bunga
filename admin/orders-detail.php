<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();

// Get order ID
$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    redirect('orders.php');
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.email, u.phone, u.address as customer_address
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image, c.name as category_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Status labels
$status_labels = [
    'pending' => 'Menunggu Konfirmasi',
    'confirmed' => 'Dikonfirmasi',
    'processing' => 'Sedang Diproses',
    'shipped' => 'Sedang Dikirim',
    'delivered' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <a href="orders.php" class="btn btn-outline">← Kembali</a>
                <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_number']); ?></h1>
            </div>

            <!-- Order Info -->
            <div class="content-card">
                <h2>Informasi Pesanan</h2>
                <div class="order-header">
                    <div>
                        <div class="info-group">
                            <div class="info-label">Nomor Pesanan</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Tanggal Pesanan</div>
                            <div class="info-value"><?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo $status_labels[$order['status']] ?? $order['status']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Metode Pembayaran</div>
                            <div class="info-value"><?php echo ucfirst($order['payment_method']); ?></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="info-group">
                            <div class="info-label">Nama Pelanggan</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['email']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Telepon</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone'] ?: '-'); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Alamat Pengiriman</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                <div class="info-group">
                    <div class="info-label">Catatan</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['notes']); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Order Items -->
            <div class="content-card">
                <h2>Item Pesanan</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($order_items as $item): ?>
                        <tr>
                            <td>
                                <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <small style="color: #666;"><?php echo htmlspecialchars($item['category_name']); ?></small>
                            </td>
                            <td><?php echo formatRupiah($item['price']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatRupiah($item['price'] * $item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Order Summary -->
                <div style="max-width: 300px; margin-left: auto; margin-top: 2rem;">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?php echo formatRupiah($order['total_amount'] - $order['shipping_cost']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim:</span>
                        <span><?php echo formatRupiah($order['shipping_cost']); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span><?php echo formatRupiah($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 