<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();
$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $order_id])) {
            $success = 'Status pesanan berhasil diupdate.';
        } else {
            $error = 'Gagal mengupdate status pesanan.';
        }
    } else {
        $error = 'Status tidak valid.';
    }
}

// Handle payment status update  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_payment_status = $_POST['payment_status'];
    
    $valid_payment_statuses = ['pending', 'paid', 'failed'];
    
    if (in_array($new_payment_status, $valid_payment_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        if ($stmt->execute([$new_payment_status, $order_id])) {
            $success = 'Status pembayaran berhasil diupdate.';
        } else {
            $error = 'Gagal mengupdate status pembayaran.';
        }
    } else {
        $error = 'Status pembayaran tidak valid.';
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$payment_status_filter = $_GET['payment_status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($payment_status_filter)) {
    $where_conditions[] = "o.payment_status = ?";
    $params[] = $payment_status_filter;
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(o.order_date) = ?";
    $params[] = $date_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get orders
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $where_clause
    ORDER BY o.order_date DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Status options
$status_options = [
    'pending' => 'Pending',
    'confirmed' => 'Dikonfirmasi',
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'delivered' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

// Payment status options
$payment_status_options = [
    'pending' => 'Belum Bayar',
    'paid' => 'Sudah Bayar',
    'failed' => 'Gagal'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Kelola Pesanan</h1>
                <div>
                    <span style="color: #666;">Management pesanan & status</span>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="content-card">
                <form method="GET" class="filters">
                    <div class="filter-group">
                        <label>Status:</label>
                        <select name="status" class="filter-select">
                            <option value="">Semua Status</option>
                            <?php foreach($status_options as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $status_filter == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Status Pembayaran:</label>
                        <select name="payment_status" class="filter-select">
                            <option value="">Semua Status</option>
                            <?php foreach($payment_status_options as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $payment_status_filter == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Tanggal:</label>
                        <input type="date" name="date" class="filter-select" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    
                    <?php if (!empty($status_filter) || !empty($payment_status_filter) || !empty($date_filter)): ?>
                        <a href="orders.php" class="btn btn-outline">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Orders List -->
            <div class="content-card">
                <h2>Daftar Pesanan (<?php echo count($orders); ?>)</h2>
                
                <?php if (empty($orders)): ?>
                    <p>Tidak ada pesanan ditemukan.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Status Bayar</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <small style="color: #666;"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                </td>
                                <td><?php echo formatRupiah($order['total_amount']); ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <?php foreach($status_options as $value => $label): ?>
                                                <option value="<?php echo $value; ?>" 
                                                        <?php echo $order['status'] == $value ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="payment_status" class="status-select" onchange="this.form.submit()">
                                            <?php foreach($payment_status_options as $value => $label): ?>
                                                <option value="<?php echo $value; ?>" 
                                                        <?php echo $order['payment_status'] == $value ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="update_payment_status" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="orders-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">Detail</a>
                                </td>
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