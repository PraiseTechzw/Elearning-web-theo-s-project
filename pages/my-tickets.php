<?php
/**
 * My Tickets Page
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

// Get tickets with pagination and filters
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';

$whereConditions = ["st.user_id = ?"];
$params = [$user['id']];

if (!empty($search)) {
    $whereConditions[] = "(st.subject LIKE ? OR st.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $whereConditions[] = "st.status = ?";
    $params[] = $status;
}

if (!empty($priority)) {
    $whereConditions[] = "st.priority = ?";
    $params[] = $priority;
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM support_tickets st $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalTickets = $stmt->fetch()['total'];
$totalPages = ceil($totalTickets / $limit);

// Get tickets
$sql = "
    SELECT st.*, a.first_name as assigned_first_name, a.last_name as assigned_last_name
    FROM support_tickets st 
    LEFT JOIN users a ON st.assigned_to = a.id
    $whereClause
    ORDER BY st.created_at DESC 
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - Campus IT Support | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .tickets-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .tickets-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
        }
        
        .tickets-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .filters form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input, .form-group select {
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .form-group input:focus, .form-group select:focus {
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
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .tickets-grid {
            display: grid;
            gap: 20px;
        }
        
        .ticket-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .ticket-card:hover {
            transform: translateY(-5px);
        }
        
        .ticket-header {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .ticket-info h3 {
            margin: 0 0 5px 0;
            color: #1e3a8a;
            font-size: 1.2rem;
        }
        
        .ticket-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .ticket-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
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
        
        .ticket-body {
            padding: 20px;
        }
        
        .ticket-description {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .ticket-footer {
            padding: 15px 20px;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ticket-dates {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .ticket-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-outline {
            background: transparent;
            color: #3b82f6;
            border: 2px solid #3b82f6;
        }
        
        .btn-outline:hover {
            background: #3b82f6;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            color: #374151;
        }
        
        .pagination a:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .no-tickets {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .no-tickets i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #d1d5db;
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
            .filters form {
                grid-template-columns: 1fr;
            }
            
            .ticket-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .ticket-meta {
                justify-content: flex-start;
            }
            
            .ticket-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="tickets-container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="tickets-header">
            <h1><i class="fas fa-ticket-alt"></i> My Support Tickets</h1>
            <p>Track and manage all your support requests</p>
        </div>
        
        <div class="filters">
            <form method="GET">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search tickets...">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="open" <?php echo $status === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="urgent" <?php echo $priority === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="my-tickets.php" class="btn btn-primary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Your Tickets (<?php echo number_format($totalTickets); ?> total)</h2>
            <a href="create-ticket.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Create New Ticket
            </a>
        </div>
        
        <?php if (empty($tickets)): ?>
            <div class="no-tickets">
                <i class="fas fa-ticket-alt"></i>
                <h3>No tickets found</h3>
                <p>You haven't created any support tickets yet, or no tickets match your current filters.</p>
                <a href="create-ticket.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Create Your First Ticket
                </a>
            </div>
        <?php else: ?>
            <div class="tickets-grid">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="ticket-header">
                            <div class="ticket-info">
                                <h3><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                                <p>Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?></p>
                            </div>
                            <div class="ticket-meta">
                                <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                    <?php echo ucfirst($ticket['status']); ?>
                                </span>
                                <span class="status-badge priority-<?php echo $ticket['priority']; ?>">
                                    <?php echo ucfirst($ticket['priority']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="ticket-body">
                            <div class="ticket-description">
                                <?php echo nl2br(htmlspecialchars(substr($ticket['description'], 0, 200))); ?>
                                <?php if (strlen($ticket['description']) > 200): ?>
                                    <span style="color: #64748b;">...</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($ticket['assigned_first_name']): ?>
                                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                                    <i class="fas fa-user"></i> Assigned to: 
                                    <?php echo htmlspecialchars($ticket['assigned_first_name'] . ' ' . $ticket['assigned_last_name']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ticket-footer">
                            <div class="ticket-dates">
                                <div>Created: <?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></div>
                                <?php if ($ticket['resolved_at']): ?>
                                    <div>Resolved: <?php echo date('M j, Y H:i', strtotime($ticket['resolved_at'])); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="ticket-actions">
                                <a href="ticket-details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if ($ticket['status'] === 'open' || $ticket['status'] === 'in_progress'): ?>
                                    <a href="update-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Update
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
