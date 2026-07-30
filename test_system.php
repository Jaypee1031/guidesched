<?php
// System Testing Script
// This script tests the basic functionality of the GuideSched system

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>GuideSched System Test</h2>";
echo "<p>Running basic system tests...</p>";

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Database Connection
echo "<h3>Test 1: Database Connection</h3>";
try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        $tests_passed++;
        closeDBConnection($conn);
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>";
    $tests_failed++;
}

// Test 2: Configuration Files
echo "<h3>Test 2: Configuration Files</h3>";
$config_files = [
    'config/config.php',
    'config/database.php',
    'includes/auth_functions.php',
    'includes/appointment_functions.php',
    'includes/admin_functions.php',
    'includes/security_functions.php'
];

foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ $file missing</p>";
        $tests_failed++;
    }
}

// Test 3: Required Directories
echo "<h3>Test 3: Required Directories</h3>";
$directories = [
    'admin',
    'student',
    'config',
    'includes',
    'database',
    'uploads'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✓ $dir/ directory exists</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ $dir/ directory missing</p>";
        $tests_failed++;
    }
}

// Test 4: Key Pages
echo "<h3>Test 4: Key Pages</h3>";
$key_pages = [
    'index.php',
    'login.php',
    'register.php',
    'logout.php',
    'setup_database.php',
    'student/dashboard.php',
    'admin/dashboard.php'
];

foreach ($key_pages as $page) {
    if (file_exists($page)) {
        echo "<p style='color: green;'>✓ $page exists</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ $page missing</p>";
        $tests_failed++;
    }
}

// Test 5: Security Features
echo "<h3>Test 5: Security Features</h3>";
try {
    require_once 'includes/security_functions.php';
    
    // Test CSRF token generation
    $token = generateCSRFToken();
    if ($token && strlen($token) === 64) {
        echo "<p style='color: green;'>✓ CSRF token generation working</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ CSRF token generation failed</p>";
        $tests_failed++;
    }
    
    // Test email validation
    if (validateEmail('test@example.com')) {
        echo "<p style='color: green;'>✓ Email validation working</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ Email validation failed</p>";
        $tests_failed++;
    }
    
    // Test password strength validation
    $weak_pass = validatePasswordStrength('weak');
    $strong_pass = validatePasswordStrength('StrongPass123');
    if (!$weak_pass['valid'] && $strong_pass['valid']) {
        echo "<p style='color: green;'>✓ Password strength validation working</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ Password strength validation failed</p>";
        $tests_failed++;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Security functions error: " . $e->getMessage() . "</p>";
    $tests_failed += 3;
}

// Test 6: Helper Functions
echo "<h3>Test 6: Helper Functions</h3>";
try {
    require_once 'config/config.php';
    
    // Test date formatting
    $formatted = formatDate('2026-07-30');
    if ($formatted) {
        echo "<p style='color: green;'>✓ Date formatting working</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ Date formatting failed</p>";
        $tests_failed++;
    }
    
    // Test time formatting
    $formatted = formatTime('14:30');
    if ($formatted) {
        echo "<p style='color: green;'>✓ Time formatting working</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ Time formatting failed</p>";
        $tests_failed++;
    }
    
    // Test input sanitization
    $sanitized = sanitizeInput('<script>alert("test")</script>');
    if ($sanitized !== '<script>alert("test")</script>') {
        echo "<p style='color: green;'>✓ Input sanitization working</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>✗ Input sanitization failed</p>";
        $tests_failed++;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Helper functions error: " . $e->getMessage() . "</p>";
    $tests_failed += 3;
}

// Test Summary
echo "<h3>Test Summary</h3>";
echo "<div style='padding: 20px; background: #f0fff4; border-radius: 10px; margin: 20px 0;'>";
echo "<p><strong>Total Tests:</strong> " . ($tests_passed + $tests_failed) . "</p>";
echo "<p style='color: green;'><strong>Passed:</strong> $tests_passed</p>";
echo "<p style='color: red;'><strong>Failed:</strong> $tests_failed</p>";

if ($tests_failed === 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ All tests passed! System is ready for use.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ Some tests failed. Please review the errors above.</p>";
}
echo "</div>";

// Next Steps
echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Run the database setup: <a href='setup_database.php'>setup_database.php</a></li>";
echo "<li>Test the landing page: <a href='index.php'>index.php</a></li>";
echo "<li>Test student registration: <a href='register.php'>register.php</a></li>";
echo "<li>Test admin login: <a href='login.php'>login.php</a> (admin@guidesched.com / admin123)</li>";
echo "<li>Test student portal functionality</li>";
echo "<li>Test admin portal functionality</li>";
echo "</ol>";

echo "<p><strong>Default Credentials:</strong></p>";
echo "<ul>";
echo "<li>Admin: admin@guidesched.com / admin123</li>";
echo "<li>Counselor: maria.santos@guidesched.com / counselor123</li>";
echo "</ul>";

echo "<p style='color: orange;'><strong>Important:</strong> Change default passwords after first login!</p>";
?>
