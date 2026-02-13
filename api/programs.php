<?php
require_once 'config.php';

$stmt = $pdo->query("SELECT id, name FROM casPrograms ORDER BY name");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($programs);
?>
