<?php
// Include the Config.php for the database connection
require_once '../../Config/Config.php';

// Create an instance of the Config class to access DB connection
$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;

if (isset($_GET['filterValue']) && isset($_GET['filterType'])) {
    $filterValue = $_GET['filterValue'];
    $filterType = $_GET['filterType'];

    // Query based on the filter type
    $sql = "SELECT * FROM employees WHERE $filterType = :filterValue";
    $stmt = $conn->prepare($sql);

    // Bind the parameter for PDO
    $stmt->bindParam(':filterValue', $filterValue, PDO::PARAM_STR);
    
    $stmt->execute();

    // Fetch the result
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any employees match the filter
    if (count($employees) > 0) {
        foreach ($employees as $employee) {
            echo "<div class='employee-item'>";
            echo "<p>Name: " . htmlspecialchars($employee['empName']) . "</p>";
            echo "<p>Position: " . htmlspecialchars($employee['empPosition']) . "</p>";
            echo "<p>School: " . htmlspecialchars($employee['empAssignSchool']) . "</p>";
            echo "<p>Teaching Subject: " . htmlspecialchars($employee['empTeachingSubject']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No employees found.</p>";
    }
}
?>
