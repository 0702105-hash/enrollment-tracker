<?php
require_once 'api/config.php';

try {
    // Find and delete prediction-like data (same start year with different end years)
    $stmt = $pdo->prepare("
        SELECT id, academic_year FROM enrollments
        WHERE CAST(SUBSTRING_INDEX(academic_year, '-', -1) AS UNSIGNED) - CAST(SUBSTRING_INDEX(academic_year, '-', 1) AS UNSIGNED) != 1
    ");
    $stmt->execute();
    $predictionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($predictionData)) {
        echo "Found " . count($predictionData) . " prediction-like records:\n";
        foreach ($predictionData as $record) {
            echo "- ID: {$record['id']}, Academic Year: {$record['academic_year']}\n";
        }

        // Delete the prediction data
        $ids = array_column($predictionData, 'id');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        $deleteStmt = $pdo->prepare("DELETE FROM enrollments WHERE id IN ($placeholders)");
        $deleteStmt->execute($ids);

        echo "\n✅ Deleted " . count($predictionData) . " prediction-like records from enrollments table.\n";
    } else {
        echo "✅ No prediction-like data found in enrollments table.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>