<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getApiDBConnection();

if ($method === 'GET') {
    $userId = intval($_GET['user_id'] ?? 0);
    $role = sanitize($_GET['role'] ?? 'student');
    $status = sanitize($_GET['status'] ?? '');

    if (!$userId) {
        jsonError('user_id is required', 422);
    }

    if ($role === 'student') {
        $sql = "SELECT a.id, a.student_id, a.counselor_id, a.appointment_date, a.start_time, a.end_time, 
                       a.concern, a.status, a.admin_notes, a.created_at, a.updated_at,
                       cu.name as counselor_name, cu.email as counselor_email,
                       cp.specialization, cp.contact_number as counselor_contact
                FROM appointments a
                JOIN users cu ON a.counselor_id = cu.id
                LEFT JOIN counselor_profiles cp ON cu.id = cp.user_id
                WHERE a.student_id = ?";
        
        $params = [$userId];
        $types = "i";

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND a.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.start_time DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    } else {
        // Counselor or admin
        $sql = "SELECT a.id, a.student_id, a.counselor_id, a.appointment_date, a.start_time, a.end_time, 
                       a.concern, a.status, a.admin_notes, a.created_at, a.updated_at,
                       su.name as student_name, su.email as student_email,
                       sp.student_number, sp.course, sp.year_level, sp.contact_number as student_contact,
                       cu.name as counselor_name
                FROM appointments a
                JOIN users su ON a.student_id = su.id
                LEFT JOIN student_profiles sp ON su.id = sp.user_id
                JOIN users cu ON a.counselor_id = cu.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if ($role === 'counselor') {
            $sql .= " AND a.counselor_id = ?";
            $params[] = $userId;
            $types .= "i";
        }

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND a.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.start_time DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    closeApiDBConnection($conn);
    jsonSuccess(['appointments' => $appointments]);

} elseif ($method === 'POST') {
    $input = getJsonInput();
    $action = $input['action'] ?? 'book';

    if ($action === 'book') {
        $studentId = intval($input['student_id'] ?? 0);
        $counselorId = intval($input['counselor_id'] ?? 0);
        $date = sanitize($input['appointment_date'] ?? $input['date'] ?? '');
        $startTime = sanitize($input['start_time'] ?? '');
        $endTime = sanitize($input['end_time'] ?? '');
        $mode = sanitize($input['mode'] ?? 'Face-to-face');
        $concernCategory = sanitize($input['concern_category'] ?? 'Academic stress');
        $details = sanitize($input['details'] ?? '');

        if (!$studentId || !$counselorId || empty($date) || empty($startTime) || empty($endTime)) {
            jsonError('Required fields: student_id, counselor_id, date, start_time, end_time', 422);
        }

        // Format full concern string
        $fullConcern = "[" . $mode . "] " . $concernCategory . ($details ? ": " . $details : "");

        // Check if slot already booked
        $chk = $conn->prepare("SELECT id FROM appointments WHERE counselor_id = ? AND appointment_date = ? AND start_time = ? AND status IN ('pending', 'approved')");
        $chk->bind_param("iss", $counselorId, $date, $startTime);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            jsonError('This time slot is already booked. Please choose another slot.', 409);
        }

        // Check if student already has a pending/approved appointment on this date
        $chk2 = $conn->prepare("SELECT id FROM appointments WHERE student_id = ? AND appointment_date = ? AND status IN ('pending', 'approved')");
        $chk2->bind_param("is", $studentId, $date);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            jsonError('You already have an active appointment booked on this date.', 409);
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO appointments (student_id, counselor_id, appointment_date, start_time, end_time, concern, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iissss", $studentId, $counselorId, $date, $startTime, $endTime, $fullConcern);
            $stmt->execute();
            $appointmentId = $conn->insert_id;

            // Notification for counselor
            $notifMsg = "New appointment request on " . date('M j, Y', strtotime($date)) . " at " . date('g:i A', strtotime($startTime));
            $n1 = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'info')");
            $n1->bind_param("iis", $counselorId, $appointmentId, $notifMsg);
            $n1->execute();

            // Notification for student
            $stuMsg = "Your appointment request has been submitted for " . date('M j, Y', strtotime($date)) . " at " . date('g:i A', strtotime($startTime));
            $n2 = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'info')");
            $n2->bind_param("iis", $studentId, $appointmentId, $stuMsg);
            $n2->execute();

            // Appointment history
            $hist = $conn->prepare("INSERT INTO appointment_history (appointment_id, action, new_status, changed_by) VALUES (?, 'created', 'pending', ?)");
            $hist->bind_param("ii", $appointmentId, $studentId);
            $hist->execute();

            $conn->commit();
            closeApiDBConnection($conn);
            jsonSuccess(['appointment_id' => $appointmentId], 'Appointment requested successfully', 201);
        } catch (Throwable $e) {
            $conn->rollback();
            closeApiDBConnection($conn);
            jsonError('Failed to book appointment: ' . $e->getMessage(), 500);
        }
    }

} elseif ($method === 'PUT' || $method === 'PATCH' || ($method === 'POST' && isset($_GET['action']))) {
    $input = getJsonInput();
    $appointmentId = intval($input['appointment_id'] ?? $input['id'] ?? ($_GET['id'] ?? 0));
    $action = sanitize($input['action'] ?? ($_GET['action'] ?? ''));
    $changedBy = intval($input['changed_by'] ?? 0);
    $adminNotes = sanitize($input['admin_notes'] ?? '');

    if (!$appointmentId || empty($action)) {
        jsonError('appointment_id and action are required', 422);
    }

    // Check if updating notes only
    $isNotesUpdate = ($action === 'update_notes' || $action === 'notes');

    // Map actions to status
    $statusMap = [
        'approve' => 'approved',
        'decline' => 'declined',
        'complete' => 'completed',
        'cancel' => 'cancelled',
        'noshow' => 'no_show',
        'reschedule' => 'rescheduled',
    ];

    $newStatus = $statusMap[$action] ?? $action;
    $allowedStatuses = ['pending', 'approved', 'declined', 'rescheduled', 'completed', 'cancelled', 'no_show'];
    if (!$isNotesUpdate && !in_array($newStatus, $allowedStatuses)) {
        jsonError('Invalid status action: ' . $action, 400);
    }

    // Fetch appointment
    $stmt = $conn->prepare("SELECT a.*, cu.name as counselor_name FROM appointments a JOIN users cu ON a.counselor_id = cu.id WHERE a.id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $apt = $stmt->get_result()->fetch_assoc();

    if (!$apt) {
        jsonError('Appointment not found', 404);
    }

    $oldStatus = $apt['status'];

    $conn->begin_transaction();
    try {
        if ($isNotesUpdate) {
            $upd = $conn->prepare("UPDATE appointments SET admin_notes = ? WHERE id = ?");
            $upd->bind_param("si", $adminNotes, $appointmentId);
            $upd->execute();

            // Add history record
            $h = $conn->prepare("INSERT INTO appointment_history (appointment_id, action, old_status, new_status, changed_by) VALUES (?, 'notes_updated', ?, ?, ?)");
            $h->bind_param("issi", $appointmentId, $oldStatus, $oldStatus, $changedBy);
            $h->execute();

            // Notify student about counselor remarks/notes
            if (!empty($adminNotes)) {
                $notifMsg = "Counselor remarks updated for your session on " . date('M j, Y', strtotime($apt['appointment_date'])) . ": " . $adminNotes;
                $n = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'info')");
                $n->bind_param("iiss", $apt['student_id'], $appointmentId, $notifMsg);
                $n->execute();
            }

            $conn->commit();
            closeApiDBConnection($conn);
            jsonSuccess(['appointment_id' => $appointmentId, 'admin_notes' => $adminNotes, 'status' => $oldStatus], 'Counselor notes saved successfully');
        }

        if (!empty($adminNotes)) {
            $upd = $conn->prepare("UPDATE appointments SET status = ?, admin_notes = ? WHERE id = ?");
            $upd->bind_param("ssi", $newStatus, $adminNotes, $appointmentId);
        } else {
            $upd = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $upd->bind_param("si", $newStatus, $appointmentId);
        }
        $upd->execute();

        // Add history record
        $h = $conn->prepare("INSERT INTO appointment_history (appointment_id, action, old_status, new_status, changed_by) VALUES (?, 'status_change', ?, ?, ?)");
        $h->bind_param("issi", $appointmentId, $oldStatus, $newStatus, $changedBy);
        $h->execute();

        // Create student notification
        $statusLabels = [
            'approved' => 'approved',
            'declined' => 'declined',
            'completed' => 'marked as completed',
            'cancelled' => 'cancelled',
            'no_show' => 'marked as no-show',
            'rescheduled' => 'rescheduled'
        ];
        $msgVerb = $statusLabels[$newStatus] ?? $newStatus;
        $notifMsg = "Your appointment on " . date('M j, Y', strtotime($apt['appointment_date'])) . " has been " . $msgVerb . ".";
        if (!empty($adminNotes)) {
            $notifMsg .= " Note: " . $adminNotes;
        }

        $nType = in_array($newStatus, ['approved', 'declined', 'rescheduled']) ? $newStatus : 'info';
        $n = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, ?)");
        $n->bind_param("iiss", $apt['student_id'], $appointmentId, $notifMsg, $nType);
        $n->execute();

        $conn->commit();
        closeApiDBConnection($conn);
        jsonSuccess(['appointment_id' => $appointmentId, 'status' => $newStatus], 'Appointment status updated to ' . $newStatus);

    } catch (Throwable $e) {
        $conn->rollback();
        closeApiDBConnection($conn);
        jsonError('Failed to update status: ' . $e->getMessage(), 500);
    }
}

closeApiDBConnection($conn);
jsonError('Method not allowed', 405);
