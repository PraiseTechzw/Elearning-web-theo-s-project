<?php
/**
 * Admin Tickets Management
 * Chinhoyi University of Technology - Campus IT Support System
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Database.php';

// Check if user is admin
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
if ($user['user_type'] !== 'admin') {
    header('Location: ../pages/dashboard.html');
    exit;
}

$db = new Database();

// Handle ticket actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ticketId = $_POST['ticket_id'] ?? '';
    
    switch ($action) {
        case 'assign':
            $assignedTo = $_POST['assigned_to'] ?? '';
            if ($assignedTo) {
                $stmt = $db->prepare("UPDATE support_tickets SET assigned_to = ? WHERE id = ?");
                $stmt->execute([$assignedTo, $ticketId]);
                $message = 'Ticket assigned successfully';
            }
            break;
            
        case 'update_status':
            $status = $_POST['status'] ?? '';
            $resolution = $_POST['resolution'] ?? '';
            
            $stmt = $db->prepare("UPDATE support_tickets SET status = ?, resolution = ? WHERE id = ?");
            $stmt->execute([$status, $resolution, $ticketId]);
            
            if ($status === 'resolved') {
                $stmt = $db->prepare("UPDATE support_tickets SET resolved_at = NOW() WHERE id = ?");
                $stmt->execute([$ticketId]);
            }
            
            $message = 'Ticket status updated successfully';
            break;
            
        case 'delete':
            $stmt = $db->prepare("DELETE FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticketId]);
            $message = 'Ticket deleted successfully';
            break;
    }
}

// Get tickets with pagination and filters
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$category = $_GET['category'] ?? '';

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(st.subject LIKE ? OR st.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
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

if (!empty($category)) {
    $whereConditions[] = "st.category = ?";
    $params[] = $category;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countSql = "
    SELECT COUNT(*) as total 
    FROM support_tickets st 
    JOIN users u ON st.user_id = u.id 
    $whereClause
";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalTickets = $stmt->fetch()['total'];
$totalPages = ceil($totalTickets / $limit);

// Get tickets
$sql = "
    SELECT st.*, u.first_name, u.last_name, u.email, u.user_type,
           a.first_name as assigned_first_name, a.last_name as assigned_last_name
    FROM support_tickets st 
    JOIN users u ON st.user_id = u.id 
    LEFT JOIN users a ON st.assigned_to = a.id
    $whereClause
    ORDER BY st.created_at DESC 
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// Get staff for assignment
$stmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE user_type IN ('staff', 'admin') ORDER BY first_name");
$stmt->execute();
$staff = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Management - Admin | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
        }
        
        .admin-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
        }
        
        .admin-nav {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .admin-nav a {
            color: #64748b;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #3b82f6;
            color: white;
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
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .tickets-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8fafc;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table-header h3 {
            margin: 0;
            color: #1e3a8a;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
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
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .category-network { background: #e0e7ff; color: #3730a3; }
        .category-hardware { background: #f0fdf4; color: #166534; }
        .category-software { background: #fef3c7; color: #92400e; }
        .category-account { background: #fecaca; color: #991b1b; }
        .category-other { background: #f3f4f6; color: #374151; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
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
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            margin: 50px auto;
            padding: 30px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #1e3a8a;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-ticket-alt"></i> Tickets Management</h1>
            <p>Manage all support tickets in the system</p>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="tickets.php" class="active"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="../pages/dashboard.html"><i class="fas fa-home"></i> User Dashboard</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="filters">
            <form method="GET">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Subject, description, or user">
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
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="network" <?php echo $category === 'network' ? 'selected' : ''; ?>>Network</option>
                        <option value="hardware" <?php echo $category === 'hardware' ? 'selected' : ''; ?>>Hardware</option>
                        <option value="software" <?php echo $category === 'software' ? 'selected' : ''; ?>>Software</option>
                        <option value="account" <?php echo $category === 'account' ? 'selected' : ''; ?>>Account</option>
                        <option value="other" <?php echo $category === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="tickets.php" class="btn btn-primary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <div class="tickets-table">
            <div class="table-header">
                <h3>Tickets (<?php echo number_format($totalTickets); ?> total)</h3>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #64748b;">
                                No tickets found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                                </td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars($ticket['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge category-<?php echo $ticket['category']; ?>">
                                        <?php echo ucfirst($ticket['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge priority-<?php echo $ticket['priority']; ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                        <?php echo ucfirst($ticket['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ticket['assigned_first_name']): ?>
                                        <?php echo htmlspecialchars($ticket['assigned_first_name'] . ' ' . $ticket['assigned_last_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #64748b;">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-primary btn-sm" onclick="viewTicket(<?php echo $ticket['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="assignTicket(<?php echo $ticket['id']; ?>)">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="updateStatus(<?php echo $ticket['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteTicket(<?php echo $ticket['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&category=<?php echo urlencode($category); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&category=<?php echo urlencode($category); ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&category=<?php echo urlencode($category); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Assignment Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assign Ticket</h3>
                <button class="close-modal" onclick="closeModal('assignModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="ticket_id" id="assign_ticket_id">
                
                <div class="form-group">
                    <label for="assigned_to">Assign to</label>
                    <select id="assigned_to" name="assigned_to" required>
                        <option value="">Select staff member</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('assignModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Status</h3>
                <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="ticket_id" id="status_ticket_id">
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="resolution">Resolution (optional)</label>
                    <textarea id="resolution" name="resolution" placeholder="Add resolution details..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function assignTicket(ticketId) {
            document.getElementById('assign_ticket_id').value = ticketId;
            document.getElementById('assignModal').style.display = 'block';
        }
        
        function updateStatus(ticketId) {
            document.getElementById('status_ticket_id').value = ticketId;
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function viewTicket(ticketId) {
            window.open('ticket-details.php?id=' + ticketId, '_blank');
        }
        
        function deleteTicket(ticketId) {
            if (confirm('Are you sure you want to delete this ticket?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="ticket_id" value="${ticketId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
