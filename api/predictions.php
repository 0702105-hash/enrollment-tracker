<?php
/**
 * Predictions API Endpoint
 * =========================
 * Retrieves ENSEMBLE predictions for all programs
 * 
 * Response format:
 * {
 *   "success": true,
 *   "count": 24,
 *   "data": [
 *     {
 *       "id": 1,
 *       "program_id": 1,
 *       "academic_year": "2026-2027",
 *       "semester": 1,
 *       "predicted_total": 75,
 *       "predicted_male": 38,
 *       "predicted_female": 37,
 *       "confidence": 0.8234,
 *       "model_ensemble": "SARMAX+Prophet+LSTM",
 *       "created_at": "2026-03-06 14:35:22"
 *     },
 *     ...
 *   ]
 * }
 */

require_once 'config.php';

try {
    $program_id = $_GET['program_id'] ?? null;
    $academic_year = $_GET['academic_year'] ?? null;
    $semester = $_GET['semester'] ?? null;
    $model_ensemble = $_GET['model_ensemble'] ?? null;
    
    $query = "SELECT 
                id,
                program_id,
                academic_year,
                semester,
                predicted_total,
                predicted_male,
                predicted_female,
                confidence,
                model_ensemble,
                created_at
              FROM predictions 
              WHERE 1=1";
    
    $params = [];
    
    if ($program_id) {
        $query .= " AND program_id = ?";
        $params[] = $program_id;
    }
    
    if ($academic_year) {
        $query .= " AND academic_year = ?";
        $params[] = $academic_year;
    }
    
    if ($semester) {
        $query .= " AND semester = ?";
        $params[] = $semester;
    }
    
    if ($model_ensemble) {
        $query .= " AND model_ensemble = ?";
        $params[] = $model_ensemble;
    }
    
    $query .= " ORDER BY program_id, academic_year, semester";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric fields
    foreach ($predictions as &$pred) {
        $pred['program_id'] = (int) $pred['program_id'];
        $pred['semester'] = (int) $pred['semester'];
        $pred['predicted_total'] = (int) $pred['predicted_total'];
        $pred['predicted_male'] = (int) $pred['predicted_male'];
        $pred['predicted_female'] = (int) $pred['predicted_female'];
        $pred['confidence'] = (float) $pred['confidence'];
    }
    
    header('Content-Type: application/json');
    http_response_code(200);
    
    echo json_encode([
        'success' => true,
        'count' => count($predictions),
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $predictions
    ]);
    
} catch(Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>