<?php
// Check availability slots
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Availability Slots Check</h2>";

try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection working</p>";
        
        // Check if availability table exists
        $result = $conn->query("SHOW TABLES LIKE 'availability'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Availability table exists</p>";
            
            // Check for availability slots
            $result = $conn->query("SELECT * FROM availability");
            if ($result->num_rows > 0) {
                echo "<p style='color: green;'>✓ Found {$result->num_rows} availability slots</p>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Counselor ID</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Status</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['counselor_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'>✗ No availability slots found in database</p>";
                echo "<p><strong>Solution:</strong> Admin needs to create availability slots for counselors first.</p>";
                echo "<p><a href='admin/schedule.php'>Go to Schedule Management</a></p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Availability table does not exist</p>";
            echo "<p><a href='setup_database.php'>Run database setup</a></p>";
        }
        
        // Check counselors
        $result = $conn->query("SELECT id, user_id, name FROM users WHERE role = 'counselor'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Found {$result->num_rows} counselors</p>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>ID: " . htmlspecialchars($row['id']) . " - " . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['user_id']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ No counselors found</p>";
        }
        
        closeDBConnection($conn);
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='admin/schedule.php'>Go to Schedule Management (Create Availability)</a></p>";
echo "<p><a href='student/book-appointment.php'>Go to Book Appointment</a></p>";
echo "<p><a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
?>
