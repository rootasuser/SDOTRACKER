<?php
require_once '../../Config/Config.php';

session_start();

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $data = json_decode(file_get_contents('php://input'), true);

    $employeeId = $data['employeeId'] ?? null;
    $trainingId = $data['trainingId'] ?? null;

    if (!$employeeId || !$trainingId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing employee or training ID']);
        exit;
    }

    $deleteStmt = $conn->prepare("
        DELETE FROM employee_trainings
        WHERE employee_id = :empId AND training_id = :trainingId
    ");
    $deleteStmt->execute([':empId' => $employeeId, ':trainingId' => $trainingId]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to remove training: ' . $e->getMessage()]);
}