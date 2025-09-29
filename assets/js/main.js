/**
 * Main JavaScript
 * Praisetech - Campus IT Support System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initializeApp();
    
    // Add smooth scrolling
    addSmoothScrolling();
    
    // Add mobile menu functionality
    addMobileMenu();
    
    // Add form enhancements
    addFormEnhancements();
    
    // Add header enhancements
    addHeaderEnhancements();
});

function initializeApp() {
    console.log('Praisetech Campus IT Support System initialized');
    
    // Add any global initialization code here
    checkUserSession();
}

function checkUserSession() {
    // This would typically check with the server for session status
    // For now, we'll just log that the check is happening
    console.log('Checking user session...');
}

function addSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function addMobileMenu() {
    // Find existing mobile menu button
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('nav');
    
    if (mobileMenuBtn && nav) {
        // Add click handler
        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            nav.classList.toggle('mobile-open');
            this.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!nav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                nav.classList.remove('mobile-open');
                mobileMenuBtn.classList.remove('active');
            }
        });
        
        // Close menu when clicking on nav links
        const navLinks = nav.querySelectorAll('.nav-links a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                nav.classList.remove('mobile-open');
                mobileMenuBtn.classList.remove('active');
            });
        });
    }
}

function addFormEnhancements() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Add loading states to submit buttons
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Processing...';
                
                // Re-enable after 3 seconds (adjust as needed)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 3000);
            }
        });
    });
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(date) {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Header enhancement functions
function addHeaderEnhancements() {
    // Add scroll effect to header
    addHeaderScrollEffect();
    
    
}

function addHeaderScrollEffect() {
    const header = document.querySelector('header');
    if (!header) return;
    
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScrollY = currentScrollY;
    });
}

function addSearchFunctionality() {
    const searchInput = document.querySelector('.header-search input');
    if (!searchInput) return;
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                performSearch(query);
            }
        }
    });
    
    // Add search suggestions (simplified)
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length > 2) {
            showSearchSuggestions(query);
        } else {
            hideSearchSuggestions();
        }
    });
}

function performSearch(query) {
    console.log('Searching for:', query);
    // In a real application, this would make an API call
    showNotification(`Searching for: ${query}`, 'info');
}

function showSearchSuggestions(query) {
    // This would typically show a dropdown with suggestions
    console.log('Showing suggestions for:', query);
}

function hideSearchSuggestions() {
    // Hide search suggestions
    console.log('Hiding suggestions');
}


// Export functions for use in other scripts
window.CUTApp = {
    showNotification,
    formatDate,
    formatTime
};
