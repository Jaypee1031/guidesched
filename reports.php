<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

if (isLoggedIn()) {
    redirect('admin/reports.php');
} else {
    redirect('login.php');
}
?>
