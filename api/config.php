<?php
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost'; $user = 'root'; $pass = ''; $dbname = 'casDB';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'DB failed']);
    exit;
}
?>
