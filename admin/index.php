<?php
/**
 * Admin Panel
 * Praisetech - Campus IT Support System
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';

// Check if user is admin (simplified check)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$stats = [];

try {
    // Get user statistics
    $userCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $recentUsers = $db->query("SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
    $stats = [
        'total_users' => $userCount,
        'recent_users' => $recentUsers
    ];
} catch (Exception $e) {
    $error = "Error loading statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Campus IT Support | Praisetech</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            background: linear-gradient(135deg, #003366, #004080);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
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
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #003366;
            margin-bottom: 10px;
        }
        .admin-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .admin-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #003366;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-cog"></i> Admin Panel</h1>
            <p>Praisetech - Campus IT Support System</p>
            <a href="logout.php" class="btn btn-secondary" style="margin-top: 15px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_users'] ?? 0; ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div>Active Sessions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div>Support Tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div>System Uptime</div>
            </div>
        </div>

        <div class="admin-table">
            <h3 style="padding: 20px; margin: 0; background: #f8f9fa; border-bottom: 1px solid #eee;">
                Recent Users
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stats['recent_users'])): ?>
                        <?php foreach ($stats['recent_users'] as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #666;">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
