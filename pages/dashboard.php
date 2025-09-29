<?php
/**
 * User Dashboard
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

// Get user's tickets
$stmt = $db->prepare("
    SELECT * FROM support_tickets 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recent_tickets = $stmt->fetchAll();

// Get user's notifications
$stmt = $db->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user['id']]);
$notifications = $stmt->fetchAll();

// Get ticket statistics
$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE user_id = ?");
$stmt->execute([$user['id']]);
$total_tickets = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE user_id = ? AND status = 'open'");
$stmt->execute([$user['id']]);
$open_tickets = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE user_id = ? AND status = 'resolved'");
$stmt->execute([$user['id']]);
$resolved_tickets = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Campus IT Support | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <img src="../assets/images/CUT_LOG-removebg-preview.png" alt="CUT Logo">
            <span class="brand">Chinhoyi University of Technology</span>
        </div>
        
        <nav>
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="about.html"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="project-overview.html"><i class="fas fa-project-diagram"></i> Project</a></li>
                <li><a href="contact.html"><i class="fas fa-envelope"></i> Contact</a></li>
                <li class="dropdown">
                    <a href="#"><i class="fas fa-tools"></i> Services <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="network-setup.html"><i class="fas fa-network-wired"></i> Network Setup</a></li>
                        <li><a href="hardware-support.html"><i class="fas fa-desktop"></i> Hardware Support</a></li>
                        <li><a href="user-training.html"><i class="fas fa-graduation-cap"></i> User Training</a></li>
                        <li><a href="activity-graph.html"><i class="fas fa-chart-line"></i> Activity Graph</a></li>
                        <li><a href="gallery.html"><i class="fas fa-images"></i> Gallery</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <div class="user-menu">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                <span class="user-type"><?php echo ucfirst($user['user_type']); ?></span>
            </div>
            <div class="user-actions">
                <a href="profile.php" class="btn btn-outline"><i class="fas fa-user"></i> Profile</a>
                <a href="../includes/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <button class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Manage your IT support requests and access campus resources</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Tickets</h3>
                        <p class="stat-number"><?php echo $total_tickets; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Open Tickets</h3>
                        <p class="stat-number"><?php echo $open_tickets; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Resolved</h3>
                        <p class="stat-number"><?php echo $resolved_tickets; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Notifications</h3>
                        <p class="stat-number"><?php echo count($notifications); ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="create-ticket.php" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <h3>Create Ticket</h3>
                        <p>Submit a new support request</p>
                    </a>
                    
                    <a href="my-tickets.php" class="action-card">
                        <i class="fas fa-list"></i>
                        <h3>My Tickets</h3>
                        <p>View all your support tickets</p>
                    </a>
                    
                    <a href="user-training.html" class="action-card">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>Training</h3>
                        <p>Access training materials</p>
                    </a>
                    
                    <a href="network-setup.html" class="action-card">
                        <i class="fas fa-network-wired"></i>
                        <h3>Network Help</h3>
                        <p>Network setup and troubleshooting</p>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-grid">
                <!-- Recent Tickets -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-ticket-alt"></i> Recent Tickets</h3>
                        <a href="my-tickets.php" class="btn btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_tickets)): ?>
                            <p class="no-data">No tickets found</p>
                        <?php else: ?>
                            <?php foreach ($recent_tickets as $ticket): ?>
                                <div class="ticket-item">
                                    <div class="ticket-info">
                                        <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                        <p><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></p>
                                    </div>
                                    <div class="ticket-status">
                                        <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                            <?php echo ucfirst($ticket['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-bell"></i> Notifications</h3>
                        <a href="notifications.php" class="btn btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p class="no-data">No notifications</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item">
                                    <div class="notification-icon">
                                        <i class="fas fa-<?php echo $notification['type'] === 'success' ? 'check-circle' : ($notification['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <small><?php echo date('M j, Y H:i', strtotime($notification['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Chinhoyi University of Technology</h3>
                <p>Empowering students and staff with reliable IT support services.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="about.html">About Us</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="project-overview.html">Project Overview</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="network-setup.html">Network Setup</a></li>
                    <li><a href="hardware-support.html">Hardware Support</a></li>
                    <li><a href="user-training.html">Training</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Chinhoyi University of Technology - Campus IT Support System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
