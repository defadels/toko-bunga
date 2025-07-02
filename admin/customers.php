<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();

// Get customers with order statistics
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent,
           MAX(o.order_date) as last_order
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'pelanggan'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Data Pelanggan</h1>
                <div>
                    <span style="color: #666;">Total: <?php echo count($customers); ?> pelanggan</span>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($customers); ?></div>
                    <div>Total Pelanggan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($customers, fn($c) => $c['is_active'])); ?></div>
                    <div>Pelanggan Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($customers, fn($c) => $c['total_orders'] > 0)); ?></div>
                    <div>Pernah Pesan</div>
                </div>
            </div>

            <div class="content-card">
                <h2>Daftar Pelanggan</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Total Pesanan</th>
                            <th>Total Belanja</th>
                            <th>Terakhir Pesan</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?: '-'); ?></td>
                            <td><?php echo $customer['total_orders']; ?></td>
                            <td><?php echo formatRupiah($customer['total_spent']); ?></td>
                            <td><?php echo $customer['last_order'] ? date('d/m/Y', strtotime($customer['last_order'])) : '-'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $customer['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $customer['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 