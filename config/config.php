<?php
// Application Configuration
session_start();

// Base URL
define('BASE_URL', 'http://localhost/APPOINTMENT%20IN%20GUIDANCE/');

// Site Configuration
define('SITE_NAME', 'GuideSched');
define('SITE_DESCRIPTION', 'Making guidance counseling more accessible, one appointment at a time.');
define('THEME_COLOR_START', '#11998e');
define('THEME_COLOR_END', '#38ef7d');

// Timezone
date_default_timezone_set('Asia/Manila');

// Security
define('HASH_ALGORITHM', 'sha256');
define('SESSION_TIMEOUT', 3600); // 1 hour

// File Upload Configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/database.php';

// Add security headers (will be included after security_functions is loaded in pages that need it)

// Helper function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to get current user role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Helper function to check if user has specific role
function hasRole($role) {
    return getUserRole() === $role;
}

// Helper function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Helper function to require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect('dashboard.php');
    }
}

// Helper function to require any of specified roles
function requireAnyRole($roles) {
    requireLogin();
    if (!in_array(getUserRole(), $roles)) {
        redirect('dashboard.php');
    }
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Helper function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Helper function to generate user ID
function generateUserId($prefix, $length = 6) {
    return $prefix . str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Helper function to format date
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

// Helper function to format time
function formatTime($time, $format = 'g:i A') {
    return date($format, strtotime($time));
}

// Helper function to check if session is expired
function isSessionExpired() {
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > SESSION_TIMEOUT) {
            return true;
        }
    }
    return false;
}

// Update last activity time
function updateLastActivity() {
    $_SESSION['last_activity'] = time();
}

// Check session expiration on each page load
if (isLoggedIn()) {
    if (isSessionExpired()) {
        session_destroy();
        redirect('login.php?session_expired=true');
    }
    updateLastActivity();
}
?>
