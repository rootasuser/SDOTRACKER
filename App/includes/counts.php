<?php

    require_once '../../Config/Config.php';

    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    function countRecords($tableName, $conn) 
    {
        $query = "SELECT COUNT(*) AS total_count FROM $tableName";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_count'];
    }

    // Num. Employees
    $totalEmployees = countRecords('employees', $conn);

    // Num. Schools
    $totalSchools = countRecords('schools', $conn);

    // Num. Positions
    $totalPositions = countRecords('positions', $conn);

    // Num Teaching Subj
    $totalSubjects = countRecords('subjects', $conn);
?>