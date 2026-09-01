<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/notifications.php');
    } else {
        redirect('admin/notifications.php');
    }
} else {
    redirect('login.php');
}
?>
