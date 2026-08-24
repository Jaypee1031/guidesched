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
    
    // Check custom slots in availability table
    $stmt = $conn->prepare("SELECT id, date, start_time, end_time, status 
                           FROM availability 
                           WHERE counselor_id = ? AND date = ?
                           ORDER BY start_time");
    $stmt->bind_param("is", $counselor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $slots = $result->fetch_all(MYSQLI_ASSOC);
    
    // Check existing appointments for booked times
    $stmt = $conn->prepare("SELECT start_time FROM appointments WHERE counselor_id = ? AND appointment_date = ? AND status IN ('pending', 'approved')");
    $stmt->bind_param("is", $counselor_id, $date);
    $stmt->execute();
    $apts_res = $stmt->get_result();
    $booked_times = [];
    while ($r = $apts_res->fetch_assoc()) {
        $booked_times[] = $r['start_time'];
    }
    
    if (count($slots) > 0) {
        foreach ($slots as &$s) {
            if (in_array($s['start_time'], $booked_times)) {
                $s['status'] = 'booked';
            }
        }
        closeDBConnection($conn);
        return $slots;
    }
    
    // Default office hour slots if no custom slots were defined
    $default_times = [
        ['start_time' => '09:00:00', 'end_time' => '10:00:00'],
        ['start_time' => '10:00:00', 'end_time' => '11:00:00'],
        ['start_time' => '11:00:00', 'end_time' => '12:00:00'],
        ['start_time' => '13:00:00', 'end_time' => '14:00:00'],
        ['start_time' => '14:00:00', 'end_time' => '15:00:00'],
        ['start_time' => '15:00:00', 'end_time' => '16:00:00'],
        ['start_time' => '16:00:00', 'end_time' => '17:00:00']
    ];
    
    $result_slots = [];
    foreach ($default_times as $dt) {
        $is_booked = in_array($dt['start_time'], $booked_times);
        $result_slots[] = [
            'id' => 0,
            'date' => $date,
            'start_time' => $dt['start_time'],
            'end_time' => $dt['end_time'],
            'status' => $is_booked ? 'booked' : 'available'
        ];
    }
    
    closeDBConnection($conn);
    return $result_slots;
}


// Book appointment
function bookAppointment($student_id, $counselor_id, $date, $start_time, $end_time, $concern) {
    $conn = getDBConnection();
    
    // Check if slot is already booked in appointments table
    $stmt = $conn->prepare("SELECT id FROM appointments 
                           WHERE counselor_id = ? AND appointment_date = ? AND start_time = ? AND status IN ('pending', 'approved')");
    $stmt->bind_param("iss", $counselor_id, $date, $start_time);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Selected time slot is already booked. Please choose another time.'];
    }
    
    // Check explicit availability record if present
    $stmt = $conn->prepare("SELECT id, status FROM availability 
                           WHERE counselor_id = ? AND date = ? AND start_time = ?");
    $stmt->bind_param("iss", $counselor_id, $date, $start_time);
    $stmt->execute();
    $avail_res = $stmt->get_result();
    
    $availability_id = null;
    if ($avail_res->num_rows > 0) {
        $avail_row = $avail_res->fetch_assoc();
        if ($avail_row['status'] === 'blocked') {
            closeDBConnection($conn);
            return ['success' => false, 'message' => 'This time slot has been blocked by the counselor.'];
        } elseif ($avail_row['status'] === 'booked') {
            closeDBConnection($conn);
            return ['success' => false, 'message' => 'Selected time slot is already booked.'];
        }
        $availability_id = $avail_row['id'];
    }
    
    // Insert appointment
    $stmt = $conn->prepare("INSERT INTO appointments (student_id, counselor_id, appointment_date, start_time, end_time, concern, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissss", $student_id, $counselor_id, $date, $start_time, $end_time, $concern);
    
    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        
        // Update or insert availability status to booked
        if ($availability_id) {
            $stmt = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
            $stmt->bind_param("i", $availability_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO availability (counselor_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, 'booked') ON DUPLICATE KEY UPDATE status = 'booked'");
            $stmt->bind_param("isss", $counselor_id, $date, $start_time, $end_time);
            $stmt->execute();
        }
        
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

// Get student analytics data for "My Insights"
function getStudentAnalyticsData($student_id) {
    $conn = getDBConnection();
    
    $appointments = getStudentAppointments($student_id);
    
    $attended_count = 0;
    $concerns_count = [];
    $months = ['Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0];
    
    foreach ($appointments as $apt) {
        if ($apt['status'] === 'completed' || ($apt['status'] === 'approved' && strtotime($apt['appointment_date']) < time())) {
            $attended_count++;
        }
        
        // Count concerns
        $concern = trim($apt['concern']);
        if (!empty($concern)) {
            // Find matched topic if present
            $matched = 'Other';
            if (stripos($concern, 'academic') !== false || stripos($concern, 'study') !== false) {
                $matched = 'Academic stress';
            } elseif (stripos($concern, 'anxiety') !== false || stripos($concern, 'stress') !== false) {
                $matched = 'Anxiety';
            } elseif (stripos($concern, 'family') !== false) {
                $matched = 'Family concerns';
            } elseif (stripos($concern, 'peer') !== false || stripos($concern, 'friend') !== false) {
                $matched = 'Peer relationships';
            } elseif (stripos($concern, 'career') !== false) {
                $matched = 'Career guidance';
            } else {
                $matched = substr($concern, 0, 20);
            }
            $concerns_count[$matched] = ($concerns_count[$matched] ?? 0) + 1;
        }
        
        // Monthly stats for last 6 months
        $m_name = date('M', strtotime($apt['appointment_date']));
        if (isset($months[$m_name])) {
            $months[$m_name]++;
        }
    }
    
    // Top concern
    $top_concern = 'Academic stress';
    if (!empty($concerns_count)) {
        arsort($concerns_count);
        $top_concern = array_key_first($concerns_count);
    }
    
    closeDBConnection($conn);
    
    return [
        'attended_count' => $attended_count,
        'top_concern' => $top_concern,
        'streak' => max(1, min($attended_count, 3)) . ' months',
        'monthly_labels' => array_keys($months),
        'monthly_values' => array_values($months)
    ];
}

// Reschedule appointment
function rescheduleAppointment($appointment_id, $student_id, $new_date, $new_start_time, $new_end_time) {
    $conn = getDBConnection();
    
    // Verify appointment exists
    $stmt = $conn->prepare("SELECT counselor_id, appointment_date, start_time, end_time FROM appointments WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $appointment_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Appointment not found.'];
    }
    
    $apt = $result->fetch_assoc();
    $counselor_id = $apt['counselor_id'];
    
    // Free previous availability slot if exists
    $stmt = $conn->prepare("UPDATE availability SET status = 'available' WHERE counselor_id = ? AND date = ? AND start_time = ? AND end_time = ?");
    $stmt->bind_param("isss", $counselor_id, $apt['appointment_date'], $apt['start_time'], $apt['end_time']);
    $stmt->execute();
    
    // Check/Book new availability slot if exists
    $stmt = $conn->prepare("UPDATE availability SET status = 'booked' WHERE counselor_id = ? AND date = ? AND start_time = ? AND end_time = ? AND status = 'available'");
    $stmt->bind_param("isss", $counselor_id, $new_date, $new_start_time, $new_end_time);
    $stmt->execute();
    
    // Update appointment
    $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, start_time = ?, end_time = ?, status = 'pending' WHERE id = ?");
    $stmt->bind_param("sssi", $new_date, $new_start_time, $new_end_time, $appointment_id);
    
    if ($stmt->execute()) {
        // Notification for counselor
        $msg = "Reschedule requested for appointment to " . formatDate($new_date) . " at " . formatTime($new_start_time);
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, message, type) VALUES (?, ?, ?, 'rescheduled')");
        $stmt->bind_param("iis", $counselor_id, $appointment_id, $msg);
        $stmt->execute();
        
        closeDBConnection($conn);
        return ['success' => true, 'message' => 'Appointment rescheduled successfully. Pending counselor approval.'];
    }
    
    closeDBConnection($conn);
    return ['success' => false, 'message' => 'Failed to reschedule appointment.'];
}
?>

