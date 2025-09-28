<?php
/**
 * Session Check
 * Praisetech - Campus IT Support System
 * Check if user is logged in and redirect if not
 */

require_once __DIR__ . '/Auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: login.html');
    exit;
}
?>
