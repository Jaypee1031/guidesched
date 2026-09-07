<?php
require_once __DIR__ . '/config.php';

$conn = getApiDBConnection();

$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT u.id, u.user_id, u.name, u.email, u.status, u.created_at,
               sp.student_number, sp.course, sp.year_level, sp.contact_number,
               COUNT(a.id) as total_appointments,
               SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_sessions
        FROM users u
        LEFT JOIN student_profiles sp ON u.id = sp.user_id
        LEFT JOIN appointments a ON u.id = a.student_id
        WHERE u.role = 'student'";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR sp.student_number LIKE ? OR u.email LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
    $types = "sss";
}

$sql .= " GROUP BY u.id ORDER BY u.name ASC LIMIT 100";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

closeApiDBConnection($conn);
jsonSuccess(['students' => $students]);
