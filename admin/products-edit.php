<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();
$success = '';
$error = '';

// Get product ID
$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    redirect('products.php');
}

// Get product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $weight = (float)$_POST['weight'];
    $image = $_POST['image'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = (int)$_POST['is_active'];
    
    if (empty($name) || $price <= 0 || $category_id <= 0) {
        $error = 'Nama produk, kategori dan harga wajib diisi dengan benar.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, 
                image = ?, weight = ?, is_featured = ?, is_active = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$category_id, $name, $description, $price, $stock, $image, $weight, $is_featured, $is_active, $product_id])) {
            $success = 'Produk berhasil diupdate.';
            // Refresh product data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
        } else {
            $error = 'Terjadi kesalahan saat mengupdate produk.';
        }
    }
}

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <a href="products.php" class="btn btn-outline">← Kembali</a>
                <h1>Edit Produk</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="content-card">
                <!-- Product Preview -->
                <div class="product-preview">
                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjVmNWY1Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                    <div>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p style="color: #666;">ID: <?php echo $product['id']; ?></p>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama Produk *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_id">Kategori *</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Harga (Rp) *</label>
                            <input type="number" id="price" name="price" class="form-control" min="0" step="1000"
                                   value="<?php echo $product['price']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="stock">Stok</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0"
                                   value="<?php echo $product['stock']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="weight">Berat (kg)</label>
                            <input type="number" id="weight" name="weight" class="form-control" min="0" step="0.1"
                                   value="<?php echo $product['weight']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Nama File Gambar</label>
                        <input type="text" id="image" name="image" class="form-control" 
                               value="<?php echo htmlspecialchars($product['image']); ?>" 
                               placeholder="contoh: produk-bunga.jpg">
                        <small style="color: #666;">Upload gambar ke folder assets/images/products/</small>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                       <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                <label for="is_featured">Produk unggulan</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select id="is_active" name="is_active" class="form-control">
                                <option value="1" <?php echo $product['is_active'] ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !$product['is_active'] ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">Update Produk</button>
                        <a href="products.php" class="btn btn-outline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 