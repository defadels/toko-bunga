<?php
require_once '../config/database.php';

// Check if user is admin or petugas
if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$stats['products'] = $stmt->fetch()['total'];

// Total categories
$stmt = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
$stats['categories'] = $stmt->fetch()['total'];

// Total customers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'pelanggan' AND is_active = 1");
$stats['customers'] = $stmt->fetch()['total'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $stmt->fetch()['total'];

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetch()['total'];

// Monthly revenue
$stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE()) AND status NOT IN ('cancelled')");
$stats['monthly_revenue'] = $stmt->fetch()['revenue'] ?? 0;

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC 
    LIMIT 10
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.stock <= 5 AND p.is_active = 1 
    ORDER BY p.stock ASC 
    LIMIT 10
");
$stmt->execute();
$low_stock_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header-actions">
                <h1>Dashboard</h1>
                <div>
                    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
                    <a href="../logout.php" class="btn btn-outline" style="margin-left: 1rem;">Logout</a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-number"><?php echo $stats['products']; ?></div>
                    <div class="stat-label">Total Produk</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📂</div>
                    <div class="stat-number"><?php echo $stats['categories']; ?></div>
                    <div class="stat-label">Kategori</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $stats['customers']; ?></div>
                    <div class="stat-label">Pelanggan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-number"><?php echo $stats['orders']; ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
                    <div class="stat-label">Pesanan Pending</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number"><?php echo formatRupiah($stats['monthly_revenue']); ?></div>
                    <div class="stat-label">Revenue Bulan Ini</div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="dashboard-section">
                <h2 class="section-title">Pesanan Terbaru</h2>
                <?php if (empty($recent_orders)): ?>
                    <p>Belum ada pesanan.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo formatRupiah($order['total_amount']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="orders-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding: 4px 8px; font-size: 0.8rem;">Detail</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="orders.php" class="btn btn-primary">Lihat Semua Pesanan</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock_products)): ?>
            <div class="dashboard-section">
                <h2 class="section-title">⚠️ Stok Menipis</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($low_stock_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td class="low-stock"><?php echo $product['stock']; ?></td>
                            <td><?php echo formatRupiah($product['price']); ?></td>
                            <td>
                                <a href="products-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-outline" style="padding: 4px 8px; font-size: 0.8rem;">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
 