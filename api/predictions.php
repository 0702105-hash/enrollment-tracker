<?php
require_once 'config.php';

$program_id = $_GET['program_id'] ?? null;
$stmt = $pdo->prepare("
    SELECT p.*, prog.name as program_name 
    FROM predictions p 
    JOIN programs prog ON p.program_id = prog.id
    WHERE 1=1 " . ($program_id ? "AND p.program_id = ?" : "") . "
    ORDER BY p.created_at DESC
");
$stmt->execute($program_id ? [$program_id] : []);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
