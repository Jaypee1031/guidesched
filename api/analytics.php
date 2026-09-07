<?php
require_once __DIR__ . '/config.php';

$userId = intval($_GET['user_id'] ?? 0);
$role = sanitize($_GET['role'] ?? 'student');
$year = intval($_GET['year'] ?? date('Y'));

if (!$userId) {
    jsonError('user_id is required', 422);
}

$conn = getApiDBConnection();

if ($role === 'student') {
    // 1. Total sessions completed
    $cStmt = $conn->prepare("SELECT COUNT(*) as completed_count FROM appointments WHERE student_id = ? AND status = 'completed'");
    $cStmt->bind_param("i", $userId);
    $cStmt->execute();
    $completedCount = intval($cStmt->get_result()->fetch_assoc()['completed_count'] ?? 0);

    // 2. Upcoming count
    $uStmt = $conn->prepare("SELECT COUNT(*) as upcoming_count FROM appointments WHERE student_id = ? AND status IN ('pending', 'approved') AND appointment_date >= CURDATE()");
    $uStmt->bind_param("i", $userId);
    $uStmt->execute();
    $upcomingCount = intval($uStmt->get_result()->fetch_assoc()['upcoming_count'] ?? 0);

    // 3. Total sessions requested
    $tStmt = $conn->prepare("SELECT COUNT(*) as total_count FROM appointments WHERE student_id = ?");
    $tStmt->bind_param("i", $userId);
    $tStmt->execute();
    $totalCount = intval($tStmt->get_result()->fetch_assoc()['total_count'] ?? 0);

    // 4. Concerns breakdown & top concern
    $concernsStmt = $conn->prepare("SELECT concern FROM appointments WHERE student_id = ?");
    $concernsStmt->bind_param("i", $userId);
    $concernsStmt->execute();
    $cRes = $concernsStmt->get_result();

    $categoryCounts = [
        'Academic stress' => 0,
        'Anxiety' => 0,
        'Family concerns' => 0,
        'Peer relationships' => 0,
        'Career & Strand guidance' => 0,
        'Other' => 0
    ];

    while ($r = $cRes->fetch_assoc()) {
        $cText = $r['concern'];
        $matched = false;
        foreach (array_keys($categoryCounts) as $cat) {
            if ($cat !== 'Other' && stripos($cText, $cat) !== false) {
                $categoryCounts[$cat]++;
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            $categoryCounts['Other']++;
        }
    }

    $topConcern = 'None yet';
    $maxConcernVal = 0;
    foreach ($categoryCounts as $cat => $cnt) {
        if ($cnt > $maxConcernVal) {
            $maxConcernVal = $cnt;
            $topConcern = $cat;
        }
    }

    // 5. Monthly sessions for selected year
    $mStmt = $conn->prepare("SELECT MONTH(appointment_date) as m, COUNT(*) as cnt 
                            FROM appointments 
                            WHERE student_id = ? AND YEAR(appointment_date) = ? AND status = 'completed'
                            GROUP BY MONTH(appointment_date)");
    $mStmt->bind_param("ii", $userId, $year);
    $mStmt->execute();
    $mRes = $mStmt->get_result();
    $monthlyData = array_fill(1, 12, 0);
    while ($mr = $mRes->fetch_assoc()) {
        $monthlyData[intval($mr['m'])] = intval($mr['cnt']);
    }

    $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $monthlyChart = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlyChart[] = [
            'month' => $monthLabels[$i - 1],
            'count' => $monthlyData[$i]
        ];
    }

    closeApiDBConnection($conn);
    jsonSuccess([
        'total_sessions' => $totalCount,
        'completed_sessions' => $completedCount,
        'upcoming_sessions' => $upcomingCount,
        'top_concern' => $topConcern,
        'consistency_streak_months' => min(6, max(1, $completedCount)),
        'monthly_chart' => $monthlyChart,
        'concern_breakdown' => $categoryCounts
    ]);

} else {
    // Counselor or Admin Analytics
    $whereCounselor = "";
    $params = [];
    $types = "";

    if ($role === 'counselor') {
        $whereCounselor = " WHERE counselor_id = ?";
        $params[] = $userId;
        $types .= "i";
    }

    // Counts by status
    $sql = "SELECT status, COUNT(*) as cnt FROM appointments" . $whereCounselor . " GROUP BY status";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $statusRes = $stmt->get_result();

    $counts = [
        'pending' => 0,
        'approved' => 0,
        'completed' => 0,
        'declined' => 0,
        'cancelled' => 0,
        'no_show' => 0,
        'total' => 0
    ];

    while ($sr = $statusRes->fetch_assoc()) {
        $st = $sr['status'];
        $c = intval($sr['cnt']);
        if (isset($counts[$st])) {
            $counts[$st] = $c;
        }
        $counts['total'] += $c;
    }

    // Today's appointments count
    $todaySql = "SELECT COUNT(*) as cnt FROM appointments WHERE appointment_date = CURDATE()" . ($role === 'counselor' ? " AND counselor_id = ?" : "");
    $tStmt = $conn->prepare($todaySql);
    if ($role === 'counselor') {
        $tStmt->bind_param("i", $userId);
    }
    $tStmt->execute();
    $todayCount = intval($tStmt->get_result()->fetch_assoc()['cnt'] ?? 0);

    // Concern topics
    $cSql = "SELECT concern FROM appointments" . $whereCounselor;
    $cStmt = $conn->prepare($cSql);
    if ($role === 'counselor') {
        $cStmt->bind_param("i", $userId);
    }
    $cStmt->execute();
    $cRes = $cStmt->get_result();

    $categories = [
        'Academic stress' => 0,
        'Anxiety' => 0,
        'Family concerns' => 0,
        'Peer relationships' => 0,
        'Career & Strand guidance' => 0,
        'Other' => 0
    ];

    while ($r = $cRes->fetch_assoc()) {
        $cText = $r['concern'];
        $matched = false;
        foreach (array_keys($categories) as $cat) {
            if ($cat !== 'Other' && stripos($cText, $cat) !== false) {
                $categories[$cat]++;
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            $categories['Other']++;
        }
    }

    // Monthly Trends for year
    $mSql = "SELECT MONTH(appointment_date) as m, COUNT(*) as cnt FROM appointments WHERE YEAR(appointment_date) = ?" . ($role === 'counselor' ? " AND counselor_id = ?" : "") . " GROUP BY MONTH(appointment_date)";
    $mStmt = $conn->prepare($mSql);
    if ($role === 'counselor') {
        $mStmt->bind_param("ii", $year, $userId);
    } else {
        $mStmt->bind_param("i", $year);
    }
    $mStmt->execute();
    $mRes = $mStmt->get_result();
    $monthlyData = array_fill(1, 12, 0);
    while ($mr = $mRes->fetch_assoc()) {
        $monthlyData[intval($mr['m'])] = intval($mr['cnt']);
    }

    $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $monthlyChart = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlyChart[] = [
            'month' => $monthLabels[$i - 1],
            'count' => $monthlyData[$i]
        ];
    }

    closeApiDBConnection($conn);
    jsonSuccess([
        'today_appointments' => $todayCount,
        'pending_requests' => $counts['pending'],
        'approved_sessions' => $counts['approved'],
        'completed_sessions' => $counts['completed'],
        'total_appointments' => $counts['total'],
        'no_show_rate' => $counts['total'] > 0 ? round(($counts['no_show'] / $counts['total']) * 100, 1) : 0,
        'monthly_trends' => $monthlyChart,
        'concern_distribution' => $categories,
        'status_summary' => $counts
    ]);
}
