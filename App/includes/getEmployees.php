<?php

require_once __DIR__ . '/../Config/Config.php';

use App\Config\Config;

$config = new Config();
$conn = $config->DB_CONNECTION;

if (isset($_GET['school_id'])) {
    $school_id = intval($_GET['school_id']);
    
    $stmt = $conn->prepare("SELECT empName, empNumber, empPosition_id FROM employees WHERE empAssignSchool_id = ?");
    $stmt->bindParam(1, $school_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($employees);
}

?>
