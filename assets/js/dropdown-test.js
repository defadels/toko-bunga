// Simple dropdown test for debugging
console.log('Dropdown test script loaded');

// Test basic functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready for dropdown test');
    
    // Find dropdown elements
    const userDropdown = document.querySelector('.user-dropdown');
    const userBtn = document.querySelector('.user-btn');
    const userMenu = document.querySelector('.user-menu');
    
    console.log('Found elements:', {
        userDropdown: !!userDropdown,
        userBtn: !!userBtn,
        userMenu: !!userMenu
    });
    
    if (userDropdown) {
        console.log('Dropdown HTML:', userDropdown.outerHTML);
    }
    
    if (userBtn) {
        console.log('Button HTML:', userBtn.outerHTML);
        
        // Add simple click handler
        userBtn.addEventListener('click', function(e) {
            console.log('DROPDOWN TEST: Button clicked!');
            e.preventDefault();
            e.stopPropagation();
            
            if (userDropdown) {
                const isActive = userDropdown.classList.contains('active');
                console.log('Current active state:', isActive);
                
                if (isActive) {
                    userDropdown.classList.remove('active');
                    console.log('Removed active class');
                } else {
                    userDropdown.classList.add('active');
                    console.log('Added active class');
                }
                
                console.log('New classes:', userDropdown.className);
            }
        });
        
        console.log('Event listener added to button');
    }
    
    // Global test function
    window.manualDropdownTest = function() {
        console.log('Manual test function called');
        if (userDropdown) {
            userDropdown.classList.toggle('active');
            console.log('Toggled classes:', userDropdown.className);
        }
    };
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (userDropdown && !userDropdown.contains(e.target)) {
            console.log('Clicked outside, closing dropdown');
            userDropdown.classList.remove('active');
        }
    });
    
    console.log('Dropdown test initialization complete');
});

// Global toggle function for HTML onclick
window.toggleUserMenuTest = function() {
    console.log('toggleUserMenuTest called from HTML');
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        userDropdown.classList.toggle('active');
        console.log('Toggled from HTML onclick:', userDropdown.className);
    }
}; 