<?php
require_once '../../Config/Config.php';
session_start();

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $data = json_decode(file_get_contents('php://input'), true);

    $employeeId = intval($data['employeeId']);
    $trainingId = intval($data['trainingId']);

    $stmt = $conn->prepare("INSERT INTO employee_trainings (employee_id, training_id) VALUES (:employeeId, :trainingId)");
    $stmt->execute([':employeeId' => $employeeId, ':trainingId' => $trainingId]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>