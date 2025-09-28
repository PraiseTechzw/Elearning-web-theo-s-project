<?php
/**
 * Admin Login
 * Praisetech - Campus IT Support System
 */

session_start();

// Simple admin login (in production, use proper authentication)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple check (replace with proper authentication)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Campus IT Support | Praisetech</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <img src="../assets/images/CUT_LOG-removebg-preview.png" alt="Campus Logo" class="login-logo">
                <h1>Admin Panel</h1>
                <p class="brand-subtitle">Praisetech - Campus IT Support</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user-shield"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required placeholder="Enter admin username">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required placeholder="Enter admin password">
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../pages/index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Main Site
                </a>
            </div>
        </div>
    </div>
</body>
</html>
