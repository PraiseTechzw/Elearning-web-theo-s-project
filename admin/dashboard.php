<?php
/**
 * Admin Dashboard
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

// Get statistics
$stats = [];

// Total users
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['total'];

// Verified users
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE is_verified = 1");
$stmt->execute();
$stats['verified_users'] = $stmt->fetch()['total'];

// Total tickets
$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets");
$stmt->execute();
$stats['total_tickets'] = $stmt->fetch()['total'];

// Open tickets
$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE status = 'open'");
$stmt->execute();
$stats['open_tickets'] = $stmt->fetch()['total'];

// Recent tickets
$stmt = $db->prepare("
    SELECT st.*, u.first_name, u.last_name, u.email 
    FROM support_tickets st 
    JOIN users u ON st.user_id = u.id 
    ORDER BY st.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_tickets = $stmt->fetchAll();

// Recent users
$stmt = $db->prepare("
    SELECT first_name, last_name, email, user_type, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_users = $stmt->fetchAll();

// Activity logs
$stmt = $db->prepare("
    SELECT al.*, u.first_name, u.last_name 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 20
");
$stmt->execute();
$activity_logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Campus IT Support | Chinhoyi University of Technology</title>
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
        
        .admin-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #3b82f6;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }
        
        .stat-card .icon {
            float: right;
            font-size: 2rem;
            color: #3b82f6;
            opacity: 0.7;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: #f8fafc;
            padding: 20px;
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
        
        .ticket-item, .user-item, .activity-item {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ticket-item:last-child, .user-item:last-child, .activity-item:last-child {
            border-bottom: none;
        }
        
        .ticket-info, .user-info, .activity-info {
            flex: 1;
        }
        
        .ticket-info h4, .user-info h4, .activity-info h4 {
            margin: 0 0 5px 0;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .ticket-info p, .user-info p, .activity-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
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
        
        .user-type {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-student { background: #e0e7ff; color: #3730a3; }
        .type-staff { background: #f0fdf4; color: #166534; }
        .type-faculty { background: #fef3c7; color: #92400e; }
        .type-admin { background: #fecaca; color: #991b1b; }
        
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
        
        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #b91c1c;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-nav ul {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! Manage your campus IT support system.</p>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="../pages/dashboard.html"><i class="fas fa-home"></i> User Dashboard</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <h3>Total Users</h3>
                <p class="number"><?php echo number_format($stats['total_users']); ?></p>
            </div>
            
            <div class="stat-card">
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <h3>Verified Users</h3>
                <p class="number"><?php echo number_format($stats['verified_users']); ?></p>
            </div>
            
            <div class="stat-card">
                <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                <h3>Total Tickets</h3>
                <p class="number"><?php echo number_format($stats['total_tickets']); ?></p>
            </div>
            
            <div class="stat-card">
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                <h3>Open Tickets</h3>
                <p class="number"><?php echo number_format($stats['open_tickets']); ?></p>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-ticket-alt"></i> Recent Tickets</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_tickets)): ?>
                        <p style="text-align: center; color: #64748b; padding: 20px;">No tickets found</p>
                    <?php else: ?>
                        <?php foreach ($recent_tickets as $ticket): ?>
                            <div class="ticket-item">
                                <div class="ticket-info">
                                    <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                    <p><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?> • <?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></p>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $ticket['status']; ?>"><?php echo ucfirst($ticket['status']); ?></span>
                                    <span class="status-badge priority-<?php echo $ticket['priority']; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-plus"></i> Recent Users</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_users)): ?>
                        <p style="text-align: center; color: #64748b; padding: 20px;">No users found</p>
                    <?php else: ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($user['email']); ?> • <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                                <span class="user-type type-<?php echo $user['user_type']; ?>"><?php echo ucfirst($user['user_type']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            <div class="card-body">
                <?php if (empty($activity_logs)): ?>
                    <p style="text-align: center; color: #64748b; padding: 20px;">No activity logs found</p>
                <?php else: ?>
                    <?php foreach ($activity_logs as $log): ?>
                        <div class="activity-item">
                            <div class="activity-info">
                                <h4><?php echo htmlspecialchars($log['action']); ?></h4>
                                <p><?php echo htmlspecialchars($log['description']); ?></p>
                                <small><?php echo date('M j, Y H:i', strtotime($log['created_at'])); ?></small>
                            </div>
                            <div>
                                <?php if ($log['first_name']): ?>
                                    <span class="user-type type-staff"><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></span>
                                <?php else: ?>
                                    <span class="user-type type-staff">System</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
