<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getApiDBConnection();

if ($method === 'GET') {
    $counselorId = intval($_GET['counselor_id'] ?? 0);
    $date = sanitize($_GET['date'] ?? date('Y-m-d'));

    if (!$counselorId) {
        // Fallback: get first available counselor if not specified
        $q = $conn->query("SELECT id FROM users WHERE role = 'counselor' AND status = 'active' LIMIT 1");
        if ($r = $q->fetch_assoc()) {
            $counselorId = intval($r['id']);
        }
    }

    // 1. Check custom slots in availability table
    $stmt = $conn->prepare("SELECT id, date, start_time, end_time, status FROM availability WHERE counselor_id = ? AND date = ? ORDER BY start_time ASC");
    $stmt->bind_param("is", $counselorId, $date);
    $stmt->execute();
    $customSlots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // 2. Fetch existing booked/pending appointments for this counselor and date
    $aptStmt = $conn->prepare("SELECT start_time, end_time FROM appointments WHERE counselor_id = ? AND appointment_date = ? AND status IN ('pending', 'approved')");
    $aptStmt->bind_param("is", $counselorId, $date);
    $aptStmt->execute();
    $aptRes = $aptStmt->get_result();
    $bookedStartTimes = [];
    while ($ar = $aptRes->fetch_assoc()) {
        $bookedStartTimes[] = substr($ar['start_time'], 0, 5); // "HH:MM"
    }

    $slots = [];

    if (count($customSlots) > 0) {
        foreach ($customSlots as $cs) {
            $slotStartPrefix = substr($cs['start_time'], 0, 5);
            $isBooked = in_array($slotStartPrefix, $bookedStartTimes);
            $status = $isBooked ? 'booked' : $cs['status'];
            $slots[] = [
                'id' => intval($cs['id']),
                'start_time' => $cs['start_time'],
                'end_time' => $cs['end_time'],
                'status' => $status,
                'is_available' => ($status === 'available'),
            ];
        }
    } else {
        // Default standard office slots (09:00 to 17:00)
        $defaultTimes = [
            ['start_time' => '09:00:00', 'end_time' => '10:00:00'],
            ['start_time' => '10:00:00', 'end_time' => '11:00:00'],
            ['start_time' => '11:00:00', 'end_time' => '12:00:00'],
            ['start_time' => '13:00:00', 'end_time' => '14:00:00'],
            ['start_time' => '14:00:00', 'end_time' => '15:00:00'],
            ['start_time' => '15:00:00', 'end_time' => '16:00:00'],
            ['start_time' => '16:00:00', 'end_time' => '17:00:00'],
        ];

        $counter = 1;
        foreach ($defaultTimes as $dt) {
            $slotStartPrefix = substr($dt['start_time'], 0, 5);
            $isBooked = in_array($slotStartPrefix, $bookedStartTimes);
            $status = $isBooked ? 'booked' : 'available';
            $slots[] = [
                'id' => $counter++,
                'start_time' => $dt['start_time'],
                'end_time' => $dt['end_time'],
                'status' => $status,
                'is_available' => ($status === 'available'),
            ];
        }
    }

    closeApiDBConnection($conn);
    jsonSuccess([
        'counselor_id' => $counselorId,
        'date' => $date,
        'slots' => $slots
    ]);

} elseif ($method === 'POST') {
    $input = getJsonInput();
    $counselorId = intval($input['counselor_id'] ?? 0);
    $date = sanitize($input['date'] ?? '');
    $startTime = sanitize($input['start_time'] ?? '');
    $endTime = sanitize($input['end_time'] ?? '');
    $status = sanitize($input['status'] ?? 'available'); // 'available' or 'blocked'

    if (!$counselorId || empty($date) || empty($startTime) || empty($endTime)) {
        jsonError('Counselor ID, date, start time, and end time are required', 422);
    }

    $stmt = $conn->prepare("INSERT INTO availability (counselor_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
    $stmt->bind_param("isssss", $counselorId, $date, $startTime, $endTime, $status, $status);
    
    if ($stmt->execute()) {
        $slotId = $stmt->insert_id ?: 0;
        closeApiDBConnection($conn);
        jsonSuccess(['id' => $slotId], 'Availability slot updated successfully');
    } else {
        closeApiDBConnection($conn);
        jsonError('Failed to update availability slot: ' . $conn->error, 500);
    }
}

closeApiDBConnection($conn);
jsonError('Method not allowed', 405);
