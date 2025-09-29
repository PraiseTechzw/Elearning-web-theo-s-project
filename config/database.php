<?php
/**
 * Database Configuration
 * Chinhoyi University of Technology - Campus IT Support System
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'campus_db');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@cut.ac.zw');
define('FROM_NAME', 'CUT IT Support');

// Application settings
define('SITE_URL', 'http://localhost/campus-support');
define('ADMIN_EMAIL', 'admin@cut.ac.zw');
define('TOKEN_EXPIRY', 3600); // 1 hour in seconds

// Security settings
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here');
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
