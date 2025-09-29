<?php
/**
 * Token Verification Handler
 * Praisetech - Campus IT Support System
 * Verify login tokens and create sessions
 */

require_once __DIR__ . '/Auth.php';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invalid Link - Campus IT Support</title>
        <link rel="stylesheet" href="../assets/css/styles.css">
    </head>
    <body>
        <div class="error-container">
            <h2>Invalid Verification Link</h2>
            <p>The verification link is invalid or missing.</p>
            <a href="../pages/login.html" class="btn">Return to Login</a>
        </div>
    </body>
    </html>
    ');
}

// Initialize authentication
$auth = new Auth();

// Verify token
$result = $auth->verifyToken($token);

if ($result['success']) {
    // Redirect to dashboard
    header('Location: ../pages/dashboard.html');
    exit;
} else {
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verification Failed - Campus IT Support</title>
        <link rel="stylesheet" href="../assets/css/styles.css">
    </head>
    <body>
        <div class="error-container">
            <h2>Verification Failed</h2>
            <p>' . htmlspecialchars($result['message']) . '</p>
            <a href="../pages/login.html" class="btn">Return to Login</a>
        </div>
    </body>
    </html>
    ');
}
?>