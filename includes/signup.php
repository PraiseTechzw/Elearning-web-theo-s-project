<?php
/**
 * User Registration Handler
 * Chinhoyi University of Technology - Campus IT Support System
 * Secure user registration processing
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

// Extract and validate required fields
$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$userType = $input['user_type'] ?? '';
$studentId = trim($input['student_id'] ?? '');

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($userType)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password strength
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit;
}

// Validate user type
$allowedUserTypes = ['student', 'staff', 'faculty'];
if (!in_array($userType, $allowedUserTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    exit;
}

// Initialize authentication
$auth = new Auth();

// Attempt registration
$result = $auth->register($firstName, $lastName, $email, $password, $userType, $studentId);

// Return JSON response
if ($result['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($result);
?>
