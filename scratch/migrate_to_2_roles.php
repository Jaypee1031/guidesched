<?php
require_once __DIR__ . '/../config/config.php';

$conn = getDBConnection();
echo "=== Migrating Database to 2 Roles: Student and Counselor ===\n\n";

// Update all admin role users to counselor
$conn->query("UPDATE users SET role = 'counselor' WHERE role = 'admin'");
echo "Updated users table: admin role converted to counselor.\n";

// Ensure default admin@guidesched.com has counselor profile
$res = $conn->query("SELECT id FROM users WHERE email = 'admin@guidesched.com'");
if ($res && $res->num_rows > 0) {
    $admin_id = $res->fetch_assoc()['id'];
    $prof_res = $conn->query("SELECT id FROM counselor_profiles WHERE user_id = $admin_id");
    if ($prof_res && $prof_res->num_rows == 0) {
        $conn->query("INSERT INTO counselor_profiles (user_id, specialization, contact_number) VALUES ($admin_id, 'Head Guidance Counselor', '09171234567')");
        echo "Created counselor profile for admin@guidesched.com.\n";
    }
}

// Reset password for counselor maria.santos@guidesched.com and admin@guidesched.com to counselor123
$pass_hash = password_hash('counselor123', PASSWORD_BCRYPT);
$conn->query("UPDATE users SET password = '$pass_hash' WHERE email IN ('maria.santos@guidesched.com', 'admin@guidesched.com')");
echo "Passwords updated to counselor123 for default counselor accounts.\n";

closeDBConnection($conn);
echo "\nMigration complete!\n";
?>
