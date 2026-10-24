<?php
require_once '../config/database.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=student_records_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Full Name', 'Email', 'Mobile', 'Course', 'Year of Study', 'Status', 'Created At']);

$result = $conn->query("SELECT id, full_name, email, mobile, course, year_of_study, status, created_at FROM practical_students");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>