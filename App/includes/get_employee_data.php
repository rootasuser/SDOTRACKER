<?php
require_once '../../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;

if (!$conn) {
    die(json_encode(["error" => "Db fail"]));
}

$query = "SELECT empPosition_id, COUNT(*) as count FROM employees GROUP BY empPosition_id";
$result = $conn->query($query);

$positions = [];
$employeeCounts = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $positions[] = "Position " . htmlspecialchars($row['empPosition_id']);
        $employeeCounts[] = (int) $row['count'];
    }
}

$statusQuery = "SELECT empStatus, COUNT(*) as count FROM employees GROUP BY empStatus";
$statusResult = $conn->query($statusQuery);

$activeCount = 0;
$inactiveCount = 0;

if ($statusResult) {
    while ($row = $statusResult->fetch_assoc()) {
        if (strcasecmp($row['empStatus'], 'Active') === 0) {
            $activeCount = (int) $row['count'];
        } elseif (strcasecmp($row['empStatus'], 'Inactive') === 0) {
            $inactiveCount = (int) $row['count'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    "positions" => $positions,
    "employeeCounts" => $employeeCounts,
    "activeCount" => $activeCount,
    "inactiveCount" => $inactiveCount
]);
?>
