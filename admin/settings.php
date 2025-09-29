<?php
/**
 * Admin Settings Page
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

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_settings') {
        try {
            $settings = [
                'site_name' => $_POST['site_name'] ?? '',
                'site_url' => $_POST['site_url'] ?? '',
                'admin_email' => $_POST['admin_email'] ?? '',
                'support_email' => $_POST['support_email'] ?? '',
                'max_file_size' => $_POST['max_file_size'] ?? '',
                'session_timeout' => $_POST['session_timeout'] ?? '',
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $message = 'Settings updated successfully';
        } catch (Exception $e) {
            error_log("Settings update error: " . $e->getMessage());
            $error = 'An error occurred while updating settings';
        }
    }
}

// Get current settings
$stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings");
$stmt->execute();
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get system statistics
$stats = [];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets");
$stmt->execute();
$stats['total_tickets'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM support_tickets WHERE status = 'open'");
$stmt->execute();
$stats['open_tickets'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0");
$stmt->execute();
$stats['unread_notifications'] = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin | Chinhoyi University of Technology</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
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
        
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            padding: 12px 24px;
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
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
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
            font-size: 2rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
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
        
        .danger-zone {
            border: 2px solid #ef4444;
            background: #fef2f2;
        }
        
        .danger-zone .card-header {
            background: #fee2e2;
            color: #991b1b;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-cog"></i> System Settings</h1>
            <p>Configure system-wide settings and preferences</p>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="../pages/dashboard.html"><i class="fas fa-home"></i> User Dashboard</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
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
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="number"><?php echo number_format($stats['total_users']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Tickets</h3>
                <p class="number"><?php echo number_format($stats['total_tickets']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Open Tickets</h3>
                <p class="number"><?php echo number_format($stats['open_tickets']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Unread Notifications</h3>
                <p class="number"><?php echo number_format($stats['unread_notifications']); ?></p>
            </div>
        </div>
        
        <div class="settings-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-globe"></i> General Settings</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" 
                                   value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Campus IT Support System'); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_url">Site URL</label>
                            <input type="url" id="site_url" name="site_url" 
                                   value="<?php echo htmlspecialchars($settings['site_url'] ?? 'http://localhost'); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Admin Email</label>
                            <input type="email" id="admin_email" name="admin_email" 
                                   value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@cut.ac.zw'); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="support_email">Support Email</label>
                            <input type="email" id="support_email" name="support_email" 
                                   value="<?php echo htmlspecialchars($settings['support_email'] ?? 'support@cut.ac.zw'); ?>" 
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-shield-alt"></i> Security Settings</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="form-group">
                            <label for="max_file_size">Max File Upload Size (MB)</label>
                            <input type="number" id="max_file_size" name="max_file_size" 
                                   value="<?php echo htmlspecialchars(($settings['max_file_size'] ?? 10485760) / 1048576); ?>" 
                                   min="1" max="100" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (minutes)</label>
                            <input type="number" id="session_timeout" name="session_timeout" 
                                   value="<?php echo htmlspecialchars(($settings['session_timeout'] ?? 3600) / 60); ?>" 
                                   min="5" max="1440" required>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                       <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <label for="maintenance_mode">Maintenance Mode</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card danger-zone">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
            </div>
            <div class="card-body">
                <p><strong>Warning:</strong> These actions are irreversible and can cause data loss.</p>
                
                <div style="margin-top: 20px;">
                    <button class="btn btn-danger" onclick="if(confirm('Are you sure? This will clear all activity logs.')) clearActivityLogs()">
                        <i class="fas fa-trash"></i> Clear Activity Logs
                    </button>
                    
                    <button class="btn btn-danger" onclick="if(confirm('Are you sure? This will clear all notifications.')) clearNotifications()">
                        <i class="fas fa-bell-slash"></i> Clear All Notifications
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function clearActivityLogs() {
            fetch('clear-logs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({action: 'clear_activity_logs'})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Activity logs cleared successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
        
        function clearNotifications() {
            fetch('clear-logs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({action: 'clear_notifications'})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Notifications cleared successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
