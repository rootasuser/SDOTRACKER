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

// Count Male and Female Employees
function countMaleFemaleEmployees($conn) {
    $query = "SELECT empSex, COUNT(*) AS count FROM employees GROUP BY empSex";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $maleCount = 0;
    $femaleCount = 0;

    foreach ($result as $row) {
        if ($row['empSex'] === 'Male') {
            $maleCount = (int) $row['count'];
        } elseif ($row['empSex'] === 'Female') {
            $femaleCount = (int) $row['count'];
        }
    }

    return [
        'maleCount' => $maleCount,
        'femaleCount' => $femaleCount
    ];
}

$genderCounts = countMaleFemaleEmployees($conn);
$maleCount = $genderCounts['maleCount'];
$femaleCount = $genderCounts['femaleCount'];


// Count Employees by Age Range
function countEmployeesByAge($conn) {
    $currentYear = date('Y');
    
    $query = "SELECT 
        CASE 
            WHEN YEAR(CURDATE()) - YEAR(empDob) < 20 THEN 'Under 20'
            WHEN YEAR(CURDATE()) - YEAR(empDob) BETWEEN 20 AND 29 THEN '20-29'
            WHEN YEAR(CURDATE()) - YEAR(empDob) BETWEEN 30 AND 39 THEN '30-39'
            WHEN YEAR(CURDATE()) - YEAR(empDob) BETWEEN 40 AND 49 THEN '40-49'
            WHEN YEAR(CURDATE()) - YEAR(empDob) BETWEEN 50 AND 59 THEN '50-59'
            ELSE '60+'
        END AS ageRange,
        COUNT(*) AS count
    FROM employees
    GROUP BY ageRange
    ORDER BY ageRange";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ageRanges = [];
    $ageCounts = [];

    foreach ($result as $row) {
        $ageRanges[] = $row['ageRange'];
        $ageCounts[] = (int) $row['count'];
    }

    return [
        'ageRanges' => $ageRanges,
        'ageCounts' => $ageCounts
    ];
}

$ageData = countEmployeesByAge($conn);
$ageRanges = $ageData['ageRanges'];
$ageCounts = $ageData['ageCounts'];

?>