<?php
/**
 * Admin Users Management
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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? '';
    
    switch ($action) {
        case 'verify':
            $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$userId]);
            $message = 'User verified successfully';
            break;
            
        case 'unverify':
            $stmt = $db->prepare("UPDATE users SET is_verified = 0 WHERE id = ?");
            $stmt->execute([$userId]);
            $message = 'User verification removed';
            break;
            
        case 'delete':
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");
            $stmt->execute([$userId]);
            $message = 'User deleted successfully';
            break;
            
        case 'reset_password':
            $newPassword = bin2hex(random_bytes(8));
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            $message = 'Password reset successfully. New password: ' . $newPassword;
            break;
    }
}

// Get users with pagination
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$userType = $_GET['user_type'] ?? '';
$verified = $_GET['verified'] ?? '';

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($userType)) {
    $whereConditions[] = "user_type = ?";
    $params[] = $userType;
}

if ($verified !== '') {
    $whereConditions[] = "is_verified = ?";
    $params[] = $verified;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalUsers = $stmt->fetch()['total'];
$totalPages = ceil($totalUsers / $limit);

// Get users
$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin | Chinhoyi University of Technology</title>
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
        
        .users-table {
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
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .status-verified { background: #d1fae5; color: #065f46; }
        .status-unverified { background: #fee2e2; color: #991b1b; }
        
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-users"></i> Users Management</h1>
            <p>Manage all users in the system</p>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
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
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name or email">
                </div>
                
                <div class="form-group">
                    <label for="user_type">User Type</label>
                    <select id="user_type" name="user_type">
                        <option value="">All Types</option>
                        <option value="student" <?php echo $userType === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="staff" <?php echo $userType === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="faculty" <?php echo $userType === 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                        <option value="admin" <?php echo $userType === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="verified">Verification Status</label>
                    <select id="verified" name="verified">
                        <option value="">All</option>
                        <option value="1" <?php echo $verified === '1' ? 'selected' : ''; ?>>Verified</option>
                        <option value="0" <?php echo $verified === '0' ? 'selected' : ''; ?>>Unverified</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <div class="users-table">
            <div class="table-header">
                <h3>Users (<?php echo number_format($totalUsers); ?> total)</h3>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                No users found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="user-type type-<?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['student_id'] ?: 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['is_verified'] ? 'verified' : 'unverified'; ?>">
                                        <?php echo $user['is_verified'] ? 'Verified' : 'Unverified'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if (!$user['is_verified']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="verify">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Verify this user?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="unverify">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Remove verification?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Reset password for this user?')">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </form>
                                        
                                        <?php if ($user['user_type'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($userType); ?>&verified=<?php echo urlencode($verified); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($userType); ?>&verified=<?php echo urlencode($verified); ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($userType); ?>&verified=<?php echo urlencode($verified); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
