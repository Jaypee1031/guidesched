<?php
// Reset admin & counselor default passwords script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Reset Admin & Counselor Passwords</h2>";

try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection working</p>";
        
        // Reset Admin password to 'admin123'
        $admin_pass = 'admin123';
        $admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@guidesched.com'");
        $stmt->bind_param("s", $admin_hash);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Admin password set to: <strong>admin123</strong> (email: admin@guidesched.com)</p>";
        }
        
        // Reset Counselor password to 'counselor123'
        $counselor_pass = 'counselor123';
        $counselor_hash = password_hash($counselor_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'maria.santos@guidesched.com'");
        $stmt->bind_param("s", $counselor_hash);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Counselor password set to: <strong>counselor123</strong> (email: maria.santos@guidesched.com)</p>";
        }
        
        closeDBConnection($conn);
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
