<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $program_id = $_GET['program_id'] ?? null;
        $stmt = $pdo->prepare("
            SELECT e.*, p.name as program_name, d.name as dept_name 
            FROM enrollments e 
            JOIN programs p ON e.program_id = p.id 
            JOIN departments d ON p.dept_id = d.id 
            WHERE 1=1 " . ($program_id ? "AND e.program_id = ?" : "") . "
            ORDER BY e.program_id, e.time_index
        ");
        $stmt->execute($program_id ? [$program_id] : []);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (program_id, academic_year, semester, male, female, time_index)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                male=VALUES(male), female=VALUES(female), time_index=VALUES(time_index)
        ");
        $stmt->execute([
            $data['program_id'], $data['academic_year'], $data['semester'],
            $data['male'], $data['female'], $data['time_index']
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;
}
?>
