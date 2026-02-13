<?php
session_start();
require_once '../includes/functions.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    global $conn;
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'msg' => 'Invalid credentials.']);
    }
    exit;
}

if ($method === 'GET' && isset($_GET['logout'])) {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'GET' && isset($_GET['check_auth'])) {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false]);
    }
    exit;
}

?>
