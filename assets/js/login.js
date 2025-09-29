/**
 * Enhanced Login/Signup JavaScript
 * Chinhoyi University of Technology - Campus IT Support System
 */

let currentTab = 'login';
let passwordStrength = {
  weak: 0,
  fair: 1,
  good: 2,
  strong: 3
};

document.addEventListener('DOMContentLoaded', function() {
    initializeAuth();
    setupEventListeners();
    addFormValidation();
});

function initializeAuth() {
    // Check if user is already logged in
    checkLoginStatus();
    
    // Set up password strength checking
    setupPasswordStrength();
    
    // Set up form validation
    setupFormValidation();
}

function setupEventListeners() {
    // Login form
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Signup form
    document.getElementById('signupForm').addEventListener('submit', handleSignup);
    
    // Forgot password form
    document.getElementById('forgotForm').addEventListener('submit', handleForgotPassword);
    
    // Password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', validatePasswordMatch);
    
    // Real-time password strength checking
    document.getElementById('signupPassword').addEventListener('input', checkPasswordStrength);
}

function switchTab(tabName) {
    currentTab = tabName;
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    
    // Update forms
    document.querySelectorAll('.auth-form').forEach(form => {
        form.classList.remove('active');
    });
    document.querySelector(`[data-form="${tabName}"]`).classList.add('active');
    
    // Clear any existing messages
    clearMessages();
    
    // Reset forms
    resetForms();
}

function addFormValidation() {
    const inputs = document.querySelectorAll('input[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

function setupFormValidation() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', validateEmail);
    });
    
    // Name validation
    const nameInputs = document.querySelectorAll('input[name="first_name"], input[name="last_name"]');
    nameInputs.forEach(input => {
        input.addEventListener('blur', validateName);
    });
}

function setupPasswordStrength() {
    const passwordInput = document.getElementById('signupPassword');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    clearFieldError(e);
    
    if (!value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    return true;
}

function validateEmail(e) {
    const field = e.target;
    const value = field.value.trim();
    
    if (value && !isValidEmail(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    return true;
}

function validateName(e) {
    const field = e.target;
    const value = field.value.trim();
    
    if (value && !isValidName(value)) {
        showFieldError(field, 'Name should only contain letters and spaces');
        return false;
    }
    
    return true;
}

function validatePasswordMatch() {
    const password = document.getElementById('signupPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (confirmPassword && password !== confirmPassword) {
        showFieldError(document.getElementById('confirmPassword'), 'Passwords do not match');
        return false;
    }
    
    return true;
}

function checkPasswordStrength() {
    const password = document.getElementById('signupPassword').value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    if (!password) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    const strength = calculatePasswordStrength(password);
    const strengthText = ['Weak', 'Fair', 'Good', 'Strong'][strength];
    const strengthClass = ['weak', 'fair', 'good', 'strong'][strength];
    
    strengthDiv.innerHTML = `
        <div class="password-strength-bar"></div>
        <span class="strength-text">${strengthText}</span>
    `;
    strengthDiv.className = `password-strength ${strengthClass}`;
}

function calculatePasswordStrength(password) {
    let score = 0;
    
    // Length check
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    
    // Character variety checks
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    return Math.min(3, Math.floor(score / 2));
}

function clearFieldError(e) {
    const field = e.target;
    field.classList.remove('error');
    
    const errorMsg = field.parentNode.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
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
    field.parentNode.appendChild(errorDiv);
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidName(name) {
    const nameRegex = /^[a-zA-Z\s]+$/;
    return nameRegex.test(name);
}

async function handleLogin(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const email = formData.get('email').trim();
    const password = formData.get('password');
    const remember = formData.get('remember') === 'on';
    
    // Validate form
    if (!email || !password) {
        showMessage('Please fill in all required fields', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    setLoadingState('loginBtn', true);
    
    try {
        const response = await fetch('../includes/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                password: password,
                remember: remember
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1500);
        } else {
            showMessage(result.message, 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    } finally {
        setLoadingState('loginBtn', false);
    }
}

async function handleSignup(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const firstName = formData.get('first_name').trim();
    const lastName = formData.get('last_name').trim();
    const email = formData.get('email').trim();
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    const userType = formData.get('user_type');
    const studentId = formData.get('student_id').trim();
    const agreeTerms = formData.get('agree_terms') === 'on';
    
    // Validate form
    if (!firstName || !lastName || !email || !password || !confirmPassword || !userType) {
        showMessage('Please fill in all required fields', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    if (!isValidName(firstName) || !isValidName(lastName)) {
        showMessage('Names should only contain letters and spaces', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showMessage('Passwords do not match', 'error');
        return;
    }
    
    if (password.length < 8) {
        showMessage('Password must be at least 8 characters long', 'error');
        return;
    }
    
    if (!agreeTerms) {
        showMessage('Please agree to the Terms of Service and Privacy Policy', 'error');
        return;
    }
    
    setLoadingState('signupBtn', true);
    
    try {
        const response = await fetch('../includes/signup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                email: email,
                password: password,
                user_type: userType,
                student_id: studentId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Account created successfully! Please check your email for verification.', 'success');
            setTimeout(() => {
                switchTab('login');
            }, 2000);
        } else {
            showMessage(result.message, 'error');
        }
    } catch (error) {
        console.error('Signup error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    } finally {
        setLoadingState('signupBtn', false);
    }
}

async function handleForgotPassword(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const email = formData.get('email').trim();
    
    if (!email || !isValidEmail(email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    setLoadingState('forgotBtn', true);
    
    try {
        const response = await fetch('../includes/forgot-password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Password reset link sent to your email', 'success');
            setTimeout(() => {
                switchTab('login');
            }, 2000);
        } else {
            showMessage(result.message, 'error');
        }
    } catch (error) {
        console.error('Forgot password error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    } finally {
        setLoadingState('forgotBtn', false);
    }
}

function setLoadingState(buttonId, loading) {
    const button = document.getElementById(buttonId);
    const btnText = button.querySelector('.btn-text');
    const btnLoading = button.querySelector('.btn-loading');
    
    if (loading) {
        button.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'flex';
    } else {
        button.disabled = false;
        btnText.style.display = 'block';
        btnLoading.style.display = 'none';
    }
}

function showMessage(message, type) {
    clearMessages();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    const activeForm = document.querySelector('.auth-form.active');
    activeForm.parentNode.insertBefore(messageDiv, activeForm);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

function clearMessages() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(msg => msg.remove());
}

function resetForms() {
    document.querySelectorAll('.auth-form').forEach(form => {
        form.reset();
        form.querySelectorAll('.error').forEach(field => {
            field.classList.remove('error');
        });
        form.querySelectorAll('.error-message').forEach(msg => {
            msg.remove();
        });
    });
    
    // Reset password strength
    const strengthDiv = document.getElementById('passwordStrength');
    if (strengthDiv) {
        strengthDiv.innerHTML = '';
        strengthDiv.className = 'password-strength';
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentNode.querySelector('.password-toggle');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function showForgotPassword() {
    switchTab('forgot');
}

function showTerms() {
    document.getElementById('termsModal').style.display = 'flex';
}

function showPrivacy() {
    document.getElementById('privacyModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function socialLogin(provider) {
    showMessage(`${provider} login is not yet implemented`, 'info');
    // In a real implementation, this would redirect to OAuth provider
}

function checkLoginStatus() {
    // Check if user is already logged in
    fetch('../includes/check-session.php')
        .then(response => response.json())
        .then(result => {
            if (result.loggedIn) {
                window.location.href = 'dashboard.html';
            }
        })
        .catch(error => {
            console.log('Session check failed:', error);
        });
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});