<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Sales summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders
    FROM orders 
    WHERE DATE(order_date) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();

// Daily sales
$stmt = $pdo->prepare("
    SELECT 
        DATE(order_date) as sale_date,
        COUNT(*) as orders_count,
        SUM(total_amount) as daily_revenue
    FROM orders 
    WHERE DATE(order_date) BETWEEN ? AND ?
    AND status NOT IN ('cancelled')
    GROUP BY DATE(order_date)
    ORDER BY sale_date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();

// Best selling products
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        c.name as category_name,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.order_date) BETWEEN ? AND ?
    AND o.status NOT IN ('cancelled')
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$best_products = $stmt->fetchAll();

// Sales by status
$stmt = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(order_date) BETWEEN ? AND ?
    GROUP BY status
    ORDER BY count DESC
");
$stmt->execute([$start_date, $end_date]);
$sales_by_status = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Laporan Penjualan</h1>
                <div>
                    <span style="color: #666;">Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></span>
                </div>
            </div>
            
            <!-- Date Filter -->
            <div class="content-card">
                <form method="GET" class="date-filter">
                    <div>
                        <label>Dari Tanggal:</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div>
                        <label>Sampai Tanggal:</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="reports.php" class="btn btn-outline">Reset</a>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $summary['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo formatRupiah($summary['total_revenue'] ?? 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo formatRupiah($summary['avg_order_value'] ?? 0); ?></div>
                    <div class="stat-label">Rata-rata per Pesanan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $summary['completed_orders'] ?? 0; ?></div>
                    <div class="stat-label">Pesanan Selesai</div>
                </div>
            </div>

            <div class="reports-grid">
                <!-- Daily Sales -->
                <div class="content-card">
                    <h2>Penjualan Harian</h2>
                    <?php if (empty($daily_sales)): ?>
                        <p>Tidak ada data penjualan untuk periode ini.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pesanan</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($daily_sales as $sale): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($sale['sale_date'])); ?></td>
                                    <td><?php echo $sale['orders_count']; ?></td>
                                    <td><?php echo formatRupiah($sale['daily_revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Sales by Status -->
                <div class="content-card">
                    <h2>Penjualan berdasarkan Status</h2>
                    <?php if (empty($sales_by_status)): ?>
                        <p>Tidak ada data.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Jumlah</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($sales_by_status as $status): ?>
                                <tr>
                                    <td><?php echo ucfirst($status['status']); ?></td>
                                    <td><?php echo $status['count']; ?></td>
                                    <td><?php echo formatRupiah($status['revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Best Selling Products -->
            <div class="content-card">
                <h2>Produk Terlaris</h2>
                <?php if (empty($best_products)): ?>
                    <p>Tidak ada data produk terjual untuk periode ini.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Terjual</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($best_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo $product['total_sold']; ?></td>
                                <td><?php echo formatRupiah($product['total_revenue']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 