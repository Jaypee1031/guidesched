<?php
// Historical Data Seeder for GuideSched (2024, 2025, 2026)
require_once __DIR__ . '/../config/config.php';

echo "=== GuideSched Multi-Year Historical Data Seeder (2024, 2025, 2026) ===\n\n";

$conn = getDBConnection();

// 1. Ensure counselors exist
$counselors_data = [
    ['name' => 'Dr. Maria Santos', 'email' => 'maria.santos@guidesched.com', 'specialization' => 'Academic Counseling', 'contact' => '09171234567'],
    ['name' => 'Ms. Grace Fontanilla', 'email' => 'g.fontanilla@cagasaths.edu.ph', 'specialization' => 'Career & Strand Guidance', 'contact' => '09182345678'],
    ['name' => 'Mr. Juan Dela Cruz', 'email' => 'j.delacruz@cagasaths.edu.ph', 'specialization' => 'Behavioral & Emotional Wellness', 'contact' => '09193456789']
];

$counselor_ids = [];
foreach ($counselors_data as $c) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $c['email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $counselor_ids[] = $res->fetch_assoc()['id'];
    } else {
        $user_id = 'CNS' . rand(1000, 9999);
        $hashed_pass = password_hash('counselor123', PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (user_id, role, name, email, password, status) VALUES (?, 'counselor', ?, ?, ?, 'active')");
        $stmt->bind_param("ssss", $user_id, $c['name'], $c['email'], $hashed_pass);
        $stmt->execute();
        $new_id = $conn->insert_id;
        
        $stmt = $conn->prepare("INSERT INTO counselor_profiles (user_id, specialization, contact_number) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $new_id, $c['specialization'], $c['contact']);
        $stmt->execute();
        $counselor_ids[] = $new_id;
    }
}

echo "Counselors ready: " . count($counselor_ids) . "\n";

// 2. Ensure students exist
$students_data = [
    ['name' => 'Juan Santos', 'email' => 'juan.santos@cagasaths.edu.ph', 'lrn' => '102938475611', 'course' => 'Grade 11 - STEM'],
    ['name' => 'Angelica Reyes', 'email' => 'a.reyes@cagasaths.edu.ph', 'lrn' => '102938475612', 'course' => 'Grade 12 - ABM'],
    ['name' => 'Mark Joseph Cruz', 'email' => 'm.cruz@cagasaths.edu.ph', 'lrn' => '102938475613', 'course' => 'Grade 10 (Junior High)'],
    ['name' => 'Sophia Loraine Diaz', 'email' => 's.diaz@cagasaths.edu.ph', 'lrn' => '102938475614', 'course' => 'Grade 11 - HUMSS'],
    ['name' => 'Carlos Miguel Ramos', 'email' => 'c.ramos@cagasaths.edu.ph', 'lrn' => '102938475615', 'course' => 'Grade 12 - TVL'],
    ['name' => 'Patricia Nicole Lim', 'email' => 'p.lim@cagasaths.edu.ph', 'lrn' => '102938475616', 'course' => 'Grade 8 (Junior High)'],
    ['name' => 'Ethan Dave Garcia', 'email' => 'e.garcia@cagasaths.edu.ph', 'lrn' => '102938475617', 'course' => 'Grade 9 (Junior High)'],
    ['name' => 'Bea Christine Mendoza', 'email' => 'b.mendoza@cagasaths.edu.ph', 'lrn' => '102938475618', 'course' => 'Grade 11 - STEM']
];

$student_ids = [];
foreach ($students_data as $s) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $s['email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $student_ids[] = $res->fetch_assoc()['id'];
    } else {
        $user_id = 'STD' . rand(10000, 99999);
        $hashed_pass = password_hash('student123', PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (user_id, role, name, email, password, status) VALUES (?, 'student', ?, ?, ?, 'active')");
        $stmt->bind_param("ssss", $user_id, $s['name'], $s['email'], $hashed_pass);
        $stmt->execute();
        $new_id = $conn->insert_id;
        
        $year_lvl = 11;
        if (strpos($s['course'], 'Grade 8') !== false) $year_lvl = 8;
        if (strpos($s['course'], 'Grade 9') !== false) $year_lvl = 9;
        if (strpos($s['course'], 'Grade 10') !== false) $year_lvl = 10;
        if (strpos($s['course'], 'Grade 12') !== false) $year_lvl = 12;

        $contact = '09' . rand(100000000, 999999999);
        $stmt = $conn->prepare("INSERT INTO student_profiles (user_id, student_number, course, year_level, contact_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $new_id, $s['lrn'], $s['course'], $year_lvl, $contact);
        $stmt->execute();
        $student_ids[] = $new_id;
    }
}

echo "Students ready: " . count($student_ids) . "\n";

// 3. Generate Historical Appointments for 2024, 2025, and 2026
$concerns_pool = [
    '[Face-to-face] Academic stress: Exam Preparation & Study Strategies',
    '[Face-to-face] Academic stress: Subject Difficulty in Mathematics & Science',
    '[Online Session] Academic stress: Classroom Focus & Time Management',
    '[Face-to-face] Anxiety & Wellness: Stress Management & Test Anxiety',
    '[Online Session] Anxiety & Wellness: Emotional Balance & Daily Mindfulness',
    '[Face-to-face] Career & Strand guidance: Senior High Strand Selection (STEM vs ABM)',
    '[Face-to-face] Career & Strand guidance: College Degree Planning & Scholarships',
    '[Online Session] Family concerns: Family Communication & Personal Support',
    '[Face-to-face] Peer relationships: Friendship Dynamics & Group Work Collaboration',
    '[Face-to-face] Personal Counseling: Personal Growth & Self-Confidence'
];

$times_pool = [
    ['08:30:00', '09:30:00'],
    ['09:30:00', '10:30:00'],
    ['10:30:00', '11:30:00'],
    ['13:00:00', '14:00:00'],
    ['14:00:00', '15:00:00'],
    ['15:00:00', '16:00:00']
];

$years = [2024, 2025, 2026];
$total_inserted = 0;

foreach ($years as $yr) {
    // Generate between 40 and 60 appointments per year
    $count = ($yr === 2026) ? 35 : rand(45, 60);
    
    for ($i = 0; $i < $count; $i++) {
        $month = rand(1, 12);
        if ($yr == 2026 && $month > 9) { $month = rand(1, 9); }
        $day = rand(1, 28);
        $date_str = sprintf('%04d-%02d-%02d', $yr, $month, $day);
        
        $st_id = $student_ids[array_rand($student_ids)];
        $cs_id = $counselor_ids[array_rand($counselor_ids)];
        $concern = $concerns_pool[array_rand($concerns_pool)];
        $time = $times_pool[array_rand($times_pool)];
        
        // Status determination
        if ($yr < 2026 || ($yr == 2026 && $month < 8)) {
            // Past appointments
            $r = rand(1, 100);
            if ($r <= 75) { $status = 'completed'; }
            elseif ($r <= 85) { $status = 'no_show'; }
            elseif ($r <= 93) { $status = 'cancelled'; }
            else { $status = 'declined'; }
        } else {
            // Recent / Upcoming 2026
            $r = rand(1, 100);
            if ($r <= 50) { $status = 'approved'; }
            elseif ($r <= 80) { $status = 'pending'; }
            else { $status = 'completed'; }
        }
        
        // Insert appointment
        $stmt = $conn->prepare("INSERT INTO appointments (student_id, counselor_id, appointment_date, start_time, end_time, concern, status, admin_notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'Seeded historical record')");
        $stmt->bind_param("iisssss", $st_id, $cs_id, $date_str, $time[0], $time[1], $concern, $status);
        if ($stmt->execute()) {
            $total_inserted++;
        }
    }
}

echo "Successfully seeded $total_inserted historical appointment records for 2024, 2025, and 2026!\n";
closeDBConnection($conn);
?>
