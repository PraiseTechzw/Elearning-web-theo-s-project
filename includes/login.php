<?php
/**
 * Login Handler
 * Chinhoyi University of Technology - Campus IT Support System
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$remember = $input['remember'] ?? false;

// Validate required fields
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Initialize authentication
$auth = new Auth();

// Attempt login
$result = $auth->login($email, $password);

// Handle remember me functionality
if ($result['success'] && $remember) {
    // Set remember me cookie (30 days)
    $token = bin2hex(random_bytes(32));
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    
    // Store token in database
    $db = new Database();
    $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    $stmt->execute([$token, $_SESSION['user_id']]);
}

// Return JSON response
if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($result);
?>
