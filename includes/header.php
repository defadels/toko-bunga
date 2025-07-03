<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">
                <span class="logo-icon">🌺</span>
                <span class="logo-text">FloRisen</span>
            </a>
            
            <nav class="main-nav">
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="products.php" class="nav-link">Produk</a></li>
                    <li><a href="categories.php" class="nav-link">Kategori</a></li>
                    <li><a href="about.php" class="nav-link">Tentang Kami</a></li>
                    <li><a href="contact.php" class="nav-link">Kontak</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" id="search-input" placeholder="Cari bunga..." autocomplete="off">
                        <button class="search-btn" type="button">
                            <span>🔍</span>
                        </button>
                    </div>
                    <div id="search-suggestions" class="search-suggestions"></div>
                </div>
                
                <div class="user-section">
                    <?php if(isLoggedIn()): ?>
                        <a href="cart.php" class="cart-link">
                            <span class="cart-icon">🛒</span>
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                        
                        <div class="user-dropdown">
                            <button class="user-btn" onclick="toggleUserMenuTest()" type="button">
                                <span class="user-avatar">👤</span>
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <div class="user-menu" id="user-menu">
                                <a href="profile.php" class="menu-item">👤 Profil Saya</a>
                                <a href="orders.php" class="menu-item">📦 Pesanan Saya</a>
                                <?php if(isPetugas()): ?>
                                    <div class="menu-divider"></div>
                                    <a href="admin/" class="menu-item admin-link">⚙️ Dashboard Admin</a>
                                <?php endif; ?>
                                <div class="menu-divider"></div>
                                <a href="logout.php" class="menu-item logout-link">🚪 Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="login.php" class="btn btn-outline">Login</a>
                            <a href="register.php" class="btn btn-primary">Daftar</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobile-menu">
            <div class="mobile-nav">
                <a href="index.php" class="mobile-nav-link">Home</a>
                <a href="products.php" class="mobile-nav-link">Produk</a>
                <a href="categories.php" class="mobile-nav-link">Kategori</a>
                <a href="about.php" class="mobile-nav-link">Tentang Kami</a>
                <a href="contact.php" class="mobile-nav-link">Kontak</a>
                
                <?php if(isLoggedIn()): ?>
                    <div class="mobile-user-section">
                        <div class="mobile-user-info">
                            <span class="mobile-user-avatar">👤</span>
                            <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        </div>
                        <a href="cart.php" class="mobile-nav-link">🛒 Keranjang</a>
                        <a href="profile.php" class="mobile-nav-link">👤 Profil</a>
                        <a href="orders.php" class="mobile-nav-link">📦 Pesanan</a>
                        <?php if(isPetugas()): ?>
                            <a href="admin/" class="mobile-nav-link">⚙️ Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="mobile-nav-link">🚪 Logout</a>
                    </div>
                <?php else: ?>
                    <div class="mobile-auth">
                        <a href="login.php" class="btn btn-outline mobile-auth-btn">Login</a>
                        <a href="register.php" class="btn btn-primary mobile-auth-btn">Daftar</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- JavaScript Variables -->
<script>
    // Set user login status for JavaScript
    var userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
</script>

<!-- Dropdown Test Script -->
<script src="dropdown-test.js"></script> 