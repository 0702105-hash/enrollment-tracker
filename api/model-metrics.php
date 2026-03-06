<?php
/**
 * Model Metrics API Endpoint
 * Retrieves evaluation metrics for all 3 models
 * 
 * Metrics returned per model:
 * - MAE: Mean Absolute Error
 * - RMSE: Root Mean Squared Error
 * - MAPE: Mean Absolute Percentage Error
 * - R²: Coefficient of Determination
 * - RMSLE: Root Mean Squared Log Error
 * - Theil-U: Theil Inequality Coefficient
 */

require_once 'config.php';

try {
    $program_id = $_GET['program_id'] ?? null;
    $model_name = $_GET['model_name'] ?? null;
    $metric_name = $_GET['metric_name'] ?? null;
    
    $query = "SELECT * FROM model_metrics WHERE 1=1";
    $params = [];
    
    if ($program_id) {
        $query .= " AND program_id = ?";
        $params[] = $program_id;
    }
    
    if ($model_name) {
        $query .= " AND model_name = ?";
        $params[] = $model_name;
    }
    
    if ($metric_name) {
        $query .= " AND metric_name = ?";
        $params[] = $metric_name;
    }
    
    $query .= " ORDER BY program_id, model_name, metric_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count' => count($metrics),
        'data' => $metrics
    ]);
    
} catch(Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>