<?php
require_once 'config.php';

function get_programs() {
    global $conn;
    $stmt = $conn->prepare("SELECT p.id, p.name, d.name AS dept_name FROM programs p JOIN departments d ON p.dept_id = d.id");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_enrollments_by_program($program_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE program_id = ? ORDER BY academic_year, semester");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_last_prediction($program_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM predictions WHERE program_id = ? ORDER BY academic_year DESC, semester DESC LIMIT 1");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ?: [];
}

?>
