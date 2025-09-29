<?php
/**
 * Admin Logout
 * Chinhoyi University of Technology - Campus IT Support System
 */

require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;
?>