// Main JavaScript file for Toko Bunga

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    console.log('User logged in:', typeof userLoggedIn !== 'undefined' ? userLoggedIn : 'undefined');
    
    initializeSearch();
    initializeCart();
    initializeUserDropdown();
    initializeMobileMenu();
    updateCartCount();
    
    // Manual test for dropdown
    setTimeout(() => {
        testDropdownFunctionality();
    }, 1000);
});

// Test dropdown functionality
function testDropdownFunctionality() {
    const userDropdown = document.querySelector('.user-dropdown');
    const userBtn = document.querySelector('.user-btn');
    const userMenu = document.querySelector('.user-menu');
    
    console.log('Testing dropdown:', {
        userDropdown: !!userDropdown,
        userBtn: !!userBtn,
        userMenu: !!userMenu,
        userDropdownHTML: userDropdown ? userDropdown.outerHTML : 'null'
    });
    
    if (userBtn) {
        console.log('Button event listeners:', getEventListeners ? getEventListeners(userBtn) : 'Cannot get listeners');
    }
}

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
    const userMenu = document.querySelector('.user-menu');
    
    console.log('Initializing user dropdown...', {
        userDropdown: !!userDropdown,
        userBtn: !!userBtn,
        userMenu: !!userMenu
    });
    
    if (userBtn && userDropdown) {
        // Remove any existing click listeners
        userBtn.removeEventListener('click', handleUserBtnClick);
        
        // Add new click listener
        userBtn.addEventListener('click', handleUserBtnClick);
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                console.log('Clicking outside, closing dropdown');
                closeUserDropdown();
            }
        });
        
        console.log('User dropdown initialized successfully');
    } else {
        console.log('User dropdown elements not found');
    }
}

// Handle user button click
function handleUserBtnClick(e) {
    console.log('User button clicked via event listener');
    e.preventDefault();
    e.stopPropagation();
    toggleUserMenuAdvanced();
}

// Advanced toggle function
function toggleUserMenuAdvanced() {
    const userDropdown = document.querySelector('.user-dropdown');
    const userMenu = document.querySelector('.user-menu');
    
    console.log('toggleUserMenuAdvanced called', {
        userDropdown: !!userDropdown,
        userMenu: !!userMenu
    });
    
    if (userDropdown && userMenu) {
        const isActive = userDropdown.classList.contains('active');
        console.log('Current state:', isActive ? 'active' : 'inactive');
        
        if (isActive) {
            closeUserDropdown();
        } else {
            openUserDropdown();
        }
    }
}

// Open user dropdown
function openUserDropdown() {
    const userDropdown = document.querySelector('.user-dropdown');
    console.log('Opening dropdown...');
    
    if (userDropdown) {
        userDropdown.classList.add('active');
        console.log('Dropdown opened, classes:', userDropdown.className);
    }
}

// Close user dropdown
function closeUserDropdown() {
    const userDropdown = document.querySelector('.user-dropdown');
    console.log('Closing dropdown...');
    
    if (userDropdown) {
        userDropdown.classList.remove('active');
        console.log('Dropdown closed, classes:', userDropdown.className);
    }
}

// Toggle user menu (for compatibility with onclick in HTML)
function toggleUserMenu() {
    console.log('toggleUserMenu called from HTML onclick');
    toggleUserMenuAdvanced();
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
    console.log('Initializing cart...');
    
    // Add event listeners to "Add to Cart" buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart') || e.target.classList.contains('add-to-cart-btn')) {
            console.log('Add to cart button clicked');
            e.preventDefault();
            const productId = e.target.getAttribute('data-product-id');
            console.log('Product ID:', productId);
            addToCart(productId);
        }
    });
}

// Add product to cart
function addToCart(productId, quantity = 1) {
    console.log('addToCart called with:', {productId, quantity, userLoggedIn});
    
    // Check if user is logged in
    if (!isUserLoggedIn()) {
        console.log('User not logged in, redirecting...');
        showAlert('Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.', 'warning');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }

    console.log('User is logged in, proceeding with add to cart...');
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    console.log('Sending request to api/cart.php...');
    
    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
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
    if (!isUserLoggedIn()) {
        console.log('User not logged in, skipping cart count update');
        return;
    }

    console.log('Updating cart count...');

    fetch('api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            console.log('Cart count data:', data);
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
    const isLoggedIn = typeof userLoggedIn !== 'undefined' && userLoggedIn;
    console.log('isUserLoggedIn check:', {userLoggedIn, isLoggedIn});
    return isLoggedIn;
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

// Manual dropdown test function (for debugging)
window.testDropdown = function() {
    console.log('Manual dropdown test started');
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        console.log('Found dropdown, toggling...');
        userDropdown.classList.toggle('active');
        console.log('New state:', userDropdown.classList.contains('active') ? 'active' : 'inactive');
    } else {
        console.log('Dropdown not found!');
    }
};

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