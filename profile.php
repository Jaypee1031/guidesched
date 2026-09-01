<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/profile.php');
    } else {
        redirect('admin/profile.php');
    }
} else {
    redirect('login.php');
}
?>
