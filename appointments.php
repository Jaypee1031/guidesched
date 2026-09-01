<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/appointments.php');
    } else {
        redirect('admin/appointments.php');
    }
} else {
    redirect('login.php');
}
?>
