<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? null;
        $male = $_POST['male'] ?? 0;
        $female = $_POST['female'] ?? 0;

        if (!$id) {
            throw new Exception('ID not provided');
        }

        $stmt = $pdo->prepare("
            UPDATE enrollments 
            SET male = ?, female = ?
            WHERE id = ?
        ");

        $stmt->execute([
            intval($male),
            intval($female),
            $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Enrollment updated successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>