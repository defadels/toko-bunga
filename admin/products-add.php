<?php
require_once '../config/database.php';

if (!isPetugas()) {
    redirect('../login.php');
}

$pdo = getConnection();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $weight = (float)$_POST['weight'];
    $image = $_POST['image'] ?? 'default.jpg';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($name) || $price <= 0 || $category_id <= 0) {
        $error = 'Nama produk, kategori dan harga wajib diisi dengan benar.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, description, price, stock, image, weight, is_featured) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$category_id, $name, $description, $price, $stock, $image, $weight, $is_featured])) {
            $success = 'Produk berhasil ditambahkan.';
        } else {
            $error = 'Terjadi kesalahan saat menambahkan produk.';
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
    <title>Tambah Produk - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <a href="products.php" class="btn btn-outline">← Kembali</a>
                <h1>Tambah Produk Baru</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="content-card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama Produk *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_id">Kategori *</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Harga (Rp) *</label>
                            <input type="number" id="price" name="price" class="form-control" min="0" step="1000"
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="stock">Stok</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0"
                                   value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="weight">Berat (kg)</label>
                            <input type="number" id="weight" name="weight" class="form-control" min="0" step="0.1"
                                   value="<?php echo htmlspecialchars($_POST['weight'] ?? '0'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Nama File Gambar</label>
                        <input type="text" id="image" name="image" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['image'] ?? 'default.jpg'); ?>" 
                               placeholder="contoh: produk-bunga.jpg">
                        <small style="color: #666;">Upload gambar ke folder assets/images/products/</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                   <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                            <label for="is_featured">Jadikan produk unggulan</label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">Tambah Produk</button>
                        <a href="products.php" class="btn btn-outline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 