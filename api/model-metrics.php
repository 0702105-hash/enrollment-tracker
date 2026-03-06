<?php
require_once 'config.php';

try {
    $program_id = $_GET['program_id'] ?? null;
    
    $query = "SELECT * FROM model_metrics WHERE 1=1";
    $params = [];
    
    if ($program_id) {
        $query .= " AND program_id = ?";
        $params[] = $program_id;
    }
    
    $query .= " ORDER BY program_id, model_name, metric_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($metrics);
    
} catch(Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>