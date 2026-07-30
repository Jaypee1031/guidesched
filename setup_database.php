<?php
// Database Setup Script
// This script will create the database and all required tables

require_once 'config/database.php';

echo "<h2>GuideSched Database Setup</h2>";
echo "<p>Setting up database and tables...</p>";

try {
    // Read the schema file
    $schema_file = __DIR__ . '/database/schema.sql';
    if (!file_exists($schema_file)) {
        die("<p style='color: red;'>Error: Schema file not found at $schema_file</p>");
    }
    
    $schema = file_get_contents($schema_file);
    
    // Connect to MySQL without database selected
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
    }
    
    // Execute the schema
    if ($conn->multi_query($schema)) {
        echo "<p style='color: green;'>✓ Database and tables created successfully!</p>";
        
        // Flush any remaining results
        while ($conn->more_results() && $conn->next_result()) {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        }
        
        echo "<p style='color: green;'>✓ Default admin user created!</p>";
        echo "<p style='color: green;'>✓ Sample counselor user created!</p>";
        
        echo "<h3>Default Login Credentials:</h3>";
        echo "<ul>";
        echo "<li><strong>Admin:</strong> admin@guidesched.com / admin123</li>";
        echo "<li><strong>Counselor:</strong> maria.santos@guidesched.com / counselor123</li>";
        echo "</ul>";
        
        echo "<p style='color: orange;'><strong>Important:</strong> Please change these default passwords after first login!</p>";
        echo "<p><a href='index.php'>Go to GuideSched Homepage</a></p>";
        
    } else {
        echo "<p style='color: red;'>Error creating database: " . $conn->error . "</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
