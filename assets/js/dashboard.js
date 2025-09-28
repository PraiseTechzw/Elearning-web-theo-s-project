/**
 * Dashboard JavaScript
 * Praisetech - Campus IT Support System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Add smooth scrolling for anchor links
    addSmoothScrolling();
    
    // Add loading states for buttons
    addLoadingStates();
});

function initializeDashboard() {
    console.log('Dashboard initialized - Praisetech Campus IT Support');
    
    // Add any initialization code here
    checkUserSession();
}

function checkUserSession() {
    // This would typically check with the server
    // For now, we'll just log that the session check is happening
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

function addLoadingStates() {
    const buttons = document.querySelectorAll('.action-btn, .card-link');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.classList.contains('action-btn')) {
                showLoading(this);
            }
        });
    });
}

function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Loading...';
    button.disabled = true;
    
    // Simulate loading (remove this in production)
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

// Quick Action Functions
function reportIssue() {
    showMessage('Issue reporting feature coming soon!', 'success');
    // TODO: Implement issue reporting modal/form
}

function requestSupport() {
    showMessage('Support request feature coming soon!', 'success');
    // TODO: Implement support request form
}

function viewTickets() {
    showMessage('Ticket system coming soon!', 'success');
    // TODO: Implement ticket viewing system
}

function showMessage(message, type = 'success') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    // Insert at the top of main content
    const main = document.querySelector('.dashboard-main');
    main.insertBefore(messageDiv, main.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Utility functions
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
window.PraisetechDashboard = {
    showMessage,
    formatDate,
    formatTime,
    reportIssue,
    requestSupport,
    viewTickets
};
