<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

header('Content-Type: application/json');

$counselor_id = intval($_GET['counselor_id'] ?? 0);
$date = sanitizeInput($_GET['date'] ?? '');

if (!$counselor_id || !$date) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$slots = getCounselorAvailability($counselor_id, $date);

echo json_encode(['success' => true, 'slots' => $slots]);
