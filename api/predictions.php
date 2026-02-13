<?php
require_once 'config.php';

$program_id = $_GET['program_id'] ?? null;
$query = "SELECT * FROM predictions WHERE 1=1";
$params = [];

if ($program_id) {
    $query .= " AND program_id = ?";
    $params[] = $program_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
