<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();

// Get products with category names
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Kelola Produk</h1>
                <a href="products-add.php" class="btn btn-primary">Tambah Produk</a>
            </div>

            <div class="content-card">
                <h2>Daftar Produk</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td>
                                <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td><?php echo formatRupiah($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo $product['is_featured'] ? 'Ya' : 'Tidak'; ?></td>
                            <td><?php echo $product['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?></td>
                            <td>
                                <a href="products-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 