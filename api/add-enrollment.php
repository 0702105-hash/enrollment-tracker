<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $program_id = $_POST['program_id'] ?? null;
        $academic_year = $_POST['academic_year'] ?? null;
        $semester = $_POST['semester'] ?? null;
        $male = $_POST['male'] ?? 0;
        $female = $_POST['female'] ?? 0;

        if (!$program_id || !$academic_year || !$semester) {
            throw new Exception('Missing required fields');
        }

        $stmt = $pdo->prepare("
            INSERT INTO enrollments (program_id, academic_year, semester, male, female)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                male = VALUES(male),
                female = VALUES(female)
        ");

        $stmt->execute([
            $program_id,
            $academic_year,
            $semester,
            intval($male),
            intval($female)
        ]);

        echo json_encode(['success' => true, 'message' => 'Enrollment added successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>