<?php
require_once 'api/config.php';
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM casPrograms');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'Programs count: ' . $result['count'] . PHP_EOL;

    $stmt = $pdo->query('SELECT name FROM casPrograms LIMIT 3');
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Sample programs:' . PHP_EOL;
    foreach($programs as $program) {
        echo '- ' . $program['name'] . PHP_EOL;
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>