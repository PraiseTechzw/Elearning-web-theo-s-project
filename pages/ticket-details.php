<?php
/**
 * Ticket Details Page
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

$ticketId = $_GET['id'] ?? '';

if (empty($ticketId) || !is_numeric($ticketId)) {
    header('Location: my-tickets.php');
    exit;
}

// Get ticket details
$stmt = $db->prepare("
    SELECT st.*, u.first_name, u.last_name, u.email, u.user_type,
           a.first_name as assigned_first_name, a.last_name as assigned_last_name, a.email as assigned_email
    FROM support_tickets st 
    JOIN users u ON st.user_id = u.id 
    LEFT JOIN users a ON st.assigned_to = a.id
    WHERE st.id = ?
");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header('Location: my-tickets.php');
    exit;
}

// Check if user owns this ticket or is admin
if ($ticket['user_id'] != $user['id'] && $user['user_type'] !== 'admin') {
    header('Location: my-tickets.php');
    exit;
}

// Get ticket comments/updates
$stmt = $db->prepare("
    SELECT tc.*, u.first_name, u.last_name, u.user_type
    FROM ticket_comments tc
    JOIN users u ON tc.user_id = u.id
    WHERE tc.ticket_id = ?
    ORDER BY tc.created_at ASC
");
$stmt->execute([$ticketId]);
$comments = $stmt->fetchAll();

$message = '';
$error = '';

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($comment)) {
        $error = 'Please enter a comment';
    } else {
        try {
            // Insert comment
            $stmt = $db->prepare("
                INSERT INTO ticket_comments (ticket_id, user_id, comment, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$ticketId, $user['id'], $comment]);
            
            // Update ticket updated_at
            $stmt = $db->prepare("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$ticketId]);
            
            $message = 'Comment added successfully';
            
            // Refresh comments
            $stmt = $db->prepare("
                SELECT tc.*, u.first_name, u.last_name, u.user_type
                FROM ticket_comments tc
                JOIN users u ON tc.user_id = u.id
                WHERE tc.ticket_id = ?
                ORDER BY tc.created_at ASC
            ");
            $stmt->execute([$ticketId]);
            $comments = $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Comment error: " . $e->getMessage());
            $error = 'An error occurred while adding the comment';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?> - Campus IT Support | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .ticket-details-container {
            max-width: 1000px;
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
            font-size: 2rem;
        }
        
        .ticket-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
        }
        
        .ticket-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .ticket-main {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .ticket-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .card-header h3 {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .ticket-description {
            color: #374151;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-open { background: #fef3c7; color: #92400e; }
        .status-in-progress { background: #dbeafe; color: #1e40af; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-closed { background: #f3f4f6; color: #374151; }
        
        .priority-high { background: #fee2e2; color: #991b1b; }
        .priority-medium { background: #fef3c7; color: #92400e; }
        .priority-low { background: #d1fae5; color: #065f46; }
        .priority-urgent { background: #fecaca; color: #991b1b; }
        
        .category-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .category-network { background: #e0e7ff; color: #3730a3; }
        .category-hardware { background: #f0fdf4; color: #166534; }
        .category-software { background: #fef3c7; color: #92400e; }
        .category-account { background: #fecaca; color: #991b1b; }
        .category-other { background: #f3f4f6; color: #374151; }
        
        .comments-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .comment-form {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e3a8a;
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
        
        .comment-item {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .comment-item:last-child {
            border-bottom: none;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comment-author {
            font-weight: 600;
            color: #374151;
        }
        
        .comment-date {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .comment-content {
            color: #374151;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .user-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }
        
        .badge-admin { background: #fecaca; color: #991b1b; }
        .badge-staff { background: #f0fdf4; color: #166534; }
        .badge-user { background: #e0e7ff; color: #3730a3; }
        
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
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
        }
        
        .info-value {
            color: #64748b;
        }
        
        @media (max-width: 768px) {
            .ticket-content {
                grid-template-columns: 1fr;
            }
            
            .ticket-meta {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-details-container">
        <a href="my-tickets.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to My Tickets
        </a>
        
        <div class="ticket-header">
            <h1>Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?></h1>
            <p><?php echo htmlspecialchars($ticket['subject']); ?></p>
            
            <div class="ticket-meta">
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span class="status-badge status-<?php echo $ticket['status']; ?>">
                        <?php echo ucfirst($ticket['status']); ?>
                    </span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-exclamation-circle"></i>
                    <span class="status-badge priority-<?php echo $ticket['priority']; ?>">
                        <?php echo ucfirst($ticket['priority']); ?>
                    </span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-folder"></i>
                    <span class="category-badge category-<?php echo $ticket['category']; ?>">
                        <?php echo ucfirst($ticket['category']); ?>
                    </span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></span>
                </div>
            </div>
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
        
        <div class="ticket-content">
            <div class="ticket-main">
                <div class="card-header">
                    <h3><i class="fas fa-file-alt"></i> Description</h3>
                </div>
                <div class="card-body">
                    <div class="ticket-description"><?php echo htmlspecialchars($ticket['description']); ?></div>
                </div>
                
                <?php if ($ticket['resolution']): ?>
                    <div class="card-header">
                        <h3><i class="fas fa-check-circle"></i> Resolution</h3>
                    </div>
                    <div class="card-body">
                        <div class="ticket-description"><?php echo htmlspecialchars($ticket['resolution']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ticket-sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Ticket Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="info-label">Created by:</span>
                            <span class="info-value"><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($ticket['email']); ?></span>
                        </div>
                        <?php if ($ticket['assigned_first_name']): ?>
                            <div class="info-item">
                                <span class="info-label">Assigned to:</span>
                                <span class="info-value"><?php echo htmlspecialchars($ticket['assigned_first_name'] . ' ' . $ticket['assigned_last_name']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <span class="info-label">Last updated:</span>
                            <span class="info-value"><?php echo date('M j, Y H:i', strtotime($ticket['updated_at'])); ?></span>
                        </div>
                        <?php if ($ticket['resolved_at']): ?>
                            <div class="info-item">
                                <span class="info-label">Resolved:</span>
                                <span class="info-value"><?php echo date('M j, Y H:i', strtotime($ticket['resolved_at'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cog"></i> Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="my-tickets.php" class="btn btn-secondary" style="width: 100%; margin-bottom: 10px;">
                            <i class="fas fa-list"></i> All Tickets
                        </a>
                        <a href="create-ticket.php" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-plus"></i> New Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="comments-section">
            <div class="card-header">
                <h3><i class="fas fa-comments"></i> Comments & Updates</h3>
            </div>
            
            <?php if (empty($comments)): ?>
                <div class="card-body">
                    <p style="text-align: center; color: #64748b; padding: 20px;">No comments yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-author">
                                <?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?>
                                <span class="user-badge badge-<?php echo $comment['user_type'] === 'admin' ? 'admin' : ($comment['user_type'] === 'staff' ? 'staff' : 'user'); ?>">
                                    <?php echo ucfirst($comment['user_type']); ?>
                                </span>
                            </div>
                            <div class="comment-date">
                                <?php echo date('M j, Y H:i', strtotime($comment['created_at'])); ?>
                            </div>
                        </div>
                        <div class="comment-content"><?php echo htmlspecialchars($comment['comment']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="comment-form">
                <form method="POST">
                    <input type="hidden" name="action" value="add_comment">
                    
                    <div class="form-group">
                        <label for="comment">Add a comment</label>
                        <textarea id="comment" name="comment" placeholder="Add your comment or update..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Add Comment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
