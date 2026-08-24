<?php
// Simple login test script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login Test Script</h2>";

try {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'includes/auth_functions.php';
    
    // Test database connection
    $conn = getDBConnection();
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection working</p>";
        
        // Test admin login directly
        $email = 'admin@guidesched.com';
        $password = 'admin123';
        
        echo "<p>Testing login with: $email</p>";
        
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            echo "<p style='color: green;'>✓ Login successful!</p>";
            echo "<p>Role: " . htmlspecialchars($result['role']) . "</p>";
            echo "<p><a href='login.php'>Try actual login page</a></p>";
        } else {
            echo "<p style='color: red;'>✗ Login failed: " . htmlspecialchars($result['message']) . "</p>";
            
            // Check if admin user exists
            $stmt = $conn->prepare("SELECT id, user_id, role, name, email, password, status FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result_check = $stmt->get_result();
            
            if ($result_check->num_rows > 0) {
                $user = $result_check->fetch_assoc();
                echo "<p>Admin user found in database:</p>";
                echo "<ul>";
                echo "<li>User ID: " . htmlspecialchars($user['user_id']) . "</li>";
                echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
                echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
                echo "<li>Role: " . htmlspecialchars($user['role']) . "</li>";
                echo "<li>Status: " . htmlspecialchars($user['status']) . "</li>";
                echo "</ul>";
                
                // Test password verification
                if (password_verify($password, $user['password'])) {
                    echo "<p style='color: green;'>✓ Password verification works</p>";
                } else {
                    echo "<p style='color: red;'>✗ Password verification failed</p>";
                    echo "<p>The stored password hash may not match 'admin123'</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Admin user not found in database</p>";
            }
        }
        
        closeDBConnection($conn);
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>
