<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

// Redirect to unified appointments tab for seamless experience
header('Location: appointments.php?tab=book');
exit;
