<?php
// Appointment Functions

// Get student appointments
function getStudentAppointments($student_id, $status = null) {
    $conn = getDBConnection();
    
    $query = "SELECT a.id, a.appointment_date, a.start_time, a.end_time, a.concern, a.status, a.admin_notes,
              u.name as counselor_name, u.user_id as counselor_id
              FROM appointments a
              JOIN users u ON a.counselor_id = u.id
              WHERE a.student_id = ?";
    
    if ($status) {
        $query .= " AND a.status = ?";
    }
    
    $query .= " ORDER BY a.appointment_date DESC, a.start_time DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($status) {
        $stmt->bind_param("is", $student_id, $status);
    } else {
        $stmt->bind_param("i", $student_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    
    closeDBConnection($conn);
    return $appointments;
}

// Get available counselors
function getAvailableCounselors() {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT u.id, u.user_id, u.name, c.specialization 
                           FROM users u 
                           LEFT JOIN counselor_profiles c ON u.id = c.user_id 
                           WHERE u.role = 'counselor' AND u.status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    $counselors = $result->fetch_all(MYSQLI_ASSOC);
    
    closeDBConnection($conn);
    return $counselors;
}

// Get counselor availability
function getCounselorAvailability($counselor_id, $date) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, date, start_time, end_time, status 
                           FROM availability 
                           WHERE counselor_id = ? AND date = ? AND status = 'available'
                           ORDER BY start_time");
    $stmt->bind_param("is", $counselor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $slots = $result->fetch_all(MYSQLI_ASSOC);
    
    closeDBConnection($conn);
    return $slots;
}

// Book appointment
function bookAppointment($student_id, $counselor_id, $date, $start_time, $end_time, $concern) {
    $conn = getDBConnection();
    
    // Check if availability slot exists and is available
    $stmt = $conn->prepare("SELECT id FROM availability 
                           WHERE counselor_id = ? AND date = ? AND start_time = ? AND end_time = ? AND status = 'available'");
    $stmt->bind_param("isss", $counselor_id, $date, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Selected time slot is not available.'];
    }
    
    $availability_id = $result->fetch_assoc()['id'];
    
    // Insert appointment
    $stmt = $conn->prepare("INSERT INTO appointments (student_id, counselor_id, appointment_date, start_time, end_time, concern, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissss", $student_id, $counselor_id, $date, $start_time, $end_time, $concern);
    
    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        
        // Update availability status to booked
        $stmt = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
        $stmt->bind_param("i", $availability_id);
        $stmt->execute();
        
        // Create notification for counselor
        $message = "New appointment request from student for " . formatDate($date) . " at " . formatTime($start_time);
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'info')");
        $stmt->bind_param("iis", $counselor_id, $appointment_id, $message);
        $stmt->execute();
        
        // Create notification for student
        $message = "Your appointment request has been submitted for " . formatDate($date) . " at " . formatTime($start_time);
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'info')");
        $stmt->bind_param("iis", $student_id, $appointment_id, $message);
        $stmt->execute();
        
        closeDBConnection($conn);
        return ['success' => true, 'message' => 'Appointment request submitted successfully!'];
    }
    
    closeDBConnection($conn);
    return ['success' => false, 'message' => 'Failed to book appointment. Please try again.'];
}

// Get student notifications
function getStudentNotifications($student_id, $unread_only = false) {
    $conn = getDBConnection();
    
    $query = "SELECT id, appointment_id, message, type, is_read, created_at 
              FROM notifications 
              WHERE user_id = ?";
    
    if ($unread_only) {
        $query .= " AND is_read = FALSE";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    
    closeDBConnection($conn);
    return $notifications;
}

// Mark notification as read
function markNotificationAsRead($notification_id, $user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $result = $stmt->execute();
    
    closeDBConnection($conn);
    return $result;
}

// Get appointment statistics for student
function getStudentAppointmentStats($student_id) {
    $conn = getDBConnection();
    
    $stats = [
        'upcoming' => 0,
        'pending' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'total' => 0
    ];
    
    // Get all appointments
    $appointments = getStudentAppointments($student_id);
    $stats['total'] = count($appointments);
    
    foreach ($appointments as $appointment) {
        switch ($appointment['status']) {
            case 'approved':
                if (strtotime($appointment['appointment_date']) >= strtotime(date('Y-m-d'))) {
                    $stats['upcoming']++;
                }
                break;
            case 'pending':
                $stats['pending']++;
                break;
            case 'completed':
                $stats['completed']++;
                break;
            case 'cancelled':
                $stats['cancelled']++;
                break;
        }
    }
    
    closeDBConnection($conn);
    return $stats;
}

// Cancel appointment
function cancelAppointment($appointment_id, $student_id) {
    $conn = getDBConnection();
    
    // Check if appointment belongs to student and is in cancellable state
    $stmt = $conn->prepare("SELECT id, counselor_id, appointment_date, start_time, end_time, status 
                           FROM appointments 
                           WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $appointment_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Appointment not found.'];
    }
    
    $appointment = $result->fetch_assoc();
    
    if (!in_array($appointment['status'], ['pending', 'approved'])) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Cannot cancel ' . $appointment['status'] . ' appointments.'];
    }
    
    // Update appointment status
    $old_status = $appointment['status'];
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        // Make availability slot available again
        $stmt = $conn->prepare("UPDATE availability SET status = 'available' 
                               WHERE counselor_id = ? AND date = ? AND start_time = ? AND end_time = ?");
        $stmt->bind_param("isss", $appointment['counselor_id'], $appointment['appointment_date'], 
                         $appointment['start_time'], $appointment['end_time']);
        $stmt->execute();
        
        // Record in history
        $stmt = $conn->prepare("INSERT INTO appointment_history (appointment_id, action, old_status, new_status, changed_by) 
                               VALUES (?, 'cancelled', ?, 'cancelled', ?)");
        $stmt->bind_param("issi", $appointment_id, $old_status, $student_id);
        $stmt->execute();
        
        // Notify counselor
        $message = "Appointment on " . formatDate($appointment['appointment_date']) . " at " . formatTime($appointment['start_time']) . " has been cancelled by student.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'info')");
        $stmt->bind_param("iis", $appointment['counselor_id'], $appointment_id, $message);
        $stmt->execute();
        
        closeDBConnection($conn);
        return ['success' => true, 'message' => 'Appointment cancelled successfully.'];
    }
    
    closeDBConnection($conn);
    return ['success' => false, 'message' => 'Failed to cancel appointment. Please try again.'];
}
?>
