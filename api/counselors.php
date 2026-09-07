<?php
require_once __DIR__ . '/config.php';

$conn = getApiDBConnection();

$query = "SELECT u.id, u.user_id, u.name, u.email, 
          COALESCE(c.specialization, 'General Guidance') as specialization,
          COALESCE(c.contact_number, '') as contact_number,
          c.profile_picture
          FROM users u
          LEFT JOIN counselor_profiles c ON u.id = c.user_id
          WHERE u.role IN ('counselor', 'admin') AND u.status = 'active'
          ORDER BY u.name ASC";

$result = $conn->query($query);
$counselors = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $counselors[] = $row;
    }
}

closeApiDBConnection($conn);
jsonSuccess(['counselors' => $counselors]);
