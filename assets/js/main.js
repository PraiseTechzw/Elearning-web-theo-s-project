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
    // Create mobile menu button
    const nav = document.querySelector('nav');
    if (nav) {
        const mobileMenuBtn = document.createElement('button');
        mobileMenuBtn.className = 'mobile-menu-btn';
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        mobileMenuBtn.setAttribute('aria-label', 'Toggle mobile menu');
        
        // Insert before nav
        nav.parentNode.insertBefore(mobileMenuBtn, nav);
        
        // Add click handler
        mobileMenuBtn.addEventListener('click', function() {
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

// Export functions for use in other scripts
window.PraisetechApp = {
    showNotification,
    formatDate,
    formatTime
};
