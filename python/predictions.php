<?php
require_once 'config.php';

$program_id = $_GET['program_id'] ?? null;
$query = "SELECT * FROM predictions WHERE 1=1";
$params = [];

if ($program_id) {
    $query .= " AND program_id = ?";
    $params[] = $program_id;
}

$query .= " ORDER BY program_id, academic_year, semester";
$stmt = $pdo->prepare($query);
$stmt->execute($params);

$predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($predictions);
?>
