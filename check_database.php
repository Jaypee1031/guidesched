<?php
// Quick database check script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Check</h2>";

try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if database exists
        $result = $conn->query("SELECT DATABASE()");
        $db_name = $result->fetch_row()[0];
        echo "<p>Current database: <strong>$db_name</strong></p>";
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Users table exists</p>";
            
            // Check for admin user
            $stmt = $conn->prepare("SELECT user_id, role, name, email, status FROM users WHERE email = ?");
            $stmt->bind_param("s", "admin@guidesched.com");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                echo "<p style='color: green;'>✓ Admin account found:</p>";
                echo "<ul>";
                echo "<li>User ID: " . htmlspecialchars($admin['user_id']) . "</li>";
                echo "<li>Name: " . htmlspecialchars($admin['name']) . "</li>";
                echo "<li>Email: " . htmlspecialchars($admin['email']) . "</li>";
                echo "<li>Role: " . htmlspecialchars($admin['role']) . "</li>";
                echo "<li>Status: " . htmlspecialchars($admin['status']) . "</li>";
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>✗ Admin account not found!</p>";
                echo "<p><a href='setup_database.php'>Run database setup</a></p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Users table does not exist!</p>";
            echo "<p><a href='setup_database.php'>Run database setup</a></p>";
        }
        
        closeDBConnection($conn);
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='login.php'>Try Login Again</a></p>";
echo "<p><a href='setup_database.php'>Setup Database</a></p>";
echo "<p><a href='test_system.php'>Run System Tests</a></p>";
?>
