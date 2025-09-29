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
    
    public function login($email, $password) {
        try {
            // Validate input
            $email = $this->sanitizeInput($email);
            
            if (!$this->validateEmail($email)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, user_type, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check if user is verified
                if (!$user['is_verified']) {
                    return ['success' => false, 'message' => 'Please verify your email before logging in'];
                }
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['login_time'] = time();
                    
                    // Update last login
                    $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    return ['success' => true, 'message' => 'Login successful', 'user' => $user];
                } else {
                    return ['success' => false, 'message' => 'Invalid password'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found'];
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
    
    public function register($firstName, $lastName, $email, $password, $userType, $studentId = '') {
        try {
            // Validate input
            $firstName = $this->sanitizeInput($firstName);
            $lastName = $this->sanitizeInput($lastName);
            $email = $this->sanitizeInput($email);
            $studentId = $this->sanitizeInput($studentId);
            
            if (!$this->validateEmail($email)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Check if student ID already exists (if provided)
            if (!empty($studentId)) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE student_id = ?");
                $stmt->execute([$studentId]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => 'Student/Staff ID already registered'];
                }
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate verification token
            $verificationToken = $this->generateSecureToken();
            
            // Insert new user
            $stmt = $this->db->prepare("
                INSERT INTO users (first_name, last_name, email, password, user_type, student_id, verification_token, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $userType, $studentId, $verificationToken]);
            
            $userId = $this->db->lastInsertId();
            
            // Send verification email
            $verificationLink = SITE_URL . "/includes/verify-email.php?token=" . $verificationToken;
            $emailSent = $this->sendVerificationEmail($email, $firstName . ' ' . $lastName, $verificationLink, 'registration');
            
            if ($emailSent) {
                return ['success' => true, 'message' => 'Registration successful! Please check your email to verify your account.'];
            } else {
                return ['success' => false, 'message' => 'Registration successful but failed to send verification email'];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration. Please try again.'];
        }
    }
    
    public function forgotPassword($email) {
        try {
            $email = $this->sanitizeInput($email);
            
            if (!$this->validateEmail($email)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, first_name, last_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $resetToken = $this->generateSecureToken();
                
                // Store reset token
                $updateStmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
                $updateStmt->execute([$resetToken, $user['id']]);
                
                // Send reset email
                $resetLink = SITE_URL . "/pages/reset-password.php?token=" . $resetToken;
                $emailSent = $this->sendPasswordResetEmail($email, $user['first_name'] . ' ' . $user['last_name'], $resetLink);
                
                if ($emailSent) {
                    return ['success' => true, 'message' => 'Password reset link sent to your email'];
                } else {
                    return ['success' => false, 'message' => 'Failed to send reset email'];
                }
            } else {
                return ['success' => false, 'message' => 'Email not found'];
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred. Please try again.'];
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            // Check if token is valid
            $stmt = $this->db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password and clear reset token
                $updateStmt = $this->db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $updateStmt->execute([$hashedPassword, $user['id']]);
                
                return ['success' => true, 'message' => 'Password reset successful'];
            } else {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred. Please try again.'];
        }
    }
    
    public function verifyEmail($token) {
        try {
            $stmt = $this->db->prepare("SELECT id, first_name, last_name, email FROM users WHERE verification_token = ? AND is_verified = 0");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Mark user as verified
                $updateStmt = $this->db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                return ['success' => true, 'message' => 'Email verified successfully', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid or already verified token'];
            }
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during verification'];
        }
    }
    
    private function sendVerificationEmail($email, $name, $verificationLink, $type = 'login') {
        $subject = $type === 'registration' ? 
            "Campus IT Support - Email Verification" : 
            "Campus IT Support - Login Verification";
            
        $message = $type === 'registration' ? "
        <html>
        <head>
            <title>Email Verification</title>
        </head>
        <body>
            <h2>Welcome to Campus IT Support, {$name}!</h2>
            <p>Thank you for registering. Please click the link below to verify your email address:</p>
            <a href='{$verificationLink}' style='background-color: #1e3a8a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 0;'>Verify Email</a>
            <p>This link will expire in 24 hours.</p>
            <p>If you didn't create this account, please ignore this email.</p>
            <hr>
            <p><small>© 2025 Chinhoyi University of Technology - Campus IT Support System</small></p>
        </body>
        </html>
        " : "
        <html>
        <head>
            <title>Login Verification</title>
        </head>
        <body>
            <h2>Welcome to Campus IT Support, {$name}!</h2>
            <p>Click the link below to complete your login:</p>
            <a href='{$verificationLink}' style='background-color: #1e3a8a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 0;'>Verify Login</a>
            <p>This link will expire in " . (TOKEN_EXPIRY / 60) . " minutes.</p>
            <p>If you didn't request this login, please ignore this email.</p>
            <hr>
            <p><small>© 2025 Chinhoyi University of Technology - Campus IT Support System</small></p>
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
    
    private function sendPasswordResetEmail($email, $name, $resetLink) {
        $subject = "Campus IT Support - Password Reset";
        $message = "
        <html>
        <head>
            <title>Password Reset</title>
        </head>
        <body>
            <h2>Password Reset Request</h2>
            <p>Hello {$name},</p>
            <p>You requested to reset your password. Click the link below to reset it:</p>
            <a href='{$resetLink}' style='background-color: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 0;'>Reset Password</a>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this reset, please ignore this email.</p>
            <hr>
            <p><small>© 2025 Chinhoyi University of Technology - Campus IT Support System</small></p>
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
