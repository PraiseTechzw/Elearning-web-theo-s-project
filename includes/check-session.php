<?php
/**
 * Session Check Handler
 * Chinhoyi University of Technology - Campus IT Support System
 * Check if user is logged in and session is valid
 */

require_once __DIR__ . '/Auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize authentication
$auth = new Auth();

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();
$user = null;

if ($isLoggedIn) {
    $user = $auth->getCurrentUser();
}

echo json_encode([
    'loggedIn' => $isLoggedIn,
    'user' => $user
]);
?>
