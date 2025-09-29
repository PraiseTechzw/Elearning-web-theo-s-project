<?php
/**
 * Email Verification Handler
 * Chinhoyi University of Technology - Campus IT Support System
 * Verify user email addresses
 */

require_once __DIR__ . '/Auth.php';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: ../pages/login.html?error=invalid_token');
    exit;
}

// Initialize authentication
$auth = new Auth();

// Verify email
$result = $auth->verifyEmail($token);

if ($result['success']) {
    // Redirect to login with success message
    header('Location: ../pages/login.html?verified=1');
} else {
    // Redirect to login with error message
    header('Location: ../pages/login.html?error=' . urlencode($result['message']));
}

exit;
?>
