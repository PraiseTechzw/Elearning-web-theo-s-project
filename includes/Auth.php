<?php
/**
 * Authentication System
 * Praisetech - Campus IT Support System
 * Secure user authentication and session management
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->startSession();
    }
    
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($name, $email) {
        try {
            // Validate input
            $name = $this->sanitizeInput($name);
            $email = $this->sanitizeInput($email);
            
            if (!$this->validateEmail($email)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE name = ? AND email = ?");
            $stmt->execute([$name, $email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate secure token
                $token = $this->generateSecureToken();
                
                // Store token in database
                $updateStmt = $this->db->prepare("UPDATE users SET token = ?, token_expires = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id = ?");
                $updateStmt->execute([$token, TOKEN_EXPIRY, $user['id']]);
                
                // Send verification email
                $verificationLink = SITE_URL . "/includes/verify.php?token=" . $token;
                $emailSent = $this->sendVerificationEmail($email, $name, $verificationLink);
                
                if ($emailSent) {
                    return ['success' => true, 'message' => 'Verification link sent to your email'];
                } else {
                    return ['success' => false, 'message' => 'Failed to send verification email'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred. Please try again.'];
        }
    }
    
    public function verifyToken($token) {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE token = ? AND token_expires > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                // Invalidate token
                $updateStmt = $this->db->prepare("UPDATE users SET token = NULL, token_expires = NULL WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid or expired token'];
            }
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during verification'];
        }
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Check session timeout
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email']
            ];
        }
        return null;
    }
    
    private function generateSecureToken() {
        return bin2hex(random_bytes(32));
    }
    
    private function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function sendVerificationEmail($email, $name, $verificationLink) {
        $subject = "Campus IT Support - Login Verification";
        $message = "
        <html>
        <head>
            <title>Login Verification</title>
        </head>
        <body>
            <h2>Welcome to Campus IT Support, {$name}!</h2>
            <p>Click the link below to complete your login:</p>
            <a href='{$verificationLink}' style='background-color: #003366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Login</a>
            <p>This link will expire in " . (TOKEN_EXPIRY / 60) . " minutes.</p>
            <p>If you didn't request this login, please ignore this email.</p>
            <hr>
            <p><small>© 2025 Praisetech - Campus IT Support System</small></p>
        </body>
        </html>
        ";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
            'Reply-To: ' . FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($email, $subject, $message, implode("\r\n", $headers));
    }
}
?>
