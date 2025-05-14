<?php
require_once '../../Config/Config.php';

session_start();

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $employeeId = $_GET['employeeId'] ?? null;

    if (!$employeeId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing employee ID']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT t.*
        FROM trainings t
        JOIN employee_trainings et ON t.id = et.training_id
        WHERE et.employee_id = :empId
    ");
    $stmt->execute([':empId' => $employeeId]);
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($trainings);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve trainings: ' . $e->getMessage()]);
}