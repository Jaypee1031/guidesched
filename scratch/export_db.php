<?php
require_once __DIR__ . '/../config/config.php';

$conn = getDBConnection();
$sql = "-- GuideSched Database Dump (Unified 2-Role System: Student & Counselor)\n";
$sql .= "-- Cagasat High School Guidance Office\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

$tables = ['users', 'student_profiles', 'counselor_profiles', 'appointments', 'availability_slots', 'notifications'];

foreach ($tables as $table) {
    $create_res = $conn->query("SHOW CREATE TABLE `$table`");
    if ($create_res) {
        $row = $create_res->fetch_row();
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $row[1] . ";\n\n";
        
        $data_res = $conn->query("SELECT * FROM `$table`");
        if ($data_res && $data_res->num_rows > 0) {
            $sql .= "INSERT INTO `$table` VALUES\n";
            $rows_sql = [];
            while ($data_row = $data_res->fetch_assoc()) {
                $vals = array_map(function($v) use ($conn) {
                    if ($v === null) return "NULL";
                    return "'" . $conn->real_escape_string($v) . "'";
                }, array_values($data_row));
                $rows_sql[] = "(" . implode(", ", $vals) . ")";
            }
            $sql .= implode(",\n", $rows_sql) . ";\n\n";
        }
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/../database/guidesched_full.sql', $sql);
echo "Exported database to database/guidesched_full.sql successfully!\n";
closeDBConnection($conn);
?>
