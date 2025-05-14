<?php
require_once '../../Config/Config.php';

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $searchTerm = $_GET['query'] ?? '';

    $stmt = $conn->prepare("
        SELECT id, title, date_conducted, venue
        FROM trainings
        WHERE title LIKE :search
        OR date_conducted LIKE :search
        OR venue LIKE :search
    ");
    $stmt->execute([':search' => "%{$searchTerm}%"]);
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($trainings);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}