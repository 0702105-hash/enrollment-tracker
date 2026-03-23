<?php
/**
 * Predictions API Endpoint
 * Returns saved prediction rows for all models and ensemble.
 */

require_once 'config.php';

try {
    $program_id = $_GET['program_id'] ?? null;
    $academic_year = $_GET['academic_year'] ?? null;
    $semester = $_GET['semester'] ?? null;
    $model_ensemble = $_GET['model_ensemble'] ?? null;
    $model_name = $_GET['model_name'] ?? null;

    $columnsStmt = $pdo->query("SHOW COLUMNS FROM predictions");
    $availableColumns = [];
    while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
        $availableColumns[$row['Field']] = true;
    }

    $selectColumns = [
        'id',
        'program_id',
        'academic_year',
        'semester',
        'predicted_total',
        'predicted_male',
        'predicted_female',
        'confidence',
        'created_at'
    ];

    if (isset($availableColumns['model_ensemble'])) {
        $selectColumns[] = 'model_ensemble';
    }

    if (isset($availableColumns['model_name'])) {
        $selectColumns[] = 'model_name';
    }

    $query = "SELECT " . implode(', ', $selectColumns) . " FROM predictions WHERE 1=1";

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

    if ($model_ensemble && isset($availableColumns['model_ensemble'])) {
        $query .= " AND model_ensemble = ?";
        $params[] = $model_ensemble;
    }

    if ($model_name && isset($availableColumns['model_name'])) {
        $query .= " AND model_name = ?";
        $params[] = $model_name;
    }

    $orderBy = "program_id, academic_year, semester";
    if (isset($availableColumns['model_name'])) {
        $orderBy .= ", model_name";
    }
    $query .= " ORDER BY " . $orderBy;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($predictions as &$pred) {
        $pred['program_id'] = (int) $pred['program_id'];
        $pred['semester'] = (int) $pred['semester'];
        $pred['predicted_total'] = (int) $pred['predicted_total'];
        $pred['predicted_male'] = (int) $pred['predicted_male'];
        $pred['predicted_female'] = (int) $pred['predicted_female'];
        $pred['confidence'] = (float) $pred['confidence'];
        if (!isset($pred['model_name']) || $pred['model_name'] === null || $pred['model_name'] === '') {
            $pred['model_name'] = 'Ensemble';
        }
        if (!isset($pred['model_ensemble']) || $pred['model_ensemble'] === null || $pred['model_ensemble'] === '') {
            $pred['model_ensemble'] = 'Prophet+LSTM+XGBoost';
        }
    }

    header('Content-Type: application/json');
    http_response_code(200);

    echo json_encode([
        'success' => true,
        'count' => count($predictions),
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $predictions
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>