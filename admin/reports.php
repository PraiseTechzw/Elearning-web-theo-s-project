<?php
/**
 * Admin Reports Page
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

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get statistics
$stats = [];

// Total users
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE created_at BETWEEN ? AND ?");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$stats['new_users'] = $stmt->fetch()['total'];

// Total tickets
$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE created_at BETWEEN ? AND ?");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$stats['total_tickets'] = $stmt->fetch()['total'];

// Resolved tickets
$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE status = 'resolved' AND created_at BETWEEN ? AND ?");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$stats['resolved_tickets'] = $stmt->fetch()['total'];

// Average resolution time
$stmt = $db->prepare("
    SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours 
    FROM support_tickets 
    WHERE status = 'resolved' AND resolved_at IS NOT NULL AND created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$avgResolution = $stmt->fetch()['avg_hours'];
$stats['avg_resolution_hours'] = $avgResolution ? round($avgResolution, 1) : 0;

// Tickets by status
$stmt = $db->prepare("
    SELECT status, COUNT(*) as count 
    FROM support_tickets 
    WHERE created_at BETWEEN ? AND ? 
    GROUP BY status
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$ticketsByStatus = [];
while ($row = $stmt->fetch()) {
    $ticketsByStatus[$row['status']] = $row['count'];
}

// Tickets by priority
$stmt = $db->prepare("
    SELECT priority, COUNT(*) as count 
    FROM support_tickets 
    WHERE created_at BETWEEN ? AND ? 
    GROUP BY priority
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$ticketsByPriority = [];
while ($row = $stmt->fetch()) {
    $ticketsByPriority[$row['priority']] = $row['count'];
}

// Tickets by category
$stmt = $db->prepare("
    SELECT category, COUNT(*) as count 
    FROM support_tickets 
    WHERE created_at BETWEEN ? AND ? 
    GROUP BY category
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$ticketsByCategory = [];
while ($row = $stmt->fetch()) {
    $ticketsByCategory[$row['category']] = $row['count'];
}

// Recent tickets
$stmt = $db->prepare("
    SELECT st.*, u.first_name, u.last_name 
    FROM support_tickets st 
    JOIN users u ON st.user_id = u.id 
    WHERE st.created_at BETWEEN ? AND ? 
    ORDER BY st.created_at DESC 
    LIMIT 10
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$recentTickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .form-group input {
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .form-group input:focus {
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
            text-align: center;
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
        
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .chart-card h3 {
            margin: 0 0 20px 0;
            color: #1e3a8a;
            font-size: 1.2rem;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .recent-tickets {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .recent-tickets h3 {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.2rem;
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .ticket-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ticket-item:last-child {
            border-bottom: none;
        }
        
        .ticket-info h4 {
            margin: 0 0 5px 0;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .ticket-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
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
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .filters form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
            <p>System performance and usage statistics</p>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="../pages/dashboard.html"><i class="fas fa-home"></i> User Dashboard</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="filters">
            <form method="GET">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>New Users</h3>
                <p class="number"><?php echo number_format($stats['new_users']); ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Tickets</h3>
                <p class="number"><?php echo number_format($stats['total_tickets']); ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Resolved Tickets</h3>
                <p class="number"><?php echo number_format($stats['resolved_tickets']); ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Avg Resolution Time</h3>
                <p class="number"><?php echo $stats['avg_resolution_hours']; ?>h</p>
            </div>
        </div>
        
        <div class="charts-grid">
            <div class="chart-card">
                <h3>Tickets by Status</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3>Tickets by Priority</h3>
                <div class="chart-container">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="chart-card">
            <h3>Tickets by Category</h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        
        <div class="recent-tickets">
            <h3>Recent Tickets</h3>
            <?php if (empty($recentTickets)): ?>
                <div style="padding: 40px; text-align: center; color: #64748b;">
                    No tickets found for the selected period
                </div>
            <?php else: ?>
                <?php foreach ($recentTickets as $ticket): ?>
                    <div class="ticket-item">
                        <div class="ticket-info">
                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <p><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?> • <?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></p>
                        </div>
                        <span class="status-badge status-<?php echo $ticket['status']; ?>">
                            <?php echo ucfirst($ticket['status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($ticketsByStatus)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($ticketsByStatus)); ?>,
                    backgroundColor: [
                        '#fef3c7',
                        '#dbeafe',
                        '#d1fae5',
                        '#f3f4f6'
                    ],
                    borderColor: [
                        '#92400e',
                        '#1e40af',
                        '#065f46',
                        '#374151'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Priority Chart
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($ticketsByPriority)); ?>,
                datasets: [{
                    label: 'Tickets',
                    data: <?php echo json_encode(array_values($ticketsByPriority)); ?>,
                    backgroundColor: [
                        '#fee2e2',
                        '#fef3c7',
                        '#d1fae5',
                        '#fecaca'
                    ],
                    borderColor: [
                        '#991b1b',
                        '#92400e',
                        '#065f46',
                        '#991b1b'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($ticketsByCategory)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($ticketsByCategory)); ?>,
                    backgroundColor: [
                        '#e0e7ff',
                        '#f0fdf4',
                        '#fef3c7',
                        '#fecaca',
                        '#f3f4f6'
                    ],
                    borderColor: [
                        '#3730a3',
                        '#166534',
                        '#92400e',
                        '#991b1b',
                        '#374151'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
