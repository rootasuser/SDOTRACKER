<?php
header('Content-Type: application/json');
require_once '../../Config/Config.php';
session_start();

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "DB connection failed: " . $e->getMessage()]);
    exit;
}

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!isset($_GET['trainingId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Training ID is required']);
    exit;
}

$trainingId = $_GET['trainingId'];

try {
    $stmt = $conn->prepare("
        SELECT e.id, e.empName, p.empPosition, s.empAssignSchool
        FROM employees e
        JOIN employee_trainings et ON e.id = et.employee_id
        JOIN positions p ON e.empPosition_id = p.id
        JOIN schools s ON e.empAssignSchool_id = s.id
        WHERE et.training_id = :trainingId
    ");
    $stmt->execute([':trainingId' => $trainingId]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($employees);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}