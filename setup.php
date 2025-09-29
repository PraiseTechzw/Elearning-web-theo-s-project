<?php
/**
 * Database Setup Script
 * Chinhoyi University of Technology - Campus IT Support System
 * Run this script to initialize the database
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'campus_it_support';

echo "<h1>Campus IT Support System - Database Setup</h1>";
echo "<p>Initializing database and creating tables...</p>";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
    echo "<p>✓ Database '$database' created/verified</p>";
    
    // Use the database
    $pdo->exec("USE `$database`");
    
    // Read and execute the complete database schema
    $sql = file_get_contents(__DIR__ . '/config/campus_db_complete.sql');
    if ($sql === false) {
        throw new Exception("Could not read database schema file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p>✓ Database tables created successfully</p>";
    
    // Read and execute additional schema updates
    $additionalSql = file_get_contents(__DIR__ . '/config/add_ticket_comments.sql');
    if ($additionalSql !== false) {
        $additionalStatements = array_filter(array_map('trim', explode(';', $additionalSql)));
        
        foreach ($additionalStatements as $statement) {
            if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<p>✓ Additional tables and columns added</p>";
    }
    
    // Verify tables were created
    $tables = ['users', 'support_tickets', 'activity_logs', 'system_settings', 'notifications', 'file_uploads', 'ticket_comments'];
    $existingTables = [];
    
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existingTables[] = $row[0];
    }
    
    $missingTables = array_diff($tables, $existingTables);
    
    if (empty($missingTables)) {
        echo "<p>✓ All required tables exist</p>";
    } else {
        echo "<p>⚠ Warning: Missing tables: " . implode(', ', $missingTables) . "</p>";
    }
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount > 0) {
        echo "<p>✓ Admin user exists</p>";
    } else {
        echo "<p>⚠ Warning: No admin user found. Please create an admin account.</p>";
    }
    
    // Test database connection using the Database class
    require_once __DIR__ . '/includes/Database.php';
    $db = new Database();
    
    if ($db) {
        echo "<p>✓ Database class connection successful</p>";
    } else {
        echo "<p>⚠ Warning: Database class connection failed</p>";
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>Your Campus IT Support System database has been initialized successfully.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Configure your web server to serve PHP files</li>";
    echo "<li>Update the database configuration in <code>config/database.php</code> if needed</li>";
    echo "<li>Set up email configuration for notifications</li>";
    echo "<li>Access the admin panel at <code>admin/login.php</code></li>";
    echo "<li>Default admin credentials: admin@cut.ac.zw / password</li>";
    echo "</ul>";
    
    echo "<p><a href='pages/index.html' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Homepage</a></p>";
    echo "<p><a href='admin/login.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Setup Failed!</h2>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
    echo "<p>Make sure MySQL is running and the credentials in this script are correct.</p>";
}
?>
