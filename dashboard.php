<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

// Smart router for root /dashboard.php
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/dashboard.php');
    } elseif ($role === 'counselor' || $role === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('login.php');
    }
} else {
    redirect('login.php');
}
?>
