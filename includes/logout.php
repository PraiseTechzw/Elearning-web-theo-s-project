<?php
/**
 * Logout Handler
 * Praisetech - Campus IT Support System
 * Handle user logout
 */

require_once __DIR__ . '/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: ../pages/login.html');
exit;
?>
