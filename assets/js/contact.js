/**
 * Contact Page JavaScript
 * Chinhoyi University of Technology - Campus IT Support System
 */

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
    }
    
    // Add form validation
    addFormValidation();
});

function addFormValidation() {
    const inputs = document.querySelectorAll('#contactForm input, #contactForm select, #contactForm textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    // Remove existing error styling
    field.classList.remove('error');
    
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (field.type === 'email' && value && !isValidEmail(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    return true;
}

function clearFieldError(e) {
    const field = e.target;
    field.classList.remove('error');
    
    // Remove error message if exists
    const errorMsg = field.parentNode.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '5px';
    
    field.parentNode.appendChild(errorDiv);
}

async function handleContactForm(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const formObject = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        formObject[key] = value;
    }
    
    // Validate form
    if (!validateContactForm(formObject)) {
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    
    try {
        // Simulate form submission (replace with actual endpoint)
        await simulateFormSubmission(formObject);
        
        showMessage('Thank you for your message! We will get back to you within 24 hours.', 'success');
        contactForm.reset();
        
    } catch (error) {
        console.error('Form submission error:', error);
        showMessage('Sorry, there was an error sending your message. Please try again or contact us directly.', 'error');
    } finally {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

function validateContactForm(data) {
    const requiredFields = ['name', 'email', 'subject', 'priority', 'message'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!data[field] || data[field].trim() === '') {
            const fieldElement = document.getElementById(field);
            if (fieldElement) {
                showFieldError(fieldElement, 'This field is required');
                isValid = false;
            }
        }
    });
    
    if (data.email && !isValidEmail(data.email)) {
        const emailField = document.getElementById('email');
        if (emailField) {
            showFieldError(emailField, 'Please enter a valid email address');
            isValid = false;
        }
    }
    
    return isValid;
}

function simulateFormSubmission(data) {
    return new Promise((resolve, reject) => {
        // Simulate network delay
        setTimeout(() => {
            // Simulate success (in real implementation, send to server)
            console.log('Form data:', data);
            
            // Simulate occasional failure for testing
            if (Math.random() < 0.1) {
                reject(new Error('Simulated network error'));
            } else {
                resolve({ success: true, message: 'Message sent successfully' });
            }
        }, 2000);
    });
}

function showMessage(message, type = 'success') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Insert at the top of the form
    const form = document.getElementById('contactForm');
    form.parentNode.insertBefore(messageDiv, form);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Add CSS for error states
const style = document.createElement('style');
style.textContent = `
    .contact-form input.error,
    .contact-form select.error,
    .contact-form textarea.error {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
    
    .message {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 600;
        animation: slideDown 0.3s ease;
    }
    
    .message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
