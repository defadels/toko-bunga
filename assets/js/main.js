// Main JavaScript file for Toko Bunga

document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeCart();
    initializeUserDropdown();
    initializeMobileMenu();
    updateCartCount();
});

// Live Search Functionality
function initializeSearch() {
    const searchInput = document.getElementById('search-input');
    const searchSuggestions = document.getElementById('search-suggestions');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchSuggestions.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                searchSuggestions.style.display = 'none';
            }
        });
        
        // Handle Enter key for search
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (query) {
                    window.location.href = `products.php?search=${encodeURIComponent(query)}`;
                }
            }
        });
    }
}

// Perform live search
function performSearch(query) {
    const searchSuggestions = document.getElementById('search-suggestions');
    
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.products.length > 0) {
                displaySearchSuggestions(data.products);
            } else {
                searchSuggestions.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            searchSuggestions.style.display = 'none';
        });
}

// Display search suggestions
function displaySearchSuggestions(products) {
    const searchSuggestions = document.getElementById('search-suggestions');
    
    let html = '';
    products.forEach(product => {
        html += `
            <div class="suggestion-item" onclick="goToProduct(${product.id})">
                <span style="font-size: 1.2rem;">🌹</span>
                <div>
                    <strong>${product.name}</strong>
                    <div style="font-size: 0.9em; color: #666;">
                        ${product.category_name} • ${formatRupiah(product.price)}
                    </div>
                </div>
            </div>
        `;
    });
    
    // Add "View all results" option
    html += `
        <div class="suggestion-item" onclick="searchAll()" style="border-top: 1px solid #eee; font-weight: bold; color: #4CAF50;">
            <span style="font-size: 1.2rem;">🔍</span>
            <div>Lihat semua hasil pencarian</div>
        </div>
    `;
    
    searchSuggestions.innerHTML = html;
    searchSuggestions.style.display = 'block';
}

// Search all products
function searchAll() {
    const query = document.getElementById('search-input').value.trim();
    if (query) {
        window.location.href = `products.php?search=${encodeURIComponent(query)}`;
    }
}

// Navigate to product detail
function goToProduct(productId) {
    window.location.href = `product-detail.php?id=${productId}`;
}

// Initialize user dropdown functionality
function initializeUserDropdown() {
    const userDropdown = document.querySelector('.user-dropdown');
    const userBtn = document.querySelector('.user-btn');
    
    if (userBtn) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
    }
}

// Toggle user menu (for mobile compatibility)
function toggleUserMenu() {
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        userDropdown.classList.toggle('active');
    }
}

// Initialize mobile menu functionality
function initializeMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            toggleMobileMenu();
        });
        
        // Close mobile menu when clicking nav links
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
    }
}

// Toggle mobile menu
function toggleMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.classList.toggle('active');
        
        if (mobileMenu.style.display === 'block') {
            mobileMenu.style.display = 'none';
        } else {
            mobileMenu.style.display = 'block';
        }
    }
}

// Close mobile menu
function closeMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.classList.remove('active');
        mobileMenu.style.display = 'none';
    }
}

// Initialize cart functionality
function initializeCart() {
    // Add event listeners to "Add to Cart" buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart') || e.target.classList.contains('add-to-cart-btn')) {
            e.preventDefault();
            const productId = e.target.getAttribute('data-product-id');
            addToCart(productId);
        }
    });
}

// Add product to cart
function addToCart(productId, quantity = 1) {
    // Check if user is logged in
    if (!isUserLoggedIn()) {
        showAlert('Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.', 'warning');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Produk berhasil ditambahkan ke keranjang! 🛒', 'success');
            updateCartCount();
        } else {
            showAlert(data.message || 'Gagal menambahkan produk ke keranjang.', 'error');
        }
    })
    .catch(error => {
        console.error('Cart error:', error);
        showAlert('Terjadi kesalahan. Silakan coba lagi.', 'error');
    });
}

// Update cart count in header
function updateCartCount() {
    if (!isUserLoggedIn()) return;

    fetch('api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                    cartCountElement.style.display = data.count > 0 ? 'flex' : 'none';
                    
                    // Add animation when count changes
                    cartCountElement.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        cartCountElement.style.transform = 'scale(1)';
                    }, 200);
                }
            }
        })
        .catch(error => {
            console.error('Cart count error:', error);
        });
}

// Check if user is logged in
function isUserLoggedIn() {
    // This will be set by PHP
    return typeof userLoggedIn !== 'undefined' && userLoggedIn;
}

// Format price to Rupiah
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Show alert message
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer; margin-left: 1rem;">×</button>
    `;
    
    // Add to top of body
    document.body.insertBefore(alert, document.body.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
    
    // Add entrance animation
    setTimeout(() => {
        alert.style.transform = 'translateY(0)';
        alert.style.opacity = '1';
    }, 10);
}

// Product quantity controls
function changeQuantity(change) {
    const quantityInput = document.getElementById('quantity');
    if (quantityInput) {
        let currentValue = parseInt(quantityInput.value) || 1;
        let newValue = currentValue + change;
        
        if (newValue < 1) newValue = 1;
        if (newValue > 100) newValue = 100; // Maximum quantity limit
        
        quantityInput.value = newValue;
        updateTotalPrice();
    }
}

// Update total price on product detail page
function updateTotalPrice() {
    const quantityInput = document.getElementById('quantity');
    const priceElement = document.getElementById('product-price');
    const totalElement = document.getElementById('total-price');
    
    if (quantityInput && priceElement && totalElement) {
        const quantity = parseInt(quantityInput.value) || 1;
        const price = parseInt(priceElement.getAttribute('data-price')) || 0;
        const total = quantity * price;
        
        totalElement.textContent = formatRupiah(total);
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Smooth scrolling for anchor links
document.addEventListener('click', function(e) {
    if (e.target.matches('a[href^="#"]')) {
        e.preventDefault();
        const targetId = e.target.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});

// Loading state for buttons
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Memproses...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }
}

// Image lazy loading
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading if supported
if ('IntersectionObserver' in window) {
    initializeLazyLoading();
} 