<?php
/**
 * Create Support Ticket
 * Chinhoyi University of Technology - Campus IT Support System
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Database.php';

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.html');
    exit;
}

$user = $auth->getCurrentUser();
$db = new Database();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $category = $_POST['category'] ?? 'other';
    
    if (empty($subject) || empty($description)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            // Generate ticket number
            $ticketNumber = 'TKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert ticket
            $stmt = $db->prepare("
                INSERT INTO support_tickets (user_id, ticket_number, subject, description, priority, category, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user['id'], $ticketNumber, $subject, $description, $priority, $category]);
            
            $ticketId = $db->lastInsertId();
            
            // Log activity
            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                VALUES (?, 'ticket_created', ?, ?, ?)
            ");
            $stmt->execute([
                $user['id'], 
                "Created ticket #$ticketNumber: $subject",
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            // Create notification
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type) 
                VALUES (?, ?, ?, 'success')
            ");
            $stmt->execute([
                $user['id'],
                'Ticket Created Successfully',
                "Your support ticket #$ticketNumber has been created and will be reviewed soon."
            ]);
            
            $message = "Ticket created successfully! Ticket number: $ticketNumber";
            
            // Clear form data
            $subject = $description = '';
            
        } catch (Exception $e) {
            error_log("Ticket creation error: " . $e->getMessage());
            $error = 'An error occurred while creating the ticket. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket - Campus IT Support | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
        }
        
        .ticket-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
        }
        
        .ticket-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e3a8a);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: #f8fafc;
            color: #64748b;
            border: 2px solid #e5e7eb;
        }
        
        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #d1d5db;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .required {
            color: #ef4444;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .back-link {
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #3b82f6;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="ticket-header">
            <h1><i class="fas fa-plus-circle"></i> Create Support Ticket</h1>
            <p>Submit a new support request and we'll help you resolve it quickly</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="ticket-form">
            <div class="form-group">
                <label for="subject">
                    Subject <span class="required">*</span>
                </label>
                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" 
                       placeholder="Brief description of your issue" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category">
                        Category <span class="required">*</span>
                    </label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="network" <?php echo ($category ?? '') === 'network' ? 'selected' : ''; ?>>Network Issues</option>
                        <option value="hardware" <?php echo ($category ?? '') === 'hardware' ? 'selected' : ''; ?>>Hardware Problems</option>
                        <option value="software" <?php echo ($category ?? '') === 'software' ? 'selected' : ''; ?>>Software Issues</option>
                        <option value="account" <?php echo ($category ?? '') === 'account' ? 'selected' : ''; ?>>Account Problems</option>
                        <option value="other" <?php echo ($category ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority">
                        Priority <span class="required">*</span>
                    </label>
                    <select id="priority" name="priority" required>
                        <option value="low" <?php echo ($priority ?? '') === 'low' ? 'selected' : ''; ?>>Low - General inquiry</option>
                        <option value="medium" <?php echo ($priority ?? '') === 'medium' ? 'selected' : ''; ?>>Medium - Standard issue</option>
                        <option value="high" <?php echo ($priority ?? '') === 'high' ? 'selected' : ''; ?>>High - Urgent issue</option>
                        <option value="urgent" <?php echo ($priority ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent - Critical issue</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">
                    Description <span class="required">*</span>
                </label>
                <textarea id="description" name="description" 
                          placeholder="Please provide detailed information about your issue. Include steps to reproduce, error messages, and any relevant details." 
                          required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Ticket
                </button>
            </div>
        </form>
    </div>

    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('description');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Form validation
        const form = document.querySelector('.ticket-form');
        form.addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const description = document.getElementById('description').value.trim();
            const category = document.getElementById('category').value;
            const priority = document.getElementById('priority').value;
            
            if (!subject || !description || !category || !priority) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            if (subject.length < 10) {
                e.preventDefault();
                alert('Subject must be at least 10 characters long');
                return;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                alert('Description must be at least 20 characters long');
                return;
            }
        });
    </script>
</body>
</html>
