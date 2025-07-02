<?php
require_once 'config/database.php';

$pdo = getConnection();

// Mengambil produk featured
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.is_featured = 1 AND p.is_active = 1 
                       ORDER BY p.created_at DESC LIMIT 6");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Mengambil kategori aktif
$stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Bunga Online - Bunga Segar untuk Setiap Momen</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bunga Segar untuk Setiap Momen Spesial</h1>
            <p>Hadirkan kebahagiaan dengan koleksi bunga terbaik kami. Dari buket romantis hingga dekorasi elegan, temukan bunga yang sempurna untuk setiap acara.</p>
            <a href="products.php" class="btn btn-primary">Jelajahi Produk</a>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero-flowers.jpg" alt="Beautiful Flowers">
        </div>
    </section>

    <!-- Category Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Kategori Bunga</h2>
            <div class="category-grid">
                <?php foreach($categories as $category): ?>
                <div class="category-card">
                    <img src="assets/images/categories/<?php echo htmlspecialchars($category['image']); ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="category.php?id=<?php echo $category['id']; ?>" class="btn btn-outline">Lihat Produk</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">Produk Unggulan</h2>
            <div class="product-grid">
                <?php foreach($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-overlay">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                        <div class="product-price"><?php echo formatRupiah($product['price']); ?></div>
                        <div class="product-actions">
                            <button class="btn btn-outline add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-us">
        <div class="container">
            <h2 class="section-title">Mengapa Memilih Kami?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🌸</div>
                    <h3>Bunga Segar</h3>
                    <p>Bunga yang kami jual selalu dalam kondisi segar dan berkualitas tinggi</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <h3>Pengiriman Cepat</h3>
                    <p>Pengiriman same day untuk wilayah Jakarta dan sekitarnya</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Harga Terjangkau</h3>
                    <p>Harga kompetitif dengan kualitas terbaik di kelasnya</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <h3>Custom Arrangement</h3>
                    <p>Kami melayani permintaan custom sesuai dengan keinginan Anda</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 