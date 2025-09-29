<?php
/**
 * Admin Login Page
 * Chinhoyi University of Technology - Campus IT Support System
 */

require_once __DIR__ . '/../includes/Auth.php';

// Check if already logged in
$auth = new Auth();
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    if ($user['user_type'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: ../pages/dashboard.php');
    }
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            $user = $auth->getCurrentUser();
            if ($user['user_type'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: ../pages/dashboard.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Campus IT Support | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-login {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
        }
        
        .admin-login .auth-container {
            border: 2px solid #dc2626;
        }
        
        .admin-login .auth-header h1 {
            color: #dc2626;
        }
        
        .admin-login .auth-btn {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }
        
        .admin-login .auth-btn:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
        }
        
        .admin-badge {
            background: #dc2626;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }
    </style>
</head>
<body class="admin-login">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <img src="../assets/images/CUT_LOG-removebg-preview.png" alt="CUT Logo" class="auth-logo">
                <h1>Admin Login<span class="admin-badge">Admin</span></h1>
                <p class="brand-subtitle">Chinhoyi University of Technology</p>
            </div>
            
            <?php if ($error): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                           placeholder="Enter your admin email">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login as Admin
                </button>
            </form>
            
            <div class="auth-footer">
                <a href="../pages/login.html" class="back-link">
                    <i class="fas fa-arrow-left"></i> User Login
                </a>
                <a href="../pages/index.html" class="back-link">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>