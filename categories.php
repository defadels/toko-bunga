<?php
require_once 'config/database.php';

// Get all categories
$categories = getAllCategories();

// Page info
$page_title = 'Kategori Produk';
$meta_description = 'Jelajahi berbagai kategori bunga segar dan dekorasi untuk setiap kebutuhan Anda di Toko Bunga Online.';
$canonical_url = 'categories.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <script>
        // Set global variables for JavaScript
        var userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
    </script>
    
    <?php include 'includes/header.php'; ?>

<main class="main-content">
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">Kategori Produk</h1>
                <p class="page-description">Jelajahi berbagai kategori bunga segar dan dekorasi untuk setiap kebutuhan Anda</p>
                <nav class="breadcrumb">
                    <a href="index.php">Beranda</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">Kategori</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="categories-section">
        <div class="container">
            <?php if (!empty($categories)): ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <a href="category.php?id=<?php echo $category['id']; ?>" class="category-link">
                                <div class="category-image">
                                    <?php if (!empty($category['image']) && file_exists('assets/images/categories/' . $category['image'])): ?>
                                        <img src="assets/images/categories/<?php echo htmlspecialchars($category['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="category-placeholder">
                                            <span class="category-icon">🌸</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="category-overlay">
                                        <span class="view-category-btn">Lihat Produk</span>
                                    </div>
                                </div>
                                
                                <div class="category-info">
                                    <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                                    
                                    <?php if (!empty($category['description'])): ?>
                                        <p class="category-description">
                                            <?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>
                                            <?php if (strlen($category['description']) > 100): ?>...<?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="category-stats">
                                        <?php
                                        $productCount = getProductCountByCategory($category['id']);
                                        ?>
                                        <span class="product-count">
                                            <span class="count-number"><?php echo $productCount; ?></span>
                                            <span class="count-label">Produk</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <h3>Belum Ada Kategori</h3>
                    <p>Kategori produk belum tersedia saat ini. Silakan kembali lagi nanti.</p>
                    <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="featured-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Produk Terpopuler</h2>
                <p class="section-description">Produk bunga terlaris dan paling disukai pelanggan</p>
            </div>
            
            <div class="products-grid">
                <?php
                $featuredProducts = getFeaturedProducts(4);
                foreach ($featuredProducts as $product):
                ?>
                    <div class="product-card">
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-link">
                            <div class="product-image">
                                <?php if (!empty($product['image']) && file_exists('assets/images/products/' . $product['image'])): ?>
                                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="product-placeholder">
                                        <span class="product-icon">🌹</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($product['stock'] < 5): ?>
                                    <span class="stock-badge low-stock">Stok Terbatas</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                
                                <div class="product-category">
                                    <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                                </div>
                                
                                <div class="product-price">
                                    <span class="current-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="products.php" class="btn btn-outline btn-large">Lihat Semua Produk</a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
