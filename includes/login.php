<?php
/**
 * Login Handler
 * Praisetech - Campus IT Support System
 * Secure login processing
 */

require_once __DIR__ . '/Auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

// Validate required fields
if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit;
}

// Initialize authentication
$auth = new Auth();

// Attempt login
$result = $auth->login($name, $email);

// Return JSON response
if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($result);
?>
