<?php
header('Content-Type: application/json');

require_once '../../Config/Config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['term'])) {
    try {
        $config = new \App\Config\Config();
        $conn = $config->DB_CONNECTION;
        
        $searchTerm = trim($_GET['term']);
        
        $stmt = $conn->prepare("
            SELECT id, empName, empNumber
            FROM employees
            WHERE empName LIKE :search OR empNumber LIKE :search
            LIMIT 10
        ");
        $stmt->execute([':search' => "%$searchTerm%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($results);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}