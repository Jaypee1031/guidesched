<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/analytics.php');
    } else {
        redirect('admin/analytics.php');
    }
} else {
    redirect('login.php');
}
?>
