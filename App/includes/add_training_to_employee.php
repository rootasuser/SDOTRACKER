<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configFilePath = __DIR__ . '/../../Config/Config.php'; 
if (!file_exists($configFilePath)) {
    die(json_encode(['success' => false, 'error' => 'Config file missing at ' . $configFilePath]));
}
require_once($configFilePath);

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]));
}

if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}


$data = json_decode(file_get_contents('php://input'), true);


if (!isset($data['employeeId']) || !isset($data['trainingId'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid request data']));
}

$employeeId = intval($data['employeeId']);
$trainingId = intval($data['trainingId']);

// Check if training is already linked to the employee
$sqlCheck = "SELECT id FROM employee_trainings 
             WHERE employee_id = :employeeId AND training_id = :trainingId 
             LIMIT 1";

$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
$stmtCheck->bindParam(':trainingId', $trainingId, PDO::PARAM_INT);
$stmtCheck->execute();

if ($stmtCheck->rowCount() > 0) {
    die(json_encode(['success' => false, 'error' => 'Training already linked to this employee']));
}

// Insert new training link
$sqlInsert = "INSERT INTO employee_trainings (employee_id, training_id) 
              VALUES (:employeeId, :trainingId)";

$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
$stmtInsert->bindParam(':trainingId', $trainingId, PDO::PARAM_INT);

if ($stmtInsert->execute()) {
    $response = ['success' => true];
} else {
    $response = ['success' => false, 'error' => 'Error inserting training: ' . implode(' - ', $stmtInsert->errorInfo())];
}

$stmtCheck = null;
$stmtInsert = null;
$conn = null;

header('Content-Type: application/json');
echo json_encode($response);
?>