<?php

require_once '../../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;

function countRecords($tableName, $conn) {
    $query = "SELECT COUNT(*) AS total_count FROM $tableName";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_count'];
}

// Count the number of employees
$totalEmployees = countRecords('employees', $conn);

// Count the number of schools
$totalSchools = countRecords('schools', $conn);

// Count the number of positions
$totalPositions = countRecords('positions', $conn);

// Count the number of subjects
$totalSubjects = countRecords('subjects', $conn);
?>