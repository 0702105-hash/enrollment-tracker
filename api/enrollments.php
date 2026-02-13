<?php
require_once 'config.php';

$program_id = $_GET['program_id'] ?? null;
$query = "SELECT e.*, (e.male + e.female) as total FROM enrollments e WHERE 1=1";
$params = [];

if ($program_id) {
    $query .= " AND e.program_id = ?";
    $params[] = $program_id;
}

$query .= " ORDER BY e.academic_year, e.semester";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
?>
