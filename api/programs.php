<?php
require_once 'config.php';

$stmt = $pdo->query("
    SELECT p.id, p.name, d.name as department 
    FROM programs p 
    JOIN departments d ON p.dept_id = d.id 
    ORDER BY d.name, p.name
");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
